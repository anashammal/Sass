@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="fas fa-box-open me-2"></i> إدارة المنتجات</h3>
        <a href="{{ route('store.products.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus me-1"></i> إضافة منتج جديد
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">المنتج</th>
                            <th>التصنيف</th>
                            <th>الوحدة الأساسية</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>المخزون</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            {{-- صورة افتراضية --}}
                                            <img src="{{ $product->getFirstMediaUrl('products') ?: asset('images/default-product.png') }}" 
                                                 class="rounded" width="40" height="40" style="object-fit: cover;" alt="...">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $product->name_ar }}</div>
                                            <div class="small text-muted">{{ $product->baseUnit->barcode ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $product->category->name ?? 'غير مصنف' }}
                                    </span>
                                </td>
                                <td>{{ $product->baseUnit->unit_name ?? '-' }}</td>
                                <td>{{ number_format($product->baseUnit->cost_price ?? 0, 2) }}</td>
                                <td class="fw-bold text-success">{{ number_format($product->baseUnit->selling_price ?? 0, 2) }}</td>
                                <td>
                                    {{-- سنبرمج المخزون لاحقاً --}}
                                    <span class="badge bg-secondary">0</span>
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">معطل</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="#" class="btn btn-outline-primary" title="تعديل"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-outline-danger" title="حذف"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3"></i>
                                        <p>لا توجد منتجات مضافة حتى الآن.</p>
                                        <a href="{{ route('store.products.create') }}" class="btn btn-sm btn-primary">أضف منتجك الأول</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white py-3">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection