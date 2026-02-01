@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="fas fa-utensils me-2"></i> إضافة صنف إلى المنيو (وجبة أو مادة خام)</h3>
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

    <form action="{{ route('store.meals.store') }}" method="POST" enctype="multipart/form-data" id="mealForm" novalidate>
        @csrf
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-1"></i> تفاصيل الصنف الجديد</h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                    <label class="form-check-label fw-bold text-white" for="is_active">فعال</label>
                </div>
            </div>
            
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم الوجبة أو المادة الخام (عربي) <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control" required value="{{ old('name_ar') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">اسم الوجبة/المكون (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}">
                    </div>
                    
                    <div class="col-md-6" id="category_div">
                        <label class="form-label fw-bold">تصنيف المنيو <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">-- اختر التصنيف --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">الوصف</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                    </div>

                    <div class="col-md-12">
                        <div class="card border-primary bg-primary bg-opacity-10 shadow-sm mt-2">
                            <div class="card-body">
                                <label class="form-label fw-bold text-primary">نوع الصنف في المنيو</label>
                                <div class="d-flex gap-4 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_meal" value="meal" {{ old('product_type', 'meal') == 'meal' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-danger" for="type_meal">وجبة جاهزة (بيع فقط - تعتمد على مكونات)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_ingredient" value="ingredient" {{ old('product_type') == 'ingredient' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-success" for="type_ingredient">مادة خام / مكون (شراء فقط)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_standard" value="standard" {{ old('product_type', 'meal') == 'standard' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold" for="type_standard">منتج جاهز (شراء وبيع)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_compound" value="compound" {{ old('product_type') == 'compound' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-primary" for="type_compound">مكون مركب (تحضير داخلي)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- الوحدة الأساسية --}}
                <div class="card border-success shadow-sm mb-3">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fas fa-cube me-1"></i> وحدة التقديم / وحدة القياس
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">اسم الوحدة الرئيسية</label>
                                <select name="base_unit_select" id="base_unit_select" class="form-select" onchange="toggleCustomUnitInput()">
                                    <option value="قطعة">قطعة</option>
                                    <option value="كيلوغرام">كيلوغرام</option>
                                    <option value="غرام">غرام</option>
                                    <option value="ليتر">ليتر</option>
                                    <option value="مل">مل</option>
                                    <option value="custom">مخصص (أدخل يدوياً)</option>
                                </select>
                                <input type="text" name="base_unit_name" id="base_unit_custom" class="form-control mt-2" style="display: none;" placeholder="اسم الوحدة (مثال: ربطة)" disabled value="{{ old('base_unit_name') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">عدد القطع بالعبوة</label>
                                <input type="number" step="any" name="pieces_per_unit" id="pieces_per_unit" class="form-control text-center" value="{{ old('pieces_per_unit', 1) }}" oninput="toggleSubUnitField()">
                            </div>
                            <div class="col-md-3" id="sub_unit_name_div" style="display: none;">
                                <label class="form-label small text-info">اسم القطعة (اختياري)</label>
                                <input type="text" name="sub_unit_name" id="sub_unit_name" class="form-control" value="{{ old('sub_unit_name') }}" placeholder="مثال: حبة، غرام">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-danger fw-bold" id="purchase_label">تكلفة الإنتاج / الشراء</label>
                                <input type="number" step="any" name="purchase_price" id="purchase_price" class="form-control text-center" value="{{ old('purchase_price', 0) }}" required oninput="calculateBaseCost()">
                            </div>
                            <div class="col-md-3" id="selling_price_div">
                                <label class="form-label small text-success fw-bold">سعر البيع</label>
                                <input type="number" step="any" name="base_selling_price" id="base_sell" class="form-control text-center fw-bold" value="{{ old('base_selling_price', 0) }}" required oninput="calculateMargin()">
                            </div>
                            <div class="col-md-3" id="margin_div">
                                <label class="form-label small text-muted">الربح %</label>
                                <input type="number" step="any" name="base_profit_percent" id="base_margin" class="form-control text-center text-primary" value="{{ old('base_profit_percent', 0) }}" oninput="calculatePriceFromMargin()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">باركود (اختياري)</label>
                                <input type="text" name="base_barcode" class="form-control" value="{{ old('base_barcode') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">صورة الوحدة</label>
                                <input type="file" name="base_unit_image" class="form-control form-control-sm" accept="image/*" onchange="previewImage(this, 'base_preview')">
                                <div class="mt-2 text-center">
                                    <img id="base_preview" src="#" alt="معاينة الصورة" class="img-thumbnail d-none" style="max-height: 80px;">
                                </div>
                            </div>
                                <div class="col-md-3 d-flex align-items-end justify-content-start gap-3 pb-1" id="trade_checkboxes">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="base_is_purchase" id="base_is_purchase" checked>
                                        <label class="form-check-label small fw-bold" for="base_is_purchase">شراء</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="base_is_sale" id="base_is_sale" checked>
                                        <label class="form-check-label small fw-bold" for="base_is_sale">بيع</label>
                                    </div>
                                </div>
                                
                                {{-- توضيح تكلفة التفكيك --}}
                                <div class="col-12 mt-2" id="unit_breakdown_div" style="display: none;">
                                    <div class="alert alert-info py-2 mb-0 border-0 shadow-sm d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold"><i class="fas fa-info-circle me-1"></i> تكلفة القطعة الواحدة (<span id="item_name_at_breakdown">...</span>):</span>
                                        <span class="english-num fw-bold fs-5 text-danger" id="cost_per_piece_display">0.00</span>
                                    </div>
                                </div>
                        </div>

                        {{-- الوحدات الإضافية (تظهر فقط للمنتجات الجاهزة) --}}
                        <div id="extra_units_section" style="display: none;">
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-layer-group me-1"></i> الوحدات الإضافية (كرتون، درزن...)</h6>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="addExtraUnit()"><i class="fa fa-plus me-1"></i> إضافة وحدة</button>
                            </div>
                            <div id="extra_units_container"></div>
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
                                <input type="number" name="alert_quantity" class="form-control" value="5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">تنبيه انتهاء الصلاحية (بالأيام)</label>
                                <input type="number" name="expiry_warning_days" class="form-control" value="30">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recipe Builder --}}
                <div id="recipe_builder_section" class="card border-danger shadow-sm mb-4" style="display: none;">
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
                                <tbody id="recipe_rows"></tbody>
                                <tfoot>
                                    <tr class="table-dark text-center">
                                        <td colspan="2" class="text-end fw-bold">إجمالي تكلفة المكونات:</td>
                                        <td id="total_recipe_cost" class="fw-bold fs-5">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-end py-3">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i class="fas fa-save me-2"></i> حفظ في المنيو</button>
                </div>
            </div>
        </div>
    </form>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>

    let unitIndex = 0;

    function addExtraUnit(data = null) {
        let tpl = document.getElementById('unit_template').innerHTML.replace(/INDEX/g, unitIndex);
        document.getElementById('extra_units_container').insertAdjacentHTML('beforeend', tpl);
        
        let row = document.getElementById('extra_units_container').lastElementChild;
        if(data) {
            // Fill data if needed (for edit/old)
        }
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
        
        // Base cost per piece if sub_unit_count > 1
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

        if (type === 'meal') {
            section.style.display = 'block';
            extraUnitsSection.style.display = 'none';
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
            catDiv.style.display = 'block';
            catInput.required = true;
            isSale.checked = true;
            isPurchase.checked = false;

            document.getElementById('purchase_label').innerText = 'تكلفة المكونات (آلي)';
            document.getElementById('purchase_price').readOnly = true;
        } else if (type === 'ingredient') {
            section.style.display = 'none';
            extraUnitsSection.style.display = 'none';
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';
            catDiv.style.display = 'none';
            catInput.required = false;
            isSale.checked = false;
            isPurchase.checked = true;

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;
        } else if (type === 'compound') { // New Compound Type
            section.style.display = 'block'; // Shows Recipe
            extraUnitsSection.style.display = 'none';
            sellDiv.style.display = 'none'; // No Selling Price
            marginDiv.style.display = 'none';
            catDiv.style.display = 'none';
            catInput.required = false;
            isSale.checked = false;
            isPurchase.checked = false; // Internal Use

            document.getElementById('purchase_label').innerText = 'تكلفة التحضير (آلي)';
            document.getElementById('purchase_price').readOnly = true;
        } else {
            // Standard (Ready Product)
            section.style.display = 'none';
            extraUnitsSection.style.display = 'block';
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
            catDiv.style.display = 'none'; // Hide Category for Ready Products per user request
            catInput.required = false;
            isSale.checked = true;
            isPurchase.checked = true;

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;
        }

        // Toggle Inventory Settings
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
                img.src = e.target.result;
                img.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Cost logic
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
    let recipeIndex = 0;
    const ingredientsData = @json($ingredients);

    function addRecipeRow() {
        let rowId = `recipe_row_${recipeIndex}`;
        let options = '<option value="">-- اختر مكون --</option>';
        ingredientsData.forEach(ing => {
            // Encode units to avoid JSON breaking in attribute
            let unitsJson = encodeURIComponent(JSON.stringify(ing.units));
            let barcodeTxt = ing.barcode ? ` [${ing.barcode}]` : '';
            options += `<option value="${ing.id}" data-units="${unitsJson}">${ing.name_ar}${barcodeTxt}</option>`;
        });


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
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove(); calculateTotalRecipe();"><i class="fas fa-trash"></i></button></td>
            </tr>`;
        document.getElementById('recipe_rows').insertAdjacentHTML('beforeend', html);
        
        // Initialize Select2 on the new row
        let selectEl = $(`#${rowId} .ingredient-select-2`);
        selectEl.select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            placeholder: '-- ابحث باسم الصنف أو الباركود --',
            width: '100%',
            minimumInputLength: 1, // Don't show list until typing
            language: {
                inputTooShort: function() {
                    return "ابدأ الكتابة للبحث...";
                },
                noResults: function() {
                    return "لا توجد نتائج";
                },
                searching: function() {
                    return "جاري البحث...";
                }
            }
        });

        // Auto-open and focus search
        selectEl.select2('open');

        // Logic to auto-select if exactly 1 match in ingredientsData
        selectEl.on('select2:open', function() {
            let searchField = document.querySelector('.select2-search__field');
            if (searchField) {
                searchField.focus();
                
                $(searchField).on('input', function() {
                    let query = this.value.trim().toLowerCase();
                    if (query.length > 0) {
                        // Filter local data
                        let matches = ingredientsData.filter(ing => {
                            let nameAr = (ing.name_ar || '').toLowerCase();
                            let barcode = (ing.barcode || '').toLowerCase();
                            return nameAr.includes(query) || barcode === query;
                        });

                        // If exactly one match, select it
                        if (matches.length === 1) {
                            selectEl.val(matches[0].id).trigger('change');
                            selectEl.select2('close');
                            
                            // Focus quantity input
                            let nextInput = document.getElementById(rowId).querySelector('.qty');
                            if (nextInput) {
                                setTimeout(() => nextInput.focus().select(), 100);
                            }
                        }
                    }
                });

                // Support Enter key for first result
                $(searchField).on('keydown', function(e) {
                    if (e.which === 13) { // Enter
                        setTimeout(() => {
                            let results = document.querySelectorAll('.select2-results__option--selectable');
                            if (results.length > 0) {
                                let firstResultId = $(results[0]).data('data')?.id;
                                if (firstResultId) {
                                    selectEl.val(firstResultId).trigger('change');
                                    selectEl.select2('close');
                                    let nextInput = document.getElementById(rowId).querySelector('.qty');
                                    if (nextInput) {
                                        setTimeout(() => nextInput.focus().select(), 100);
                                    }
                                }
                            }
                        }, 50);
                    }
                });
            }
        });

        recipeIndex++;
    }



    
    function populateRecipeUnits(ingredientSelect) {
        let tr = ingredientSelect.closest('tr');
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
        toggleSubUnitField();
        toggleCustomUnitInput();
        toggleBaseSaleFields();
    });


    function toggleCustomUnitInput() {
        let select = document.getElementById('base_unit_select');
        let input = document.getElementById('base_unit_custom');
        if (select.value === 'custom') {
            input.style.display = 'block';
            input.disabled = false;
            input.required = true;
        } else {
            input.style.display = 'none';
            input.disabled = true;
            input.required = false;
        }
    }
</script>
@endsection
{{-- قالب الوحدة الإضافية --}}
<template id="unit_template">
    <div class="unit-row card border-secondary mb-2 shadow-sm position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 bg-danger" onclick="this.closest('.unit-row').remove()"></button>
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-2 text-center">
                    <img id="preview_INDEX" src="{{ asset('images/default-product.png') }}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: contain;">
                    <input type="file" name="units[INDEX][image]" class="form-control form-control-sm mt-1" accept="image/*" onchange="previewImage(this, 'preview_INDEX')">
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
                            <input type="number" step="any" name="units[INDEX][factor]" class="form-control form-control-sm unit-factor fw-bold text-center" value="1" oninput="calculateUnitCost(this)">
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
                                <input class="form-check-input row-is-sale" type="checkbox" name="units[INDEX][is_sale]" checked onchange="toggleRowSaleFields(this)">
                                <label class="form-check-label small fw-bold">بيع</label>
                            </div>

                        </div>

                        <div class="col-md-4">
                            <label class="small text-muted fw-bold">التكلفة (آلي)</label>
                            <input type="number" step="any" name="units[INDEX][cost_price]" class="form-control form-control-sm bg-light unit-cost fw-bold text-danger text-center" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="small fw-bold text-primary">الربح %</label>
                            <input type="number" step="any" name="units[INDEX][profit_percent]" class="form-control form-control-sm unit-profit fw-bold text-primary text-center" oninput="calcExtraUnitSell(this)">
                        </div>

                        <div class="col-md-4">
                            <label class="small text-success fw-bold">سعر البيع</label>
                            <input type="number" step="any" name="units[INDEX][selling_price]" class="form-control form-control-sm unit-sell fw-bold text-success text-center" oninput="calcExtraUnitProfit(this)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection
