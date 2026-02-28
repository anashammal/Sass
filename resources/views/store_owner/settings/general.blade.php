@extends('layouts.app')

@section('content')
<!-- Select2 CSS for Multi-Select support -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="fas fa-sliders-h me-2"></i> {{ __('General Settings') }}</h3>
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
                        <span><i class="fas fa-map-marker-alt me-2"></i> {{ __('Store Location (Google Maps)') }}</span>
                        <span class="badge bg-white text-danger small">{{ __('Determine Location') }}</span>
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
                        <i class="fas fa-store me-2"></i> {{ __('Store Basic Details') }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Store Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="store_name_update" class="form-control" value="{{ old('store_name_update', $store->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Country') }}</label>
                                <select name="country" id="country_select" class="form-select select2-basic">
                                    <option value="">{{ __('Select Country') }}</option>
                                    @if($store->country)
                                        <option value="{{ $store->country }}" selected>{{ $store->country }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('City') }}</label>
                                <select name="city" id="city_select" class="form-select select2-basic">
                                    <option value="">{{ __('Select City') }}</option>
                                    @if($store->city)
                                        <option value="{{ $store->city }}" selected>{{ $store->city }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Address Details') }}</label>
                            <input type="text" name="address_details" id="address_details" class="form-control" 
                                   value="{{ old('address_details', $store->address_details) }}" 
                                   placeholder="{{ __('Address Placeholder') }}">
                            <small class="text-muted" id="address_details_hint">{{ __('Address Hint') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Phone Number') }} <small class="text-muted">{{ __('With International Code') }}</small></label>
                            <input type="tel" id="phone_input" class="form-control w-100" 
                                   value="{{ old('phone_number', $store->phone_number) }}" dir="ltr">
                            <input type="hidden" name="phone_number" id="full_phone" value="{{ old('phone_number', $store->phone_number) }}">
                            <span id="phone-error-msg" class="text-danger small mt-1" style="display: none;"></span>
                            <small class="text-muted mt-1 d-block">{{ __('WhatsApp Notification Hint') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Email') }}</label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   value="{{ old('email', $store->email) }}" 
                                   placeholder="store@example.com"
                                   style="transition: all 0.3s ease;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Tax Number') }}</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $store->tax_number) }}">
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-coins me-2"></i> {{ __('Currency Settings') }}</h6>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Base Currency') }} <span class="text-danger">*</span></label>
                            <select name="base_currency_id" class="form-select select2-basic" required>
                                <option value="">{{ __('Select Base Currency') }}</option>
                                @foreach(\App\Models\Currency::where('is_active', true)->get() as $currency)
                                    <option value="{{ $currency->id }}" {{ $store->base_currency_id == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->name_ar }} ({{ $currency->code }}) - {{ $currency->symbol }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">{{ __('This is the main currency for products and reports.') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Accepted Sub-Currencies') }}</label>
                            <select name="accepted_currencies[]" class="form-select select2-multiple" multiple="multiple">
                                @php
                                    $acceptedIds = $store->acceptedCurrencies->pluck('id')->toArray();
                                @endphp
                                @foreach(\App\Models\Currency::where('is_active', true)->get() as $currency)
                                    <option value="{{ $currency->id }}" {{ in_array($currency->id, $acceptedIds) ? 'selected' : '' }}>
                                        {{ $currency->name_ar }} ({{ $currency->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">{{ __('Customers can pay using these currencies at the POS.') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-success btn-lg px-5 shadow rounded-pill">
                    <i class="fa fa-save me-2"></i> {{ __('Save General Settings') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        if($.fn.select2) {
            $('.select2-basic').select2({ theme: 'bootstrap-5', width: '100%' });
            $('.select2-multiple').select2({ theme: 'bootstrap-5', width: '100%', placeholder: '{{ __('Select Currencies') }}', allowClear: true });
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
                    searchPlaceholder: "{{ __('Search for Country...') }}",
                }
            });

            const errorMsg = document.querySelector("#phone-error-msg");
            const fullPhoneInput = document.querySelector("#full_phone");

            // دالة التحقق من صحة الرقم ومخاطبة المستخدم
            const validatePhone = () => {
                fullPhoneInput.value = phoneInput.getNumber(); // تحديث مستمر للقيمة الخفية
                
                if (phoneInputField.value.trim()) {
                    if (phoneInput.isValidNumber()) {
                        phoneInputField.classList.remove("is-invalid");
                        phoneInputField.classList.add("is-valid");
                        errorMsg.style.display = "none";
                        errorMsg.innerHTML = "";
                        return true;
                    } else {
                        phoneInputField.classList.remove("is-valid");
                        phoneInputField.classList.add("is-invalid");
                        
                        // رسائل خطأ مفصلة من المكتبة
                        const errorCode = phoneInput.getValidationError();
                        let errorText = "{{ __('Invalid Number.') }}";
                        switch(errorCode) {
                            case intlTelInputUtils.validationError.IS_POSSIBLE:
                            case intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
                                errorText = "{{ __('Invalid Country Code.') }}"; break;
                            case intlTelInputUtils.validationError.TOO_SHORT:
                                errorText = "{{ __('Number is too short for this country.') }}"; break;
                            case intlTelInputUtils.validationError.TOO_LONG:
                                errorText = "{{ __('Number is too long for this country.') }}"; break;
                        }
                        errorMsg.innerHTML = errorText;
                        errorMsg.style.display = "block";
                        return false;
                    }
                } else {
                    phoneInputField.classList.remove("is-valid", "is-invalid");
                    errorMsg.style.display = "none";
                    return true;
                }
            };

            // تشغيل الفحص عند الخروج من الحقل أو الكتابة
            phoneInputField.addEventListener('blur', validatePhone);
            phoneInputField.addEventListener('keyup', validatePhone);
            // فحص الرقم المبدئي
            if(phoneInputField.value) {
                phoneInput.setNumber(phoneInputField.value); // Set number properly
                validatePhone();
            }

            // منع إدخال الأحرف
            phoneInputField.addEventListener('keypress', e => {
                if (e.which < 48 || e.which > 57) {
                    if (e.which !== 8 && e.which !== 0 && e.which !== 43) e.preventDefault();
                }
            });

            // تأكيد المنع عند الإرسال
            $('form').on('submit', function(e) {
                if(phoneInputField.value.trim() !== '') {
                    if(!validatePhone()) {
                        e.preventDefault();
                        errorMsg.innerHTML = "{{ __('Please enter a perfectly valid phone number to continue.') }}";
                        phoneInputField.focus();
                        return false;
                    }
                }
            });
        }
        
        initPhoneInput();

        // --- منطق البريد الإلكتروني (إكمال تلقائي ومنع العربي) يفضل عزله تماماً لضمان عمله ---
        const emailInput = document.querySelector("#email");
        const domains = ["gmail.com", "outlook.com", "hotmail.com", "yahoo.com", "icloud.com", "msn.com"];
        
        if (emailInput) {
            emailInput.addEventListener("input", function(e) {
                // منع الأحرف العربية تماماً
                this.value = this.value.replace(/[\u0600-\u06FF]/g, "");
                
                const val = this.value;
                const atIndex = val.indexOf("@");
                
                // التلوين بناءً على الصحة
                if(val.trim() === '') {
                    this.style.borderColor = "";
                    this.style.boxShadow = "";
                } else {
                    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                    this.style.borderColor = isValid ? "#10b981" : "#ef4444";
                    this.style.boxShadow = isValid ? "0 0 0 0.25rem rgba(16, 185, 129, 0.1)" : "0 0 0 0.25rem rgba(239, 68, 68, 0.1)";
                }

                if (atIndex > -1) {
                    const query = val.substring(atIndex + 1);
                    if (query.length > 0) {
                        const match = domains.find(d => d.startsWith(query));
                        if (match && e.inputType !== "deleteContentBackward") {
                            const start = val.length;
                            this.value = val.substring(0, atIndex + 1) + match;
                            this.setSelectionRange(start, this.value.length);
                        }
                    }
                }
            });

            // تحويل الصغير عند الخروج من الحقل
            emailInput.addEventListener("blur", function() {
                this.value = this.value.toLowerCase().trim();
                if(this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('Invalid Email') }}',
                        text: '{{ __('Please enter a valid email format (e.g., name@domain.com)') }}',
                        confirmButtonText: '{{ __('OK') }}'
                    });
                }
            });
        }
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

    let cityTranslations = {};
    const currentLang = '{{ app()->getLocale() }}';
    
    // جلب ملف الترجمات إذا لم يكن محملاً
    fetch('/translations.json').then(r => r.json()).then(data => {
        if(data[currentLang]) cityTranslations = data[currentLang];
    }).catch(e => console.error("Error loading translations: ", e));

    function populateCountries() {
        const countrySelect = $('#country_select');
        const currentCountry = "{{ $store->country }}";
        countrySelect.empty().append('<option value="">اختر الدولة...</option>');
        
        countriesData.forEach(c => {
            const translatedCountry = cityTranslations[c.country] || c.country;
            const selected = (c.country === currentCountry) ? 'selected' : '';
            countrySelect.append(`<option value="${c.country}" ${selected}>${translatedCountry}</option>`);
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
                const translatedCity = cityTranslations[city] || city;
                const selected = (city === preselectedCity) ? 'selected' : '';
                citySelect.append(`<option value="${city}" ${selected}>${translatedCity}</option>`);
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
        const defaultLocations = {
            'ar': { lat: 24.4672, lng: 39.6112 }, // المدينة المنورة
            'en': { lat: 51.5074, lng: -0.1278 }, // لندن
            'ru': { lat: 55.7558, lng: 37.6173 }, // موسكو
            'tr': { lat: 39.9334, lng: 32.8597 }, // أنقرة
            'zh': { lat: 39.9042, lng: 116.4074 },// بكين
            'fr': { lat: 48.8566, lng: 2.3522 },  // باريس
            'es': { lat: 40.4168, lng: -3.7038 }, // مدريد
            'de': { lat: 52.5200, lng: 13.4050 }, // برلين
            'pt': { lat: 38.7223, lng: -9.1393 }, // لشبونة
            'pt-BR': { lat: -15.7975, lng: -47.8919 }, // برازيليا
            'ja': { lat: 35.6895, lng: 139.6917 },// طوكيو
            'hr': { lat: 45.8150, lng: 15.9819 }  // زغرب
        };
        const currentLang = '{{ app()->getLocale() }}';
        const defaultPos = defaultLocations[currentLang] || defaultLocations['ar'];

        const latEl = document.getElementById("latitude");
        const lngEl = document.getElementById("longitude");
        const lat = parseFloat(latEl && latEl.value ? latEl.value : defaultPos.lat) || defaultPos.lat;
        const lng = parseFloat(lngEl && lngEl.value ? lngEl.value : defaultPos.lng) || defaultPos.lng;
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
<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&language={{ app()->getLocale() }}&callback=initMap"></script>
@endsection
