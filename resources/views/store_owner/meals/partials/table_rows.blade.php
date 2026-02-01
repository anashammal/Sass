@forelse($products as $product)
<tr class="align-middle">
    <td>
        @if($product->getFirstMediaUrl('products', 'thumb'))
            <img src="{{ $product->getFirstMediaUrl('products', 'thumb') }}" class="rounded product-img-fit" width="50" height="50">
        @else
            <img src="{{ asset('images/default-product.png') }}" class="rounded product-img-fit" width="50" height="50">
        @endif
    </td>
    
    <td class="text-start">
        <div class="fw-bold text-dark">{{ $product->name_ar }}</div>
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
    
    <td class="text-danger fw-bold">{{ $product->baseUnit ? (float)$product->baseUnit->cost_price : '0' }}</td>
    
    <td>
        @php 
            $price = $product->baseUnit->selling_price ?? 0;
            $tax = $product->tax_percent ?? 0;
            $priceWithTax = $price * (1 + $tax / 100);
        @endphp
        <span class="fw-bold text-success">{{ (float)number_format($priceWithTax, 2) }}</span>
    </td>
    
    <td>
        @php $stock = (float)$product->current_stock; @endphp
        <span class="badge {{ $stock <= $product->alert_quantity ? 'bg-danger' : 'bg-success' }}">
            {{ $stock }}
        </span>
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
    <td colspan="9" class="p-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0 bg-white text-center">
                    <thead class="table-light">
                        <tr>
                            <th>الوحدة/الحجم</th>
                            <th>التحويل</th>
                            <th class="text-danger">التكلفة</th>
                            <th class="text-success">سعر البيع</th>
                            <th class="text-primary">المخزون</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->units->where('is_base_unit', false) as $unit)
                        <tr>
                            <td class="fw-bold">{{ $unit->unit_name }}</td>
                            <td>{{ (float)$unit->conversion_factor }}</td>
                            <td class="text-danger fw-bold">{{ (float)$unit->cost_price }}</td>
                            <td class="text-success fw-bold">
                                @php $uPriceTax = $unit->selling_price * (1 + $product->tax_percent / 100); @endphp
                                {{ number_format($uPriceTax, 2) }}
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
<tr><td colspan="9" class="text-center py-4 text-muted">لا يوجد وجبات أو مكونات مضافة</td></tr>
@endforelse

<tr><td colspan="9" class="p-0"><div class="d-flex justify-content-center py-2">{{ $products->links() }}</div></td></tr>
