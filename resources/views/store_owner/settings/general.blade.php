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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">الدولة</label>
                                <select name="country" id="country_select" class="form-select select2-basic">
                                    <option value="">اختر الدولة...</option>
                                    @if($store->country)
                                        <option value="{{ $store->country }}" selected>{{ $store->country }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">المدينة</label>
                                <select name="city" id="city_select" class="form-select select2-basic">
                                    <option value="">اختر المدينة...</option>
                                    @if($store->city)
                                        <option value="{{ $store->city }}" selected>{{ $store->city }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">بقية العنوان (تفاصيل)</label>
                            <input type="text" name="address_details" id="address_details" class="form-control" 
                                   value="{{ old('address_details', $store->address_details) }}" 
                                   placeholder="اسم الشارع، الحي، المبنى...">
                            <small class="text-muted" id="address_details_hint">يرجى اختيار الدولة والمدينة أولاً لتفعيل هذا الحقل</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">رقم الهاتف <small class="text-muted">(مع الرمز الدولي)</small></label>
                            <input type="tel" name="phone_number" id="phone_input" class="form-control w-100" 
                                   value="{{ old('phone_number', $store->phone_number) }}" dir="ltr">
                            <small class="text-muted mt-1 d-block">هذا الرقم سيستقبل رسائل الواتساب.</small>
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
<script>
    $(document).ready(function() {
        if($.fn.select2) {
            $('.select2-basic').select2({ theme: 'bootstrap-5', width: '100%' });
        }

        // تهيئة حقل الهاتف الدولي
        const phoneInputField = document.querySelector("#phone_input");

        function initPhoneInput() {
            if (typeof window.intlTelInput === 'undefined') {
                setTimeout(initPhoneInput, 500);
                return;
            }

            const phoneInput = window.intlTelInput(phoneInputField, {
                initialCountry: "sa",
                preferredCountries: ["sa", "ae", "tr", "eg", "jo", "sy"],
                separateDialCode: true,
                autoPlaceholder: "aggressive",
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js",
                countryNameLocale: "ar",
                i18n: {
                    searchPlaceholder: "ابحث عن الدولة...",
                }
            });

            // اعتراض عملية الحفظ (Submit) لإرسال الرقم الكامل (الكود + الرقم)
            $('form').on('submit', function() {
                if(phoneInput.isValidNumber()) {
                    const fullNumber = phoneInput.getNumber();
                    // استبدال قيمة الحقل بالرقم الكامل قبل الإرسال
                    $('#phone_input').val(fullNumber);
                }
            });
        }
        
        initPhoneInput();
    });
</script>
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
    let map, marker, autocomplete, detailsAutocomplete;
    let countriesData = [];
    let isMapSyncing = false; // لمنع اللوب اللانهائي

    // تحميل بيانات الدول والمدن
    async function loadCountriesData() {
        try {
            const cacheKey = 'countries_cities_data';
            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                countriesData = JSON.parse(cached);
                populateCountries();
            } else {
                const response = await fetch("{{ asset('js/countries_cities.json') }}");
                countriesData = await response.json();
                localStorage.setItem(cacheKey, JSON.stringify(countriesData));
                populateCountries();
            }
        } catch (error) {
            console.error("خطأ في تحميل بيانات الدول:", error);
        }
    }

    function populateCountries() {
        const countrySelect = $('#country_select');
        const currentCountry = "{{ $store->country }}";
        countrySelect.empty().append('<option value="">اختر الدولة...</option>');
        
        countriesData.forEach(c => {
            const selected = (c.country === currentCountry) ? 'selected' : '';
            countrySelect.append(`<option value="${c.country}" ${selected}>${c.country}</option>`);
        });
        
        // تحديث واجهة البحث الذكي
        if($.fn.select2) {
            countrySelect.select2({ theme: 'bootstrap-5', width: '100%' });
        }

        if (currentCountry) {
            populateCities(currentCountry, "{{ $store->city }}");
        } else {
            checkDetailsInputState();
        }
    }

    function populateCities(countryName, preselectedCity = null) {
        const citySelect = $('#city_select');
        citySelect.empty().append('<option value="">اختر المدينة...</option>');
        
        const countryObj = countriesData.find(c => c.country === countryName);
        if (countryObj && countryObj.cities) {
            countryObj.cities.forEach(city => {
                const selected = (city === preselectedCity) ? 'selected' : '';
                citySelect.append(`<option value="${city}" ${selected}>${city}</option>`);
            });
        }
        
        // تحديث واجهة البحث الذكي
        if($.fn.select2) {
            citySelect.select2({ theme: 'bootstrap-5', width: '100%' });
        }

        checkDetailsInputState();
    }

    // تهيئة الخريطة
    function initMap() {
        const latEl = document.getElementById("latitude");
        const lngEl = document.getElementById("longitude");
        const lat = parseFloat(latEl ? latEl.value : 24.7136) || 24.7136;
        const lng = parseFloat(lngEl ? lngEl.value : 46.6753) || 46.6753;
        const initialPos = { lat: lat, lng: lng };
        map = new google.maps.Map(document.getElementById("map"), { center: initialPos, zoom: 12, mapTypeControl: false });
        marker = new google.maps.Marker({ position: initialPos, map: map, draggable: true });
        
        marker.addListener("dragend", () => { 
            const pos = marker.getPosition(); 
            updateCoords(pos.lat(), pos.lng()); 
            reverseGeocode(pos); 
        });
        
        const addressInput = document.getElementById("address");
        if(addressInput) {
            autocomplete = new google.maps.places.Autocomplete(addressInput);
            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace(); if (!place.geometry) return;
                map.setCenter(place.geometry.location); 
                map.setZoom(15);
                marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location.lat(), place.geometry.location.lng());
                extractAddressComponents(place.address_components);
            });
        }

        const detailsInput = document.getElementById("address_details");
        if(detailsInput) {
            detailsAutocomplete = new google.maps.places.Autocomplete(detailsInput);
            detailsAutocomplete.addListener("place_changed", () => {
                const place = detailsAutocomplete.getPlace(); if (!place.geometry) return;
                map.setCenter(place.geometry.location);
                map.setZoom(17);
                marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location.lat(), place.geometry.location.lng());
                
                // تحديث حقل البحث الرئيسي ليتوافق مع التفاصيل أيضاً
                if(addressInput && place.formatted_address) {
                    addressInput.value = place.formatted_address;
                }
            });
            
            // Prevent browser autocomplete
            detailsInput.setAttribute('autocomplete', 'off');
        }

        // تفاعلات القوائم المنسدلة
        $('#country_select').on('change', function() {
            const country = $(this).val();
            populateCities(country);
            if (!isMapSyncing) geocodeAddress(country, '');
            checkDetailsInputState();
        });

        $('#city_select').on('change', function() {
            const city = $(this).val();
            const country = $('#country_select').val();
            if (!isMapSyncing) geocodeAddress(country, city);
            checkDetailsInputState();
        });

        loadCountriesData();
        
        // جلب الإحداثيات إذا كانت الدولة والمدينة محددة مسبقاً لتقييد الإكمال التلقائي
        if ("{{ $store->country }}" && "{{ $store->city }}") {
            geocodeAddress("{{ $store->country }}", "{{ $store->city }}", true);
        }
    }

    function checkDetailsInputState() {
        const country = $('#country_select').val();
        const city = $('#city_select').val();
        const detailsInput = $('#address_details');
        const detailsHint = $('#address_details_hint');

        if (country && city) {
            detailsInput.prop('disabled', false).prop('readonly', false);
            detailsInput.css('background-color', '#fff');
            detailsHint.hide();
        } else {
            detailsInput.prop('disabled', true).prop('readonly', true);
            detailsInput.css('background-color', '#e9ecef');
            if (!("{{ $store->address_details }}")) {
                detailsInput.val('');
            }
            detailsHint.show();
            if(detailsAutocomplete) {
                detailsAutocomplete.setOptions({ strictBounds: false });
                detailsAutocomplete.setBounds(null);
            }
        }
    }

    function geocodeAddress(country, city, isInitialLoad = false) {
        if (!country && !city) return;
        const query = `${city ? city + ', ' : ''}${country}`;
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: query }, (results, status) => {
            if (status === "OK" && results[0]) {
                const loc = results[0].geometry.location;
                const bounds = results[0].geometry.viewport;
                
                if(!isInitialLoad) {
                    map.setCenter(loc);
                    map.setZoom(city ? 12 : 5);
                    marker.setPosition(loc);
                    updateCoords(loc.lat(), loc.lng());
                }

                // تقييد الإكمال التلقائي لحقل التفاصيل ضمن نطاق المدينة
                if (city && detailsAutocomplete && bounds) {
                    detailsAutocomplete.setBounds(bounds);
                    detailsAutocomplete.setOptions({ strictBounds: true });
                }
            }
        });
    }

    function extractAddressComponents(components) {
        if (!components) return;
        isMapSyncing = true;
        let country = '', city = '';
        
        components.forEach(comp => {
            if (comp.types.includes('country')) country = comp.long_name;
            if (comp.types.includes('locality') || comp.types.includes('administrative_area_level_1')) {
                if(!city) city = comp.long_name;
            }
        });

        if (country) {
            // محاولة إيجاد أقرب تطابق للدولة في القائمة
            const matchCountry = countriesData.find(c => c.country.toLowerCase() === country.toLowerCase() || country.toLowerCase().includes(c.country.toLowerCase()));
            if (matchCountry) {
                $('#country_select').val(matchCountry.country).trigger('change.select2');
                populateCities(matchCountry.country);
                
                if (city) {
                    const matchCity = matchCountry.cities.find(c => c.toLowerCase() === city.toLowerCase() || city.toLowerCase().includes(c.toLowerCase()));
                    if (matchCity) {
                        $('#city_select').val(matchCity).trigger('change.select2');
                    }
                }
            }
        }
        isMapSyncing = false;
    }

    function updateCoords(lat, lng) { 
        if(document.getElementById("latitude")) document.getElementById("latitude").value = lat; 
        if(document.getElementById("longitude")) document.getElementById("longitude").value = lng; 
    }
    
    function reverseGeocode(pos) { 
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: pos }, (results, status) => { 
            if (status === "OK" && results[0]) {
                if(document.getElementById("address")) document.getElementById("address").value = results[0].formatted_address; 
                extractAddressComponents(results[0].address_components);
            }
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
