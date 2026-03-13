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
        @if(request('iframe')) <input type="hidden" name="iframe" value="1"> @endif
        @if(request('quick_add')) <input type="hidden" name="quick_add" value="1"> @endif

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
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{ __('اسم المنتج') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="{{ __('مثال: بيتزا مارغريتا') }}">
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
                                    @if(!request('quick_add'))
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_meal" value="meal" {{ old('product_type', 'meal') == 'meal' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-danger" for="type_meal">وجبة جاهزة (بيع فقط - تعتمد على مكونات)</label>
                                    </div>
                                    @endif
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_ingredient" value="ingredient" {{ old('product_type', request('quick_add') ? 'ingredient' : 'meal') == 'ingredient' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
                                        <label class="form-check-label fw-bold text-success" for="type_ingredient">مادة خام / مكون (شراء فقط)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="product_type" id="type_standard" value="standard" {{ old('product_type') == 'standard' ? 'checked' : '' }} onchange="toggleRecipeBuilder()">
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
                                <div class="input-group">
                                    <input type="number" step="any" name="purchase_price" id="purchase_price" class="form-control text-center" value="{{ old('purchase_price', 0) }}" required oninput="calculateBaseCost()">
                                    <select name="purchase_price_currency_id" id="purchase_price_currency_id" class="form-select currency-select" style="max-width: 90px;" onchange="handleCurrencyChange(this, 'purchase_exchange_rate')">
                                        @foreach($acceptedCurrencies as $cur)
                                            <option value="{{ $cur->id }}" data-code="{{ $cur->code }}" data-rate="{{ $currenciesData[$cur->id] ?? 1 }}" {{ $cur->id == $baseCurrency->id ? 'selected' : '' }}>{{ $cur->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="purchase_exchange_rate" id="purchase_exchange_rate" value="1">
                                <div id="purchase_exchange_info" class="exchange-info mt-1" style="display: none;"></div>
                            </div>
                            <div class="col-md-3" id="selling_price_div">
                                <label class="form-label small text-success fw-bold">سعر البيع</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="base_selling_price" id="base_sell" class="form-control text-center fw-bold" value="{{ old('base_selling_price', 0) }}" required oninput="calculateMargin()">
                                    <select name="base_selling_price_currency_id" id="base_selling_price_currency_id" class="form-select currency-select" style="max-width: 90px;" onchange="handleCurrencyChange(this, 'base_sell_exchange_rate')">
                                        @foreach($acceptedCurrencies as $cur)
                                            <option value="{{ $cur->id }}" data-code="{{ $cur->code }}" data-rate="{{ $currenciesData[$cur->id] ?? 1 }}" {{ $cur->id == $baseCurrency->id ? 'selected' : '' }}>{{ $cur->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="base_sell_exchange_rate" id="base_sell_exchange_rate" value="1">
                                <div id="base_sell_exchange_info" class="exchange-info mt-1" style="display: none;"></div>
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
                                <label class="form-label small"> {{ __('صورة الوحدة') }} </label>
                                <div class="localized-file-wrapper">
                                    <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                                        <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                                    </button>
                                    <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                                    <input type="file" name="base_unit_image" accept="image/*" onchange="previewImage(this, 'base_preview'); updateFileName(this)">
                                </div>
                                <div class="mt-2 text-center">
                                    <img id="base_preview" src="#" alt="{{ __('معاينة الصورة') }}" class="img-thumbnail d-none" style="max-height: 80px;">
                                </div>
                            </div>
                                <div class="col-md-3 d-flex align-items-end justify-content-start gap-3 pb-1" id="trade_checkboxes">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="base_is_purchase" id="base_is_purchase" checked>
                                        <label class="form-check-label small fw-bold" for="base_is_purchase">شراء</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="base_is_sale" id="base_is_sale" checked onchange="toggleBaseSaleFields()">
                                        <label class="form-check-label small fw-bold" for="base_is_sale">قابل للبيع</label>
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
        let baseRate = parseFloat(document.getElementById('purchase_exchange_rate').value) || 1;
        let baseCostInBase = baseCost * baseRate;

        let factor = parseFloat(row.querySelector('.unit-factor').value) || 0;
        let pieces = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
        
        let baseCostPerPiece = (pieces > 0) ? (baseCostInBase / pieces) : baseCostInBase;
        let newCostInBase = baseCostPerPiece * factor;
        
        let unitPurchaseRate = parseFloat(row.querySelector('[name*="purchase_exchange_rate"]').value) || 1;
        let newCostInSelectedCurrency = newCostInBase / unitPurchaseRate;

        let costInput = row.querySelector('.unit-cost');
        costInput.value = Number(newCostInSelectedCurrency.toFixed(3));
        costInput.dataset.baseValue = newCostInBase; // Preserve precision for exchange info
        
        let profitInput = row.querySelector('.unit-profit');
        calcExtraUnitSell(profitInput);
    }

    function handleCurrencyChangeUnit(select) {
        let opt = select.options[select.selectedIndex];
        let currId = select.value;
        let currCode = opt.dataset.code;
        let suggestedRate = parseFloat(opt.dataset.rate) || 1;
        let baseCurrId = "{{ $baseCurrency->id }}";
        let baseCurrCode = "{{ $baseCurrency->code }}";

        let unitRow = select.closest('.unit-row');
        let hiddenInputName = select.name.includes('purchase') ? '[purchase_exchange_rate]' : '[sell_exchange_rate]';
        let hiddenInput = unitRow.querySelector(`[name$="${hiddenInputName}"]`);

        if (currId == baseCurrId) {
            hiddenInput.value = 1;
            if (select.name.includes('purchase')) calculateUnitCost(select);
            else calcExtraUnitSell(unitRow.querySelector('.unit-profit'));
            return;
        }

        Swal.fire({
            title: `💱 سعر صرف: ${currCode} ↔ ${baseCurrCode}`,
            icon: 'info',
            html: `
                <div class="text-start mb-3">
                    <label class="form-label fw-bold">✏️ سعر صرف (1 ${currCode} = ؟ ${baseCurrCode}):</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white fw-bold">1 ${currCode}</span>
                        <input type="text" inputmode="decimal" id="swalExchangeRateUnit" class="form-control text-center fw-bold fs-5" value="${suggestedRate}">
                        <span class="input-group-text fw-bold">${baseCurrCode}</span>
                    </div>
                </div>
            `,
            confirmButtonText: '✅ تأكيد',
            showCancelButton: true,
            cancelButtonText: '❌ إلغاء',
            preConfirm: () => {
                let val = parseFloat(document.getElementById('swalExchangeRateUnit').value);
                if (!val || val <= 0) {
                    Swal.showValidationMessage('يرجى إدخال سعر صرف صحيح (> 0)');
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                hiddenInput.value = result.value;
                if (select.name.includes('purchase')) calculateUnitCost(select);
                else calcExtraUnitSell(unitRow.querySelector('.unit-profit'));
                if(typeof toastr !== 'undefined') toastr.success('تم تحديث سعر الصرف');
            } else {
                select.value = baseCurrId;
                hiddenInput.value = 1;
                if (select.name.includes('purchase')) calculateUnitCost(select);
                else calcExtraUnitSell(unitRow.querySelector('.unit-profit'));
            }
            updateAllExchangeDisplays();
        });
    }

    function updateExchangeRateUnit(select, hiddenId) {
        let rate = select.options[select.selectedIndex].dataset.rate || 1;
        let unitRow = select.closest('.unit-row');
        if (select.name.includes('purchase')) {
            unitRow.querySelector('[name$="[purchase_exchange_rate]"]').value = rate;
            calculateUnitCost(select);
        } else {
            unitRow.querySelector('[name$="[sell_exchange_rate]"]').value = rate;
            calcExtraUnitSell(unitRow.querySelector('.unit-profit'));
        }
        updateAllExchangeDisplays();
    }

    function calcExtraUnitSell(input) {
        let row = input.closest('.unit-row');
        let costPrice = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let costRate = parseFloat(row.querySelector('[name$="[purchase_exchange_rate]"]').value) || 1;
        
        let profit = parseFloat(row.querySelector('.unit-profit').value) || 0;
        let sellRate = parseFloat(row.querySelector('[name$="[sell_exchange_rate]"]').value) || 1;

        let costInBase = costPrice * costRate;
        if(costInBase > 0) {
            let sellingPriceInBase = costInBase * (1 + (profit / 100));
            let sellingPriceInSelectedCurrency = sellingPriceInBase / sellRate;
            let sellInput = row.querySelector('.unit-sell');
            sellInput.value = Number(sellingPriceInSelectedCurrency.toFixed(3));
            sellInput.dataset.baseValue = sellingPriceInBase; // Preserve precision
        }
        updateAllExchangeDisplays();
    }

    function calcExtraUnitProfit(input) {
        let row = input.closest('.unit-row');
        let costPrice = parseFloat(row.querySelector('.unit-cost').value) || 0;
        let costRate = parseFloat(row.querySelector('[name$="[purchase_exchange_rate]"]').value) || 1;

        let sellPrice = parseFloat(row.querySelector('.unit-sell').value) || 0;
        let sellRate = parseFloat(row.querySelector('[name$="[sell_exchange_rate]"]').value) || 1;

        let costInBase = costPrice * costRate;
        let sellInBase = sellPrice * sellRate;

        let profitInput = row.querySelector('.unit-profit');
        if(costInBase > 0) {
            let profitPercent = ((sellInBase - costInBase) / costInBase) * 100;
            profitInput.value = Number(profitPercent.toFixed(3));
        }
        updateAllExchangeDisplays();
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
            extraUnitsSection.style.display = 'none';
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
            catDiv.style.display = 'block';
            tradeDiv.style.display = 'flex'; // Ensure container is visible
            
            catInput.required = true;
            
            // Meal: Sale Only
            isSale.checked = true;
            setCheckDisplay(isSale, true); // Show Sale Checkbox (Optional to uncheck? Usually meals are for sale)
            
            isPurchase.checked = false;
            setCheckDisplay(isPurchase, false); // Hide Purchase Checkbox

            document.getElementById('purchase_label').innerText = 'تكلفة المكونات (آلي)';
            document.getElementById('purchase_price').readOnly = true;

        } else if (type === 'ingredient') {
            section.style.display = 'none';
            extraUnitsSection.style.display = 'none';
            
            // Ingredient: Purchase (Forced), Sale (Optional)
            catDiv.style.display = 'none';
            catInput.required = false;
            tradeDiv.style.display = 'flex';

            // Purchase: Yes, but hide box (User request)
            isPurchase.checked = true;
            setCheckDisplay(isPurchase, false); 

            // Sale: Default No, but Show box
            isSale.checked = false;
            setCheckDisplay(isSale, true);
            
            // Visibility of Sell/Margin depends on isSale check (handled by toggleBaseSaleFields)
            // But we need to trigger it initially
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;

        } else if (type === 'compound') { 
            section.style.display = 'block'; 
            extraUnitsSection.style.display = 'none';
            catDiv.style.display = 'none';
            catInput.required = false;
            tradeDiv.style.display = 'flex';

            // Compound: Sale (Optional), Purchase (No)
            
            // Purchase: No, Hide box
            isPurchase.checked = false;
            setCheckDisplay(isPurchase, false);

            // Sale: Default No, Show box
            isSale.checked = false;
            setCheckDisplay(isSale, true);

            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';

            document.getElementById('purchase_label').innerText = 'تكلفة التحضير (آلي)';
            document.getElementById('purchase_price').readOnly = true;

        } else {
            // Standard (Ready Product)
            section.style.display = 'none';
            extraUnitsSection.style.display = 'block';
            sellDiv.style.display = 'block';
            marginDiv.style.display = 'block';
            catDiv.style.display = 'none'; 
            catInput.required = false;
            tradeDiv.style.display = 'flex';

            isSale.checked = true;
            setCheckDisplay(isSale, true);

            isPurchase.checked = true;
            setCheckDisplay(isPurchase, true);

            document.getElementById('purchase_label').innerText = 'سعر الشراء';
            document.getElementById('purchase_price').readOnly = false;
        }

        // Trigger visibility update for Sale fields based on the new isSale state
        toggleBaseSaleFields();
        
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
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                    img.style.display = 'inline-block'; // Force display
                }
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
    
    function updateExchangeDisplay(input, select, hiddenRateInput, infoDiv) {
        if (!infoDiv) return;
        let rate = parseFloat(hiddenRateInput.value) || 1;
        let val = parseFloat(input.value) || 0;
        let baseCurrCode = "{{ $baseCurrency->code }}";
        let opt = select.options[select.selectedIndex];
        let selectedCurrCode = opt ? opt.dataset.code : '';

        if (rate != 1 && selectedCurrCode) {
            // Use data-base-value if present for 100% precision
            let inBase = input.dataset.baseValue ? parseFloat(input.dataset.baseValue) : (val * rate);
            infoDiv.innerHTML = `<span class="small text-info fw-bold">(1 ${selectedCurrCode} = ${Number(rate.toFixed(3))} ${baseCurrCode}) ↔ ${Number(inBase.toFixed(3))} ${baseCurrCode}</span>`;
            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    }

    function handleCurrencyChange(select, hiddenId) {
        let opt = select.options[select.selectedIndex];
        let currId = select.value;
        let currCode = opt.dataset.code;
        let suggestedRate = parseFloat(opt.dataset.rate) || 1;
        let baseCurrId = "{{ $baseCurrency->id }}";
        let baseCurrCode = "{{ $baseCurrency->code }}";

        if (currId == baseCurrId) {
            document.getElementById(hiddenId).value = 1;
            calculateMargin();
            calculateTotalRecipe();
            return;
        }

        Swal.fire({
            title: `💱 سعر صرف: ${currCode} ↔ ${baseCurrCode}`,
            icon: 'info',
            html: `
                <div class="text-start mb-3">
                    <label class="form-label fw-bold">✏️ سعر صرف (1 ${currCode} = ؟ ${baseCurrCode}):</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white fw-bold">1 ${currCode}</span>
                        <input type="text" inputmode="decimal" id="swalExchangeRate" class="form-control text-center fw-bold fs-5" value="${suggestedRate}">
                        <span class="input-group-text fw-bold">${baseCurrCode}</span>
                    </div>
                </div>
            `,
            confirmButtonText: '✅ تأكيد',
            showCancelButton: true,
            cancelButtonText: '❌ إلغاء',
            preConfirm: () => {
                let val = parseFloat(document.getElementById('swalExchangeRate').value);
                if (!val || val <= 0) {
                    Swal.showValidationMessage('يرجى إدخال سعر صرف صحيح (> 0)');
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(hiddenId).value = result.value;
                calculateMargin();
                calculateTotalRecipe();
                if(typeof toastr !== 'undefined') toastr.success('تم تحديث سعر الصرف');
            } else {
                // Return to base currency if cancelled
                select.value = baseCurrId;
                document.getElementById(hiddenId).value = 1;
                calculateMargin();
            }
            updateAllExchangeDisplays();
        });
    }

    function updateExchangeRate(select, hiddenId) {
        let rate = select.options[select.selectedIndex].dataset.rate || 1;
        document.getElementById(hiddenId).value = rate;
        calculateMargin(); 
    }

    function calculateMargin() {
        let costPrice = parseFloat(document.getElementById('purchase_price').value) || 0;
        let costRate = parseFloat(document.getElementById('purchase_exchange_rate').value) || 1;
        
        let sellPrice = parseFloat(document.getElementById('base_sell').value) || 0;
        let sellRate = parseFloat(document.getElementById('base_sell_exchange_rate').value) || 1;
        
        // Convert both to base currency for margin calculation
        let costInBase = costPrice * costRate;
        let sellInBase = sellPrice * sellRate;
        
        let marginInput = document.getElementById('base_margin');
        if(costInBase > 0) {
            marginInput.value = Number((((sellInBase - costInBase) / costInBase) * 100).toFixed(3));
        } else {
            marginInput.value = 0;
        }
        updateUnitBreakdown();
        updateAllExchangeDisplays();
    }
    
    function calculatePriceFromMargin() {
        let costPrice = parseFloat(document.getElementById('purchase_price').value) || 0;
        let costRate = parseFloat(document.getElementById('purchase_exchange_rate').value) || 1;
        let margin = parseFloat(document.getElementById('base_margin').value) || 0;
        
        let sellRate = parseFloat(document.getElementById('base_sell_exchange_rate').value) || 1;
        
        let costInBase = costPrice * costRate;
        let sellInBase = costInBase * (1 + (margin / 100));
        
        // Convert sell from base back to selected sell currency
        let sellInSelectedCurrency = sellInBase / sellRate;
        
        let sellInput = document.getElementById('base_sell');
        sellInput.value = Number(sellInSelectedCurrency.toFixed(3));
        sellInput.dataset.baseValue = sellInBase; // Preserve precision
        
        updateUnitBreakdown();
        updateAllExchangeDisplays();
    }
    
    function updateAllExchangeDisplays() {
        // Main fields
        updateExchangeDisplay(
            document.getElementById('purchase_price'),
            document.getElementById('purchase_price_currency_id'),
            document.getElementById('purchase_exchange_rate'),
            document.getElementById('purchase_exchange_info')
        );
        updateExchangeDisplay(
            document.getElementById('base_sell'),
            document.getElementById('base_selling_price_currency_id'),
            document.getElementById('base_sell_exchange_rate'),
            document.getElementById('base_sell_exchange_info')
        );
        
        // Extra units
        document.querySelectorAll('.unit-row').forEach(row => {
            updateExchangeDisplay(
                row.querySelector('.unit-cost'),
                row.querySelector('[name$="[purchase_price_currency_id]"]'),
                row.querySelector('[name$="[purchase_exchange_rate]"]'),
                row.querySelector('.unit-purchase-exchange-info')
            );
            updateExchangeDisplay(
                row.querySelector('.unit-sell'),
                row.querySelector('[name$="[sell_price_currency_id]"]'),
                row.querySelector('[name$="[sell_exchange_rate]"]'),
                row.querySelector('.unit-sell-exchange-info')
            );
        });
    }

    function updateUnitBreakdown() {
        let cost = parseFloat(document.getElementById('purchase_price').value) || 0;
        let pieces = parseFloat(document.getElementById('pieces_per_unit').value) || 1;
        let itemName = document.getElementById('sub_unit_name').value || 'قطعة';
        
        let subDiv = document.getElementById('unit_breakdown_div');
        if (subDiv) {
            if (pieces > 1) {
                subDiv.style.display = 'block';
                let purchaseSelect = document.querySelector('[name="purchase_price_currency_id"]');
                let currencyCode = purchaseSelect ? purchaseSelect.options[purchaseSelect.selectedIndex].text : '';
                
                document.getElementById('item_name_at_breakdown').innerText = itemName;
                document.getElementById('cost_per_piece_display').innerText = Number((cost / pieces).toFixed(3)) + ' ' + currencyCode;
            } else {
                subDiv.style.display = 'none';
            }
        }
    }

    // Recipe Builder with Unit Support
    let recipeIndex = 0;
    let lastRequestRowId = null;

    // Listen for Quick Add success message
    window.addEventListener('message', function(event) {
        if (event.data.type === 'quick_add_success') {
            const newId = event.data.productId;
            const newName = event.data.productName;
            
            // If we know which row triggered the quick add, update and select it
            if (lastRequestRowId) {
                let selectEl = $(`#${lastRequestRowId} .ingredient-select-2`);
                
                // Fetch full product details (to get units)
                $.ajax({
                    url: "{{ route('store.meals.ingredients_json') }}?id=" + newId,
                    method: 'GET',
                    success: function(data) {
                        if (data && data.length > 0) {
                            let ing = data[0];
                            let unitsJson = encodeURIComponent(JSON.stringify(ing.units));
                            let barcodeTxt = ing.barcode ? ` [${ing.barcode}]` : '';
                            
                            // Add new option and select it
                            let newOption = new Option(ing.name + barcodeTxt, ing.id, true, true);
                            $(newOption).attr('data-units', unitsJson);
                            selectEl.empty().append(newOption).trigger('change');
                            
                            // Logic inside populateRecipeUnits will hide the button
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
                        return { id: ing.id, text: ing.name + (ing.barcode ? ` [${ing.barcode}]` : ''), units: ing.units };
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
            dropdownParent: $(tr),
            closeOnSelect: true,
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
        
        tr.querySelector('.row-cost').innerText = Number(cost.toFixed(3));
        calculateTotalRecipe();
    }

    function calculateTotalRecipe() {
        let total = 0;
        document.querySelectorAll('.row-cost').forEach(td => total += parseFloat(td.innerText) || 0);
        document.getElementById('total_recipe_cost').innerText = Number(total.toFixed(3));
        
        let productType = document.querySelector('input[name="product_type"]:checked')?.value;
        if (productType === 'meal' || productType === 'compound') {
            let costRate = parseFloat(document.getElementById('purchase_exchange_rate').value) || 1;
            let purchaseInput = document.getElementById('purchase_price');
            purchaseInput.value = Number((total / costRate).toFixed(3));
            purchaseInput.dataset.baseValue = total; // The 'total' is already in base TRY
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
            document.getElementById('base_sell').required = true;
        } else {
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';
            document.getElementById('base_sell').required = false;
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
            row.querySelector('.unit-sell').required = true;
        } else {
            sellDiv.style.display = 'none';
            marginDiv.style.display = 'none';
            row.querySelector('.unit-sell').required = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleRecipeBuilder();
        toggleSubUnitField();
        toggleCustomUnitInput();
        toggleBaseSaleFields();
        updateAllExchangeDisplays();
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
                    <div class="localized-file-wrapper mt-1">
                        <button type="button" class="btn btn-xs btn-outline-secondary localized-file-btn" style="font-size: 0.7rem; padding: 2px 5px;">
                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                        </button>
                        <div class="localized-file-name text-start" style="font-size: 0.7rem;"> {{ __('لم يتم اختيار ملف') }} </div>
                        <input type="file" name="units[INDEX][image]" accept="image/*" onchange="previewImage(this, 'preview_INDEX'); updateFileName(this)">
                    </div>
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
                            <div class="input-group input-group-sm">
                                <input type="number" step="any" name="units[INDEX][purchase_price]" class="form-control bg-light unit-cost fw-bold text-danger text-center" readonly>
                                <select name="units[INDEX][purchase_price_currency_id]" class="form-select currency-select-unit" onchange="handleCurrencyChangeUnit(this)">
                                    @foreach($acceptedCurrencies as $cur)
                                        <option value="{{ $cur->id }}" data-code="{{ $cur->code }}" data-rate="{{ $currenciesData[$cur->id] ?? 1 }}" {{ $cur->id == $baseCurrency->id ? 'selected' : '' }}>{{ $cur->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="units[INDEX][purchase_exchange_rate]" id="units_INDEX_purchase_rate" value="1">
                            <div class="unit-purchase-exchange-info exchange-info mt-1 small" style="display: none;"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="small fw-bold text-primary">الربح %</label>
                            <input type="number" step="any" name="units[INDEX][profit_percent]" class="form-control form-control-sm unit-profit fw-bold text-primary text-center" oninput="calcExtraUnitSell(this)">
                        </div>

                        <div class="col-md-4">
                            <label class="small text-success fw-bold">سعر البيع</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="any" name="units[INDEX][selling_price]" class="form-control unit-sell fw-bold text-success text-center" oninput="calcExtraUnitProfit(this)">
                                <select name="units[INDEX][sell_price_currency_id]" class="form-select currency-select-unit" onchange="handleCurrencyChangeUnit(this)">
                                    @foreach($acceptedCurrencies as $cur)
                                        <option value="{{ $cur->id }}" data-code="{{ $cur->code }}" data-rate="{{ $currenciesData[$cur->id] ?? 1 }}" {{ $cur->id == $baseCurrency->id ? 'selected' : '' }}>{{ $cur->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="units[INDEX][sell_exchange_rate]" id="units_INDEX_sell_rate" value="1">
                            <div class="unit-sell-exchange-info exchange-info mt-1 small" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>

@endsection
