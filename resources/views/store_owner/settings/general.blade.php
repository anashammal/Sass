@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="fas fa-sliders-h me-2"></i> إعدادات عامة</h3>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('store.settings.update-general', $store->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- بطاقة الموقع الجغرافي (قوقل ماب) والعنوان --}}
            <div class="col-lg-6 mb-4">
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
                        <div id="map" class="rounded-3 shadow-inner border flex-grow-1" style="min-height: 250px; background: #f8f9fa;">
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

            {{-- بيانات المتجر الأساسية --}}
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white text-primary fw-bold border-bottom">
                        <i class="fas fa-store me-2"></i> بيانات المتجر الأساسية
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">اسم المتجر <span class="text-danger">*</span></label>
                            <input type="text" name="store_name_update" class="form-control" value="{{ old('store_name_update', $store->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">رقم الهاتف <small class="text-muted">(مع الرمز الدولي)</small></label>
                            <input type="text" name="phone_number" class="form-control" 
                                   value="{{ old('phone_number', $store->phone_number) }}" 
                                   placeholder="مثال: 9665xxxxxxxx">
                            <small class="text-muted">هذا الرقم سيستقبل رسائل الواتساب.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email', $store->email) }}" 
                                   placeholder="store@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">الرقم الضريبي</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $store->tax_number) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-success btn-lg px-5 shadow rounded-pill">
                    <i class="fa fa-save me-2"></i> حفظ الإعدادات العامة
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
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

<script>
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
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    map.setCenter(pos);
                    marker.setPosition(pos);
                    updateCoords(pos.lat, pos.lng);
                    reverseGeocode({lat: pos.lat, lng: pos.lng});
                },
                () => alert("حدث خطأ في تحديد الموقع.")
            );
        } else {
            alert("المتصفح لا يدعم تحديد الموقع.");
        }
    }
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap"></script>
@endsection
