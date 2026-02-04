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
                                    <input type="tel" class="form-control" id="phone_input" 
                                           value="{{ old('phone_number', $store->phone_number ?? '') }}"
                                           {{ ($store->phone_verified ?? false) ? 'readonly' : '' }}>
                                </div>
                                @if($store->phone_verified ?? false)
                                    <button type="button" class="btn btn-outline-secondary px-3" id="change_phone_btn" style="height: 50px; white-space: nowrap;">
                                        <i class="fas fa-edit me-1"></i> تغيير
                                    </button>
                                @endif
                                <button type="button" class="btn btn-success px-4 {{ ($store->phone_verified ?? false) ? 'd-none' : '' }}" id="send_otp_btn" style="height: 50px; white-space: nowrap;">
                                    <i class="fab fa-whatsapp me-1"></i> تحقق
                                </button>
                            </div>
                            <input type="hidden" name="phone_number" id="full_phone" value="{{ $store->phone_number ?? '' }}">
                            <input type="hidden" name="phone_country_code" id="phone_country_code">
                            <input type="hidden" name="phone_verified" id="phone_verified" value="{{ ($store->phone_verified ?? false) ? '1' : '' }}">
                            
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
                            <div id="verified_badge" class="mt-2 {{ ($store->phone_verified ?? false) ? '' : 'd-none' }}">
                                <span class="badge bg-success fs-6 py-2 px-3">
                                    <i class="fas fa-check-circle me-1"></i> تم التحقق من الرقم ✅
                                </span>
                                @if($store->phone_verified_at ?? false)
                                <small class="text-muted ms-2">
                                    ({{ \Carbon\Carbon::parse($store->phone_verified_at)->diffForHumans() }})
                                </small>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4" style="opacity: 0.1;">

                        <div class="row mb-4">
                            <div class="col-md-5 mb-3 mb-md-0">
                                <label class="form-label fw-bold">دولة البنك</label>
                                <select name="bank_country" id="bank_country_select" class="form-select"></select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold">اسم البنك</label>
                                <select name="bank_name" id="bank_name_select" class="form-select">
                                    <option value="">اختر دولة البنك أولاً...</option>
                                </select>
                                <input type="text" name="bank_name_manual" id="bank_name_manual" class="form-control d-none mt-2" placeholder="اكتب اسم البنك يدوياً">
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">رقم الآيبان (IBAN)</label>
                                <div class="input-group" dir="ltr">
                                    <span class="input-group-text fw-bold" id="iban_prefix" style="min-width: 50px; justify-content: center; background: #e9ecef; font-family: monospace; font-size: 16px;">--</span>
                                    <input type="text" name="iban" id="iban_input" class="form-control fw-bold" 
                                           value="{{ old('iban', $store->iban ?? '') }}"
                                           placeholder="Enter IBAN number"
                                           dir="ltr"
                                           style="letter-spacing: 2px; font-size: 15px; font-family: monospace;">
                                </div>
                                <div class="form-text" id="iban_hint">اختر دولة البنك لمعرفة صيغة الآيبان الصحيحة</div>
                                <div class="invalid-feedback" id="iban_error"></div>
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
    
    // Get initial country code if selected
    var initialCountry = $('#country_select').val();
    var autocompleteOptions = {
        types: ['address']
    };
    
    // Set initial country restriction if available
    if (initialCountry) {
        autocompleteOptions.componentRestrictions = { country: initialCountry };
    }
    
    autocomplete = new google.maps.places.Autocomplete(input, autocompleteOptions);

    // Bias autocomplete to current map bounds
    autocomplete.bindTo('bounds', map);
    
    // ===== UPDATE AUTOCOMPLETE RESTRICTION ON COUNTRY/CITY CHANGE =====
    window.updateAutocompleteRestriction = function() {
        var countryCode = $('#country_select').val();
        var cityName = $('#city_select').val() || $('#city_manual').val();
        
        if (countryCode) {
            // Restrict to selected country
            autocomplete.setComponentRestrictions({ country: countryCode });
            
            // If city is selected, update placeholder to hint at city
            if (cityName && cityName !== '__manual__') {
                $('#address_autocomplete').attr('placeholder', 'ابحث عن عنوان في ' + cityName + '...');
            } else {
                // Find country name
                var countryData = COUNTRIES.find(c => c.code === countryCode);
                var countryName = countryData ? countryData.ar : countryCode.toUpperCase();
                $('#address_autocomplete').attr('placeholder', 'ابحث عن عنوان في ' + countryName + '...');
            }
        } else {
            // No country selected - global search
            autocomplete.setComponentRestrictions(null);
            $('#address_autocomplete').attr('placeholder', 'ابدأ بكتابة العنوان وستظهر لك اقتراحات من خرائط جوجل...');
        }
    };

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

            // Sync country dropdown and load cities
            if (countryCode) {
                var $opt = $('#country_select option[value="' + countryCode + '"]');
                if ($opt.length) {
                    // Set country value
                    $('#country_select').val(countryCode).trigger('change.select2');
                    countryAr = $opt.data('ar') || country;
                    var countryEn = $opt.data('en') || country;
                    
                    // Update phone country
                    if (typeof iti !== 'undefined') iti.setCountry(countryCode);
                    
                    // Restrict autocomplete to country
                    if (typeof autocomplete !== 'undefined' && autocomplete) {
                        autocomplete.setComponentRestrictions({ country: countryCode });
                    }
                    
                    // Load cities and auto-select detected city
                    loadCitiesAndSelect(countryEn, city);
                }
            }

            $('#location_display').text((city || '') + '، ' + (countryAr || country));
        }
    });
}

