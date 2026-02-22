@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="fas fa-bell me-2"></i> إعدادات الإشعارات</h3>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('store.settings.update-notifications', $store->id) }}">
        @csrf
        @method('PUT')

        <div class="row mb-4">
            {{-- 1. بطاقة ربط الواتساب --}}
            <div class="col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="fab fa-whatsapp me-2"></i> ربط واتساب المتجر</span>
                        <span class="badge bg-white text-success small" id="wa_status_badge">جاري الفحص...</span>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <div id="wa_loading">
                            <div class="spinner-border text-success mb-2" role="status"></div>
                            <p class="text-muted small">جاري الاتصال بالسيرفر...</p>
                        </div>

                        <div id="wa_qr" style="display: none;">
                            <p class="small text-muted mb-2">امسح الكود لربط رقم المتجر</p>
                            <div class="bg-white p-2 d-inline-block rounded border mb-2">
                                <div id="qrcode_canvas"></div>
                            </div>
                            <div class="alert alert-info small py-1 mb-0"><i class="fas fa-info-circle"></i> سيتم التحديث تلقائياً بعد المسح.</div>
                        </div>

                        <div id="wa_connected" style="display: none;">
                            <div class="alert alert-success border-0 shadow-sm mb-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h6 class="fw-bold">متصل بنجاح!</h6>
                                <div class="small" dir="ltr">
                                    <span id="wa_number" class="fw-bold text-dark">...</span><br>
                                    <span id="wa_name" class="text-muted">...</span>
                                </div>
                            </div>
                            <button type="button" onclick="logoutWhatsApp()" class="btn btn-outline-danger btn-sm w-100 mt-auto">
                                <i class="fas fa-sign-out-alt me-1"></i> فك الارتباط
                            </button>
                        </div>

                        <div id="wa_error" style="display: none;">
                            <span class="text-danger small fw-bold"><i class="fas fa-exclamation-triangle"></i> الخدمة غير متاحة</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                {{-- وقت التقرير اليومي العام --}}
                <div class="card shadow-sm bg-light mb-4">
                    <div class="card-body p-3 text-center">
                        <label class="fw-bold me-2"><i class="fas fa-clock"></i> توقيت إرسال التقرير اليومي الإجمالي للجميع:</label>
                        <input type="time" name="daily_report_time" class="form-control d-inline-block text-center fw-bold border-dark" style="width: 150px;" 
                               value="{{ current($store->daily_report_time ? [(new \Carbon\Carbon($store->daily_report_time))->format('H:i')] : ['']) }}">
                        <small class="text-muted ms-2 d-block mt-2">(اتركه فارغاً لإيقاف التقرير تماماً المجدول)</small>
                    </div>
                </div>

                {{-- خيار طلب التأكيد قبل الإرسال --}}
                <div class="card shadow-sm bg-white border border-success">
                    <div class="card-body p-3">
                        <div class="form-check form-switch px-0 ms-2">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="whatsapp_auto_prompt" value="1" {{ $store->whatsapp_auto_prompt ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-success">فتح نافذة تأكيد الإرسال للعميل عند حفظ الفواتير</label>
                        </div>
                        <small class="text-muted d-block mt-1 ms-4 text-wrap">عند تمكين هذا الخيار، سيظهر لك خيار إرسال الفاتورة عبر واتساب فور حفظها، مع إمكانية تعديل رقم العميل والرسالة قبل الإرسال.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- 📧 إعدادات البريد الإلكتروني --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-primary h-100">
                    <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i> إشعارات الإيميل للمتجر</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="notify_email" name="notify_email" value="1" {{ $store->notify_email ? 'checked' : '' }} onchange="$('#email_body').slideToggle()">
                            <label class="form-check-label text-white fw-bold ms-2" for="notify_email">تفعيل</label>
                        </div>
                    </div>
                    
                    <div class="card-body bg-white" id="email_body" style="display: {{ $store->notify_email ? 'block' : 'none' }};">
                        {{-- 1. فواتير المبيعات --}}
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold text-primary"><i class="fas fa-file-invoice me-1"></i> المبيعات</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="email_notify_sales" value="1" {{ $store->email_notify_sales ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_sales_credit_only" name="email_sales_credit_only" value="1" {{ $store->email_sales_credit_only ? 'checked' : '' }} onchange="toggleInputs('email_sales')">
                                        <label class="form-check-label text-danger small fw-bold" for="email_sales_credit_only">إشعارات بفواتير الدين فقط</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div id="email_sales_general_div" style="display: {{ $store->email_sales_credit_only ? 'none' : 'block' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">الحد الأدنى لقيمة الفاتورة</span>
                                            <input type="number" step="0.01" class="form-control" name="email_sales_min" value="{{ $store->email_sales_min }}" placeholder="0">
                                        </div>
                                    </div>
                                    <div id="email_sales_credit_div" style="display: {{ $store->email_sales_credit_only ? 'block' : 'none' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-danger text-white">الحد الأدنى لقيمة الدين</span>
                                            <input type="number" step="0.01" class="form-control border-danger" name="email_sales_credit_min" value="{{ $store->email_sales_credit_min }}" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. فواتير المشتريات --}}
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold text-success"><i class="fas fa-truck-loading me-1"></i> المشتريات</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="email_notify_purchases" value="1" {{ $store->email_notify_purchases ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_purchases_credit_only" name="email_purchases_credit_only" value="1" {{ $store->email_purchases_credit_only ? 'checked' : '' }} onchange="toggleInputs('email_purchases')">
                                        <label class="form-check-label text-danger small fw-bold" for="email_purchases_credit_only">إشعارات بفواتير الآجل فقط</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div id="email_purchases_general_div" style="display: {{ $store->email_purchases_credit_only ? 'none' : 'block' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">الحد الأدنى للفاتورة</span>
                                            <input type="number" step="0.01" class="form-control" name="email_purchases_min" value="{{ $store->email_purchases_min }}" placeholder="0">
                                        </div>
                                    </div>
                                    <div id="email_purchases_credit_div" style="display: {{ $store->email_purchases_credit_only ? 'block' : 'none' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-danger text-white">الحد الأدنى للآجل</span>
                                            <input type="number" step="0.01" class="form-control border-danger" name="email_purchases_credit_min" value="{{ $store->email_purchases_credit_min }}" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. خيارات أخرى --}}
                        <div class="row text-center mt-3 g-2">
                            <div class="col-md-4">
                                <div class="form-check form-switch d-inline-block text-start">
                                    <input class="form-check-input" type="checkbox" name="email_notify_stock" value="1" {{ $store->email_notify_stock ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold">تنبيهات المخزون</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch d-inline-block text-start">
                                    <input class="form-check-input" type="checkbox" name="email_notify_expiry" value="1" {{ $store->email_notify_expiry ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold">تنبيهات الصلاحية</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch d-inline-block text-start">
                                    <input class="form-check-input" type="checkbox" name="email_daily_report" value="1" {{ $store->email_daily_report ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-primary">التقرير اليومي</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 💬 إعدادات الواتساب --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-success h-100" id="wa_notify_container">
                    <div class="card-header bg-success bg-gradient text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fab fa-whatsapp me-2"></i> إشعارات الواتساب للمتجر</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="notify_whatsapp" name="notify_whatsapp" value="1" {{ $store->notify_whatsapp ? 'checked' : '' }} onchange="$('#wa_body').slideToggle()">
                            <label class="form-check-label text-white fw-bold ms-2" for="notify_whatsapp">تفعيل</label>
                        </div>
                    </div>
                    
                    <div class="card-body bg-white pb-4" id="wa_body" style="display: {{ $store->notify_whatsapp ? 'block' : 'none' }};">
                        
                        <div class="alert alert-warning wa-warning-msg" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i> يرجى ربط رقم الواتساب بالمتجر (في الأعلى) لتتمكن من استقبال الإشعارات.
                        </div>

                        {{-- 1. فواتير المبيعات --}}
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold text-primary"><i class="fas fa-file-invoice me-1"></i> المبيعات</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="wa_notify_sales" value="1" {{ $store->wa_notify_sales ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="wa_sales_credit_only" name="wa_sales_credit_only" value="1" {{ $store->wa_sales_credit_only ? 'checked' : '' }} onchange="toggleInputs('wa_sales')">
                                        <label class="form-check-label text-danger small fw-bold" for="wa_sales_credit_only">إشعارات بفواتير الدين فقط</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div id="wa_sales_general_div" style="display: {{ $store->wa_sales_credit_only ? 'none' : 'block' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">الحد الأدنى لقيمة الفاتورة</span>
                                            <input type="number" step="0.01" class="form-control" name="wa_sales_min" value="{{ $store->wa_sales_min }}" placeholder="0">
                                        </div>
                                    </div>
                                    <div id="wa_sales_credit_div" style="display: {{ $store->wa_sales_credit_only ? 'block' : 'none' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-danger text-white">الحد الأدنى لقيمة الدين</span>
                                            <input type="number" step="0.01" class="form-control border-danger" name="wa_sales_credit_min" value="{{ $store->wa_sales_credit_min }}" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. فواتير المشتريات --}}
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold text-success"><i class="fas fa-truck-loading me-1"></i> المشتريات</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="wa_notify_purchases" value="1" {{ $store->wa_notify_purchases ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="wa_purchases_credit_only" name="wa_purchases_credit_only" value="1" {{ $store->wa_purchases_credit_only ? 'checked' : '' }} onchange="toggleInputs('wa_purchases')">
                                        <label class="form-check-label text-danger small fw-bold" for="wa_purchases_credit_only">إشعارات بفواتير الآجل فقط</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div id="wa_purchases_general_div" style="display: {{ $store->wa_purchases_credit_only ? 'none' : 'block' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">الحد الأدنى للفاتورة</span>
                                            <input type="number" step="0.01" class="form-control" name="wa_purchases_min" value="{{ $store->wa_purchases_min }}" placeholder="0">
                                        </div>
                                    </div>
                                    <div id="wa_purchases_credit_div" style="display: {{ $store->wa_purchases_credit_only ? 'block' : 'none' }};">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-danger text-white">الحد الأدنى للآجل</span>
                                            <input type="number" step="0.01" class="form-control border-danger" name="wa_purchases_credit_min" value="{{ $store->wa_purchases_credit_min }}" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. خيارات أخرى --}}
                        <div class="row text-center mt-3 g-2">
                            <div class="col-md-4">
                                <div class="form-check form-switch d-inline-block text-start">
                                    <input class="form-check-input" type="checkbox" name="wa_notify_stock" value="1" {{ $store->wa_notify_stock ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold">تنبيهات المخزون</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch d-inline-block text-start">
                                    <input class="form-check-input" type="checkbox" name="wa_notify_expiry" value="1" {{ $store->wa_notify_expiry ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold">تنبيهات الصلاحية</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch d-inline-block text-start">
                                    <input class="form-check-input" type="checkbox" name="wa_daily_report" value="1" {{ $store->wa_daily_report ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-success">التقرير اليومي</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-success btn-lg px-5 shadow rounded-pill">
                    <i class="fa fa-save me-2"></i> حفظ الإعدادات
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let lastQrCode = null;
    let isWhatsAppConfirmed = {{ isset($whatsappData['connected']) && $whatsappData['connected'] ? 'true' : 'false' }};

    function toggleInputs(prefix) {
        const isCreditOnly = document.getElementById(prefix + '_credit_only').checked;
        const generalDiv = document.getElementById(prefix + '_general_div');
        const creditDiv = document.getElementById(prefix + '_credit_div');

        if (isCreditOnly) {
            $(generalDiv).slideUp(200);
            $(creditDiv).slideDown(200);
        } else {
            $(creditDiv).slideUp(200);
            $(generalDiv).slideDown(200);
        }
    }

    function checkWhatsApp() {
        $.get("{{ route('store.whatsapp.status') }}").done(function(res) {
            $('#wa_loading').hide(); 
            if (res.connected || res.is_authenticated) {
                $('#wa_qr').hide(); $('#wa_connected').show(); $('#wa_error').hide();
                if(res.user) {
                    let cleanNumber = (res.user.id || '').split(':')[0];
                    $('#wa_number').text(cleanNumber);
                    $('#wa_name').text(res.user.name || 'مستخدم واتساب');
                }
                isWhatsAppConfirmed = true; enforceNotificationRules(true);
                $('#wa_status_badge').text('متصل').removeClass().addClass('badge bg-success text-white');
            } else {
                $('#wa_connected').hide(); $('#wa_qr').show(); $('#wa_error').hide();
                if (!res.qr) {
                    if (!document.getElementById("qrcode_canvas").innerHTML.trim()) {
                        document.getElementById("qrcode_canvas").innerHTML = "<div class='text-muted small py-3'><i class='fas fa-sync fa-spin'></i> جاري التجهيز...</div>";
                    }
                } else if (res.qr !== lastQrCode) {
                    document.getElementById("qrcode_canvas").innerHTML = "";
                    new QRCode(document.getElementById("qrcode_canvas"), { text: res.qr, width: 160, height: 160, correctLevel : QRCode.CorrectLevel.M });
                    lastQrCode = res.qr;
                }
                isWhatsAppConfirmed = false; enforceNotificationRules(false);
                $('#wa_status_badge').text('غير متصل').removeClass().addClass('badge bg-danger text-white');
            }
        });
    }

    function enforceNotificationRules(connected) {
        const waWarning = document.querySelector('.wa-warning-msg');
        const waContainer = document.getElementById('wa_notify_container');
        if (connected) {
            if(waContainer) { waContainer.classList.remove('bg-light'); waContainer.classList.add('bg-success', 'bg-opacity-10'); }
            if(waWarning) waWarning.style.display = 'none';
        } else {
            if(waContainer) { waContainer.classList.remove('bg-success', 'bg-opacity-10'); waContainer.classList.add('bg-light'); }
            if(waWarning) waWarning.style.display = 'block';
        }
    }

    function logoutWhatsApp() {
        if(!confirm('هل أنت متأكد من فك ارتباط الواتساب؟')) return;
        $('#wa_connected').hide(); $('#wa_loading').show();
        $.post("{{ route('store.whatsapp.logout') }}", { _token: '{{ csrf_token() }}' })
        .always(function() { lastQrCode = null; document.getElementById("qrcode_canvas").innerHTML = ""; setTimeout(checkWhatsApp, 1000); });
    }

    $(document).ready(function() {
        enforceNotificationRules(isWhatsAppConfirmed);
        checkWhatsApp();
        setInterval(checkWhatsApp, 5000);
    });
</script>
@endsection
