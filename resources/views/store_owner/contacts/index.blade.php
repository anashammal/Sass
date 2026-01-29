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
            <div class="card kpi-card kpi-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="kpi-label">العدد الكلي</div>
                        <div class="kpi-value english-num">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. عدد الزبائن (جديد) --}}
        <div class="col">
            <div class="card kpi-card kpi-info h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div>
                        <div class="kpi-label">عدد الزبائن</div>
                        <div class="kpi-value english-num">{{ $stats['customers_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. عدد الموردين (جديد) --}}
        <div class="col">
            <div class="card kpi-card kpi-warning h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <div class="kpi-label">عدد الموردين</div>
                        <div class="kpi-value english-num">{{ $stats['suppliers_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. ديون لنا --}}
        <div class="col">
            <div class="card kpi-card kpi-success h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div class="kpi-label">رصيد الزباين</div>
                        <div class="kpi-value english-num">{{ number_format($stats['receivables'] ?? 0, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. ديون علينا --}}
        <div class="col">
            <div class="card kpi-card kpi-danger h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <div class="kpi-label">مجموع الديون</div>
                        <div class="kpi-value english-num">{{ number_format(abs($stats['payables'] ?? 0), 0) }}</div>
                    </div>
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
        <option value="">(الكل)</option>
        <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>زبائن فقط</option>
        <option value="supplier" {{ request('type') == 'supplier' ? 'selected' : '' }}>موردين فقط</option>
    </select>
</div>

<div class="col-auto">
    <select name="balance_status" class="form-select fw-bold" onchange="performSearch()">
        <option value="">الأرصدة (الكل)</option>
        <option value="receivables">نطلبهم (ديون زبائن)</option>
        <option value="payables">يطلبونا (مستحقات موردين)</option>
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

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('store.payments.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="contact_id" id="pay_contact_id">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">تسجيل دفعة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    <i class="fas fa-info-circle me-1"></i>
                    الجهة: <span id="pay_contact_name" class="fw-bold"></span> | 
                    الرصيد الحالي: <span id="pay_contact_balance" class="fw-bold" dir="ltr"></span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">نوع العملية</label>
                    <select name="type" id="pay_type" class="form-select" required>
                        <option value="receive">قبض من عميل (Money In)</option>
                        <option value="pay">صرف لمورد (Money Out)</option>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">المبلغ</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                            <span class="input-group-text bg-white">د.أ</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">التاريخ</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">طريقة الدفع</label>
                    <select name="method" class="form-select" required>
                        <option value="cash">نقداً (Cash)</option>
                        <option value="card">شبكة (Card)</option>
                        <option value="bank">تحويل بنكي (Bank)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-success px-4">حفظ الدفعة</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal(id, name, balance) {
        document.getElementById('pay_contact_id').value = id;
        document.getElementById('pay_contact_name').innerText = name;
        
        const bal = parseFloat(balance);
        document.getElementById('pay_contact_balance').innerText = bal.toLocaleString(undefined, { minimumFractionDigits: 2 });
        
        // اقتراح النوع حسب الرصيد
        if (bal < 0) {
            document.getElementById('pay_type').value = 'receive'; // غالباً عميل عليه دين
        } else if (bal > 0) {
            document.getElementById('pay_type').value = 'pay'; // غالباً مورد له فلوس
        }

        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }

    let timeout = null;
    // (بقية كود البحث القديم...)
    // ...
</script>
<script>
    // دالة البحث (AJAX) - تم تكرارها لضمان العمل
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
        if(!menu) return;
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