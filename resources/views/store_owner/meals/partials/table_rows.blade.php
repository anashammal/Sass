@forelse($products as $product)
<tr class="align-middle">
    <td>
        <img src="{{ $product->image_url }}" class="rounded product-img-fit" width="50" height="50">
    </td>
    
    <td class="text-start">
        <div class="fw-bold text-dark">{{ $product->name }}</div>
        @if($product->units->where('is_base_unit', false)->count() > 0)
            <button class="btn btn-sm btn-link text-decoration-none p-0 mt-1" type="button" data-bs-toggle="collapse" data-bs-target="#units_{{ $product->id }}">
                <i class="fas fa-chevron-down me-1"></i> أحجام/وحدات تقديم ({{ $product->units->where('is_base_unit', false)->count() }})
            </button>
        @endif
    </td>
    
    <td>
        @if($product->product_type == 'meal')
            <span class="badge bg-danger">وجبة وجاهزة</span>
        @elseif($product->product_type == 'standard')
            <span class="badge bg-primary">منتج جاهز</span>
        @elseif($product->product_type == 'compound')
            <span class="badge bg-secondary">مكون مركب</span>
        @else
            <span class="badge bg-success">مادة خام</span>
        @endif
    </td>
    <td><span class="badge bg-info text-dark">{{ $product->category->name ?? '---' }}</span></td>
    <td>{{ $product->baseUnit->unit_name ?? '---' }}</td>
    
    @php
        $baseCost = $product->baseUnit ? (float)$product->baseUnit->cost_price : 0;
        $basePrice = $product->baseUnit->selling_price ?? 0;
        $currencyCode = $product->baseUnit->sellCurrency->code ?? $baseCurrency->code;
        
        $tax = $product->tax_percent ?? 0;
        $displayPriceWithTax = $basePrice * (1 + $tax / 100);
        $displayProfit = ($basePrice - $baseCost);
    @endphp

    <td class="text-danger fw-bold">
        {{ (float)number_format($baseCost, 2) }} {{ $product->baseUnit->purchaseCurrency->code ?? $baseCurrency->code }}
    </td>
    
    <td>
        @if($product->baseUnit && $product->baseUnit->is_sale)
            <span class="fw-bold text-success">{{ (float)number_format($displayPriceWithTax, 2) }} {{ $currencyCode }}</span>
        @else
            <span class="badge bg-danger">غير قابل للبيع</span>
        @endif
    </td>

    <td class="text-primary fw-bold">
        @php $profitPercent = $baseCost > 0 ? (($basePrice - $baseCost) / $baseCost) * 100 : 0; @endphp
        {{ (float)number_format($displayProfit, 2) }} {{ $currencyCode }}
        <small class="text-muted">({{ (float)number_format($profitPercent, 1) }}%)</small>
    </td>
    
    <td>
        @php $stock = (float)$product->current_stock; @endphp
        <span class="badge {{ $stock <= $product->alert_quantity ? 'bg-danger' : 'bg-success' }}">
            {{ $stock }}
        </span>
    </td>

    <td>
        @if($product->is_active)
            <span class="badge bg-success">فعال</span>
        @else
            <span class="badge bg-danger">معطل</span>
        @endif
    </td>
    
    <td class="no-print">
        <a href="{{ route('store.meals.edit', $product->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
        <form action="{{ route('store.meals.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف؟');">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
        </form>
    </td>
</tr>

{{-- أحجام التقديم الإضافية --}}
@if($product->units->where('is_base_unit', false)->count() > 0)
<tr class="collapse bg-light" id="units_{{ $product->id }}">
    <td colspan="11" class="p-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0 bg-white text-center">
                    <thead class="table-light">
                        <tr>
                            <th>الوحدة/الحجم</th>
                            <th>التحويل</th>
                            <th class="text-danger">التكلفة</th>
                            <th class="text-success">سعر البيع</th>
                            <th class="text-info">الربح</th>
                            <th class="text-primary">المخزون</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->units->where('is_base_unit', false) as $unit)
                        @php
                            $uCost = (float)$unit->cost_price;
                            $uPrice = (float)$unit->selling_price;
                            $uPurchaseCurrency = $unit->purchaseCurrency->code ?? $baseCurrency->code;
                            $uSellCurrency = $unit->sellCurrency->code ?? $baseCurrency->code;
                            
                            $uPriceWithTax = $uPrice * (1 + $tax / 100);
                            $uProfit = ($uPrice - $uCost);
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $unit->unit_name }}</td>
                            <td>{{ (float)$unit->conversion_factor }}</td>
                            <td class="text-danger fw-bold">
                                {{ (float)number_format($uCost, 2) }} {{ $uPurchaseCurrency }}
                            </td>
                            <td class="text-success fw-bold">
                                @if($unit->is_sale)
                                    {{ (float)number_format($uPriceWithTax, 2) }} {{ $uSellCurrency }}
                                @else
                                    <span class="badge bg-danger">غير قابل للبيع</span>
                                @endif
                            </td>
                            <td class="text-info fw-bold">
                                @php 
                                    $uProfitPercent = $uCost > 0 ? (($uPrice - $uCost) / $uCost) * 100 : 0; 
                                @endphp
                                {{ (float)number_format($uProfit, 2) }} {{ $uSellCurrency }}
                                <small class="text-muted">({{ (float)number_format($uProfitPercent, 1) }}%)</small>
                            </td>
                            <td class="text-primary fw-bold">{{ $product->getStockByUnit($unit->id) }}</td>
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
<tr><td colspan="11" class="text-center py-4 text-muted">لا يوجد وجبات أو مكونات مضافة</td></tr>
@endforelse

<tr><td colspan="11" class="p-0"><div class="d-flex justify-content-center py-2">{{ $products->links() }}</div></td></tr>
