@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('store.purchases.store') }}" method="POST" id="purchaseForm" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="save_type" value="approved">
        
        <div class="row">
            {{-- رأس الفاتورة --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> فاتورة شراء جديدة</h5>
                        <a href="{{ route('store.purchases.index') }}" class="btn btn-sm btn-light text-primary fw-bold">العودة</a>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">المورد <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSupplierModal" title="مورد جديد"><i class="fas fa-plus"></i></button>
                                        <input type="text" id="supplierSearchInput" class="form-control" placeholder="ابحث عن مورد..." autocomplete="off">
                                        <input type="hidden" name="supplier_id" id="supplierId" required>
                                    </div>
                                    <div id="supplierResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; display: none;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">تاريخ وتوقيت الفاتورة</label>
                                <input type="datetime-local" name="invoice_date" class="form-control custom-date-input" 
                                       value="{{ \Carbon\Carbon::now()->addHours(3)->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">رقم الفاتورة (للمورد)</label>
                                <input type="text" name="invoice_number" class="form-control" placeholder="مثال: INV-1001">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- جدول المنتجات --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="p-3 bg-white border-bottom position-relative">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
                                <input type="text" id="productSearch" class="form-control border-start-0" 
                                       placeholder="ابحث باسم المنتج أو امسح الباركود (استخدم الأسهم ⬇️⬆️)..." autocomplete="off">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickProductModal"><i class="fas fa-plus-circle me-1"></i> منتج جديد</button>
                            </div>
                            <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; top: 100%; display: none;"></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle mb-0" id="itemsTable">
                                <thead class="bg-dark text-white small">
                                    <tr>
                                        <th style="width: 5%">صورة</th>
                                        <th style="width: 20%">المنتج</th>
                                        <th style="width: 12%">الباركود</th>
                                        <th style="width: 10%">الوحدة</th>
                                        <th style="width: 7%">الكمية</th>
                                        <th style="width: 10%">سعر الشراء</th>
                                        <th style="width: 7%">الربح %</th> 
                                        <th style="width: 10%">الخصم</th>
                                        <th style="width: 10%">سعر المبيع</th>
                                        <th style="width: 8%">الضريبة</th>
                                        <th style="width: 10%">الإجمالي</th>
                                        <th style="width: 3%"></th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
                            <div id="emptyState" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-secondary opacity-50"></i>
                                <p>قم بالبحث لإضافة منتجات</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الحسابات والدفع --}}
            <div class="col-lg-5 ms-auto">
                <div class="card shadow border-primary">
                    <div class="card-header bg-primary bg-opacity-10 py-2">
                        <h6 class="mb-0 fw-bold text-primary">ملخص الدفع</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>المجموع الفرعي:</span> <span id="subTotalDisplay" class="fw-bold">0.00</span>
                        </div>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text">خصم إضافي</span>
                            <input type="text" inputmode="decimal" name="discount" id="discountInput" class="form-control text-center fw-bold text-danger" value="0" oninput="calculateGrandTotal()">
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 mb-3">
                            <span class="fs-5 fw-bold">الصافي النهائي:</span>
                            <span id="grandTotalDisplay" class="fs-4 fw-bold text-primary">0.00</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small text-muted mb-1">المدفوعات</label>
                            <div id="paymentsContainer">
                                <div class="input-group mb-2 payment-row">
                                    <select name="payments[0][method]" class="form-select" style="max-width: 120px;">
                                        <option value="cash">💰 نقدي</option>
                                        <option value="card">💳 بطاقة</option>
                                        <option value="bank">🏦 تحويل</option>
                                    </select>
                                    <input type="text" inputmode="decimal" name="payments[0][amount]" class="form-control text-center payment-input" value="0" oninput="calculateGrandTotal()">
                                    <button type="button" class="btn btn-outline-success" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="alert p-2 text-center fw-bold" id="balanceAlert" style="display: none;">
                            <span id="balanceLabel">المتبقي:</span> <span id="balanceAmount">0.00</span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg mt-3"><i class="fas fa-save me-2"></i> حفظ الفاتورة</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- المودالات --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">إضافة مورد جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="quickSupplierForm"><div class="mb-2"><label class="small fw-bold">الاسم *</label><input type="text" id="suppName" name="name" class="form-control" required></div><div class="mb-2"><label class="small">الشركة</label><input type="text" id="suppComp" name="company" class="form-control"></div><button type="submit" class="btn btn-success w-100">حفظ وإضافة</button></form></div></div></div></div>
