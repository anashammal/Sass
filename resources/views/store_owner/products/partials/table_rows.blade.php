@forelse($products as $product)
<tr class="align-middle">
    <td>
        {{-- عرض الصورة بشكل ذكي --}}
        <img src="{{ $product->image_url }}" class="rounded product-img-fit" width="50" height="50">
    </td>
    
    <td class="text-start">
        <div class="fw-bold text-dark">{{ $product->name }}</div>
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
    <td class="text-danger fw-bold">
        @if($product->baseUnit)
            @php 
                $u = $product->baseUnit;
                $cost = (float)$u->cost_price;
                $pCurrency = $u->purchaseCurrency;
                $pSymbol = $pCurrency ? $pCurrency->symbol : $globalCurrencySymbol;
                
                // حساب المعادل للعملة الأساسية إذا كانت مختلفة
                $baseCost = $cost;
                $pRate = 1;
                if($pCurrency && $pCurrency->id != $store->base_currency_id) {
                    $pRate = $u->purchase_exchange_rate ?? ($store->acceptedCurrencies->find($pCurrency->id)->pivot->custom_rate ?? 1);
                    $baseCost = $cost * $pRate;
                }
@endphp
            <div class="d-flex flex-column">
                <span>{{ $cost }} <small class="text-muted">{{ $pSymbol }}</small></span>
                @if($baseCost != $cost)
                    <small class="text-muted" style="font-size: 0.7rem;">(≈ {{ number_format($baseCost, 2) }} {{ $globalCurrencySymbol }})</small>
                    <small class="text-muted" style="font-size: 0.65rem;">@ 1 {{ $pCurrency->code }} = {{ (float)$pRate }} {{ $globalCurrencySymbol }}</small>
                @endif
            </div>
        @else
            0 <small class="text-muted">{{ $globalCurrencySymbol }}</small>
        @endif
    </td>
    
    <td>
        @php $price = $product->baseUnit->selling_price ?? 0; @endphp
        @if($product->baseUnit && $product->baseUnit->is_sale)
            @php 
                $u = $product->baseUnit;
                $sPrice = (float)$u->selling_price;
                $sCurrency = $u->sellCurrency;
                $sSymbol = $sCurrency ? $sCurrency->symbol : $globalCurrencySymbol;
                
                $tax = $product->tax_percent ?? 0;
                $priceWithTax = $sPrice * (1 + $tax / 100);

                // حساب المعادل للعملة الأساسية
                $basePriceTax = $priceWithTax;
                $sRate = 1;
                if($sCurrency && $sCurrency->id != $store->base_currency_id) {
                    $sRate = $u->sell_exchange_rate ?? ($store->acceptedCurrencies->find($sCurrency->id)->pivot->custom_rate ?? 1);
                    $basePriceTax = $priceWithTax * $sRate;
                }
@endphp
            <div class="d-flex flex-column">
                <span class="fw-bold text-success">{{ number_format($priceWithTax, 2) }} <small class="text-muted">{{ $sSymbol }}</small></span>
                @if($basePriceTax != $priceWithTax)
                    <small class="text-muted" style="font-size: 0.7rem;">(≈ {{ number_format($basePriceTax, 2) }} {{ $globalCurrencySymbol }})</small>
                    <small class="text-muted" style="font-size: 0.65rem;">@ 1 {{ $sCurrency->code }} = {{ (float)$sRate }} {{ $globalCurrencySymbol }}</small>
                @endif
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
            // الربح بالعملة الأساسية لتوحيد المقارنة
            $u = $product->baseUnit;
            $cost = (float)($u->cost_price ?? 0);
            $sell = (float)($u->selling_price ?? 0);
            
            // تحويل للعملة الأساسية
            $baseCost = $cost;
            if($u->purchaseCurrency && $u->purchaseCurrency->id != $store->base_currency_id) {
                $pRate = $u->purchase_exchange_rate ?? ($store->acceptedCurrencies->find($u->purchase_price_currency_id)->pivot->custom_rate ?? 1);
                $baseCost = $cost * $pRate;
            }
            
            $baseSell = $sell;
            if($u->sellCurrency && $u->sellCurrency->id != $store->base_currency_id) {
                $sRate = $u->sell_exchange_rate ?? ($store->acceptedCurrencies->find($u->sell_price_currency_id)->pivot->custom_rate ?? 1);
                $baseSell = $sell * $sRate;
            }

            $profit = $baseSell - $baseCost;
            $profitPercent = $baseCost > 0 ? ($profit / $baseCost) * 100 : 0;
        @endphp
        {{ number_format($profit, 2) }} <small class="text-muted">{{ $globalCurrencySymbol }}</small>
        <small class="text-muted">({{ number_format($profitPercent, 1) }}%)</small>
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
                            
                            <td class="text-danger fw-bold">
                                @php 
                                    $uCost = (float)$unit->cost_price;
                                    $uPCurrency = $unit->purchaseCurrency;
                                    $uPSymbol = $uPCurrency ? $uPCurrency->symbol : $globalCurrencySymbol;
                                    
                                    $uBaseCost = $uCost;
                                    $uPRate = 1;
                                    if($uPCurrency && $uPCurrency->id != $store->base_currency_id) {
                                        $uPRate = $unit->purchase_exchange_rate ?? ($store->acceptedCurrencies->find($uPCurrency->id)->pivot->custom_rate ?? 1);
                                        $uBaseCost = $uCost * $uPRate;
                                    }
