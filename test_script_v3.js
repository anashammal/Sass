
    // --- المتغيرات العامة ---
    let rowIdx = 1;
    let paymentIdx = 1;
    const storeTaxRates = []; 
    const oldItems = []; 
    window.productsData = {}; 

    // خريطة أسعار الصرف
    const preloadedRates = [];
    const ratesMap = {};
    if (Array.isArray(preloadedRates)) {
        preloadedRates.forEach(r => ratesMap[r.id] = r);
    }

    // Currency setup
    const defaultCurrencyCode = "1";
    const baseCurrencyId = "1";
    const baseCurrencyCode = "1";

    // Current Locale for JS
    const CURRENT_LOCALE = "1";

    function parseMoney(value) {
        if (!value) return 0;
        let clean = String(value).replace(/[^0-9.]/g, ''); 
        return parseFloat(clean) || 0;
    }

    // Localization helper
    const LANG = {
        buy: "1",
        profit_percent: "1",
        sell: "1",
        loss: "1",
        low_profit: "1",
        credit_for_you: "1",
        paid_settled: "1",
        remaining_due: "1",
        updated_related_units: "1"
    };

// --- دوال التنسيق والحسابات ---
function formatNum(num) { 
    if (num === null || num === undefined || num === '') return 0;
    let val = parseFloat(num) || 0;
    return parseFloat(val.toFixed(4)); 
}

function onInvoiceCurrencyChange(select) {
    let opt = select.options[select.selectedIndex];
    let currId = select.value;
    let currCode = opt.dataset.code;
    let baseCurrId = "1";
    let baseCurrCode = "1";

    // Update labels in UI (Priority: Symbol > Code)
    let label = opt.dataset.symbol || currCode;
    document.querySelectorAll('.currency-label').forEach(el => el.innerText = label);

    if (currId == baseCurrId) {
        document.getElementById('invoice_exchange_rate').value = 1;
        document.getElementById('invoice_rate_info').classList.add('d-none');
        recalculateAllRows();
        calculateGrandTotal();

        // إعادة مزامنة الدفعة للعملة الأساسية
        let payCurr = document.getElementById('pay_curr_id_0');
        let payRate = document.getElementById('pay_rate_0');
        if (payCurr) payCurr.value = baseCurrId;
        if (payRate) payRate.value = 1;
        return;
    }

    let currData = ratesMap[currId];
    let suggestedRate = currData ? parseFloat(currData.exchange_rate).toFixed(6) : '1.000000';

    Swal.fire({
        title: `💱 سعر صرف الفاتورة: ${currCode} ↔ ${baseCurrCode}`,
        icon: 'info',
        html: `
            <div class="text-start mb-3">
                <label class="form-label fw-bold">✏️ سعر صرف (1 ${currCode} = ؟ ${baseCurrCode}):</label>
                <div class="input-group">
                    <span class="input-group-text bg-primary text-white fw-bold">1 ${currCode}</span>
                    <input type="text" inputmode="decimal" id="swalInvoiceRate" class="form-control text-center fw-bold fs-5" value="${suggestedRate}">
                    <span class="input-group-text fw-bold">${baseCurrCode}</span>
                </div>
            </div>
        `,
        confirmButtonText: '✅ تأكيد',
        showCancelButton: true,
        cancelButtonText: '❌ إلغاء',
        preConfirm: () => {
            let val = parseFloat(document.getElementById('swalInvoiceRate').value);
            if (!val || val <= 0) {
                Swal.showValidationMessage('⚠️ يرجى إدخال سعر صرف صحيح');
                return false;
            }
            return val;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let newRate = result.value;
            document.getElementById('invoice_exchange_rate').value = newRate;
            document.getElementById('selected_curr_code').innerText = currCode;
            document.getElementById('selected_curr_rate').innerText = newRate;
            document.getElementById('invoice_rate_info').classList.remove('d-none');
            
            recalculateAllRows();
            calculateGrandTotal();

            // مزامنة الدفعة الأولى مع عملة الفاتورة
            let payCurr = document.getElementById('pay_curr_id_0');
            let payRate = document.getElementById('pay_rate_0');
            if (payCurr) payCurr.value = currId;
            if (payRate) payRate.value = newRate;
            
        } else {
            // Revert back
            select.value = prevCurrencyId;
            let revertedCode = select.options[select.selectedIndex].dataset.code;
            let revertedLabel = select.options[select.selectedIndex].dataset.symbol || revertedCode;
            document.querySelectorAll('.currency-label').forEach(el => el.innerText = revertedLabel);
        }
    });
}

