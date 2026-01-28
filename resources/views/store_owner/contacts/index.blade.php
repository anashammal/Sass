@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3 class="text-primary fw-bold"><i class="fas fa-users me-2"></i> إدارة جهات الاتصال</h3>
        <a href="{{ route('store.contacts.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-user-plus me-1"></i> إضافة جهة اتصال
        </a>
    </div>
{{-- 🔥 هنا مكان الإحصائيات الصحيح 🔥 --}}
{{-- 🔥 لوحة الإحصائيات الجديدة (5 مربعات) 🔥 --}}
    <div class="row g-3 mb-4">
        {{-- 1. الكل --}}
        <div class="col">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-primary text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-users fa-lg"></i></div>
                    <h6 class="text-muted small mb-1">العدد الكلي</h6>
                    <h4 class="fw-bold mb-0 text-primary">{{ $stats['total'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        {{-- 2. عدد الزبائن (جديد) --}}
        <div class="col">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-info text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-user-tag fa-lg"></i></div>
                    <h6 class="text-muted small mb-1">عدد الزبائن</h6>
                    <h4 class="fw-bold mb-0 text-info">{{ $stats['customers_count'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        {{-- 3. عدد الموردين (جديد) --}}
        <div class="col">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-warning text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-truck fa-lg"></i></div>
                    <h6 class="text-muted small mb-1">عدد الموردين</h6>
                    <h4 class="fw-bold mb-0 text-warning" style="color: #d35400 !important;">{{ $stats['suppliers_count'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        {{-- 4. ديون لنا --}}
        <div class="col">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-success text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-arrow-down fa-lg"></i></div>
                    <h6 class="text-muted small mb-1">مجموع رصيد الزباين</h6>
                    <h5 class="fw-bold mb-0 text-success">{{ number_format($stats['receivables'] ?? 0, 0) }}</h5>
                </div>
            </div>
        </div>

        {{-- 5. ديون علينا --}}
        <div class="col">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 h-100">
                <div class="card-body p-3 text-center">
                    <div class="bg-danger text-white rounded-circle p-2 d-inline-block mb-2"><i class="fas fa-arrow-up fa-lg"></i></div>
                    <h6 class="text-muted small mb-1">مجموع الديون</h6>
                    <h5 class="fw-bold mb-0 text-danger">{{ number_format(abs($stats['payables'] ?? 0), 0) }}</h5>
                </div>
            </div>
        </div>
    </div>
    {{-- نهاية الإحصائيات --}}
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


{{-- هذا الكود يوضع مكان القائمة المنسدلة الحالية للنوع --}}
<div class="col-auto">
    <select name="type" class="form-select fw-bold" id="typeFilter" onchange="performSearch()">
        <option value="">الكل (عملاء وموردين)</option>
        
        {{-- 🔥 الإصلاح هنا: التأكد من القيم (customer / supplier) --}}
        <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>زبائن فقط</option>
        <option value="supplier" {{ request('type') == 'supplier' ? 'selected' : '' }}>موردين فقط</option>
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
                            <input type="text" id="searchInput" name="search" class="form-control" placeholder="بحث بالاسم، الشركة، الهاتف..." onkeyup="performSearch()">
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
                <table class="table table-hover align-middle text-center mb-0" id="contactsTable">
                    <thead class="bg-light" id="mainTableHead">
                        <tr>
                            <th>الاسم</th>
                            <th>الشركة</th>
                            <th>النوع</th>
                            <th>الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th>الرقم الضريبي</th>
                            <th>العنوان</th>
                            <th>الرصيد</th>
                            <th class="no-print">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="contactsTableBody">
                        @include('store_owner.contacts.partials.table_rows')
                    </tbody>
                </table>
            </div>
            <div id="paginationLinks" class="p-3 d-flex justify-content-center">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    @media print { .no-print { display: none !important; } }
</style>

<script>
    let timeout = null;

    // دالة البحث (AJAX)
    function performSearch(url = null) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            const fetchUrl = url ? url : "{{ route('store.contacts.index') }}?" + params;

            document.getElementById('contactsTableBody').style.opacity = '0.5';

            fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                document.getElementById('contactsTableBody').innerHTML = html;
                document.getElementById('contactsTableBody').style.opacity = '1';
                applyColumnVisibility();
            });
        }, 300);
    }

    // إدارة الأعمدة
    function initColumnVisibility() {
        const headers = document.querySelectorAll('#contactsTable > thead > tr > th');
        const menu = document.getElementById('columnToggleMenu');
        menu.innerHTML = '';

        headers.forEach((th, index) => {
            const text = th.textContent.trim();
            if (!text || text === 'إجراءات') return;

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
        const table = document.getElementById('contactsTable');
        const th = table.querySelector(`#mainTableHead > tr > th:nth-child(${index + 1})`);
        if(th) th.style.display = isVisible ? '' : 'none';

        const cells = table.querySelectorAll(`tbody > tr > td:nth-child(${index + 1})`);
        cells.forEach(cell => cell.style.display = isVisible ? '' : 'none');
    }

    function applyColumnVisibility() {
        const toggles = document.querySelectorAll('.column-toggle');
        toggles.forEach(toggle => toggleColumn(parseInt(toggle.dataset.column), toggle.checked));
    }

    document.addEventListener('DOMContentLoaded', function() {
        initColumnVisibility();
    });

    // الترقيم عبر AJAX
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            performSearch(e.target.closest('.pagination a').href);
        }
    });

</script>
@endsection