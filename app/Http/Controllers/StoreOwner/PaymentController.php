<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Payment;
use App\Models\Contact;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * عرض كشف حساب لجهة اتصال معينة
     */
    public function ledger(Request $request, $contactId)
    {
        $storeId = Auth::user()->store->id;
        $contact = Contact::where('store_id', $storeId)->findOrFail($contactId);

        // 1. جلب فواتير البيع (تنقص الرصيد)
        $sales = Sale::where('contact_id', $contactId)
            ->select('id', 'created_at as date', DB::raw("'sale' as type"), 'total as amount', DB::raw("CONCAT('#', id) as reference"))
            ->get();

        // 2. جلب فواتير الشراء (تزيد الرصيد)
        $purchases = Purchase::where('supplier_id', $contactId)
            ->select('id', 'invoice_date as date', DB::raw("'purchase' as type"), 'grand_total as amount', 'invoice_number as reference')
            ->get();

        // 3. جلب جميع الدفعات المرتبطة بجهة الاتصال (سواء مستقلة أو لفاتورة)
        // ملاحظة: نحتاج جلب الدفعات المرتبطة بـ sale_id أو purchase_id التي تخص هذا العميل/المورد أيضاً
        
        $payments = Payment::where(function($q) use ($contactId) {
                $q->where('contact_id', $contactId)
                  ->orWhereIn('sale_id', Sale::where('contact_id', $contactId)->pluck('id'))
                  ->orWhereIn('purchase_id', Purchase::where('supplier_id', $contactId)->pluck('id'));
            })
            ->select('id', 'payment_date as date', 'created_at', DB::raw("'payment' as type"), 'amount', 'notes as reference', 'sale_id', 'purchase_id')
            ->get();

        // توحيد الحركات في مصفوفة واحدة مع تحديد "تأثير" كل حركة (Effect)
        $merged = collect();

        foreach ($sales as $s) {
            $merged->push((object)[
                'date' => $s->date,
                'type' => 'sale',
                'reference' => 'فاتورة مبيعات ' . ($s->reference ?? '#' . $s->id),
                'amount' => $s->amount,
                'effect' => -$s->amount, // المبيعات تنقص الرصيد (نحو السالب)
            ]);
        }

        foreach ($purchases as $p) {
            $merged->push((object)[
                'date' => $p->date,
                'type' => 'purchase',
                'reference' => 'فاتورة مشتريات ' . ($p->reference ? '#' . $p->reference : '#' . $p->id),
                'amount' => $p->amount,
                'effect' => $p->amount, // المشتريات تزيد الرصيد (نحو الموجب)
            ]);
        }

        foreach ($payments as $pay) {
            // تحديد تأثير الدفعة: 
            // إذا كانت مرتبطة ببيع (sale_id) -> فهي 'قبض' من عميل -> تزيد الرصيد
            // إذا كانت مرتبطة بشراء (purchase_id) -> فهي 'صرف' لمورد -> تنقص الرصيد
            // إذا كانت مستقلة -> نعتمد على كونها 'قبض' أو 'صرف'؟
            // لجعلها بسيطة: سنفترض أن الدفعة المستقلة تزيد الرصيد (قبض) إلا لو كان الموظف اختار 'صرف'
            // سأفترض حالياً أن الدفعات للبيع تزيد، وللشراء تنقص.
            
            $payEffect = $pay->amount;
            $payRef = 'دفعة مالية';
            if ($pay->sale_id) {
                $payEffect = $pay->amount; 
                $payRef = 'دفعة مبيعات #' . $pay->sale_id;
            } elseif ($pay->purchase_id) {
                $payEffect = -$pay->amount;
                $payRef = 'دفعة مشتريات #' . $pay->purchase_id;
            } else {
                // دفعات يدوية: سنحتاج لعمول يحدد هل هي دخل أم خرج. 
                // سأعتبر الموجب (إضافة للرصيد = قبض) والسالب (خصم من الرصيد = دفع)
                // في Controller::store، نحن نستخدم increment/decrement.
                // سنحفظ إشارة المبلغ في عمود النوع أو نجعله مسجلاً بالكنترولر.
                // حالياً سأفترض القبض (توفير دفعة من عميل) هو الموجب.
            }

            $merged->push((object)[
                'date' => $pay->date ?? $pay->created_at,
                'type' => 'payment',
                'reference' => $payRef . ($pay->reference ? ' (' . $pay->reference . ')' : ''),
                'amount' => $pay->amount,
                'effect' => $payEffect,
            ]);
        }

        $ledger = $merged->sortBy('date');

        return view('store_owner.payments.ledger', compact('contact', 'ledger'));
    }

    /**
     * حفظ دفعة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,bank',
            'type' => 'required|in:receive,pay', // receive from customer, pay to supplier
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
            $storeId = Auth::user()->store->id;
            $contact = Contact::where('store_id', $storeId)->findOrFail($request->contact_id);

            // تفعيل التغيير في الرصيد
            // - إذا استلمنا من عميل (receive): الرصيد يزداد (يقترب من الصفر إذا كان سالباً)
            // - إذا دفعنا لمورد (pay): الرصيد ينقص (يقترب من الصفر إذا كان موجباً)
            $amount = $request->amount;
            if ($request->type == 'pay') {
                $contact->decrement('balance', $amount);
            } else {
                $contact->increment('balance', $amount);
            }

            Payment::create([
                'store_id' => $storeId,
                'contact_id' => $request->contact_id,
                'method' => $request->method,
                'amount' => $amount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'attachment' => $request->hasFile('attachment') ? $request->file('attachment')->store('payments', 'public') : null,
            ]);

            DB::commit();
            return back()->with('success', 'تم تسجيل الدفعة وتحديث الرصيد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }
}
