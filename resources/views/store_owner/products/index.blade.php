@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">

<h3 class="text-primary fw-bold"><i class="fas fa-box-open me-2"></i> إدارة المنتجات</h3>
        <a href="{{ route('store.products.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus me-1"></i> إضافة منتج جديد
        </a>
    </div>
{{-- 🔥 بداية كود الإحصائيات (ضعه هنا) 🔥 --}}
<div class="row g-3 mb-4 no-print">
    {{-- 1. إجمالي المنتجات --}}
    <div class="col">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
            <div class="card-body p-3 text-center">
                <div class="bg-primary text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-box-open fa-lg"></i></div>
                <h6 class="text-muted small mb-1">إجمالي المنتجات</h6>
                <h4 class="fw-bold mb-0 text-primary">{{ $prodStats['total'] ?? 0 }}</h4>
            </div>
        </div>
    </div>

    {{-- 2. مخزون منخفض --}}
    <div class="col">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
            <div class="card-body p-3 text-center">
                <div class="bg-warning text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-exclamation-triangle fa-lg"></i></div>
                <h6 class="text-muted small mb-1">مخزون منخفض</h6>
                <h4 class="fw-bold mb-0 text-warning" style="color: #d35400 !important;">{{ $prodStats['low_stock'] ?? 0 }}</h4>
            </div>
        </div>
    </div>

    {{-- 3. نافذ من المخزون --}}
    <div class="col">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10 h-100">
            <div class="card-body p-3 text-center">
                <div class="bg-danger text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-times-circle fa-lg"></i></div>
                <h6 class="text-muted small mb-1">نافذ (0 كمية)</h6>
                <h4 class="fw-bold mb-0 text-danger">{{ $prodStats['out_of_stock'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>
{{-- 🔥 نهاية كود الإحصائيات 🔥 --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 no-print">
            <form id="filterForm" onsubmit="return false;">
                <div class="row g-3 align-items-center">
                    
                    {{-- عدد العناصر --}}
                    <div class="col-auto">
                        <select name="per_page" class="form-select" onchange="performSearch()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    {{-- فلتر التصنيفات --}}
                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i> التصنيف
                            </button>
                            <ul class="dropdown-menu p-2 shadow" style="max-height: 300px; overflow-y: auto;">
                                <li><h6 class="dropdown-header">اختر التصنيفات</h6></li>
                                @foreach($categories as $cat)
                                    <li class="form-check">
                                        <input class="form-check-input cat-checkbox" type="checkbox" name="category_id[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" onchange="performSearch()">
                                        <label class="form-check-label" for="cat_{{ $cat->id }}">{{ $cat->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- 🟢 فلتر الحالة (جديد) --}}
                    <div class="col-auto">
                        <select name="status" class="form-select" onchange="performSearch()">
                            <option value="">كل الحالات</option>
                            <option value="1">منتجات فعالة</option>
                            <option value="0">منتجات معطلة</option>
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

                    {{-- البحث --}}
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" name="search" class="form-control" placeholder="بحث..." onkeyup="performSearch()">
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
                {{-- أضفنا ID للـ THEAD لتسهيل الاستهداف --}}
                <table class="table table-hover align-middle text-center mb-0" id="productsTable">
                    <thead class="bg-light" id="mainTableHead">
                        <tr>
                            <th class="py-3" style="width: 80px;">صورة</th>
                            <th>المنتج</th>
                            <th>الباركود</th>
                            <th>التصنيف</th>
                            <th>الوحدة</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>المخزون</th>
                            <th>الحالة</th>
                            <th class="no-print">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        @include('store_owner.products.partials.table_rows')
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<style>
    @media print { .no-print { display: none !important; } }
    /* تحسين عرض الصور */
    .product-img-fit {
        object-fit: contain !important; /* يمنع القص */
        background-color: #fff; /* خلفية بيضاء */
        border: 1px solid #dee2e6;
        padding: 2px;
    }
</style>

<script>
    let timeout = null;

    function performSearch(url = null) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            const fetchUrl = url ? url : "{{ route('store.products.index') }}?" + params;

            document.getElementById('productsTableBody').style.opacity = '0.5';

            fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                document.getElementById('productsTableBody').innerHTML = html;
                document.getElementById('productsTableBody').style.opacity = '1';
                applyColumnVisibility();
            });
        }, 300);
    }

    // 🟢 إصلاح فلتر الأعمدة: استهداف الصف الأول فقط من الرأس الرئيسي
    function initColumnVisibility() {
        // نستخدم > لاختيار الأبناء المباشرين فقط (لتجاهل الجداول الداخلية)
        const headers = document.querySelectorAll('#productsTable > thead > tr > th');
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
        const table = document.getElementById('productsTable');
        // إخفاء الرأس
        const th = table.querySelector(`#mainTableHead > tr > th:nth-child(${index + 1})`);
        if(th) th.style.display = isVisible ? '' : 'none';

        // إخفاء الخلايا (في الصفوف الرئيسية فقط)
        const cells = table.querySelectorAll(`tbody > tr > td:nth-child(${index + 1})`);
        cells.forEach(cell => {
            // تأكد أن الخلية تابعة للصف الرئيسي وليست داخل جدول فرعي
            if(cell.closest('table').id === 'productsTable') {
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