@extends('layouts.app')

@section('content')
<style>
/* تأثير الوميض الأحمر عند التكرار */
    @keyframes flashRed {
        0% { background-color: #ffcccc; }
        50% { background-color: #ff0000; color: white; }
        100% { background-color: white; color: black; }
    }
    .duplicate-flash {
        animation: flashRed 0.5s ease-in-out 3; /* يومض 3 مرات */
    }
    /* تعريف حركة الوميض */
    @keyframes blink-animation {
        0% { opacity: 1; }
        50% { opacity: 0.2; }
        100% { opacity: 1; }
    }
    /* كلاس التفعيل */
    .flash-warning {
        animation: blink-animation 1.5s infinite; /* يتكرر كل ثانية ونصف */
        font-weight: bold;
        font-size: 0.8rem;
        display: inline-block;
        padding: 2px 5px;
        border-radius: 4px;
    }
<style>
    /* 1. منع التفاف النص في كل خلايا الجدول افتراضياً */
    #itemsTable th, #itemsTable td {
        vertical-align: middle !important;
        white-space: nowrap; /* سطر واحد فقط */
    }

    /* 2. استثناء عمود المنتج ليسمح بتعدد الأسطر */
    .product-col {
        white-space: normal !important; /* السماح بالنزول لسطر ثاني */
        min-width: 200px; /* أقل عرض مسموح */
        max-width: 350px; /* أقصى عرض */
        line-height: 1.4; /* مسافة مريحة بين الأسطر */
    }
    
    /* تنسيق الصور */
    .product-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; }
    
    /* الوميض والتحذير (كودك السابق) */
    @keyframes blink-animation { 0% { opacity: 1; } 50% { opacity: 0.2; } 100% { opacity: 1; } }
    .flash-warning { animation: blink-animation 1.5s infinite; font-weight: bold; font-size: 0.8rem; padding: 2px 5px; }
</style>
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
    {{-- ✅ هذا هو الزر الذي كان مفقوداً --}}
    <button type="button" class="btn btn-success" onclick="openCreateSupplierModal()" title="مورد جديد"><i class="fas fa-plus"></i></button>
    
    <input type="text" id="supplierSearchInput" class="form-control" placeholder="ابحث عن مورد..." autocomplete="off">
    <input type="hidden" name="supplier_id" id="supplierId" required>
    <input type="hidden" id="currentSupplierBalance" value="0">
</div>
                                    <div id="supplierResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; display: none;"></div>
                                </div>
                                {{-- 🟦🟥🟩 تكبير وتلوين الرصيد --}}
                                <div id="supplierBalanceDisplay" class="mt-2 fs-5 fw-bold text-center p-2 rounded bg-white border">
                                    <span class="text-muted small">الرصيد: --</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">تاريخ وتوقيت الفاتورة</label>
                                <input type="datetime-local" name="invoice_date" class="form-control custom-date-input" 
                                       value="{{ old('invoice_date', $currentDate) }}" required>
                            </div>
<div class="form-group">
    <label>رقم الفاتورة</label>
    <input type="text" name="invoice_number" class="form-control" value="{{ $nextInvoiceNumber }}" readonly>
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
                                {{-- تم التعديل لاستدعاء دالة فتح الإطار --}}
<button type="button" class="btn btn-success" onclick="openCreateProductModal()"><i class="fas fa-plus-circle me-1"></i> منتج جديد</button>
                            </div>
                            <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; top: 100%; display: none;"></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle mb-0" id="itemsTable">
                                <thead class="bg-dark text-white small">
                                    <tr>
                                        <th style="width: 10%">صورة</th>
                                        <th style="width: 8%">المنتج</th>
                                        <th style="width: 14%">الباركود</th>
                                        <th style="width: 9%">الوحدة</th>
                                        <th style="width: 6%">الكمية</th>
                                        <th style="width: 8%">سعر الشراء</th>
                                        <th style="width: 7%">الربح %</th> 
                                        <th style="width: 7%">الخصم</th>
                                        <th style="width: 7%">سعر المبيع</th>
                                        <th width="5%">تاريخ الانتهاء</th>
                                        <th width="4%">تنبيه قبل (يوم)</th>
                                        <th style="width: 8%">الضريبة</th>
                                        <th style="width: 14%">الإجمالي</th>
                                        <th style="width: 2%"></th>
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
                        
                        <button type="button" onclick="checkBalanceAndSubmit()" class="btn btn-primary w-100 btn-lg mt-3"><i class="fas fa-save me-2"></i> حفظ الفاتورة</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- مودال تأكيد الرصيد والدين --}}
