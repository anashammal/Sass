@forelse($products as $product)
<tr class="align-middle">
    <td>
        {{-- عرض الصورة بشكل ذكي --}}
        <img src="{{ $product->image_url }}" class="rounded product-img-fit" width="50" height="50">
    </td>
    
    <td class="text-start">
        <div class="fw-bold text-dark">{{ $product->name_ar }}</div>
        @if($product->units->where('is_base_unit', false)->count() > 0)
            <button class="btn btn-sm btn-link text-decoration-none p-0 mt-1" type="button" data-bs-toggle="collapse" data-bs-target="#units_{{ $product->id }}">
                <i class="fas fa-chevron-down me-1"></i> 
                @if(Auth::user()->store->type == 'restaurant')
                    {{ __('أحجام/وحدات تقديم') }} ({{ $product->units->where('is_base_unit', false)->count() }})
                @else
                    {{ __('وحدات إضافية') }} ({{ $product->units->where('is_base_unit', false)->count() }})
                @endif
            </button>
        @endif
    </td>
    
    <td><span class="badge bg-light text-dark border font-monospace">{{ $product->baseUnit->barcode ?? '---' }}</span></td>
    <td><span class="badge bg-info text-dark">{{ $product->category->name ?? '---' }}</span></td>
    <td>{{ $product->baseUnit->unit_name ?? '---' }}</td>
    
    {{-- التكلفة للوحدة الأساسية --}}
    <td class="text-danger fw-bold">{{ $product->baseUnit ? (float)$product->baseUnit->cost_price : '0' }}</td>
    
    <td>
        @php $price = $product->baseUnit->selling_price ?? 0; @endphp
        @if($product->baseUnit && $product->baseUnit->is_sale)
            <div class="d-flex flex-column">
                @php 
                    $tax = $product->tax_percent ?? 0;
                    $priceWithTax = $price * (1 + $tax / 100);
                @endphp
                <span class="fw-bold text-success">{{ (float)number_format($priceWithTax, 2) }}</span>
                @if($tax > 0)
                    <small class="text-muted" style="font-size: 10px;">({{ __('شامل') }} {{ (float)$tax }}%)</small>
                @endif
            </div>
        @else
            <span class="badge bg-danger">{{ __('غير قابل للبيع') }}</span>
        @endif
    </td>

    <td class="text-primary fw-bold">
        @php 
            $cost = $product->baseUnit ? (float)$product->baseUnit->cost_price : 0;
            $profit = $price - $cost;
            $profitPercent = $cost > 0 ? ($profit / $cost) * 100 : 0;
        @endphp
        {{ (float)number_format($profit, 2) }}
        <small class="text-muted">({{ (float)number_format($profitPercent, 1) }}%)</small>
    </td>
    
    {{-- المخزون الكلي (للوحدة الأساسية) --}}
    <td>
        @php $stock = (float)$product->current_stock; @endphp
        <span class="badge {{ $stock <= $product->alert_quantity ? 'bg-danger' : 'bg-success' }}">
            {{ $stock }}
        </span>
    </td>

    <td>
        @if($product->is_active)
            <span class="badge bg-success">{{ __('فعال') }}</span>
        @else
            <span class="badge bg-danger">{{ __('معطل') }}</span>
        @endif
    </td>

    
    <td class="no-print">
        <a href="{{ route('store.products.edit', $product->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
        <form action="{{ route('store.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('حذف؟') }}');">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
        </form>
    </td>
</tr>

{{-- جدول الوحدات الإضافية --}}
@if($product->units->where('is_base_unit', false)->count() > 0)
<tr class="collapse bg-light" id="units_{{ $product->id }}">
    <td colspan="11" class="p-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-1 border-bottom">
                <small class="fw-bold text-primary">{{ __('تفاصيل الوحدات الإضافية') }}</small>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0 bg-white text-center">
                    <thead class="table-light">
                        <tr>
                            <th width="60">{{ __('صورة') }}</th>
                            <th>{{ __('الوحدة') }}</th>
                            <th>{{ __('التحويل') }}</th>
                            <th>{{ __('الباركود') }}</th>
                            <th class="text-danger">{{ __('التكلفة') }}</th> {{-- عمود جديد --}}
                            <th>{{ __('سعر البيع (شامل الضريبة)') }}</th>
                            <th class="text-info">{{ __('الربح') }}</th>
                            <th class="text-primary">{{ __('المخزون المتوفر') }}</th> {{-- عمود جديد --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->units->where('is_base_unit', false) as $unit)
                        <tr>
                            <td class="text-center">
                                <img src="{{ $unit->image }}" width="40" height="40" class="rounded product-img-fit">
                            </td>
                            <td class="fw-bold">{{ $unit->unit_name }}</td>
                            <td>{{ (float)$unit->conversion_factor }}</td>
                            <td class="font-monospace">{{ $unit->barcode ?? '---' }}</td>
                            
                            <td class="text-danger fw-bold">{{ (float)$unit->cost_price }}</td>
                            
<td class="text-success fw-bold">
    @if($unit->is_sale)
        @php $uPriceTax = $unit->selling_price * (1 + $product->tax_percent / 100); @endphp
        {{ number_format($uPriceTax, 2) }}
    @else
        <span class="badge bg-danger">{{ __('غير قابل للبيع') }}</span>
    @endif
</td>

<td class="text-info fw-bold">
    @php 
        $uProfit = $unit->selling_price - $unit->cost_price;
        $uProfitPercent = $unit->cost_price > 0 ? ($uProfit / $unit->cost_price) * 100 : 0;
    @endphp
    {{ number_format($uProfit, 2) }}
    <small class="text-muted">({{ number_format($uProfitPercent, 1) }}%)</small>
</td>

                            {{-- عرض المخزون بهذه الوحدة --}}
                            <td class="text-primary fw-bold">
                                {{ $product->getStockByUnit($unit->id) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </td>
</tr>
@endif

@empty
<tr><td colspan="11" class="text-center py-4 text-muted">{{ __('لا توجد بيانات') }}</td></tr>
@endforelse

<tr><td colspan="11" class="p-0"><div class="d-flex justify-content-center py-2">{{ $products->links() }}</div></td></tr>