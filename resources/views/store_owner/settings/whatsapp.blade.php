@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="fab fa-whatsapp me-2"></i> ربط واتساب المتجر</h5>
                    {{-- زر العودة للإعدادات --}}
                    <a href="{{ route('store.settings.index') }}" class="btn btn-sm btn-light text-success fw-bold">العودة للإعدادات</a>
                </div>

                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <p class="text-muted">عند ربط رقمك هنا، سيتم إرسال الفواتير والإشعارات لعملائك من رقمك مباشرة.</p>
                    </div>

                    {{-- 1. حالة الانتظار --}}
                    <div id="wa_loading">
                        <div class="spinner-border text-success mb-3" role="status"></div>
                        <p class="text-muted fw-bold">جاري الاتصال بسيرفر الواتساب...</p>
                    </div>

                    {{-- 2. حالة الباركود (غير متصل) --}}
                    <div id="wa_qr" style="display: none;">
                        <h5 class="fw-bold mb-3">امسح الكود لربط رقم المتجر</h5>
                        <div class="bg-white p-3 d-inline-block rounded border mb-3 shadow-sm">
                            <div id="qrcode_canvas"></div>
                        </div>
                        <ol class="text-start d-inline-block text-muted small">
                            <li>افتح واتساب في جوالك</li>
                            <li>اذهب إلى <b>الأجهزة المرتبطة</b> > <b>ربط جهاز</b></li>
                            <li>وجه الكاميرا نحو الباركود أعلاه</li>
                        </ol>
                    </div>

                    {{-- 3. حالة المتصل --}}
                    <div id="wa_connected" style="display: none;">
                        <div class="alert alert-success d-inline-block px-5 py-4 rounded shadow-sm mb-4">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4 class="fw-bold">متصل بنجاح!</h4>
                            <p class="mb-0 text-dark" dir="ltr" id="wa_number_display">...</p>
                            <small class="text-muted" id="wa_name_display">...</small>
                        </div>
                        <div>
                            <button onclick="logoutWhatsApp()" class="btn btn-outline-danger px-4">
                                <i class="fas fa-sign-out-alt me-2"></i> فك ارتباط الرقم
                            </button>
                        </div>
                    </div>

                    {{-- 4. حالة الخطأ --}}
                    <div id="wa_error" style="display: none;">
                        <div class="alert alert-danger">
                            <i class="fas fa-server me-2"></i> خدمة الواتساب غير متاحة حالياً. يرجى التواصل مع الإدارة.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- مكتبة الباركود --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    let lastQrCode = null;

    function checkWhatsApp() {
        // لاحظ استخدام راوت المتجر store.whatsapp.status
        $.get("{{ route('store.whatsapp.status') }}")
        .done(function(res) {
            $('#wa_loading').hide(); 
            $('#wa_error').hide();

            if (res.connected) {
                $('#wa_qr').hide();
                $('#wa_connected').fadeIn();
                lastQrCode = null;

                if(res.user) {
                    $('#wa_number_display').text(res.user.id.split(':')[0]);
                    $('#wa_name_display').text(res.user.name || 'WhatsApp User');
                }
            } else if (res.qr) {
                $('#wa_connected').hide();
                $('#wa_qr').fadeIn();

                if (res.qr !== lastQrCode) {
                    document.getElementById("qrcode_canvas").innerHTML = "";
                    new QRCode(document.getElementById("qrcode_canvas"), {
                        text: res.qr, width: 200, height: 200
                    });
                    lastQrCode = res.qr;
                }
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX Fail: ", textStatus, errorThrown);
            $('#wa_loading').hide();
            $('#wa_error').fadeIn();
        });
    }

    function logoutWhatsApp() {
        if(!confirm('هل أنت متأكد؟ سيتوقف المتجر عن إرسال الفواتير واتساب.')) return;
        
        $('#wa_connected').hide(); 
        $('#wa_loading').show();
        
        $.post("{{ route('store.whatsapp.logout') }}", { _token: '{{ csrf_token() }}' })
        .done(function() { 
            setTimeout(checkWhatsApp, 3000); 
        });
    }

    $(document).ready(function() {
        checkWhatsApp();
        setInterval(checkWhatsApp, 4000); // فحص كل 4 ثواني
    });
</script>
@endsection