@extends('layouts.app')

@section('content')
{{-- Select2 CSS & JS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .iti { width: 100% !important; display: block !important; }
    .select2-container { width: 100% !important; }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 50px !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        padding: 5px 10px !important;
    }
    #map-container {
        height: 300px;
        border-radius: 15px;
        overflow: hidden;
        border: 2px solid #e9ecef;
        margin-bottom: 15px;
        position: relative;
    }
    .location-btn {
        position: absolute;
        bottom: 15px;
        left: 15px;
        z-index: 10;
        background: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    .location-btn:hover { background: #4a6cf7; color: white; }
    .location-btn i { margin-inline-end: 5px; }
    
    .address-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        margin-bottom: 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .pac-container { z-index: 9999 !important; }
    #address_autocomplete {
        font-size: 16px;
        padding: 12px 15px;
        border-radius: 10px;
    }
    
    /* Validation Error Styles */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    .is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 text-center border-0">
                    <h4 class="mb-0 text-primary fw-bold">🌍 إكمال بيانات المتجر</h4>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('store.onboarding.update') }}" id="settingsForm">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold">الدولة <span class="text-danger">*</span></label>
                                <select name="country" id="country_select" class="form-select" required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">المدينة <span class="text-danger">*</span></label>
                                <select name="city" id="city_select" class="form-select" required>
                                    <option value="">...اختر الدولة أولاً</option>
                                </select>
                                <input type="text" name="city_manual" id="city_manual" class="form-control d-none mt-2" placeholder="اكتب اسم المدينة يدوياً">
                            </div>
                        </div>

                        {{-- Google Maps Section --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold"><i class="fas fa-map-marker-alt text-danger me-2"></i>الموقع على الخريطة</label>
                            <div id="map-container">
                                <div id="map" style="width: 100%; height: 100%;"></div>
                                <button type="button" class="location-btn" id="get_location_btn">
                                    <i class="fas fa-crosshairs"></i> موقعي الحالي
                                </button>
                            </div>
                            
                            <div class="address-badge" id="address_badge">
                                <i class="fas fa-map-pin"></i>
                                <span id="location_display">اختر الدولة والمدينة</span>
                            </div>
                            
                            <label class="form-label fw-bold">العنوان التفصيلي <small class="text-muted">(ابدأ بالكتابة للاقتراحات)</small></label>
                            <input type="text" id="address_autocomplete" class="form-control" 
                                   placeholder="ابدأ بكتابة العنوان وستظهر لك اقتراحات من خرائط جوجل..."
                                   autocomplete="off">
                            <input type="hidden" name="address" id="full_address" value="{{ old('address', $store->address ?? '') }}">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                        </div>

                        <hr class="my-4" style="opacity: 0.1;">

                        <div class="mb-4">
                            <label class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <input type="tel" class="form-control" id="phone_input" value="{{ old('phone_number', $store->phone_number ?? '') }}">
                                </div>
                                <button type="button" class="btn btn-success px-4" id="send_otp_btn" style="height: 50px; white-space: nowrap;">
                                    <i class="fab fa-whatsapp me-1"></i> تحقق
                                </button>
                            </div>
                            <input type="hidden" name="phone_number" id="full_phone">
                            <input type="hidden" name="phone_country_code" id="phone_country_code">
                            <input type="hidden" name="phone_verified" id="phone_verified" value="">
                            
                            {{-- OTP Input (Hidden initially) --}}
                            <div id="otp_section" class="mt-3 d-none">
                                <div class="alert alert-info py-2">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    تم إرسال رمز التحقق إلى واتساب
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control text-center fw-bold" id="otp_input" 
                                           placeholder="أدخل الرمز المكون من 6 أرقام" maxlength="6" style="letter-spacing: 5px; font-size: 18px;">
                                    <button type="button" class="btn btn-primary" id="verify_otp_btn">
                                        <i class="fas fa-check me-1"></i> تأكيد
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Verified Badge --}}
                            <div id="verified_badge" class="mt-2 d-none">
                                <span class="badge bg-success fs-6 py-2 px-3">
                                    <i class="fas fa-check-circle me-1"></i> تم التحقق من الرقم ✅
                                </span>
                            </div>
                        </div>

                        <hr class="my-4" style="opacity: 0.1;">

                        <div class="row mb-4">
                            <div class="col-md-7 mb-3 mb-md-0">
                                <label class="form-label fw-bold">رقم الآيبان (IBAN)</label>
                                <input type="text" name="iban" class="form-control text-center fw-bold" value="{{ old('iban', $store->iban ?? '') }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">دولة البنك</label>
                                <input type="text" name="bank_country" class="form-control" value="{{ old('bank_country', $store->bank_country ?? '') }}">
                            </div>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold">
                                <i class="fas fa-check-circle me-2"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Google Maps API with Places - Define initMap BEFORE loading API --}}
