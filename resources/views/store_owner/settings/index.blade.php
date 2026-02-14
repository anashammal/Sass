@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-4 text-purple fw-bold">إعدادات المتجر والهوية البصرية</h3>
            
            @if (session('success'))
                <div class="alert alert-success shadow-sm border-0">
                    <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('store.settings.update', $store->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- 🔥 الصف الأول: ربط الواتساب (يمين) + إعدادات التنبيهات (يسار) 🔥 --}}
                <div class="row mb-4">
                    
                    {{-- 1. بطاقة ربط الواتساب --}}
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-whatsapp me-2"></i> ربط واتساب المتجر</span>
                                <span class="badge bg-white text-success small" id="wa_status_badge">جاري الفحص...</span>
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                {{-- حالات الاتصال --}}
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

                    {{-- 2. بطاقة الموقع الجغرافي (قوقل ماب) --}}
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-danger bg-gradient text-white fw-bold d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-map-marker-alt me-2"></i> موقع المتجر (قوقل ماب)</span>
                                <span class="badge bg-white text-danger small">تحديد الموقع</span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <div class="input-group shadow-sm border rounded-pill overflow-hidden bg-light" dir="ltr">
                                        <button type="button" class="btn btn-danger px-3" onclick="getCurrentLocation()" title="موقعي الحالي">
                                            <i class="fas fa-location-arrow"></i>
                                        </button>
                                        <input type="text" class="form-control border-0 bg-transparent" id="address" name="address" value="{{ old('address', $store->address) }}" 
                                               placeholder="ابحث عن عنوان المتجر..." dir="rtl">
                                    </div>
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $store->latitude) }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $store->longitude) }}">
                                </div>
                                <div id="map" class="rounded-3 shadow-inner border flex-grow-1" style="min-height: 200px; background: #f8f9fa;">
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                        <div class="text-center">
                                            <i class="fas fa-map-marked-alt fa-2x mb-2 opacity-25"></i><br>
                                            جاري تحميل الخريطة...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

{{-- ========================================================= --}}
{{-- 📧 القسم الأول: إعدادات البريد الإلكتروني (مستقل) --}}
{{-- ========================================================= --}}
<div class="card mt-4 shadow-sm border-primary">
    <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i> إعدادات البريد الإلكتروني</h5>
        <div class="form-check form-switch">
            {{-- مفتاح التشغيل الرئيسي للإيميل --}}
            <input class="form-check-input" type="checkbox" id="notify_email" name="notify_email" value="1" {{ $store->notify_email ? 'checked' : '' }} onchange="$('#email_body').slideToggle()">
            <label class="form-check-label text-white fw-bold ms-2" for="notify_email">تفعيل الخدمة</label>
        </div>
    </div>
    
    <div class="card-body bg-white" id="email_body" style="display: {{ $store->notify_email ? 'block' : 'none' }};">
        
        {{-- 1. فواتير المبيعات (إيميل) --}}
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between">
                <h6 class="fw-bold text-primary"><i class="fas fa-file-invoice me-1"></i> المبيعات</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="email_notify_sales" value="1" {{ $store->email_notify_sales ? 'checked' : '' }}>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    {{-- خيار الدين فقط --}}
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="email_sales_credit_only" name="email_sales_credit_only" value="1" {{ $store->email_sales_credit_only ? 'checked' : '' }} onchange="toggleInputs('email_sales')">
                        <label class="form-check-label text-danger small fw-bold" for="email_sales_credit_only">فواتير الدين فقط</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="email_sales_general_div" style="display: {{ $store->email_sales_credit_only ? 'none' : 'block' }};">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">الحد الأدنى للفاتورة</span>
                            <input type="number" step="0.01" class="form-control" name="email_sales_min" value="{{ $store->email_sales_min }}" placeholder="0">
                        </div>
                    </div>
                    <div id="email_sales_credit_div" style="display: {{ $store->email_sales_credit_only ? 'block' : 'none' }};">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-danger text-white">الحد الأدنى للدين</span>
                            <input type="number" step="0.01" class="form-control border-danger" name="email_sales_credit_min" value="{{ $store->email_sales_credit_min }}" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. فواتير المشتريات (إيميل) --}}
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between">
                <h6 class="fw-bold text-success"><i class="fas fa-truck-loading me-1"></i> المشتريات</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="email_notify_purchases" value="1" {{ $store->email_notify_purchases ? 'checked' : '' }}>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="email_purchases_credit_only" name="email_purchases_credit_only" value="1" {{ $store->email_purchases_credit_only ? 'checked' : '' }} onchange="toggleInputs('email_purchases')">
                        <label class="form-check-label text-danger small fw-bold" for="email_purchases_credit_only">فواتير الآجل فقط</label>
                    </div>
                </div>
                <div class="col-md-6">
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

        {{-- 3. خيارات أخرى (إيميل) --}}
        <div class="row text-center mt-3">
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
                    <label class="form-check-label small fw-bold">التقرير اليومي</label>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ========================================================= --}}
{{-- 💬 القسم الثاني: إعدادات الواتساب (مستقل) --}}
{{-- ========================================================= --}}
<div class="card mt-4 shadow-sm border-success">
    <div class="card-header bg-success bg-gradient text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fab fa-whatsapp me-2"></i> إعدادات الواتساب</h5>
        <div class="form-check form-switch">
            {{-- مفتاح التشغيل الرئيسي للواتساب --}}
            <input class="form-check-input" type="checkbox" id="notify_whatsapp" name="notify_whatsapp" value="1" {{ $store->notify_whatsapp ? 'checked' : '' }} onchange="$('#wa_body').slideToggle()">
            <label class="form-check-label text-white fw-bold ms-2" for="notify_whatsapp">تفعيل الخدمة</label>
        </div>
    </div>
    
    <div class="card-body bg-white" id="wa_body" style="display: {{ $store->notify_whatsapp ? 'block' : 'none' }};">
        
        {{-- 1. فواتير المبيعات (واتساب) --}}
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between">
                <h6 class="fw-bold text-primary"><i class="fas fa-file-invoice me-1"></i> المبيعات</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="wa_notify_sales" value="1" {{ $store->wa_notify_sales ? 'checked' : '' }}>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="wa_sales_credit_only" name="wa_sales_credit_only" value="1" {{ $store->wa_sales_credit_only ? 'checked' : '' }} onchange="toggleInputs('wa_sales')">
                        <label class="form-check-label text-danger small fw-bold" for="wa_sales_credit_only">فواتير الدين فقط</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="wa_sales_general_div" style="display: {{ $store->wa_sales_credit_only ? 'none' : 'block' }};">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">الحد الأدنى للفاتورة</span>
                            <input type="number" step="0.01" class="form-control" name="wa_sales_min" value="{{ $store->wa_sales_min }}" placeholder="0">
                        </div>
                    </div>
                    <div id="wa_sales_credit_div" style="display: {{ $store->wa_sales_credit_only ? 'block' : 'none' }};">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-danger text-white">الحد الأدنى للدين</span>
                            <input type="number" step="0.01" class="form-control border-danger" name="wa_sales_credit_min" value="{{ $store->wa_sales_credit_min }}" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. فواتير المشتريات (واتساب) --}}
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between">
                <h6 class="fw-bold text-success"><i class="fas fa-truck-loading me-1"></i> المشتريات</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="wa_notify_purchases" value="1" {{ $store->wa_notify_purchases ? 'checked' : '' }}>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="wa_purchases_credit_only" name="wa_purchases_credit_only" value="1" {{ $store->wa_purchases_credit_only ? 'checked' : '' }} onchange="toggleInputs('wa_purchases')">
                        <label class="form-check-label text-danger small fw-bold" for="wa_purchases_credit_only">فواتير الآجل فقط</label>
                    </div>
                </div>
                <div class="col-md-6">
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

        {{-- 3. خيارات أخرى (واتساب) --}}
        <div class="row text-center mt-3">
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
                    <label class="form-check-label small fw-bold">التقرير اليومي</label>
                </div>
            </div>
        </div>

        {{-- 4. خيار جديد: طلب التأكيد قبل الإرسال --}}
        <div class="border-top pt-3 mt-3 text-start">
            <div class="form-check form-switch px-0 ms-2">
                <input class="form-check-input ms-0 me-2" type="checkbox" name="whatsapp_auto_prompt" value="1" {{ $store->whatsapp_auto_prompt ? 'checked' : '' }}>
                <label class="form-check-label small fw-bold text-success">فتح نافذة تأكيد الإرسال للعميل عند حفظ الفواتير</label>
            </div>
            <small class="text-muted d-block mt-1 ms-4 d-inline-block">عند تمكين هذا الخيار، سيظهر لك خيار إرسال الفاتورة عبر واتساب فور حفظها، مع إمكانية تعديل رقم العميل.</small>
        </div>
    </div>