@endphp
                                <div class="d-flex flex-column">
                                    <span>{{ $uCost }} <small class="text-muted">{{ $uPSymbol }}</small></span>
                                    @if($uBaseCost != $uCost)
                                        <small class="text-muted" style="font-size: 0.7rem;">(≈ {{ number_format($uBaseCost, 2) }} {{ $globalCurrencySymbol }})</small>
                                        <small class="text-muted" style="font-size: 0.65rem;">@ 1 {{ $uPCurrency->code }} = {{ (float)$uPRate }} {{ $globalCurrencySymbol }}</small>
                                    @endif
                                </div>
                            </td>
                            
<td class="text-success fw-bold">
    @if($unit->is_sale)
        @php 
            $uSPrice = (float)$unit->selling_price;
            $uSCurrency = $unit->sellCurrency;
            $uSSymbol = $uSCurrency ? $uSCurrency->symbol : $globalCurrencySymbol;
            
            $uPriceTax = $uSPrice * (1 + $product->tax_percent / 100);
            
            $uBasePriceTax = $uPriceTax;
            $uSRate = 1;
            if($uSCurrency && $uSCurrency->id != $store->base_currency_id) {
                $uSRate = $unit->sell_exchange_rate ?? ($store->acceptedCurrencies->find($uSCurrency->id)->pivot->custom_rate ?? 1);
                $uBasePriceTax = $uPriceTax * $uSRate;
            }
@endphp
        <div class="d-flex flex-column">
            <span>{{ number_format($uPriceTax, 2) }} <small class="text-muted">{{ $uSSymbol }}</small></span>
            @if($uBasePriceTax != $uPriceTax)
                <small class="text-muted" style="font-size: 0.7rem;">(≈ {{ number_format($uBasePriceTax, 2) }} {{ $globalCurrencySymbol }})</small>
                <small class="text-muted" style="font-size: 0.65rem;">@ 1 {{ $uSCurrency->code }} = {{ (float)$uSRate }} {{ $globalCurrencySymbol }}</small>
            @endif
        </div>
    @else
        <span class="badge bg-danger">{{ __('غير قابل للبيع') }}</span>
    @endif
</td>

<td class="text-info fw-bold">
    @php 
        $uSell = (float)$unit->selling_price;
        $uCost = (float)$unit->cost_price;

        // تحويل للعملة الأساسية لحساب الربح
        $uBaseSell = $uSell;
        if($unit->sellCurrency && $unit->sellCurrency->id != $store->base_currency_id) {
            $uSRate = $unit->sell_exchange_rate ?? ($store->acceptedCurrencies->find($unit->sell_price_currency_id)->pivot->custom_rate ?? 1);
            $uBaseSell = $uSell * $uSRate;
        }

        $uBaseCost = $uCost;
        if($unit->purchaseCurrency && $unit->purchaseCurrency->id != $store->base_currency_id) {
            $uPRate = $unit->purchase_exchange_rate ?? ($store->acceptedCurrencies->find($unit->purchase_price_currency_id)->pivot->custom_rate ?? 1);
            $uBaseCost = $uCost * $uPRate;
        }

        $uProfit = $uBaseSell - $uBaseCost;
        $uProfitPercent = $uBaseCost > 0 ? ($uProfit / $uBaseCost) * 100 : 0;
    @endphp
    {{ number_format($uProfit, 2) }} <small class="text-muted">{{ $globalCurrencySymbol }}</small>
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