<div class="modal fade" id="quickProductModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">إضافة منتج سريع</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">قريباً...</div></div></div></div>

@endsection

@section('scripts')
<script>
    // --- المتغيرات العامة ---
    let rowIdx = 0;
    let paymentIdx = 1;
    const storeTaxRates = @json($taxRates); 
    window.productsData = {}; 

    // --- دوال مساعدة ---
    function parseMoney(value) {
        if (!value) return 0;
        let clean = String(value).replace(/[^0-9.]/g, ''); 
        return parseFloat(clean) || 0;
    }

    function formatNum(num) { 
        return parseMoney(num).toFixed(2); 
    }
// 🔥 الحل النهائي بناءً على توصيات تقرير البحث 🔥
    function getExactUnitPrice(unit) {
        // 1. تحويل القيم لأرقام لضمان عدم وجود نصوص
        let cost = parseFloat(unit.cost_price) || 0;
        let purchase = parseFloat(unit.purchase_price) || 0;

        // 2. فحص "الوعي بالسياق" (Context Awareness):
        // إذا قمنا بتنظيف الداتابيس في الخطوة الأولى، سيكون الـ cost والـ purchase متطابقين (4.33).
        // لكن للأمان: التكلفة (cost_price) هي المرجع "المقدس" في نظامك.
        if (cost > 0) {
            return cost;
        }

        // 3. شبكة الأمان الأخيرة
        return purchase;
    }
    function resolveImage(unit, product) {
        if (unit.media && unit.media.length > 0) return unit.media[0].original_url;
        if (unit.image_url) return unit.image_url;
        if (product.image_url) return product.image_url;
        return "{{ asset('images/default-product.png') }}";
    }

