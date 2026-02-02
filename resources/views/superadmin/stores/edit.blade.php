@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">تعديل المتجر: {{ $store->name }}</h5>
                    <a href="{{ route('superadmin.stores.index') }}" class="btn btn-outline-secondary btn-sm">
                        العودة إلى القائمة
                    </a>
                </div>

                <div class="card-body">

                    {{-- كود عرض الأخطاء العامة --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">حدث خطأ!</h6>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('superadmin.stores.update', $store->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="text-muted">بيانات صاحب المتجر (المالك)</h6>
                        <hr>

                        {{-- (اسم صاحب المتجر) --}}
                        <div class="row mb-3">
                            <label for="owner_name" class="col-md-4 col-form-label text-md-end">اسم صاحب المتجر</label>
                            <div class="col-md-8">
                                <input id="owner_name" type="text" class="form-control @error('owner_name') is-invalid @enderror" name="owner_name" value="{{ old('owner_name', optional($store->owner)->name) }}" required>
                                @error('owner_name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        {{-- (إيميل صاحب المتجر) --}}
                        <div class="row mb-3">
                            <label for="owner_email" class="col-md-4 col-form-label text-md-end">إيميل صاحب المتجر</label>
                            <div class="col-md-8">
                                <input id="owner_email" type="email" class="form-control @error('owner_email') is-invalid @enderror" name="owner_email" value="{{ old('owner_email', optional($store->owner)->email) }}" required>
                                @error('owner_email') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <h6 class="text-muted mt-4">بيانات المتجر</h6>
                        <hr>

                        {{-- (اسم المتجر) --}}
                        <div class="row mb-3">
                            <label for="store_name" class="col-md-4 col-form-label text-md-end">اسم المتجر</label>
                            <div class="col-md-8">
                                <input id="store_name" type="text" class="form-control @error('store_name') is-invalid @enderror" name="store_name" value="{{ old('store_name', $store->name) }}" required>
                                @error('store_name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        {{-- (الدومين الفرعي) --}}
                        <div class="row mb-3">
                            <label for="subdomain" class="col-md-4 col-form-label text-md-end">الدومين الفرعي</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="subdomain" type="text" class="form-control @error('subdomain') is-invalid @enderror" name="subdomain" value="{{ old('subdomain', $store->subdomain) }}" required>
                                    <span class="input-group-text">.tech-sys.online</span>
                                </div>
                                @error('subdomain') <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        {{-- ------------------------------------------------------------- --}}
                        {{-- <<<< الكود الجديد: بيانات الإعدادات الأولية (للقراءة فقط) >>>> --}}
                        {{-- ------------------------------------------------------------- --}}
                        <h6 class="text-muted mt-4">بيانات الإعدادات الأولية (Onboarding) - للقراءة فقط</h6>
                        <hr>

                        {{-- الدولة --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">الدولة</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->country ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>

                        {{-- المدينة --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">المدينة</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->city ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>

                        {{-- رقم الهاتف --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">رقم الهاتف</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->phone_number ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>

                        {{-- رقم الحساب البنكي (IBAN) --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">IBAN</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->iban ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>
                        {{-- ------------------------------------------------------------- --}}
                        {{-- <<<< نهاية الكود الجديد >>>> --}}
                        {{-- ------------------------------------------------------------- --}}
                        
                        {{-- (الحالة) --}}
                        <h6 class="text-muted mt-5">إدارة حالة المتجر وموقعه (للسوبر أدمن)</h6>
                        <hr class="mb-4">

                        <div class="row mb-3">
                            <label for="tax_number" class="col-12 form-label fw-bold">الرقم الضريبي</label>
                            <div class="col-12">
                                <input id="tax_number" type="text" class="form-control" name="tax_number" value="{{ old('tax_number', $store->tax_number) }}" placeholder="أدخل الرقم الضريبي">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="address" class="col-12 form-label fw-bold mt-3">العنوان (قوقل ماب)</label>
                            <div class="col-12">
                                <div class="input-group shadow-sm border rounded-3 overflow-hidden" dir="ltr">
                                    <button type="button" class="btn btn-primary px-3" onclick="getCurrentLocation()" title="موقعي الحالي">
                                        <i class="fas fa-location-arrow"></i>
                                    </button>
                                    <input type="text" class="form-control border-0" id="address" name="address" value="{{ old('address', $store->address) }}" 
                                           placeholder="ابحث عن عنوان المتجر (سيظهر الإكمال التلقائي هنا)" dir="rtl" style="font-size: 1rem;">
                                </div>
                                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $store->latitude) }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $store->longitude) }}">
                                <div id="map" class="mt-3 rounded-3 shadow-sm border" style="height: 350px; width: 100%; background: #f8f9fa;">
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                        <span>يرجى إضافة Google Maps API Key لتفعيل الخريطة</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <label for="status" class="col-md-4 col-form-label text-md-end">حالة المتجر</label>
                            <div class="col-md-8">
                                <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                    <option value="active" {{ old('status', $store->status) == 'active' ? 'selected' : '' }}>فعال (Active)</option>
                                    <option value="suspended" {{ old('status', $store->status) == 'suspended' ? 'selected' : '' }}>متوقف مؤقتاً (Suspended)</option>
                                </select>
                                @error('status') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="type" class="col-md-4 col-form-label text-md-end">نوع المتجر</label>
                            <div class="col-md-8">
                                <select id="type" class="form-control @error('type') is-invalid @enderror" name="type" required>
                                    <option value="retail" {{ old('type', $store->type) == 'retail' ? 'selected' : '' }}>تجارة عامة / تجزئة</option>
                                    <option value="restaurant" {{ old('type', $store->type) == 'restaurant' ? 'selected' : '' }}>مطعم</option>
                                </select>
                                @error('type') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> تنبيه: تغيير النوع قد يؤثر على كيفية ظهور البيانات لصاحب المتجر.
                                </small>
                            </div>
                        </div>

                        {{-- (السبب) --}}
                        <div class="row mb-3" id="status-reason-container" style="display: none;">
                            <label for="status_reason" class="col-md-4 col-form-label text-md-end">سبب تغيير الحالة</label>
                            <div class="col-md-8">
                                <textarea id="status_reason" class="form-control @error('status_reason') is-invalid @enderror" name="status_reason" rows="3">{{ old('status_reason', $store->status_reason) }}</textarea>
                                @error('status_reason') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="row mb-0 mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-3 shadow-sm">حفظ التعديلات</button>
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
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const statusSelect = document.getElementById('status');
        const reasonContainer = document.getElementById('status-reason-container');
        const reasonTextarea = document.getElementById('status_reason');
        const originalStatus = '{{ $store->status }}';

        function toggleReasonField() {
            // نستخدم 'flex' لأننا غيرنا الـ display ليتوافق مع الـ row (Bootstrap)
            if (statusSelect.value !== originalStatus) {
                reasonContainer.style.display = 'flex';
                reasonTextarea.setAttribute('required', 'required');
            } else {
                reasonContainer.style.display = 'none';
                reasonTextarea.removeAttribute('required');
            }
        }

        // تحقق عند تحميل الصفحة (في حال وجود خطأ والعودة)
        toggleReasonField();
        // تحقق عند تغيير القائمة
        statusSelect.addEventListener('change', toggleReasonField);
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
                () => Swal.fire('تنبيه', 'تم رفض الوصول لموقعك الحالي', 'warning')
            );
        }
    }
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap"></script>
@endsection