// Helper function to load cities and select one
function loadCitiesAndSelect(countryName, cityToSelect) {
    var $city = $('#city_select');
    $city.prop('disabled', true).empty().append('<option value="">⏳ جاري تحميل المدن...</option>');
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
                
                // Try to select the detected city
                if (cityToSelect) {
                    var $matchOption = $city.find('option').filter(function() {
                        return $(this).text().toLowerCase() === cityToSelect.toLowerCase();
                    });
                    
                    if ($matchOption.length) {
                        $city.val($matchOption.val()).trigger('change.select2');
                        console.log('Auto-selected city: ' + $matchOption.val());
                    } else {
                        // Try partial match
                        $matchOption = $city.find('option').filter(function() {
                            return $(this).text().toLowerCase().includes(cityToSelect.toLowerCase()) ||
                                   cityToSelect.toLowerCase().includes($(this).text().toLowerCase());
                        });
                        if ($matchOption.length) {
                            $city.val($matchOption.first().val()).trigger('change.select2');
                            console.log('Auto-selected city (partial): ' + $matchOption.first().val());
                        }
                    }
                }
            } else {
                $city.empty().append('<option value="">لا توجد مدن - أدخل يدوياً</option>');
                $('#city_manual').removeClass('d-none').prop('required', true);
                if (cityToSelect) {
                    $('#city_manual').val(cityToSelect);
                }
            }
        },
        error: function() {
            $city.empty().append('<option value="">لا توجد مدن - أدخل يدوياً</option>');
            $('#city_manual').removeClass('d-none').prop('required', true);
            if (cityToSelect) {
                $('#city_manual').val(cityToSelect);
            }
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

    // ===== CHANGE PHONE NUMBER =====
    $('#change_phone_btn').on('click', function() {
        Swal.fire({
            title: 'تغيير رقم الهاتف',
            text: 'هل تريد تغيير الرقم المتحقق منه؟ سيتطلب ذلك التحقق من الرقم الجديد.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، تغيير',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enable phone input for editing
                $('#phone_input').prop('readonly', false).focus().select();
                
                // Hide verified badge
                $('#verified_badge').addClass('d-none');
                
                // Show verify button
                $('#send_otp_btn').removeClass('d-none');
                
                // Clear verification status
                $('#phone_verified').val('');
                
                // Hide change button
                $(this).addClass('d-none');
            }
        });
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

    // ===== BANK COUNTRY, BANKS & IBAN SYSTEM =====
    const BANK_DATA = {
        'SA': {
            name: 'السعودية',
            code: 'SA',
            ibanLength: 24,
            ibanFormat: 'SA00 0000 0000 0000 0000 0000',
            banks: [
                'البنك الأهلي السعودي', 'مصرف الراجحي', 'بنك الرياض', 'البنك السعودي الفرنسي',
                'البنك السعودي البريطاني (ساب)', 'بنك البلاد', 'بنك الجزيرة', 'بنك الإنماء',
                'البنك العربي الوطني', 'مصرف الإنماء', 'بنك ساب', 'STC Pay'
            ]
        },
        'AE': {
            name: 'الإمارات',
            code: 'AE',
            ibanLength: 23,
            ibanFormat: 'AE00 0000 0000 0000 0000 000',
            banks: [
                'بنك أبوظبي الأول', 'بنك الإمارات دبي الوطني', 'بنك دبي الإسلامي', 'مصرف أبوظبي الإسلامي',
                'بنك المشرق', 'بنك رأس الخيمة الوطني', 'بنك الفجيرة الوطني', 'بنك الشارقة'
            ]
        },
        'TR': {
            name: 'تركيا',
            code: 'TR',
            ibanLength: 26,
            ibanFormat: 'TR00 0000 0000 0000 0000 0000 00',
            banks: [
                'Ziraat Bankası', 'İş Bankası', 'Garanti BBVA', 'Yapı Kredi', 'Akbank',
                'Halkbank', 'VakıfBank', 'QNB Finansbank', 'Denizbank', 'TEB',
                'ING Bank', 'HSBC', 'Kuveyt Türk', 'Albaraka Türk', 'PTT Bank'
            ]
        },
        'EG': {
            name: 'مصر',
            code: 'EG',
            ibanLength: 29,
            ibanFormat: 'EG00 0000 0000 0000 0000 0000 000',
            banks: [
                'البنك الأهلي المصري', 'بنك مصر', 'بنك القاهرة', 'البنك التجاري الدولي CIB',
                'بنك الإسكندرية', 'البنك العربي الأفريقي', 'بنك QNB الأهلي', 'بنك HSBC مصر'
            ]
        },
        'JO': {
            name: 'الأردن',
            code: 'JO',
            ibanLength: 30,
            ibanFormat: 'JO00 AAAA 0000 0000 0000 0000 0000 00',
            banks: [
                'البنك العربي', 'بنك الإسكان', 'البنك الأهلي الأردني', 'بنك الأردن',
                'البنك الإسلامي الأردني', 'بنك القاهرة عمان', 'بنك المال الأردني'
            ]
        },
        'KW': {
            name: 'الكويت',
            code: 'KW',
            ibanLength: 30,
            ibanFormat: 'KW00 AAAA 0000 0000 0000 0000 0000 00',
            banks: [
                'بنك الكويت الوطني', 'بيت التمويل الكويتي', 'بنك برقان', 'البنك التجاري الكويتي',
                'بنك الخليج', 'بنك بوبيان', 'البنك الأهلي المتحد'
            ]
        },
        'QA': {
            name: 'قطر',
            code: 'QA',
            ibanLength: 29,
            ibanFormat: 'QA00 AAAA 0000 0000 0000 0000 000',
            banks: [
                'بنك قطر الوطني', 'البنك التجاري', 'مصرف قطر الإسلامي', 'بنك الدوحة',
                'بنك قطر الدولي', 'مصرف الريان', 'بنك أبوظبي الإسلامي'
            ]
        },
        'BH': {
            name: 'البحرين',
            code: 'BH',
            ibanLength: 22,
            ibanFormat: 'BH00 AAAA 0000 0000 0000 00',
            banks: [
                'بنك البحرين الوطني', 'بنك البحرين والكويت', 'بيت التمويل الكويتي البحرين',
                'البنك الأهلي المتحد', 'مصرف السلام', 'بنك ABC'
            ]
        },
        'OM': {
            name: 'عُمان',
            code: 'OM',
            ibanLength: 23,
            ibanFormat: 'OM00 000 0000 0000 0000 000',
            banks: [
                'بنك مسقط', 'بنك عُمان العربي', 'البنك الوطني العُماني', 'بنك صحار الدولي',
                'بنك نزوى', 'بنك العز الإسلامي', 'بنك ظفار'
            ]
        },
        'SY': {
            name: 'سوريا',
            code: 'SY',
            ibanLength: 24,
            ibanFormat: 'SY00 0000 0000 0000 0000 0000',
            banks: [
                'مصرف سورية المركزي', 'المصرف التجاري السوري', 'المصرف الصناعي', 'المصرف العقاري',
                'بنك البركة سوريا', 'بنك سورية والخليج', 'بنك عودة سورية', 'بنك بيمو السعودي الفرنسي'
            ]
        },
        'LB': {
            name: 'لبنان',
            code: 'LB',
            ibanLength: 28,
            ibanFormat: 'LB00 0000 0000 0000 0000 0000 0000',
            banks: [
                'بنك لبنان والمهجر BLOM', 'بنك عودة', 'بنك بيبلوس', 'بنك البحر المتوسط',
                'فرنسبنك', 'بنك الاعتماد اللبناني', 'البنك اللبناني للتجارة'
            ]
        },
        // Americas
        'US': {
            name: 'أمريكا',
            code: 'US',
            ibanLength: 0, // US doesn't use IBAN
            banks: [
                'Bank of America', 'JPMorgan Chase', 'Wells Fargo', 'Citibank', 'U.S. Bank',
                'PNC Bank', 'Capital One', 'TD Bank', 'Goldman Sachs', 'Morgan Stanley',
                'Charles Schwab', 'American Express', 'Discover Bank', 'HSBC USA', 'BMO Harris'
            ]
        },
        'CA': {
            name: 'كندا',
            code: 'CA',
            ibanLength: 0,
            banks: [
                'Royal Bank of Canada (RBC)', 'Toronto-Dominion Bank (TD)', 'Bank of Nova Scotia (Scotiabank)',
                'Bank of Montreal (BMO)', 'Canadian Imperial Bank (CIBC)', 'National Bank of Canada',
                'Desjardins', 'HSBC Canada', 'Tangerine', 'Simplii Financial'
            ]
        },
        'MX': {
            name: 'المكسيك',
            code: 'MX',
            ibanLength: 18,
            banks: [
                'BBVA México', 'Santander México', 'Citibanamex', 'Banorte', 'HSBC México',
                'Scotiabank México', 'Inbursa', 'Banco Azteca', 'BanCoppel', 'Banco del Bajío'
            ]
        },
        'BR': {
            name: 'البرازيل',
            code: 'BR',
            ibanLength: 29,
            banks: [
                'Banco do Brasil', 'Itaú Unibanco', 'Bradesco', 'Santander Brasil', 'Caixa Econômica',
                'BTG Pactual', 'Banco Safra', 'Nubank', 'Banco Inter', 'C6 Bank'
            ]
        },
        // Europe
        'GB': {
            name: 'بريطانيا',
            code: 'GB',
            ibanLength: 22,
            banks: [
                'HSBC', 'Barclays', 'Lloyds Bank', 'NatWest', 'Santander UK',
                'Royal Bank of Scotland', 'Halifax', 'TSB', 'Metro Bank', 'Monzo',
                'Revolut', 'Starling Bank', 'Virgin Money', 'Co-operative Bank'
            ]
        },
        'DE': {
            name: 'ألمانيا',
            code: 'DE',
            ibanLength: 22,
            banks: [
                'Deutsche Bank', 'Commerzbank', 'DZ Bank', 'KfW', 'UniCredit Bank AG',
                'Postbank', 'ING-DiBa', 'Targobank', 'N26', 'Sparda-Bank',
                'Sparkasse', 'Volksbank', 'Comdirect', 'DKB'
            ]
        },
        'FR': {
            name: 'فرنسا',
            code: 'FR',
            ibanLength: 27,
            banks: [
                'BNP Paribas', 'Crédit Agricole', 'Société Générale', 'Groupe BPCE',
                'Crédit Mutuel', 'La Banque Postale', 'HSBC France', 'CIC',
                'Boursorama', 'Hello Bank', 'Orange Bank', 'N26 France'
            ]
        },
        'IT': {
            name: 'إيطاليا',
            code: 'IT',
            ibanLength: 27,
            banks: [
                'Intesa Sanpaolo', 'UniCredit', 'Banco BPM', 'Monte dei Paschi di Siena',
                'UBI Banca', 'BPER Banca', 'Mediobanca', 'Credem', 'FinecoBank',
                'ING Italia', 'N26 Italia', 'Poste Italiane'
            ]
        },
        'ES': {
            name: 'إسبانيا',
            code: 'ES',
            ibanLength: 24,
            banks: [
                'Santander', 'BBVA', 'CaixaBank', 'Sabadell', 'Bankinter',
                'Ibercaja', 'Unicaja', 'Kutxabank', 'Abanca', 'ING España',
                'Openbank', 'N26 España', 'Revolut España'
            ]
        },
        'NL': {
            name: 'هولندا',
            code: 'NL',
            ibanLength: 18,
            banks: [
                'ING Bank', 'Rabobank', 'ABN AMRO', 'De Volksbank', 'Triodos Bank',
                'Bunq', 'Knab', 'ASN Bank', 'SNS Bank', 'N26 Netherlands'
            ]
        },
        'CH': {
            name: 'سويسرا',
            code: 'CH',
            ibanLength: 21,
            banks: [
                'UBS', 'Credit Suisse', 'Julius Baer', 'Raiffeisen Switzerland', 'PostFinance',
                'Zürcher Kantonalbank', 'Migros Bank', 'Coop Bank', 'Vontobel', 'Lombard Odier'
            ]
        },
        'AT': {
            name: 'النمسا',
            code: 'AT',
            ibanLength: 20,
            banks: [
                'Erste Group', 'Raiffeisen Bank', 'UniCredit Bank Austria', 'BAWAG',
                'Oberbank', 'Hypo Vorarlberg', 'Bank Austria', 'N26 Austria'
            ]
        },
        'PL': {
            name: 'بولندا',
            code: 'PL',
            ibanLength: 28,
            banks: [
                'PKO Bank Polski', 'Bank Pekao', 'Santander Bank Polska', 'mBank',
                'ING Bank Śląski', 'BNP Paribas Polska', 'Millennium Bank', 'Alior Bank'
            ]
        },
        'SE': {
            name: 'السويد',
            code: 'SE',
            ibanLength: 24,
            banks: [
                'Swedbank', 'SEB', 'Handelsbanken', 'Nordea Sweden', 'Danske Bank Sweden',
                'Länsförsäkringar', 'ICA Banken', 'Klarna', 'Avanza'
            ]
        },
        'NO': {
            name: 'النرويج',
            code: 'NO',
            ibanLength: 15,
            banks: [
                'DNB', 'Nordea Norway', 'SpareBank 1', 'Danske Bank Norway',
                'Handelsbanken Norway', 'Sbanken', 'KLP Banken'
            ]
        },
        'DK': {
            name: 'الدانمرك',
            code: 'DK',
            ibanLength: 18,
            banks: [
                'Danske Bank', 'Nordea Denmark', 'Jyske Bank', 'Nykredit',
                'Sydbank', 'Spar Nord', 'Arbejdernes Landsbank', 'Lunar'
            ]
        },
        'RU': {
            name: 'روسيا',
            code: 'RU',
            ibanLength: 0,
            banks: [
                'Sberbank', 'VTB Bank', 'Gazprombank', 'Alfa-Bank', 'Rosbank',
                'Otkritie Bank', 'Raiffeisenbank Russia', 'Tinkoff Bank', 'Sovcombank'
            ]
        },
        // Asia
        'CN': {
            name: 'الصين',
            code: 'CN',
            ibanLength: 0,
            banks: [
                'Industrial and Commercial Bank of China (ICBC)', 'China Construction Bank',
                'Agricultural Bank of China', 'Bank of China', 'Bank of Communications',
                'China Merchants Bank', 'Ping An Bank', 'CITIC Bank', 'Minsheng Bank',
                'Shanghai Pudong Development Bank', 'WeBank', 'MYbank'
            ]
        },
        'JP': {
            name: 'اليابان',
            code: 'JP',
            ibanLength: 0,
            banks: [
                'MUFG Bank', 'Sumitomo Mitsui Banking Corporation (SMBC)', 'Mizuho Bank',
                'Japan Post Bank', 'Resona Bank', 'Saitama Resona Bank',
                'Shinsei Bank', 'Aozora Bank', 'SBI Sumishin Net Bank', 'Sony Bank', 'Rakuten Bank'
            ]
        },
        'KR': {
            name: 'كوريا الجنوبية',
            code: 'KR',
            ibanLength: 0,
            banks: [
                'KB Kookmin Bank', 'Shinhan Bank', 'Woori Bank', 'Hana Bank',
                'NH Bank (Nonghyup)', 'IBK Industrial Bank', 'Standard Chartered Korea',
                'Citibank Korea', 'KakaoBank', 'K Bank', 'Toss Bank'
            ]
        },
        'IN': {
            name: 'الهند',
            code: 'IN',
            ibanLength: 0,
            banks: [
                'State Bank of India (SBI)', 'HDFC Bank', 'ICICI Bank', 'Axis Bank',
                'Kotak Mahindra Bank', 'Punjab National Bank', 'Bank of Baroda',
                'Canara Bank', 'IndusInd Bank', 'Yes Bank', 'IDFC First Bank', 'Paytm Payments Bank'
            ]
        },
        'ID': {
            name: 'إندونيسيا',
            code: 'ID',
            ibanLength: 0,
            banks: [
                'Bank Central Asia (BCA)', 'Bank Mandiri', 'Bank Rakyat Indonesia (BRI)',
                'Bank Negara Indonesia (BNI)', 'CIMB Niaga', 'Bank Danamon',
                'Panin Bank', 'Permata Bank', 'OCBC NISP', 'Bank Jago', 'Jenius'
            ]
        },
        'MY': {
            name: 'ماليزيا',
            code: 'MY',
            ibanLength: 0,
            banks: [
                'Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 'Hong Leong Bank',
                'AmBank', 'Alliance Bank', 'OCBC Malaysia', 'HSBC Malaysia',
                'Standard Chartered Malaysia', 'UOB Malaysia', 'Touch n Go eWallet'
            ]
        },
        'SG': {
            name: 'سنغافورة',
            code: 'SG',
            ibanLength: 0,
            banks: [
                'DBS Bank', 'OCBC Bank', 'UOB', 'Standard Chartered Singapore',
                'Citibank Singapore', 'HSBC Singapore', 'Maybank Singapore',
                'Bank of China Singapore', 'GXS Bank', 'Trust Bank'
            ]
        },
        'TH': {
            name: 'تايلاند',
            code: 'TH',
            ibanLength: 0,
            banks: [
                'Bangkok Bank', 'Kasikornbank', 'Siam Commercial Bank', 'Krung Thai Bank',
                'Bank of Ayudhya (Krungsri)', 'TMBThanachart Bank', 'CIMB Thai',
                'UOB Thailand', 'Krungthai Card'
            ]
        },
        'PH': {
            name: 'الفلبين',
            code: 'PH',
            ibanLength: 0,
            banks: [
                'BDO Unibank', 'Metrobank', 'Bank of the Philippine Islands (BPI)',
                'Land Bank', 'Philippine National Bank (PNB)', 'Security Bank',
                'UnionBank', 'China Bank', 'RCBC', 'GCash', 'Maya'
            ]
        },
        'PK': {
            name: 'باكستان',
            code: 'PK',
            ibanLength: 24,
            banks: [
                'Habib Bank (HBL)', 'United Bank (UBL)', 'MCB Bank', 'Allied Bank',
                'Bank Alfalah', 'Meezan Bank', 'Faysal Bank', 'JS Bank',
                'Standard Chartered Pakistan', 'Bank Al Habib', 'JazzCash', 'Easypaisa'
            ]
        },
        'IQ': {
            name: 'العراق',
            code: 'IQ',
            ibanLength: 23,
            banks: [
                'مصرف الرافدين', 'مصرف الرشيد', 'البنك التجاري العراقي', 'البنك الأهلي العراقي',
                'مصرف بغداد', 'مصرف الشرق الأوسط', 'بنك كردستان الدولي', 'مصرف آسيا العراق'
            ]
        },
        'DZ': {
            name: 'الجزائر',
            code: 'DZ',
            ibanLength: 26,
            banks: [
                'البنك الوطني الجزائري', 'القرض الشعبي الجزائري', 'بنك الجزائر الخارجي',
                'البنك الجزائري للتنمية الريفية', 'بنك البركة الجزائر', 'الشركة العامة الجزائرية',
                'بنك السلام الجزائر', 'BNP Paribas El Djazair'
            ]
        },
        'MA': {
            name: 'المغرب',
            code: 'MA',
            ibanLength: 28,
            banks: [
                'التجاري وفا بنك', 'البنك الشعبي', 'BMCE Bank', 'البنك المغربي للتجارة الخارجية',
                'القرض الفلاحي', 'بنك CIH', 'الشركة العامة المغربية', 'بريد بنك',
                'CFG Bank', 'بنك الصفاء'
            ]
        },
        'TN': {
            name: 'تونس',
            code: 'TN',
            ibanLength: 24,
            banks: [
                'البنك الوطني الفلاحي', 'بنك الإسكان', 'الشركة التونسية للبنك',
                'بنك تونس العربي الدولي', 'البنك العربي لتونس', 'الاتحاد البنكي للتجارة',
                'بنك الأمان', 'بنك قطر الوطني تونس'
            ]
        },
        // Oceania
        'AU': {
            name: 'أستراليا',
            code: 'AU',
            ibanLength: 0,
            banks: [
                'Commonwealth Bank', 'Westpac', 'ANZ Bank', 'National Australia Bank (NAB)',
                'Macquarie Bank', 'Bendigo Bank', 'Bank of Queensland', 'Suncorp',
                'ING Australia', 'Up Bank', 'Judo Bank'
            ]
        },
        'NZ': {
            name: 'نيوزيلندا',
            code: 'NZ',
            ibanLength: 0,
            banks: [
                'ANZ New Zealand', 'BNZ (Bank of New Zealand)', 'Westpac New Zealand',
                'ASB Bank', 'Kiwibank', 'TSB Bank', 'Heartland Bank', 'Co-operative Bank'
            ]
        }
    };
    
    // IBAN Length data for all countries (standard lengths)
    const IBAN_LENGTHS = {
        'AL':28,'AD':24,'AT':20,'AZ':28,'BH':22,'BY':28,'BE':16,'BA':20,'BR':29,'BG':22,
        'CR':22,'HR':21,'CY':28,'CZ':24,'DK':18,'DO':28,'TL':23,'EE':20,'FO':18,'FI':18,
        'FR':27,'GE':22,'DE':22,'GI':23,'GR':27,'GL':18,'GT':28,'HU':28,'IS':26,'IQ':23,
        'IE':22,'IL':23,'IT':27,'JO':30,'KZ':20,'XK':20,'KW':30,'LV':21,'LB':28,'LI':21,
        'LT':20,'LU':20,'MK':19,'MT':31,'MR':27,'MU':30,'MC':27,'MD':24,'ME':22,'NL':18,
        'NO':15,'PK':24,'PS':29,'PL':28,'PT':25,'QA':29,'RO':24,'LC':32,'SM':27,'ST':25,
        'SA':24,'RS':22,'SC':31,'SK':24,'SI':19,'ES':24,'SD':18,'SE':24,'CH':21,'TN':24,
        'TR':26,'UA':29,'AE':23,'GB':22,'VA':22,'VG':24,'EG':29,'OM':23,'SY':24
    };
    
    // Populate bank country dropdown using same COUNTRIES array
    var $bankCountry = $('#bank_country_select');
    var $bankName = $('#bank_name_select');
    
    $bankCountry.empty().append('<option value="">اختر دولة البنك...</option>');
    COUNTRIES.forEach(function(c) {
        var code = c.code.toUpperCase();
        var ibanLen = IBAN_LENGTHS[code] || 24; // Default 24 if not found
        $bankCountry.append('<option value="' + code + '" data-ar="' + c.ar + '" data-en="' + c.en + '" data-iban-len="' + ibanLen + '">' + 
            c.ar + ' - ' + c.en + ' (' + code + ')</option>');
    });
    
    $bankCountry.select2({
        theme: 'bootstrap-5',
        dir: 'rtl',
        placeholder: 'اختر دولة البنك...',
        allowClear: true
    });
    
    $bankName.select2({
        theme: 'bootstrap-5',
        dir: 'rtl',
        placeholder: 'اختر البنك...'
    });
    
    // When bank country changes
    $bankCountry.on('select2:select change', function() {
        var code = $(this).val();
        var $selectedOpt = $(this).find('option:selected');
        var ibanLength = parseInt($selectedOpt.data('iban-len')) || IBAN_LENGTHS[code] || 24;
        var countryAr = $selectedOpt.data('ar') || '';
        var bankData = BANK_DATA[code]; // May be undefined for countries without bank list
        
        if (code) {
            // Update IBAN prefix
            $('#iban_prefix').text(code);
            
            // Show IBAN format info
            var formatHint = code + ' + ' + (ibanLength - 2) + ' حرف/رقم = ' + ibanLength + ' حرف';
            $('#iban_hint').html('صيغة الآيبان: <code dir="ltr">' + formatHint + '</code>');
            
            // Load banks if available for this country
            $bankName.empty();
            if (bankData && bankData.banks && bankData.banks.length > 0) {
                $bankName.append('<option value="">اختر البنك...</option>');
                bankData.banks.forEach(function(bank) {
                    $bankName.append(new Option(bank, bank));
                });
                $bankName.append('<option value="__other__">🏦 بنك آخر (أدخل يدوياً)</option>');
                $bankName.prop('disabled', false);
                $('#bank_name_manual').addClass('d-none').val('');
            } else {
                // No bank list for this country - show manual input
                $bankName.append('<option value="">أدخل اسم البنك يدوياً...</option>');
                $bankName.prop('disabled', true);
                $('#bank_name_manual').removeClass('d-none').attr('placeholder', 'اكتب اسم البنك في ' + countryAr);
            }
        } else {
            $('#iban_prefix').text('--');
            $('#iban_hint').text('اختر دولة البنك لمعرفة صيغة الآيبان الصحيحة');
            $bankName.empty().append('<option value="">اختر دولة البنك أولاً...</option>');
            $bankName.prop('disabled', true);
            $('#bank_name_manual').addClass('d-none').val('');
        }
    });
    
    // Show manual input for "other" bank
    $bankName.on('select2:select change', function() {
        if ($(this).val() === '__other__') {
            $('#bank_name_manual').removeClass('d-none').focus();
        } else {
            $('#bank_name_manual').addClass('d-none').val('');
        }
    });
    
    // IBAN Validation on input
    $('#iban_input').on('input', function() {
        var iban = $(this).val().replace(/\s/g, '').toUpperCase();
        var countryCode = $('#bank_country_select').val();
        var $selectedOpt = $('#bank_country_select').find('option:selected');
        var expectedLength = parseInt($selectedOpt.data('iban-len')) || IBAN_LENGTHS[countryCode] || 24;
        
        // Remove old country code if user pasted full IBAN
        if (countryCode && iban.startsWith(countryCode)) {
            iban = iban.substring(2);
            $(this).val(iban);
        }
        
        // Remove any non-alphanumeric
        iban = iban.replace(/[^A-Z0-9]/g, '');
        $(this).val(iban);
        
        // Validate if country selected
        if (countryCode) {
            var fullIban = countryCode + iban;
            
            if (iban.length > 0) {
                if (fullIban.length < expectedLength) {
                    $('#iban_hint').html('<span class="text-warning">⚠️ الآيبان قصير (' + fullIban.length + '/' + expectedLength + ' حرف)</span>');
                } else if (fullIban.length > expectedLength) {
                    $('#iban_hint').html('<span class="text-danger">❌ الآيبان طويل جداً (' + fullIban.length + '/' + expectedLength + ' حرف)</span>');
                } else {
                    $('#iban_hint').html('<span class="text-success">✅ طول الآيبان صحيح (' + expectedLength + ' حرف)</span>');
                }
            } else {
                var formatHint = countryCode + ' + ' + (expectedLength - 2) + ' حرف/رقم = ' + expectedLength + ' حرف';
                $('#iban_hint').html('صيغة الآيبان: <code dir="ltr">' + formatHint + '</code>');
            }
        }
    });
    
    // Set initial bank country if exists
    var oldBankCountry = "{{ old('bank_country', $store->bank_country ?? '') }}";
    if (oldBankCountry && BANK_DATA[oldBankCountry]) {
        $bankCountry.val(oldBankCountry).trigger('change');
    }
    
    var oldBankName = "{{ old('bank_name', $store->bank_name ?? '') }}";
    if (oldBankName) {
        setTimeout(function() {
            if ($bankName.find('option[value="' + oldBankName + '"]').length) {
                $bankName.val(oldBankName).trigger('change');
            }
        }, 300);
    }
});
</script>

{{-- Load Google Maps API AFTER all functions are defined --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMap" async defer></script>
@endsection