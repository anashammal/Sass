@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg mx-auto border-0" style="max-width: 500px; border-radius: 20px;">
        <div class="card-header bg-success text-white text-center py-3" style="border-radius: 20px 20px 0 0;">
            <h4 class="mb-0 fw-bold"><i class="fab fa-whatsapp me-2"></i> بوابة واتساب النظام</h4>
        </div>
        <div class="card-body text-center p-5">
            
            {{-- حالة الانتظار --}}
            <div id="loadingState">
                <div class="spinner-border text-success mb-3" role="status"></div>
                <p class="text-muted fw-bold">جاري الاتصال بسيرفر الواتساب...</p>
            </div>

            {{-- حالة عدم الاتصال (عرض الباركود) --}}
            <div id="qrState" style="display: none;">
                <h5 class="fw-bold mb-3">ربط الجهاز</h5>
                <p class="text-muted small mb-4">افتح واتساب في هاتفك > الأجهزة المرتبطة > ربط جهاز<br>وامسح الكود أدناه:</p>
                
                <div class="d-flex justify-content-center mb-4">
                    <div id="qrcode" class="p-2 border rounded"></div>
                </div>
                
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle"></i> سيتم تحديث الصفحة تلقائياً عند نجاح الربط.
                </div>
            </div>

            {{-- حالة الاتصال الناجح --}}
            <div id="connectedState" style="display: none;">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success fa-5x mb-3 animate__animated animate__bounceIn"></i>
                    <h3 class="fw-bold text-success">متصل بنجاح!</h3>
                    <p class="text-muted">النظام جاهز لإرسال الإشعارات.</p>
                </div>
                
                <div class="bg-light p-3 rounded mb-4 text-start">
                    <strong><i class="fas fa-user me-2"></i> الاسم:</strong> <span id="waName">-</span><br>
                    <strong><i class="fas fa-phone me-2"></i> الرقم:</strong> <span id="waPhone" dir="ltr">-</span>
                </div>

                <button onclick="logoutWhatsApp()" class="btn btn-outline-danger w-100 py-2 fw-bold">
                    <i class="fas fa-sign-out-alt me-2"></i> فك الارتباط (تسجيل خروج)
                </button>
            </div>

            {{-- حالة الخطأ --}}
            <div id="errorState" style="display: none;">
                <i class="fas fa-server text-danger fa-3x mb-3"></i>
                <h5 class="text-danger fw-bold">خدمة الواتساب متوقفة!</h5>
                <p class="text-muted small">يرجى تشغيل سيرفر Node.js على المنفذ 3000.</p>
            </div>

        </div>
    </div>
</div>

{{-- مكتبة توليد الباركود --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    let checkInterval;

    function checkStatus() {
        $.get("{{ route('superadmin.whatsapp.status') }}")
        .done(function(res) {
            $('#loadingState').hide();
            $('#errorState').hide();

            if (res.connected) {
                // حالة الاتصال
                $('#qrState').hide();
                $('#connectedState').fadeIn();
                
                if(res.user) {
                    $('#waName').text(res.user.name || 'مستخدم واتساب');
                    $('#waPhone').text(res.user.id.split(':')[0]);
                }
            } else if (res.qr) {
                // حالة الباركود
                $('#connectedState').hide();
                $('#qrState').fadeIn();
                
                // رسم الباركود فقط إذا تغير (لتجنب الرمش)
                document.getElementById("qrcode").innerHTML = "";
                new QRCode(document.getElementById("qrcode"), {
                    text: res.qr,
                    width: 250,
                    height: 250
                });
            }
        })
        .fail(function() {
            $('#loadingState').hide();
            $('#connectedState').hide();
            $('#qrState').hide();
            $('#errorState').show();
        });
    }

    function logoutWhatsApp() {
        if(!confirm('هل أنت متأكد من فك ارتباط هذا الرقم؟ ستتوقف الإشعارات حتى إعادة الربط.')) return;
        
        $('#connectedState').hide();
        $('#loadingState').show();

        $.post("{{ route('superadmin.whatsapp.logout') }}", { _token: '{{ csrf_token() }}' })
        .done(function() {
            setTimeout(() => location.reload(), 2000);
        });
    }

    // فحص الحالة كل 3 ثواني
    $(document).ready(function() {
        checkStatus();
        checkInterval = setInterval(checkStatus, 3000);
    });
</script>
@endsection