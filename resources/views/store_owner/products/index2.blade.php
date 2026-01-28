@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i> إدارة المنتجات</h4>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary me-2"><i class="fas fa-print me-1"></i> طباعة الجرد</button>
            <a href="{{ route('store.products.create') }}" class="btn btn-success"><i class="fas fa-plus-circle me-1"></i> منتج جديد</a>
        </div>
    </div>

    {{-- بطاقات الملخص --}}
    <div class="row g-3 mb-4 no-print">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-3 me-3"><i class="fas fa-box-open fa-lg"></i></div>
                    <div>
                        <h6 class="text-muted mb-1">إجمالي المنتجات</h6>
                        <h4 class="fw-bold mb-0">{{ $productsStats['total_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning text-white rounded-circle p-3 me-3"><i class="fas fa-exclamation-triangle fa-lg"></i></div>
                    <div>
                        <h6 class="text-muted mb-1">نواقص المخزون</h6>
                        <h4 class="fw-bold mb-0 text-warning">{{ $productsStats['low_stock'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success text-white rounded-circle p-3 me-3"><i class="fas fa-coins fa-lg"></i></div>
                    <div>
                        <h6 class="text-muted mb-1">قيمة المخزون (شراء)</h6>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($productsStats['total_value'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الفلاتر --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body p-4">
            <form action="{{ route('store.products.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">بحث</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="اسم المنتج، الباركود..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">التصنيف</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- الكل --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">حالة المخزون</label>
                        <select name="stock_status" class="form-select">
                            <option value="">-- الكل --</option>
                            <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>متوفر</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>منخفض</option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>نافذ</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> تصفية</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- الجدول --}}
    <div class="card border-0 shadow-sm">
        <div class="d-none d-print-block p-4 text-center border-bottom">
            <h3>تقرير جرد المخزون</h3>
            <p>تاريخ الطباعة: {{ date('Y-m-d H:i') }}</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th>صورة</th>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>الكمية</th>
                            <th class="no-print">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <img src="{{ $product->image_url ?? asset('images/default-product.png') }}" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                                </td>
                                <td>
                                    <span class="fw-bold d-block">{{ $product->name_ar }}</span>
                                    <span class="text-muted small font-monospace">{{ $product->sku ?? $product->baseUnit->barcode }}</span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $product->category->name_ar ?? '-' }}</span></td>
                                <td>{{ number_format($product->baseUnit->cost_price ?? 0, 2) }}</td>
                                <td class="fw-bold text-success">{{ number_format($product->baseUnit->selling_price ?? 0, 2) }}</td>
                                <td>
                                    @if($product->current_stock <= 0)
                                        <span class="badge bg-danger">نافذ (0)</span>
                                    @elseif($product->current_stock <= $product->min_stock)
                                        <span class="badge bg-warning text-dark">{{ $product->current_stock }} (منخفض)</span>
                                    @else
                                        <span class="badge bg-success">{{ $product->current_stock }}</span>
                                    @endif
                                </td>
                                <td class="no-print">
                                    <div class="btn-group">
                                        <a href="{{ route('store.products.edit', $product->id) }}" class="btn btn-sm btn-light text-primary"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">لا توجد منتجات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 no-print">
            {{ $products->links() }}
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, .navbar, .sidebar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .d-print-block { display: block !important; }
    }
</style>
@endsection