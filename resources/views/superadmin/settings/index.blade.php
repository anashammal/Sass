@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            {{-- ================= بطاقة 1: الإعدادات العامة وربط الواتساب ================= --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="fa fa-cogs"></i> إعدادات النظام العامة</h5>
                </div>

                <div class="card-body text-center p-5">
                    @if (session('success'))
                        <div class="alert alert-success"><i class="fa fa-check"></i> {{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> {{ session('error') }}</div>
                    @endif

                    {{-- بداية فورم الإعدادات --}}
                    <form method="POST" action="{{ route('superadmin.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-5">
                            <label class="form-label d-block fw-bold fs-5 mb-3">شعار النظام الافتراضي (System Default Logo)</label>
                            <p class="text-muted">سيظهر هذا الشعار للمتاجر التي لم ترفع شعارها الخاص.</p>
                            
                            {{-- منطقة المعاينة --}}
                            <div class="p-3 border rounded bg-light d-inline-block mb-3">
                                @if(isset($settings['system_default_logo']) && $settings['system_default_logo'])
                                    <img id="admin_preview" src="{{ asset('storage/' . $settings['system_default_logo']) }}" alt="System Logo" style="max-height: 120px;">
                                @else
                                    <img id="admin_preview" src="https://via.placeholder.com/150?text=No+Logo" alt="System Logo" style="max-height: 120px;">
                                @endif
                            </div>

                            <div class="input-group mb-3 w-75 mx-auto">
                                <input type="file" class="form-control" name="system_default_logo" onchange="previewAdminLogo(this)">
                                <label class="input-group-text">اختر ملف</label>
                            </div>
                        </div>

                        <hr>
                        {{-- ================= قسم بوابة واتساب ================= --}}
                        <div class="mb-4 text-start">
                            <h5 class="fw-bold mb-3"><i class="fab fa-whatsapp text-success me-2"></i> بوابة واتساب النظام (TechSys)</h5>
                            
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    
                                    {{-- 1. حالة الانتظار --}}
                                    <div id="wa_loading">
                                        <div class="spinner-border text-success mb-2" role="status"></div>
                                        <p class="text-muted small">جاري الاتصال بسيرفر الواتساب...</p>
                                    </div>

                                    {{-- 2. حالة الباركود (غير متصل) --}}
                                    <div id="wa_qr" style="display: none;">
                                        <div class="alert alert-warning d-inline-block px-4">
                                            <i class="fas fa-exclamation-circle me-1"></i> الجهاز غير مرتبط
                                        </div>
                                        <p class="small text-muted mb-3">افتح واتساب > الأجهزة المرتبطة > ربط جهاز</p>
                                        <div class="bg-white p-2 d-inline-block rounded border">
                                            <div id="qrcode_canvas"></div>
                                        </div>
                                    </div>

                                    {{-- 3. حالة المتصل --}}
                                    <div id="wa_connected" style="display: none;">
                                        <div class="alert alert-success d-inline-block px-4 py-3 rounded shadow-sm mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <h6 class="fw-bold mb-1">متصل بنجاح (TechSys)</h6>
                                                    <small dir="ltr" id="wa_number_display" class="d-block text-dark fw-bold">...</small>
                                                    <small id="wa_name_display" class="text-muted">...</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" onclick="logoutWhatsApp()" class="btn btn-outline-danger btn-sm px-4">
                                                <i class="fas fa-sign-out-alt me-1"></i> فك الارتباط
                                            </button>
                                        </div>
                                    </div>

                                    {{-- 4. حالة الخطأ --}}
                                    <div id="wa_error" style="display: none;">
                                        <span class="badge bg-danger p-2 mb-2">السيرفر متوقف</span>
                                        <p class="small text-muted">تأكد من تشغيل Node.js على المنفذ 3000</p>
                                    </div>

                                </div>
                            </div>
                        </div>
                        {{-- ================= نهاية قسم الواتساب ================= --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fa fa-save"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                    {{-- نهاية فورم الإعدادات --}}
                </div>
            </div>

            {{-- ================= بطاقة 2: التحكم بسيرفر الواتساب (منفصلة تماماً) ================= --}}
            <div class="card border-warning mb-4 shadow-sm">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-server me-2"></i> التحكم بسيرفر الواتساب
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        في حال توقف خدمة الواتساب عن جميع المتاجر، يمكنك ضغط هذا الزر.
                        <br>
                        <small class="text-danger fw-bold">* سيقوم النظام بإعادة تشغيل الخدمة وإرسال رسالة واتساب تلقائية لجميع أصحاب المتاجر تطلب منهم إعادة الربط.</small>
                    </p>
                    
                    {{-- فورم منفصل لإعادة التشغيل --}}
                    <form action="{{ route('super_admin.whatsapp.restart') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-dark" onclick="return confirm('هل أنت متأكد؟ سيتم إرسال رسائل لكل العملاء.')">
                            <i class="fas fa-sync fa-spin-hover me-2"></i> إعادة تشغيل السيرفر وإشعار العملاء
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- مكتبة الباركود (مهم جداً أن تكون هنا) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // متغير لمنع رسم الباركود مراراً وتكراراً
    let lastQrCode = null;

    // 1. معاينة الشعار
    function previewAdminLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { document.getElementById('admin_preview').src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 2. فحص حالة الواتساب
    function checkWhatsApp() {
        // نستخدم الراوت الجديد المخصص للإعدادات
        $.get("{{ route('superadmin.settings.whatsapp.status') }}")
        .done(function(res) {
            $('#wa_loading').hide(); 
            $('#wa_error').hide();

            if (res.connected) {
                // === حالة الاتصال ===
                $('#wa_qr').hide();
                $('#wa_connected').fadeIn();
                lastQrCode = null; // تصفير الباركود السابق

                // عرض البيانات (الاسم والرقم)
                if(res.user) {
                    let cleanNumber = res.user.id.split(':')[0]; // حذف الزوائد من الرقم
                    $('#wa_number_display').text(cleanNumber);
                    $('#wa_name_display').text(res.user.name || 'مستخدم واتساب');
                }
            } 
            else if (res.qr) {
                // === حالة الباركود ===
                $('#wa_connected').hide();
                $('#wa_qr').fadeIn();

                // رسم الباركود فقط إذا تغير (لتجنب إعادة الرسم كل 3 ثواني)
                if (res.qr !== lastQrCode) {
                    document.getElementById("qrcode_canvas").innerHTML = "";
                    new QRCode(document.getElementById("qrcode_canvas"), {
                        text: res.qr,
                        width: 160,
                        height: 160,
                        correctLevel : QRCode.CorrectLevel.L
                    });
                    lastQrCode = res.qr;
                }
            } 
            else {
                // حالة انتقالية (لم يتم الاتصال بعد ولم يتم توليد باركود)
                $('#wa_connected').hide();
                $('#wa_qr').hide();
                $('#wa_loading').show();
            }
        })
        .fail(function() {
            $('#wa_loading').hide();
            $('#wa_connected').hide(); 
            $('#wa_qr').hide();
            $('#wa_error').fadeIn(); // إظهار خطأ السيرفر
        });
    }

    // 3. فك الارتباط
    function logoutWhatsApp() {
        if(!confirm('هل أنت متأكد من فك ارتباط الرقم؟ ستتوقف الإشعارات فوراً.')) return;
        
        $('#wa_connected').hide(); 
        $('#wa_loading').show();
        
        $.post("{{ route('superadmin.settings.whatsapp.logout') }}", { _token: '{{ csrf_token() }}' })
        .done(function() { 
            // ننتظر قليلاً ثم نعيد الفحص ليظهر الباركود الجديد
            lastQrCode = null;
            setTimeout(checkWhatsApp, 3000); 
        })
        .fail(function() {
            alert('حدث خطأ أثناء تسجيل الخروج');
            checkWhatsApp();
        });
    }

    // التشغيل التلقائي
    $(document).ready(function() {
        checkWhatsApp();
        // الفحص كل 3 ثواني
        setInterval(checkWhatsApp, 3000);
    });
</script>
@endsection