<script>
// Define initMap in global scope FIRST (required for callback=initMap)
var map, marker, geocoder, autocomplete;
window.initMap = function() {
    var defaultLocation = { lat: 21.4225, lng: 39.8262 };
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: defaultLocation,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true
    });

    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP
    });

    geocoder = new google.maps.Geocoder();

    // ===== PLACES AUTOCOMPLETE =====
    var input = document.getElementById('address_autocomplete');
    autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['address']
    });

    // Bias autocomplete to current map bounds
    autocomplete.bindTo('bounds', map);

    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        
        if (!place.geometry) {
            console.log("No geometry for place");
            return;
        }

        // Move map to selected place
        map.setCenter(place.geometry.location);
        map.setZoom(17);
        marker.setPosition(place.geometry.location);

        // Save coordinates
        $('#latitude').val(place.geometry.location.lat());
        $('#longitude').val(place.geometry.location.lng());

        // Extract country and city from address components
        var country = '', city = '', countryCode = '';
        place.address_components.forEach(function(component) {
            if (component.types.includes('country')) {
                country = component.long_name;
                countryCode = component.short_name.toLowerCase();
            }
            if (component.types.includes('locality') || component.types.includes('administrative_area_level_1')) {
                if (!city) city = component.long_name;
            }
        });

        // Update country dropdown if found
        if (countryCode && $('#country_select option[value="' + countryCode + '"]').length) {
            $('#country_select').val(countryCode).trigger('change');
        }

        // Update display
        $('#location_display').text(city + '، ' + country);
        $('#full_address').val(place.formatted_address);
    });

    // ===== MARKER DRAG =====
    marker.addListener('dragend', function() {
        var pos = marker.getPosition();
        $('#latitude').val(pos.lat());
        $('#longitude').val(pos.lng());
        reverseGeocode(pos.lat(), pos.lng());
    });

    // ===== MAP CLICK =====
    map.addListener('click', function(e) {
        marker.setPosition(e.latLng);
        $('#latitude').val(e.latLng.lat());
        $('#longitude').val(e.latLng.lng());
        reverseGeocode(e.latLng.lat(), e.latLng.lng());
    });

    // ===== GET CURRENT LOCATION =====
    $('#get_location_btn').on('click', function() {
        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديد...');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    map.setCenter(pos);
                    map.setZoom(17);
                    marker.setPosition(pos);
                    
                    $('#latitude').val(pos.lat);
                    $('#longitude').val(pos.lng);
                    
                    reverseGeocode(pos.lat, pos.lng);
                    btn.html('<i class="fas fa-crosshairs"></i> موقعي الحالي');
                },
                function(error) {
                    alert('لم نتمكن من تحديد موقعك. تأكد من تفعيل خدمة الموقع.');
                    btn.html('<i class="fas fa-crosshairs"></i> موقعي الحالي');
                }
            );
        } else {
            alert('المتصفح لا يدعم خدمة تحديد الموقع');
            btn.html('<i class="fas fa-crosshairs"></i> موقعي الحالي');
        }
    });
}

