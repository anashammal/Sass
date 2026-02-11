@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    {{-- عنوان الصفحة وزر الإضافة --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="fw-bold text-primary"><i class="fas fa-shopping-cart me-2"></i> سجل المشتريات</h4>
        <div class="d-flex gap-2">
            <button onclick="sharePurchasesViaWhatsapp()" class="btn btn-outline-success"><i class="fab fa-whatsapp me-1"></i> إرسال واتساب</button>
            <button onclick="sharePurchasesViaEmail()" class="btn btn-outline-primary"><i class="fas fa-envelope me-1"></i> إرسال إيميل</button>
            <a href="{{ route('store.purchases.report.interactive', request()->all()) }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-file-invoice me-1"></i> التقرير التفاعلي للطباعة</a>
            <a href="{{ route('store.purchases.create') }}" class="btn btn-primary"><i class="fas fa-plus-circle me-1"></i> فاتورة جديدة</a>
        </div>
    </div>

    {{-- بطاقات الملخص (تتغير حسب الفلترة) --}}
    <div class="row g-3 mb-4 no-print">
        <div class="col-md-4">
            <div class="card kpi-card kpi-info h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div class="kpi-label">إجمالي الفواتير ({{ $totals['count'] }})</div>
                        <div class="kpi-value english-num">{{ number_format($totals['sum_total'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi-card kpi-success h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="kpi-label">إجمالي المدفوع</div>
                        <div class="kpi-value english-num">{{ number_format($totals['sum_paid'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi-card kpi-danger h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div>
                        <div class="kpi-label">المتبقي (الأجل)</div>
                        <div class="kpi-value english-num">{{ number_format($totals['sum_due'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- قسم الفلاتر والبحث --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body p-4">
            <form action="{{ route('store.purchases.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- بحث عام --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">بحث ذكي</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="رقم الفاتورة، اسم المورد..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- فلتر المورد --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">المورد</label>
                        <select name="supplier_id" class="form-select auto-filter">
                            <option value="">-- كل الموردين --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                    {{ $sup->contact_name }} {{ $sup->company_name ? '('.$sup->company_name.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- فلتر الحالة --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">حالة الدفع</label>
                        <select name="payment_status" class="form-select auto-filter">
                            <option value="">-- الكل --</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>جزئية</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
                        </select>
                    </div>

                    {{-- من تاريخ --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">من تاريخ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                            <input type="date" id="filterDateFrom" name="date_from" class="form-control enhanced-date-input auto-filter" value="{{ request('date_from') }}">
                        </div>
                    </div>

                    {{-- إلى تاريخ --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">إلى تاريخ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                            <input type="date" id="filterDateTo" name="date_to" class="form-control enhanced-date-input auto-filter" value="{{ request('date_to') }}">
                        </div>
                    </div>

                    {{-- أزرار التحكم والترتيب --}}
                    <div class="col-12 d-flex justify-content-between align-items-end mt-3 border-top pt-3">
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 250px;">
                                <span class="input-group-text bg-light small">ترتيب بـ</span>
                                <select name="sort_by" class="form-select form-select-sm bg-light border-start-0" onchange="document.getElementById('filterForm').submit()">
                                    <option value="invoice_date" {{ request('sort_by') == 'invoice_date' ? 'selected' : '' }}>تاريخ الفاتورة</option>
                                    <option value="grand_total" {{ request('sort_by') == 'grand_total' ? 'selected' : '' }}>المبلغ الإجمالي</option>
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>تاريخ الإدخال</option>
                                </select>
                                <select name="order_by" class="form-select form-select-sm bg-light" style="max-width: 80px;" onchange="document.getElementById('filterForm').submit()">
                                    <option value="desc" {{ request('order_by') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                                    <option value="asc" {{ request('order_by') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('store.purchases.index') }}" class="btn btn-light border text-muted">إعادة ضبط</a>
                            <button type="submit" class="btn btn-secondary px-4"><i class="fas fa-filter me-1"></i> تطبيق الفلتر</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول البيانات --}}
    <div class="card border-0 shadow-sm">
        {{-- ترويسة الطباعة فقط (تظهر فقط عند الطباعة) --}}
        <div class="d-none d-print-block p-4 text-center border-bottom">
            <h3>تقرير المشتريات</h3>
            <p>من: {{ request('date_from') ?? 'البداية' }} | إلى: {{ request('date_to') ?? 'الآن' }}</p>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th>#</th>
                            <th>رقم الفاتورة</th>
                            <th>المورد</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th class="no-print">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $purchase->invoice_number }}</td>
                                <td>
                                    @if($purchase->supplier)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle text-center me-2" style="width:30px;height:30px;line-height:30px;">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark small">{{ $purchase->supplier->contact_name }}</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $purchase->supplier->company_name }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">مورد محذوف</span>
                                    @endif
                                </td>
                               {{-- عرض التاريخ والوقت بتنسيق 12 ساعة (مثال: 2023-10-25 02:30 PM) --}}
<td>
    <div class="d-flex flex-column">
        <span class="fw-bold">{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('Y-m-d') }}</span>
        <span class="text-muted small">{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('h:i A') }}</span>
    </div>
</td>
                                <td>
                                    @if($purchase->payment_status == 'paid')
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">مدفوع</span>
                                    @elseif($purchase->payment_status == 'partial')
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">جزئي</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">غير مدفوع</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ number_format($purchase->grand_total, 2) }}</td>
                                <td class="text-success small">{{ number_format($purchase->paid_amount, 2) }}</td>
                                <td class="text-danger small fw-bold">
                                    {{ number_format($purchase->grand_total - $purchase->paid_amount, 2) }}
                                <td>
    <a href="javascript:void(0);" 
   data-url="{{ route('store.purchases.show', $purchase->id) }}" 
   class="btn btn-sm btn-outline-info view-invoice-btn">
    <i class="fa fa-eye"></i>
</a>

    <a href="{{ route('store.purchases.edit', $purchase->id) }}" class="btn btn-sm btn-outline-warning">
        <i class="fa fa-edit"></i>
    </a>

    <form action="{{ route('store.purchases.destroy', $purchase->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟ سيتم عكس المخزون وحساب المورد.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
            <i class="fa fa-trash"></i>
        </button>
    </form>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <img src="{{ asset('images/no-data.svg') }}" alt="No Data" style="width: 80px; opacity: 0.5" class="mb-3">
                                    <p class="text-muted">لا توجد مشتريات تطابق الفلتر الحالي</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr class="table-active">
                            <td colspan="5" class="text-end">الإجماليات (للصفحة الحالية):</td>
                            <td>{{ number_format($purchases->sum('grand_total'), 2) }}</td>
                            <td class="text-success">{{ number_format($purchases->sum('paid_amount'), 2) }}</td>
                            <td class="text-danger">{{ number_format($purchases->sum('grand_total') - $purchases->sum('paid_amount'), 2) }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white py-3 no-print">
            {{ $purchases->links() }}
        </div>
    </div>
</div>

<style>
    /* تنسيقات الطباعة */
    @media print {
        .no-print, .navbar, .sidebar, footer, .btn {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .container-fluid {
            width: 100%;
            padding: 0;
            margin: 0;
        }
        table {
            width: 100% !important;
            font-size: 12px;
        }
        .d-print-block {
            display: block !important;
        }
        body {
            background: white !important;
        }
    }
</style>
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">تفاصيل الفاتورة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="invoiceModalBody">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
// دالة طباعة محتوى المودال
    function printInvoiceContent() {
        var content = document.getElementById('invoiceModalBody').innerHTML;
        var mywindow = window.open('', 'PRINT', 'height=600,width=800');

        mywindow.document.write('<html dir="rtl"><head><title>طباعة الفاتورة</title>');
        // استدعاء ملفات الستايل الخاصة بالمشروع ليظهر التنسيق (Bootstrap)
        mywindow.document.write('<link href="{{ asset("css/app.css") }}" rel="stylesheet">');
        // تنسيقات إضافية لتحسين شكل الطباعة
        mywindow.document.write('<style>body{background-color:white; font-family: "Nunito", sans-serif;} .no-print{display:none !important;} table{width:100% !important;} .modal-body{padding:20px;}</style>');
        mywindow.document.write('</head><body>');
        
        mywindow.document.write('<div class="container pt-4">');
        mywindow.document.write(content);
        mywindow.document.write('</div>');
        
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // ضروري لبعض المتصفحات
        mywindow.focus(); // ضروري لبعض المتصفحات

        // تأخير بسيط لضمان تحميل ملفات الـ CSS قبل الطباعة
        setTimeout(function(){ 
            mywindow.print(); 
            mywindow.close(); 
        }, 500);
    }
    function sharePurchasesViaWhatsapp() {
        // نستخدم الفلترة الحالية من الرابط
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('output', 'url');
        
        const fetchUrl = "{{ route('store.purchases.report.pdf') }}?" + urlParams.toString();
        
        if (typeof Swal !== 'undefined') {
            let progressTimer;
            Swal.fire({
                title: 'جاري تجهيز ملف التقرير...',
                html: `
                    <div class="mb-3">يرجى الانتظار قليلاً لجمع البيانات وتكوين ملف PDF...</div>
                    <div class="progress" style="height: 20px;">
                        <div id="swal-progress-bar-wa" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Swal.showLoading(); 
                    const progressBar = document.getElementById('swal-progress-bar-wa');
                    let progress = 0;
                    const duration = 180000; // 3 دقائق
                    const interval = 1000;
                    const increment = 95 / (duration / interval);

                    progressTimer = setInterval(() => {
                        progress += increment;
                        if(progress > 95) progress = 95;
                        const pct = Math.round(progress) + '%';
                        if(progressBar) {
                            progressBar.style.width = pct;
                            progressBar.innerText = pct;
                        }
                    }, interval);
                },
                willClose: () => {
                    clearInterval(progressTimer);
                }
            });
        } else {
            console.log('Preparing report...');
        }

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {

                // if (typeof Swal !== 'undefined') Swal.close(); // Don't verify Swal close here, let it close or handle inside
                if (typeof Swal !== 'undefined') Swal.close();
                
                if (data.url) {
                    const message = `*تقرير سجل المشتريات*\n` +
                                    `المتجر: {{ auth()->user()->store->name }}\n` +
                                    `تاريخ التقرير: {{ now()->format('Y-m-d') }}\n` +
                                    `مرفق لكم التقرير التفصيلي كملف PDF.`;
                    
                    const filename = data.filename || "purchase_report.pdf";
                    // إرسال المرفق عبر المودال العالمي
                    if (typeof triggerWhatsappPrompt === 'function') {
                        triggerWhatsappPrompt('', message, "إرسال سجل المشتريات كمرفق PDF", data.url, filename);
                    } else {
                        alert('حدث خطأ: وظيفة إرسال الواتساب غير متوفرة');
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('خطأ', 'فشل تجهيز ملف التقرير', 'error');
                    } else {
                        alert('فشل تجهيز ملف التقرير');
                    }
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') Swal.close();
                console.error(err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('خطأ', 'حدث خطأ أثناء التواصل مع السيرفر', 'error');
                } else {
                    alert('حدث خطأ أثناء التواصل مع السيرفر');
                }
            });
    }

    function sharePurchasesViaEmail() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('output', 'url');
        
        const fetchUrl = "{{ route('store.purchases.report.pdf') }}?" + urlParams.toString();
        
        if (typeof Swal !== 'undefined') {
            let progressTimer;
            Swal.fire({
                title: 'جاري تجهيز طلب الإرسال...',
                html: `
                    <div class="mb-3">يرجى الانتظار بينما يتم تجهيز ملف PDF والاتصال بخدمة البريد الإلكتروني...</div>
                    <div class="progress" style="height: 20px;">
                        <div id="swal-progress-bar-email" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Swal.showLoading(); 
                    const progressBar = document.getElementById('swal-progress-bar-email');
                    let progress = 0;
                    const duration = 180000; // 3 دقائق
                    const interval = 1000;
                    const increment = 95 / (duration / interval);

                    progressTimer = setInterval(() => {
                        progress += increment;
                        if(progress > 95) progress = 95;
                        const pct = Math.round(progress) + '%';
                        if(progressBar) {
                            progressBar.style.width = pct;
                            progressBar.innerText = pct;
                        }
                    }, interval);
                },
                willClose: () => {
                    clearInterval(progressTimer);
                }
            });
        }

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                if (typeof Swal !== 'undefined') Swal.close();
                
                if (data.url) {
                    const message = `*تقرير سجل المشتريات*\n` +
                                    `المتجر: {{ auth()->user()->store->name }}\n` +
                                    `تاريخ التقرير: {{ now()->format('Y-m-d') }}\n` +
                                    `مرفق لكم التقرير التفصيلي كملف PDF.`;
                    
                    const filename = data.filename || "purchase_report.pdf";
                    if (typeof triggerEmailPrompt === 'function') {
                        triggerEmailPrompt('', message, "إرسال سجل المشتريات كمرفق PDF", data.url, filename);
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
        // 1. التحديث التلقائي
        $('.auto-filter').on('change', function() {
            $('#filterForm').submit();
        });

        // 2. التحقق الذكي من التواريخ
        $('#filterDateFrom').on('change', function() {
            let fromDate = $(this).val();
            $('#filterDateTo').attr('min', fromDate);
            let currentTo = $('#filterDateTo').val();
            if(fromDate && currentTo && currentTo < fromDate) {
                $('#filterDateTo').val(fromDate);
            }
        });

        $('#filterDateTo').on('change', function() {
            let toDate = $(this).val();
            $('#filterDateFrom').attr('max', toDate);
            let currentFrom = $('#filterDateFrom').val();
            if(toDate && currentFrom && currentFrom > toDate) {
                $('#filterDateFrom').val(toDate);
            }
        });

        // تشغيل التحقق البدئي (بدون تحديث تلقائي)
        if($('#filterDateFrom').val()) $('#filterDateTo').attr('min', $('#filterDateFrom').val());
        if($('#filterDateTo').val()) $('#filterDateFrom').attr('max', $('#filterDateTo').val());

        // عند الضغط على زر المعاينة
        $(document).on('click', '.view-invoice-btn', function() {
            var url = $(this).data('url');
            
            // 1. فتح المودال
            $('#invoiceModal').modal('show');
            
            // 2. إظهار علامة التحميل (للتأكد من تنظيف المحتوى السابق)
            $('#invoiceModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

            // 3. جلب البيانات عبر AJAX
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // وضع المحتوى داخل جسم المودال
                    $('#invoiceModalBody').html(response);
                },
                error: function() {
                    $('#invoiceModalBody').html('<div class="alert alert-danger">حدث خطأ أثناء تحميل الفاتورة.</div>');
                }
            });
        });
    });
</script>
@endsection