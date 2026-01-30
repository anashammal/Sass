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
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    /**
     * عرض كشف حساب لجهة اتصال معينة
     */
    public function ledger(Request $request, $contactId)
    {
        $data = $this->getLedgerData($contactId);
        $contact = $data['contact'];
        $ledger = $data['ledger'];

        return view('store_owner.payments.ledger', compact('contact', 'ledger'));
    }

    /**
     * تصدير كشف الحساب كملف PDF
     */
    public function ledgerPdf($contactId)
    {
        $data = $this->getLedgerData($contactId);
        $contact = $data['contact'];
        $ledger = $data['ledger'];
        $store = Auth::user()->store;

        $pdf = Pdf::loadView('store_owner.payments.pdf_ledger', compact('contact', 'ledger', 'store'));
        
        // تحسين دعم اللغة العربية: تعيين اسم ملف آمن
        $safeName = "ledger_" . $contact->id . ".pdf";
        return $pdf->download($safeName);
    }

    private function getLedgerData($contactId)
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

        // 3. جلب الدفعات
        $payments = Payment::where(function($q) use ($contactId) {
                $q->where('contact_id', $contactId)
                  ->orWhereIn('sale_id', Sale::where('contact_id', $contactId)->pluck('id'))
                  ->orWhereIn('purchase_id', Purchase::where('supplier_id', $contactId)->pluck('id'));
            })
            ->select('id', 'payment_date as date', 'created_at', DB::raw("'payment' as type"), 'amount', 'notes as reference', 'sale_id', 'purchase_id')
            ->get();

        $merged = collect();

        foreach ($sales as $s) {
            $merged->push((object)[
                'id' => $s->id,
                'date' => $s->date,
                'type' => 'sale',
                'reference_id' => $s->id,
                'reference' => 'فاتورة مبيعات ' . ($s->reference ?? '#' . $s->id),
                'amount' => $s->amount,
                'effect' => -$s->amount,
            ]);
        }

        foreach ($purchases as $p) {
            $merged->push((object)[
                'id' => $p->id,
                'date' => $p->date,
                'type' => 'purchase',
                'reference_id' => $p->id,
                'reference' => 'فاتورة مشتريات ' . ($p->reference ? '#' . $p->reference : '#' . $p->id),
                'amount' => $p->amount,
                'effect' => $p->amount,
            ]);
        }

        foreach ($payments as $pay) {
            $payEffect = $pay->amount;
            $payRef = 'دفعة مالية';
            if ($pay->sale_id) {
                $payEffect = $pay->amount; 
                $payRef = 'دفعة مبيعات #' . $pay->sale_id;
            } elseif ($pay->purchase_id) {
                $payEffect = -$pay->amount;
                $payRef = 'دفعة مشتريات #' . $pay->purchase_id;
            }

            $merged->push((object)[
                'id' => $pay->id,
                'date' => $pay->date ?? $pay->created_at,
                'type' => 'payment',
                'reference_id' => $pay->sale_id ?? ($pay->purchase_id ?? $pay->id),
                'reference' => $payRef . ($pay->reference ? ' (' . $pay->reference . ')' : ''),
                'amount' => $pay->amount,
                'effect' => $payEffect,
            ]);
        }

        $ledger = $merged->sortBy('date');

        return ['contact' => $contact, 'ledger' => $ledger];
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
