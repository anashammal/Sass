@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3 class="text-primary fw-bold"><i class="fas fa-utensils me-2"></i> إدارة المنيو (الوجبات والمكونات)</h3>
        <div class="d-flex gap-2">
            <form action="{{ route('store.meals.recalculate_all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-sync-alt me-1"></i> تحديث التكاليف
                </button>
            </form>
            <a href="{{ route('store.meals.create') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-plus me-1"></i> إضافة وجبة أو مكون خام
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4 no-print">
        <div class="col">
            <div class="card kpi-card kpi-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3"><i class="fas fa-boxes"></i></div>
                    <div><div class="kpi-label">الإجمالي</div><div class="kpi-value english-num">{{ $prodStats['total'] ?? 0 }}</div></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card kpi-success h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3"><i class="fas fa-hamburger"></i></div>
                    <div><div class="kpi-label">وجبات جاهزة</div><div class="kpi-value english-num">{{ $prodStats['meals'] ?? 0 }}</div></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card kpi-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3"><i class="fas fa-box"></i></div>
                    <div><div class="kpi-label">منتجات جاهزة</div><div class="kpi-value english-num">{{ $prodStats['standards'] ?? 0 }}</div></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card kpi-info h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3"><i class="fas fa-carrot"></i></div>
                    <div><div class="kpi-label">مواد خام</div><div class="kpi-value english-num">{{ $prodStats['ingredients'] ?? 0 }}</div></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card kpi-secondary h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3"><i class="fas fa-blender"></i></div>
                    <div><div class="kpi-label">مكونات مركبة</div><div class="kpi-value english-num">{{ $prodStats['compounds'] ?? 0 }}</div></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card kpi-warning h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3"><i class="fas fa-exclamation-triangle"></i></div>
                    <div><div class="kpi-label">تنبيه المخزون</div><div class="kpi-value english-num">{{ $prodStats['low_stock'] ?? 0 }}</div></div>
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
                                <i class="fas fa-filter"></i> نوع الصنف
                            </button>
                            <ul class="dropdown-menu p-2 shadow">
                                <li class="form-check">
                                    <input class="form-check-input type-checkbox" type="checkbox" name="type[]" value="meal" id="type_meal" onchange="performSearch()">
                                    <label class="form-check-label" for="type_meal">وجبة جاهزة</label>
                                </li>
                                <li class="form-check">
                                    <input class="form-check-input type-checkbox" type="checkbox" name="type[]" value="ingredient" id="type_ingredient" onchange="performSearch()">
                                    <label class="form-check-label" for="type_ingredient">مادة خام</label>
                                </li>
                                <li class="form-check">
                                    <input class="form-check-input type-checkbox" type="checkbox" name="type[]" value="compound" id="type_compound" onchange="performSearch()">
                                    <label class="form-check-label" for="type_compound">مكون مركب</label>
                                </li>
                                <li class="form-check">
                                    <input class="form-check-input type-checkbox" type="checkbox" name="type[]" value="standard" id="type_standard" onchange="performSearch()">
                                    <label class="form-check-label" for="type_standard">منتج جاهز</label>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-tags"></i> التصنيف
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

                    @if(isset($currencies) && count($currencies) > 0)
                    <div class="col-auto">
                        <select name="currency_id" class="form-select" onchange="performSearch()">
                            <option value="">كل العملات</option>
                            <option value="{{ $baseCurrency->id }}" {{ request('currency_id') == $baseCurrency->id ? 'selected' : '' }}>
                                {{ $baseCurrency->code }} (الأساسية)
                            </option>
                            @foreach($currencies as $cur)
                                @if($cur->id !== $baseCurrency->id)
                                    <option value="{{ $cur->id }}" {{ request('currency_id') == $cur->id ? 'selected' : '' }}>
                                        {{ $cur->code }} — {{ $cur->name_ar ?? $cur->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- 🟢 فلتر الحالة --}}
                    <div class="col-auto">
                        <select name="status" class="form-select" onchange="performSearch()">
                            <option value="">كل الحالات</option>
                            <option value="1">أصناف فعالة</option>
                            <option value="0">أصناف معطلة</option>
                        </select>
                    </div>

                    {{-- فلتر الأعمدة --}}
                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-eye"></i> الأعمدة
                            </button>
                            <ul class="dropdown-menu p-2 shadow" id="columnToggleMenu">
                                {{-- سيملأ بواسطة JS --}}
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
                    <thead class="bg-light" id="mainTableHead">
                        <tr>
                            <th class="py-3" style="width: 80px;">صورة</th>
                            <th>الاسم</th>
                            <th>النوع</th>
                            <th>التصنيف</th>
                            <th>وحدة التقديم</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>الربح</th>
                            <th>المخزون</th>
                            <th>الحالة</th>
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
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            
            console.log("Searching with params:", params); // Debug for user console if they check
            
            const fetchUrl = url ? url : "{{ route('store.meals.index') }}?" + params;

            document.getElementById('mealsTableBody').style.opacity = '0.5';

            fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                document.getElementById('mealsTableBody').innerHTML = html;
                document.getElementById('mealsTableBody').style.opacity = '1';
                applyColumnVisibility();
            });
        }, 300);
    }

    // فلتر الأعمدة
    function initColumnVisibility() {
        const headers = document.querySelectorAll('#mealsTable > thead > tr > th');
        const menu = document.getElementById('columnToggleMenu');
        menu.innerHTML = '';

        headers.forEach((th, index) => {
            const text = th.textContent.trim();
            if (!text || text === 'صورة' || text === 'إجراءات') return;

            const li = document.createElement('li');
            li.innerHTML = `
                <div class="form-check ms-2">
                    <input class="form-check-input column-toggle" type="checkbox" checked 
                           data-column="${index}" id="col_${index}" onchange="toggleColumn(${index}, this.checked)">
                    <label class="form-check-label" for="col_${index}">${text}</label>
                </div>
            `;
            menu.appendChild(li);
        });
    }

    function toggleColumn(index, isVisible) {
        const table = document.getElementById('mealsTable');
        const th = table.querySelector(`#mainTableHead > tr > th:nth-child(${index + 1})`);
        if(th) th.style.display = isVisible ? '' : 'none';

        const cells = table.querySelectorAll(`tbody > tr > td:nth-child(${index + 1})`);
        cells.forEach(cell => {
            if(cell.closest('table').id === 'mealsTable') {
                cell.style.display = isVisible ? '' : 'none';
            }
        });
    }

    function applyColumnVisibility() {
        const toggles = document.querySelectorAll('.column-toggle');
        toggles.forEach(toggle => toggleColumn(parseInt(toggle.dataset.column), toggle.checked));
    }

    document.addEventListener('DOMContentLoaded', function() {
        initColumnVisibility();
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            performSearch(e.target.closest('.pagination a').href);
        }
    });
</script>
@endsection
