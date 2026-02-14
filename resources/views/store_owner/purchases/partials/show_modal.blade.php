<div class="row">
    <div class="col-md-6">
        <h5>رقم الفاتورة: {{ $purchase->invoice_number }}</h5>
        <p>المورد: <strong>{{ $purchase->supplier->contact_name ?? 'غير محدد' }}</strong></p>
        <p>التاريخ: {{ $purchase->invoice_date }}</p>
    </div>
    <div class="col-md-6 text-start">
        <span class="badge bg-{{ $purchase->payment_status == 'paid' ? 'success' : 'warning' }}">
            {{ $purchase->payment_status }}
        </span>
    </div>
</div>

<hr>

<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>المنتج</th>
                <th>الوحدة</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'منتج محذوف' }}</td>
                <td>{{ $item->unit->unit_name ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->total_cost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end"><strong>الإجمالي النهائي</strong></td>
                <td><strong>{{ number_format($purchase->grand_total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<hr>

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