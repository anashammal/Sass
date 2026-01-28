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

    // 🔥🔥🔥 الدالة الحاسمة: تحديد السعر بناءً على نوع الوحدة بدقة تامة 🔥🔥🔥
    function getExactUnitPrice(unit) {
        let factor = parseFloat(unit.conversion_factor) || 1;
        let purchase = parseFloat(unit.purchase_price) || 0;
        let cost = parseFloat(unit.cost_price) || 0;

        // 1. الوحدة الأساسية (قطعة، معامل 1): نأخذ cost_price
        if (factor === 1) {
            return cost > 0 ? cost : purchase;
        }
        
        // 2. الوحدة الإضافية (كرتون، معامل > 1):
        // هنا يجب أن نحصل على سعر الكرتون الإجمالي. نأخذ الأكبر بين purchase و cost
        // لأن cost_price قد يحتوي سعر القطعة فقط بالخطأ.
        return Math.max(purchase, cost); 
    }

    function resolveImage(unit, product) {
        if (unit.media && unit.media.length > 0) return unit.media[0].original_url;
        if (unit.image_url) return unit.image_url;
        if (product.image_url) return product.image_url;
        return "{{ asset('images/default-product.png') }}";
    }

    // --- إضافة صف المنتج ---
    function addProductRow(product) {
        document.getElementById('emptyState').style.display = 'none';
        window.productsData[rowIdx] = product;

        let selectedUnitId = product.scanned_unit_id; 
        if (!selectedUnitId) {
            let base = product.units.find(u => u.is_base_unit == 1) || product.units[0];
            selectedUnitId = base.id;
        }

        let selectedUnit = product.units.find(u => u.id == selectedUnitId);
        let initialBarcode = selectedUnit.barcode || '-';
        
        // 🔥 استخدام الدالة الحاسمة لجلب السعر الصحيح للوحدة المختارة 🔥
        let price = getExactUnitPrice(selectedUnit);
        
        let sellPrice = parseMoney(selectedUnit.selling_price);
        let profitPercent = (price > 0 && sellPrice > 0) ? ((sellPrice - price) / price) * 100 : 0;
        let imgUrl = resolveImage(selectedUnit, product);
        let taxOptionsHtml = storeTaxRates.map(rate => `<option value="${rate}">${rate}%</option>`).join('');

        const tr = document.createElement('tr');
        tr.id = `row_${rowIdx}`;
        tr.className = "align-middle";

        // 🔥 بناء القائمة: حساب السعر لكل خيار بشكل منفصل ومستقل 🔥
        let optionsHtml = product.units.map(u => {
            let uPrice = getExactUnitPrice(u); 
            
            return `<option value="${u.id}" 
                    data-barcode="${u.barcode || '-'}" 
                    data-price="${formatNum(uPrice)}" 
                    data-sell="${formatNum(u.selling_price)}" 
                    data-factor="${u.conversion_factor}"
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
            
            {{-- سعر الشراء: الآن سيأخذ القيمة الصحيحة (34.65 للقطعة و 1039 للكرتون) --}}
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price text-danger fw-bold" value="${formatNum(price)}" oninput="syncSubUnits(${rowIdx})" onfocus="this.select()"></td>

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

        // صف التفاصيل
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

    // --- تحديث عند تغيير الوحدة ---
    function updateRowData(idx) {
        let row = document.getElementById(`row_${idx}`);
        let select = row.querySelector('.unit-select');
        let opt = select.options[select.selectedIndex];
        let product = window.productsData[idx];
        let newUnitId = select.value;

        // القراءة المباشرة من data-price (الذي حسبناه بدقة في getExactUnitPrice)
        let price = parseMoney(opt.getAttribute('data-price'));
        let sell = parseMoney(opt.getAttribute('data-sell'));
        let barcode = opt.getAttribute('data-barcode');
        
        let profit = (price > 0 && sell > 0) ? ((sell - price) / price) * 100 : 0;

        row.querySelector('.price').value = formatNum(price);
        row.querySelector('.sell').value = formatNum(sell);
        row.querySelector('.profit').value = formatNum(profit);
        document.getElementById(`barcode_${idx}`).value = barcode || '-';
        
        let newUnit = product.units.find(u => u.id == newUnitId);
        if(newUnit) document.getElementById(`img_${idx}`).src = resolveImage(newUnit, product);

        renderRelatedUnits(idx, newUnitId);
        calcTotals(idx);
    }

    // --- الوحدات المرتبطة ---
    function renderRelatedUnits(idx, currentUnitId) {
        let product = window.productsData[idx];
        let container = document.getElementById(`related_units_container_${idx}`);
        container.innerHTML = '';

        let row = document.getElementById(`row_${idx}`);
        let currentPrice = parseMoney(row.querySelector('.price').value);
        let currentUnit = product.units.find(u => u.id == currentUnitId);
        let currentFactor = parseFloat(currentUnit.conversion_factor) || 1;
        
        // حساب سعر القطعة الواحدة (الأساس) لتحديث بقية الوحدات
        let baseUnitCost = (currentFactor > 0) ? (currentPrice / currentFactor) : 0;

        let html = '';
        product.units.forEach(u => {
            if (u.id == currentUnitId) return; 
            let factor = parseFloat(u.conversion_factor) || 1;
            // حساب تكلفة الوحدة الأخرى بناءً على سعر الوحدة الحالية
            let calculatedCost = baseUnitCost * factor;
            let uSell = parseMoney(u.selling_price);
            let uProfit = (calculatedCost > 0 && uSell > 0) ? ((uSell - calculatedCost) / calculatedCost) * 100 : 0;
            let uImg = resolveImage(u, product);

            html += `
                <div class="row g-2 align-items-center mb-2 related-unit-row border-bottom pb-2" data-unit-id="${u.id}" data-factor="${factor}">
                    <div class="col-md-2 d-flex align-items-center"><img src="${uImg}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;"><div><span class="badge bg-secondary">${u.unit_name}</span><small class="d-block text-muted" style="font-size: 0.75rem;">(x${factor})</small></div></div>
                    <div class="col-md-3"><div class="input-group input-group-sm"><span class="input-group-text bg-light text-muted">شراء</span><input type="text" class="form-control text-center bg-light sub-cost text-danger fw-bold" value="${formatNum(calculatedCost)}" readonly></div></div>
                    <div class="col-md-3"><div class="input-group input-group-sm"><span class="input-group-text">ربح %</span><input type="text" inputmode="decimal" class="form-control text-center sub-profit" value="${formatNum(uProfit)}" oninput="calcSubUnitSell(this)" onfocus="this.select()"></div></div>
                    <div class="col-md-4"><div class="input-group input-group-sm"><span class="input-group-text">بيع</span><input type="text" inputmode="decimal" class="form-control text-center fw-bold sub-sell text-success" value="${formatNum(uSell)}" oninput="calcSubUnitProfit(this)" onfocus="this.select()"></div><div class="text-danger small mt-1 warning-msg fw-bold" style="display:${uProfit <= 0 ? 'block' : 'none'};">⚠️ ربح منخفض!</div></div>
                </div>`;
        });
        container.innerHTML = html;
    }

    // --- مزامنة عند التعديل اليدوي ---
    function syncSubUnits(idx) {
        calcTotals(idx);
        let row = document.getElementById(`row_${idx}`);
        let mainPrice = parseMoney(row.querySelector('.price').value);
        let mainSell  = parseMoney(row.querySelector('.sell').value);
        if (mainPrice > 0) {
            let newMainProfit = ((mainSell - mainPrice) / mainPrice) * 100;
            row.querySelector('.profit').value = formatNum(newMainProfit);
        }
        let select = row.querySelector('.unit-select');
        let currentUnitId = select.value;
        let product = window.productsData[idx];
        let currentUnit = product.units.find(u => u.id == currentUnitId);
        let currentFactor = parseFloat(currentUnit.conversion_factor) || 1;
        let baseCost = (currentFactor > 0) ? (mainPrice / currentFactor) : 0;

        let container = document.getElementById(`related_units_container_${idx}`);
        if(container) {
            container.querySelectorAll('.related-unit-row').forEach(subRow => {
                let subFactor = parseFloat(subRow.getAttribute('data-factor'));
                let newSubCost = baseCost * subFactor;
                subRow.querySelector('.sub-cost').value = formatNum(newSubCost);
                
                let currentSubSell = parseMoney(subRow.querySelector('.sub-sell').value);
                let newSubProfit = 0;
                if (newSubCost > 0) {
                    newSubProfit = ((currentSubSell - newSubCost) / newSubCost) * 100;
                }
                subRow.querySelector('.sub-profit').value = formatNum(newSubProfit);
                let warning = subRow.querySelector('.warning-msg');
                warning.style.display = (newSubProfit <= 0) ? 'block' : 'none';
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