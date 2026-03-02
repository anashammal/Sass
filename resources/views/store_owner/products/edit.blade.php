@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- 🔥 قسم عرض الأخطاء المفقود سابقاً 🔥 --}}
    @if(session('error'))
        <div class="alert alert-danger shadow-sm">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('store.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm" novalidate>
        @csrf @method('PUT')
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i> {{ __('تعديل المنتج') }} : {{ $product->name }}</h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ $product->is_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-white" for="is_active"> {{ __('منتج فعال') }} </label>
                </div>
            </div>
            
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold"> {{ __('اسم المنتج') }} </label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold"> {{ __('التصنيف') }} </label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"> {{ __('الوصف') }} </label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $product->description) }}">
                    </div>

                    <input type="hidden" name="product_type" value="{{ $product->product_type }}">
                </div>

                <hr class="my-4 text-secondary">

                {{-- الوحدة الأساسية --}}
                @php $base = $product->baseUnit; @endphp
                <div class="card border-success shadow-sm mb-3">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fas fa-cube me-1"></i> 
                        {{ __('الوحدة الأساسية') }} 📦
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2 text-center">
                                <label class="form-label small fw-bold"> {{ __('صورة الوحدة') }} </label>
                                <div class="position-relative">
                                    <img id="base_preview" src="{{ $product->image_url }}" class="img-thumbnail mb-2" style="height: 120px; width: 120px; object-fit: contain;">
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
                                        @php 
                                            $currentUnitName = old('base_unit_name', $base->unit_name);
                                            $isCustom = !in_array($currentUnitName, ['قطعة', 'كيلو', 'علبة']);
                                            $selectVal = $isCustom ? 'custom' : $currentUnitName;
                                        @endphp
                                        <select name="base_unit_select" class="form-select" onchange="handleBaseUnitChange(this)">
                                            <option value="قطعة" {{ $selectVal == 'قطعة' ? 'selected' : '' }}> {{ __('قطعة') }} </option>
                                            <option value="كيلو" {{ $selectVal == 'كيلو' ? 'selected' : '' }}> {{ __('كيلو') }} </option>
                                            <option value="علبة" {{ $selectVal == 'علبة' ? 'selected' : '' }}> {{ __('علبة') }} </option>
                                            <option value="custom" {{ $selectVal == 'custom' ? 'selected' : '' }}> {{ __('مخصص..') }} </option>
                                        </select>
                                        <input type="text" name="base_unit_name" id="base_unit_input" class="form-control {{ $isCustom ? '' : 'd-none' }} mt-1" value="{{ $currentUnitName }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold"> {{ __('باركود الوحدة') }} </label>
                                        <input type="text" name="base_barcode" class="form-control" value="{{ old('base_barcode', $base->barcode) }}">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-end justify-content-start gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="base_is_purchase" id="base_is_purchase" {{ old('base_is_purchase', $base->is_purchase ?? 1) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold" for="base_is_purchase"> {{ __('شراء') }} </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="base_is_sale" id="base_is_sale" {{ old('base_is_sale', $base->is_sale ?? 1) ? 'checked' : '' }} onchange="toggleSellingFields()">
                                            <label class="form-check-label small fw-bold" for="base_is_sale"> {{ __('بيع') }} </label>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label small"> {{ __('عدد القطع بالعبوة') }} </label>
                                        <input type="number" step="any" name="pieces_per_unit" id="pieces_per_unit" class="form-control text-center" value="{{ old('pieces_per_unit', (float)$base->conversion_factor) }}" oninput="calculateBaseCost()">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small text-danger fw-bold">{{ __('سعر الشراء') }}</label>
                                        <div class="input-group">
                                            <input type="number" step="any" name="purchase_price" id="purchase_price" class="form-control text-center" value="{{ old('purchase_price', (float)$base->purchase_price) }}" required oninput="calculateBaseCost()">
                                            <select name="purchase_price_currency_id" id="purchase_price_currency_id" class="form-select form-select-sm currency-select-trigger" style="max-width: 90px;" data-target="purchase_exchange_rate">
                                                <option value="{{ $store->base_currency_id }}">{{ $store->baseCurrency->code }}</option>
                                                @foreach($acceptedCurrencies as $cur)
                                                    @if($cur->id != $store->base_currency_id)
                                                        <option value="{{ $cur->id }}" {{ (old('purchase_price_currency_id', $base->purchase_price_currency_id) == $cur->id) ? 'selected' : '' }}>{{ $cur->code }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="purchase_exchange_rate" id="purchase_exchange_rate" value="{{ old('purchase_exchange_rate', $base->purchase_exchange_rate ?? 1) }}">
                                        </div>
                                        <div id="purchase_price_base_hint" class="small text-muted mt-1 d-none" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle me-1"></i> {{ __('ما يعادله بالعملة الافتراضية:') }} <span class="fw-bold text-dark base-val">0</span> {{ $store->baseCurrency->code }}
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label small text-muted"> {{ __('التكلفة (للقطعة)') }} </label>
                                        <div class="input-group">
                                            <input type="text" id="calculated_base_cost" class="form-control bg-light text-center fw-bold" readonly>
                                            <span class="input-group-text bg-light fw-bold text-muted base-currency-label">{{ $store->baseCurrency->code }}</span>
                                        </div>
                                        <input type="hidden" name="base_cost_price" id="base_cost">
                                    </div>

                                    <div class="col-md-2" id="base_margin_div">
                                        <label class="form-label small"> {{ __('الربح %') }} </label>
                                        <input type="number" step="any" name="base_profit_percent" id="base_margin" class="form-control text-center text-primary" value="{{ old('base_profit_percent', (float)$base->profit_percent) }}" oninput="calculatePriceFromMargin('base')">
                                    </div>

                                    <div class="col-md-3" id="base_selling_price_div">
                                        <label class="form-label small text-success fw-bold"> {{ __('سعر البيع') }} </label>
                                        <div class="input-group">
                                            <input type="number" step="any" name="base_selling_price" id="base_sell" class="form-control text-center fw-bold" value="{{ old('base_selling_price', (float)$base->selling_price) }}" required oninput="calculateMargin('base')">
                                            <select name="base_selling_price_currency_id" id="base_selling_price_currency_id" class="form-select form-select-sm currency-select-trigger" style="max-width: 90px;" data-target="base_sell_exchange_rate">
                                                <option value="{{ $store->base_currency_id }}">{{ $store->baseCurrency->code }}</option>
                                                @foreach($acceptedCurrencies as $cur)
                                                    @if($cur->id != $store->base_currency_id)
                                                        <option value="{{ $cur->id }}" {{ (old('base_selling_price_currency_id', $base->sell_price_currency_id) == $cur->id) ? 'selected' : '' }}>{{ $cur->code }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="base_sell_exchange_rate" id="base_sell_exchange_rate" value="{{ old('base_sell_exchange_rate', $base->sell_exchange_rate ?? 1) }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label small"> {{ __('الضريبة المضافة') }} </label>
                                        <select name="tax_percent" id="tax_percent" class="form-select bg-warning bg-opacity-10" onchange="calculatePriceWithTax()">
                                            @php $storeTaxes = explode(',', Auth::user()->store->tax_rates ?? '0,15'); @endphp
                                            @foreach($storeTaxes as $rate)
                                                <option value="{{ $rate }}" {{ $product->tax_percent == $rate ? 'selected' : '' }}>{{ $rate }}%</option>
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

                {{-- مكونات الوجبة (Recipe Builder) - يظهر فقط للمطاعم وإذا كان النوع 'meal' --}}
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark"><i class="fas fa-cog me-1"></i> {{ __('الوحدات الإضافية') }} </h6>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addExtraUnit()"><i class="fa fa-plus"></i> {{ __('إضافة وحدة') }} </button>
                </div>
                <div id="extra_units_container"></div>

                <hr class="my-4">

                <div class="row align-items-end g-3">
                    {{-- حد تنبيه نقص الكمية --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">{{ __('تنبيه نقص الكمية والتقادم') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-warning bg-opacity-25"><i class="fas fa-boxes"></i></span>
                            <input type="number" name="alert_quantity" class="form-control text-center fw-bold" value="{{ $product->alert_quantity }}">
                        </div>
                    </div>

                    {{-- 🔥 الحقل الجديد: حد تنبيه انتهاء الصلاحية --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-danger">{{ __('عند قرب انتهاء الصلاحية بـ(أيام)') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-danger bg-opacity-10 text-danger"><i class="fas fa-hourglass-half"></i></span>
                            <input type="number" name="expiry_warning_days" class="form-control text-center fw-bold text-danger border-danger" 
                                   value="{{ $product->expiry_warning_days ?? 30 }}" placeholder="مثال: 30">
                        </div>
                        <div class="form-text small">{{ __('سيصلك إشعار تلقائي قبل انتهاء الصلاحية بالعدد من الأيام') }}</div>
                    </div>

                    {{-- زر الحفظ --}}
                    <div class="col-md-4 text-end">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow w-100"><i class="fas fa-save me-2"></i> {{ __('حفظ التعديلات') }} 💾</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="unit_template">
    <div class="unit-row card border-secondary mb-2 shadow-sm position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 bg-danger" onclick="this.closest('.unit-row').remove()"></button>
        <input type="hidden" name="units[INDEX][id]" class="unit-id">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-2 text-center">
                    {{-- الصورة الافتراضية هنا --}}
                    <img src="{{ asset('images/default-product.png') }}" class="img-thumbnail unit-img-preview" style="width: 100px; height: 100px; object-fit: contain;">
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
                                {{-- ⚠️ تمت إزالة checked من هنا --}}
                                <input class="form-check-input unit-buy" type="checkbox" name="units[INDEX][is_purchase]">
                                <label class="form-check-label small fw-bold"> {{ __('شراء') }} </label>
                            </div>
                            <div class="form-check">
                                {{-- ⚠️ تمت إزالة checked من هنا --}}
                                <input class="form-check-input unit-sell-check" type="checkbox" name="units[INDEX][is_sale]" onchange="toggleExtraUnitSale(this)">
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
    document.addEventListener("DOMContentLoaded", function() {
        calculateBaseCost(); 
        calculatePriceWithTax();
        toggleSellingFields();

        // تفعيل مستمعات العملات للوحدة الأساسية والوحدات الموجودة
        initCurrencyTriggers(document);

        @foreach($product->units->where('is_base_unit', false) as $unit)
            addExtraUnit({
                id: '{{ $unit->id }}',
                name: '{{ $unit->unit_name }}',
                factor: {{ (float)$unit->conversion_factor }},
                barcode: '{{ $unit->barcode }}',
                cost: {{ (float)$unit->cost_price }},
                profit: {{ (float)$unit->profit_percent }},
                selling: {{ (float)$unit->selling_price }},
                // تحويل 1/0 إلى true/false للجافاسكريبت
                is_purchase: {{ $unit->is_purchase ? 'true' : 'false' }},
                is_sale: {{ $unit->is_sale ? 'true' : 'false' }},
                // ضمان وجود رابط صورة صالح
                image: '{{ $unit->image }}',
                sell_currency_id: '{{ $unit->sell_price_currency_id }}',
                purchase_currency_id: '{{ $unit->purchase_price_currency_id }}',
                purchase_price: {{ (float)$unit->purchase_price }},
                purchase_rate: {{ $unit->purchase_exchange_rate ?? 1 }},
                sell_rate: {{ $unit->sell_exchange_rate ?? 1 }}
            });
@endforeach
    });

    // ... (نفس الدوال المساعدة السابقة: formatNum, previewImage, الخ) ...
    function formatNum(num) { return isNaN(num) ? 0 : parseFloat(parseFloat(num).toFixed(10)); }
    function previewImage(input, imgId) { let img = imgId ? document.getElementById(imgId) : input.previousElementSibling; if (input.files && input.files[0]) { let r = new FileReader(); r.onload = (e) => { img.src = e.target.result; }; r.readAsDataURL(input.files[0]); } }
    function handleBaseUnitChange(s) { let i=document.getElementById('base_unit_input'); if(s.value=='custom'){i.classList.remove('d-none');i.value='';i.focus();}else{i.classList.add('d-none');i.value=s.value;} }

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

    function handleUnitChange(s) { let i=s.nextElementSibling; if(s.value=='custom'){i.classList.remove('d-none');i.value='';i.focus();}else{i.classList.add('d-none');i.value=s.value;} }

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
        
        if (currencyId == baseCurrencyId) {
            updateGlobalCurrencyRate(currencyId, 1);
            return;
        }

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

        calculateBaseCost();
        calculatePriceWithTax();
        updateAllUnitsCosts();
    }

    function calculateBaseCost() { 
        if (isCalculating) return;
        isCalculating = true;

        let p=parseFloat(document.getElementById('purchase_price').value)||0; 
        let f=parseFloat(document.getElementById('pieces_per_unit').value)||1; 
        let r=parseFloat(document.getElementById('purchase_exchange_rate').value)||1;
        let c=(f>0)?((p*r)/f):0; 
        document.getElementById('base_cost').value=c; 
        document.getElementById('calculated_base_cost').value=formatNum(c); 

        // تحديث تلميح سعر الشراء بالعملة الأساسية
        const hintDiv = document.getElementById('purchase_price_base_hint');
        const curId = document.getElementById('purchase_price_currency_id').value;
        if (curId != baseCurrencyId) {
            hintDiv.classList.remove('d-none');
            hintDiv.querySelector('.base-val').innerText = formatNum(p * r);
        } else {
            hintDiv.classList.add('d-none');
        }

        isCalculating = false;
        updateAllUnitsCosts(); 
        calculateMargin('base'); 
    }
    function calculateUnitCost(i) { 
        let r=i.closest('.unit-row'); 
        let factor=parseFloat(r.querySelector('.unit-factor').value)||1; 
        let purchaseRate=parseFloat(r.querySelector('.unit-purchase-rate').value)||1;
        let purchaseCurId = r.querySelector('.currency-select-purchase').value;
        const purchaseHint = r.querySelector('.unit-purchase-base-hint');
        const purchasePriceInput = r.querySelector('.unit-purchase');

        if (i.classList.contains('unit-purchase')) {
            if (isCalculating) return;
            isCalculating = true;

            let manualPurchasePrice = parseFloat(i.value) || 0;
            let costInBase = (manualPurchasePrice * purchaseRate) / factor;
            
            document.getElementById('base_cost').value=costInBase; 
            document.getElementById('calculated_base_cost').value=formatNum(costInBase); 
            
            let basePurchaseRate = parseFloat(document.getElementById('purchase_exchange_rate').value) || 1;
            let piecesPerUnit = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
            document.getElementById('purchase_price').value = formatNum((costInBase * piecesPerUnit) / basePurchaseRate);

            const baseHintDiv = document.getElementById('purchase_price_base_hint');
            if (document.getElementById('purchase_price_currency_id').value != baseCurrencyId) {
                baseHintDiv.classList.remove('d-none');
                baseHintDiv.querySelector('.base-val').innerText = formatNum(costInBase * piecesPerUnit);
            }

            isCalculating = false;
            updateAllUnitsCosts(r);
        } else {
            let baseCost = parseFloat(document.getElementById('base_cost').value) || 0; 
            let unitCostInBase = baseCost * factor;
            r.querySelector('.unit-cost').value = formatNum(unitCostInBase);
            
            if (!isCalculating) {
                purchasePriceInput.value = formatNum(unitCostInBase / purchaseRate);
            }
        }

        if (purchaseCurId != baseCurrencyId) {
            purchaseHint.classList.remove('d-none');
            purchaseHint.querySelector('.base-val').innerText = formatNum(parseFloat(purchasePriceInput.value) * purchaseRate);
        } else {
            purchaseHint.classList.add('d-none');
        }

        let p=r.querySelector('.unit-profit'); 
        calcExtraUnitSell(p); 
    }

    function updateAllUnitsCosts(excludeRow = null) {
        document.querySelectorAll('.unit-row').forEach(row => {
            if (row === excludeRow) return;
            calculateUnitCost(row.querySelector('.unit-factor'));
        });
    }
    function calculatePriceWithTax() {
        let p=parseFloat(document.getElementById('base_sell').value)||0;
        let t=parseFloat(document.getElementById('tax_percent').value)||0;
        let rate = parseFloat(document.getElementById('base_sell_exchange_rate').value) || 1;
        
        let final = p*(1+(t/100));
        document.getElementById('price_with_tax').value=formatNum(final);

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
    function calculateMargin(t) { 
        if(t==='base'){ 
            let c=parseFloat(document.getElementById('base_cost').value)||0; 
            let s=parseFloat(document.getElementById('base_sell').value)||0; 
            let r=parseFloat(document.getElementById('base_sell_exchange_rate').value)||1;
            let sInBase = s * r;
            if(c>0) document.getElementById('base_margin').value=formatNum(((sInBase-c)/c)*100); 
            calculatePriceWithTax(); 
        } 
    }
    function calculatePriceFromMargin(t) { 
        if(t==='base'){ 
            let c=parseFloat(document.getElementById('base_cost').value)||0; 
            let m=parseFloat(document.getElementById('base_margin').value)||0; 
            let r=parseFloat(document.getElementById('base_sell_exchange_rate').value)||1;
            let sInBase = c*(1+(m/100));
            document.getElementById('base_sell').value=formatNum(sInBase/r); 
            calculatePriceWithTax(); 
        } 
    }
    function calcExtraUnitSell(i) { 
        let r=i.closest('.unit-row'); 
        let c=parseFloat(r.querySelector('.unit-cost').value)||0; 
        let m=parseFloat(i.value)||0; 
        let rate=parseFloat(r.querySelector('.unit-sell-rate').value)||1;
        
        if(c>0) {
            let sellingPriceInBase = c*(1+(m/100));
            let sellingPriceInCurrency = sellingPriceInBase/rate;
            r.querySelector('.unit-sell').value=formatNum(sellingPriceInCurrency);

            // تحديث التلميح
            const curSelector = r.querySelector('[name*="[sell_price_currency_id]"]');
            const hint = r.querySelector('.unit-sell-base-hint');
            if (curSelector.value != baseCurrencyId) {
                hint.classList.remove('d-none');
                hint.querySelector('.base-val').innerText = formatNum(sellingPriceInBase);
            } else {
                hint.classList.add('d-none');
            }
        }
    }
    function calcExtraUnitProfit(i) { 
        let r=i.closest('.unit-row'); 
        let c=parseFloat(r.querySelector('.unit-cost').value)||0; 
        let s=parseFloat(i.value)||0; 
        let rate=parseFloat(r.querySelector('.unit-sell-rate').value)||1;
        if(c>0) r.querySelector('.unit-profit').value=formatNum(((s*rate-c)/c)*100); 
    }

    let unitIndex = 0;
    function addExtraUnit(data = null) {
        let tpl = document.getElementById('unit_template').innerHTML.replace(/INDEX/g, unitIndex);
        document.getElementById('extra_units_container').insertAdjacentHTML('beforeend', tpl);
        let row = document.getElementById('extra_units_container').lastElementChild;

        if(data) {
            // --- حالة التعديل (وحدة موجودة) ---
            if(data.id) row.querySelector('.unit-id').value = data.id;
            
            let sel = row.querySelector('.unit-select');
            let inp = row.querySelector('.unit-custom-input');
            if(['كرتون','درزن','شريط'].includes(data.name)) sel.value = data.name;
            else { sel.value = 'custom'; inp.classList.remove('d-none'); inp.value = data.name; }

            row.querySelector('.unit-factor').value = data.factor;
            row.querySelector('[name*="[barcode]"]').value = data.barcode;
            row.querySelector('.unit-cost').value = data.cost;
            if(data.purchase_price) row.querySelector('.unit-purchase').value = data.purchase_price;
            if(data.purchase_currency_id) row.querySelector('.currency-select-purchase').value = data.purchase_currency_id;
            if(data.purchase_rate) row.querySelector('.unit-purchase-rate').value = data.purchase_rate;
            
            if(data.sell_currency_id) row.querySelector('.currency-select').value = data.sell_currency_id;
            if(data.sell_rate) row.querySelector('.unit-sell-rate').value = data.sell_rate;

            row.querySelector('.unit-profit').value = data.profit;
            row.querySelector('.unit-sell').value = data.selling;
            
            row.querySelector('.unit-buy').checked = data.is_purchase;
            row.querySelector('.unit-sell-check').checked = data.is_sale;
            
            // 🔥 إصلاح عرض الصورة 🔥
            // إذا كان هناك رابط صورة، ضعه في الـ src، وإلا اترك الصورة الافتراضية
            if(data.image && data.image.length > 0) {
                row.querySelector('.unit-img-preview').src = data.image;
            }
        } else {
            // --- حالة إضافة وحدة جديدة يدوياً ---
            // نجعل الـ Checkboxes مفعلة افتراضياً عند إضافة وحدة جديدة
            row.querySelector('.unit-buy').checked = true;
            row.querySelector('.unit-sell-check').checked = true;
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

        // Trigger initial visibility
        toggleExtraUnitSale(row.querySelector('.unit-sell-check'));

        // تحديث الحسابات للوحدة الجديدة لإظهار تلميحات العملة
        calculateUnitCost(row.querySelector('.unit-factor'));

        unitIndex++;
    }

    // --- منطق المطاعم والريسبي ---
    function toggleRecipeBuilder() {
        let type = document.querySelector('input[name="product_type"]:checked')?.value;
        let section = document.getElementById('recipe_builder_section');
        
        if (type === 'meal') {
            if(section) section.style.display = 'block';
            document.getElementById('base_is_purchase').checked = false;
            document.getElementById('base_is_sale').checked = true;
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

    function addRecipeRow(data = null) {
        let rowId = `recipe_row_${recipeIndex}`;
        let options = '<option value="">-- اختر مكون --</option>';
        ingredientsData.forEach(ing => {
            let selected = (data && data.ingredient_product_id == ing.id) ? 'selected' : '';
            options += `<option value="${ing.id}" data-cost="${ing.base_unit ? ing.base_unit.cost_price : 0}" data-unit="${ing.base_unit ? ing.base_unit.unit_name : ''}" ${selected}>${ing.name}</option>`;
        });

        let qty = data ? data.quantity : 1;
        let html = `
            <tr id="${rowId}" class="recipe-row">
                <td>
                    <select name="recipe[${recipeIndex}][ingredient_id]" class="form-select recipe-ing-select" onchange="updateRecipeRowCost(this)" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" step="any" name="recipe[${recipeIndex}][quantity]" class="form-control text-center recipe-qty" value="${qty}" oninput="updateRecipeRowCost(this)" required>
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
        
        let rowEl = document.getElementById(rowId);
        updateRecipeRowCost(rowEl.querySelector('.recipe-ing-select'));
        
        recipeIndex++;
    }

    function updateRecipeRowCost(el) {
        let row = el.closest('.recipe-row');
        if(!row) return;
        let select = row.querySelector('.recipe-ing-select');
        let qty = row.querySelector('.recipe-qty').value || 0;
        let selectedOption = select.options[select.selectedIndex];
        
        if (select.value) {
            let unitCost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
            let unitName = selectedOption.getAttribute('data-unit') || '';
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
        let totalDisplay = document.getElementById('total_recipe_cost');
        if(totalDisplay) totalDisplay.innerText = total.toFixed(2);
        
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
        @if($product->recipes->count() > 0)
            @foreach($product->recipes as $recipe)
                addRecipeRow(@json($recipe));
            @endforeach
        @endif
    });
</script>
@endsection
@endsection