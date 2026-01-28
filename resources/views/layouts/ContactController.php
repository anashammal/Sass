<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Notifications\ContactWelcomeNotification;
use Illuminate\Support\Facades\File;
use App\Exports\ContactsExport; // <-- !! ضروري جداً !!
use Maatwebsite\Excel\Facades\Excel; // <-- !! ضروري جداً !!
use Barryvdh\DomPDF\Facade\Pdf; // <-- !! ضروري جداً !!
 
class ContactController extends Controller
{
    protected function getCountryCodes()
    {
        $path = resource_path('data/country_codes.json');
        if (File::exists($path)) {
            return json_decode(File::get($path), true);
        }
        return [];
    }

    /**
     * عرض صفحة تحتوي على قائمة بجميع جهات الاتصال (مع دعم التقسيم والفلترة).
     */
    public function index(Request $request)
    {
        $store = Auth::user()->store;
        $query = Contact::where('store_id', $store->id);

        // منطق التقسيم والبحث والفلترة
        $limit = $request->get('limit', 10); 
        $search = $request->get('search'); 
        $filterType = $request->get('type', 'all'); 

        // تطبيق الفلترة حسب النوع
        if ($filterType == 'suppliers') {
            $query->where('is_supplier', true);
        } elseif ($filterType == 'customers') {
            $query->where('is_customer', true);
        }

        // تطبيق البحث
        if ($search) {
             $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', '%'.$search.'%')
                  ->orWhere('company_name', 'like', '%'.$search.'%')
                  ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }
        
        $contacts = $query->orderBy('contact_name')->paginate($limit);
        
        // حساب الرصيد الإجمالي
        $totalBalance = Contact::where('store_id', $store->id)
                                ->when($filterType == 'suppliers', function ($q) {
                                    return $q->where('is_supplier', true);
                                })
                                ->when($filterType == 'customers', function ($q) {
                                    return $q->where('is_customer', true);
                                })
                                ->sum('balance');

        return view('store_owner.contacts.index', compact('contacts', 'filterType', 'search', 'limit', 'totalBalance'));
    }

    public function create(Request $request) 
    {
        $countries = $this->getCountryCodes();
        return view('store_owner.contacts.create', compact('countries'));
    }

