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
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-box-open me-2"></i> إضافة منتج جديد</h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', 'on') == 'on' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-white" for="is_active">منتج فعال</label>
                </div>
            </div>
            
            <div class="card-body bg-light">
                {{-- البيانات الأساسية --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم المنتج (عربي) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="name_ar" class="form-control" required placeholder="مثال: شيبس ليز ملح" value="{{ old('name_ar') }}">
                            <button class="btn btn-outline-primary" type="button" id="google_search_btn" title="بحث في جوجل صور">
                                <i class="fab fa-google"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">اسم المنتج (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control" placeholder="Ex: Lays Chips Salt" value="{{ old('name_en') }}">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">التصنيف <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- اختر تصنيف --</option>
                            @if(isset($categories))
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">الوصف</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                    </div>
                </div>

                <hr class="my-4 text-secondary">

                {{-- الوحدة الأساسية --}}
                <div class="card border-success shadow-sm mb-3">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fas fa-cube me-1"></i> الوحدة الأساسية (أصغر وحدة)
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            {{-- صورة كبيرة وواضحة --}}
                            <div class="col-md-2 text-center">
                                <label class="form-label small fw-bold">صورة الوحدة</label>
                                <div class="position-relative">
                                    <img id="base_preview" src="{{ asset('images/default-product.png') }}" class="img-thumbnail mb-2" style="height: 120px; width: 120px; object-fit: contain;">
                                    <input type="file" name="base_unit_image" class="form-control form-control-sm" accept="image/*" onchange="previewImage(this, 'base_preview')">
                                </div>
                            </div>

                            <div class="col-md-10">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small">اسم الوحدة</label>
                                        {{-- استعادة اختيار القائمة --}}
                                        <select name="base_unit_select" class="form-select" id="base_unit_select" onchange="handleBaseUnitChange(this)">
                                            @php $sel = old('base_unit_select', 'قطعة'); @endphp
                                            <option value="قطعة" {{ $sel == 'قطعة' ? 'selected' : '' }}>قطعة</option>
                                            <option value="كيلو" {{ $sel == 'كيلو' ? 'selected' : '' }}>كيلو</option>
                                            <option value="علبة" {{ $sel == 'علبة' ? 'selected' : '' }}>علبة</option>
                                            <option value="custom" {{ $sel == 'custom' ? 'selected' : '' }}>مخصص..</option>
                                        </select>
                                        {{-- استعادة النص المخصص --}}
                                        <input type="text" name="base_unit_name" id="base_unit_input" class="form-control {{ $sel == 'custom' ? '' : 'd-none' }} mt-1" value="{{ old('base_unit_name', 'قطعة') }}">
                                    </div>

                                                                            <div class="col-md-2">
                                        <label class="form-label small text-danger fw-bold">سعر الشراء (للعبوة)</label>
                                        <input type="number" step="any" name="purchase_price" id="purchase_price" class="form-control text-center" value="{{ old('purchase_price', 0) }}" required oninput="calculateBaseCost()">
                                    </div>


                                    <div class="col-md-2">
                                        <label class="form-label small">عدد القطع بالعبوة</label>
                                        <input type="number" step="any" name="pieces_per_unit" id="pieces_per_unit" class="form-control text-center" value="{{ old('pieces_per_unit', 1) }}" oninput="calculateBaseCost()">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">باركود الوحدة</label>
                                        <input type="text" name="base_barcode" class="form-control" placeholder="تلقائي إذا فارغ" value="{{ old('base_barcode') }}">
                                    </div>

                                                                            <div class="col-md-3">
                                        <label class="form-label small text-success fw-bold">سعر البيع</label>
                                        <input type="number" step="any" name="base_selling_price" id="base_sell" class="form-control text-center fw-bold" value="{{ old('base_selling_price', 0) }}" required oninput="calculateMargin('base')">
                                    </div>
                                        
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted">التكلفة (للقطعة)</label>
                                        <input type="text" id="calculated_base_cost" class="form-control bg-light text-center fw-bold" readonly value="0">
                                        <input type="hidden" name="base_cost_price" id="base_cost" value="0">
                                    </div>
                                     
                                        
                                    <div class="col-md-2">
                                        <label class="form-label small">الربح %</label>
                                        <input type="number" step="any" name="base_profit_percent" id="base_margin" class="form-control text-center text-primary" value="{{ old('base_profit_percent', 0) }}" oninput="calculatePriceFromMargin('base')">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label small">الضريبة المضافة</label>
                                        <select name="tax_percent" id="tax_percent" class="form-select bg-warning bg-opacity-10" onchange="calculatePriceWithTax()">
                                            @foreach($taxRates as $rate)
                                                <option value="{{ $rate }}" {{ old('tax_percent') == $rate ? 'selected' : '' }}>{{ $rate }}%</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">السعر مع الضريبة</label>
                                        <input type="text" id="price_with_tax" class="form-control bg-light fw-bold text-success" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الوحدات الإضافية --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark"><i class="fas fa-layer-group me-1"></i> الوحدات الإضافية (كرتون، درزن...)</h6>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addExtraUnit()"><i class="fa fa-plus"></i> إضافة وحدة</button>
                </div>
                <div id="extra_units_container"></div>

                <hr class="my-4">

                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-warning bg-opacity-25">حد التنبيه للمخزون</span>
                            <input type="number" name="alert_quantity" class="form-control text-center" value="{{ old('alert_quantity', 5) }}">
                        </div>
                    </div>
                    <div class="col-md-8 text-end">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow"><i class="fas fa-save me-2"></i> حفظ المنتج</button>
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
                    <input type="file" name="units[INDEX][image]" class="form-control form-control-sm mt-1" accept="image/*" onchange="previewImage(this)">
                </div>
                
                <div class="col-md-10">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="small fw-bold">اسم الوحدة</label>
                            <select name="units[INDEX][name_select]" class="form-select form-select-sm unit-select fw-bold" onchange="handleUnitChange(this)">
                                <option value="كرتون">كرتون</option>
                                <option value="درزن">درزن</option>
                                <option value="شريط">شريط</option>
                                <option value="custom">مخصص..</option>
                            </select>
                            <input type="text" name="units[INDEX][name]" class="form-control form-control-sm d-none mt-1 unit-custom-input fw-bold" placeholder="اكتب الاسم">
                        </div>

                        <div class="col-md-2">
                            <label class="small fw-bold">التحويل</label>
                            <input type="number" name="units[INDEX][factor]" class="form-control form-control-sm unit-factor fw-bold" value="1" oninput="calculateUnitCost(this)">
                        </div>

                        <div class="col-md-3">
                            <label class="small fw-bold">الباركود</label>
                            <input type="text" name="units[INDEX][barcode]" class="form-control form-control-sm fw-bold">
                        </div>

                        <div class="col-md-4 d-flex align-items-end justify-content-start gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="units[INDEX][is_purchase]" checked>
                                <label class="form-check-label small fw-bold">شراء</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="units[INDEX][is_sale]" checked>
                                <label class="form-check-label small fw-bold">بيع</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="small text-muted fw-bold">التكلفة (آلي)</label>
                            <input type="number" name="units[INDEX][cost_price]" class="form-control form-control-sm bg-light unit-cost fw-bold text-danger" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="small fw-bold text-primary">الربح %</label>
                            <input type="number" step="any" name="units[INDEX][profit_percent]" class="form-control form-control-sm unit-profit fw-bold text-primary" oninput="calcExtraUnitSell(this)">
                        </div>

                        <div class="col-md-4">
                            <label class="small text-success fw-bold">سعر البيع</label>
                            <input type="number" step="any" name="units[INDEX][selling_price]" class="form-control form-control-sm unit-sell fw-bold text-success" oninput="calcExtraUnitProfit(this)">
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
        let name = document.querySelector('input[name="name_ar"]').value;
        if(name) window.open('https://www.google.com/search?tbm=isch&q=' + encodeURIComponent(name), '_blank');
        else alert('الرجاء كتابة اسم المنتج بالعربي أولاً');
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

    function calculateBaseCost() {
        let price = parseFloat(document.getElementById('purchase_price').value) || 0;
        let pieces = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
        let cost = (pieces > 0) ? (price / pieces) : 0;
        document.getElementById('base_cost').value = cost;
        document.getElementById('calculated_base_cost').value = formatNum(cost);
        updateAllUnitsCosts();
        calculateMargin('base');
    }

    function calculateUnitCost(input) {
        let row = input.closest('.unit-row');
        let baseCost = parseFloat(document.getElementById('base_cost').value) || 0;
        let factor = parseFloat(row.querySelector('.unit-factor').value) || 0;
        let newCost = formatNum(baseCost * factor);
        row.querySelector('.unit-cost').value = newCost;
        
        // تحديث السعر أيضاً
        let profitInput = row.querySelector('.unit-profit');
        calcExtraUnitSell(profitInput);
    }

    function updateAllUnitsCosts() {
        document.querySelectorAll('.unit-factor').forEach(i => calculateUnitCost(i));
    }

    function calculatePriceWithTax() {
        let price = parseFloat(document.getElementById('base_sell').value) || 0;
        let tax = parseFloat(document.getElementById('tax_percent').value) || 0;
        let final = price * (1 + (tax / 100));
        document.getElementById('price_with_tax').value = formatNum(final);
    }

    function calculateMargin(type) {
        if(type === 'base') {
            let cost = parseFloat(document.getElementById('base_cost').value) || 0;
            let sell = parseFloat(document.getElementById('base_sell').value) || 0;
            if(cost > 0) document.getElementById('base_margin').value = formatNum(((sell - cost) / cost) * 100);
            calculatePriceWithTax();
        }
    }

    function calculatePriceFromMargin(type) {
        if(type === 'base') {
            let cost = parseFloat(document.getElementById('base_cost').value) || 0;
            let margin = parseFloat(document.getElementById('base_margin').value) || 0;
            let sell = cost * (1 + (margin / 100));
            document.getElementById('base_sell').value = formatNum(sell);
            calculatePriceWithTax();
        }
    }

    function calcExtraUnitSell(input) {
        let row = input.closest('.unit-row');
        let cost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let profit = parseFloat(input.value) || 0;
        let sellInput = row.querySelector('.unit-sell');
        if(cost > 0) {
            let sellingPrice = cost * (1 + (profit / 100));
            sellInput.value = formatNum(sellingPrice);
        }
    }

    function calcExtraUnitProfit(input) {
        let row = input.closest('.unit-row');
        let cost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let sell = parseFloat(input.value) || 0;
        let profitInput = row.querySelector('.unit-profit');
        if(cost > 0) {
            let profitPercent = ((sell - cost) / cost) * 100;
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
            
            // إعادة الحساب
            calculateUnitCost(row.querySelector('.unit-factor'));
        }
        
        unitIndex++;
    }
</script>
@endsection
@endsection