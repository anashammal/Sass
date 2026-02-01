@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3 class="text-primary fw-bold"><i class="fas fa-utensils me-2"></i> إدارة المنيو (الوجبات والمكونات)</h3>
        <a href="{{ route('store.meals.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus me-1"></i> إضافة وجبة أو مكون خام
        </a>
    </div>

<div class="row g-3 mb-4 no-print">
    <div class="col">
        <div class="card kpi-card kpi-primary h-100">
            <div class="card-body d-flex align-items-center">
                <div class="kpi-icon-container me-3">
                    <i class="fas fa-utensils"></i>
                </div>
                <div>
                    <div class="kpi-label">إجمالي المنيو</div>
                    <div class="kpi-value english-num">{{ $prodStats['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card kpi-card kpi-warning h-100">
            <div class="card-body d-flex align-items-center">
                <div class="kpi-icon-container me-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="kpi-label">خامات أوشكت على النفاذ</div>
                    <div class="kpi-value english-num">{{ $prodStats['low_stock'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 no-print">
            <form id="filterForm" onsubmit="return false;">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <select name="per_page" class="form-select" onchange="performSearch()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i> التصنيف
                            </button>
                            <ul class="dropdown-menu p-2 shadow" style="max-height: 300px; overflow-y: auto;">
                                @foreach($categories as $cat)
                                    <li class="form-check">
                                        <input class="form-check-input cat-checkbox" type="checkbox" name="category_id[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" onchange="performSearch()">
                                        <label class="form-check-label" for="cat_{{ $cat->id }}">{{ $cat->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" name="search" class="form-control" placeholder="بحث في المنيو..." onkeyup="performSearch()">
                        </div>
                    </div>

                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0" id="mealsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3" style="width: 80px;">صورة</th>
                            <th>الاسم</th>
                            <th>النوع</th>
                            <th>التصنيف</th>
                            <th>وحدة التقديم</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>المخزون</th>
                            <th class="no-print">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="mealsTableBody">
                        @include('store_owner.meals.partials.table_rows')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let timeout = null;
    function performSearch(url = null) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const form = document.getElementById('filterForm');
            const params = new URLSearchParams(new FormData(form)).toString();
            const fetchUrl = url ? url : "{{ route('store.meals.index') }}?" + params;

            document.getElementById('mealsTableBody').style.opacity = '0.5';

            fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                document.getElementById('mealsTableBody').innerHTML = html;
                document.getElementById('mealsTableBody').style.opacity = '1';
            });
        }, 300);
    }
</script>
@endsection