<div class="modal fade" id="balanceConfirmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> تأكيد العملية المالية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h5 class="mb-3">ملخص تأثير الفاتورة على الرصيد</h5>
                
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span>الرصيد الحالي للمورد:</span>
                    <span id="modalOldBalance" class="fw-bold"></span>
                </div>
                
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span>قيمة الفاتورة (الصافي):</span>
                    <span id="modalGrandTotal" class="fw-bold text-primary"></span>
                </div>

                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span>المبلغ المدفوع الآن:</span>
                    <span id="modalPaid" class="fw-bold text-success"></span>
                </div>

                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span>المتبقي (الآجل):</span>
                    <span id="modalDiff" class="fw-bold text-danger"></span>
                </div>

                <div class="alert alert-secondary mt-3">
                    <strong>الرصيد الجديد بعد الحفظ:</strong><br>
                    <span id="modalNewBalance" class="fs-4 fw-bold"></span>
                </div>
                
                <p class="text-muted small" id="modalMessage"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary px-5" onclick="document.getElementById('purchaseForm').submit()">موافق وحفظ</button>
            </div>
        </div>
    </div>
</div>

{{-- المودالات الأخرى --}}
{{-- مودال إضافة مورد (نافذة Iframe) --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> إضافة مورد جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                <iframe id="createSupplierFrame" src="" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>
{{-- مودال إضافة منتج سريع (نافذة Iframe) --}}
<div class="modal fade" id="quickProductModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title"><i class="fas fa-cube me-2"></i> إضافة منتج جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                {{-- هنا سيتم تحميل صفحة الإضافة --}}
                <iframe id="createProductFrame" src="" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    // --- المتغيرات العامة ---
    let rowIdx = 0;
    let paymentIdx = 1;
    const storeTaxRates = @json($taxRates); 
    window.productsData = {}; 

    // --- عند تحميل الصفحة ---
    document.addEventListener("DOMContentLoaded", function() {
        console.log("✅ Main Script Loaded");

        // إعداد بحث الموردين (مع الاختيار التلقائي)
        setupSearch('supplierSearchInput', 'supplierResults', "{{ route('store.contacts.search') }}", function(s) {
            document.getElementById('supplierSearchInput').value = s.contact_name || s.company_name;
            document.getElementById('supplierId').value = s.id;
            
            // 🟦🟥🟩 منطق الرصيد الجديد
            let balance = parseFloat(s.current_balance || 0);
            let displayDiv = document.getElementById('supplierBalanceDisplay');
            document.getElementById('currentSupplierBalance').value = balance;

            if (balance > 0) {
                // أحمر
                displayDiv.innerHTML = `<span class="text-danger fs-3"><i class="fas fa-arrow-down"></i> له علينا: ${formatNum(balance)}</span>`;
            } else if (balance < 0) {
                // أخضر
                displayDiv.innerHTML = `<span class="text-success fs-3"><i class="fas fa-arrow-up"></i> لنا عنده: ${formatNum(Math.abs(balance))}</span>`;
            } else {
                // أزرق
                displayDiv.innerHTML = `<span class="text-primary fs-3">الرصيد: 0.00</span>`;
            }
            // مسح البحث
            document.getElementById('supplierResults').style.display = 'none';
        }, true); // true = تفعيل الاختيار التلقائي للموردين

        // إعداد بحث المنتجات
        setupSearch('productSearch', 'searchResults', "{{ route('store.products.search') }}", function(p) {
            addProductRow(p);
            // تفريغ الحقل
            let input = document.getElementById('productSearch');
            input.value = ''; 
            input.focus();
        }, true);
    // --- 🟢 كود مراقبة نافذة إضافة المورد للإضافة التلقائية ---
        const supplierFrame = document.getElementById('createSupplierFrame');
        if(supplierFrame) {
            supplierFrame.onload = function() {
                try {
                    const newUrl = supplierFrame.contentWindow.location.href;
                    
                    // التحقق من الحفظ (خروج من صفحة create/edit)
                    if (!newUrl.includes('create') && !newUrl.includes('edit')) {
                        console.log("✅ تم حفظ المورد، الرابط: " + newUrl);
                        
                        // 1. إغلاق المودال
                        var modalEl = document.getElementById('addSupplierModal');
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if(modal) modal.hide();

                        // 2. استخراج ID المورد من الرابط (مثال: /contacts/50)
                        const match = newUrl.match(/contacts\/(\d+)/);
                        if (match && match[1]) {
                            const newContactId = match[1];
                            
                            // 3. جلب بيانات المورد وتعبئة الحقول
                            fetch(`{{ route('store.contacts.search') }}?term=${newContactId}`)
                                .then(r => r.json())
                                .then(data => {
                                     let contact = null;
                                     if(Array.isArray(data)) {
                                         contact = data.find(c => c.id == newContactId) || data[0];
                                     } else {
                                         contact = data;
                                     }

                                     if(contact) {
                                         // ✅ تعبئة البيانات في الفاتورة مباشرة
                                         document.getElementById('supplierSearchInput').value = contact.contact_name || contact.company_name;
                                         document.getElementById('supplierId').value = contact.id;
                                         
                                         // تحديث الرصيد
                                         let balance = parseFloat(contact.current_balance || 0);
                                         let displayDiv = document.getElementById('supplierBalanceDisplay');
                                         document.getElementById('currentSupplierBalance').value = balance;

                                         if (balance > 0) {
                                             displayDiv.innerHTML = `<span class="text-danger fs-3"><i class="fas fa-arrow-down"></i> له علينا: ${formatNum(balance)}</span>`;
                                         } else if (balance < 0) {
                                             displayDiv.innerHTML = `<span class="text-success fs-3"><i class="fas fa-arrow-up"></i> لنا عنده: ${formatNum(Math.abs(balance))}</span>`;
                                         } else {
                                             displayDiv.innerHTML = `<span class="text-primary fs-3">الرصيد: 0.00</span>`;
                                         }

                                         if(typeof toastr !== 'undefined') toastr.success('تم اختيار المورد الجديد تلقائياً');
                                     }
                                })
                                .catch(err => console.error('خطأ في جلب المورد', err));
                        }
                    }
                } catch (e) {
                    console.log('Access restricted (Cross-origin) or loading...');
                }
            };
        }
    // true = تفعيل الاختيار التلقائي للمنتجات
// --- 🟢 كود مراقبة نافذة إضافة المنتج للإضافة التلقائية للفاتورة ---
        const frame = document.getElementById('createProductFrame');
        if(frame) {
            frame.onload = function() {
                try {
                    // قراءة الرابط الحالي داخل الـ iframe
                    const newUrl = frame.contentWindow.location.href;
                    
                    // إذا تغير الرابط ولم يعد في صفحة الإنشاء (create) أو التعديل (edit)
                    // فهذا يعني أن المستخدم ضغط حفظ وتم تحويله
                    if (!newUrl.includes('create') && !newUrl.includes('edit')) {
                        console.log("✅ تم الحفظ بنجاح، الرابط الجديد: " + newUrl);
                        
                        // 1. إغلاق المودال
                        var myModalEl = document.getElementById('quickProductModal');
                        var modal = bootstrap.Modal.getInstance(myModalEl);
                        if(modal) modal.hide();

                        // 2. محاولة استخراج ID المنتج من الرابط (مثال: /products/15)
                        const match = newUrl.match(/products\/(\d+)/);
                        if (match && match[1]) {
                            const newProductId = match[1];
                            
                            // 3. جلب بيانات المنتج الجديد وإضافته للجدول
                            fetch(`{{ route('store.products.search') }}?term=${newProductId}`)
                                .then(r => r.json())
                                .then(data => {
                                     // التأكد من أن النتيجة مصفوفة أو كائن
                                     let product = null;
                                     if(Array.isArray(data)) {
                                         // البحث عن المنتج الذي يطابق الـ ID
                                         product = data.find(p => p.id == newProductId) || data[0];
                                     } else {
                                         product = data;
                                     }

                                     if(product) {
                                         addProductRow(product); // إضافة للصف
                                         
                                         // تنبيه نجاح (اختياري)
                                         if(typeof toastr !== 'undefined') toastr.success('تم إضافة المنتج الجديد للفاتورة');
                                         else alert('تم إضافة المنتج الجديد للفاتورة بنجاح!');
                                     }
                                })
                                .catch(err => console.error('خطأ في جلب المنتج الجديد', err));
                        }
                    }
                } catch (e) {
                    console.log('لا يمكن الوصول لمحتوى الإطار بسبب سياسات الأمان (Cross-origin) أو لم يتم التحميل بعد.');
                }
            };
        }
    });

    // --- دوال مساعدة ---
    function parseMoney(value) {
        if (!value) return 0;
        let clean = String(value).replace(/[^0-9.]/g, ''); 
        return parseFloat(clean) || 0;
    }

    function formatNum(num) { return parseMoney(num).toFixed(2); }

    // --- دالة إضافة صف المنتج المصححة ---
    function addProductRow(product) {
        document.getElementById('emptyState').style.display = 'none';
        window.productsData[rowIdx] = product;

        // تحديد الوحدة الافتراضية
        let selectedUnitId = product.scanned_unit_id;
        if (!selectedUnitId) {
            let base = product.units.find(u => u.is_base_unit == 1) || product.units[0];
            selectedUnitId = base.id;
        }

        // حسابات التكلفة
        let maxUnit = product.units.reduce((prev, curr) => (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr);
        let maxUnitCost = parseFloat(maxUnit.cost_price) || parseFloat(maxUnit.purchase_price) || 0;
        let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
        let trueBaseCost = maxUnitCost / maxFactor; 

        let selectedUnit = product.units.find(u => u.id == selectedUnitId);
        let selectedFactor = (selectedUnit.is_base_unit) ? 1 : (parseFloat(selectedUnit.conversion_factor) || 1);
        let calculatedCost = trueBaseCost * selectedFactor;
        
        let initialBarcode = selectedUnit.barcode || '-';
        let sellPrice = parseFloat(selectedUnit.selling_price) || 0;
        let profitPercent = (calculatedCost > 0) ? ((sellPrice - calculatedCost) / calculatedCost) * 100 : 0;
        
        let imgUrl = selectedUnit.image_url || product.main_image;
        let taxOptionsHtml = storeTaxRates.map(rate => `<option value="${rate}">${rate}%</option>`).join('');

        const tr = document.createElement('tr');
        tr.id = `row_${rowIdx}`;
        tr.className = "align-middle";

        let optionsHtml = product.units.filter(u => u.is_purchase == 1).map(u => {
            let isBase = u.is_base_unit == 1;
            let safeFactor = isBase ? 1 : (parseFloat(u.conversion_factor) || 1);
            let mathPrice = trueBaseCost * safeFactor;
            return `<option value="${u.id}" 
                    data-barcode="${u.barcode || '-'}" 
                    data-price="${mathPrice.toFixed(4)}" 
                    data-sell="${formatNum(u.selling_price)}" 
                    data-profit="${formatNum(u.profit_percent || 0)}"
                    data-factor="${safeFactor}" 
                    data-img="${u.image_url || ''}" 
                    ${u.id == selectedUnitId ? 'selected' : ''}>
                    ${u.unit_name}
                    </option>`;
        }).join('');

        // بناء الصف (HTML) بشكل صحيح بدون تكرار أو قطع
        tr.innerHTML = `
            <td>
                <button type="button" class="btn btn-sm btn-info text-white" onclick="toggleDetails(${rowIdx})"><i class="fas fa-chevron-down"></i></button>
                <img src="${imgUrl}" id="img_${rowIdx}" class="product-thumb mt-1" style="width: 40px; height: 40px; object-fit: cover;">
            </td>
            <td class="text-start">
                <input type="hidden" name="items[${rowIdx}][product_id]" value="${product.id}">
                <span class="fw-bold small">${product.name_ar}</span>
            </td>
            <td><input type="text" class="form-control form-control-sm text-center bg-white barcode-display" id="barcode_${rowIdx}" value="${initialBarcode}" readonly></td>
            <td>
                <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm unit-select" onchange="updateRowData(${rowIdx})">${optionsHtml}</select>
            </td>
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][quantity]" class="form-control form-control-sm text-center qty" value="1" oninput="calcTotals(${rowIdx})" onfocus="this.select()"></td>
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price text-danger fw-bold" value="${parseFloat(calculatedCost.toFixed(4))}" oninput="syncSubUnits(${rowIdx}, 'purchase')" onfocus="this.select()"></td>
            <td><input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary" value="${formatNum(profitPercent)}" oninput="calcSellPrice(${rowIdx})" onfocus="this.select()"></td>
            <td><div class="input-group input-group-sm" style="min-width: 90px;"><input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="0" oninput="calcTotals(${rowIdx})"><select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" onchange="calcTotals(${rowIdx})"><option value="fixed">₺</option><option value="percent">%</option></select></div></td>
            <td>
                <input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control form-control-sm text-center sell text-success fw-bold" value="${formatNum(sellPrice)}" oninput="calcProfitPercent(${rowIdx})" onfocus="this.select()">
                <div class="main-warning-container mt-1" style="min-height:20px;"></div>
            </td>

            {{-- 🟢 تاريخ الانتهاء 🟢 --}}
            <td>
                <input type="date" name="items[${rowIdx}][expiry_date]" class="form-control form-control-sm text-center" title="تاريخ الانتهاء">
            </td>

            {{-- 🟢 أيام التنبيه (الافتراضي 10) 🟢 --}}
            <td>
                <input type="number" name="items[${rowIdx}][alert_days]" class="form-control form-control-sm text-center text-danger fw-bold" value="10" placeholder="10" title="نبهني قبل X يوم">
            </td>
            
            <td><select name="items[${rowIdx}][tax]" class="form-select form-select-sm tax bg-warning bg-opacity-10" onchange="calcTotals(${rowIdx})">${taxOptionsHtml}</select></td>
            <td><input type="text" class="form-control form-control-sm text-center bg-light fw-bold total" readonly></td>
            <td><button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="removeRow(${rowIdx})"><i class="fas fa-times"></i></button></td>
        `;
        
        document.getElementById('tableBody').appendChild(tr);

        // صف التفاصيل المخفية
        const detailsTr = document.createElement('tr');
        detailsTr.id = `details_${rowIdx}`;
        detailsTr.style.display = 'none';
        detailsTr.className = "bg-light";
        detailsTr.innerHTML = `<td colspan="14"><div class="p-3 border rounded bg-white"><h6 class="fw-bold text-primary mb-2"><i class="fas fa-sitemap"></i> تحديث الوحدات المرتبطة</h6><div id="related_units_container_${rowIdx}"></div></div></td>`;
        document.getElementById('tableBody').appendChild(detailsTr);

        renderRelatedUnits(rowIdx, selectedUnitId);
        calcTotals(rowIdx); 
        rowIdx++;
    }

    function updateRowData(idx) {
        let row = document.getElementById(`row_${idx}`);
        let select = row.querySelector('.unit-select');
        let opt = select.options[select.selectedIndex];
        
        let unitId = opt.value;
        let product = window.productsData[idx];

        let price = parseFloat(opt.getAttribute('data-price')) || 0;
        let barcode = opt.getAttribute('data-barcode');
        let sell = parseFloat(opt.getAttribute('data-sell')) || 0;
        let profit = parseFloat(opt.getAttribute('data-profit')) || 0;
        let imgUrl = opt.getAttribute('data-img');

        row.querySelector('.barcode-display').value = barcode;
        row.querySelector('.price').value = parseFloat(price.toFixed(4)); 
        row.querySelector('.sell').value = formatNum(sell);
        row.querySelector('.profit').value = formatNum(profit);
        
        let imgTag = document.getElementById(`img_${idx}`);
        if(imgTag && imgUrl) {
            imgTag.src = imgUrl;
        } else if (imgTag) {
            imgTag.src = product.main_image; 
        }

        renderRelatedUnits(idx, unitId);
        // عند تغيير الوحدة لا نغير أسعار باقي الوحدات، فقط نعيد رسمها
        calcTotals(idx);
    }

    function renderRelatedUnits(idx, currentUnitId) {
        let product = window.productsData[idx];
        let container = document.getElementById(`related_units_container_${idx}`);
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
            
            let uSell = parseFloat(u.selling_price) || 0;
            // جلب الربح الأصلي من قاعدة البيانات للمقارنة
            let originalProfit = parseFloat(u.profit_percent) || 0; 
            
            let uProfit = 0;
            if(calculatedCost > 0) uProfit = ((uSell - calculatedCost) / calculatedCost) * 100;
            
            let uImg = u.image_url || product.main_image;

            html += `
                <div class="row g-2 align-items-center mb-2 related-unit-row border-bottom pb-2" 
                     data-unit-id="${u.id}" 
                     data-factor="${safeFactor}"
                     data-original-profit="${originalProfit}"> {{-- 🟢 تخزين الربح الأصلي هنا --}}
                    
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
                            <span class="input-group-text bg-light text-muted">شراء</span>
                            <input type="text" class="form-control text-center bg-light sub-cost text-danger fw-bold" 
                                   value="${formatNum(calculatedCost)}" readonly>
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
                            <input type="text" inputmode="decimal" class="form-control text-center fw-bold sub-sell text-success" 
                                   value="${formatNum(uSell)}" oninput="calcSubUnitProfit(this)" onfocus="this.select()">
                        </div>
                        {{-- 🟢 مكان التحذير (فارغ افتراضياً) --}}
                        <div class="warning-container mt-1" style="min-height:20px;"></div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    }

   // 🚀 تحديث ذكي للوحدات (تم التعديل لتحديث ربح الوحدة الحالية أيضاً)
   function syncSubUnits(idx, source) {
        calcTotals(idx);
        let row = document.getElementById(`row_${idx}`);
        
        let mainPrice = parseFloat(row.querySelector('.price').value) || 0; 
        
        // تحديث ربح الوحدة الأساسية والتحذير الخاص بها
        let mainSellInput = row.querySelector('.sell');
        let mainProfitInput = row.querySelector('.profit');
        let currentSell = parseFloat(mainSellInput.value) || 0;
        
        // جلب الربح الأصلي للوحدة الأساسية
        let select = row.querySelector('.unit-select');
        let selectedOption = select.options[select.selectedIndex];
        let originalMainProfit = parseFloat(selectedOption.getAttribute('data-profit')) || 0;

        let newMainProfit = 0;
        if (mainPrice > 0) {
            newMainProfit = ((currentSell - mainPrice) / mainPrice) * 100;
            mainProfitInput.value = formatNum(newMainProfit);
        }

        // 🟢 تحديث تحذير الوحدة الأساسية (بدون إطار)
        let mainWarningDiv = row.querySelector('.main-warning-container');
        mainWarningDiv.innerHTML = ''; 

        if (newMainProfit <= 0) {
            mainWarningDiv.innerHTML = `<span class="text-danger flash-warning">خسارة ⚠️</span>`;
        } 
        else if (newMainProfit < originalMainProfit - 0.1) {
            mainWarningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">📉 انخفاض الربح</span>`;
        }

        // ---------------------------------------------------------

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

                // 🟢 تحديث تحذير الوحدات الفرعية (بدون إطار)
                let subWarningDiv = subRow.querySelector('.warning-container');
                subWarningDiv.innerHTML = '';

                if (newSubProfit <= 0) {
                    subWarningDiv.innerHTML = `<span class="text-danger flash-warning">خسارة ⚠️</span>`;
                } 
                else if (newSubProfit < originalSubProfit - 0.1) {
                    subWarningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">📉 انخفاض الربح</span>`;
                }
            });
        }
    }

    // دوال الحساب للوحدات الفرعية (مستقلة لكل وحدة)
    function calcSubUnitSell(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let profit = parseFloat(input.value) || 0;
        
        // تغيير الربح -> يغير سعر البيع
        let sell = cost * (1 + profit / 100);
        row.querySelector('.sub-sell').value = formatNum(sell);
        row.querySelector('.hidden-sub-sell').value = sell.toFixed(2);
        row.querySelector('.hidden-sub-profit').value = profit;
        
        let warning = row.querySelector('.warning-msg');
        if(warning) warning.style.display = (profit <= 0) ? 'block' : 'none';
    }

    function calcSubUnitProfit(input) {
        let row = input.closest('.related-unit-row');
        let cost = parseFloat(row.querySelector('.sub-cost').value) || 0;
        let sell = parseFloat(input.value) || 0;

        let hiddenSell = row.querySelector('.hidden-sub-sell');
        if(hiddenSell) hiddenSell.value = sell;

        let newProfit = 0;
        if (cost > 0) {
            newProfit = ((sell - cost) / cost) * 100;
            row.querySelector('.sub-profit').value = formatNum(newProfit);
        }
        
        let hiddenProfit = row.querySelector('.hidden-sub-profit'); // تأكد من وجود هذا الحقل المخفي
        if(hiddenProfit) hiddenProfit.value = newProfit; // تحديث القيمة المخفية للربح

        // 🟢 منطق إخفاء/إظهار التحذير لحظياً عند الكتابة للوحدات الفرعية
        let originalProfit = parseFloat(row.getAttribute('data-original-profit')) || 0;
        let warningDiv = row.querySelector('.warning-container');
        
        warningDiv.innerHTML = ''; // مسح القديم

        if (newProfit <= 0) {
            warningDiv.innerHTML = `<span class="text-danger flash-warning">خسارة ⚠️</span>`;
        } 
        else if (newProfit < originalProfit - 0.1) {
            warningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">📉 انخفاض الربح</span>`;
        }
    }

    // --- دالة البحث المحدثة (تدعم الاختيار التلقائي للمورد والمنتج) ---
    function setupSearch(inputId, resultsId, url, onSelect, autoSelect = false) {
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
                fetch(`${url}?term=${encodeURIComponent(term)}`, {
    method: 'GET',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin'
})

                    .then(r => {
                        if (!r.ok) throw new Error("Server Error");
                        return r.json();
                    })
                    .then(data => {
                        results.innerHTML = '';
                        if (data.length > 0) {
                            
                            // 🚀 الاختيار التلقائي إذا كانت النتيجة واحدة فقط
                            if (autoSelect && data.length === 1) {
                                onSelect(data[0]);
                                results.style.display = 'none';
                                return;
                            }

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
                        console.error("خطأ:", error);
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

    // --- بقية الدوال (calcTotals, removeRow, etc...) ضروري تكون موجودة ---
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
    
    function calcProfitPercent(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseMoney(row.querySelector('.price').value);
        let sell = parseMoney(row.querySelector('.sell').value);
        let profitInput = row.querySelector('.profit');
        
        let newProfit = 0;
        if(price > 0) {
            newProfit = ((sell - price) / price) * 100;
            profitInput.value = formatNum(newProfit);
        }

        // 🟢 منطق إخفاء/إظهار التحذير لحظياً عند الكتابة
        let select = row.querySelector('.unit-select');
        let originalMainProfit = parseFloat(select.options[select.selectedIndex].getAttribute('data-profit')) || 0;
        let mainWarningDiv = row.querySelector('.main-warning-container');
        
        mainWarningDiv.innerHTML = ''; // مسح القديم دائماً

        if (newProfit <= 0) {
            mainWarningDiv.innerHTML = `<span class="text-danger flash-warning">خسارة ⚠️</span>`;
        } 
        else if (newProfit < originalMainProfit - 0.1) {
            mainWarningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">📉 انخفاض الربح</span>`;
        }
    }

    function calcSellPrice(idx) {
        let row = document.getElementById(`row_${idx}`);
        let price = parseMoney(row.querySelector('.price').value);
        let profit = parseMoney(row.querySelector('.profit').value);
        let sell = price * (1 + profit / 100);
        row.querySelector('.sell').value = formatNum(sell);
    }
    
    // الدالة المفقودة: checkBalanceAndSubmit
    function checkBalanceAndSubmit() {
        // 🛑 1. التحقق من اختيار المورد
        // نتحقق من القيمة المخفية (ID) وليس النص الظاهر فقط لضمان اختيار مورد صحيح
        let supplierId = document.getElementById('supplierId').value;
        let supplierInput = document.getElementById('supplierSearchInput');

        if (!supplierId || supplierId.trim() === '') {
            // تلوين الحقل بالأحمر
            supplierInput.classList.add('is-invalid'); 
            
            // إظهار رسالة خطأ (يدعم SweetAlert أو التنبيه العادي)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'تنبيه',
                    text: 'الرجاء اختيار المورد من القائمة قبل حفظ الفاتورة!',
                    confirmButtonText: 'حسناً'
                });
            } else {
                alert('الرجاء اختيار المورد من القائمة قبل حفظ الفاتورة!');
            }
            return; // ⛔ إيقاف الدالة هنا
        } else {
            supplierInput.classList.remove('is-invalid');
        }

        // 🛑 2. التحقق من تواريخ الانتهاء لكل المنتجات
        let expiryInputs = document.querySelectorAll('input[name$="[expiry_date]"]');
        let missingExpiry = false;

        // التأكد أولاً من وجود منتجات
        if (expiryInputs.length === 0) {
            if (typeof toastr !== 'undefined') toastr.error('الفاتورة فارغة! أضف منتجات أولاً.');
            else alert('الفاتورة فارغة! أضف منتجات أولاً.');
            return;
        }

        expiryInputs.forEach(input => {
            // التحقق مما إذا كان الحقل فارغاً
            if (!input.value) {
                missingExpiry = true;
                input.classList.add('is-invalid'); // تلوين الحقل الفارغ بالأحمر
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (missingExpiry) {
             if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'تاريخ الانتهاء مطلوب',
                    text: 'لا يمكن حفظ الفاتورة. يوجد منتجات بدون تاريخ انتهاء!',
                    confirmButtonText: 'مراجعة المنتجات'
                });
             } else {
                 alert('لا يمكن حفظ الفاتورة. يوجد منتجات بدون تاريخ انتهاء!');
             }
            return; // ⛔ إيقاف الدالة هنا
        }

        // ✅ إذا تجاوزنا الفحوصات أعلاه، نكمل الكود الطبيعي للحسابات والمودال
        let grandTotal = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
        let totalPaid = 0;
        document.querySelectorAll('.payment-input').forEach(inp => totalPaid += parseMoney(inp.value));
        
        let diff = grandTotal - totalPaid;
        
        // إذا كان المبلغ المدفوع يساوي الإجمالي تماماً (الفارق شبه معدوم)، احفظ مباشرة
        if (Math.abs(diff) < 0.01) {
            document.getElementById('purchaseForm').submit();
            return;
        }

        // إعداد بيانات المودال (تأكيد الدين أو الرصيد)
        let oldBalance = parseFloat(document.getElementById('currentSupplierBalance').value) || 0;
        let newBalance = oldBalance + diff; 

        let modalOldBalance = document.getElementById('modalOldBalance');
        modalOldBalance.innerText = formatNum(oldBalance);
        modalOldBalance.className = "fw-bold " + (oldBalance >= 0 ? "text-danger" : "text-success");
        
        document.getElementById('modalGrandTotal').innerText = formatNum(grandTotal);
        document.getElementById('modalPaid').innerText = formatNum(totalPaid);
        document.getElementById('modalDiff').innerText = formatNum(diff);

        let balText = "";
        let colorClass = "";
        
        if (newBalance > 0) {
            colorClass = "text-danger";
            balText = `سيصبح له علينا: ${formatNum(newBalance)} (دين)`;
        } else if (newBalance < 0) {
            colorClass = "text-success";
            balText = `سيصبح لنا عنده: ${formatNum(Math.abs(newBalance))} (رصيد)`;
        } else {
            colorClass = "text-primary";
            balText = "الرصيد سيصبح 0.00 (خالص)";
        }

        let modalNewBalance = document.getElementById('modalNewBalance');
        modalNewBalance.innerText = balText;
        modalNewBalance.className = "fs-4 fw-bold " + colorClass;

        let msg = "";
        if (diff > 0) {
            msg = "⚠️ المبلغ المدفوع أقل من الفاتورة. سيتم إضافة الفارق إلى الدين.";
        } else {
            msg = "⚠️ المبلغ المدفوع أكبر من الفاتورة. سيتم إضافة الفارق كرصيد لك.";
        }
        document.getElementById('modalMessage').innerText = msg;

        var myModal = new bootstrap.Modal(document.getElementById('balanceConfirmModal'));
        myModal.show();
    }
// --- دالة فتح نافذة إضافة المنتج ---
    function openCreateProductModal() {
        const frame = document.getElementById('createProductFrame');
        
        // 👇 التعديل هنا: أضفنا ?iframe=1 في نهاية الرابط
        frame.src = "{{ url('/store-owner/products/create') }}?iframe=1"; 
        
        var myModal = new bootstrap.Modal(document.getElementById('quickProductModal'));
        myModal.show();
    }
// --- دالة فتح نافذة إضافة المورد ---
    function openCreateSupplierModal() {
        const frame = document.getElementById('createSupplierFrame');
        // تأكد من ضبط الرابط الصحيح لإضافة مورد (type=supplier عادة ما يستخدم في أنظمة ERP)
        // أضفنا iframe=1 لإخفاء القوائم
        frame.src = "{{ route('store.contacts.create') }}?type=supplier&iframe=1"; 
        
        var myModal = new bootstrap.Modal(document.getElementById('addSupplierModal'));
        myModal.show();
    }
</script>

@endsection