@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary">تعديل جهة الاتصال: {{ $contact->contact_name }}</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('store.contacts.update', $contact->id) }}" id="contactForm" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">الاسم <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $contact->contact_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الشركة</label>
                                <input type="text" class="form-control" name="company_name" value="{{ old('company_name', $contact->company_name) }}">
                            </div>

                            {{-- 🧠 منطق تحديد النوع (الذكي) --}}
                            <div class="col-12">
                                <label class="form-label d-block fw-bold">النوع <span class="text-danger">*</span></label>
                                
                                {{-- تحديد حالة الزبون --}}
                                @php
                                    $isCustomerChecked = false;
                                    if (old('type')) {
                                        $isCustomerChecked = in_array('customer', old('type'));
                                    } else {
                                        // إذا كان النوع 'customer' أو 'both'
                                        $isCustomerChecked = ($contact->type === 'customer' || $contact->type === 'both');
                                    }
                                @endphp
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="customer" id="type_customer" 
                                           {{ $isCustomerChecked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_customer">زبون</label>
                                </div>

                                {{-- تحديد حالة المورد --}}
                                @php
                                    $isSupplierChecked = false;
                                    if (old('type')) {
                                        $isSupplierChecked = in_array('supplier', old('type'));
                                    } else {
                                        // إذا كان النوع 'supplier' أو 'both'
                                        $isSupplierChecked = ($contact->type === 'supplier' || $contact->type === 'both');
                                    }
                                @endphp
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="supplier" id="type_supplier" 
                                           {{ $isSupplierChecked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_supplier">مورد</label>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="col-md-6">
                                <label class="form-label">الهاتف
                                    <span id="phone_v_status" class="ms-2" style="{{ $contact->phone_verified_at ? 'display:inline-block;' : 'display:none;' }}">
                                        <i class="fas fa-certificate text-success" title="تم التحقق"></i>
                                    </span>
                                </label>
                                <div class="d-flex gap-1">
                                    <div class="tel-input-wrapper flex-grow-1">
                                        <input type="tel" class="form-control" id="phone_input" value="{{ old('phone', $contact->phone) }}" dir="ltr">
                                        <input type="hidden" name="phone" id="full_phone" value="{{ old('phone', $contact->phone) }}">
                                        <div id="phone-error" class="phone-error-msg">رقم الهاتف غير صحيح لهذه الدولة</div>
                                    </div>
                                    @if(!$contact->phone_verified_at)
                                        <button type="button" class="btn btn-light btn-sm border" id="send_phone_v" onclick="triggerVerification('phone')">تحقق</button>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">البريد
                                    <span id="email_v_status" class="ms-2" style="{{ $contact->email_verified_at ? 'display:inline-block;' : 'display:none;' }}">
                                        <i class="fas fa-certificate text-success" title="تم التحقق"></i>
                                    </span>
                                </label>
                                <div class="d-flex gap-1">
                                    <div class="flex-grow-1">
                                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $contact->email) }}">
                                    </div>
                                    @if(!$contact->email_verified_at)
                                        <button type="button" class="btn btn-light btn-sm border" id="send_email_v" onclick="triggerVerification('email')">تحقق</button>
                                    @endif
                                </div>
                            </div>

                            <script>
                                (function() {
                                    const arNames = {
                                        "tr": "تركيا - Turkey", "sa": "السعودية - Saudi Arabia", "ae": "الإمارات - UAE",
                                        "jo": "الأردن - Jordan", "sy": "سوريا - Syria", "eg": "مصر - Egypt",
                                        "kw": "الكويت - Kuwait", "qa": "قطر - Qatar", "bh": "البحرين - Bahrain",
                                        "om": "عمان - Oman", "ye": "اليمن - Yemen", "iq": "العراق - Iraq",
                                        "ps": "فلسطين - Palestine", "lb": "لبنان - Lebanon", "ly": "ليبيا - Libya",
                                        "ma": "المغرب - Morocco", "dz": "الجزائر - Algeria", "tn": "تونس - Tunisia",
                                        "sd": "السودان - Sudan", "so": "الصومال - Somalia", "dj": "جيبوتي - Djibouti",
                                        "mr": "موريتانيا - Mauritania", "km": "جزر القمر - Comoros"
                                    };

                                    function init() {
                                        const phoneInput = document.querySelector("#phone_input");
                                        const fullPhoneInput = document.querySelector("#full_phone");
                                        
                                        if (!phoneInput) return;

                                        if (typeof window.intlTelInput === 'undefined') {
                                            setTimeout(init, 500);
                                            return;
                                        }

                                        const iti = window.intlTelInput(phoneInput, {
                                            initialCountry: "auto",
                                            geoIpLookup: callback => {
                                                fetch("https://ipapi.co/json")
                                                    .then(res => res.json())
                                                    .then(data => callback(data.country_code))
                                                    .catch(() => callback("tr"));
                                            },
                                            preferredCountries: ["tr", "sa", "ae", "jo", "sy", "eg"],
                                            separateDialCode: true,
                                            autoPlaceholder: "aggressive",
                                            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js",
                                            countryNameLocale: "ar",
                                            i18n: {
                                                searchPlaceholder: "ابحث عن دولة...",
                                            }
                                        });

                                        // --- منطق البريد الإلكتروني (إكمال تلقائي ومنع العربي) ---
                                        const emailInput = document.querySelector('input[name="email"]');
                                        const domains = ["gmail.com", "outlook.com", "hotmail.com", "yahoo.com", "icloud.com"];
                                        
                                        if (emailInput) {
                                            emailInput.addEventListener("input", function(e) {
                                                // منع الأحرف العربية تماماً
                                                this.value = this.value.replace(/[\u0600-\u06FF]/g, "");
                                                
                                                const val = this.value;
                                                const atIndex = val.indexOf("@");
                                                
                                                // التلوين بناءً على الصحة
                                                const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                                                this.style.borderColor = isValid ? "#10b981" : "#ef4444";
                                                this.style.boxShadow = isValid ? "0 0 0 0.25rem rgba(16, 185, 129, 0.1)" : "0 0 0 0.25rem rgba(239, 68, 68, 0.1)";

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

                                            emailInput.addEventListener("blur", function() {
                                                this.value = this.value.toLowerCase().trim();
                                            });
                                        }

                                        const validate = () => {
                                            fullPhoneInput.value = iti.getNumber();
                                            if (phoneInput.value.trim()) {
                                                if (iti.isValidNumber()) {
                                                    phoneInput.classList.remove("is-invalid-phone");
                                                    errorMsg.style.display = "none";
                                                } else {
                                                    phoneInput.classList.add("is-invalid-phone");
                                                    errorMsg.style.display = "block";
                                                }
                                            } else {
                                                phoneInput.classList.remove("is-invalid-phone");
                                                errorMsg.style.display = "none";
                                            }
                                        };

                                        phoneInput.addEventListener('keypress', e => {
                                            if (e.which < 48 || e.which > 57) {
                                                if (e.which !== 8 && e.which !== 0 && e.which !== 43) e.preventDefault();
                                            }
                                        });

                                        phoneInput.addEventListener('change', validate);
                                        phoneInput.addEventListener('keyup', validate);
                                        phoneInput.addEventListener('countrychange', validate);
                                        
                                        if (phoneInput.value) setTimeout(validate, 500);
                                    }

                                    if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', init);
                                    } else {
                                        init();
                                    }
                                })();

                                // دالة إرسال كود التحقق للمعدل
                                async function triggerVerification(type) {
                                    const value = (type === 'phone') ? document.querySelector("#full_phone").value : document.querySelector("#email").value;
                                    const contact_id = "{{ $contact->id }}";
                                    
                                    if (!value) {
                                        Swal.fire('خطأ', 'يرجى إدخال البيانات أولاً', 'error');
                                        return;
                                    }

                                    const btn = document.querySelector('#send_' + type + '_v');
                                    btn.disabled = true;
                                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                                    try {
                                        const response = await fetch("{{ route('store.contacts.verify.send') }}", {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                            },
                                            body: JSON.stringify({ type, value, contact_id })
                                        });

                                        const data = await response.json();
                                        if (data.success) {
                                            const { value: code } = await Swal.fire({
                                                title: 'أدخل رمز التحقق',
                                                text: data.message,
                                                input: 'text',
                                                inputPlaceholder: '123456',
                                                showCancelButton: true,
                                                confirmButtonText: 'تأكيد الرمز',
                                                cancelButtonText: 'إلغاء'
                                            });

                                            if (code) {
                                                verifyCode(type, value, code);
                                            }
                                        } else {
                                            Swal.fire('فشل', data.message, 'error');
                                        }
                                    } catch (e) {
                                        Swal.fire('خطأ', 'حدث خطأ غير متوقع', 'error');
                                    } finally {
                                        btn.disabled = false;
                                        btn.innerText = 'تحقق';
                                    }
                                }

                                async function verifyCode(type, value, code) {
                                    const contact_id = "{{ $contact->id }}";
                                    try {
                                        const response = await fetch("{{ route('store.contacts.verify.confirm') }}", {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                            },
                                            body: JSON.stringify({ type, value, code, contact_id })
                                        });

                                        const data = await response.json();
                                        if (data.success) {
                                            Swal.fire('تم التحقق!', 'تم التحقق بنجاح', 'success');
                                            document.querySelector('#' + type + '_v_status').style.display = 'inline-block';
                                            document.querySelector('#send_' + type + '_v').style.display = 'none';
                                        } else {
                                            Swal.fire('خطأ', data.message, 'error');
                                        }
                                    } catch (e) {
                                        Swal.fire('خطأ', 'فشل التحقق من الرمز', 'error');
                                    }
                                }

                                // --- قوقل ماب وإدارة المواقع ---
                                let map, marker, autocomplete;

                                function initMap() {
                                    const savedLat = parseFloat(document.getElementById("latitude").value);
                                    const savedLng = parseFloat(document.getElementById("longitude").value);
                                    
                                    const initialPos = (!isNaN(savedLat) && !isNaN(savedLng)) 
                                        ? { lat: savedLat, lng: savedLng } 
                                        : { lat: 24.7136, lng: 46.6753 }; // الرياض كافتراضي
                                    
                                    map = new google.maps.Map(document.getElementById("map"), {
                                        center: initialPos,
                                        zoom: (!isNaN(savedLat)) ? 17 : 13,
                                        mapTypeControl: false,
                                    });

                                    marker = new google.maps.Marker({
                                        position: initialPos,
                                        map: map,
                                        draggable: true,
                                        animation: google.maps.Animation.DROP,
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

                                        if (place.geometry.viewport) {
                                            map.fitBounds(place.geometry.viewport);
                                        } else {
                                            map.setCenter(place.geometry.location);
                                            map.setZoom(17);
                                        }

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
                                                const currentPos = {
                                                    lat: pos.coords.latitude,
                                                    lng: pos.coords.longitude,
                                                };
                                                map.setCenter(currentPos);
                                                map.setZoom(17);
                                                marker.setPosition(currentPos);
                                                updateCoords(currentPos.lat, currentPos.lng);
                                                reverseGeocode(currentPos);
                                            },
                                            () => Swal.fire('تنبيه', 'تم رفض الوصول لموقعك الحالي', 'warning')
                                        );
                                    }
                                }
                            </script>
                            <script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMap"></script>

                            {{-- المعلومات المالية (نقلت للأعلى) --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">حد الدين</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="credit_limit" value="{{ old('credit_limit', $contact->credit_limit) }}">
                                    <span class="input-group-text">TL</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الرصيد الحالي</label>
                                <input type="text" class="form-control bg-light" value="{{ number_format($contact->balance, 2) }}" readonly>
                            </div>

                            <hr class="my-4">

                            {{-- العنوان والضريبة (الآن في الأسفل وبكامل العرض) --}}
                            <div class="col-12 mb-3">
                                <label class="form-label">الرقم الضريبي</label>
                                <input type="text" class="form-control" name="tax_number" value="{{ old('tax_number', $contact->tax_number) }}" placeholder="أدخل الرقم الضريبي">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">العنوان (قوقل ماب)</label>
                                <div class="input-group shadow-sm border rounded-3 overflow-hidden" dir="ltr">
                                    <button type="button" class="btn btn-primary px-3" onclick="getCurrentLocation()" title="موقعي الحالي">
                                        <i class="fas fa-location-arrow"></i>
                                    </button>
                                    <input type="text" class="form-control border-0" id="address" name="address" value="{{ old('address', $contact->address) }}" 
                                           placeholder="ابحث عن العنوان (سيظهر الإكمال التلقائي هنا)" dir="rtl" style="font-size: 1rem;">
                                </div>
                                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $contact->latitude) }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $contact->longitude) }}">
                                
                                <div id="map" class="mt-3 rounded-3 shadow-sm border" style="height: 350px; width: 100%; background: #f8f9fa;">
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                        <span>يرجى إضافة Google Maps API Key لتفعيل الخريطة</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-3 shadow-sm">حفظ التعديلات</button>
                            </div>

                            <style>
                                /* إصلاح ظهور قائمة مقترحات قوقل ماب */
                                .pac-container {
                                    z-index: 10000 !important;
                                    border-radius: 8px !important;
                                    box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
                                    border-top: none !important;
                                    margin-top: 5px !important; /* لفك التداخل مع مربع البحث */
                                    font-family: inherit;
                                }
                                .pac-item {
                                    padding: 10px;
                                    cursor: pointer;
                                }
                                .pac-item:hover {
                                    background-color: #f8f9fa;
                                }
                            </style>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection