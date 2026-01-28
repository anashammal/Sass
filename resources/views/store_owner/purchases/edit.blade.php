@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- نرسل الطلب إلى update ونحدد النوع الافتراضي approved --}}
    <form action="{{ route('store.purchases.update', $purchase->id) }}" method="POST" id="purchaseForm" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        
        {{-- حقل مخفي يجبر الفاتورة لتكون معتمدة عند الضغط على زر الحفظ اليدوي --}}
        <input type="hidden" name="save_type" value="approved">
        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
        
        <div class="row">
            {{-- رأس الفاتورة --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning bg-opacity-10 text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> تعديل فاتورة / إكمال مسودة (#{{ $purchase->invoice_number }})</h5>
                        <div class="d-flex align-items-center">
                             <span id="saveStatus" class="badge bg-white text-success me-3 d-none"><i class="fas fa-check"></i> محفوظ</span>
                             <a href="{{ route('store.purchases.index') }}" class="btn btn-sm btn-light text-dark fw-bold">العودة</a>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">المورد <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSupplierModal" title="مورد جديد"><i class="fas fa-plus"></i></button>
                                        {{-- ملء بيانات المورد القديم --}}
                                        <input type="text" id="supplierSearchInput" class="form-control" 
                                               value="{{ $purchase->supplier->company_name ?? $purchase->supplier->contact_name }}" 
                                               placeholder="ابحث عن مورد..." autocomplete="off">
                                        <input type="hidden" name="supplier_id" id="supplierId" value="{{ $purchase->supplier_id }}" required>
                                    </div>
                                    <div id="supplierResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; display: none;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">تاريخ وتوقيت الفاتورة</label>
                                <input type="datetime-local" name="invoice_date" class="form-control custom-date-input" 
                                       value="{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">رقم الفاتورة</label>
                                <input type="text" name="invoice_number" class="form-control" value="{{ $purchase->invoice_number }}" placeholder="مثال: INV-1001">
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
                                       placeholder="ابحث باسم المنتج أو امسح الباركود..." autocomplete="off">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickProductModal"><i class="fas fa-plus-circle me-1"></i> منتج جديد</button>
                            </div>
                            <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; top: 100%; display: none;"></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle mb-0" id="itemsTable">
                                <thead class="bg-dark text-white small">
                                     <tr>
                                        <th style="width: 10%">صورة</th>
                                        <th style="width: 7%">المنتج</th>
                                        <th style="width: 14%">الباركود</th>
                                        <th style="width: 9%">الوحدة</th>
                                        <th style="width: 6%">الكمية</th>
                                        <th style="width: 8%">سعر الشراء</th>
                                        <th style="width: 7%">الربح %</th> 
                                        <th style="width: 7%">الخصم</th>
                                        <th style="width: 7%">سعر المبيع</th>
                                        <th width="5%">تاريخ الانتهاء</th>
                                        <th width="5%">تنبيه قبل (يوم)</th>
                                        <th style="width: 7%">الضريبة</th>
                                        <th style="width: 14%">الإجمالي</th>
                                        <th style="width: 2%"></th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
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
                            <span>المجموع الفرعي:</span> <span id="subTotalDisplay" class="fw-bold">{{ $purchase->sub_total }}</span>
                        </div>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text">خصم إضافي</span>
                            <input type="number" name="discount" id="discountInput" class="form-control text-center fw-bold text-danger" value="{{ $purchase->discount_amount }}" step="any" oninput="calculateGrandTotal()">
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 mb-3">
                            <span class="fs-5 fw-bold">الصافي النهائي:</span>
                            <span id="grandTotalDisplay" class="fs-4 fw-bold text-primary">{{ $purchase->grand_total }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small text-muted mb-1">المدفوعات</label>
                            <div id="paymentsContainer">
                                <div class="input-group mb-2 payment-row">
                                    <select name="payments[0][method]" class="form-select" style="max-width: 120px;">
                                        <option value="cash" {{ $purchase->payment_method == 'cash' ? 'selected' : '' }}>💰 نقدي</option>
                                        <option value="card" {{ $purchase->payment_method == 'card' ? 'selected' : '' }}>💳 بطاقة</option>
                                        <option value="bank" {{ $purchase->payment_method == 'bank' ? 'selected' : '' }}>🏦 تحويل</option>
                                    </select>
                                    <input type="number" name="payments[0][amount]" class="form-control text-center payment-input" value="{{ $purchase->paid_amount }}" step="any" oninput="calculateGrandTotal()">
                                </div>
                            </div>
                        </div>

                        <div class="alert p-2 text-center fw-bold" id="balanceAlert" style="display: none;">
                            <span id="balanceLabel">المتبقي:</span> <span id="balanceAmount">0.00</span>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100 btn-lg mt-3" id="saveBtn"><i class="fas fa-save me-2"></i> حفظ التعديلات واعتماد الفاتورة</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- نفس المودالات --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">إضافة مورد جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="quickSupplierForm"><div class="mb-2"><label class="small fw-bold">الاسم *</label><input type="text" id="suppName" name="name" class="form-control" required></div><div class="mb-2"><label class="small">الشركة</label><input type="text" id="suppComp" name="company" class="form-control"></div><button type="submit" class="btn btn-success w-100">حفظ وإضافة</button></form></div></div></div></div>
<div class="modal fade" id="quickProductModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">إضافة منتج سريع</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">قريباً...</div></div></div></div>
{{-- 🔥 كود تجهيز الصور للمنتجات القديمة 🔥 --}}
@php
    // نمر على كل عنصر في الفاتورة ونحدد الصورة الصحيحة له
    foreach($purchase->items as $item) {
        $img = asset('images/default-product.png'); // الصورة الافتراضية

        // 1. محاولة جلب صورة الوحدة (إذا وجدت)
        if($item->unit && $item->unit->getFirstMediaUrl('unit_images')) {
            $img = $item->unit->getFirstMediaUrl('unit_images');
        } 
        // 2. محاولة جلب صورة المنتج الأساسية
        elseif ($item->product && $item->product->getFirstMediaUrl('products')) {
            $img = $item->product->getFirstMediaUrl('products');
        }
        
        // نضيف رابط الصورة للمنتج لكي يقرأه الجافاسكربت
        $item->product->image_url = $img;
    }
@endphp

@section('scripts')
<script>
    // --- المتغيرات العامة ---
    let rowIdx = 0;
    let paymentIdx = 1;
    const storeTaxRates = @json($taxRates); 
    // التأكد من أن البيانات تأتي مصفوفة سليمة حتى لو كانت فارغة
    const oldItems = @json($purchase->items ?? []); 

    // --- دوال التنسيق والحسابات ---

// دالة جديدة لا تقرب الأرقام وتعرضها كما هي
function formatNum(num) { 
    if (num === null || num === undefined || num === '') return 0;
    // نتأكد أنه رقم ولكن لا نستخدم toFixed حتى لا يقرب الكسور
    return parseFloat(num); 
}

    // إضافة صف منتج (سواء جديد أو قادم من الداتابيس)
    function addProductRow(product, savedItem = null) {
        document.getElementById('emptyState') ? document.getElementById('emptyState').style.display = 'none' : '';
        
        // تحديد الوحدة
        let selectedUnitId;
        if (savedItem) {
            selectedUnitId = savedItem.product_unit_id;
        } else {
            selectedUnitId = product.scanned_unit_id || (product.units.find(u => u.is_base_unit)?.id || product.units[0].id);
        }

        let selectedUnit = product.units.find(u => u.id == selectedUnitId);

        // القيم الافتراضية
        let initialBarcode = selectedUnit ? (selectedUnit.barcode || '-') : '-';
        let rawPurchase = parseFloat(selectedUnit.purchase_price) || 0;
        let rawCost = parseFloat(selectedUnit.cost_price) || 0;
        let defaultPrice = rawPurchase > 0 ? rawPurchase : rawCost;
        let defaultSell = parseFloat(selectedUnit.selling_price) || 0;
        
        // استرجاع القيم المحفوظة
        let qty = savedItem ? parseFloat(savedItem.quantity) : 1;
        let price = savedItem ? parseFloat(savedItem.unit_price) : defaultPrice;
        let sellPrice = defaultSell; 
        let discountVal = 0;

        // 🟢🟢 استرجاع تاريخ الانتهاء وأيام التنبيه من قاعدة البيانات 🟢🟢
        let expiryValue = savedItem ? (savedItem.expiry_date || '') : '';
        let alertValue = savedItem ? (savedItem.alert_days || 10) : 10;

        // حساب نسبة الربح
        let profitPercent = (price > 0 && sellPrice > 0) ? ((sellPrice - price) / price) * 100 : 0;
        
        let imgUrl = product.image_url; 
        let taxOptionsHtml = storeTaxRates.map(rate => `<option value="${rate}">${rate}%</option>`).join('');

        // الوحدات المرتبطة
        let relatedUnitsHtml = '';
        if(product.units){
            product.units.forEach(u => {
                if (u.id == selectedUnitId) return;
                let uCost = parseFloat(u.cost_price) || 0;
                let uSell = parseFloat(u.selling_price) || 0;
                let uProfit = (uCost > 0 && uSell > 0) ? ((uSell - uCost) / uCost) * 100 : 0;
                let factor = u.is_base_unit ? 1 : (parseFloat(u.conversion_factor) || 1);
                relatedUnitsHtml += `
                    <div class="row g-2 align-items-center mb-2 related-unit-row" data-unit-id="${u.id}" data-factor="${factor}">
                        <div class="col-md-2"><span class="badge bg-secondary">${u.unit_name}</span><small class="d-block text-muted">(x${factor})</small></div>
                        <div class="col-md-3"><input type="number" class="form-control form-control-sm text-center bg-light" value="${formatNum(uCost)}" readonly></div>
                        <div class="col-md-3"><input type="number" class="form-control form-control-sm text-center" value="${formatNum(uProfit)}"></div>
                        <div class="col-md-4"><input type="number" class="form-control form-control-sm text-center fw-bold" value="${formatNum(uSell)}"></div>
                    </div>`;
            });
        }

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
                <span class="fw-bold small">${product.name_ar}</span>
            </td>
            <td><input type="text" class="form-control form-control-sm text-center bg-white barcode-display" value="${initialBarcode}" readonly></td>
            <td>
                <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm unit-select" onchange="updateRowData(${rowIdx}, this)">
                    ${product.units.map(u => {
                        let uP = parseFloat(u.purchase_price) || 0; let uC = parseFloat(u.cost_price) || 0; let baseP = uP > 0 ? uP : uC;
                        return `<option value="${u.id}" 
                                data-barcode="${u.barcode || '-'}" 
                                data-price="${baseP}" 
                                data-sell="${u.selling_price}" 
                                data-profit="${u.profit_percent}" 
                                ${u.id == selectedUnitId ? 'selected' : ''}>
                                ${u.unit_name}
                                </option>`;
                    }).join('')}
                </select>
            </td>
            
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][quantity]" class="form-control form-control-sm text-center qty" value="${qty}" oninput="calcTotals(${rowIdx})"></td>
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price" value="${price}" oninput="syncSubUnits(${rowIdx})"></td>
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary" value="${profitPercent}" oninput="calcSellPrice(${rowIdx})"></td>

            <td>
                <div class="input-group input-group-sm" style="min-width: 90px;">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="${discountVal}" oninput="calcTotals(${rowIdx})">
                    <select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" style="max-width: 40px;" onchange="calcTotals(${rowIdx})">
                        <option value="fixed">₺</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </td>

            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control form-control-sm text-center sell fw-bold text-success" value="${sellPrice}" oninput="calcProfitPercent(${rowIdx})"></td>

            {{-- 🟢 عرض التاريخ المخزن 🟢 --}}
            <td>
                <input type="date" name="items[${rowIdx}][expiry_date]" 
                       class="form-control form-control-sm text-center" 
                       value="${expiryValue}" title="تاريخ الانتهاء">
            </td>

            {{-- 🟢 عرض أيام التنبيه المخزنة 🟢 --}}
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
        
        const detailsTr = document.createElement('tr');
        detailsTr.id = `details_${rowIdx}`;
        detailsTr.style.display = 'none';
        detailsTr.className = "bg-light";
        detailsTr.innerHTML = `<td colspan="14"><div class="p-3 border rounded bg-white"><h6 class="fw-bold text-primary mb-2">الوحدات المرتبطة</h6>${relatedUnitsHtml}</div></td>`;
        document.getElementById('tableBody').appendChild(detailsTr);

        calcTotals(rowIdx); 
        rowIdx++;
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

        calcTotals(idx);
    }

    function calcTotals(idx) {
        let row = document.getElementById(`row_${idx}`);
        if(!row) return;

        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        let taxRate = parseFloat(row.querySelector('.tax').value) || 0;
        let discountVal = parseFloat(row.querySelector('.discount').value) || 0;
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
    }

    function removeRow(idx) {
        document.getElementById(`row_${idx}`).remove();
        document.getElementById(`details_${idx}`).remove();
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let subTotal = 0;
        document.querySelectorAll('.total').forEach(el => subTotal += parseFloat(el.value) || 0);
        document.getElementById('subTotalDisplay').innerText = formatNum(subTotal); 
        
        let discount = parseFloat(document.getElementById('discountInput').value) || 0;
        let grandTotal = subTotal - discount;
        document.getElementById('grandTotalDisplay').innerText = formatNum(grandTotal);

        let totalPaid = 0;
        document.querySelectorAll('.payment-input').forEach(inp => totalPaid += parseFloat(inp.value) || 0);

        const diff = parseFloat((totalPaid - grandTotal).toFixed(2));
        const balDiv = document.getElementById('balanceAlert');
        
        if(balDiv) {
            balDiv.style.display = 'block';
            let msg = diff >= 0 ? (diff > 0 ? `رصيد لك: ${diff}` : 'خالص') : `متبقي عليك: ${Math.abs(diff)}`;
            let cls = diff >= 0 ? 'alert-success' : 'alert-danger';
            balDiv.className = `alert p-2 text-center fw-bold ${cls}`;
            document.getElementById('balanceLabel').innerText = msg;
            document.getElementById('balanceAmount').innerText = '';
        }
    }

    function toggleDetails(idx) {
        let row = document.getElementById(`details_${idx}`);
        row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
    }

    function syncSubUnits(idx) {
        calcTotals(idx);
        calcProfitPercent(idx); // تحديث نسبة الربح عند تغير سعر الشراء
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
                            div.innerHTML = item.contact_name || `${item.name_ar} - <small>${item.sku || ''}</small>`;
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
        
        // 1. تشغيل البحث عن الموردين والمنتجات
        setupSearch('supplierSearchInput', 'supplierResults', '/store-owner/contacts/search', function(s) {
            document.getElementById('supplierSearchInput').value = s.contact_name;
            document.getElementById('supplierId').value = s.id;
        });

        setupSearch('productSearch', 'searchResults', '/store-owner/products/search', function(p) {
            addProductRow(p); // منتج جديد
            document.getElementById('productSearch').value = ''; 
            document.getElementById('productSearch').focus();
        });

        // 2. 🔥 تعبئة المنتجات القديمة (Fix) 🔥
        if (oldItems && oldItems.length > 0) {
            console.log("Loading saved items:", oldItems);
            oldItems.forEach(item => {
                if (item.product) {
                    // نمرر الـ item المحفوظ للدالة ليتم أخذ الكمية والسعر منه
                    addProductRow(item.product, item);
                }
            });
            calculateGrandTotal();
        }
    });

</script>
@endsection
@endsection