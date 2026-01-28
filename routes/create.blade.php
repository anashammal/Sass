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
                                        <th style="width: 15%">صورة</th>
                                        <th style="width: 10%">المنتج</th>
                                        <th style="width: 10%">الباركود</th>
                                        <th style="width: 12%">الوحدة</th>
                                        <th style="width: 5%">الكمية</th>
                                        <th style="width: 10%">سعر الشراء</th>
                                        <th style="width: 7%">الربح %</th> 
                                        <th style="width: 7%">الخصم</th>
                                        <th style="width: 10%">سعر المبيع</th>
                                        <th style="width: 8%">الضريبة</th>
                                        <th style="width: 15%">الإجمالي</th>
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

    // --- عند تحميل الصفحة ---
    document.addEventListener("DOMContentLoaded", function() {
        // إعداد بحث الموردين
        setupSearch('supplierSearchInput', 'supplierResults', "{{ url('/store-owner/contacts/search') }}", function(s) {
            document.getElementById('supplierSearchInput').value = s.contact_name;
            document.getElementById('supplierId').value = s.id;
        });

        // إعداد بحث المنتجات
        setupSearch('productSearch', 'searchResults', "{{ url('/store-owner/products/search') }}", function(p) {
            addProductRow(p);
            // تفريغ الحقل وإعادة التركيز عليه لإضافة منتج آخر بسرعة
            let input = document.getElementById('productSearch');
            input.value = ''; 
            input.focus();
        });
    });

    // --- دوال مساعدة ---
    function parseMoney(value) {
        if (!value) return 0;
        let clean = String(value).replace(/[^0-9.]/g, ''); 
        return parseFloat(clean) || 0;
    }

    function formatNum(num) { 
        return parseMoney(num).toFixed(2); 
    }

    function resolveImage(unit, product) {
        if (unit.media && unit.media.length > 0) return unit.media[0].original_url;
        if (unit.image_url) return unit.image_url;
        if (product.image_url) return product.image_url;
        return "{{ asset('images/default-product.png') }}";
    }

    // --- دالة إضافة صف المنتج ---
    function addProductRow(product) {
        document.getElementById('emptyState').style.display = 'none';
        window.productsData[rowIdx] = product;

        let selectedUnitId = product.scanned_unit_id;
        if (!selectedUnitId) {
            let base = product.units.find(u => u.is_base_unit == 1) || product.units[0];
            selectedUnitId = base.id;
        }

        // حساب السعر الخام من أكبر وحدة (لتصحيح أخطاء الداتابيس)
        let maxUnit = product.units.reduce((prev, curr) => 
            (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr
        );
        let maxUnitCost = parseFloat(maxUnit.cost_price);
        if (!maxUnitCost || maxUnitCost === 0) maxUnitCost = parseFloat(maxUnit.purchase_price) || 0;
        let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
        let trueBaseCost = maxUnitCost / maxFactor; 

        // الوحدة المختارة
        let selectedUnit = product.units.find(u => u.id == selectedUnitId);
        let isSelectedBase = (selectedUnit.is_base_unit == 1 || selectedUnit.is_base_unit === true);
        let selectedFactor = isSelectedBase ? 1 : (parseFloat(selectedUnit.conversion_factor) || 1);
        let calculatedCost = trueBaseCost * selectedFactor;

        let initialBarcode = selectedUnit.barcode || '-';
        let sellPrice = parseFloat(selectedUnit.selling_price) || 0;
        let profitPercent = (calculatedCost > 0 && sellPrice > 0) ? ((sellPrice - calculatedCost) / calculatedCost) * 100 : 0;
        let imgUrl = resolveImage(selectedUnit, product);
        let taxOptionsHtml = storeTaxRates.map(rate => `<option value="${rate}">${rate}%</option>`).join('');

        const tr = document.createElement('tr');
        tr.id = `row_${rowIdx}`;
        tr.className = "align-middle";

        let optionsHtml = product.units.map(u => {
            let isBase = (u.is_base_unit == 1 || u.is_base_unit === true);
            let safeFactor = isBase ? 1 : (parseFloat(u.conversion_factor) || 1);
            let mathPrice = trueBaseCost * safeFactor;

            return `<option value="${u.id}" 
                    data-barcode="${u.barcode || '-'}" 
                    data-price="${mathPrice.toFixed(4)}" 
                    data-sell="${formatNum(u.selling_price)}" 
                    data-factor="${safeFactor}" 
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
        syncSubUnits(rowIdx); 
        rowIdx++;
    }

    // --- دالة التحديث عند تغيير القائمة ---
    function updateRowData(idx) {
        let row = document.getElementById(`row_${idx}`);
        let select = row.querySelector('.unit-select');
        let opt = select.options[select.selectedIndex];
        
        let unitId = opt.value;
        let product = window.productsData[idx];
        let unit = product.units.find(u => u.id == unitId);

        let price = parseFloat(opt.getAttribute('data-price')) || 0;
        let barcode = opt.getAttribute('data-barcode');
        let sell = parseFloat(opt.getAttribute('data-sell')) || 0;

        row.querySelector('.barcode-display').value = barcode;
        row.querySelector('.price').value = parseFloat(price.toFixed(4)); 
        row.querySelector('.sell').value = formatNum(sell);
        
        let imgUrl = resolveImage(unit, product);
        let imgTag = document.getElementById(`img_${idx}`);
        if(imgTag) imgTag.src = imgUrl;

        renderRelatedUnits(idx, unitId);
        syncSubUnits(idx);
    }

    // --- دالة عرض الوحدات المرتبطة ---
    function renderRelatedUnits(idx, currentUnitId) {
        let product = window.productsData[idx];
        let container = document.getElementById(`related_units_container_${idx}`);
        container.innerHTML = '';

        let maxUnit = product.units.reduce((prev, curr) => 
            (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr
        );
        let maxUnitCost = parseFloat(maxUnit.cost_price);
        if (!maxUnitCost || maxUnitCost === 0) maxUnitCost = parseFloat(maxUnit.purchase_price) || 0;
        let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
        let trueBaseCost = maxUnitCost / maxFactor; 

        let html = '';
        product.units.forEach(u => {
            if (u.id == currentUnitId) return; 

            let isBase = (u.is_base_unit == 1 || u.is_base_unit === true);
            let safeFactor = isBase ? 1 : (parseFloat(u.conversion_factor) || 1);
            let calculatedCost = trueBaseCost * safeFactor; 
            
            let uSell = parseFloat(u.selling_price) || 0;
            let uImg = resolveImage(u, product);

            html += `
                <div class="row g-2 align-items-center mb-2 related-unit-row border-bottom pb-2" 
                     data-unit-id="${u.id}" data-factor="${safeFactor}">
                    
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][price]" class="hidden-sub-cost" value="${calculatedCost.toFixed(4)}">
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][selling_price]" class="hidden-sub-sell" value="${uSell}">

                    <div class="col-md-2 d-flex align-items-center">
                        <img src="${uImg}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <div>
                            <span class="badge bg-secondary">${u.unit_name}</span>
                            <small class="d-block text-muted" style="font-size: 0.75rem;">(x${safeFactor})</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted">شراء</span>
                            <input type="text" class="form-control text-center bg-light sub-cost text-danger fw-bold" 
                                   value="${formatNum(calculatedCost)}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">ربح %</span>
                            <input type="text" inputmode="decimal" class="form-control text-center sub-profit" value="0" oninput="calcSubUnitSell(this)" onfocus="this.select()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">بيع</span>
                            <input type="text" inputmode="decimal" class="form-control text-center fw-bold sub-sell text-success" 
                                   value="${formatNum(uSell)}" oninput="calcSubUnitProfit(this)" onfocus="this.select()">
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    }

    // --- دالة المزامنة الرياضية ---
    function syncSubUnits(idx) {
        calcTotals(idx);
        let row = document.getElementById(`row_${idx}`);
        
        let mainPrice = parseFloat(row.querySelector('.price').value) || 0; 
        let mainSell  = parseFloat(row.querySelector('.sell').value) || 0; 

        let select = row.querySelector('.unit-select');
        let selectedOption = select.options[select.selectedIndex];
        let currentFactor = parseFloat(selectedOption.getAttribute('data-factor')) || 1;

        let costPerPiece = 0;
        let sellPerPiece = 0;
        
        if (currentFactor > 0) {
            costPerPiece = mainPrice / currentFactor;
            sellPerPiece = mainSell / currentFactor;
        }

        let container = document.getElementById(`related_units_container_${idx}`);
        if(container) {
            container.querySelectorAll('.related-unit-row').forEach(subRow => {
                let subFactor = parseFloat(subRow.getAttribute('data-factor')) || 1;
                
                let newSubCost = costPerPiece * subFactor;
                let newSubSell = sellPerPiece * subFactor; 
                
                subRow.querySelector('.sub-cost').value = formatNum(newSubCost);
                subRow.querySelector('.sub-sell').value = formatNum(newSubSell);
                
                let hiddenCost = subRow.querySelector('.hidden-sub-cost');
                if(hiddenCost) hiddenCost.value = newSubCost.toFixed(4);
                
                let hiddenSell = subRow.querySelector('.hidden-sub-sell');
                if(hiddenSell) hiddenSell.value = newSubSell.toFixed(2);

                let newSubProfit = 0;
                if (newSubCost > 0) {
                    newSubProfit = ((newSubSell - newSubCost) / newSubCost) * 100;
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
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let profit = parseFloat(input.value) || 0;
        
        let sell = cost * (1 + profit / 100);
        row.querySelector('.sub-sell').value = formatNum(sell);
        
        let hiddenSell = row.querySelector('.hidden-sub-sell');
        if(hiddenSell) hiddenSell.value = sell.toFixed(2);
    }

    function calcSubUnitProfit(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let sell = parseFloat(input.value) || 0;

        let hiddenSell = row.querySelector('.hidden-sub-sell');
        if(hiddenSell) hiddenSell.value = sell;

        if (cost > 0) {
            let profit = ((sell - cost) / cost) * 100;
            row.querySelector('.sub-profit').value = formatNum(profit);
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

    // --- إعداد البحث المتطور (النسخة النهائية) ---
    function setupSearch(inputId, resultsId, url, onSelect) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        let debounce;
        let currentFocus = -1;

        function addActive(items) {
            if (!items) return false;
            removeActive(items);
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (items.length - 1);
            items[currentFocus].classList.add("active");
            items[currentFocus].scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }

        function removeActive(items) {
            for (let i = 0; i < items.length; i++) {
                items[i].classList.remove("active");
            }
        }

        input.addEventListener("keydown", function(e) {
            let items = results.getElementsByTagName("a");
            if (e.key === "ArrowDown") {
                currentFocus++;
                addActive(items);
            } else if (e.key === "ArrowUp") {
                currentFocus--;
                addActive(items);
            } else if (e.key === "Enter") {
                e.preventDefault(); 
                if (currentFocus > -1) {
                    if (items[currentFocus]) items[currentFocus].click();
                } else if (items.length === 1) {
                    items[0].click();
                }
            }
        });

        input.addEventListener('input', function() {
            clearTimeout(debounce);
            const term = input.value.trim();
            currentFocus = -1;

            if(term.length < 1) { 
                results.style.display = 'none'; 
                return; 
            }

            debounce = setTimeout(() => {
                // استخدام console.log لمعرفة هل يتم استدعاء البحث
                console.log("Searching for:", term, "at URL:", url);
                
                fetch(`${url}?term=${term}`)
                    .then(r => {
                        if (!r.ok) throw new Error("Server Error: " + r.status);
                        return r.json();
                    })
                    .then(data => {
                        results.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach((item, index) => {
                                let a = document.createElement('a');
                                a.className = 'list-group-item list-group-item-action cursor-pointer';
                                a.href = "#"; 
                                
                                if (item.contact_name) {
                                    a.innerHTML = `<strong>${item.contact_name}</strong>`;
                                } else {
                                    a.innerHTML = `<div class="d-flex justify-content-between"><span>${item.name_ar}</span> <small class="text-muted">${item.sku || ''}</small></div>`;
                                }

                                a.addEventListener("click", function(e) {
                                    e.preventDefault();
                                    onSelect(item); 
                                    results.style.display = 'none';
                                });

                                a.addEventListener("mouseover", function() {
                                    currentFocus = index;
                                    removeActive(results.getElementsByTagName("a"));
                                    this.classList.add("active");
                                });

                                results.appendChild(a);
                            });
                            results.style.display = 'block';
                        } else {
                            results.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error("خطأ في البحث:", error);
                        results.style.display = 'none';
                    });
            }, 250);
        });

        document.addEventListener("click", function(e) {
            if (e.target !== input && !results.contains(e.target)) {
                results.style.display = 'none';
            }
        });
    }
</script>
@endsection