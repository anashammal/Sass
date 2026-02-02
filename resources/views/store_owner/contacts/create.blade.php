@extends('layouts.app')
@if(request()->has('iframe'))
    <style>
        /* إخفاء القوائم والهيدر والفوتر */
        nav.navbar, .main-header, .navbar-header, header, 
        aside, .main-sidebar, .app-sidebar, .sidebar, #sidebar, .sidebar-wrapper, 
        footer, .main-footer, .breadcrumb, .content-header, .btn-back {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            visibility: hidden !important;
        }

        /* ضبط المحتوى ليملأ الشاشة */
        body, .wrapper, .content-wrapper, .main-panel, .main-content {
            margin: 0 !important;
            padding: 0 !important;
            min-height: 100vh !important;
            width: 100% !important;
            max-width: 100% !important;
            background-color: white !important; /* خلفية بيضاء لتبدو نظيفة */
        }
        
        .card { box-shadow: none !important; border: none !important; }
    </style>
@endif
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            {{-- عنوان الصفحة --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-primary fw-bold"><i class="fas fa-user-plus me-2"></i> إضافة جهة اتصال جديدة</h4>
                <a href="{{ route('store.contacts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-success">بيانات جهة الاتصال</h5>
                </div>
                <div class="card-body">

                    {{-- عرض الأخطاء --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('store.contacts.store') }}" id="contactForm" novalidate>
                        @csrf

                        <div class="row g-3">
{{-- 1. اسم الشخص المسؤول --}}
<div class="col-md-6">
    <label for="name" class="form-label required">اسم جهة الاتصال (الشخص المسؤول) <span class="text-danger">*</span></label>
    {{-- تم تغيير name="contact_name" إلى name="name" --}}
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="مثال: محمد أحمد">
</div>
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">اسم الشركة / المؤسسة</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="مثال: شركة الأمانة">
                            </div>

                            {{-- 2. نوع العلاقة (Checkboxes) --}}
                            <div class="col-12">
                                <label class="form-label d-block fw-bold">نوع العلاقة <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="customer" id="type_customer" 
                                           {{ (is_array(old('type')) && in_array('customer', old('type'))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_customer">زبون (Customer)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="supplier" id="type_supplier" 
                                           {{ (is_array(old('type')) && in_array('supplier', old('type'))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_supplier">مورد (Supplier)</label>
                                </div>
                            </div>

                            <hr class="my-4">

                             {{-- 3. معلومات الاتصال --}}
                             <div class="col-md-6">
                                 <label for="phone" class="form-label">رقم الهاتف 
                                     <span id="phone_v_status" class="ms-2" style="display:none;">
                                         <i class="fas fa-certificate text-success" title="تم التحقق"></i>
                                     </span>
                                 </label>
                                 <div class="d-flex gap-1">
                                     <div class="tel-input-wrapper flex-grow-1">
                                         <input type="tel" class="form-control" id="phone_input" value="{{ old('phone') }}" placeholder="5xxxxxxxxx">
                                         <input type="hidden" name="phone" id="full_phone" value="{{ old('phone') }}">
                                         <div id="phone-error" class="phone-error-msg">رقم الهاتف غير صحيح لهذه الدولة</div>
                                     </div>
                                     <button type="button" class="btn btn-light btn-sm border" id="send_phone_v" onclick="triggerVerification('phone')">تحقق</button>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <label for="email" class="form-label">البريد الإلكتروني
                                     <span id="email_v_status" class="ms-2" style="display:none;">
                                         <i class="fas fa-certificate text-success" title="تم التحقق"></i>
                                     </span>
                                 </label>
                                 <div class="d-flex gap-1">
                                     <div class="flex-grow-1">
                                         <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com">
                                     </div>
                                     <button type="button" class="btn btn-light btn-sm border" id="send_email_v" onclick="triggerVerification('email')">تحقق</button>
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
                                            initialCountry: "tr",
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
                                        const emailInput = document.querySelector("#email");
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

                                            // تحويل الصغير
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
                                        // Set initial full phone if exists
                                        if (phoneInput.value) validate();
                                    }

                                    if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', init);
                                    } else {
                                        init();
                                    }
                                })();

                                // دالة إرسال كود التحقق
                                async function triggerVerification(type) {
                                    const value = (type === 'phone') ? document.querySelector("#full_phone").value : document.querySelector("#email").value;
                                    
                                    if (!value) {
                                        Swal.fire('خطأ', 'يرجى إدخال ' + (type === 'phone' ? 'رقم الهاتف' : 'البريد') + ' أولاً', 'error');
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
                                            body: JSON.stringify({ type, value })
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
                                    try {
                                        const response = await fetch("{{ route('store.contacts.verify.confirm') }}", {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                            },
                                            body: JSON.stringify({ type, value, code })
                                        });

                                        const data = await response.json();
                                        if (data.success) {
                                            Swal.fire('ممتاز!', 'تم التحقق من ' + (type === 'phone' ? 'الرقم' : 'الإيميل') + ' بنجاح', 'success');
                                            document.querySelector('#' + type + '_v_status').style.display = 'inline-block';
                                            document.querySelector('#send_' + type + '_v').style.display = 'none';
                                        } else {
                                            Swal.fire('خطأ', data.message, 'error');
                                        }
                                    } catch (e) {
                                        Swal.fire('خطأ', 'فشل التحقق من الرمز', 'error');
                                    }
                                }
                            </script>

                            {{-- 4. العنوان والضريبة --}}
                            <div class="col-md-6">
                                <label for="tax_number" class="form-label">الرقم الضريبي</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" value="{{ old('tax_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">العنوان</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                            </div>

                            <hr class="my-4">

                            {{-- 5. المعلومات المالية --}}
                            <div class="col-md-6">
                                <label for="credit_limit" class="form-label">حد الدين (للزبائن)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="credit_limit" name="credit_limit" value="{{ old('credit_limit', 0) }}">
                                    <span class="input-group-text">TL</span> {{-- أو العملة الافتراضية --}}
                                </div>
                                <div class="form-text">اتركه 0 إذا لم يكن هناك حد.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="opening_balance" class="form-label">الرصيد الافتتاحي</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                                    <span class="input-group-text">TL</span>
                                </div>
                                <div class="form-text text-danger">سالب (-) = عليه دين | موجب (+) = له رصيد</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                                    <i class="fas fa-save me-1"></i> حفظ جهة الاتصال
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection