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

                        <div class="row mb-3">
                            <label for="type" class="col-md-4 col-form-label text-md-end">نوع المتجر <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <select id="type" class="form-select" name="type" required>
                                    <option value="retail" {{ old('type') == 'retail' ? 'selected' : '' }}>تجارة عامة / تجزئة</option>
                                    <option value="restaurant" {{ old('type') == 'restaurant' ? 'selected' : '' }}>مطعم</option>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> نوع "المطعم" يوفر ميزات إدارة المكونات والوجبات وحساب التكاليف.
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tax_number" class="col-12 form-label fw-bold">الرقم الضريبي (اختياري)</label>
                            <div class="col-12">
                                <input id="tax_number" type="text" class="form-control" name="tax_number" value="{{ old('tax_number') }}" placeholder="أدخل الرقم الضريبي">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="address" class="col-12 form-label fw-bold mt-3">العنوان (قوقل ماب)</label>
                            <div class="col-12">
                                <div class="input-group shadow-sm border rounded-3 overflow-hidden" dir="ltr">
                                    <button type="button" class="btn btn-primary px-3" onclick="getCurrentLocation()" title="موقعي الحالي">
                                        <i class="fas fa-location-arrow"></i>
                                    </button>
                                    <input type="text" class="form-control border-0" id="address" name="address" value="{{ old('address') }}" 
                                           placeholder="ابحث عن عنوان المتجر (سيظهر الإكمال التلقائي هنا)" dir="rtl" style="font-size: 1rem;">
                                </div>
                                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                                <div id="map" class="mt-3 rounded-3 shadow-sm border" style="height: 350px; width: 100%; background: #f8f9fa;">
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                        <span>يرجى إضافة Google Maps API Key لتفعيل الخريطة</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0 mt-5">
                            <div class="col-12">
                                <button type="submit" id="mainSubmitBtn" class="btn btn-success btn-lg w-100 fw-bold shadow-sm py-3">
                                    <i class="fas fa-store me-1"></i> إنشاء المتجر وإرسال الرابط
                                </button>
                            </div>
                        </div>

                        <style>
                            .pac-container {
                                z-index: 10000 !important;
                                border-radius: 8px !important;
                                box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
                                border-top: none !important;
                                margin-top: 5px !important;
                                font-family: inherit;
                            }
                            .pac-item { padding: 10px; cursor: pointer; }
                            .pac-item:hover { background-color: #f8f9fa; }
                        </style>
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

    // --- قوقل ماب وإدارة المواقع ---
    let map, marker, autocomplete;

    function initMap() {
        const defaultPos = { lat: 24.7136, lng: 46.6753 };
        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultPos, zoom: 13, mapTypeControl: false,
        });

        marker = new google.maps.Marker({
            position: defaultPos, map: map, draggable: true, animation: google.maps.Animation.DROP,
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
                () => Swal.fire('تنبيه', 'تم رفض الوصول لموقعك الحالي', 'warning')
            );
        }
    }
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap"></script>
@endsection