@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="fas fa-edit me-2"></i> تعديل صنف المنيو: {{ $meal->name_ar }}</h3>
        <a href="{{ route('store.meals.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right me-1"></i> العودة للمنيو</a>
    </div>

    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            font-size: 0.9rem !important;
            font-weight: bold !important;
        }
    </style>


    @if (session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger shadow-sm">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
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

    <form action="{{ route('store.meals.update', $meal->id) }}" method="POST" enctype="multipart/form-data" id="mealForm" novalidate>
        @csrf
        @method('PUT')
        @if(request('iframe')) <input type="hidden" name="iframe" value="1"> @endif
        @if(request('quick_add')) <input type="hidden" name="quick_add" value="1"> @endif
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-info-circle me-1"></i> تفاصيل الصنف</h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ $meal->is_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-white" for="is_active">فعال</label>
                </div>
            </div>
            
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم الوجبة أو المادة الخام (عربي) <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control" required value="{{ old('name_ar', $meal->name_ar) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">اسم الوجبة/المكون (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $meal->name_en) }}">
                    </div>
                    
                    <div class="col-md-6" id="category_div">
                        <label class="form-label fw-bold">تصنيف المنيو <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">-- اختر التصنيف --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $meal->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">الوصف</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $meal->description) }}">
                    </div>

                    <div class="col-md-12">
                        <div class="card border-primary bg-primary bg-opacity-10 shadow-sm mt-2">
                            <div class="card-body">
                                <label class="form-label fw-bold text-primary">نوع الصنف في المنيو</label>
                                <div class="d-flex gap-4 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_meal" value="meal" {{ old('product_type', $meal->product_type) == 'meal' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-danger" for="type_meal">وجبة جاهزة (بيع فقط)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_ingredient" value="ingredient" {{ old('product_type', $meal->product_type) == 'ingredient' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-success" for="type_ingredient">مادة خام (شراء فقط)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_standard" value="standard" {{ old('product_type', $meal->product_type) == 'standard' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold" for="type_standard">منتج جاهز (شراء وبيع)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_compound" value="compound" {{ old('product_type', $meal->product_type) == 'compound' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-primary" for="type_compound">مكون مركب (تحضير داخلي)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- الوحدة الأساسية --}}
                @php $base = $meal->baseUnit; @endphp
                <div class="card border-success shadow-sm mb-3">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fas fa-cube me-1"></i> وحدة التقديم / وحدة القياس
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">اسم الوحدة الرئيسية</label>
                                @php
                                    $standardCS = ['قطعة', 'كيلوغرام', 'غرام', 'ليتر', 'مل'];
                                    $isCustom = !in_array($base->unit_name, $standardCS);
                                    $selectVal = $isCustom ? 'custom' : $base->unit_name;
                                @endphp
                                <select name="base_unit_select" id="base_unit_select" class="form-select" onchange="toggleCustomUnitInput()">
                                    <option value="قطعة" {{ $selectVal == 'قطعة' ? 'selected' : '' }}>قطعة</option>
                                    <option value="كيلوغرام" {{ $selectVal == 'كيلوغرام' ? 'selected' : '' }}>كيلوغرام</option>
                                    <option value="غرام" {{ $selectVal == 'غرام' ? 'selected' : '' }}>غرام</option>
                                    <option value="ليتر" {{ $selectVal == 'ليتر' ? 'selected' : '' }}>ليتر</option>
                                    <option value="مل" {{ $selectVal == 'مل' ? 'selected' : '' }}>مل</option>
                                    <option value="custom" {{ $selectVal == 'custom' ? 'selected' : '' }}>مخصص (أدخل يدوياً)</option>
                                </select>
                                <input type="text" name="base_unit_name" id="base_unit_custom" class="form-control mt-2" 
                                    style="{{ $isCustom ? '' : 'display: none;' }}" 
                                    value="{{ old('base_unit_name', $base->unit_name) }}" 
                                    {{ $isCustom ? '' : 'disabled' }}>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">عدد القطع بالعبوة</label>
                                <input type="number" step="any" name="pieces_per_unit" id="pieces_per_unit" class="form-control text-center" value="{{ old('pieces_per_unit', $meal->units->where('parent_id', $base->id)->count() > 0 ? $meal->units->where('parent_id', $base->id)->first()->conversion_factor : 1) }}" oninput="toggleSubUnitField()">
                            </div>
                            <div class="col-md-3" id="sub_unit_name_div" style="{{ ($meal->units->where('parent_id', $base->id)->count() > 0) ? '' : 'display: none;' }}">
                                <label class="form-label small text-info">اسم القطعة (اختياري)</label>
                                <input type="text" name="sub_unit_name" id="sub_unit_name" class="form-control" value="{{ old('sub_unit_name', $meal->units->where('parent_id', $base->id)->first()->unit_name ?? '') }}" placeholder="مثال: حبة، غرام">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-danger fw-bold" id="purchase_label">تكلفة الإنتاج / الشراء</label>
                                <input type="number" step="any" name="purchase_price" id="purchase_price" class="form-control text-center" value="{{ old('purchase_price', (float)$base->purchase_price) }}" required oninput="calculateBaseCost()">
                            </div>
                            <div class="col-md-3" id="selling_price_div">
                                <label class="form-label small text-success fw-bold">سعر البيع</label>
                                <input type="number" step="any" name="base_selling_price" id="base_sell" class="form-control text-center fw-bold" value="{{ old('base_selling_price', (float)$base->selling_price) }}" required oninput="calculateMargin()">
                            </div>
                            <div class="col-md-3" id="margin_div">
                                <label class="form-label small text-muted">الربح %</label>
                                <input type="number" step="any" name="base_profit_percent" id="base_margin" class="form-control text-center text-primary" value="{{ old('base_profit_percent', (float)$base->profit_percent) }}" oninput="calculatePriceFromMargin()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">باركود (اختياري)</label>
                                <input type="text" name="base_barcode" class="form-control" value="{{ old('base_barcode', $base->barcode) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">صورة الوحدة</label>
                                <input type="file" name="base_unit_image" class="form-control form-control-sm" accept="image/*" onchange="previewImage(this, 'base_preview')">
                                <div class="mt-2 text-center">
                                    <img id="base_preview" src="{{ $meal->image_url }}" alt="معاينة الصورة" class="img-thumbnail" style="max-height: 80px;">
                                </div>
                            </div>
                                <div class="col-md-3 d-flex align-items-end justify-content-start gap-3 pb-1" id="trade_checkboxes">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="base_is_purchase" id="base_is_purchase" {{ $base->is_purchase ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-bold" for="base_is_purchase">شراء</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="base_is_sale" id="base_is_sale" {{ $base->is_sale ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-bold" for="base_is_sale">قابل للبيع</label>
                                    </div>
                                </div>

                                {{-- توضيح تكلفة التفكيك --}}
                                <div class="col-12 mt-2" id="unit_breakdown_div" style="{{ $meal->units->where('parent_id', $base->id)->count() > 0 ? '' : 'display: none;' }}">
                                    <div class="alert alert-info py-2 mb-0 border-0 shadow-sm d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold"><i class="fas fa-info-circle me-1"></i> تكلفة القطعة الواحدة (<span id="item_name_at_breakdown">{{ $meal->units->where('parent_id', $base->id)->first()->unit_name ?? 'قطعة' }}</span>):</span>
                                        <span class="english-num fw-bold fs-5 text-danger" id="cost_per_piece_display">{{ number_format($meal->units->where('parent_id', $base->id)->first()->cost_price ?? 0, 2) }}</span>
                                    </div>
                                </div>
                        </div>

                        {{-- الوحدات الإضافية (تظهر فقط للمنتجات الجاهزة) --}}
                        <div id="extra_units_section" style="{{ $meal->product_type == 'standard' ? '' : 'display: none;' }}">
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-layer-group me-1"></i> الوحدات الإضافية (كرتون، درزن...)</h6>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="addExtraUnit()"><i class="fa fa-plus me-1"></i> إضافة وحدة</button>
                            </div>
                            <div id="extra_units_container">
                                @foreach($meal->units->where('is_base_unit', false) as $extra)
                                    @include('store_owner.meals.partials.unit_row', ['unit' => $extra, 'index' => $loop->index])
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- إعدادات المخزون (تنبيهات واستحقاق) - تظهر فقط للمنتجات الجاهزة --}}
                <div class="card border-warning shadow-sm mb-3" id="inventory_settings_div" style="display: none;">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="fas fa-boxes me-1"></i> إعدادات المخزون
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">تنبيه انخفاض الكمية (Alert Quantity)</label>
                                <input type="number" name="alert_quantity" class="form-control" value="{{ old('alert_quantity', $meal->alert_quantity ?? 5) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">تنبيه انتهاء الصلاحية (بالأيام)</label>
                                <input type="number" name="expiry_warning_days" class="form-control" value="{{ old('expiry_warning_days', $meal->expiry_warning_days ?? 30) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recipe Builder --}}
                <div id="recipe_builder_section" class="card border-danger shadow-sm mb-4" style="{{ $meal->product_type == 'meal' ? '' : 'display: none;' }}">
                    <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-utensils me-2"></i> مكونات الوجبة (الرسبي)</h6>
                        <button type="button" class="btn btn-sm btn-light fw-bold" onclick="addRecipeRow()">
                            <i class="fas fa-plus-circle me-1"></i> إضافة مكون
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="bg-light text-center small fw-bold">
                                    <tr>
                                        <th style="width: 35%;">المكون</th>
                                        <th style="width: 15%;">الوحدة</th>
                                        <th style="width: 15%;">الكمية</th>
                                        <th style="width: 20%;">التكلفة التقريبية</th>
                                        <th style="width: 15%;">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody id="recipe_rows">
                                    @foreach($meal->recipes as $index => $recipe)
                                        <tr id="recipe_row_{{ $index }}" class="recipe-row">
                                            <td>
                                                <select name="recipe[{{ $index }}][ingredient_id]" class="form-select ingredient-select-2" onchange="populateRecipeUnits(this)" required>
                                                    <option value="">-- اختر مكون --</option>
                                                    @foreach($ingredients as $ing)
                                                        @php 
                                                            $unitsJson = rawurlencode(json_encode($ing->units)); 
                                                            $barcodeTxt = $ing->barcode ? " [{$ing->barcode}]" : "";
                                                        @endphp
                                                        <option value="{{ $ing->id }}" {{ $recipe->ingredient_product_id == $ing->id ? 'selected' : '' }} data-units="{{ $unitsJson }}">{{ $ing->name_ar }}{{ $barcodeTxt }}</option>

                                                    @endforeach
                                                </select>

                                            </td>
                                            <td>
                                                <select name="recipe[{{ $index }}][unit_id]" class="form-select unit-select" onchange="updateRecipeEntry(this)" required>
                                                    <option value="">--</option>
                                                    @if($recipe->ingredient)
                                                        @foreach($recipe->ingredient->units as $u)
                                                            <option value="{{ $u->id }}" {{ $recipe->unit_id == $u->id ? 'selected' : '' }} data-cost="{{ $u->cost_price }}">{{ $u->unit_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td><input type="number" step="any" name="recipe[{{ $index }}][quantity]" class="form-control text-center qty" value="{{ (float)$recipe->quantity }}" oninput="updateRecipeEntry(this)" required></td>
                                            
                                            @php 
                                                $selectedUnit = $recipe->ingredient->units->firstWhere('id', $recipe->unit_id);
                                                $unitCost = $selectedUnit ? $selectedUnit->cost_price : 0;
                                                $totalCost = $unitCost * $recipe->quantity;
                                            @endphp
                                            
                                            <td class="text-center fw-bold text-danger row-cost">{{ number_format($totalCost, 2) }}</td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button type="button" class="btn btn-outline-success btn-sm quick-add-btn" style="{{ $recipe->ingredient_product_id ? 'display:none;' : '' }}" onclick="openQuickAddIngredient('recipe_row_{{ $index }}')" title="إضافة صنف جديد">
                                                        <i class="fas fa-plus-circle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove(); calculateTotalRecipe();">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark text-center">
                                        <td colspan="2" class="text-end fw-bold">إجمالي تكلفة المكونات:</td>
                                        <td id="total_recipe_cost" class="fw-bold fs-5">{{ number_format($meal->base_cost_price, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-end py-3">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i class="fas fa-save me-2"></i> تحديث الصنف</button>
                </div>
            </div>
        </div>
    </form>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>

    let unitIndex = {{ $meal->units->count() + 1 }};

    function addExtraUnit(data = null) {
        let tpl = document.getElementById('unit_template').innerHTML.replace(/INDEX/g, unitIndex);
        document.getElementById('extra_units_container').insertAdjacentHTML('beforeend', tpl);
        
        let row = document.getElementById('extra_units_container').lastElementChild;
        calculateUnitCost(row.querySelector('.unit-factor'));
        unitIndex++;
    }

    function handleUnitChange(select) {
        let input = select.nextElementSibling; 
        if (select.value === 'custom') {
            input.classList.remove('d-none');
            input.required = true;
            input.focus();
        } else {
            input.classList.add('d-none');
            input.required = false;
            input.value = select.value;
        }
    }

    function calculateUnitCost(input) {
        let row = input.closest('.unit-row');
        let baseCost = parseFloat(document.getElementById('purchase_price').value) || 0;
        let factor = parseFloat(row.querySelector('.unit-factor').value) || 0;
        let pieces = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
        
        let baseCostPerPiece = (pieces > 0) ? (baseCost / pieces) : baseCost;
        
        let newCost = baseCostPerPiece * factor;
        row.querySelector('.unit-cost').value = newCost.toFixed(2);
        
        let profitInput = row.querySelector('.unit-profit');
        calcExtraUnitSell(profitInput);
    }

    function calcExtraUnitSell(input) {
        let row = input.closest('.unit-row');
        let cost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let profit = parseFloat(input.value) || 0;
        let sellInput = row.querySelector('.unit-sell');
        if(cost > 0) {
            let sellingPrice = cost * (1 + (profit / 100));
            sellInput.value = sellingPrice.toFixed(2);
        }
    }

    function calcExtraUnitProfit(input) {
        let row = input.closest('.unit-row');
        let cost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let sell = parseFloat(input.value) || 0;
        let profitInput = row.querySelector('.unit-profit');
        if(cost > 0) {
            let profitPercent = ((sell - cost) / cost) * 100;
            profitInput.value = profitPercent.toFixed(2);
        }
    }

    function updateAllUnitsCosts() {
        document.querySelectorAll('.unit-factor').forEach(i => calculateUnitCost(i));
    }

    function toggleRecipeBuilder() {
        let type = document.querySelector('input[name="product_type"]:checked')?.value;
        let section = document.getElementById('recipe_builder_section');
        let extraUnitsSection = document.getElementById('extra_units_section');
        
        let isSale = document.getElementById('base_is_sale');
        let isPurchase = document.getElementById('base_is_purchase');
        
        // Selling Price Input Divs
        let sellDiv = document.getElementById('selling_price_div');
        let marginDiv = document.getElementById('margin_div');
        let tradeDiv = document.getElementById('trade_checkboxes'); 
        let catDiv = document.getElementById('category_div');
        let catInput = document.getElementById('category_id');

        // Helper to control visibility
        const setDisplay = (el, show) => el.style.display = show ? 'block' : 'none';
        const setCheckDisplay = (el, show) => el.parentElement.style.display = show ? 'block' : 'none';

        if (type === 'meal') {
            section.style.display = 'block';
            if(extraUnitsSection) extraUnitsSection.style.display = 'none';
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
            catDiv.style.display = 'block';
            tradeDiv.style.display = 'flex'; 

            catInput.required = true;
            
            // Meal: Sale Only
            // Don't force check on Edit, respect saved value? 
            // Actually, for consistency with Create logic and user intent ("Meal is sell only"), we can force it or at least show/hide.
            setCheckDisplay(isSale, true); 
            setCheckDisplay(isPurchase, false);

            document.getElementById('purchase_label').innerText = 'تكلفة المكونات (آلي)';
            document.getElementById('purchase_price').readOnly = true;

        } else if (type === 'ingredient') {
            section.style.display = 'none';
            if(extraUnitsSection) extraUnitsSection.style.display = 'none';
            
            catDiv.style.display = 'none';
            catInput.required = false;
            tradeDiv.style.display = 'flex';

            // Purchase: Yes (Forced/Hidden or Visible/Checked) - User said "Cancel purchase box"
            setCheckDisplay(isPurchase, false); 
            // Ensure checked if not already? The backend might handle it, but safer to assume 'ingredient' implies purchase.
             if(!isPurchase.checked) isPurchase.checked = true;

            // Sale: Optional (Show box)
            setCheckDisplay(isSale, true);
            
            // Visibility of Sell/Margin handled by toggleBaseSaleFields
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;

        } else if (type === 'compound') { 
            section.style.display = 'block'; 
            if(extraUnitsSection) extraUnitsSection.style.display = 'none';
            catDiv.style.display = 'none';
            catInput.required = false;
            tradeDiv.style.display = 'flex';

            // Compound: Sale (Optional), Purchase (No)
            setCheckDisplay(isPurchase, false);
            if(isPurchase.checked) isPurchase.checked = false; // Force uncheck

            setCheckDisplay(isSale, true);
            
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';

            document.getElementById('purchase_label').innerText = 'تكلفة التحضير (آلي)';
            document.getElementById('purchase_price').readOnly = true;

        } else {
            // Standard
            section.style.display = 'none';
            if(extraUnitsSection) extraUnitsSection.style.display = 'block';
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
            catDiv.style.display = 'none'; 
            catInput.required = false;
            tradeDiv.style.display = 'flex';

            setCheckDisplay(isSale, true);
            setCheckDisplay(isPurchase, true);

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;
        }

        // Trigger visibility update
        toggleBaseSaleFields();
        
        let invDiv = document.getElementById('inventory_settings_div');
        if (type === 'standard') {
            invDiv.style.display = 'block';
        } else {
            invDiv.style.display = 'none';
        }
    }

    function previewImage(input, imgId) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById(imgId);
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                    img.style.display = 'inline-block'; // Force display
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleSubUnitField() {
        let count = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
        let div = document.getElementById('sub_unit_name_div');
        if (count > 1) {
            div.style.display = 'block';
            document.getElementById('sub_unit_name').required = true;
        } else {
            div.style.display = 'none';
            document.getElementById('sub_unit_name').required = false;
        }
        updateUnitBreakdown();
        updateAllUnitsCosts();
    }

    function calculateBaseCost() {
        calculateMargin();
        updateAllUnitsCosts();
    }
    function calculateMargin() {
        let cost = parseFloat(document.getElementById('purchase_price').value) || 0;
        let sell = parseFloat(document.getElementById('base_sell').value) || 0;
        
        let marginInput = document.getElementById('base_margin');
        if(cost > 0) {
            marginInput.value = (((sell - cost) / cost) * 100).toFixed(2);
        } else {
            marginInput.value = 0;
        }
        updateUnitBreakdown();
    }
    function calculatePriceFromMargin() {
        let cost = parseFloat(document.getElementById('purchase_price').value) || 0;
        let margin = parseFloat(document.getElementById('base_margin').value) || 0;
        
        document.getElementById('base_sell').value = (cost * (1 + (margin / 100))).toFixed(2);
        updateUnitBreakdown();
    }

    function updateUnitBreakdown() {
        let cost = parseFloat(document.getElementById('purchase_price').value) || 0;
        let pieces = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
        let itemName = document.getElementById('sub_unit_name').value || 'قطعة';
        
        let subDiv = document.getElementById('unit_breakdown_div');
        if (subDiv) {
            if (pieces > 1) {
                subDiv.style.display = 'block';
                document.getElementById('item_name_at_breakdown').innerText = itemName;
                document.getElementById('cost_per_piece_display').innerText = (cost / pieces).toFixed(2);
            } else {
                subDiv.style.display = 'none';
            }
        }
    }

    // Recipe Builder with Unit Support
    let recipeIndex = {{ $meal->recipes->count() }};
    let lastRequestRowId = null;

    // Listen for Quick Add success message
    window.addEventListener('message', function(event) {
        if (event.data.type === 'quick_add_success') {
            const newId = event.data.productId;
            
            if (lastRequestRowId) {
                let selectEl = $(`#${lastRequestRowId} .ingredient-select-2`);
                
                $.ajax({
                    url: "{{ route('store.meals.ingredients_json') }}?id=" + newId,
                    method: 'GET',
                    success: function(data) {
                        if (data && data.length > 0) {
                            let ing = data[0];
                            let unitsJson = encodeURIComponent(JSON.stringify(ing.units));
                            let barcodeTxt = ing.barcode ? ` [${ing.barcode}]` : '';
                            
                            let newOption = new Option(ing.name_ar + barcodeTxt, ing.id, true, true);
                            $(newOption).attr('data-units', unitsJson);
                            selectEl.empty().append(newOption).trigger('change');
                            populateRecipeUnits(selectEl[0]);
                        }
                    }
                });
            }
        }
    });

    function openQuickAddIngredient(rowId) {
        lastRequestRowId = rowId;
        const url = "{{ route('store.meals.create') }}?quick_add=1";
        window.open(url, 'QuickAddIngredient', 'width=1100,height=850,scrollbars=yes');
    }

    function addRecipeRow() {
        let rowId = `recipe_row_${recipeIndex}`;
        let options = '<option value="">-- ابحث عن صنف أو باركود --</option>';


        let html = `
            <tr id="${rowId}" class="recipe-row">
                <td>
                    <select name="recipe[${recipeIndex}][ingredient_id]" class="form-select ingredient-select-2" onchange="populateRecipeUnits(this)" required>
                        ${options}
                    </select>
                </td>

                <td>
                    <select name="recipe[${recipeIndex}][unit_id]" class="form-select unit-select" onchange="updateRecipeEntry(this)" required>
                        <option value="">--</option>
                    </select>
                </td>
                <td><input type="number" step="any" name="recipe[${recipeIndex}][quantity]" class="form-control text-center qty" value="1" oninput="updateRecipeEntry(this)" required></td>
                <td class="text-center fw-bold text-danger row-cost">0.00</td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-success btn-sm quick-add-btn" onclick="openQuickAddIngredient('${rowId}')" title="إضافة صنف جديد">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove(); calculateTotalRecipe();">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        document.getElementById('recipe_rows').insertAdjacentHTML('beforeend', html);
        initIngredientSelect2($(`#${rowId} .ingredient-select-2`));
        recipeIndex++;
    }

    function initIngredientSelect2(selectObj) {
        let tr = selectObj.closest('tr');
        selectObj.select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            placeholder: '-- ابحث باسم الصنف أو الباركود --',
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('store.meals.ingredients_json') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data, params) {
                    let results = data.map(ing => {
                        return { id: ing.id, text: ing.name_ar + (ing.barcode ? ` [${ing.barcode}]` : ''), units: ing.units };
                    });

                    // Auto-select if exactly 1 result
                    if (results.length === 1 && params.term) {
                        setTimeout(() => {
                            if ($('.select2-search__field').val()) {
                                let item = results[0];
                                let newOption = new Option(item.text, item.id, true, true);
                                $(newOption).attr('data-units', encodeURIComponent(JSON.stringify(item.units)));
                                selectObj.empty().append(newOption).trigger('change');
                                selectObj.select2('close');
                                populateRecipeUnits(selectObj[0]);
                                setTimeout(() => tr.find('.qty').focus().select(), 50);
                            }
                        }, 100);
                    }
                    return { results: results };
                },
                cache: true
            },
            language: {
                inputTooShort: function() { return "ابدأ الكتابة للبحث..."; },
                noResults: function() { return "لا توجد نتائج"; },
                searching: function() { return "جاري البحث..."; }
            }
        });

        selectObj.on('select2:select', function(e) {
            let data = e.params.data;
            if (data.units) {
                $(this).find(':selected').attr('data-units', encodeURIComponent(JSON.stringify(data.units)));
            }
            populateRecipeUnits(this);
            setTimeout(() => tr.find('.qty').focus().select(), 50);
        });

        if (!selectObj.val()) {
            setTimeout(() => selectObj.select2('open'), 50);
        }
    }



    
    function populateRecipeUnits(ingredientSelect) {
        let tr = ingredientSelect.closest('tr');
        let quickAddBtn = tr.querySelector('.quick-add-btn');
        if (ingredientSelect.value) {
            if (quickAddBtn) quickAddBtn.style.display = 'none';
        } else {
            if (quickAddBtn) quickAddBtn.style.display = 'inline-block';
        }

        let unitSelect = tr.querySelector('.unit-select');
        unitSelect.innerHTML = '<option value="">--</option>';
        
        let opt = ingredientSelect.options[ingredientSelect.selectedIndex];
        if(!opt.value) return;

        let units = JSON.parse(decodeURIComponent(opt.dataset.units));
        
        units.forEach(u => {
            let label = u.unit_name;
            let selected = u.is_base_unit ? 'selected' : '';
            unitSelect.innerHTML += `<option value="${u.id}" ${selected} data-cost="${u.cost_price}">${label}</option>`;
        });
        
        updateRecipeEntry(unitSelect);
    }

    function updateRecipeEntry(el) {
        let tr = el.closest('tr');
        let unitSelect = tr.querySelector('.unit-select');
        let qtyInput = tr.querySelector('.qty');
        
        let unitOpt = unitSelect.options[unitSelect.selectedIndex];
        let cost = 0;
        
        if (unitOpt && unitOpt.value) {
            let unitCost = parseFloat(unitOpt.dataset.cost) || 0;
            let qty = parseFloat(qtyInput.value) || 0;
            cost = unitCost * qty;
        }
        
        tr.querySelector('.row-cost').innerText = cost.toFixed(2);
        calculateTotalRecipe();
    }

    function calculateTotalRecipe() {
        let total = 0;
        document.querySelectorAll('.row-cost').forEach(td => total += parseFloat(td.innerText) || 0);
        document.getElementById('total_recipe_cost').innerText = total.toFixed(2);
        
        let productType = document.querySelector('input[name="product_type"]:checked')?.value;
        if (productType === 'meal' || productType === 'compound') {
            document.getElementById('purchase_price').value = total.toFixed(2);
            calculateBaseCost();
        }
    }


    function toggleBaseSaleFields() {
        let isSale = document.getElementById('base_is_sale').checked;
        let sellDiv = document.getElementById('selling_price_div');
        let marginDiv = document.getElementById('margin_div');
        if (isSale) {
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
        } else {
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';
        }
    }

    document.getElementById('base_is_sale').addEventListener('change', toggleBaseSaleFields);

    function toggleRowSaleFields(checkbox) {
        let row = checkbox.closest('.row');
        let sellDiv = row.querySelector('.unit-sell').closest('.col-md-4');
        let marginDiv = row.querySelector('.unit-profit').closest('.col-md-4');
        if (checkbox.checked) {
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
        } else {
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleRecipeBuilder();
        calculateTotalRecipe();
        toggleCustomUnitInput();
        toggleBaseSaleFields();
        
        // Initial state for existing rows
        $('.row-is-sale').each(function() {
            toggleRowSaleFields(this);
        });

        // Initialize Select2 for existing rows
        $('.ingredient-select-2').each(function() {
            initIngredientSelect2($(this));
        });

    });



    function toggleCustomUnitInput() {
        let select = document.getElementById('base_unit_select');
        let input = document.getElementById('base_unit_custom');
        if (select.value === 'custom') {
            input.style.display = 'block';
            input.disabled = false;
        } else {
            input.style.display = 'none';
            input.disabled = true;
        }
    }
</script>
@endsection

@section('scripts_after')
{{-- قالب الوحدة الإضافية --}}
<template id="unit_template">
    @include('store_owner.meals.partials.unit_row', ['index' => 'INDEX'])
</template>
@endsection

@endsection
