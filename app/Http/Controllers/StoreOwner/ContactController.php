<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * 1. عرض قائمة جهات الاتصال (مع بحث وفلترة ذكية)
     */
public function index(Request $request)
    {
        $storeId = Auth::user()->store->id;
        
        // 1. تجهيز الاستعلام الأساسي
        $query = Contact::where('store_id', $storeId);

        // 2. تطبيق البحث (كما كان عندك)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 3. تطبيق فلتر النوع
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type == 'supplier') {
                $query->whereIn('type', ['supplier', 'both']);
            } elseif ($type == 'customer') {
                $query->whereIn('type', ['customer', 'both']);
            }
        }

        // 4. تطبيق فلتر الرصيد
        if ($request->filled('balance_status')) {
            if ($request->balance_status == 'creditor') {
                $query->where('balance', '<', 0); // دائن (له)
            } elseif ($request->balance_status == 'debtor') {
                $query->where('balance', '>', 0); // مدين (عليه)
            }
        }

// 5. جلب البيانات
        $perPage = $request->input('per_page', 10);
        $contacts = $query->latest()->paginate($perPage)->withQueryString();

        // 🔥 4. الإحصائيات (تمت إضافة الزبائن والموردين) 🔥
        $stats = [
            'total' => \App\Models\Contact::where('store_id', $storeId)->count(),
            
            // 👇 هنا الإضافات الجديدة
            'customers_count' => \App\Models\Contact::where('store_id', $storeId)->whereIn('type', ['customer', 'both'])->count(),
            'suppliers_count' => \App\Models\Contact::where('store_id', $storeId)->whereIn('type', ['supplier', 'both'])->count(),
            
            'receivables' => \App\Models\Contact::where('store_id', $storeId)->where('balance', '>', 0)->sum('balance'),
            'payables' => \App\Models\Contact::where('store_id', $storeId)->where('balance', '<', 0)->sum('balance'),
        ];

        if ($request->ajax()) {
            return view('store_owner.contacts.partials.table_rows', compact('contacts'))->render();
        }

        return view('store_owner.contacts.index', compact('contacts', 'stats'));
    }
    
    public function create() { return view('store_owner.contacts.create'); }
    
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|array|min:1',
        ]);
        try {
            $storeId = Auth::user()->store->id;
            $types = $request->type;
            $finalType = 'customer';
            if (in_array('customer', $types) && in_array('supplier', $types)) $finalType = 'both';
            elseif (in_array('supplier', $types)) $finalType = 'supplier';

            $fullPhone = $request->phone;
            if ($request->filled('phone') && $request->filled('dial_code') && !str_starts_with($request->phone, '+')) {
                $fullPhone = $request->dial_code . $request->phone;
            }

            Contact::create([
                'store_id' => $storeId, 'contact_name' => $request->name, 'type' => $finalType,
                'company_name' => $request->company_name, 'phone' => $fullPhone, 'email' => $request->email,
                'address' => $request->address, 'tax_number' => $request->tax_number,
                'credit_limit' => $request->credit_limit ?? 0, 'balance' => $request->opening_balance ?? 0,
            ]);
            return redirect()->route('store.contacts.index')->with('success', 'تم الحفظ.');
        } catch (\Exception $e) { return back()->with('error', $e->getMessage())->withInput(); }
    }

    public function edit(Contact $contact) {
        if ($contact->store_id !== Auth::user()->store->id) return redirect()->route('store.contacts.index');
        return view('store_owner.contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact) {
        if ($contact->store_id !== Auth::user()->store->id) return back();
        $request->validate(['name' => 'required|string|max:255', 'type' => 'required|array|min:1']);
        try {
            $types = $request->type;
            $finalType = 'customer';
            if (in_array('customer', $types) && in_array('supplier', $types)) $finalType = 'both';
            elseif (in_array('supplier', $types)) $finalType = 'supplier';

            $contact->update([
                'contact_name' => $request->name, 'type' => $finalType,
                'company_name' => $request->company_name, 'phone' => $request->phone,
                'email' => $request->email, 'address' => $request->address,
                'tax_number' => $request->tax_number, 'credit_limit' => $request->credit_limit,
            ]);
            return redirect()->route('store.contacts.index')->with('success', 'تم التحديث.');
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function destroy(Contact $contact) {
        if ($contact->store_id !== Auth::user()->store->id) return back();
        $contact->delete();
        return redirect()->route('store.contacts.index')->with('success', 'تم الحذف.');
    }
}