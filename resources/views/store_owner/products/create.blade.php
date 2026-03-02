@extends('layouts.app')
@if(request()->has('iframe'))
    <style>
        /* 1. إخفاء الهيدر (الشريط العلوي) */
        nav.navbar, .main-header, .navbar-header, header {
            display: none !important;
        }

        /* 2. إخفاء السايد بار (الشريط الجانبي) - تم إضافة معرفات أكثر شمولاً */
        aside, .main-sidebar, .app-sidebar, .sidebar, #sidebar, .sidebar-wrapper, .main-menu, .sidenav {
            display: none !important;
            width: 0 !important;
            visibility: hidden !important;
        }

        /* 3. إخفاء الفوتر */
        footer, .main-footer {
            display: none !important;
        }

        /* 4. إصلاح عرض المحتوى ليأخذ كامل الشاشة وإلغاء إزاحة اليمين */
        body, .wrapper, .content-wrapper, .main-panel, .main-content {
            margin: 0 !important;
            margin-right: 0 !important; /* ضروري جداً في التنسيق العربي RTL لإلغاء مكان السايدبار */
            margin-left: 0 !important;
            padding: 0 !important;
            min-height: 100vh !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* 5. إخفاء أي عناصر تنقل أخرى قد تظهر */
        .btn-back, .breadcrumb, .content-header {
            display: none !important;
        }
    </style>
@endif
@section('content')
<div class="container-fluid">
    {{-- عرض الأخطاء في الأعلى --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('store.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm" novalidate>
        @csrf
        <div class="card shadow-sm border-0 mb-4">
                <h5 class="mb-0"><i class="fas fa-box-open me-2"></i> {{ __('إضافة منتج جديد') }} </h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', 'on') == 'on' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-white" for="is_active"> {{ __('منتج فعال') }} </label>
                </div>
            </div>
            
            <div class="card-body bg-light">
                {{-- البيانات الأساسية --}}
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold"> {{ __('اسم المنتج') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" required placeholder="{{ __('مثال: شيبس ليز ملح') }}" value="{{ old('name') }}">
                            <button class="btn btn-outline-primary" type="button" id="google_search_btn" title="{{ __('بحث في جوجل صور') }}">
                                <i class="fab fa-google"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold"> {{ __('التصنيف') }} <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value=""> {{ __('-- اختر تصنيف --') }} </option>
                            @if(isset($categories))
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label"> {{ __('الوصف') }} </label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                    </div>

                        <input type="hidden" name="product_type" value="standard">
                </div>

                <hr class="my-4 text-secondary">

                {{-- الوحدة الأساسية --}}
                <div class="card border-success shadow-sm mb-3">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fas fa-cube me-1"></i> {{ __('الوحدة الأساسية (أصغر وحدة)') }} </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            {{-- صورة كبيرة وواضحة --}}
                            <div class="col-md-2 text-center">
                                <label class="form-label small fw-bold"> {{ __('صورة الوحدة') }} </label>
                                <div class="position-relative">
                                    <img id="base_preview" src="{{ asset('images/default-product.png') }}" class="img-thumbnail mb-2" style="height: 120px; width: 120px; object-fit: contain;">
                                    <div class="localized-file-wrapper">
                                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                                        </button>
                                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                                        <input type="file" name="base_unit_image" accept="image/*" onchange="previewImage(this, 'base_preview'); updateFileName(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-10">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small"> {{ __('اسم الوحدة') }} </label>
                                        {{-- استعادة اختيار القائمة --}}
                                        <select name="base_unit_select" class="form-select" id="base_unit_select" onchange="handleBaseUnitChange(this)">
                                            @php $sel = old('base_unit_select', 'قطعة'); @endphp
                                            <option value="قطعة" {{ $sel == 'قطعة' ? 'selected' : '' }}> {{ __('قطعة') }} </option>
                                            <option value="كيلو" {{ $sel == 'كيلو' ? 'selected' : '' }}> {{ __('كيلو') }} </option>
                                            <option value="علبة" {{ $sel == 'علبة' ? 'selected' : '' }}> {{ __('علبة') }} </option>
                                            <option value="custom" {{ $sel == 'custom' ? 'selected' : '' }}> {{ __('مخصص..') }} </option>
                                        </select>
                                        {{-- استعادة النص المخصص --}}
                                        <input type="text" name="base_unit_name" id="base_unit_input" class="form-control {{ $sel == 'custom' ? '' : 'd-none' }} mt-1" value="{{ old('base_unit_name', 'قطعة') }}">
                                    </div>

                                                                            <div class="col-md-3">
                                        <label class="form-label small text-danger fw-bold"> {{ __('سعر الشراء (للعبوة)') }} </label>
                                        <div class="input-group">
                                            <input type="number" step="any" name="purchase_price" id="purchase_price" class="form-control text-center" value="{{ old('purchase_price', 0) }}" required oninput="calculateBaseCost()">
                                            <select name="purchase_price_currency_id" id="purchase_price_currency_id" class="form-select form-select-sm currency-select-trigger" style="max-width: 90px;" data-target="purchase_exchange_rate">
                                                <option value="{{ $store->base_currency_id }}">{{ $store->baseCurrency->code }}</option>
                                                @foreach($acceptedCurrencies as $cur)
                                                    @if($cur->id != $store->base_currency_id)
                                                        <option value="{{ $cur->id }}">{{ $cur->code }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="purchase_exchange_rate" id="purchase_exchange_rate" value="{{ old('purchase_exchange_rate', 1) }}">
                                        </div>
                                        <div id="purchase_price_base_hint" class="small text-muted mt-1 d-none" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle me-1"></i> {{ __('ما يعادله بالعملة الافتراضية:') }} <span class="fw-bold text-dark base-val">0</span> {{ $store->baseCurrency->code }}
                                        </div>
                                    </div>


                                    <div class="col-md-2">
                                        <label class="form-label small"> {{ __('عدد القطع بالعبوة') }} </label>
                                        <input type="number" step="any" name="pieces_per_unit" id="pieces_per_unit" class="form-control text-center" value="{{ old('pieces_per_unit', 1) }}" oninput="calculateBaseCost()">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold"> {{ __('باركود الوحدة') }} </label>
                                        <input type="text" name="base_barcode" class="form-control" placeholder="{{ __('تلقائي إذا فارغ') }}" value="{{ old('base_barcode') }}">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-end justify-content-start gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="base_is_purchase" id="base_is_purchase" {{ old('base_is_purchase', 'on') == 'on' ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold" for="base_is_purchase"> {{ __('شراء') }} </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="base_is_sale" id="base_is_sale" {{ old('base_is_sale', 'on') == 'on' ? 'checked' : '' }} onchange="toggleSellingFields()">
                                            <label class="form-check-label small fw-bold" for="base_is_sale"> {{ __('بيع') }} </label>
                                        </div>
                                    </div>

                                                                            <div class="col-md-3" id="base_selling_price_div">
                                        <label class="form-label small text-success fw-bold"> {{ __('سعر البيع') }} </label>
                                        <div class="input-group">
                                            <input type="number" step="any" name="base_selling_price" id="base_sell" class="form-control text-center fw-bold" value="{{ old('base_selling_price', 0) }}" required oninput="calculateMargin('base')">
                                            <select name="base_selling_price_currency_id" id="base_selling_price_currency_id" class="form-select form-select-sm currency-select-trigger" style="max-width: 90px;" data-target="base_sell_exchange_rate">
                                                <option value="{{ $store->base_currency_id }}">{{ $store->baseCurrency->code }}</option>
                                                @foreach($acceptedCurrencies as $cur)
                                                    @if($cur->id != $store->base_currency_id)
                                                        <option value="{{ $cur->id }}">{{ $cur->code }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="base_sell_exchange_rate" id="base_sell_exchange_rate" value="{{ old('base_sell_exchange_rate', 1) }}">
                                        </div>
                                    </div>
                                        
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted"> {{ __('التكلفة (للقطعة)') }} </label>
                                        <div class="input-group">
                                            <input type="text" id="calculated_base_cost" class="form-control bg-light text-center fw-bold" readonly value="0">
                                            <span class="input-group-text bg-light fw-bold text-muted base-currency-label">{{ $store->baseCurrency->code }}</span>
                                        </div>
                                        <input type="hidden" name="base_cost_price" id="base_cost" value="0">
                                    </div>
                                     
                                        
                                    <div class="col-md-2" id="base_margin_div">
                                        <label class="form-label small"> {{ __('الربح %') }} </label>
                                        <input type="number" step="any" name="base_profit_percent" id="base_margin" class="form-control text-center text-primary" value="{{ old('base_profit_percent', 0) }}" oninput="calculatePriceFromMargin('base')">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label small"> {{ __('الضريبة المضافة') }} </label>
                                        <select name="tax_percent" id="tax_percent" class="form-select bg-warning bg-opacity-10" onchange="calculatePriceWithTax()">
                                            @foreach($taxRates as $rate)
                                                <option value="{{ $rate }}" {{ old('tax_percent') == $rate ? 'selected' : '' }}>{{ $rate }}%</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted"> {{ __('السعر مع الضريبة') }} </label>
                                        <div class="input-group">
                                            <input type="text" id="price_with_tax" class="form-control bg-light fw-bold text-success text-center" readonly>
                                            <span class="input-group-text bg-light fw-bold text-success sell-currency-label">{{ $store->baseCurrency->code }}</span>
                                        </div>
                                        <div id="price_with_tax_base_hint" class="small text-muted mt-1 d-none" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle me-1"></i> {{ __('ما يعادله بالعملة الافتراضية:') }} <span class="fw-bold text-dark base-val">0</span> <span class="base-curr">{{ $store->baseCurrency->code }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- الوحدات الإضافية --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark"><i class="fas fa-layer-group me-1"></i> {{ __('الوحدات الإضافية (كرتون، درزن...)') }} </h6>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addExtraUnit()"><i class="fa fa-plus"></i> {{ __('إضافة وحدة') }} </button>
                </div>
                <div id="extra_units_container"></div>

                <hr class="my-4">

                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-warning bg-opacity-25"> {{ __('حد التنبيه للمخزون') }} </span>
                            <input type="number" name="alert_quantity" class="form-control text-center" value="{{ old('alert_quantity', 5) }}">
                        </div>
                    </div>
                    <div class="col-md-8 text-end">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow"><i class="fas fa-save me-2"></i> {{ __('حفظ المنتج') }} </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- قالب الوحدة الإضافية --}}
<template id="unit_template">
    <div class="unit-row card border-secondary mb-2 shadow-sm position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 bg-danger" onclick="this.closest('.unit-row').remove()"></button>
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-2 text-center">
                    <img src="{{ asset('images/default-product.png') }}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: contain;">
                    <div class="localized-file-wrapper mt-1">
                        <button type="button" class="btn btn-xs btn-outline-secondary localized-file-btn" style="font-size: 0.7rem; padding: 2px 5px;">
                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                        </button>
                        <div class="localized-file-name text-start" style="font-size: 0.7rem;"> {{ __('لم يتم اختيار ملف') }} </div>
                        <input type="file" name="units[INDEX][image]" accept="image/*" onchange="previewImage(this); updateFileName(this)">
                    </div>
                </div>
                
                <div class="col-md-10">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="small fw-bold"> {{ __('اسم الوحدة') }} </label>
                            <select name="units[INDEX][name_select]" class="form-select form-select-sm unit-select fw-bold" onchange="handleUnitChange(this)">
                                <option value="كرتون"> {{ __('كرتون') }} </option>
                                <option value="درزن"> {{ __('درزن') }} </option>
                                <option value="شريط"> {{ __('شريط') }} </option>
                                <option value="custom"> {{ __('مخصص..') }} </option>
                            </select>
                            <input type="text" name="units[INDEX][name]" class="form-control form-control-sm d-none mt-1 unit-custom-input fw-bold" placeholder="{{ __('اكتب الاسم') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="small fw-bold"> {{ __('التحويل') }} </label>
                            <input type="number" name="units[INDEX][factor]" class="form-control form-control-sm unit-factor fw-bold" value="1" oninput="calculateUnitCost(this)">
                        </div>

                        <div class="col-md-3">
                            <label class="small fw-bold"> {{ __('الباركود') }} </label>
                            <input type="text" name="units[INDEX][barcode]" class="form-control form-control-sm fw-bold">
                        </div>

                        <div class="col-md-4 d-flex align-items-end justify-content-start gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="units[INDEX][is_purchase]" checked>
                                <label class="form-check-label small fw-bold"> {{ __('شراء') }} </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="units[INDEX][is_sale]" checked onchange="toggleExtraUnitSale(this)">
                                <label class="form-check-label small fw-bold"> {{ __('بيع') }} </label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="small text-danger fw-bold"> {{ __('سعر الشراء') }} </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="any" name="units[INDEX][purchase_price]" class="form-control form-control-sm unit-purchase fw-bold text-danger" oninput="calculateUnitCost(this)">
                                <select name="units[INDEX][purchase_price_currency_id]" class="form-select currency-select-purchase currency-select-trigger" style="max-width: 80px;" data-target="units[INDEX][purchase_exchange_rate]">
                                    <option value="{{ $store->base_currency_id }}">{{ $store->baseCurrency->code }}</option>
                                    @foreach($acceptedCurrencies as $cur)
                                        @if($cur->id != $store->base_currency_id)
                                            <option value="{{ $cur->id }}">{{ $cur->code }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="hidden" name="units[INDEX][purchase_exchange_rate]" class="unit-purchase-rate" value="1">
                            </div>
                            <div class="unit-purchase-base-hint small text-muted mt-1 d-none" style="font-size: 0.7rem;">
                                {{ __('ما يعادله:') }} <span class="fw-bold text-dark base-val">0</span> {{ $store->baseCurrency->code }}
                            </div>
                            <input type="hidden" name="units[INDEX][cost_price]" class="unit-cost">
                        </div>

                        <div class="col-md-4 unit-profit-div">
                            <label class="small fw-bold text-primary"> {{ __('الربح %') }} </label>
                            <input type="number" step="any" name="units[INDEX][profit_percent]" class="form-control form-control-sm unit-profit fw-bold text-primary" oninput="calcExtraUnitSell(this)">
                        </div>

                        <div class="col-md-4 unit-sell-div">
                            <label class="small text-success fw-bold"> {{ __('سعر البيع') }} </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="any" name="units[INDEX][selling_price]" class="form-control form-control-sm unit-sell fw-bold text-success text-center" oninput="calcExtraUnitProfit(this)">
                                <select name="units[INDEX][sell_price_currency_id]" class="form-select currency-select-trigger" style="max-width: 80px;" data-target="units[INDEX][sell_exchange_rate]">
                                    <option value="{{ $store->base_currency_id }}">{{ $store->baseCurrency->code }}</option>
                                    @foreach($acceptedCurrencies as $cur)
                                        @if($cur->id != $store->base_currency_id)
                                            <option value="{{ $cur->id }}">{{ $cur->code }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="hidden" name="units[INDEX][sell_exchange_rate]" class="unit-sell-rate" value="1">
                            </div>
                            <div class="unit-sell-base-hint small text-muted mt-1 d-none" style="font-size: 0.7rem;">
                                {{ __('ما يعادله:') }} <span class="fw-bold text-dark base-val">0</span> {{ $store->baseCurrency->code }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@section('scripts')
<script>
    // عند تحميل الصفحة، نحسب التكاليف فوراً (لأن القيم قد تكون مسترجعة من old)
    document.addEventListener("DOMContentLoaded", function() {
        calculateBaseCost();
        toggleSellingFields();
        
        // تفعيل مستمعات العملات للوحدة الأساسية
        initCurrencyTriggers(document);

        // 🔥 استعادة الوحدات الإضافية القديمة عند الخطأ 🔥
        const oldUnits = @json(old('units', []));
        if(Object.keys(oldUnits).length > 0) {
            Object.keys(oldUnits).forEach(key => {
                let unitData = oldUnits[key];
                addExtraUnit(unitData);
            });
        }
    });

    document.getElementById('google_search_btn').addEventListener('click', function(e) {
        e.preventDefault();
        let name = document.querySelector('input[name="name"]').value;
        if(name) window.open('https://www.google.com/search?tbm=isch&q=' + encodeURIComponent(name), '_blank');
        else alert("{{ __('الرجاء كتابة اسم المنتج بالعربي أولاً') }}");
    });

    function previewImage(input, imgId) {
        let img = imgId ? document.getElementById(imgId) : input.previousElementSibling;
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) { img.src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function handleBaseUnitChange(select) {
        let input = document.getElementById('base_unit_input');
        if (select.value === 'custom') {
            input.classList.remove('d-none');
            // لا نفرغ الحقل هنا إذا كانت القيمة قادمة من old
            if(!input.value || input.value == 'قطعة') input.value = '';
            input.focus();
        } else {
            input.classList.add('d-none');
            input.value = select.value;
        }
    }

    function toggleSellingFields() {
        const isSale = document.getElementById('base_is_sale').checked;
        const sellDiv = document.getElementById('base_selling_price_div');
        const marginDiv = document.getElementById('base_margin_div');
        
        if (isSale) {
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
            document.getElementById('base_sell').required = true;
        } else {
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';
            document.getElementById('base_sell').required = false;
        }
    }

    function toggleExtraUnitSale(checkbox) {
        const row = checkbox.closest('.unit-row');
        const profitDiv = row.querySelector('.unit-profit-div');
        const sellDiv = row.querySelector('.unit-sell-div');
        const sellInput = row.querySelector('.unit-sell');

        if (checkbox.checked) {
            profitDiv.style.display = 'block';
            sellDiv.style.display = 'block';
            sellInput.required = true;
        } else {
            profitDiv.style.display = 'none';
            sellDiv.style.display = 'none';
            sellInput.required = false;
        }
    }

    function handleUnitChange(select) {
        let input = select.nextElementSibling; 
        if (select.value === 'custom') {
            input.classList.remove('d-none');
            input.value = '';
            input.focus();
        } else {
            input.classList.add('d-none');
            input.value = select.value;
        }
    }

    // --- حسابات التكلفة ---
    function formatNum(num) { return isNaN(num) ? 0 : parseFloat(parseFloat(num).toFixed(10)); }

    const baseCurrencyId = '{{ $store->base_currency_id }}';
    const storeCurrencies = @json($acceptedCurrencies->keyBy('id'));
    let confirmedRates = {};
    let isCalculating = false;
    const currenciesData = @json($currenciesData);
    const baseCurrencyCode = "{{ optional($store->baseCurrency)->code ?? 'TRY' }}";

    function initCurrencyTriggers(container) {
        container.querySelectorAll('.currency-select-trigger').forEach(select => {
            select.addEventListener('change', function() {
                handleCurrencyChange(this);
            });
        });
    }

    function handleCurrencyChange(select) {
        const currencyId = select.value;
        const targetInputName = select.getAttribute('data-target');
        
        // إذا كانت العملة الأساسية، السعر دائماً 1
        if (currencyId == baseCurrencyId) {
            updateGlobalCurrencyRate(currencyId, 1);
            return;
        }

        // إذا كان لدينا سعر مؤكد مسبقاً، نستخدمه فوراً
        if (confirmedRates[currencyId]) {
            updateGlobalCurrencyRate(currencyId, confirmedRates[currencyId]);
            return;
        }

        const currency = storeCurrencies[currencyId];
        const defaultRate = currenciesData[currencyId] || (currency ? (parseFloat(currency.pivot.custom_rate) || 1) : 1);
        const currencyCode = currency ? currency.code : '';
        const baseCurrCode = '{{ $store->baseCurrency->code }}';

        Swal.fire({
            title: `💱 {{ __('سعر الصرف') }}: ${currencyCode} ↔ ${baseCurrCode}`,
            icon: 'info',
            html: `
                <div class="text-start mb-3" dir="rtl">
                    <label class="form-label text-muted small">📡 {{ __('السعر المقترح (من الإعدادات):') }}</label>
                    <div class="input-group mb-1">
                        <span class="input-group-text bg-light fw-bold">1 ${currencyCode}</span>
                        <input type="number" id="swalSuggestedRate" class="form-control text-center text-info fw-bold" value="${defaultRate}" readonly>
                        <span class="input-group-text">${baseCurrCode}</span>
                    </div>
                </div>
                <hr>
                <div class="text-start mb-3" dir="rtl">
                    <label class="form-label fw-bold">✏️ {{ __('سعر الصرف المعتمد لهذا المنتج:') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white fw-bold">1 ${currencyCode}</span>
                        <input type="text" inputmode="decimal" id="swalConfirmedRate" class="form-control text-center fw-bold fs-5" value="${defaultRate}">
                        <span class="input-group-text fw-bold">${baseCurrCode}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="document.getElementById('swalConfirmedRate').value=document.getElementById('swalSuggestedRate').value">
                        ↩️ {{ __('استخدم المقترح') }}
                    </button>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '✅ {{ __('تأكيد') }}',
            cancelButtonText: '❌ {{ __('إلغاء') }}',
            confirmButtonColor: '#198754',
            preConfirm: () => {
                let val = parseFloat(document.getElementById('swalConfirmedRate').value);
                if (!val || val <= 0) {
                    Swal.showValidationMessage('{{ __('يرجى إدخال سعر صرف صحيح أكبر من صفر') }}');
                    return false;
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                confirmedRates[currencyId] = result.value;
                updateGlobalCurrencyRate(currencyId, result.value);
            } else {
                select.value = baseCurrencyId;
                updateGlobalCurrencyRate(baseCurrencyId, 1);
            }
        });
    }

    function updateGlobalCurrencyRate(currencyId, rate) {
        // تحديث جميع المدخلات المخفية التي تتبع لعملة مختارة بنفس القيمة
        document.querySelectorAll('.currency-select-trigger').forEach(sel => {
            if (sel.value == currencyId) {
                const targetName = sel.getAttribute('data-target');
                let input;
                if (targetName.includes('INDEX') || targetName.includes('units')) {
                    const row = sel.closest('.unit-row');
                    input = targetName.includes('purchase') ? row.querySelector('.unit-purchase-rate') : row.querySelector('.unit-sell-rate');
                } else {
                    input = document.getElementById(targetName);
                }
                if (input) input.value = rate;
            }
        });

        // تشغيل الحسابات
        calculateBaseCost();
        calculatePriceWithTax();
        updateAllUnitsCosts();
    }

    function calculateBaseCost() {
        if (isCalculating) return;
        isCalculating = true;

        let price = parseFloat(document.getElementById('purchase_price').value) || 0;
        let pieces = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
        let rate = parseFloat(document.getElementById('purchase_exchange_rate').value) || 1;
        
        // التحويل للعملة الأساسية ثم القسمة على القطع
        let costInBase = (price * rate) / pieces;
        
        document.getElementById('base_cost').value = costInBase;
        document.getElementById('calculated_base_cost').value = formatNum(costInBase);

        // تحديث تلميح سعر الشراء بالعملة الأساسية
        const hintDiv = document.getElementById('purchase_price_base_hint');
        const curId = document.getElementById('purchase_price_currency_id').value;
        if (curId != baseCurrencyId) {
            hintDiv.classList.remove('d-none');
            hintDiv.querySelector('.base-val').innerText = formatNum(price * rate);
        } else {
            hintDiv.classList.add('d-none');
        }

        isCalculating = false;
        updateAllUnitsCosts();
        calculateMargin('base');
    }

    function calculateUnitCost(input) {
        let row = input.closest('.unit-row');
        let factor = parseFloat(row.querySelector('.unit-factor').value) || 1;
        let purchaseRate = parseFloat(row.querySelector('.unit-purchase-rate').value) || 1;
        let purchaseCurId = row.querySelector('.currency-select-purchase').value;
        const purchaseHint = row.querySelector('.unit-purchase-base-hint');
        const purchasePriceInput = row.querySelector('.unit-purchase');

        if (input.classList.contains('unit-purchase')) {
            // المستخدم أدخل سعر شراء لهذه الوحدة يدوياً
            if (isCalculating) return;
            isCalculating = true;

            let manualPurchasePrice = parseFloat(input.value) || 0;
            let costInBase = (manualPurchasePrice * purchaseRate) / factor;
            
            // تحديث السعر الأساسي (Master)
            document.getElementById('base_cost').value = costInBase;
            document.getElementById('calculated_base_cost').value = formatNum(costInBase);
            
            // تحديث سعر شراء المادة الأساسية أيضاً ليعكس التكلفة الجديدة
            let basePurchaseRate = parseFloat(document.getElementById('purchase_exchange_rate').value) || 1;
            let piecesPerUnit = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
            document.getElementById('purchase_price').value = formatNum((costInBase * piecesPerUnit) / basePurchaseRate);

            // تحديث تلميح السعر الأساسي
            const baseHintDiv = document.getElementById('purchase_price_base_hint');
            if (document.getElementById('purchase_price_currency_id').value != baseCurrencyId) {
                baseHintDiv.classList.remove('d-none');
                baseHintDiv.querySelector('.base-val').innerText = formatNum(costInBase * piecesPerUnit);
            }

            isCalculating = false;
            updateAllUnitsCosts(row); // تحديث باقي الوحدات
        } else {
            // التكلفة الأساسية أو المعامل تغير
            let baseCost = parseFloat(document.getElementById('base_cost').value) || 0;
            let unitCostInBase = baseCost * factor;
            row.querySelector('.unit-cost').value = formatNum(unitCostInBase);
            
            // تحديث حقل سعر شراء الوحدة إذا لم يكن هو المصدر
            if (!isCalculating) {
                purchasePriceInput.value = formatNum(unitCostInBase / purchaseRate);
            }
        }

        // تحديث تلميح سعر شراء الوحدة الإضافية
        if (purchaseCurId != baseCurrencyId) {
            purchaseHint.classList.remove('d-none');
            purchaseHint.querySelector('.base-val').innerText = formatNum(parseFloat(purchasePriceInput.value) * purchaseRate);
        } else {
            purchaseHint.classList.add('d-none');
        }
        
        calcExtraUnitSell(row.querySelector('.unit-profit'));
    }

    function updateAllUnitsCosts(excludeRow = null) {
        document.querySelectorAll('.unit-row').forEach(row => {
            if (row === excludeRow) return;
            calculateUnitCost(row.querySelector('.unit-factor'));
        });
    }

    function calculatePriceWithTax() {
        let price = parseFloat(document.getElementById('base_sell').value) || 0;
        let tax = parseFloat(document.getElementById('tax_percent').value) || 0;
        let rate = parseFloat(document.getElementById('base_sell_exchange_rate').value) || 1;
        
        let final = price * (1 + (tax / 100));
        document.getElementById('price_with_tax').value = formatNum(final);
        
        // تحديث رمز العملة
        const sellCurrencyId = document.getElementById('base_selling_price_currency_id').value;
        const sellCurrency = storeCurrencies[sellCurrencyId];
        const sellCurrencyCode = sellCurrency ? sellCurrency.code : baseCurrencyCode;
        document.querySelector('.sell-currency-label').innerText = sellCurrencyCode;

        // تحديث التلميح إذا كانت العملة أجنبية
        const hintDiv = document.getElementById('price_with_tax_base_hint');
        if (sellCurrencyId != baseCurrencyId) {
            hintDiv.classList.remove('d-none');
            hintDiv.querySelector('.base-val').innerText = formatNum(final * rate);
        } else {
            hintDiv.classList.add('d-none');
        }
    }

    function calculateMargin(type) {
        if(type === 'base') {
            let cost = parseFloat(document.getElementById('base_cost').value) || 0;
            let sell = parseFloat(document.getElementById('base_sell').value) || 0;
            let rate = parseFloat(document.getElementById('base_sell_exchange_rate').value) || 1;
            
            let sellInBase = sell * rate;
            
            if(cost > 0) document.getElementById('base_margin').value = formatNum(((sellInBase - cost) / cost) * 100);
            calculatePriceWithTax();
        }
    }

    function calculatePriceFromMargin(type) {
        if(type === 'base') {
            let cost = parseFloat(document.getElementById('base_cost').value) || 0;
            let margin = parseFloat(document.getElementById('base_margin').value) || 0;
            let rate = parseFloat(document.getElementById('base_sell_exchange_rate').value) || 1;
            
            let sellInBase = cost * (1 + (margin / 100));
            let sellInCurrency = sellInBase / rate;
            
            document.getElementById('base_sell').value = formatNum(sellInCurrency);
            calculatePriceWithTax();
        }
    }

    function calcExtraUnitSell(input) {
        let row = input.closest('.unit-row');
        let cost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let profit = parseFloat(input.value) || 0;
        let rate = parseFloat(row.querySelector('.unit-sell-rate').value) || 1;
        let sellInput = row.querySelector('.unit-sell');
        
        if(cost > 0) {
            let sellingPriceInBase = cost * (1 + (profit / 100));
            let sellingPriceInCurrency = sellingPriceInBase / rate;
            sellInput.value = formatNum(sellingPriceInCurrency);
            
            // تحديث التلميح
            const curSelector = row.querySelector('[name*="[sell_price_currency_id]"]');
            const hint = row.querySelector('.unit-sell-base-hint');
            if (curSelector.value != baseCurrencyId) {
                hint.classList.remove('d-none');
                hint.querySelector('.base-val').innerText = formatNum(sellingPriceInBase);
            } else {
                hint.classList.add('d-none');
            }
        }
    }

    function calcExtraUnitProfit(input) {
        let row = input.closest('.unit-row');
        let cost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let sell = parseFloat(input.value) || 0;
        let rate = parseFloat(row.querySelector('.unit-sell-rate').value) || 1;
        let profitInput = row.querySelector('.unit-profit');
        
        if(cost > 0) {
            let sellInBase = sell * rate;
            let profitPercent = ((sellInBase - cost) / cost) * 100;
            profitInput.value = formatNum(profitPercent);
        }
    }

    let unitIndex = 0;
    // تعديل الدالة لتقبل بيانات قديمة (oldData)
    function addExtraUnit(data = null) {
        let tpl = document.getElementById('unit_template').innerHTML.replace(/INDEX/g, unitIndex);
        document.getElementById('extra_units_container').insertAdjacentHTML('beforeend', tpl);
        
        let row = document.getElementById('extra_units_container').lastElementChild;
        
        // إذا كان هناك بيانات مسترجعة، نملأ الحقول
        if(data) {
            let select = row.querySelector('.unit-select');
            let input = row.querySelector('.unit-custom-input');
            
            // تعبئة الاسم
            let nameVal = data.name_select || data.name;
            if(['كرتون','درزن','شريط'].includes(nameVal)) {
                select.value = nameVal;
            } else {
                select.value = 'custom';
                input.classList.remove('d-none');
                input.value = data.name || '';
            }

            row.querySelector('.unit-factor').value = data.factor || 1;
            row.querySelector('[name*="[barcode]"]').value = data.barcode || '';
            row.querySelector('.unit-profit').value = data.profit_percent || 0;
            row.querySelector('.unit-sell').value = data.selling_price || 0;

            if(data.purchase_price) row.querySelector('.unit-purchase').value = data.purchase_price;
            if(data.purchase_price_currency_id) row.querySelector('.currency-select-purchase').value = data.purchase_price_currency_id;
            if(data.purchase_exchange_rate) row.querySelector('.unit-purchase-rate').value = data.purchase_exchange_rate;
            
            if(data.sell_price_currency_id) row.querySelector('[name*="[sell_price_currency_id]"]').value = data.sell_price_currency_id;
            if(data.sell_exchange_rate) row.querySelector('.unit-sell-rate').value = data.sell_exchange_rate;

            // إعادة الحساب
            calculateUnitCost(row.querySelector('.unit-factor'));
            toggleExtraUnitSale(row.querySelector('[name*="[is_sale]"]'));
        }

        // تهيئة مستمعات العملات للوحدة الجديدة
        initCurrencyTriggers(row);
        
        // المزامنة العالمية للوحدة الجديدة
        const pCur = row.querySelector('.currency-select-purchase').value;
        if (confirmedRates[pCur]) {
            row.querySelector('.unit-purchase-rate').value = confirmedRates[pCur];
        }
        const sCur = row.querySelector('.currency-select-trigger:not(.currency-select-purchase)').value;
        if (confirmedRates[sCur]) {
            row.querySelector('.unit-sell-rate').value = confirmedRates[sCur];
        }

        // تحديث الحسابات للوحدة الجديدة لإظهار تلميحات العملة
        calculateUnitCost(row.querySelector('.unit-factor'));
        
        unitIndex++;
    }

    // --- منطق المطاعم والريسبي ---
    function toggleRecipeBuilder() {
        let type = document.querySelector('input[name="product_type"]:checked')?.value;
        let section = document.getElementById('recipe_builder_section');
        let purchaseCard = document.querySelector('.card.border-success'); // الوحدة الأساسية
        
        if (type === 'meal') {
            if(section) section.style.display = 'block';
            // في الوجبة لا نشتري، فقط نبيع
            document.getElementById('base_is_purchase').checked = false;
            document.getElementById('base_is_sale').checked = true;
            // يمكن جعل حقل سعر الشراء للقراءة فقط أو إخفاؤه
        } else if (type === 'ingredient') {
            if(section) section.style.display = 'none';
            document.getElementById('base_is_purchase').checked = true;
            document.getElementById('base_is_sale').checked = false;
        } else {
            if(section) section.style.display = 'none';
        }
    }

    let recipeIndex = 0;
    const ingredientsData = @json($ingredients);

    function addRecipeRow() {
        let rowId = `recipe_row_${recipeIndex}`;
        let options = '<option value="">-- اختر مكون --</option>';
        ingredientsData.forEach(ing => {
            options += `<option value="${ing.id}" data-cost="${ing.base_unit ? ing.base_unit.cost_price : 0}" data-unit="${ing.base_unit ? ing.base_unit.unit_name : ''}">${ing.name}</option>`;
        });

        let html = `
            <tr id="${rowId}" class="recipe-row">
                <td>
                    <select name="recipe[${recipeIndex}][ingredient_id]" class="form-select recipe-ing-select" onchange="updateRecipeRowCost(this)" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" step="any" name="recipe[${recipeIndex}][quantity]" class="form-control text-center recipe-qty" value="1" oninput="updateRecipeRowCost(this)" required>
                        <span class="input-group-text recipe-unit-display">-</span>
                    </div>
                </td>
                <td class="text-center fw-bold text-danger recipe-row-cost">0.00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="document.getElementById('${rowId}').remove(); calculateTotalRecipeCost();">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        document.getElementById('recipe_rows').insertAdjacentHTML('beforeend', html);
        recipeIndex++;
    }

    function updateRecipeRowCost(el) {
        let row = el.closest('.recipe-row');
        let select = row.querySelector('.recipe-ing-select');
        let qty = row.querySelector('.recipe-qty').value || 0;
        let selectedOption = select.options[select.selectedIndex];
        
        if (select.value) {
            let unitCost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
            let unitName = selectedOption.getAttribute('data-unit') || '';
            // Actual code fix below
            let unitDisplay = row.querySelector('.recipe-unit-display');
            if(unitDisplay) unitDisplay.innerText = unitName;

            let rowCost = unitCost * qty;
            row.querySelector('.recipe-row-cost').innerText = rowCost.toFixed(2);
        }
        
        calculateTotalRecipeCost();
    }

    function calculateTotalRecipeCost() {
        let total = 0;
        document.querySelectorAll('.recipe-row-cost').forEach(cell => {
            total += parseFloat(cell.innerText) || 0;
        });
        document.getElementById('total_recipe_cost').innerText = total.toFixed(2);
        
        // إذا كان المنتج 'meal'، نحدث سعر التكلفة الأساسي بناءً على مجموع المكونات
        let type = document.querySelector('input[name="product_type"]:checked')?.value;
        if (type === 'meal') {
            document.getElementById('purchase_price').value = total.toFixed(2);
            document.getElementById('pieces_per_unit').value = 1;
            calculateBaseCost();
        }
    }

    // تهيئة الصفحة عند التحميل
    window.addEventListener('load', function() {
        toggleRecipeBuilder();
    });
</script>
@endsection
@endsection