let prevCurrencyId = "1";
document.getElementById('currency_id').addEventListener('focus', function() {
    prevCurrencyId = this.value;
});

    // إضافة صف منتج (سواء جديد أو قادم من الداتابيس)
    function addProductRow(product, savedItem = null) {
        document.getElementById('emptyState') ? document.getElementById('emptyState').style.display = 'none' : '';
        
        // 🟢 ضمان أن الوحدات مصفوفة دائماً (لتفادي تعليق الـ try/catch لو عادت من PHP ككائن)
        if (product && product.units && !Array.isArray(product.units)) {
            product.units = Object.values(product.units);
        }

        window.productsData[rowIdx] = product; // 🟢 حفظ المنتج في الذاكرة

        if (!product || !product.units || product.units.length === 0) {
            console.error("Product has no units or is invalid:", product);
            return;
        }

        // حسابات التكلفة (Normalization) لمعالجة تضارب الأسعار في الداتابيس
        let maxUnit = product.units.reduce((prev, curr) => (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr);
        let maxUnitCost = parseFloat(maxUnit.cost_price) || parseFloat(maxUnit.purchase_price) || 0;
        let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
        
        let mCurrId = maxUnit.purchase_currency_id || baseCurrencyId;
        let mRate = (mCurrId == baseCurrencyId) ? 1 : (parseFloat(maxUnit.purchase_exchange_rate) || parseFloat(maxUnit.store_custom_purchase_rate) || ratesMap[mCurrId]?.exchange_rate || 1);
        
        // التكلفة بالعملة الأساسية (TRY)
        let trueBaseCost = (maxUnitCost * mRate) / maxFactor; 
        
        let selectedUnitId;
        if (savedItem) {
            selectedUnitId = savedItem.product_unit_id;
        } else {
            let foundScanned = product.units.find(u => u.id == product.scanned_unit_id);
            if (foundScanned) {
                selectedUnitId = foundScanned.id;
            } else {
                 selectedUnitId = (product.units.find(u => u.is_base_unit)?.id || product.units[0].id);
            }
        }

        let selectedUnit = product.units.find(u => u.id == selectedUnitId);
        // حماية في حال تم حذف الوحدة المربوطة بالفاتورة القديمة من قاعدة البيانات
        if (!selectedUnit) {
            selectedUnit = product.units.find(u => u.is_base_unit) || product.units[0];
            selectedUnitId = selectedUnit.id;
        }
        
        let selectedFactor = (selectedUnit.is_base_unit) ? 1 : (parseFloat(selectedUnit.conversion_factor) || 1);
        
        // التكلفة بالعملة الأساسية (TRY)
        let priceInBase = trueBaseCost * selectedFactor;

        // جلب سعر صرف الفاتورة
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;

        // التكلفة بعملة الفاتورة
        let calculatedPrice = priceInBase / invRate;

        // --- تصحيح تحويل سعر البيع إلى عملة الفاتورة ---
        let rawSellPrice = parseFloat(selectedUnit.selling_price) || 0;
        let sCurrId = selectedUnit.sell_currency_id || baseCurrencyId;
        let sRate = (sCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.sell_exchange_rate) || parseFloat(selectedUnit.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
        
        // سعر البيع بالعملة الأساسية (TRY)
        let sellInBase = rawSellPrice * sRate;
        // سعر البيع بعملة الفاتورة
        let sellPrice = sellInBase / invRate;

        // القيم الافتراضية
        let initialBarcode = selectedUnit ? (selectedUnit.barcode || '-') : '-';
        
        // استرجاع القيم المحفوظة (مع الحفاظ على السعر القديم إذا كان مخصصاً)
        // ملاحظة: في حالة الإضافة الجديدة نستخدم السعر المحسوب لتفادي القفزات
        let qty = savedItem ? parseFloat(savedItem.quantity) : 1;
        
        // السعر موجود في الداتابيس بعملة الفاتورة، لذلك لا نحتاج لتحويله هنا! (كنا نحوله سابقاً لليرة، الآن نعرض عملة الفاتورة مباشرة)
        let price = savedItem ? parseFloat(savedItem.unit_price) : parseFloat(calculatedPrice.toFixed(4));
        
        // وكذلك سعر البيع، موجود بالداتابيس بعملة الفاتورة أو نستخرجه منها إذا كان جديداً
        if (savedItem && savedItem.selling_price) {
             sellPrice = parseFloat(savedItem.selling_price);
        }

        // 🟢🟢 استرجاع تاريخ الانتهاء وأيام التنبيه من قاعدة البيانات 🟢🟢
        let expiryValue = savedItem ? (savedItem.expiry_date || '') : '';
        let alertValue = savedItem ? (savedItem.alert_days || 10) : 10;

        // حساب نسبة الربح
        let profitPercent = (price > 0 && sellPrice > 0) ? ((sellPrice - price) / price) * 100 : 0;
        
        let imgUrl = product.image_url; 
        let taxOptionsHtml = storeTaxRates.map(rate => `<option value="${rate}">${rate}%</option>`).join('');

        const tr = document.createElement('tr');
        tr.id = `row_${rowIdx}`;
        tr.className = "align-middle";
        
        tr.innerHTML = `
            <td>
                <button type="button" class="btn btn-sm btn-info text-white" onclick="toggleDetails(${rowIdx})"><i class="fas fa-chevron-down"></i></button>
                <img src="${imgUrl}" class="img-thumbnail unit-img mt-1" style="width: 40px; height: 40px; object-fit: cover;">
            </td>
            <td class="text-start">
                <input type="hidden" name="items[${rowIdx}][product_id]" value="${product.id}">
                ${savedItem ? `<input type="hidden" name="items[${rowIdx}][item_id]" value="${savedItem.id}">` : ''}
                <span class="fw-bold small">${product.name}</span>
            </td>
            <td><input type="text" class="form-control form-control-sm text-center bg-white barcode-display" value="${initialBarcode}" readonly></td>
            <td>
                <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm unit-select" onchange="updateRowData(${rowIdx}, this)">
                    ${product.units.filter(u => u.is_purchase == 1).map(u => {
                        let isBase = u.is_base_unit == 1;
                        let safeFactor = isBase ? 1 : (parseFloat(u.conversion_factor) || 1);
                        let mathPrice = (trueBaseCost * safeFactor) / invRate;

                        // تحويل سعر البيع لكل وحدة أيضاً
                        let rSell = parseFloat(u.selling_price) || 0;
                        let rCurrId = u.sell_currency_id || baseCurrencyId;
                        let rRate = (rCurrId == baseCurrencyId) ? 1 : (ratesMap[rCurrId]?.exchange_rate || 1);
                        let rSellInBase = rSell * rRate;
                        let rSellInInv = rSellInBase / invRate;

                        let rProfit = (mathPrice > 0) ? ((rSellInInv - mathPrice) / mathPrice) * 100 : 0;

                        return `<option value="${u.id}" 
                                data-barcode="${u.barcode || '-'}" 
                                data-price="${mathPrice.toFixed(4)}" 
                                data-sell="${formatNum(rSellInInv)}" 
                                data-profit="${formatNum(rProfit)}" 
                                data-factor="${safeFactor}" 
                                ${u.id == selectedUnitId ? 'selected' : ''}>
                                ${u.unit_name}
                                </option>`;
                    }).join('')}
                </select>
            </td>
            
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][quantity]" class="form-control form-control-sm text-center qty" value="${qty}" oninput="calcTotals(${rowIdx})"></td>
            <td>
                <input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price text-danger fw-bold" value="${price}" oninput="syncSubUnits(${rowIdx})">
                <div class="cost-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1; color: black !important;">
                   <span class="cost-original d-block text-muted"></span>
                   <span class="cost-base d-block text-info fw-bold"></span>
                </div>
            </td>
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary" value="${profitPercent}" oninput="calcSellPrice(${rowIdx})"></td>

            <td>
                <div class="input-group input-group-sm" style="min-width: 90px;">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="${discountVal}" oninput="calcTotals(${rowIdx})">
                    <select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" style="max-width: 40px;" onchange="calcTotals(${rowIdx})">
                        <option value="fixed" class="currency-label">1</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </td>

            <td>
                <input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control form-control-sm text-center sell fw-bold text-success" value="${sellPrice}" oninput="calcProfitPercent(${rowIdx})">
                <div class="sell-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1; color: black !important;">
                   <span class="sell-original d-block text-muted"></span>
                   <span class="sell-base d-block text-info fw-bold"></span>
                </div>
                <div class="main-warning-container mt-1" style="min-height:18px;"></div>
            </td>

            1
            <td>
                <input type="text" name="items[${rowIdx}][expiry_date]" 
                       class="form-control form-control-sm text-center expiry-date-input" 
                       value="${expiryValue}" title="${LANG.expiry_date || 'تاريخ الانتهاء'}" placeholder="YYYY-MM-DD">
            </td>

            1
            <td>
                <input type="number" name="items[${rowIdx}][alert_days]" 
                       class="form-control form-control-sm text-center text-danger fw-bold" 
                       value="${alertValue}" placeholder="10">
            </td>

            <td><select name="items[${rowIdx}][tax]" class="form-select form-select-sm tax bg-warning bg-opacity-10" onchange="calcTotals(${rowIdx})">${taxOptionsHtml}</select></td>
            <td><input type="text" class="form-control form-control-sm text-center bg-light fw-bold total" readonly></td>
            <td><button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="removeRow(${rowIdx})"><i class="fas fa-times"></i></button></td>
        `;
        document.getElementById('tableBody').appendChild(tr);
        
        // 🔥 Initialize Flatpickr for Expiry Date 🔥
        flatpickr(tr.querySelector('.expiry-date-input'), {
            dateFormat: "Y-m-d",
            locale: CURRENT_LOCALE,
            allowInput: true
        });

        const detailsTr = document.createElement('tr');
        detailsTr.id = `details_${rowIdx}`;
        detailsTr.style.display = 'none';
        detailsTr.className = "bg-light";
        detailsTr.innerHTML = `<td colspan="14"><div class="p-3 border rounded bg-white"><h6 class="fw-bold text-primary mb-2"><i class="fas fa-sitemap"></i> ${LANG.updated_related_units}</h6><div id="related_units_container_${rowIdx}"></div></div></td>`;
        document.getElementById('tableBody').appendChild(detailsTr);

        renderRelatedUnits(rowIdx, selectedUnitId); // 🟢 استدعاء الدالة الموحدة
        updateDualPriceDisplay(rowIdx); // 🟢 عرض العملتين
        calcTotals(rowIdx); 
        rowIdx++;
    }

    // 🟢 عرض السعر بالعملة الأصلية وما يعادلها بالعملة الأساسية
    function updateDualPriceDisplay(idx) {
        let row = document.getElementById(`row_${idx}`);
        if (!row) return;
        
        let product = window.productsData[idx];
        let select = row.querySelector('.unit-select');
        let selectedUnitId = select.value;
        let unit = product.units.find(u => u.id == selectedUnitId);
        
        // 1. جلب القيم الحالية من الحقول (الآن أصبحت بعملة الفاتورة)
        let priceInvoice = parseFloat(row.querySelector('.price').value) || 0;
        let sellInvoice = parseFloat(row.querySelector('.sell').value) || 0;

        // 2. التحويل لليرة التركية / العملة الأساسية
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        let priceBase = priceInvoice * invRate;
        let sellBase = sellInvoice * invRate;
        let baseCurrCode = "1";

        // 🟢 سعر الشراء الإضافي
        let costOriginal = row.querySelector('.cost-original');
        let costBaseEl = row.querySelector('.cost-base');
        
        // السعر بالأصلي (يورو مثلاً)
        if (unit && unit.purchase_currency_id) {
            let pPrice = parseFloat(unit.cost_price) || parseFloat(unit.purchase_price) || 0;
            let pSym = unit.purchase_currency_symbol || unit.purchase_currency_code || '';
            costOriginal.innerText = `${pSym} ${pPrice}`;
        }
        // السعر بالعملة الأساسية (TRY)
        if(costBaseEl) costBaseEl.innerText = `${formatNum(priceBase)} ${baseCurrCode}`;

        // 🟢 سعر البيع الإضافي
        let sellOriginal = row.querySelector('.sell-original');
        let sellBaseEl = row.querySelector('.sell-base');

        // السعر بالأصلي
        if (unit && (unit.sell_currency_id || unit.sell_price_currency_id)) {
            let sCurrId = unit.sell_price_currency_id || unit.sell_currency_id;
            let sPrice = parseFloat(unit.selling_price) || parseFloat(unit.sale_price) || 0;
            let sSym = unit.sell_currency_symbol || unit.sell_currency_code || '';
            sellOriginal.innerText = `${sSym} ${sPrice}`;
        }
        // السعر بالعملة الأساسية (TRY)
        if(sellBaseEl) sellBaseEl.innerText = `${formatNum(sellBase)} ${baseCurrCode}`;
    }

    // --- العمليات الحسابية ---
    function updateRowData(idx, select) {
        let opt = select.options[select.selectedIndex];
        let row = document.getElementById(`row_${idx}`);
        
        // جلب البيانات من الـ data attributes
        let price = parseFloat(opt.getAttribute('data-price')) || 0;
        let sell = parseFloat(opt.getAttribute('data-sell')) || 0;
        let profit = parseFloat(opt.getAttribute('data-profit')) || 0;
        let barcode = opt.getAttribute('data-barcode') || '-';

        row.querySelector('.price').value = formatNum(price);
        row.querySelector('.sell').value = formatNum(sell);
        row.querySelector('.profit').value = formatNum(profit);
        row.querySelector('.barcode-display').value = barcode;

        updateDualPriceDisplay(idx);
        renderRelatedUnits(idx, opt.value); // 🟢 إعادة رسم الوحدات عند تغيير الوحدة المختارة
        calcTotals(idx);
    }

    function calcTotals(idx) {
        let row = document.getElementById(`row_${idx}`);
        if(!row) return;

        let qty = parseMoney(row.querySelector('.qty').value);
        let priceInvoice = parseMoney(row.querySelector('.price').value);
        let invoiceRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;

        let taxRate = parseFloat(row.querySelector('.tax').value) || 0;
        let discountVal = parseFloat(row.querySelector('.discount').value) || 0;
        let discountType = row.querySelector('.discount-type').value;

        // الحسابات بعملة الفاتورة لأن الأسعار أصبحت بها
        let subTotalInv = qty * priceInvoice;
        let discountAmtInv = (discountType === 'percent') ? (subTotalInv * discountVal / 100) : discountVal;
        let afterDiscountInv = subTotalInv - discountAmtInv;
        let taxAmtInv = afterDiscountInv * (taxRate / 100);
        let finalTotalInv = afterDiscountInv + taxAmtInv;

        // حفظ إجمالي الصف بعملة الفاتورة لسهولة حساب المجموع الكلي لاحقاً
        row.setAttribute('data-total-invoice', finalTotalInv.toFixed(6));

        // عرض الإجمالي بالعملة الأساسية كما طلب المستخدم
        let finalTotalBase = finalTotalInv * invoiceRate;
        row.querySelector('.total').value = formatNum(finalTotalBase);
        
        updateDualPriceDisplay(idx);
        calculateGrandTotal();
    }

    function calcProfitPercent(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseFloat(row.querySelector('.price').value) || 0;
        let sell = parseFloat(row.querySelector('.sell').value) || 0;
        if(price > 0) {
            let profit = ((sell - price) / price) * 100;
            row.querySelector('.profit').value = formatNum(profit);
        }
    }

    function calcSellPrice(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseFloat(row.querySelector('.price').value) || 0;
        let profit = parseFloat(row.querySelector('.profit').value) || 0;
        let sell = price * (1 + profit / 100);
        row.querySelector('.sell').value = formatNum(sell);
        
        updateDualPriceDisplay(idx);
        calculateGrandTotal();
    }

    // 🟢 دالة إعادة حساب كافة الصفوف عند تغيير عملة الفاتورة
    function recalculateAllRows() {
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        
        Object.keys(window.productsData).forEach(idx => {
            let row = document.getElementById(`row_${idx}`);
            if (!row) return;

            let product = window.productsData[idx];
            let select = row.querySelector('.unit-select');
            let selectedUnitId = select.value;
            let selectedUnit = product.units.find(u => u.id == selectedUnitId);
            
            if (!selectedUnit) return;

            let selectedFactor = (selectedUnit.is_base_unit) ? 1 : (parseFloat(selectedUnit.conversion_factor) || 1);
            
            // 1. حساب التكلفة بناءً على السعر المفضل في بيانات المنتج
            let preferredPrice = parseFloat(selectedUnit.cost_price) || parseFloat(selectedUnit.purchase_price) || 0;
            let pCurrId = selectedUnit.purchase_currency_id || baseCurrencyId;
            let pRate = (pCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.purchase_exchange_rate) || parseFloat(selectedUnit.store_custom_purchase_rate) || ratesMap[pCurrId]?.exchange_rate || 1);
            
            let priceInBase = preferredPrice * pRate;
            let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
            let calculatedCost = priceInBase / invRate;

            // 2. معالجة الحالة الاحتياطية (إذا كان السعر صفر)
            if (calculatedCost === 0) {
                let maxUnit = product.units.reduce((prev, curr) => (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr);
                let maxUnitCost = parseFloat(maxUnit.cost_price) || parseFloat(maxUnit.purchase_price) || 0;
                let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
                let mCurrId = maxUnit.purchase_currency_id || baseCurrencyId;
                let mRate = (mCurrId == baseCurrencyId) ? 1 : (parseFloat(maxUnit.purchase_exchange_rate) || parseFloat(maxUnit.store_custom_purchase_rate) || ratesMap[mCurrId]?.exchange_rate || 1);
                
                let basePriceFallback = (maxUnitCost * mRate / maxFactor) * selectedFactor;
                calculatedCost = basePriceFallback / invRate;
            }

            // 3. تحديث حقل سعر الشراء في الصف بعملة الفاتورة
            row.querySelector('.price').value = parseFloat(calculatedCost.toFixed(4));

            // 4. تحديث سعر البيع بعملة الفاتورة
            let rawSellPrice = parseFloat(selectedUnit.selling_price) || parseFloat(selectedUnit.sale_price) || 0;
            let sCurrId = selectedUnit.sell_price_currency_id || selectedUnit.sell_currency_id || baseCurrencyId;
            let sRate = (sCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.sell_exchange_rate) || parseFloat(selectedUnit.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
            
            let sellInBase = rawSellPrice * sRate;
            let sellPrice = sellInBase / invRate;
            row.querySelector('.sell').value = formatNum(sellPrice);

            // 5. تحديث نسبة الربح
            let newProfitPercent = (calculatedCost > 0) ? ((sellPrice - calculatedCost) / calculatedCost) * 100 : 0;
            row.querySelector('.profit').value = formatNum(newProfitPercent);

            // 6. تحديث قيم الـ data attributes في الـ select (بعملة الفاتورة)
            let trueBaseCost = (selectedFactor > 0) ? (calculatedCost / selectedFactor) : 0;
            Array.from(select.options).forEach(opt => {
                let uId = opt.value;
                let u = product.units.find(ux => ux.id == uId);
                if (u) {
                    let fac = (u.is_base_unit) ? 1 : (parseFloat(u.conversion_factor) || 1);
                    let mPrice = trueBaseCost * fac;
                    
                    let rs = parseFloat(u.selling_price) || parseFloat(u.sale_price) || 0;
                    let rci = u.sell_price_currency_id || u.sell_currency_id || baseCurrencyId;
                    let rr = (rci == baseCurrencyId) ? 1 : (parseFloat(u.sell_exchange_rate) || parseFloat(u.store_custom_sell_rate) || ratesMap[rci]?.exchange_rate || 1);
                    let rsiBase = rs * rr; 
                    let rsi = rsiBase / invRate; // بعملة الفاتورة
                    let rp = (mPrice > 0) ? ((rsi - mPrice) / mPrice) * 100 : 0;

                    opt.setAttribute('data-price', mPrice.toFixed(4));
                    opt.setAttribute('data-sell', formatNum(rsi));
                    opt.setAttribute('data-profit', formatNum(rp));
                }
            });

            // 7. تحديث الوحدات الفرعية (التفاصيل)
            renderRelatedUnits(idx, selectedUnitId);
            
            // 8. تحديث العرض المزدوج والإجمالي
            updateDualPriceDisplay(idx);
            calcTotals(idx);
        });
    }

    function removeRow(idx) {
        document.getElementById(`row_${idx}`).remove();
        document.getElementById(`details_${idx}`).remove();
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let invoiceRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        let baseCurrCode = "1";

        // 1. حساب الإجمالي بعملة الفاتورة أولاً
        let subTotalInvoice = 0;
        document.querySelectorAll('tr[id^="row_"]').forEach(row => {
            subTotalInvoice += parseFloat(row.getAttribute('data-total-invoice')) || 0;
        });

        // 2. تحويل وعرض المجموع الفرعي بالعملة الأساسية 
        let subTotalBase = subTotalInvoice * invoiceRate;
        document.getElementById('subTotalDisplay').innerHTML = `${formatNum(subTotalBase)} <span class="fs-6 text-muted">(${formatNum(subTotalInvoice)} Invoice)</span>`;
        
        // 3. تطبيق الخصم (يدخل المستخدم الخصم بعملة الفاتورة)
        let discountInput = parseFloat(document.getElementById('discountInput').value) || 0;
        let grandTotalInvoice = subTotalInvoice - discountInput; // افتراضاً الخصم هنا fixed بعملة الفاتورة بالمجمل
        
        // 4. تحويل وعرض الصافي النهائي بالعملة الأساسية
        let grandTotalBase = grandTotalInvoice * invoiceRate;
        document.getElementById('grandTotalDisplay').innerHTML = `${formatNum(grandTotalBase)} <span class="fs-6 text-muted">(${formatNum(grandTotalInvoice)} Invoice)</span>`;
        // نحتفظ بهذه القيم للإستخدام في الدفعات لاحقاً
        document.getElementById('grandTotalDisplay').setAttribute('data-grand-total', grandTotalBase);

        // حساب مجموع المدفوعات بالعملة الأساسية
        // في صفحة التعديل حالياً يوجد حقل واحد للدفع
        let totalPaid = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let inp = row.closest('div').parentElement.querySelector('.payment-input');
            if (!inp) return;
            
            let hiddenRate = document.getElementById('pay_rate_0'); // استخدام المعرف الفريد للصف الأول حالياً
            let rate = hiddenRate ? (parseFloat(hiddenRate.value) || 1) : 1;
            
            let amt = parseFloat(inp.value) || 0;
            totalPaid += amt * rate; 
        });

        const diff = parseFloat((totalPaid - grandTotalBase).toFixed(2));
        const balDiv = document.getElementById('balanceAlert');
        
        if(balDiv) {
            balDiv.style.display = 'block';
            if (Math.abs(diff) < 0.01) {
                balDiv.className = 'alert p-2 text-center fw-bold alert-success';
                document.getElementById('balanceLabel').innerText = LANG.paid_settled || 'خالص';
                document.getElementById('balanceAmount').innerText = '';
            } else if (diff < 0) {
                balDiv.className = 'alert p-2 text-center fw-bold alert-danger';
                document.getElementById('balanceLabel').innerText = (LANG.remaining_due || 'متبقي') + ":";
                document.getElementById('balanceAmount').innerText = formatNum(Math.abs(diff)) + " " + baseCurrCode;
            } else {
                balDiv.className = 'alert p-2 text-center fw-bold alert-info';
                document.getElementById('balanceLabel').innerText = (LANG.credit_for_you || 'رصيد لك') + ":";
                document.getElementById('balanceAmount').innerText = formatNum(diff) + " " + baseCurrCode;
            }
        }
    }

    function toggleDetails(idx) {
        let row = document.getElementById(`details_${idx}`);
        row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
    }

    function syncSubUnits(idx) {
        calcTotals(idx);
        let row = document.getElementById(`row_${idx}`);
        
        let mainPrice = parseFloat(row.querySelector('.price').value) || 0; 
        
        // تحديث ربح الوحدة الأساسية
        let mainSellInput = row.querySelector('.sell');
        let mainProfitInput = row.querySelector('.profit');
        let currentSell = parseFloat(mainSellInput.value) || 0;
        
        if (mainPrice > 0) {
            let newMainProfit = ((currentSell - mainPrice) / mainPrice) * 100;
            mainProfitInput.value = formatNum(newMainProfit);
        }

        // جلب المعامل الحالي
        let select = row.querySelector('.unit-select');
        let selectedOption = select.options[select.selectedIndex];
        let currentFactor = parseFloat(selectedOption.getAttribute('data-factor')) || 1;
        let costPerPiece = (currentFactor > 0) ? (mainPrice / currentFactor) : 0;

        let container = document.getElementById(`related_units_container_${idx}`);
        if(container) {
            container.querySelectorAll('.related-unit-row').forEach(subRow => {
                let subFactor = parseFloat(subRow.getAttribute('data-factor')) || 1;
                let originalSubProfit = parseFloat(subRow.getAttribute('data-original-profit')) || 0;
                
                let newSubCost = costPerPiece * subFactor;
                
                subRow.querySelector('.sub-cost').value = formatNum(newSubCost);
                subRow.querySelector('.hidden-sub-cost').value = newSubCost.toFixed(4);

                let currentSubSell = parseFloat(subRow.querySelector('.sub-sell').value) || 0;
                let newSubProfit = 0;
                if(newSubCost > 0) {
                    newSubProfit = ((currentSubSell - newSubCost) / newSubCost) * 100;
                }
                
                subRow.querySelector('.sub-profit').value = formatNum(newSubProfit);
                subRow.querySelector('.hidden-sub-profit').value = formatNum(newSubProfit);

                // 🟢 تحديث تحذير الوحدات الفرعية
                let subWarningDiv = subRow.querySelector('.warning-container');
                if(subWarningDiv){
                    subWarningDiv.innerHTML = '';
                    if (newSubProfit <= 0) {
                        subWarningDiv.innerHTML = `<span class="text-danger fw-bold small">${LANG.loss}</span>`;
                    } 
                    else if (newSubProfit < originalSubProfit - 0.1) {
                        subWarningDiv.innerHTML = `<span class="text-warning text-dark fw-bold small">${LANG.low_profit}</span>`;
                    }
                }
            });
        }
    }

    // --- دوال الوحدات المرتبطة الجديدة ---
    function renderRelatedUnits(idx, currentUnitId) {
        let product = window.productsData[idx];
        let container = document.getElementById(`related_units_container_${idx}`);
        if(!container || !product) return;
        
        container.innerHTML = '';

        let row = document.getElementById(`row_${idx}`);
        let mainPrice = parseFloat(row.querySelector('.price').value) || 0;
        let select = row.querySelector('.unit-select');
        let currentFactor = parseFloat(select.options[select.selectedIndex].getAttribute('data-factor')) || 1;
        let trueBaseCost = (currentFactor > 0) ? (mainPrice / currentFactor) : 0;

        let html = '';
        product.units.forEach(u => {
            if (u.id == currentUnitId) return; 

            let isBase = (u.is_base_unit == 1);
            let safeFactor = isBase ? 1 : (parseFloat(u.conversion_factor) || 1);
            let calculatedCost = trueBaseCost * safeFactor; 
            
            let rawSell = parseFloat(u.selling_price) || 0;
            let sCurrId = u.sell_currency_id || u.sell_price_currency_id || "1";
            let sRate = (sCurrId == "1") ? 1 : (parseFloat(u.sell_exchange_rate) || parseFloat(u.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
            
            let sInBase = rawSell * sRate;
            let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
            let uSell = sInBase / invRate;

            let originalProfit = parseFloat(u.profit_percent) || 0; 
            
            let uProfit = 0;
            if(calculatedCost > 0) uProfit = ((uSell - calculatedCost) / calculatedCost) * 100;
            
            let uImg = u.image_url || product.main_image;

            html += `
                <div class="row g-2 align-items-center mb-2 related-unit-row border-bottom pb-2" 
                     data-unit-id="${u.id}" 
                     data-factor="${safeFactor}"
                     data-original-profit="${originalProfit}"> 
                    
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][price]" class="hidden-sub-cost" value="${calculatedCost.toFixed(4)}">
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][selling_price]" class="hidden-sub-sell" value="${uSell}">
                    <input type="hidden" name="items[${idx}][related_updates][${u.id}][profit_percent]" class="hidden-sub-profit" value="${formatNum(uProfit)}">

                    <div class="col-md-2 d-flex align-items-center">
                        <img src="${uImg}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <div>
                            <span class="badge bg-secondary">${u.unit_name}</span>
                            <small class="d-block text-muted" style="font-size: 0.75rem;">(x${safeFactor})</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted">${LANG.buy}</span>
                            <input type="text" class="form-control text-center bg-light sub-cost text-danger fw-bold" 
                                   value="${formatNum(calculatedCost)}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">${LANG.profit_percent}</span>
                            <input type="text" inputmode="decimal" class="form-control text-center sub-profit" value="${formatNum(uProfit)}" oninput="calcSubUnitSell(this)" onfocus="this.select()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">${LANG.sell}</span>
                            <input type="text" inputmode="decimal" class="form-control text-center fw-bold sub-sell text-success" 
                                   value="${formatNum(uSell)}" oninput="calcSubUnitProfit(this)" onfocus="this.select()">
                        </div>
                        <div class="warning-container mt-1" style="min-height:20px;"></div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    }

    function calcSubUnitSell(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let profit = parseFloat(input.value) || 0;
        
        let sell = cost * (1 + profit / 100);
        row.querySelector('.sub-sell').value = formatNum(sell);
        row.querySelector('.hidden-sub-sell').value = sell.toFixed(2);
        row.querySelector('.hidden-sub-profit').value = profit;
        
        // تحديث التحذير
        calcSubUnitProfit(row.querySelector('.sub-sell'), true);
    }

    function calcSubUnitProfit(input, fromProfitCalc = false) {
        let row = input.closest('.related-unit-row');
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let sell = parseFloat(row.querySelector('.sub-sell').value) || 0;

        // إذا لم يتم الاستدعاء من دالة حساب البيع، نقوم بحساب الربح
        if(!fromProfitCalc) {
            let row = input.closest('.related-unit-row');
            row.querySelector('.hidden-sub-sell').value = sell;
            
            let newProfit = 0;
            if (cost > 0) newProfit = ((sell - cost) / cost) * 100;
            
            row.querySelector('.sub-profit').value = formatNum(newProfit);
            row.querySelector('.hidden-sub-profit').value = newProfit;
        }

        let profitVal = parseFloat(row.querySelector('.sub-profit').value) || 0;
        let originalProfit = parseFloat(row.getAttribute('data-original-profit')) || 0;
        let warningDiv = row.querySelector('.warning-container');
        
        warningDiv.innerHTML = ''; 

        if (profitVal <= 0) {
            warningDiv.innerHTML = `<span class="text-danger fw-bold small">${LANG.loss}</span>`;
        } 
        else if (profitVal < originalProfit - 0.1) {
            warningDiv.innerHTML = `<span class="text-warning text-dark fw-bold small">${LANG.low_profit}</span>`;
        }
    }

    // --- Search Logic ---
    function setupSearch(inputId, resultsId, url, onSelect) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        if(!input) return;

        let debounce;
        input.addEventListener('input', function() {
            clearTimeout(debounce);
            const term = this.value.trim();
            if(term.length < 1) { results.style.display='none'; return; }

            debounce = setTimeout(() => {
                fetch(`${url}?term=${term}`).then(r => r.json()).then(data => {
                    results.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach((item, index) => {
                            let div = document.createElement('a');
                            div.className = 'list-group-item list-group-item-action cursor-pointer';
                            div.innerHTML = item.contact_name || `${item.name} - <small>${item.sku || ''}</small>`;
                            div.onclick = function() { onSelect(item); results.style.display = 'none'; };
                            results.appendChild(div);
                        });
                        results.style.display = 'block';
                    } else { results.style.display = 'none'; }
                });
            }, 300);
        });
        
        document.addEventListener("click", function (e) { 
            if (e.target !== input && e.target !== results) results.style.display = 'none'; 
        });
    }

    // --- تشغيل عند التحميل (DOMContentLoaded) ---
    document.addEventListener("DOMContentLoaded", function() {
        // 🔥 Initialize Date Picker 🔥
        flatpickr("input[name='invoice_date']", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            locale: CURRENT_LOCALE,
            time_24hr: true,
            allowInput: true
        });

        // 1. تشغيل البحث عن الموردين والمنتجات
        
        // Currency change listener
        // document.getElementById('currency_id').addEventListener('change', function() {
        //     let selectedCode = this.options[this.selectedIndex].text.split(' — ')[0];
        //     document.querySelectorAll('.currency-label').forEach(el => el.innerText = selectedCode);
        // });

        setupSearch('supplierSearchInput', 'supplierResults', "1", function(s) {
            document.getElementById('supplierSearchInput').value = s.contact_name;
            document.getElementById('supplierId').value = s.id;
        });

        setupSearch('productSearch', 'searchResults', "1", function(p) {
            addProductRow(p); // منتج جديد
            document.getElementById('productSearch').value = ''; 
            document.getElementById('productSearch').focus();
        });

        // 2. 🔥 تعبئة المنتجات القديمة (Fix) 🔥
        let normalizedOldItems = oldItems;
        // إذا جاءت ككائن (Object) بدلاً من مصفوفة، نحولها
        if (normalizedOldItems && !Array.isArray(normalizedOldItems) && typeof normalizedOldItems === 'object') {
            normalizedOldItems = Object.values(normalizedOldItems);
        }

        if (normalizedOldItems && Array.isArray(normalizedOldItems) && normalizedOldItems.length > 0) {
            console.log("Loading saved items:", normalizedOldItems);
            normalizedOldItems.forEach(item => {
                if (item.product) {
                    try {
                        // نمرر الـ item المحفوظ للدالة ليتم أخذ الكمية والسعر منه
                        addProductRow(item.product, item);
                    } catch (e) {
                        console.error("Error adding row for item:", item, e);
                    }
                }
            });
            calculateGrandTotal();
        }
    });


