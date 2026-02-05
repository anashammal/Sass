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
                                    <input type="file" name="logo" class="form-control" onchange="previewImage(this, 'preview_logo', 'placeholder_logo')">
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
                                    <input type="file" name="stamp" class="form-control" onchange="previewImage(this, 'preview_stamp', 'placeholder_stamp')">
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
                                    <input type="file" name="signature" class="form-control" onchange="previewImage(this, 'preview_signature', 'placeholder_sign')">
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

@section('scripts')
{{-- مكتبات ضرورية --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // تعريف المتغيرات العامة
    let lastQrCode = null;
    let isWhatsAppConfirmed = {{ $store->is_whatsapp_linked ? 'true' : 'false' }};

    // ================= 1. دوال الساعة والصور =================
    function toggleClockThemes() {
        // ✅ التعديل هنا: استخدام المعرف الصحيح (t_dig)
        const dig = document.getElementById('t_dig');
        if(dig && dig.checked) {
            document.getElementById('digital_themes').style.display = 'block';
            document.getElementById('analog_themes').style.display = 'none';
        } else {
            document.getElementById('digital_themes').style.display = 'none';
            document.getElementById('analog_themes').style.display = 'block';
        }
    }

    function previewImage(input, imgId, placeholderId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if(document.getElementById(placeholderId)) {
                    document.getElementById(placeholderId).style.display = 'none';
                }
                var img = document.getElementById(imgId);
                img.style.display = 'block';
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

// ================= 2. منطق الإشعارات (تعديل: عدم قفل الأزرار) =================
    function enforceNotificationRules(isConnected) {
        const waWarning = document.querySelector('.wa-warning-msg');
        const waContainer = document.getElementById('wa_notify_container');

        if (isConnected) {
            // ✅ متصل: شكل أخضر
            if(waContainer) {
                waContainer.classList.remove('bg-light');
                waContainer.classList.add('bg-success', 'bg-opacity-10');
            }
            if(waWarning) waWarning.style.display = 'none';

        } else {
            // ❌ غير متصل: شكل رمادي وتنبيه (لكن الزر يعمل)
            if(waContainer) {
                waContainer.classList.remove('bg-success', 'bg-opacity-10');
                waContainer.classList.add('bg-light');
            }
            if(waWarning) waWarning.style.display = 'block';
        }
    }

    // ================= 3. فحص حالة الواتساب =================
    function checkWhatsApp() {
    $.ajax({
    url: "{{ route('store.whatsapp.status') }}", // ✅ تم إزالة false ليستخدم الرابط الكامل الصحيح
    method: "GET",
    dataType: "json",
    cache: false
})

    .done(function(res) {
            // ✅ ضمان أن res كائن JSON وليس نص
        if (typeof res === 'string') {
            try {
                res = JSON.parse(res);
            } catch (e) {
                console.error("WhatsApp status is not valid JSON:", res);
                $('#wa_loading').hide();
                $('#wa_qr').hide();
                $('#wa_connected').hide();
                $('#wa_error').show();
                $('#wa_status_badge').text('غير متاح')
                    .removeClass().addClass('badge bg-danger text-white');
                return;
            }
        }


        // ✅ أي نجاح HTTP => اخفِ اللودينغ (لا تربطها بـ connected/qr)
        $('#wa_loading').hide();
        $('#wa_error').hide();

        // ✅ لو السيرفر رجّع error لكن 200 (احتياط)
        if (res && res.error) {
            $('#wa_error').show();
            $('#wa_status_badge').text('غير متاح').removeClass().addClass('badge bg-danger text-white');
            return;
        }

        if (res.connected || res.is_authenticated) {

            // 🟢 متصل
            $('#wa_qr').hide();
            $('#wa_connected').show();
            $('#wa_status_badge').text('متصل').removeClass().addClass('badge bg-success text-white');

            if(res.user) {
                $('#wa_number').text(res.user.id || '...');
                $('#wa_name').text(res.user.name || 'WhatsApp');
            }
            return;
        }

        // 🟡 غير متصل
        $('#wa_connected').hide();
        $('#wa_qr').show();
        $('#wa_status_badge').text('بانتظار المسح').removeClass().addClass('badge bg-warning text-dark');

        // ✅ أهم إصلاح: إذا qr=null لا تعتبرها مشكلة
        if (!res.qr) {
            document.getElementById("qrcode_canvas").innerHTML =
                "<div class='text-muted small'>جارٍ تجهيز QR... انتظر ثواني</div>";
            lastQrCode = null;
            return;
        }

        // رسم الـ QR عند تغيّره فقط
        if (res.qr !== lastQrCode) {
            document.getElementById("qrcode_canvas").innerHTML = "";
            new QRCode(document.getElementById("qrcode_canvas"), {
                text: res.qr,
                width: 150,
                height: 150,
                correctLevel : QRCode.CorrectLevel.L
            });
            lastQrCode = res.qr;
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        // 🔥 إضافة تنبيه للتشخيص 🔥
        alert("خطأ الاتصال (Settings): " + jqXHR.status + " " + errorThrown + "\nالرابط: " + this.url);
        
        // ✅ fail يعني فعلاً غير متاح (503/500/timeout)
        $('#wa_loading').hide();
        $('#wa_qr').hide();
        $('#wa_connected').hide();
        $('#wa_error').show();
        $('#wa_status_badge').text('غير متاح').removeClass().addClass('badge bg-danger text-white');
    });
}


    function logoutWhatsApp() {
        if(!confirm('هل أنت متأكد من فك الارتباط؟')) return;
        $('#wa_connected').hide(); 
        $('#wa_loading').show();
        $.post("{{ route('store.whatsapp.logout') }}", { _token: '{{ csrf_token() }}' }) // ✅ رابط كامل

        .done(function() { 
            lastQrCode = null;
            isWhatsAppConfirmed = false;
            enforceNotificationRules(false);
            setTimeout(checkWhatsApp, 2000); 
        });
    }

    // ================= التشغيل =================
    document.addEventListener("DOMContentLoaded", function() {
        // تطبيق القواعد فوراً
        enforceNotificationRules(isWhatsAppConfirmed);
        toggleClockThemes();
        
        // تشغيل الفحص الدوري
        checkWhatsApp();
        setInterval(checkWhatsApp, 4000);
    });

    // --- قوقل ماب وإدارة المواقع ---
    let map, marker, autocomplete;

    function initMap() {
        const lat = parseFloat(document.getElementById("latitude").value) || 24.7136;
        const lng = parseFloat(document.getElementById("longitude").value) || 46.6753;
        const initialPos = { lat: lat, lng: lng };

        map = new google.maps.Map(document.getElementById("map"), {
            center: initialPos, zoom: (document.getElementById("latitude").value ? 15 : 12), mapTypeControl: false,
        });

        marker = new google.maps.Marker({
            position: initialPos, map: map, draggable: true, animation: google.maps.Animation.DROP,
        });

        marker.addListener("dragend", () => {
            const pos = marker.getPosition();
            updateCoords(pos.lat(), pos.lng());
            reverseGeocode(pos);
        });

        const addressInput = document.getElementById("address");
        autocomplete = new google.maps.places.Autocomplete(addressInput);
        autocomplete.bindTo("bounds", map);

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;
            if (place.geometry.viewport) map.fitBounds(place.geometry.viewport);
            else { map.setCenter(place.geometry.location); map.setZoom(17); }
            marker.setPosition(place.geometry.location);
            updateCoords(place.geometry.location.lat(), place.geometry.location.lng());
        });

        map.addListener("click", (e) => {
            marker.setPosition(e.latLng);
            updateCoords(e.latLng.lat(), e.latLng.lng());
            reverseGeocode(e.latLng);
        });
    }

    function updateCoords(lat, lng) {
        document.getElementById("latitude").value = lat;
        document.getElementById("longitude").value = lng;
    }

    function reverseGeocode(pos) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: pos }, (results, status) => {
            if (status === "OK" && results[0]) {
                document.getElementById("address").value = results[0].formatted_address;
            }
        });
    }

    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const currentPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    map.setCenter(currentPos); map.setZoom(17);
                    marker.setPosition(currentPos);
                    updateCoords(currentPos.lat, currentPos.lng);
                    reverseGeocode(currentPos);
                },
                () => alert('تنبيه: تم رفض الوصول لموقعك الحالي')
            );
        }
    }
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap"></script>
@endsection
@endsection