// ===== REVERSE GEOCODE =====
function reverseGeocode(lat, lng) {
    geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
        if (status === 'OK' && results[0]) {
            var place = results[0];
            
            // Update autocomplete input with address
            $('#address_autocomplete').val(place.formatted_address);
            $('#full_address').val(place.formatted_address);
            
            // Extract country and city
            var country = '', city = '', countryCode = '', countryAr = '';
            place.address_components.forEach(function(c) {
                if (c.types.includes('country')) {
                    country = c.long_name;
                    countryCode = c.short_name.toLowerCase();
                }
                if (c.types.includes('locality') || c.types.includes('administrative_area_level_1')) {
                    if (!city) city = c.long_name;
                }
            });

            // Sync country dropdown
            if (countryCode) {
                var $opt = $('#country_select option[value="' + countryCode + '"]');
                if ($opt.length) {
                    $('#country_select').val(countryCode).trigger('change.select2');
                    countryAr = $opt.data('ar') || country;
                    
                    // After cities load, try to select the city
                    setTimeout(function() {
                        if (city && $('#city_select option').filter(function() { 
                            return $(this).text().toLowerCase() === city.toLowerCase(); 
                        }).length) {
                            $('#city_select').val(city).trigger('change.select2');
                        }
                    }, 1500);
                }
            }

            $('#location_display').text((city || '') + '، ' + (countryAr || country));
        }
    });
}

// ===== MOVE MAP TO CITY =====
function moveMapToCity(countryName, cityName) {
    var address = cityName + ', ' + countryName;
    
    geocoder.geocode({ address: address }, function(results, status) {
        if (status === 'OK' && results[0]) {
            var location = results[0].geometry.location;
            map.setCenter(location);
            map.setZoom(13);
            marker.setPosition(location);
            
            $('#latitude').val(location.lat());
            $('#longitude').val(location.lng());
            
            // Restrict autocomplete to this country
            var countryCode = $('#country_select').val();
            if (countryCode) {
                autocomplete.setComponentRestrictions({ country: countryCode });
            }
        }
    });
}

