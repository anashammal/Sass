<div class="row">
    <div class="col-md-6">
        <h5>رقم الفاتورة: #{{ $sale->id }}</h5>
        <p>العميل: <strong>{{ $sale->contact->contact_name ?? 'عميل نقدي' }}</strong></p>
        <p>التاريخ: {{ $sale->created_at->format('Y-m-d h:i A') }}</p>
    </div>
    <div class="col-md-6 text-start">
        @php
            $badgeClass = 'danger';
            if($sale->status == 'paid') $badgeClass = 'success';
            elseif($sale->status == 'partial') $badgeClass = 'warning';
        @endphp
        <span class="badge bg-{{ $badgeClass }}">
            {{ $sale->status == 'paid' ? 'مدفوعة' : ($sale->status == 'partial' ? 'مدفوعة جزئياً' : 'غير مدفوعة') }}
        </span>
    </div>
</div>

<hr>

<div class="table-responsive">
    <table class="table table-bordered table-sm text-center">
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
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product->name_ar ?? 'منتج محذوف' }}</td>
                <td>{{ $item->unit->unit_name ?? 'قطعة' }}</td>
                <td>{{ (float)$item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="4" class="text-end">الإجمالي</td>
                <td>{{ number_format($sale->total, 2) }}</td>
            </tr>
            @if($sale->discount > 0)
            <tr>
                <td colspan="4" class="text-end text-danger">الخصم</td>
                <td class="text-danger">-{{ number_format($sale->discount, 2) }}</td>
            </tr>
            @endif
            <tr class="table-light fw-bold">
                <td colspan="4" class="text-end">الصافي</td>
                <td>{{ number_format($sale->total - $sale->discount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<hr>

<div class="d-flex justify-content-between no-print">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
    
    <div>
        {{-- هنا يمكن إضافة زر طباعة لاحقاً --}}
        <a href="{{ route('store.pos.index') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-shopping-cart me-1"></i> اذهب لنقطة البيع
        </a>
    </div>
</div>