// --- دالة إضافة المنتج: قراءة مباشرة (Direct Read) بدون أي عمليات حسابية ---
// --- إضافة صف المنتج (مع تصحيح إجباري لمعامل الوحدة الأساسية) ---
// --- إضافة صف المنتج (المرجعية: أكبر وحدة لتصحيح الأسعار الملوثة) ---
function addProductRow(product) {
    document.getElementById('emptyState').style.display = 'none';
    window.productsData[rowIdx] = product;

    let selectedUnitId = product.scanned_unit_id;
    if (!selectedUnitId) {
        let base = product.units.find(u => u.is_base_unit == 1) || product.units[0];
        selectedUnitId = base.id;
    }

    // 1. كشف "أكبر وحدة" لاتخاذها مرجعية (لتجاوز خطأ سعر القطعة المخزن)
    // نبحث عن الوحدة ذات أكبر معامل تحويل
    let maxUnit = product.units.reduce((prev, curr) => 
        (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr
    );
    
    // سعر الكرتون/الطبق الصحيح
    let maxUnitCost = parseFloat(maxUnit.cost_price);
    if (!maxUnitCost || maxUnitCost === 0) maxUnitCost = parseFloat(maxUnit.purchase_price) || 0;
    
    // معامل الكرتون (مثلاً 30)
    let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
    
    // 🔥 الحقيقة المطلقة: سعر الحبة الواحدة الخام (بدون تقريب) 🔥
    let trueBaseCost = maxUnitCost / maxFactor; 

    // تحديد الوحدة المختارة
    let selectedUnit = product.units.find(u => u.id == selectedUnitId);
    let selectedFactor = parseFloat(selectedUnit.conversion_factor) || 1;
    
    // حساب السعر للوحدة المختارة بناءً على الحقيقة المطلقة
    let calculatedCost = trueBaseCost * selectedFactor;

    let initialBarcode = selectedUnit.barcode || '-';
    let sellPrice = parseFloat(selectedUnit.selling_price) || 0;
    let profitPercent = (calculatedCost > 0 && sellPrice > 0) ? ((sellPrice - calculatedCost) / calculatedCost) * 100 : 0;
    let imgUrl = resolveImage(selectedUnit, product);
    let taxOptionsHtml = storeTaxRates.map(rate => `<option value="${rate}">${rate}%</option>`).join('');

    const tr = document.createElement('tr');
    tr.id = `row_${rowIdx}`;
    tr.className = "align-middle";

    // بناء القائمة: نحسب سعر كل خيار بناءً على (سعر الحبة الخام * المعامل)
    let optionsHtml = product.units.map(u => {
        let factor = parseFloat(u.conversion_factor) || 1;
        let mathPrice = trueBaseCost * factor; // السعر المصحح

        return `<option value="${u.id}" 
                data-barcode="${u.barcode || '-'}" 
                data-price="${mathPrice.toFixed(4)}" 
                data-sell="${formatNum(u.selling_price)}" 
                data-factor="${factor}" 
                ${u.id == selectedUnitId ? 'selected' : ''}>
                ${u.unit_name}
                </option>`;
    }).join('');

    tr.innerHTML = `
        <td>
            <button type="button" class="btn btn-sm btn-info text-white" onclick="toggleDetails(${rowIdx})"><i class="fas fa-chevron-down"></i></button>
            <img src="${imgUrl}" id="img_${rowIdx}" class="img-thumbnail unit-img mt-1" style="width: 40px; height: 40px; object-fit: cover;">
        </td>
        <td class="text-start">
            <input type="hidden" name="items[${rowIdx}][product_id]" value="${product.id}">
            <span class="fw-bold small">${product.name_ar}</span>
        </td>
        <td><input type="text" class="form-control form-control-sm text-center bg-white barcode-display" id="barcode_${rowIdx}" value="${initialBarcode}" readonly></td>
        <td>
            <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm unit-select" onchange="updateRowData(${rowIdx})">
                ${optionsHtml}
            </select>
        </td>
        <td><input type="text" inputmode="decimal" name="items[${rowIdx}][quantity]" class="form-control form-control-sm text-center qty" value="1" oninput="calcTotals(${rowIdx})" onfocus="this.select()"></td>
        
        {{-- عرض السعر بدقة 4 خانات لمنع مشاكل التقريب --}}
        <td><input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price text-danger fw-bold" value="${parseFloat(calculatedCost.toFixed(4))}" oninput="syncSubUnits(${rowIdx})" onfocus="this.select()"></td>

        <td><input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary" value="${formatNum(profitPercent)}" oninput="calcSellPrice(${rowIdx})" onfocus="this.select()"></td>
        
        <td>
            <div class="input-group input-group-sm" style="min-width: 90px;">
                <input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="0" oninput="calcTotals(${rowIdx})" onfocus="this.select()">
                <select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" style="max-width: 40px;" onchange="calcTotals(${rowIdx})"><option value="fixed">₺</option><option value="percent">%</option></select>
            </div>
        </td>
        <td><input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control form-control-sm text-center sell text-success fw-bold" value="${formatNum(sellPrice)}" oninput="calcProfitPercent(${rowIdx})" onfocus="this.select()"></td>
        <td><select name="items[${rowIdx}][tax]" class="form-select form-select-sm tax bg-warning bg-opacity-10" onchange="calcTotals(${rowIdx})">${taxOptionsHtml}</select></td>
        <td><input type="text" class="form-control form-control-sm text-center bg-light fw-bold total" readonly></td>
        <td><button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="removeRow(${rowIdx})"><i class="fas fa-times"></i></button></td>
    `;
    document.getElementById('tableBody').appendChild(tr);

    const detailsTr = document.createElement('tr');
    detailsTr.id = `details_${rowIdx}`;
    detailsTr.style.display = 'none';
    detailsTr.className = "bg-light";
    detailsTr.innerHTML = `<td colspan="12"><div class="p-3 border rounded bg-white"><h6 class="fw-bold text-primary mb-2"><i class="fas fa-sitemap"></i> تحديث أسعار الوحدات المرتبطة تلقائياً</h6><div id="related_units_container_${rowIdx}"></div></div></td>`;
    document.getElementById('tableBody').appendChild(detailsTr);

    renderRelatedUnits(rowIdx, selectedUnitId);
    calcTotals(rowIdx); 
    rowIdx++;
}
// --- دالة التحديث عند تغيير القائمة (صور + حسابات رياضية) ---
   // --- دالة التحديث عند تغيير القائمة (صور + حسابات رياضية) ---
// --- دالة التحديث عند تغيير القائمة ---
function updateRowData(idx) {
    let row = document.getElementById(`row_${idx}`);
    let select = row.querySelector('.unit-select');
    let opt = select.options[select.selectedIndex];
    
    let unitId = opt.value;
    let product = window.productsData[idx];
    let unit = product.units.find(u => u.id == unitId);

    // قراءة السعر من data-price (الذي حسبناه بدقة في addProductRow)
    let price = parseFloat(opt.getAttribute('data-price')) || 0;
    let barcode = opt.getAttribute('data-barcode');
    let sell = parseFloat(opt.getAttribute('data-sell')) || 0;

    row.querySelector('.barcode-display').value = barcode;
    row.querySelector('.price').value = parseFloat(price.toFixed(4)); // استخدام الدقة العالية
    row.querySelector('.sell').value = formatNum(sell);
    
    // تحديث الصورة
    let imgUrl = resolveImage(unit, product);
    let imgTag = document.getElementById(`img_${idx}`);
    if(imgTag) imgTag.src = imgUrl;

    // إعادة بناء الوحدات المرتبطة
    renderRelatedUnits(idx, unitId);
    
    // مزامنة الحسابات
    syncSubUnits(idx);
}
    // --- الوحدات المرتبطة ---
    // --- عرض الوحدات المرتبطة (القسم الأزرق) - قراءة مباشرة بدون حسابات ---
  // --- دالة عرض الوحدات المرتبطة (مع تصحيح الأسعار والصور) ---
function renderRelatedUnits(idx, currentUnitId) {
    let product = window.productsData[idx];
    let container = document.getElementById(`related_units_container_${idx}`);
    container.innerHTML = '';

    // 1. إعادة حساب "سعر الحبة الخام" من أكبر وحدة (المرجعية الصحيحة)
    let maxUnit = product.units.reduce((prev, curr) => 
        (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr
    );
    let maxUnitCost = parseFloat(maxUnit.cost_price);
    if (!maxUnitCost || maxUnitCost === 0) maxUnitCost = parseFloat(maxUnit.purchase_price) || 0;
    let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
    let trueBaseCost = maxUnitCost / maxFactor; // السعر الخام (4.33333)

    let html = '';
    product.units.forEach(u => {
        if (u.id == currentUnitId) return; 

        // 2. حساب السعر لهذه الوحدة (بدلاً من قراءته من الداتابيس الملوثة)
        let factor = parseFloat(u.conversion_factor) || 1;
        let calculatedCost = trueBaseCost * factor; // 4.33 للبيضة، 65 للنص طبق

        let uSell = parseFloat(u.selling_price) || 0;
        let uProfit = (calculatedCost > 0 && uSell > 0) ? ((uSell - calculatedCost) / calculatedCost) * 100 : 0;
        
        // 3. الصورة: نمرر الوحدة الحالية `u` وليس المنتج العام
        let uImg = resolveImage(u, product); 

        html += `
            <div class="row g-2 align-items-center mb-2 related-unit-row border-bottom pb-2" 
                 data-unit-id="${u.id}" data-factor="${factor}">
                
                {{-- حقل مخفي للحفظ --}}
                <input type="hidden" name="items[${idx}][related_prices][${u.id}][unit_id]" value="${u.id}">

                <div class="col-md-2 d-flex align-items-center">
                    <img src="${uImg}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                    <div>
                        <span class="badge bg-secondary">${u.unit_name}</span>
                        <small class="d-block text-muted" style="font-size: 0.75rem;">(x${factor})</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted">شراء</span>
                        {{-- هنا نضع السعر المحسوب Calculated Cost وليس المخزن --}}
                        <input type="text" name="items[${idx}][related_prices][${u.id}][purchase_price]" 
                               class="form-control text-center bg-light sub-cost text-danger fw-bold" 
                               value="${parseFloat(calculatedCost.toFixed(4))}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">ربح %</span>
                        <input type="text" inputmode="decimal" class="form-control text-center sub-profit" value="${formatNum(uProfit)}" oninput="calcSubUnitSell(this)" onfocus="this.select()">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">بيع</span>
                        <input type="text" name="items[${idx}][related_prices][${u.id}][selling_price]" 
                               inputmode="decimal" class="form-control text-center fw-bold sub-sell text-success" 
                               value="${formatNum(uSell)}" oninput="calcSubUnitProfit(this)" onfocus="this.select()">
                    </div>
                    <div class="text-danger small mt-1 warning-msg fw-bold" style="display:${uProfit <= 0 ? 'block' : 'none'};">⚠️ ربح منخفض!</div>
                </div>
            </div>`;
    });
    container.innerHTML = html;
}
    function syncSubUnits(idx) {
        calcTotals(idx);
        let row = document.getElementById(`row_${idx}`);
        
        // السعر المكتوب حالياً في الخانة
        let mainPrice = parseMoney(row.querySelector('.price').value); 
        
        let select = row.querySelector('.unit-select');
        let selectedOption = select.options[select.selectedIndex];
        let currentFactor = parseFloat(selectedOption.getAttribute('data-factor')) || 1;

        // حساب سعر الحبة الواحدة بناءً على ما هو مكتوب
        let pricePerPiece = 0;
        if (currentFactor > 0) {
            pricePerPiece = mainPrice / currentFactor;
        }

        // تحديث نسبة الربح للسطر الرئيسي
        let mainSell = parseMoney(row.querySelector('.sell').value);
        if (mainPrice > 0) {
            let newMainProfit = ((mainSell - mainPrice) / mainPrice) * 100;
            row.querySelector('.profit').value = formatNum(newMainProfit);
        }

        // تحديث القسم الأزرق (ضرب سعر الحبة × معامل كل وحدة)
        let container = document.getElementById(`related_units_container_${idx}`);
        if(container) {
            container.querySelectorAll('.related-unit-row').forEach(subRow => {
                let subFactor = parseFloat(subRow.getAttribute('data-factor')) || 1;
                
                // المعادلة: سعر الحبة × عدد قطع الوحدة
                let newSubCost = pricePerPiece * subFactor;
                
                // وضع السعر الجديد في الحقل (الذي سيتم حفظه الآن بسبب وجود name)
                subRow.querySelector('.sub-cost').value = formatNum(newSubCost);
                
                // تحديث نسب الربح الفرعية
                let currentSubSell = parseMoney(subRow.querySelector('.sub-sell').value);
                let newSubProfit = 0;
                if (newSubCost > 0) {
                    newSubProfit = ((currentSubSell - newSubCost) / newSubCost) * 100;
                }
                subRow.querySelector('.sub-profit').value = formatNum(newSubProfit);
                
                let warning = subRow.querySelector('.warning-msg');
                if(warning) warning.style.display = (newSubProfit <= 0) ? 'block' : 'none';
            });
        }
    }
    // --- حسابات عامة ---
    function calcTotals(idx) {
        let row = document.getElementById(`row_${idx}`);
        if(!row) return;
        let qty = parseMoney(row.querySelector('.qty').value);
        let price = parseMoney(row.querySelector('.price').value);
        let taxRate = parseMoney(row.querySelector('.tax').value);
        let discountVal = parseMoney(row.querySelector('.discount').value);
        let discountType = row.querySelector('.discount-type').value;

        let subTotal = qty * price;
        let discountAmount = (discountType === 'percent') ? (subTotal * discountVal / 100) : discountVal;
        let afterDiscount = subTotal - discountAmount;
        let taxAmount = afterDiscount * (taxRate / 100);
        let finalTotal = afterDiscount + taxAmount;
        row.querySelector('.total').value = formatNum(finalTotal);
        calculateGrandTotal();
    }

    function calcProfitPercent(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseMoney(row.querySelector('.price').value);
        let sell = parseMoney(row.querySelector('.sell').value);
        if(price > 0) {
            let profit = ((sell - price) / price) * 100;
            row.querySelector('.profit').value = formatNum(profit);
        }
    }

    function calcSellPrice(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseMoney(row.querySelector('.price').value);
        let profit = parseMoney(row.querySelector('.profit').value);
        let sell = price * (1 + profit / 100);
        row.querySelector('.sell').value = formatNum(sell);
    }
    
    function calcSubUnitSell(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseMoney(row.querySelector('.sub-cost').value);
        let profit = parseMoney(input.value);
        let sell = cost * (1 + profit / 100);
        row.querySelector('.sub-sell').value = formatNum(sell);
        let warning = row.querySelector('.warning-msg');
        warning.style.display = (profit <= 0) ? 'block' : 'none';
    }

    function calcSubUnitProfit(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseMoney(row.querySelector('.sub-cost').value);
        let sell = parseMoney(input.value);
        if (cost > 0) {
            let profit = ((sell - cost) / cost) * 100;
            row.querySelector('.sub-profit').value = formatNum(profit);
            let warning = row.querySelector('.warning-msg');
            warning.style.display = (profit <= 0) ? 'block' : 'none';
        }
    }

    function removeRow(idx) {
        let row = document.getElementById(`row_${idx}`);
        let details = document.getElementById(`details_${idx}`);
        if (row) row.remove();
        if (details) details.remove();
        delete window.productsData[idx];
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let subTotal = 0;
        document.querySelectorAll('.total').forEach(el => subTotal += parseMoney(el.value));
        document.getElementById('subTotalDisplay').innerText = formatNum(subTotal); 
        let discount = parseMoney(document.getElementById('discountInput').value);
        let grandTotal = subTotal - discount;
        document.getElementById('grandTotalDisplay').innerText = formatNum(grandTotal);
        let totalPaid = 0;
        document.querySelectorAll('.payment-input').forEach(inp => totalPaid += parseMoney(inp.value));
        const diff = parseFloat((totalPaid - grandTotal).toFixed(2));
        const balDiv = document.getElementById('balanceAlert');
        const balLbl = document.getElementById('balanceLabel');
        const balAmt = document.getElementById('balanceAmount');
        balDiv.style.display = 'block';
        if (Math.abs(diff) < 0.01) {
            balDiv.className = 'alert p-2 text-center fw-bold alert-success';
            balLbl.innerText = 'خالص (تم الدفع بالكامل)';
            balAmt.innerText = '';
        } else if (diff < 0) {
            balDiv.className = 'alert p-2 text-center fw-bold alert-danger';
            balLbl.innerText = 'متبقي (عليك):';
            balAmt.innerText = formatNum(Math.abs(diff));
        } else {
            balDiv.className = 'alert p-2 text-center fw-bold alert-info';
            balLbl.innerText = 'رصيد (لك):';
            balAmt.innerText = formatNum(diff);
        }
    }

    function addPaymentRow() {
        let grandTotal = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
        let currentPaid = 0;
        document.querySelectorAll('.payment-input').forEach(inp => currentPaid += parseMoney(inp.value));
        let remaining = grandTotal - currentPaid;
        let defaultVal = remaining > 0 ? formatNum(remaining) : 0;
        const div = document.createElement('div');
        div.className = 'input-group mb-2 payment-row';
        div.innerHTML = `
            <select name="payments[${paymentIdx}][method]" class="form-select" style="max-width: 120px;">
                <option value="cash">💰 نقدي</option>
                <option value="card">💳 بطاقة</option>
                <option value="bank">🏦 تحويل</option>
            </select>
            <input type="text" inputmode="decimal" name="payments[${paymentIdx}][amount]" class="form-control text-center payment-input" value="${defaultVal}" oninput="calculateGrandTotal()" onfocus="this.select()">
            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove(); calculateGrandTotal();"><i class="fas fa-trash"></i></button>
        `;
        document.getElementById('paymentsContainer').appendChild(div);
        paymentIdx++;
        calculateGrandTotal();
    }

    function toggleDetails(idx) {
        let row = document.getElementById(`details_${idx}`);
        if (row.style.display === 'none') row.style.display = 'table-row';
        else row.style.display = 'none';
    }

    function setupSearch(inputId, resultsId, url, onSelect) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        let debounce;
        input.addEventListener('input', function() {
            clearTimeout(debounce);
            const term = this.value.trim();
            if(term.length < 1) { results.style.display='none'; return; }
            debounce = setTimeout(() => {
                fetch(`${url}?term=${term}`).then(r => r.json()).then(data => {
                    results.innerHTML = '';
                    if (data.length === 1) { onSelect(data[0]); results.style.display = 'none'; return; }
                    if (data.length > 0) {
                        data.forEach((item, index) => {
                            let div = document.createElement('a');
                            div.className = 'list-group-item list-group-item-action cursor-pointer';
                            div.innerHTML = item.contact_name ? `<strong>${item.contact_name}</strong>` : `<div class="d-flex justify-content-between"><span>${item.name_ar}</span> <small class="text-muted">${item.sku || ''}</small></div>`;
                            div.onclick = function() { onSelect(item); results.style.display = 'none'; };
                            results.appendChild(div);
                        });
                        results.style.display = 'block';
                    } else { results.style.display = 'none'; }
                });
            }, 250);
        });
        document.addEventListener("click", function (e) { if (e.target !== input && e.target !== results) results.style.display = 'none'; });
    }

    document.addEventListener("DOMContentLoaded", function() {
        setupSearch('supplierSearchInput', 'supplierResults', '/store-owner/contacts/search', function(s) {
            document.getElementById('supplierSearchInput').value = s.contact_name;
            document.getElementById('supplierId').value = s.id;
        });
        setupSearch('productSearch', 'searchResults', '/store-owner/products/search', function(p) {
            addProductRow(p);
            document.getElementById('productSearch').value = ''; 
            document.getElementById('productSearch').focus();
        });
    });
</script>
@endsection