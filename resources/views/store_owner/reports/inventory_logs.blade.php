@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="m-0 fw-bold"><i class="fas fa-history me-2"></i>سجل تعديلات المخزون</h5>
            <div class="d-flex gap-2 no-print">
                <button onclick="window.print()" class="btn btn-sm btn-light">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
                <button onclick="shareViaWhatsApp()" class="btn btn-sm btn-success shadow-sm">
                    <i class="fab fa-whatsapp me-1"></i> واتساب
                </button>
                <button onclick="shareViaEmail()" class="btn btn-sm btn-info text-white shadow-sm">
                    <i class="fas fa-envelope me-1"></i> بريد إلكتروني
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- فلاتر البحث --}}
            <form id="filterForm" method="GET" action="{{ route('reports.inventory_logs') }}" class="row g-3 mb-4 no-print">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">البحث (منتج / باركود)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="اسم المنتج أو الباركود..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">الموظف</label>
                    <select name="user_id" class="form-select auto-filter">
                        <option value="">الكل</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control auto-filter" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control auto-filter" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted">الصفوف</label>
                    <select name="per_page" class="form-select auto-filter">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('reports.inventory_logs') }}" class="btn btn-outline-secondary w-100" title="إعادة تعيين"><i class="fas fa-undo me-1"></i> إعادة تعيين</a>
                </div>
            </form>

            <div id="logsTableContainer">
                @include('store_owner.reports.partials.inventory_logs_table')
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .card-header { background: #fff !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
        .table thead th { background-color: #f8f9fa !important; color: #000 !important; border: 1px solid #000 !important; }
        .table td { border: 1px solid #000 !important; }
        body { background: white !important; }
    }
</style>
@endsection

@section('scripts')
<script>
    function shareViaWhatsApp() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('output', 'url');
        
        const fetchUrl = "{{ route('reports.inventory_logs.pdf') }}?" + urlParams.toString();
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'جاري تجهيز تقرير المخزون...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                if (typeof Swal !== 'undefined') Swal.close();
                if (data.url) {
                    const message = `*تقرير سجل تعديلات المخزون*\n` +
                                    `المتجر: {{ auth()->user()->store->name }}\n` +
                                    `تاريخ التقرير: {{ now()->format('Y-m-d') }}\n` +
                                    `مرفق لكم التقرير التفصيلي للتعديلات اليدوية.`;
                    
                    const filename = data.filename || "inventory_log_report.pdf";
                    if (typeof triggerWhatsappPrompt === 'function') {
                        triggerWhatsappPrompt('', message, "إرسال سجل التعديلات كمرفق PDF", data.url, filename);
                    } else {
                        alert('حدث خطأ: وظيفة إرسال الواتساب غير متوفرة');
                    }
                } else {
                    alert('فشل تجهيز ملف التقرير');
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') Swal.close();
                console.error(err);
                alert('حدث خطأ أثناء التواصل مع السيرفر');
            });
    }

    function shareViaEmail() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('output', 'url');
        
        const fetchUrl = "{{ route('reports.inventory_logs.pdf') }}?" + urlParams.toString();
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'جاري تجهيز طلب الإرسال...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                if (typeof Swal !== 'undefined') Swal.close();
                if (data.url) {
                    const message = `*تقرير سجل تعديلات المخزون*\n` +
                                    `المتجر: {{ auth()->user()->store->name }}\n` +
                                    `تاريخ التقرير: {{ now()->format('Y-m-d') }}\n` +
                                    `مرفق لكم التقرير التفصيلي للتعديلات اليدوية.`;
                    
                    const filename = data.filename || "inventory_log_report.pdf";
                    if (typeof triggerEmailPrompt === 'function') {
                        triggerEmailPrompt('', message, "إرسال سجل التعديلات كمرفق PDF", data.url, filename);
                    } else {
                        alert('حدث خطأ: وظيفة إرسال البريد غير متوفرة');
                    }
                } else {
                    alert('فشل تجهيز ملف التقرير');
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') Swal.close();
                console.error(err);
                alert('حدث خطأ أثناء التواصل مع السيرفر');
            });
    }
    
    $(document).ready(function() {
        let fetchTimer;
        
        function fetchLogs(url = null) {
            const formData = $('#filterForm').serialize();
            const targetUrl = url || "{{ route('reports.inventory_logs') }}?" + formData;

            // تحديث رابط الصفحة في المتصفح دون إعادة تحميل
            window.history.pushState({path: targetUrl}, '', targetUrl);

            $.ajax({
                url: targetUrl,
                type: 'GET',
                success: function(html) {
                    $('#logsTableContainer').html(html);
                },
                error: function() {
                    console.error('Failed to fetch logs');
                }
            });
        }

        // فلترة فورية للحقول العادية
        $('.auto-filter').on('change', function() {
            fetchLogs();
        });

        // بحث ذكي (Debounced) لحفل النص
        $('input[name="search"]').on('keyup', function() {
            clearTimeout(fetchTimer);
            fetchTimer = setTimeout(fetchLogs, 400); // إرسال الطلب بعد 400 مللي ثانية من التوقف عن الكتابة
        });

        // منع إرسال النموذج بالطريقة التقليدية
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            fetchLogs();
        });

        // معالجة روابط الترقيم بالـ AJAX لضمان استمرار الفلاتر
        $(document).on('click', '.pagination-container .pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if(url) fetchLogs(url);
        });
    });
</script>
@endsection
