@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between no-print">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
    
    <div>
        <button type="button" class="btn btn-success" onclick="printInvoiceContent()">
            <i class="fa fa-print"></i> طباعة
        </button>

        <a href="{{ route('store.purchases.edit', $purchase->id) }}" class="btn btn-warning">
            <i class="fa fa-edit"></i> تعديل
        </a>

        <form action="{{ route('store.purchases.destroy', $purchase->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-trash"></i> حذف
            </button>
        </form>
    </div>
</div>

    {{-- بطاقة تفاصيل الفاتورة --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0 fw-bold text-dark">معلومات أساسية</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="text-muted small">المورد</label>
                    <h5 class="fw-bold text-primary">{{ $purchase->supplier->company_name ?? $purchase->supplier->contact_name }}</h5>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">تاريخ الفاتورة</label>
                    <h5 class="fw-bold">{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('Y-m-d h:i A') }}</h5>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">حالة الفاتورة</label>
                    <div>
                        @if($purchase->status == 'draft')
                            <span class="badge bg-warning text-dark fs-6">مسودة (غير معتمدة)</span>
                        @else
                            <span class="badge bg-success fs-6">معتمدة</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">حالة الدفع</label>
                    <div>
                        @if($purchase->payment_status == 'paid')
                            <span class="badge bg-success">خالص</span>
                        @elseif($purchase->payment_status == 'partial')
                            <span class="badge bg-warning text-dark">جزئي</span>
                        @else
                            <span class="badge bg-danger">غير مدفوع</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- جدول المنتجات --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 25%">المنتج</th>
                            <th style="width: 15%">الوحدة</th>
                            <th style="width: 10%">الكمية</th>
                            <th style="width: 15%">سعر الإفرادي</th>
                            <th style="width: 15%">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-start fw-bold">{{ $item->product->name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->unit->unit_name ?? '-' }}</span>
                            </td>
                            <td class="english-num fw-bold fs-5">{{ $item->quantity }}</td>
                            <td class="english-num">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="english-num fw-bold">{{ number_format($item->total_cost, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">المجموع الفرعي:</td>
                            <td class="english-num fw-bold">{{ number_format($purchase->sub_total, 2) }}</td>
                        </tr>
                        @if($purchase->discount_amount > 0)
                        <tr>
                            <td colspan="5" class="text-end fw-bold text-danger">خصم إضافي:</td>
                            <td class="english-num fw-bold text-danger">-{{ number_format($purchase->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="bg-primary bg-opacity-10">
                            <td colspan="5" class="text-end fw-bold fs-5 text-primary">الصافي النهائي:</td>
                            <td class="english-num fw-bold fs-4 text-primary">{{ number_format($purchase->grand_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">المدفوع:</td>
                            <td class="english-num fw-bold text-success">{{ number_format($purchase->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">المتبقي:</td>
                            <td class="english-num fw-bold {{ ($purchase->grand_total - $purchase->paid_amount) > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($purchase->grand_total - $purchase->paid_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    @if($purchase->notes)
    <div class="alert alert-secondary mt-4">
        <strong>ملاحظات:</strong> {{ $purchase->notes }}
    </div>
    @endif
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; shadow: none !important; }
        .badge { border: 1px solid #000; color: #000 !important; background: none !important; }
    }
</style>
@endsection