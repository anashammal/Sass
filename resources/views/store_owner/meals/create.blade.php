@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="fas fa-utensils me-2"></i> إضافة صنف إلى المنيو (وجبة أو مادة خام)</h3>
        <a href="{{ route('store.meals.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right me-1"></i> العودة للمنيو</a>
    </div>

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
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">تصنيف المنيو <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
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
                                        <input class="form-check-input" type="radio" name="product_type" id="type_standard" value="standard" {{ old('product_type') == 'standard' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold" for="type_standard">منتج جاهز (شراء وبيع مباشرة)</label>
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
                                <label class="form-label small">اسم الوحدة الرئيسية</label>
                                <select name="base_unit_select" id="base_unit_select" class="form-select" onchange="toggleCustomUnitInput()">
                                    <option value="قطعة">قطعة</option>
                                    <option value="كيلوغرام">كيلوغرام</option>
                                    <option value="غرام">غرام</option>
                                    <option value="custom">مخصص (أدخل يدوياً)</option>
                                </select>
                                <input type="text" name="base_unit_name" id="base_unit_custom" class="form-control mt-2" style="display: none;" placeholder="اسم الوحدة (مثال: ربطة)" disabled value="{{ old('base_unit_name') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">يحتوي على (عدد/كمية)</label>
                                <input type="number" step="any" name="sub_unit_count" id="sub_unit_count" class="form-control text-center" value="{{ old('sub_unit_count', 1) }}" oninput="toggleSubUnitField()">
                            </div>
                            <div class="col-md-3" id="sub_unit_name_div" style="display: none;">
                                <label class="form-label small text-info">اسم الوحدة الفرعية</label>
                                <input type="text" name="sub_unit_name" id="sub_unit_name" class="form-control" value="{{ old('sub_unit_name') }}" placeholder="مثال: رغيف، غرام">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-danger fw-bold" id="purchase_label">تكلفة الإنتاج / الشراء</label>
                                <input type="number" step="any" name="purchase_price" id="purchase_price" class="form-control text-center" value="{{ old('purchase_price', 0) }}" required oninput="calculateBaseCost()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-success fw-bold">سعر البيع</label>
                                <input type="number" step="any" name="base_selling_price" id="base_sell" class="form-control text-center fw-bold" value="{{ old('base_selling_price', 0) }}" required oninput="calculateMargin()">
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3 d-flex align-items-end gap-3 pb-1" id="trade_checkboxes" style="display: none !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="base_is_purchase" id="base_is_purchase" checked>
                                    <label class="form-check-label small fw-bold" for="base_is_purchase">شراء</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="base_is_sale" id="base_is_sale" checked>
                                    <label class="form-check-label small fw-bold" for="base_is_sale">بيع</label>
                                </div>
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
<script>
    function toggleRecipeBuilder() {
        let type = document.querySelector('input[name="product_type"]:checked')?.value;
        let section = document.getElementById('recipe_builder_section');
        
        let isSale = document.getElementById('base_is_sale');
        let isPurchase = document.getElementById('base_is_purchase');

        if (type === 'meal') {
            section.style.display = 'block';
            isSale.checked = true;
            isPurchase.checked = false;
            
            document.getElementById('purchase_label').innerText = 'تكلفة المكونات (آلي)';
            document.getElementById('purchase_price').readOnly = true;
        } else if (type === 'ingredient') {
            section.style.display = 'none';
            isSale.checked = false;
            isPurchase.checked = true;

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;
        } else {
            section.style.display = 'none';
            isSale.checked = true;
            isPurchase.checked = true;

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;
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
        let count = parseFloat(document.getElementById('sub_unit_count').value) || 1;
        let div = document.getElementById('sub_unit_name_div');
        if (count > 1) {
            div.style.display = 'block';
            document.getElementById('sub_unit_name').required = true;
        } else {
            div.style.display = 'none';
            document.getElementById('sub_unit_name').required = false;
        }
    }

    function calculateBaseCost() {
        calculateMargin();
    }
    function calculateMargin() {
        let cost = parseFloat(document.getElementById('purchase_price').value) || 0;
        let sell = parseFloat(document.getElementById('base_sell').value) || 0;
        if(cost > 0) document.getElementById('base_margin').value = (((sell - cost) / cost) * 100).toFixed(2);
    }
    function calculatePriceFromMargin() {
        let cost = parseFloat(document.getElementById('purchase_price').value) || 0;
        let margin = parseFloat(document.getElementById('base_margin').value) || 0;
        document.getElementById('base_sell').value = (cost * (1 + (margin / 100))).toFixed(2);
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
            options += `<option value="${ing.id}" data-units="${unitsJson}">${ing.name_ar}</option>`;
        });

        let html = `
            <tr id="${rowId}" class="recipe-row">
                <td><select name="recipe[${recipeIndex}][ingredient_id]" class="form-select" onchange="populateRecipeUnits(this)" required>${options}</select></td>
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
        recipeIndex++;
    }
    
    function populateRecipeUnits(ingredientSelect) {
        let tr = ingredientSelect.closest('tr');
        let unitSelect = tr.querySelector('.unit-select');
        unitSelect.innerHTML = '<option value="">--</option>';
        
        let opt = ingredientSelect.options[ingredientSelect.selectedIndex];
        if(!opt.value) return;

        let units = JSON.parse(decodeURIComponent(opt.dataset.units));
        
        // Show units: if empty, show just the product name?
        // Usually product has at least one unit (base).
        units.forEach(u => {
            let label = u.unit_name;
            // Optionally add price hint: ${label} (${u.cost_price})
            let selected = u.is_base_unit ? 'selected' : '';
            unitSelect.innerHTML += `<option value="${u.id}" ${selected} data-cost="${u.cost_price}">${label}</option>`;
        });
        
        // Trigger calculation updates
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
        if (document.querySelector('input[name="product_type"]:checked')?.value === 'meal') {
            document.getElementById('purchase_price').value = total.toFixed(2);
            calculateBaseCost();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleRecipeBuilder();
        toggleSubUnitField();
        toggleCustomUnitInput();
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
@endsection