    /**
     * تخزين جهة الاتصال الجديدة في قاعدة البيانات.
     */
    public function store(Request $request)
    {
        // ... (كود دالة store) ...
        $request->validate([
            'contact_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'country_code' => 'required|string|max:5',
            'phone' => ['required', 'string', 'max:50', Rule::unique('contacts', 'phone')->where('store_id', Auth::user()->store->id)], 
            'email' => 'nullable|email|max:255',
            'is_supplier' => 'nullable|boolean',
            'is_customer' => 'nullable|boolean',
            'credit_limit' => 'nullable|numeric|min:0',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);
        
        if (!$request->has('is_supplier') && !$request->has('is_customer')) {
            return back()->withInput()->withErrors(['type_error' => 'يجب تحديد نوع واحد على الأقل (مورد أو زبون).']);
        }

        $fullPhone = $request->country_code . $request->phone;

        $contact = new Contact($request->all());
        $contact->store_id = Auth::user()->store->id;
        $contact->is_supplier = $request->has('is_supplier');
        $contact->is_customer = $request->has('is_customer');
        $contact->phone = $fullPhone;
        $contact->save();

        return redirect()->route('store.contacts.index')
                         ->with('success', 'تم إنشاء جهة الاتصال بنجاح.');
    }

    /**
     * عرض نموذج التعديل.
     */
    public function edit(Contact $contact)
    {
        if ($contact->store_id != Auth::user()->store->id) { abort(403, 'غير مصرح لك'); }
        $countries = $this->getCountryCodes();
        
        // تقسيم رقم الهاتف إلى الكود والرقم
        $fullPhone = $contact->phone;
        $countryCode = '';
        $phoneWithoutCode = $fullPhone;

        foreach ($countries as $country) {
            $code = $country['dial_code'];
            if (strpos($fullPhone, $code) === 0) {
                $countryCode = $code;
                $phoneWithoutCode = substr($fullPhone, strlen($code));
                break;
            }
        }

        return view('store_owner.contacts.edit', compact('contact', 'countries', 'countryCode', 'phoneWithoutCode'));
    }


    /**
     * تحديث جهة الاتصال في قاعدة البيانات.
     */
    public function update(Request $request, Contact $contact)
    {
        if ($contact->store_id != Auth::user()->store->id) { abort(403, 'غير مصرح لك'); }
        
        // التحقق من صحة البيانات (مع استثناء جهة الاتصال الحالية من التحقق من الهاتف)
        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'country_code' => 'required|string|max:5',
            'phone' => ['required', 'string', 'max:50', Rule::unique('contacts', 'phone')->ignore($contact->id, 'id')->where('store_id', Auth::user()->store->id)], 
            'email' => 'nullable|email|max:255',
            'is_supplier' => 'nullable|boolean',
            'is_customer' => 'nullable|boolean',
            'credit_limit' => 'nullable|numeric|min:0',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        if (!$request->has('is_supplier') && !$request->has('is_customer')) {
            return back()->withInput()->withErrors(['type_error' => 'يجب تحديد نوع واحد على الأقل (مورد أو زبون).']);
        }

        $fullPhone = $request->country_code . $request->phone;

        $contact->fill($validatedData);
        $contact->is_supplier = $request->has('is_supplier');
        $contact->is_customer = $request->has('is_customer');
        $contact->phone = $fullPhone; 
        $contact->save();

        return redirect()->route('store.contacts.index')
                         ->with('success', 'تم تحديث جهة الاتصال بنجاح.');
    }

    /**
     * دالة الحذف (Destroy).
     */
    public function destroy(Contact $contact, Request $request) 
    {
        if ($contact->store_id != Auth::user()->store->id) { abort(403, 'غير مصرح لك'); }

        // !! (لاحقاً: يجب إضافة منطق التحقق من الفواتير المرتبطة قبل الحذف) !!
        $contact->delete();

        return redirect()->route('store.contacts.index')
                         ->with('success', 'تم حذف جهة الاتصال بنجاح.');
    }


    /**
     * !! -- دالة التصدير (Export) -- !!
     * تقوم بتصدير قائمة جهات الاتصال إلى Excel/PDF.
     */
    public function export(Request $request)
    {
        $store = Auth::user()->store;
        $query = Contact::where('store_id', $store->id);
        
        // جلب الفلاتر والبحث (لضمان تصدير البيانات الصحيحة)
        if ($request->type == 'suppliers') {
            $query->where('is_supplier', true);
        } elseif ($request->type == 'customers') {
            $query->where('is_customer', true);
        }
        if ($request->search) {
             $search = $request->search;
             $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', '%'.$search.'%')
                  ->orWhere('company_name', 'like', '%'.$search.'%')
                  ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        // جلب كل البيانات المفلترة (بدون تقسيم)
        $contactsToExport = $query->orderBy('contact_name')->get();
        
        $filename = 'contacts_export_'.time();

        if ($request->format == 'excel' || $request->format == 'xlsx') {
            // !! تصدير Excel !!
            return Excel::download(new ContactsExport($contactsToExport), $filename . '.xlsx');
        } 
        
        if ($request->format == 'pdf') {
            // !! تصدير PDF (مع رسالة الخطأ لتذكيرك) !!
            // يجب أن ننشئ الملف: resources/views/store_owner/reports/contacts_pdf.blade.php
            
            return redirect()->back()->with('error', 'لتفعيل PDF، يرجى تزويدي بملف واجهة PDF المخصص.');
        }

        return redirect()->back()->with('error', 'صيغة التصدير غير مدعومة.');
    }
}