</div>

{{-- وقت التقرير اليومي العام --}}
<div class="card mt-3 shadow-sm bg-light">
    <div class="card-body p-2 text-center">
        <label class="fw-bold small me-2"><i class="fas fa-clock"></i> توقيت إرسال التقرير اليومي:</label>
        <input type="time" name="daily_report_time" class="form-control form-control-sm d-inline-block text-center fw-bold border-dark" style="width: 120px;" 
               value="{{ $store->daily_report_time ? \Carbon\Carbon::parse($store->daily_report_time)->format('H:i') : '' }}">
        <small class="text-muted ms-2">(اتركه فارغاً لإيقاف التقرير)</small>
    </div>
</div>

<script>
    function toggleInputs(prefix) {
        // مثال: prefix = 'email_sales' أو 'wa_sales'
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
</script>

                {{-- الصف الثاني: الهوية والفوترة (كما هي) --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header bg-white text-primary fw-bold">
                                <i class="fa fa-paint-brush me-2"></i> الهوية البصرية
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">اسم المتجر</label>
                                    <input type="text" name="store_name_update" class="form-control" value="{{ $store->name }}">
                                </div>
                                {{-- رقم الهاتف للتنبيهات --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">رقم الهاتف للتنبيهات (مع الرمز الدولي)</label>
                                    <input type="text" name="phone_number" class="form-control" 
                                           value="{{ $store->phone_number }}" 
                                           placeholder="مثال: 9665xxxxxxxx">
                                    <small class="text-muted">هذا الرقم سيستقبل رسائل الواتساب.</small>
                                </div>

                                {{-- الإيميل --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">البريد الإلكتروني للمتجر</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="{{ $store->email }}" 
                                           placeholder="store@example.com">
                                </div>
                                <hr class="text-muted">
                                
                                <div class="mb-4 text-center">
                                    <label class="form-label fw-bold d-block">شعار المتجر</label>
                                    <div class="mb-2 p-3 border rounded bg-light d-inline-block position-relative">
                                        @if($store->logo_path)
                                            <img id="preview_logo" src="{{ $store->logo_url }}?t={{ time() }}" height="80" alt="Logo" style="mix-blend-mode: multiply;">
                                        @else
                                            <div class="text-muted p-4" id="placeholder_logo">لا يوجد شعار</div>
                                            <img id="preview_logo" src="" height="80" style="display:none; mix-blend-mode: multiply;">
                                        @endif
                                    </div>
                                    <div class="localized-file-wrapper">
                                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف الشعار') }}
                                        </button>
                                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                                        <input type="file" name="logo" class="form-control" onchange="previewImage(this, 'preview_logo', 'placeholder_logo'); updateFileName(this)">
                                    </div>
                                </div>

                                <div class="mb-4 text-center">
                                    <label class="form-label fw-bold d-block">الختم الإلكتروني</label>
                                    <div class="mb-2 p-3 border rounded bg-light d-inline-block">
                                        @if($store->stamp_path)
                                            <img id="preview_stamp" src="{{ $store->stamp_url }}?t={{ time() }}" height="100" alt="Stamp" style="mix-blend-mode: multiply;">
                                        @else
                                            <div class="text-muted p-4" id="placeholder_stamp">لا يوجد ختم</div>
                                            <img id="preview_stamp" src="" height="100" style="display:none; mix-blend-mode: multiply;">
                                        @endif
                                    </div>
                                    <div class="localized-file-wrapper">
                                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف الختم') }}
                                        </button>
                                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                                        <input type="file" name="stamp" class="form-control" onchange="previewImage(this, 'preview_stamp', 'placeholder_stamp'); updateFileName(this)">
                                    </div>
                                </div>

                                <div class="mb-3 text-center">
                                    <label class="form-label fw-bold d-block">التوقيع المعتمد</label>
                                    <div class="mb-2 p-3 border rounded bg-light d-inline-block">
                                        @if($store->signature_path)
                                            <img id="preview_signature" src="{{ $store->signature_url }}?t={{ time() }}" height="60" alt="Sign" style="mix-blend-mode: multiply;">
                                        @else
                                            <div class="text-muted p-4" id="placeholder_sign">لا يوجد توقيع</div>
                                            <img id="preview_signature" src="" height="60" style="display:none; mix-blend-mode: multiply;">
                                        @endif
                                    </div>
                                    <div class="localized-file-wrapper">
                                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف التوقيع') }}
                                        </button>
                                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                                        <input type="file" name="signature" class="form-control" onchange="previewImage(this, 'preview_signature', 'placeholder_sign'); updateFileName(this)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header bg-white text-danger fw-bold">
                                <i class="fa fa-file-invoice-dollar me-2"></i> بيانات الفوترة
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">الدولة التشغيلية</label>
                                    <select name="country_code" class="form-control">
                                        <option value="SA" {{ $store->country_code == 'SA' ? 'selected' : '' }}>السعودية 🇸🇦</option>
                                        <option value="TR" {{ $store->country_code == 'TR' ? 'selected' : '' }}>تركيا 🇹🇷</option>
                                        <option value="EG" {{ $store->country_code == 'EG' ? 'selected' : '' }}>مصر 🇪🇬</option>
                                        <option value="OTHER" {{ $store->country_code == 'OTHER' ? 'selected' : '' }}>أخرى</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">نظام الفوترة</label>
                                    <select name="invoice_mode" class="form-control bg-light">
                                        <option value="zatca_phase_1" {{ $store->invoice_mode == 'zatca_phase_1' ? 'selected' : '' }}>ZATCA</option>
                                        <option value="turkey_kdv" {{ $store->invoice_mode == 'turkey_kdv' ? 'selected' : '' }}>KDV</option>
                                        <option value="simple" {{ $store->invoice_mode == 'simple' ? 'selected' : '' }}>ضريبة مبسطة</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">الرقم الضريبي</label>
                                    <input type="text" name="tax_number" class="form-control" value="{{ $store->tax_number }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">نسب الضرائب المتاحة</label>
                                    <input type="text" name="tax_rates" class="form-control" 
                                           value="{{ old('tax_rates', auth()->user()->store->tax_rates ?? '0,15') }}" 
                                           placeholder="0,5,15">
                                </div>

                                <hr>
                                                                <div class="mb-3">
                                     <h6 class="text-primary fw-bold mb-3"><i class="fa fa-university me-2"></i> بيانات الحساب البنكي</h6>
                                     <div class="row g-2">
                                         <div class="col-md-4 mb-2">
                                             <label class="form-label">دولة البنك</label>
                                             <select name="bank_country" id="bank_country_select" class="form-select"></select>
                                         </div>
                                         <div class="col-md-4 mb-2">
                                             <label class="form-label">اسم البنك</label>
                                             <select name="iban_bank_name" id="bank_name_select" class="form-select">
                                                 <option value="">اختر دولة البنك أولاً...</option>
                                             </select>
                                             <input type="text" name="bank_name_manual" id="bank_name_manual" class="form-control d-none mt-2" placeholder="اكتب اسم البنك يدوياً">
                                         </div>
                                         <div class="col-md-4 mb-2">
                                             <label class="form-label">اسم صاحب الحساب</label>
                                             <input type="text" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $store->bank_account_holder) }}" placeholder="الاسم كما يظهر في البنك">
                                         </div>
                                         <div class="col-12">
                                             <label class="form-label">رقم الآيبان (IBAN)</label>
                                             <div class="input-group" dir="ltr">
                                                 <span class="input-group-text fw-bold" id="iban_prefix" style="min-width: 50px; justify-content: center; background: #e9ecef; font-family: monospace; font-size: 16px;">--</span>
                                                 <input type="text" name="iban" id="iban_input" class="form-control fw-bold" 
                                                        value="{{ old('iban', $store->iban) }}"
                                                        placeholder="Enter IBAN number"
                                                        dir="ltr"
                                                        style="letter-spacing: 2px; font-size: 15px; font-family: monospace;">
                                             </div>
                                             <div class="form-text" id="iban_hint">اختر دولة البنك لمعرفة صيغة الآيبان الصحيحة</div>
                                         </div>
                                     </div>
                                     <div class="mt-2">
                                         <div class="alert alert-info py-2 px-3 small border-0 shadow-none">
                                             <i class="fa fa-info-circle me-1"></i> هذه البيانات تظهر للعملاء في الفواتير لتسهيل التحويل البنكي اليدوي.
                                         </div>
                                     </div>
                                 </div>
                                </div>

                                {{-- ✅ قائمة التوقيت الجديدة والشاملة ✅ --}}
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">توقيت النظام (Timezone)</label>
                                    <select name="timezone" class="form-select" dir="ltr"> {{-- dir="ltr" لعرض الوقت بشكل صحيح --}}
                                        @php
                                            $currentTz = auth()->user()->store->timezone ?? 'Asia/Riyadh';
                                            
                                            // قائمة شاملة بالتوقيت العالمي
                                            $timezones = [
                                                'Pacific/Midway'       => '(GMT-11:00) ميدواي، ساموا',
                                                'America/Adak'         => '(GMT-10:00) هاواي-ألوتيان',
                                                'Pacific/Honolulu'     => '(GMT-10:00) هاواي',
                                                'America/Anchorage'    => '(GMT-09:00) ألاسكا',
                                                'America/Los_Angeles'  => '(GMT-08:00) توقيت المحيط الهادئ (الولايات المتحدة وكندا)',
                                                'America/Denver'       => '(GMT-07:00) التوقيت الجبلي (الولايات المتحدة وكندا)',
                                                'America/Chicago'      => '(GMT-06:00) التوقيت المركزي (الولايات المتحدة وكندا)',
                                                'America/New_York'     => '(GMT-05:00) التوقيت الشرقي (الولايات المتحدة وكندا)',
                                                'America/Caracas'      => '(GMT-04:00) كاراكاس، لاباز',
                                                'America/Santiago'     => '(GMT-04:00) سانتياغو',
                                                'America/St_Johns'     => '(GMT-03:30) نيوفاوندلاند',
                                                'America/Sao_Paulo'    => '(GMT-03:00) برازيليا',
                                                'America/Argentina/Buenos_Aires' => '(GMT-03:00) بوينس آيرس، جورج تاون',
                                                'Atlantic/Azores'      => '(GMT-01:00) جزر الأزور',
                                                'Europe/London'        => '(GMT+00:00) لندن، دبلن، لشبونة (توقيت جرينتش)',
                                                'Africa/Casablanca'    => '(GMT+00:00) الدار البيضاء، مونروفيا',
                                                'Europe/Paris'         => '(GMT+01:00) أمستردام، برلين، روما، باريس، مدريد',
                                                'Africa/Lagos'         => '(GMT+01:00) غرب وسط أفريقيا',
                                                'Africa/Cairo'         => '(GMT+02:00) القاهرة',
                                                'Europe/Kiev'          => '(GMT+02:00) هلسنكي، كييف، ريغا، صوفيا',
                                                'Asia/Amman'           => '(GMT+02:00) عمّان',
                                                'Asia/Beirut'          => '(GMT+02:00) بيروت',
                                                'Asia/Jerusalem'       => '(GMT+02:00) القدس',
                                                'Africa/Johannesburg'  => '(GMT+02:00) هراري، بريتوريا',
                                                'Asia/Baghdad'         => '(GMT+03:00) بغداد',
                                                'Asia/Riyadh'          => '(GMT+03:00) الكويت، الرياض، مكة المكرمة',
                                                'Europe/Istanbul'      => '(GMT+03:00) إسطنبول',
                                                'Europe/Moscow'        => '(GMT+03:00) موسكو، سان بطرسبرج',
                                                'Asia/Tehran'          => '(GMT+03:30) طهران',
                                                'Asia/Dubai'           => '(GMT+04:00) أبو ظبي، مسقط',
                                                'Asia/Baku'            => '(GMT+04:00) باكو',
                                                'Asia/Kabul'           => '(GMT+04:30) كابول',
                                                'Asia/Karachi'         => '(GMT+05:00) إسلام آباد، كراتشي',
                                                'Asia/Kolkata'         => '(GMT+05:30) تشيناي، كلكتا، مومباي، نيودلهي',
                                                'Asia/Kathmandu'       => '(GMT+05:45) كاتماندو',
                                                'Asia/Dhaka'           => '(GMT+06:00) دكا',
                                                'Asia/Bangkok'         => '(GMT+07:00) بانكوك، هانوي، جاكرتا',
                                                'Asia/Hong_Kong'       => '(GMT+08:00) بكين، هونغ كونغ، سنغافورة، كوالالمبور',
                                                'Asia/Tokyo'           => '(GMT+09:00) أوساكا، سابورو، طوكيو',
                                                'Asia/Seoul'           => '(GMT+09:00) سيول',
                                                'Australia/Darwin'     => '(GMT+09:30) داروين',
                                                'Australia/Sydney'     => '(GMT+10:00) كانبيرا، ملبورن، سيدني',
                                                'Pacific/Guadalcanal'  => '(GMT+11:00) جزر سليمان، كاليدونيا الجديدة',
                                                'Pacific/Auckland'     => '(GMT+12:00) أوكلاند، ويلينغتون',
                                            ];
                                        @endphp

                                        @foreach($timezones as $tz => $label)
                                            <option value="{{ $tz }}" {{ $currentTz == $tz ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">سيتم تطبيق هذا التوقيت على كافة الفواتير والتقارير في متجرك.</small>
                                </div>

                                {{-- ثيمات الساعة --}}
                                <div class="card mb-4 mt-4 border-primary shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0 fw-bold"><i class="fas fa-palette me-2"></i> شكل الساعة</h6>
                                    </div>
                                    <div class="card-body bg-white text-center">
                                        <div class="btn-group mb-3" role="group">
                                            {{-- ✅ تصحيح المعرفات IDs لتعمل مع الجافاسكربت --}}
                                            <input type="radio" class="btn-check" name="clock_type" id="t_dig" value="digital" {{ ($store->clock_type ?? 'digital') == 'digital' ? 'checked' : '' }} onchange="toggleClockThemes()">
                                            <label class="btn btn-outline-primary" for="t_dig">رقمية</label>

                                            <input type="radio" class="btn-check" name="clock_type" id="t_ana" value="analog" {{ ($store->clock_type ?? 'digital') == 'analog' ? 'checked' : '' }} onchange="toggleClockThemes()">
                                            <label class="btn btn-outline-primary" for="t_ana">عقارب</label>
                                        </div>

                                        {{-- 1. الرقمية --}}
                                        <div id="digital_themes" style="display: {{ ($store->clock_type ?? 'digital') == 'digital' ? 'block' : 'none' }}">
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <label class="clock-card w-100 cursor-pointer">
                                                        <input type="radio" name="clock_theme" value="modern_white" class="d-none" {{ ($store->clock_theme ?? 'default') == 'modern_white' ? 'checked' : '' }}>
                                                        <div class="p-2 rounded border text-center shadow-sm" style="background: #1c1c1e; color: #ff9f0a; border: 1px solid #333 !important;">
                                                            <div class="fw-bold">09:41</div><small style="font-size:0.6rem">Ultra</small>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-4">
                                                    <label class="clock-card w-100 cursor-pointer">
                                                        <input type="radio" name="clock_theme" value="galaxy" class="d-none" {{ ($store->clock_theme ?? 'default') == 'galaxy' ? 'checked' : '' }}>
                                                        <div class="p-2 rounded border text-center text-white shadow-sm" style="background: linear-gradient(135deg, #4158D0 0%, #C850C0 100%);">
                                                            <div class="fw-bold">09:41</div><small style="font-size:0.6rem">Galaxy</small>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-4">
                                                    <label class="clock-card w-100 cursor-pointer">
                                                        <input type="radio" name="clock_theme" value="hud" class="d-none" {{ ($store->clock_theme ?? 'default') == 'hud' ? 'checked' : '' }}>
                                                        <div class="p-2 rounded border text-center shadow-sm" style="background: #000; color: #00ff41; border: 1px solid #00ff41 !important;">
                                                            <div class="fw-bold">09:41</div><small style="font-size:0.6rem">HUD</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- 2. العقارب --}}
                                        <div id="analog_themes" style="display: {{ ($store->clock_type ?? 'digital') == 'analog' ? 'block' : 'none' }}">
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <label class="clock-card w-100 cursor-pointer d-flex flex-column align-items-center">
                                                        <input type="radio" name="clock_theme" value="royal_gold" class="d-none" {{ ($store->clock_theme ?? 'default') == 'royal_gold' ? 'checked' : '' }}>
                                                        <div class="rounded-circle shadow d-flex justify-content-center align-items-center mb-1" style="width: 40px; height: 40px; background: #111; border: 2px solid #d4af37;">
                                                            <div style="width: 2px; height: 12px; background: #d4af37; transform: rotate(45deg);"></div>
                                                        </div>
                                                        <span style="font-size:0.7rem">Gold</span>
                                                    </label>
                                                </div>
                                                <div class="col-4">
                                                    <label class="clock-card w-100 cursor-pointer d-flex flex-column align-items-center">
                                                        <input type="radio" name="clock_theme" value="ocean" class="d-none" {{ ($store->clock_theme ?? 'default') == 'ocean' ? 'checked' : '' }}>
                                                        <div class="rounded-circle shadow d-flex justify-content-center align-items-center mb-1" style="width: 40px; height: 40px; background: radial-gradient(circle, #0f2027, #203a43, #2c5364); border: 1px solid white;">
                                                            <div style="width: 2px; height: 12px; background: #00d2ff; transform: rotate(90deg);"></div>
                                                        </div>
                                                        <span style="font-size:0.7rem">Ocean</span>
                                                    </label>
                                                </div>
                                                <div class="col-4">
                                                    <label class="clock-card w-100 cursor-pointer d-flex flex-column align-items-center">
                                                        <input type="radio" name="clock_theme" value="sport_red" class="d-none" {{ ($store->clock_theme ?? 'default') == 'sport_red' ? 'checked' : '' }}>
                                                        <div class="rounded-circle shadow d-flex justify-content-center align-items-center mb-1" style="width: 40px; height: 40px; background: #f0f0f0; border: 2px solid #333;">
                                                            <div style="width: 2px; height: 12px; background: #d32f2f; transform: rotate(0deg);"></div>
                                                        </div>
                                                        <span style="font-size:0.7rem">Sport</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                </div>

                <div class="row mb-5">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-success btn-lg px-5 py-3 shadow-lg fw-bold rounded-pill">
                            <i class="fa fa-save me-2"></i> حفظ كافة الإعدادات والتغييرات
                        </button>
                    </div>
                </div>

                <style>
                    .pac-container {
                        z-index: 10000 !important;
                        border-radius: 12px !important;
                        box-shadow: 0 15px 45px rgba(0,0,0,0.2) !important;
                        border: none !important;
                        margin-top: 5px !important;
                        font-family: inherit;
                        padding: 5px;
                    }
                    .pac-item { padding: 12px; cursor: pointer; border: none !important; border-radius: 8px; }
                    .pac-item:hover { background-color: #fcebeb !important; }
                    .pac-item-query { font-size: 14px; color: #333; }
                    .pac-icon { display: none; }
                </style>
            </form>
        </div>
    </div>
</div>

<style>
    .clock-card input:checked + div { border: 2px solid blue !important; font-weight: bold; }
</style>

@endsection

@section('scripts')
{{-- لضمان عمل كافة المكونات بشكل صحيح وبدون تكرار للمكتبات --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // 1. تعريف المتغيرات العامة (Global Scope)
    let lastQrCode = null;
    let isWhatsAppConfirmed = {{ isset($whatsappData['connected']) && $whatsappData['connected'] ? 'true' : 'false' }};

    // --- نظام بيانات البنك والآيبان المطور ---
    const COUNTRIES_GLOBAL = [
        { code: "af", ar: "أفغانستان", en: "Afghanistan" }, { code: "al", ar: "ألبانيا", en: "Albania" },
        { code: "dz", ar: "الجزائر", en: "Algeria" }, { code: "ad", ar: "أندورا", en: "Andorra" },
        { code: "ao", ar: "أنغولا", en: "Angola" }, { code: "ar", ar: "الأرجنتين", en: "Argentina" },
        { code: "am", ar: "أرمينيا", en: "Armenia" }, { code: "au", ar: "أستراليا", en: "Australia" },
        { code: "at", ar: "النمسا", en: "Austria" }, { code: "az", ar: "أذربيجان", en: "Azerbaijan" },
        { code: "bh", ar: "البحرين", en: "Bahrain" }, { code: "bd", ar: "بنغلاديش", en: "Bangladesh" },
        { code: "by", ar: "بيلاروسيا", en: "Belarus" }, { code: "be", ar: "بلجيكا", en: "Belgium" },
        { code: "bj", ar: "بنين", en: "Benin" }, { code: "bo", ar: "بوليفيا", en: "Bolivia" },
        { code: "ba", ar: "البوسنة والهرسك", en: "Bosnia And Herzegovina" }, { code: "bw", ar: "بوتسوانا", en: "Botswana" },
        { code: "br", ar: "البرازيل", en: "Brazil" }, { code: "bn", ar: "بروناي", en: "Brunei" },
        { code: "bg", ar: "بلغاريا", en: "Bulgaria" }, { code: "kh", ar: "كمبوديا", en: "Cambodia" },
        { code: "cm", ar: "الكاميرون", en: "Cameroon" }, { code: "ca", ar: "كندا", en: "Canada" },
        { code: "cl", ar: "تشيلي", en: "Chile" }, { code: "cn", ar: "الصين", en: "China" },
        { code: "co", ar: "كولومبيا", en: "Colombia" }, { code: "cr", ar: "كوستاريكا", en: "Costa Rica" },
        { code: "hr", ar: "كرواتيا", en: "Croatia" }, { code: "cu", ar: "كوبا", en: "Cuba" },
        { code: "cy", ar: "قبرص", en: "Cyprus" }, { code: "cz", ar: "التشيك", en: "Czech Republic" },
        { code: "dk", ar: "الدانمرك", en: "Denmark" }, { code: "dj", ar: "جيبوتي", en: "Djibouti" },
        { code: "do", ar: "جمهورية الدومينيكان", en: "Dominican Republic" }, { code: "ec", ar: "الإكوادور", en: "Ecuador" },
        { code: "eg", ar: "مصر", en: "Egypt" }, { code: "sv", ar: "السلفادور", en: "El Salvador" },
        { code: "ee", ar: "إستونيا", en: "Estonia" }, { code: "et", ar: "إثيوبيا", en: "Ethiopia" },
        { code: "fj", ar: "فيجي", en: "Fiji" }, { code: "fi", ar: "فنلندا", en: "Finland" },
        { code: "fr", ar: "فرنسا", en: "France" }, { code: "ge", ar: "جورجيا", en: "Georgia" },
        { code: "de", ar: "ألمانيا", en: "Germany" }, { code: "gh", ar: "غانا", en: "Ghana" },
        { code: "gr", ar: "اليونان", en: "Greece" }, { code: "gt", ar: "غواتيمالا", en: "Guatemala" },
        { code: "hn", ar: "هندوراس", en: "Honduras" }, { code: "hk", ar: "هونغ كونغ", en: "Hong Kong" },
        { code: "hu", ar: "المجر", en: "Hungary" }, { code: "is", ar: "آيسلندا", en: "Iceland" },
        { code: "in", ar: "الهند", en: "India" }, { code: "id", ar: "إندونيسيا", en: "Indonesia" },
        { code: "ir", ar: "إيران", en: "Iran" }, { code: "iq", ar: "العراق", en: "Iraq" },
        { code: "ie", ar: "أيرلندا", en: "Ireland" }, { code: "il", ar: "إسرائيل", en: "Israel" },
        { code: "it", ar: "إيطاليا", en: "Italy" }, { code: "jm", ar: "جامايكا", en: "Jamaica" },
        { code: "jp", ar: "اليابان", en: "Japan" }, { code: "jo", ar: "الأردن", en: "Jordan" },
        { code: "kz", ar: "كازاخستان", en: "Kazakhstan" }, { code: "ke", ar: "كينيا", en: "Kenya" },
        { code: "kw", ar: "الكويت", en: "Kuwait" }, { code: "kg", ar: "قيرغيزستان", en: "Kyrgyzstan" },
        { code: "la", ar: "لاوس", en: "Laوس" }, { code: "lv", ar: "لاتفيا", en: "Latvia" },
        { code: "lb", ar: "لبنان", en: "Lebanon" }, { code: "ly", ar: "ليبيا", en: "Libya" },
        { code: "lt", ar: "ليتوانيا", en: "Lithuania" }, { code: "lu", ar: "لوكسمبورغ", en: "Luxembourg" },
        { code: "my", ar: "ماليزيا", en: "Malaysia" }, { code: "mv", ar: "المالديف", en: "Maldives" },
        { code: "mt", ar: "مالطا", en: "Malta" }, { code: "mx", ar: "المكسيك", en: "Mexico" },
        { code: "md", ar: "مولدوفا", en: "Moldova" }, { code: "mc", ar: "موناكو", en: "Monaco" },
        { code: "mn", ar: "منغوليا", en: "Mongolia" }, { code: "me", ar: "الجبل الأسود", en: "Montenegro" },
        { code: "ma", ar: "المغرب", en: "Morocco" }, { code: "mm", ar: "ميانمار", en: "Myanmar" },
        { code: "np", ar: "نيبال", en: "Nepal" }, { code: "nl", ar: "هولندا", en: "Netherlands" },
        { code: "nz", ar: "نيوزيلندا", en: "New Zealand" }, { code: "ni", ar: "نيكاراغوا", en: "Nicaragua" },
        { code: "ng", ar: "نيجيريا", en: "Nigeria" }, { code: "no", ar: "النرويج", en: "Norway" },
        { code: "om", ar: "عُمان", en: "Oman" }, { code: "pk", ar: "باكستان", en: "Pakistan" },
        { code: "ps", ar: "فلسطين", en: "Palestine" }, { code: "pa", ar: "بنما", en: "Panama" },
        { code: "py", ar: "باراغواي", en: "Paraguay" }, { code: "pe", ar: "بيرو", en: "Peru" },
        { code: "ph", ar: "الفلبين", en: "Philippines" }, { code: "pl", ar: "بولندا", en: "Poland" },
        { code: "pt", ar: "البرتغال", en: "Portugal" }, { code: "qa", ar: "قطر", en: "Qatar" },
        { code: "ro", ar: "رومانيا", en: "Romania" }, { code: "ru", ar: "روسيا", en: "Russia" },
        { code: "rw", ar: "رواندا", en: "Rwanda" }, { code: "sa", ar: "السعودية", en: "Saudi Arabia" },
        { code: "sn", ar: "السنغال", en: "Senegal" }, { code: "rs", ar: "صربيا", en: "Serbia" },
        { code: "sg", ar: "سنغافورة", en: "Singapore" }, { code: "sk", ar: "سلوفاكيا", en: "Slovakia" },
        { code: "si", ar: "سلوفينيا", en: "Slovenia" }, { code: "so", ar: "الصومال", en: "Somalia" },
        { code: "za", ar: "جنوب أفريقيا", en: "South Africa" }, { code: "kr", ar: "كوريا الجنوبية", en: "South Korea" },
        { code: "ss", ar: "جنوب السودان", en: "South Sudan" }, { code: "es", ar: "إسبانيا", en: "Spain" },
        { code: "lk", ar: "سريلانكا", en: "Sri Lanka" }, { code: "sd", ar: "السودان", en: "Sudan" },
        { code: "se", ar: "السويد", en: "Sweden" }, { code: "ch", ar: "سويسرا", en: "Switzerland" },
        { code: "sy", ar: "سوريا", en: "Syria" }, { code: "tw", ar: "تايوان", en: "Taiwan" },
        { code: "tj", ar: "طاجيكستان", en: "Tajikistan" }, { code: "tz", ar: "تنزانيا", en: "Tanzania" },
        { code: "th", ar: "تايلاند", en: "Thailand" }, { code: "tn", ar: "تونس", en: "Tunisia" },
        { code: "tr", ar: "تركيا", en: "Turkey" }, { code: "tm", ar: "تركمانستان", en: "Turkmenistan" },
        { code: "ug", ar: "أوغندا", en: "Uganda" }, { code: "ua", ar: "أوكرانيا", en: "Ukraine" },
        { code: "ae", ar: "الإمارات", en: "United Arab Emirates" }, { code: "gb", ar: "المملكة المتحدة", en: "United Kingdom" },
        { code: "us", ar: "الولايات المتحدة", en: "United States" }, { code: "uy", ar: "أوروغواي", en: "Uruguay" },
        { code: "uz", ar: "أوزبكستان", en: "Uzbekistan" }, { code: "ve", ar: "فنزويلا", en: "Venezuela" },
        { code: "vn", ar: "فيتنام", en: "Vietnam" }, { code: "ye", ar: "اليمن", en: "Yemen" },
        { code: "zm", ar: "زامبيا", en: "Zambia" }, { code: "zw", ar: "زيمبابوي", en: "Zimbabwe" }
    ];

    const BANK_DATA_GLOBAL = {
        'SA': { banks: ['البنك الأهلي السعودي', 'مصرف الراجحي', 'بنك الرياض', 'البنك السعودي الفرنسي', 'البنك السعودي البريطاني (ساب)', 'بنك البلاد', 'بنك الجزيرة', 'بنك الإنماء', 'البنك العربي الوطني', 'STC Pay'] },
        'AE': { banks: ['بنك أبوظبي الأول', 'بنك الإمارات دبي الوطني', 'بنك دبي الإسلامي', 'مصرف أبوظبي الإسلامي', 'بنك المشرق', 'بنك رأس الخيمة الوطني'] },
        'TR': { banks: ['Ziraat Bankası', 'İş Bankası', 'Garanti BBVA', 'Yapı Kredi', 'Akbank', 'Halkbank', 'VakıfBank', 'QNB Finansbank', 'Denizbank', 'TEB', 'Kuveyt Türk', 'Albaraka Türk', 'PTT Bank'] },
        'EG': { banks: ['البنك الأهلي المصري', 'بنك مصر', 'بنك القاهرة', 'البنك التجاري الدولي CIB', 'بنك الإسكندرية', 'البنك العربي الأفريقي', 'بنك QNB الأهلي'] },
        'JO': { banks: ['البنك العربي', 'بنك الإسكان', 'البنك الأهلي الأردني', 'بنك الأردن', 'البنك الإسلامي الأردني', 'بنك القاهرة عمان'] },
        'KW': { banks: ['بنك الكويت الوطني', 'بيت التمويل الكويتي', 'بنك برقان', 'البنك التجاري الكويتي', 'بنك الخليج', 'بنك بوبيان'] },
        'IQ': { banks: ['مصرف الرافدين', 'مصرف الرشيد', 'البنك التجاري العراقي', 'البنك الأهلي العراقي', 'مصرف بغداد'] },
    };

    const IBAN_LENGTHS_GLOBAL = {
        'AL':28,'AD':24,'AT':20,'AZ':28,'BH':22,'BY':28,'BE':16,'BA':20,'BR':29,'BG':22,
        'CR':22,'HR':21,'CY':28,'CZ':24,'DK':18,'DO':28,'TL':23,'EE':20,'FO':18,'FI':18,
        'FR':27,'GE':22,'DE':22,'GI':23,'GR':27,'GL':18,'GT':28,'HU':28,'IS':26,'IQ':23,
        'IE':22,'IL':23,'IT':27,'JO':30,'KZ':20,'XK':20,'KW':30,'LV':21,'LB':28,'LI':21,
        'LT':20,'LU':20,'MK':19,'MT':31,'MR':27,'MU':30,'MC':27,'MD':24,'ME':22,'NL':18,
        'NO':15,'PK':24,'PS':29,'PL':28,'PT':25,'QA':29,'RO':24,'LC':32,'SM':27,'ST':25,
        'SA':24,'RS':22,'SC':31,'SK':24,'SI':19,'ES':24,'SD':18,'SE':24,'CH':21,'TN':24,
        'TR':26,'UA':29,'AE':23,'GB':22,'VA':22,'VG':24,'EG':29,'OM':23,'SY':24
    };

    function getCode(name) {
        if (!name) return ""; name = name.trim().toLowerCase();
        const f = COUNTRIES_GLOBAL.find(c => c.code.toLowerCase() === name || c.ar.toLowerCase() === name || c.en.toLowerCase() === name);
        return f ? f.code.toUpperCase() : "";
    }

    // --- 2. دوال الواتساب ---
    function checkWhatsApp() {
        $.get("{{ route('store.whatsapp.status') }}")
        .done(function(res) {
            $('#wa_loading').hide(); 
            if (res.connected || res.is_authenticated) {
                $('#wa_qr').hide(); $('#wa_connected').show(); $('#wa_error').hide();
                if(res.user) {
                    let cleanNumber = (res.user.id || '').split(':')[0];
                    $('#wa_number_display').text(cleanNumber);
                    $('#wa_name_display').text(res.user.name || 'مستخدم واتساب');
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
                    new QRCode(document.getElementById("qrcode_canvas"), { text: res.qr, width: 160, height: 160, correctLevel : QRCode.Level.M });
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

    function toggleClockThemes() {
        const dig = document.getElementById('t_dig');
        if(dig && dig.checked) {
            if(document.getElementById('digital_themes')) document.getElementById('digital_themes').style.display = 'block';
            if(document.getElementById('analog_themes')) document.getElementById('analog_themes').style.display = 'none';
        } else {
            if(document.getElementById('digital_themes')) document.getElementById('digital_themes').style.display = 'none';
            if(document.getElementById('analog_themes')) document.getElementById('analog_themes').style.display = 'block';
        }
    }

    function previewImage(input, imgId, placeholderId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if(document.getElementById(placeholderId)) document.getElementById(placeholderId).style.display = 'none';
                var img = document.getElementById(imgId); img.style.display = 'block'; img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // --- 3. قوقل ماب وإدارة المواقع ---
    let map, marker, autocomplete;
    function initMap() {
        const latEl = document.getElementById("latitude");
        const lngEl = document.getElementById("longitude");
        const lat = parseFloat(latEl ? latEl.value : 24.7136) || 24.7136;
        const lng = parseFloat(lngEl ? lngEl.value : 46.6753) || 46.6753;
        const initialPos = { lat: lat, lng: lng };
        map = new google.maps.Map(document.getElementById("map"), { center: initialPos, zoom: 12, mapTypeControl: false });
        marker = new google.maps.Marker({ position: initialPos, map: map, draggable: true });
        marker.addListener("dragend", () => { const pos = marker.getPosition(); updateCoords(pos.lat(), pos.lng()); reverseGeocode(pos); });
        const addressInput = document.getElementById("address");
        if(addressInput) {
            autocomplete = new google.maps.places.Autocomplete(addressInput);
            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace(); if (!place.geometry) return;
                map.setCenter(place.geometry.location); marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location.lat(), place.geometry.location.lng());
            });
        }
    }
    function updateCoords(lat, lng) { 
        if(document.getElementById("latitude")) document.getElementById("latitude").value = lat; 
        if(document.getElementById("longitude")) document.getElementById("longitude").value = lng; 
    }
    function reverseGeocode(pos) { 
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: pos }, (results, status) => { 
            if (status === "OK" && results[0] && document.getElementById("address")) document.getElementById("address").value = results[0].formatted_address; 
        }); 
    }

    // --- 4. التشغيل عند التحميل ---
    $(document).ready(function() {
        enforceNotificationRules(isWhatsAppConfirmed);
        toggleClockThemes();
        checkWhatsApp();
        setInterval(checkWhatsApp, 5000);

        const $bc = $('#bank_country_select');
        const $bn = $('#bank_name_select');
        const $ib = $('#iban_input');
        const $pr = $('#iban_prefix');
        const $hi = $('#iban_hint');

        // تعبئة الدول فوراً باستخدام جيكويري
        $bc.empty().append('<option value="">اختر دولة البنك...</option>');
        COUNTRIES_GLOBAL.forEach(c => {
            $bc.append($('<option>', { value: c.code.toUpperCase(), text: c.ar + ' - ' + c.en }).attr('data-ar', c.ar));
        });

        // تفعيل Select2 إذا كان موجوداً
        if ($.fn.select2) {
            $bc.select2({ theme: 'bootstrap-5', dir: 'rtl', width: '100%' });
            $bn.select2({ theme: 'bootstrap-5', dir: 'rtl', width: '100%' });
        }

        $bc.on('change', function() {
            const v = $(this).val();
            const ar = $(this).find('option:selected').attr('data-ar');
            const len = IBAN_LENGTHS_GLOBAL[v] || 24;
            if (v) {
                $pr.text(v);
                $hi.html(`صيغة الآيبان: <code dir="ltr">${v} + ${len-2} رقم/حرف</code>`);
                $bn.empty();
                const d = BANK_DATA_GLOBAL[v];
                if (d && d.banks) {
                    $bn.append('<option value="">اختر البنك...</option>');
                    d.banks.forEach(b => $bn.append(new Option(b, b)));
                    $bn.append('<option value="__other__">🏦 بنك آخر...</option>');
                    $bn.prop('disabled', false);
                    $('#bank_name_manual').addClass('d-none');
                } else {
                    $bn.append('<option value="">أدخل اسم البنك يدوياً</option>').prop('disabled', true);
                    $('#bank_name_manual').removeClass('d-none').attr('placeholder', 'اكتب اسم البنك في ' + (ar || 'هذه الدولة'));
                }
            }
        });

        $ib.on('input', function() {
            let v = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(v);
            const c = $bc.val();
            const exp = IBAN_LENGTHS_GLOBAL[c] || 24;
            const fullSize = (c ? c.length : 0) + v.length;
            if (c) {
                if (fullSize < exp) $hi.html(`<span class="text-warning">⚠️ قصير (${fullSize}/${exp})</span>`);
                else if (fullSize > exp) $hi.html(`<span class="text-danger">❌ طويل جداً (${fullSize}/${exp})</span>`);
                else $hi.html(`<span class="text-success">✅ الطول صحيح (${exp})</span>`);
            }
        });

        // تنظيف القيمة الابتدائية إذا كانت موجودة (حذف أي حروف بادئة مخزنة)
        if ($ib.val()) {
            $ib.trigger('input');
        }

        // استعادة البيانات المحفوظة
        const savedC = "{{ $store->bank_country }}";
        if (savedC) {
            let cC = savedC.toUpperCase();
            if (cC.length > 2) cC = getCode(savedC);
            if (cC) {
                $bc.val(cC).trigger('change');
                setTimeout(() => {
                    const savedB = "{{ $store->iban_bank_name }}";
                    if (savedB && savedB !== "null") {
                        if ($bn.find(`option[value="${savedB}"]`).length) $bn.val(savedB).trigger('change');
                        else { $bn.val('__other__').trigger('change'); $('#bank_name_manual').val(savedB); }
                    }
                }, 800);
            }
        }
    });
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap"></script>
@endsection