$(function() {
    // ===== ALL COUNTRIES =====
    const COUNTRIES = [
        { code: "af", ar: "أفغانستان", en: "Afghanistan" },
        { code: "al", ar: "ألبانيا", en: "Albania" },
        { code: "dz", ar: "الجزائر", en: "Algeria" },
        { code: "ad", ar: "أندورا", en: "Andorra" },
        { code: "ao", ar: "أنغولا", en: "Angola" },
        { code: "ar", ar: "الأرجنتين", en: "Argentina" },
        { code: "am", ar: "أرمينيا", en: "Armenia" },
        { code: "au", ar: "أستراليا", en: "Australia" },
        { code: "at", ar: "النمسا", en: "Austria" },
        { code: "az", ar: "أذربيجان", en: "Azerbaijan" },
        { code: "bh", ar: "البحرين", en: "Bahrain" },
        { code: "bd", ar: "بنغلاديش", en: "Bangladesh" },
        { code: "by", ar: "بيلاروسيا", en: "Belarus" },
        { code: "be", ar: "بلجيكا", en: "Belgium" },
        { code: "bj", ar: "بنين", en: "Benin" },
        { code: "bo", ar: "بوليفيا", en: "Bolivia" },
        { code: "ba", ar: "البوسنة والهرسك", en: "Bosnia And Herzegovina" },
        { code: "bw", ar: "بوتسوانا", en: "Botswana" },
        { code: "br", ar: "البرازيل", en: "Brazil" },
        { code: "bn", ar: "بروناي", en: "Brunei" },
        { code: "bg", ar: "بلغاريا", en: "Bulgaria" },
        { code: "kh", ar: "كمبوديا", en: "Cambodia" },
        { code: "cm", ar: "الكاميرون", en: "Cameroon" },
        { code: "ca", ar: "كندا", en: "Canada" },
        { code: "cl", ar: "تشيلي", en: "Chile" },
        { code: "cn", ar: "الصين", en: "China" },
        { code: "co", ar: "كولومبيا", en: "Colombia" },
        { code: "cr", ar: "كوستاريكا", en: "Costa Rica" },
        { code: "hr", ar: "كرواتيا", en: "Croatia" },
        { code: "cu", ar: "كوبا", en: "Cuba" },
        { code: "cy", ar: "قبرص", en: "Cyprus" },
        { code: "cz", ar: "التشيك", en: "Czech Republic" },
        { code: "dk", ar: "الدانمرك", en: "Denmark" },
        { code: "dj", ar: "جيبوتي", en: "Djibouti" },
        { code: "do", ar: "جمهورية الدومينيكان", en: "Dominican Republic" },
        { code: "ec", ar: "الإكوادور", en: "Ecuador" },
        { code: "eg", ar: "مصر", en: "Egypt" },
        { code: "sv", ar: "السلفادور", en: "El Salvador" },
        { code: "ee", ar: "إستونيا", en: "Estonia" },
        { code: "et", ar: "إثيوبيا", en: "Ethiopia" },
        { code: "fj", ar: "فيجي", en: "Fiji" },
        { code: "fi", ar: "فنلندا", en: "Finland" },
        { code: "fr", ar: "فرنسا", en: "France" },
        { code: "ge", ar: "جورجيا", en: "Georgia" },
        { code: "de", ar: "ألمانيا", en: "Germany" },
        { code: "gh", ar: "غانا", en: "Ghana" },
        { code: "gr", ar: "اليونان", en: "Greece" },
        { code: "gt", ar: "غواتيمالا", en: "Guatemala" },
        { code: "hn", ar: "هندوراس", en: "Honduras" },
        { code: "hk", ar: "هونغ كونغ", en: "Hong Kong" },
        { code: "hu", ar: "المجر", en: "Hungary" },
        { code: "is", ar: "آيسلندا", en: "Iceland" },
        { code: "in", ar: "الهند", en: "India" },
        { code: "id", ar: "إندونيسيا", en: "Indonesia" },
        { code: "ir", ar: "إيران", en: "Iran" },
        { code: "iq", ar: "العراق", en: "Iraq" },
        { code: "ie", ar: "أيرلندا", en: "Ireland" },
        { code: "il", ar: "إسرائيل", en: "Israel" },
        { code: "it", ar: "إيطاليا", en: "Italy" },
        { code: "jm", ar: "جامايكا", en: "Jamaica" },
        { code: "jp", ar: "اليابان", en: "Japan" },
        { code: "jo", ar: "الأردن", en: "Jordan" },
        { code: "kz", ar: "كازاخستان", en: "Kazakhstan" },
        { code: "ke", ar: "كينيا", en: "Kenya" },
        { code: "kw", ar: "الكويت", en: "Kuwait" },
        { code: "kg", ar: "قيرغيزستان", en: "Kyrgyzstan" },
        { code: "la", ar: "لاوس", en: "Laos" },
        { code: "lv", ar: "لاتفيا", en: "Latvia" },
        { code: "lb", ar: "لبنان", en: "Lebanon" },
        { code: "ly", ar: "ليبيا", en: "Libya" },
        { code: "lt", ar: "ليتوانيا", en: "Lithuania" },
        { code: "lu", ar: "لوكسمبورغ", en: "Luxembourg" },
        { code: "my", ar: "ماليزيا", en: "Malaysia" },
        { code: "mv", ar: "المالديف", en: "Maldives" },
        { code: "mt", ar: "مالطا", en: "Malta" },
        { code: "mx", ar: "المكسيك", en: "Mexico" },
        { code: "md", ar: "مولدوفا", en: "Moldova" },
        { code: "mc", ar: "موناكو", en: "Monaco" },
        { code: "mn", ar: "منغوليا", en: "Mongolia" },
        { code: "me", ar: "الجبل الأسود", en: "Montenegro" },
        { code: "ma", ar: "المغرب", en: "Morocco" },
        { code: "mm", ar: "ميانمار", en: "Myanmar" },
        { code: "np", ar: "نيبال", en: "Nepal" },
        { code: "nl", ar: "هولندا", en: "Netherlands" },
        { code: "nz", ar: "نيوزيلندا", en: "New Zealand" },
        { code: "ni", ar: "نيكاراغوا", en: "Nicaragua" },
        { code: "ng", ar: "نيجيريا", en: "Nigeria" },
        { code: "no", ar: "النرويج", en: "Norway" },
        { code: "om", ar: "عُمان", en: "Oman" },
        { code: "pk", ar: "باكستان", en: "Pakistan" },
        { code: "ps", ar: "فلسطين", en: "Palestine" },
        { code: "pa", ar: "بنما", en: "Panama" },
        { code: "py", ar: "باراغواي", en: "Paraguay" },
        { code: "pe", ar: "بيرو", en: "Peru" },
        { code: "ph", ar: "الفلبين", en: "Philippines" },
        { code: "pl", ar: "بولندا", en: "Poland" },
        { code: "pt", ar: "البرتغال", en: "Portugal" },
        { code: "qa", ar: "قطر", en: "Qatar" },
        { code: "ro", ar: "رومانيا", en: "Romania" },
        { code: "ru", ar: "روسيا", en: "Russia" },
        { code: "rw", ar: "رواندا", en: "Rwanda" },
        { code: "sa", ar: "السعودية", en: "Saudi Arabia" },
        { code: "sn", ar: "السنغال", en: "Senegal" },
        { code: "rs", ar: "صربيا", en: "Serbia" },
        { code: "sg", ar: "سنغافورة", en: "Singapore" },
        { code: "sk", ar: "سلوفاكيا", en: "Slovakia" },
        { code: "si", ar: "سلوفينيا", en: "Slovenia" },
        { code: "so", ar: "الصومال", en: "Somalia" },
        { code: "za", ar: "جنوب أفريقيا", en: "South Africa" },
        { code: "kr", ar: "كوريا الجنوبية", en: "South Korea" },
        { code: "ss", ar: "جنوب السودان", en: "South Sudan" },
        { code: "es", ar: "إسبانيا", en: "Spain" },
        { code: "lk", ar: "سريلانكا", en: "Sri Lanka" },
        { code: "sd", ar: "السودان", en: "Sudan" },
        { code: "se", ar: "السويد", en: "Sweden" },
        { code: "ch", ar: "سويسرا", en: "Switzerland" },
        { code: "sy", ar: "سوريا", en: "Syria" },
        { code: "tw", ar: "تايوان", en: "Taiwan" },
        { code: "tj", ar: "طاجيكستان", en: "Tajikistan" },
        { code: "tz", ar: "تنزانيا", en: "Tanzania" },
        { code: "th", ar: "تايلاند", en: "Thailand" },
        { code: "tn", ar: "تونس", en: "Tunisia" },
        { code: "tr", ar: "تركيا", en: "Turkey" },
        { code: "tm", ar: "تركمانستان", en: "Turkmenistan" },
        { code: "ug", ar: "أوغندا", en: "Uganda" },
        { code: "ua", ar: "أوكرانيا", en: "Ukraine" },
        { code: "ae", ar: "الإمارات", en: "United Arab Emirates" },
        { code: "gb", ar: "المملكة المتحدة", en: "United Kingdom" },
        { code: "us", ar: "الولايات المتحدة", en: "United States" },
        { code: "uy", ar: "أوروغواي", en: "Uruguay" },
        { code: "uz", ar: "أوزبكستان", en: "Uzbekistan" },
        { code: "ve", ar: "فنزويلا", en: "Venezuela" },
        { code: "vn", ar: "فيتنام", en: "Vietnam" },
        { code: "ye", ar: "اليمن", en: "Yemen" },
        { code: "zm", ar: "زامبيا", en: "Zambia" },
        { code: "zw", ar: "زيمبابوي", en: "Zimbabwe" }
    ];

    function toFlag(code) {
        return code.toUpperCase().replace(/./g, c => String.fromCodePoint(c.charCodeAt(0) + 127397));
    }

    const $country = $('#country_select');
    const $city = $('#city_select');
    const phoneEl = document.getElementById("phone_input");

    // ===== POPULATE COUNTRIES =====
    $country.empty().append('<option value="">🔍 ابحث عن دولتك...</option>');
    COUNTRIES.forEach(c => {
        $country.append(`<option value="${c.code}" data-en="${c.en}" data-ar="${c.ar}">${toFlag(c.code)} ${c.ar} - ${c.en}</option>`);
    });

    // ===== INIT SELECT2 =====
    $country.select2({ theme: 'bootstrap-5', width: '100%', dir: 'rtl', placeholder: '🔍 ابحث عن دولتك...', allowClear: true });
    $city.select2({ theme: 'bootstrap-5', width: '100%', dir: 'rtl' });

    // ===== PHONE INPUT =====
    var iti = null;
    if (window.intlTelInput && phoneEl) {
        iti = window.intlTelInput(phoneEl, {
            initialCountry: "auto",
            geoIpLookup: function(cb) { fetch("https://ipapi.co/json").then(r => r.json()).then(d => cb(d.country_code)).catch(() => cb("sa")); },
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js"
        });
        $(phoneEl).on('blur', function() {
            if (iti && iti.isValidNumber()) {
                $('#full_phone').val(iti.getNumber());
                $('#phone_country_code').val(iti.getSelectedCountryData().dialCode);
            }
        });
    }

    // ===== HANDLE COUNTRY SELECTION =====
    $country.on('select2:select', function(e) {
        var code = e.params.data.id;
        var enName = $(this).find('option:selected').data('en');
        var arName = $(this).find('option:selected').data('ar');
        
        if (iti) iti.setCountry(code);
        loadCities(enName);
        
        // Update location display
        $('#location_display').text(arName);
        
        // Restrict autocomplete to selected country
        if (autocomplete) {
            autocomplete.setComponentRestrictions({ country: code });
        }
    });

    // ===== HANDLE CITY SELECTION =====
    $city.on('select2:select', function(e) {
        var cityName = e.params.data.id;
        var countryEn = $country.find('option:selected').data('en');
        var countryAr = $country.find('option:selected').data('ar');
        
        if (cityName && countryEn && typeof moveMapToCity === 'function') {
            moveMapToCity(countryEn, cityName);
            $('#location_display').text(cityName + '، ' + countryAr);
        }
    });

    // ===== CITY LOADER =====
    function loadCities(countryName) {
        $city.prop('disabled', true).empty().append('<option value="">⏳ جاري التحميل...</option>');
        $('#city_manual').addClass('d-none').prop('required', false);

        $.ajax({
            url: 'https://countriesnow.space/api/v0.1/countries/cities',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ country: countryName }),
            success: function(response) {
                $city.empty();
                if (!response.error && response.data && response.data.length > 0) {
                    $city.append('<option value="">اختر المدينة...</option>');
                    response.data.forEach(function(city) {
                        $city.append(new Option(city, city));
                    });
                    $city.prop('disabled', false);
                } else {
                    showManualCity();
                }
            },
            error: function() { showManualCity(); }
        });
    }

    function showManualCity() {
        $city.empty().append('<option value="">لا توجد مدن - أدخل يدوياً</option>');
        $('#city_manual').removeClass('d-none').prop('required', true).focus();
    }

    // ===== INITIAL VALUE =====
    var oldCountry = "{{ old('country', $store->country ?? '') }}";
    if (oldCountry) {
        $country.val(oldCountry).trigger('change');
        var enName = $country.find('option:selected').data('en');
        if (enName) loadCities(enName);
    }

    // ===== PHONE VERIFICATION (OTP via WhatsApp) =====
    $('#send_otp_btn').on('click', function() {
        var btn = $(this);
        var phone = '';
        
        // Get phone from intl-tel-input if available
        if (iti && iti.getNumber()) {
            phone = iti.getNumber();
        } else {
            phone = $('#phone_input').val();
        }
        
        if (!phone || phone.length < 9) {
            Swal.fire('تنبيه', 'يرجى إدخال رقم هاتف صحيح', 'warning');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ url("store-owner/onboarding/send-otp") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                phone: phone
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fab fa-whatsapp me-1"></i> إعادة الإرسال');
                
                if (response.success) {
                    $('#otp_section').removeClass('d-none');
                    $('#otp_input').focus();
                    Swal.fire({
                        icon: 'success',
                        title: 'تم الإرسال',
                        text: 'تفقد واتساب لاستلام رمز التحقق',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('خطأ', response.message || 'فشل إرسال الرمز', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fab fa-whatsapp me-1"></i> تحقق');
                Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
            }
        });
    });

    $('#verify_otp_btn').on('click', function() {
        var btn = $(this);
        var phone = '';
        
        if (iti && iti.getNumber()) {
            phone = iti.getNumber();
        } else {
            phone = $('#phone_input').val();
        }
        
        var otp = $('#otp_input').val();
        
        if (!otp || otp.length !== 6) {
            Swal.fire('تنبيه', 'يرجى إدخال رمز التحقق المكون من 6 أرقام', 'warning');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ url("store-owner/onboarding/verify-otp") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                phone: phone,
                otp: otp
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> تأكيد');
                
                if (response.success) {
                    $('#otp_section').addClass('d-none');
                    $('#verified_badge').removeClass('d-none');
                    $('#phone_verified').val('1');
                    $('#send_otp_btn').addClass('d-none');
                    $('#phone_input').prop('readonly', true);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'تم التحقق بنجاح ✅',
                        text: 'يمكنك الآن إكمال التسجيل',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('خطأ', response.message || 'رمز التحقق غير صحيح', 'error');
                    $('#otp_input').val('').focus();
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> تأكيد');
                Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
            }
        });
    });

    // Allow only numbers in OTP input
    $('#otp_input').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // ===== FORM VALIDATION =====
    $('#settingsForm').on('submit', function(e) {
        var isValid = true;
        var firstError = null;
        var errorMessages = [];
        
        // Remove previous error styling
        $('.is-invalid').removeClass('is-invalid');
        $('.validation-error-msg').remove();
        $('.select2-selection').css('border-color', '');
        
        // Check Country
        if (!$('#country_select').val()) {
            isValid = false;
            $('#country_select').addClass('is-invalid');
            $('#country_select').next('.select2-container').find('.select2-selection').css('border-color', '#dc3545');
            errorMessages.push('الدولة');
            if (!firstError) firstError = $('#country_select');
        }
        
        // Check City
        var cityVal = $('#city_select').val() || $('#city_manual').val();
        if (!cityVal) {
            isValid = false;
            $('#city_select').addClass('is-invalid');
            $('#city_select').next('.select2-container').find('.select2-selection').css('border-color', '#dc3545');
            $('#city_manual').addClass('is-invalid');
            errorMessages.push('المدينة');
            if (!firstError) firstError = $('#city_select');
        }
        
        // Check Phone
        var phoneVal = $('#full_phone').val() || $('#phone_input').val();
        if (!phoneVal || phoneVal.length < 9) {
            isValid = false;
            $('#phone_input').addClass('is-invalid');
            errorMessages.push('رقم الهاتف');
            if (!firstError) firstError = $('#phone_input');
        }
        
        // If validation fails
        if (!isValid) {
            e.preventDefault();
            
            // Show error message with SweetAlert
            Swal.fire({
                icon: 'warning',
                title: 'بيانات ناقصة',
                html: '<div style="text-align: right; font-size: 16px;">' +
                      'يرجى إكمال الحقول التالية:<br><br>' +
                      '<ul style="text-align: right; padding-right: 20px;">' +
                      errorMessages.map(function(msg) { return '<li style="color: #dc3545; font-weight: bold;">' + msg + '</li>'; }).join('') +
                      '</ul></div>',
                confirmButtonText: 'حسناً'
            });
            
            // Scroll to first error
            if (firstError) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 300);
            }
            
            return false;
        }
        
        // Make sure phone is set before submit
        if (typeof iti !== 'undefined' && iti.getNumber()) {
            $('#full_phone').val(iti.getNumber());
            $('#phone_country_code').val(iti.getSelectedCountryData().dialCode);
        }
    });

    // Remove error styling when user starts typing/selecting
    $('#country_select').on('change', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.select2-container').find('.select2-selection').css('border-color', '');
    });
    
    $('#city_select').on('change', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.select2-container').find('.select2-selection').css('border-color', '');
    });
    
    $('#city_manual, #phone_input').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>

{{-- Load Google Maps API AFTER all functions are defined --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMap" async defer></script>
@endsection