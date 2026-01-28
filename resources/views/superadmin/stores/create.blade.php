@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-primary">إنشاء متجر جديد</h5>
                    <a href="{{ route('superadmin.stores.index') }}" class="btn btn-outline-secondary btn-sm">
                        العودة إلى القائمة
                    </a>
                </div>
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm">
                            <h6 class="alert-heading fw-bold"><i class="fas fa-exclamation-circle"></i> حدث خطأ!</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('superadmin.stores.store') }}" id="createStoreForm">
                        @csrf
                        {{-- حقل مخفي للتأكد من حالة التحقق --}}
                        <input type="hidden" id="is_verified" value="0">

                        <h6 class="text-muted fw-bold">بيانات صاحب المتجر (المالك)</h6>
                        <hr class="mt-1 mb-4">

                        <div class="row mb-3">
                            <label for="owner_name" class="col-md-4 col-form-label text-md-end">اسم صاحب المتجر <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input id="owner_name" type="text" class="form-control" name="owner_name" value="{{ old('owner_name') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="owner_email" class="col-md-4 col-form-label text-md-end">إيميل صاحب المتجر <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input id="owner_email" type="email" class="form-control" name="owner_email" value="{{ old('owner_email') }}" required>
                            </div>
                        </div>

                        {{-- ✅ حقل الواتساب الجديد مع منطق التعطيل --}}
                        <div class="row mb-3">
                            <label for="owner_phone" class="col-md-4 col-form-label text-md-end">
                                رقم الواتساب <span class="text-muted small">(اختياري)</span>
                            </label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text @if($isWhatsappActive) bg-success text-white @else bg-secondary text-white @endif">
                                        <i class="fab fa-whatsapp"></i>
                                    </span>
                                    <input id="owner_phone" type="text" class="form-control" name="owner_phone" 
                                           value="{{ old('owner_phone') }}" 
                                           placeholder="مثال: 96650xxxxxxx"
                                           @if(!$isWhatsappActive) disabled @endif>
                                </div>
                                
                                @if(!$isWhatsappActive)
                                    <small class="text-danger fw-bold mt-1 d-block">
                                        <i class="fas fa-plug-circle-xmark"></i> خدمة الواتساب غير متصلة، سيتم الاكتفاء بالإيميل.
                                    </small>
                                @else
                                    <small class="text-muted mt-1 d-block">
                                        إذا أدخلت الرقم، سيطلب النظام كود تفعيل قبل الإنشاء.
                                    </small>
                                @endif
                            </div>
                        </div>

                        <h6 class="text-muted fw-bold mt-5">بيانات المتجر</h6>
                        <hr class="mt-1 mb-4">

                        <div class="row mb-3">
                            <label for="store_name" class="col-md-4 col-form-label text-md-end">اسم المتجر <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input id="store_name" type="text" class="form-control" name="store_name" value="{{ old('store_name') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="subdomain" class="col-md-4 col-form-label text-md-end">الدومين الفرعي <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <div class="input-group" dir="ltr">
                                    <input id="subdomain" type="text" class="form-control text-end" name="subdomain" value="{{ old('subdomain') }}" required>
                                    <span class="input-group-text bg-light">.tech-sys.online</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-end">حالة المتجر <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <select id="status" class="form-select" name="status" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>فعال (Active)</option>
                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>متوقف مؤقتاً (Suspended)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-0 mt-4">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" id="mainSubmitBtn" class="btn btn-primary btn-lg px-5">إنشاء المتجر وإرسال الرابط</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅ نافذة الـ OTP --}}
<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">التحقق من رقم الواتساب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p>تم إرسال كود التفعيل إلى الرقم: <b id="otpPhoneDisplay" dir="ltr"></b></p>
                <input type="text" id="otpInput" class="form-control text-center fs-3 letter-spacing-2 mb-3" placeholder="XXXXXX" maxlength="6">
                <p class="text-danger small" id="otpError" style="display:none;"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success w-100" onclick="verifyOtp()">تأكيد وإنشاء المتجر</button>
            </div>
        </div>
    </div>
</div>

{{-- ✅ السكربت --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('createStoreForm').addEventListener('submit', function(e) {
        let phone = document.getElementById('owner_phone').value.trim();
        let isDisabled = document.getElementById('owner_phone').disabled;
        let isVerified = document.getElementById('is_verified').value;

        // 1. إذا كان حقل الهاتف معطل (الخدمة مفصولة) أو فارغ -> أرسل مباشرة
        if (isDisabled || phone === '') {
            return true;
        }

        // 2. إذا تم التحقق مسبقاً -> أرسل مباشرة
        if (isVerified === '1') {
            return true;
        }

        // 3. إذا كان هناك رقم ولم يتم التحقق -> أوقف الإرسال وابدأ الـ OTP
        e.preventDefault();
        startOtpProcess(phone);
    });

    function startOtpProcess(phone) {
        let btn = document.getElementById('mainSubmitBtn');
        let oldText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري الإرسال...';
        btn.disabled = true;

        fetch("{{ route('superadmin.stores.otp.send') }}", {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ phone: phone })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = oldText;
            
            if(data.success) {
                document.getElementById('otpPhoneDisplay').innerText = phone;
                var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
                myModal.show();
            } else {
                Swal.fire('خطأ', data.message || 'فشل إرسال الكود', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = oldText;
            Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
        });
    }

    function verifyOtp() {
        let otp = document.getElementById('otpInput').value;
        let phone = document.getElementById('owner_phone').value;
        let errorEl = document.getElementById('otpError');

        if(otp.length < 4) {
            errorEl.innerText = 'الرجاء إدخال الكود';
            errorEl.style.display = 'block';
            return;
        }

        fetch("{{ route('superadmin.stores.otp.verify') }}", {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ phone: phone, otp: otp })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // الكود صحيح
                document.getElementById('is_verified').value = '1';
                bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide();
                
                Swal.fire({
                    icon: 'success', title: 'تم التحقق!', text: 'جاري إنشاء المتجر...',
                    timer: 1500, showConfirmButton: false
                }).then(() => {
                    document.getElementById('createStoreForm').submit();
                });
            } else {
                errorEl.innerText = 'الكود غير صحيح!';
                errorEl.style.display = 'block';
            }
        });
    }
</script>
@endsection