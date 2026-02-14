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

    /* 1. تنسيق الجدول الذكي - Dynamic Table */
    #itemsTable {
        table-layout: auto !important; /* يسمح للجدول بالتوسع بناءً على المحتوى */
        width: 100%;
    }

    #itemsTable th {
        background-color: #343a40 !important;
        color: white;
        white-space: nowrap; /* منع العناوين من الالتفاف لضمان العرض الأدنى */
        text-align: center;
        padding: 10px 5px !important;
        font-weight: 600;
    }

    #itemsTable td {
        vertical-align: middle !important;
        padding: 6px 4px !important;
    }

    /* 2. التحكم في عرض الأعمدة بالحد الأدنى */
    /* عمود المنتج يأخذ المساحة المتبقية مع التفاف النص */
    .product-col {
        white-space: normal !important;
        width: 130px !important; 
        min-width: 100px !important;
        max-width: 150px !important;
        line-height: 1.2;
        text-align: right !important;
        font-size: 0.82rem;
    }

    /* باقي الأعمدة تأخذ أقل عرض ممكن يكفي لمحتواها */
    .col-shrink {
        width: 1%;
        white-space: nowrap;
    }
    
    /* تنسيق الصور والمصغرات */
    .product-thumb { width: 35px; height: 35px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6; }

    /* تحسين شكل المدخلات الديناميكية */
    #itemsTable .form-control, #itemsTable .form-select {
        height: 30px;
        padding: 2px 4px !important;
        font-size: 0.8rem;
        border-radius: 5px;
        border: 1px solid #ced4da;
        width: 100%; 
        min-width: 40px;
        field-sizing: content; 
    }
    
    /* استثناءات لتحسين الوضوح */
    .input-barcode { min-width: 115px !important; }
    .input-expiry  { min-width: 110px !important; } 
    .input-qty     { min-width: 45px !important; }
    .input-price   { min-width: 75px !important; }
    .input-total   { min-width: 95px !important; background-color: #fcfcfc !important; color: #000 !important; }
    .input-unit    { min-width: 85px !important; }
    
    .discount-group .form-control { width: 60% !important; border-left: 0 !important; }
    .discount-group .form-select { width: 40% !important; padding: 0 !important; font-size: 0.75rem; border-right: 0 !important; background-color: #f8f9fa; }
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
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> {{ __('فاتورة شراء جديدة') }} </h5>
                        <a href="{{ route('store.purchases.index') }}" class="btn btn-sm btn-light text-primary fw-bold"> {{ __('العودة') }} </a>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"> {{ __('المورد') }} <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <div class="input-group">
    {{-- ✅ هذا هو الزر الذي كان مفقوداً --}}
    <button type="button" class="btn btn-success" onclick="openCreateSupplierModal()" title="{{ __('مورد جديد') }}"><i class="fas fa-plus"></i></button>
    
    <input type="text" id="supplierSearchInput" class="form-control" placeholder="{{ __('ابحث عن مورد...') }}" autocomplete="off">
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
                                <label class="form-label fw-bold"> {{ __('تاريخ وتوقيت الفاتورة') }} </label>
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
                                {{-- تم التعديل لاستدعاء دالة فتح الإطار - مع دعم المطاعم --}}
@if(Auth::user()->store->type == 'restaurant')
<button type="button" class="btn btn-success" onclick="openCreateMealModal()"><i class="fas fa-plus-circle me-1"></i> وجبة أو مكون خام</button>
@else
<button type="button" class="btn btn-success" onclick="openCreateProductModal()"><i class="fas fa-plus-circle me-1"></i> منتج جديد</button>
@endif
                            </div>
                            <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; top: 100%; display: none;"></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle mb-0" id="itemsTable">
                                <thead class="bg-dark text-white small">
                                    <tr>
                                        <th class="col-shrink">صورة</th>
                                        <th class="product-col">المنتج</th>
                                        <th class="col-shrink"> {{ __('الباركود') }} </th>
                                        <th class="col-shrink">الوحدة</th>
                                        <th class="col-shrink">الكمية</th>
                                        <th class="col-shrink">سعر الشراء</th>
                                        <th class="col-shrink"> {{ __('الربح %') }} </th> 
                                        <th class="col-shrink">الخصم</th>
                                        <th class="col-shrink">سعر المبيع</th>
                                        <th class="col-shrink">تاريخ الانتهاء</th>
                                        <th class="col-shrink">تنبيه</th>
                                        <th class="col-shrink">الضريبة</th>
                                        <th class="col-shrink">الإجمالي</th>
                                        <th class="col-shrink"></th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
                            <div id="emptyState" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-secondary opacity-50"></i>
                                <p> {{ __('قم بالبحث لإضافة منتجات') }} </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الحسابات والدفع --}}
            <div class="col-lg-5 ms-auto">
                <div class="card shadow border-primary">
                    <div class="card-header bg-primary bg-opacity-10 py-2">
                        <h6 class="mb-0 fw-bold text-primary"> {{ __('ملخص الدفع') }} </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span> {{ __('المجموع الفرعي:') }} </span> <span id="subTotalDisplay" class="fw-bold">0.00</span>
                        </div>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text"> {{ __('خصم إضافي') }} </span>
                            <input type="text" inputmode="decimal" name="discount" id="discountInput" class="form-control text-center fw-bold text-danger" value="0" oninput="calculateGrandTotal()">
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 mb-3">
                            <span class="fs-5 fw-bold"> {{ __('الصافي النهائي:') }} </span>
                            <span id="grandTotalDisplay" class="fs-4 fw-bold text-primary">0.00</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small text-muted mb-1"> {{ __('المدفوعات') }} </label>
                            <div id="paymentsContainer">
                                <div class="input-group mb-2 payment-row">
                                    <select name="payments[0][method]" class="form-select" style="max-width: 120px;">
                                        <option value="cash"> {{ __('💰 نقدي') }} </option>
                                        <option value="card"> {{ __('💳 بطاقة') }} </option>
                                        <option value="bank"> {{ __('🏦 تحويل') }} </option>
                                    </select>
                                    <input type="text" inputmode="decimal" name="payments[0][amount]" class="form-control text-center payment-input" value="0" oninput="calculateGrandTotal()">
                                    <button type="button" class="btn btn-outline-success" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="alert p-2 text-center fw-bold" id="balanceAlert" style="display: none;">
                            <span id="balanceLabel"> {{ __('المتبقي:') }} </span> <span id="balanceAmount">0.00</span>
                        </div>
                        
                        <button type="button" onclick="checkBalanceAndSubmit()" class="btn btn-primary w-100 btn-lg mt-3"><i class="fas fa-save me-2"></i> {{ __('حفظ الفاتورة') }} </button>
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
            <div class="modal-body text-center" dir="rtl">
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
                <button type="button" id="confirmSaveBtn" class="btn btn-primary px-5" onclick="ajaxSubmitPurchase()">موافق وحفظ</button>
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
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> {{ __('إضافة مورد جديد') }} </h5>
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
                <h5 class="modal-title"><i class="fas fa-cube me-2"></i> {{ __('إضافة منتج جديد') }} </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                {{-- هنا سيتم تحميل صفحة الإضافة --}}
                <iframe id="createProductFrame" src="" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>

{{-- مودال إضافة وجبة (خاص بالمطاعم) --}}
<div class="modal fade" id="quickMealModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title"><i class="fas fa-utensils me-2"></i> إضافة وجبة أو مكون جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                <iframe id="createMealFrame" src="" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
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
        console.log("Initializing Supplier Search...");
        setupSearch('supplierSearchInput', 'supplierResults', "{{ route('store.contacts.search') }}", function(s) {
            console.log("Supplier Selected:", s);
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

        // حساب الرابط ديناميكياً مع استخدام Alias جديد لتجنب الحظر
        const basePath = window.location.pathname.split('/store-owner/')[0];
        const searchUrl = `${window.location.origin}${basePath}/store-owner/core/lookup`;
        console.log('Computed Search URL (Safe Alias):', searchUrl);

        setupSearch('productSearch', 'searchResults', searchUrl, function(p) {
            console.log("Product Selected:", p);
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

        // --- 🟢 كود مراقبة نافذة إضافة الوجبة للمطاعم ---
        const mealFrame = document.getElementById('createMealFrame');
        if(mealFrame) {
            mealFrame.onload = function() {
                try {
                    const newUrl = mealFrame.contentWindow.location.href;
                    if (!newUrl.includes('create') && !newUrl.includes('edit')) {
                        console.log("✅ تم حفظ الوجبة بنجاح، الرابط الجديد: " + newUrl);
                        
                        var mealModalEl = document.getElementById('quickMealModal');
                        var modal = bootstrap.Modal.getInstance(mealModalEl);
                        if(modal) modal.hide();

                        // الوجبات والمكونات تعامل كمنتجات في الفاتورة
                        // نحاول استخراج ID
                        const match = newUrl.match(/meals\/(\d+)/);
                        // أو products إذا كان مكوناً خاماً وتم تحويله لصفحة المنتجات (يعتمد على النظام)
                        // لكن لنفترض أنه سيعود لصفحة الوجبات أو المنتجات.
                        // في نظامك، الوجبات قد تكون في جدول products أيضاً أو منفصلة.
                        // إذا كانت في products فالرابط سيكون products/id. 
                        // إذا كانت meals/id، فنحتاج endpoint للبحث عنها.
                        // لكنك قلت "وجبة أو مكون خام"، وكلاهما يخزنان كمنتجات عادةً.
                        
                        let newId = null;
                        if (match && match[1]) newId = match[1];
                        else {
                             const matchProd = newUrl.match(/products\/(\d+)/);
                             if (matchProd && matchProd[1]) newId = matchProd[1];
                        }

                        if (newId) {
                            fetch(`{{ route('store.products.search') }}?term=${newId}`) // نستخدم نفس البحث لأن الوجبات منتجات
                                .then(r => r.json())
                                .then(data => {
                                     let item = null;
                                     if(Array.isArray(data)) item = data.find(p => p.id == newId) || data[0];
                                     else item = data;

                                     if(item) {
                                         addProductRow(item);
                                         if(typeof toastr !== 'undefined') toastr.success('تم إضافة الوجبة/المكون للفاتورة');
                                     }
                                })
                                .catch(err => console.error('خطأ في جلب الوجبة', err));
                        }
                    }
                } catch (e) {
                    console.log('Access restricted or loading...');
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

    function formatNum(num) { 
        if (num === null || num === undefined || num === '') return 0;
        return parseFloat(num); 
    }

    // --- دالة إضافة صف المنتج المصححة ---
    function addProductRow(product) {
        if (!product || !product.units || product.units.length === 0) {
            console.error("Product has no units or is null:", product);
            return;
        }
        document.getElementById('emptyState').style.display = 'none';
        window.productsData[rowIdx] = product;

        // تحديد الوحدة الافتراضية
        let selectedUnitId = product.scanned_unit_id;
        if (!selectedUnitId) {
            let base = product.units.find(u => u.is_base_unit == 1) || product.units[0];
            selectedUnitId = base ? base.id : null;
        }
        if (!selectedUnitId) return;

        // حسابات التكلفة (تعديل: الاعتماد على سعر الوحدة المختارة أولاً)
        let selectedUnit = product.units.find(u => u.id == selectedUnitId);
        let selectedFactor = (selectedUnit.is_base_unit) ? 1 : (parseFloat(selectedUnit.conversion_factor) || 1);
        
        // 1. محاولة قراءة التكلفة المخزنة للوحدة المختارة
        let calculatedCost = parseFloat(selectedUnit.cost_price) || parseFloat(selectedUnit.purchase_price) || 0;

        // 2. إذا كانت صفر، نحاول استنتاجها من أكبر وحدة (المنطق القديم)
        if (calculatedCost === 0) {
            let maxUnit = product.units.reduce((prev, curr) => (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr);
            let maxUnitCost = parseFloat(maxUnit.cost_price) || parseFloat(maxUnit.purchase_price) || 0;
            let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
            
            // تكلفة الوحدة الأساسية
            let trueBaseCost = maxUnitCost / maxFactor; 
            calculatedCost = trueBaseCost * selectedFactor;
        }

        // 3. حساب تكلفة الوحدة الأساسية (للاستخدام في باقي الوحدات)
        let trueBaseCost = (selectedFactor > 0) ? (calculatedCost / selectedFactor) : 0;
        
        let initialBarcode = selectedUnit.barcode || '-';
        let sellPrice = parseFloat(selectedUnit.sale_price) || 0;
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
                    data-sell="${formatNum(u.sale_price)}" 
                    data-profit="${formatNum(u.profit_percent || 0)}"
                    data-factor="${safeFactor}" 
                    data-img="${u.image_url || ''}" 
                    ${u.id == selectedUnitId ? 'selected' : ''}>
                    ${u.unit_name}
                    </option>`;
        }).join('');

        // بناء الصف (HTML) بشكل صحيح بدون تكرار أو قطع
        tr.innerHTML = `
            <td class="col-shrink">
                <div class="d-flex flex-column align-items-center gap-1">
                    <button type="button" class="btn btn-xs btn-info text-white p-0" style="width:18px; height:18px; font-size:9px;" onclick="toggleDetails(${rowIdx})"><i class="fas fa-chevron-down"></i></button>
                    <img src="${imgUrl}" id="img_${rowIdx}" class="product-thumb" alt="">
                </div>
            </td>
            <td class="product-col">
                <input type="hidden" name="items[${rowIdx}][product_id]" value="${product.id}">
                <div class="fw-bold small">${product.name_ar}</div>
            </td>
            <td class="col-shrink"><input type="text" class="form-control form-control-sm text-center bg-white barcode-display input-barcode" id="barcode_${rowIdx}" value="${initialBarcode}" readonly></td>
            <td class="col-shrink">
                <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm unit-select input-unit" onchange="updateRowData(${rowIdx})">${optionsHtml}</select>
            </td>
            <td class="col-shrink"><input type="text" inputmode="decimal" name="items[${rowIdx}][quantity]" class="form-control form-control-sm text-center qty input-qty" value="1" oninput="calcTotals(${rowIdx})" onfocus="this.select()"></td>
            <td class="col-shrink"><input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price text-danger fw-bold input-price" value="${parseFloat(calculatedCost.toFixed(4))}" oninput="syncSubUnits(${rowIdx}, 'purchase')" onfocus="this.select()"></td>
            <td class="col-shrink"><input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary input-profit" value="${formatNum(profitPercent)}" oninput="calcSellPrice(${rowIdx})" onfocus="this.select()"></td>
            <td class="col-shrink">
                <div class="input-group input-group-sm discount-group input-discount">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="0" oninput="calcTotals(${rowIdx})">
                    <select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" onchange="calcTotals(${rowIdx})">
                        <option value="fixed">₺</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </td>
            <td class="col-shrink">
                <input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control form-control-sm text-center sell text-success fw-bold input-price" value="${formatNum(sellPrice)}" oninput="calcProfitPercent(${rowIdx})" onfocus="this.select()">
                <div class="main-warning-container mt-1" style="min-height:18px;"></div>
            </td>

            <td class="col-shrink">
                <input type="date" name="items[${rowIdx}][expiry_date]" class="form-control form-control-sm text-center px-1 input-expiry" title="تاريخ الانتهاء">
            </td>

            <td class="col-shrink">
                <input type="number" name="items[${rowIdx}][alert_days]" class="form-control form-control-sm text-center text-danger fw-bold px-1" style="width: 50px;" value="10" placeholder="10" title="{{ __('نبهني قبل X يوم') }}">
            </td>
            
            <td class="col-shrink"><select name="items[${rowIdx}][tax]" class="form-select form-select-sm tax bg-warning bg-opacity-10" onchange="calcTotals(${rowIdx})">${taxOptionsHtml}</select></td>
            <td class="col-shrink"><input type="text" class="form-control form-control-sm text-center bg-light fw-bold total input-total" readonly></td>
            <td class="col-shrink text-center"><button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="removeRow(${rowIdx})"><i class="fas fa-times"></i></button></td>
        `;
        
        document.getElementById('tableBody').appendChild(tr);

        // صف التفاصيل المخفية (مقسم لعمودين: وحدات + سجل)
        const detailsTr = document.createElement('tr');
        detailsTr.id = `details_${rowIdx}`;
        detailsTr.style.display = 'none';
        detailsTr.className = "bg-light";
        detailsTr.innerHTML = `
            <td colspan="14">
                <div class="p-3 border rounded bg-white">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-sitemap"></i> تحديث الوحدات المرتبطة</h6>
                            <div id="related_units_container_${rowIdx}"></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-success mb-2"><i class="fas fa-history"></i> سجل آخر 5 مشتريات</h6>
                            <div id="history_container_${rowIdx}" class="small">
                                <div class="text-center text-muted p-2"><i class="fas fa-spinner fa-spin"></i> جاري الجلب...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>`;
        document.getElementById('tableBody').appendChild(detailsTr);

        renderRelatedUnits(rowIdx, selectedUnitId);
        renderHistory(rowIdx, product.id); // ✅ جلب السجل
        calcTotals(rowIdx); 
        rowIdx++;
    }

    // 🟢 دالة جلب ورسم السجل
    function renderHistory(idx, productId) {
        console.log(`[History] Fetching for Product ID: ${productId} (Row: ${idx})`);

        if (!productId) {
            console.error("[History] Product ID is missing!");
            document.getElementById(`history_container_${idx}`).innerHTML = '<span class="text-danger">معرف المنتج مفقود</span>';
            return;
        }

        // استخدام رابط مباشر للقضاء على مشاكل الـ replacement
        // نستخدم الرابط الأساسي ثم نضيف الـ ID
        let baseUrl = "{{ route('store.purchases.history', ['id' => ':id']) }}";
        let url = baseUrl.replace(':id', productId);
        
        console.log(`[History] Request URL: ${url}`);

        fetch(url)
            .then(async res => {
                if (!res.ok) {
                    throw new Error("HTTP Status: " + res.status);
                }
                return res.json();
            })
            .then(data => {
                console.log(`[History] Data received:`, data);
                let container = document.getElementById(`history_container_${idx}`);
                if (data.length === 0) {
                    container.innerHTML = '<div class="alert alert-secondary p-1 m-0 text-center">لا يوجد سجل مشتريات سابق</div>';
                    return;
                }

                let html = `
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>التاريخ</th>
                                <th> {{ __('المورد') }} </th>
                                <th>الوحدة</th>
                                <th>السعر</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                data.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.date}</td>
                            <td class="text-truncate" style="max-width: 100px;" title="${item.supplier}">${item.supplier}</td>
                            <td>${item.unit} (${item.qty})</td>
                            <td class="fw-bold">${formatNum(item.price)}</td>
                        </tr>`;
                });

                html += '</tbody></table>';
                container.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById(`history_container_${idx}`).innerHTML = '<span class="text-danger">خطأ في جلب السجل</span>';
            });
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
            
            let uSell = parseFloat(u.sale_price) || 0;
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
                            <span class="input-group-text bg-light text-muted"> {{ __('شراء') }} </span>
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
                            <span class="input-group-text"> {{ __('بيع') }} </span>
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

    // الدالة المحلية setupSearch تم حذفها لاستخدام الدالة العامة المعرفة في app.blade.php


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
                <option value="cash"> {{ __('💰 نقدي') }} </option>
                <option value="card"> {{ __('💳 بطاقة') }} </option>
                <option value="bank"> {{ __('🏦 تحويل') }} </option>
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
            ajaxSubmitPurchase();
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

    function ajaxSubmitPurchase() {
        const form = document.getElementById('purchaseForm');
        const formData = new FormData(form);
        
        const btn = document.getElementById('confirmSaveBtn');
        const originalText = btn ? btn.innerHTML : '';
        if(btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري الحفظ...';
            btn.disabled = true;
        }

        Swal.fire({title: 'جاري حفظ الفاتورة...', didOpen: () => Swal.showLoading()});

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.close();
                // إذا كان هناك بيانات واتساب أو إيميل، اعرض خيارات المشاركة
                if (data.whatsapp_data) {
                    Swal.fire({
                        title: 'تم الحفظ بنجاح',
                        text: 'كيف ترغب في مشاركة الفاتورة مع المورد؟',
                        icon: 'success',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fab fa-whatsapp"></i> واتساب',
                        denyButtonText: '<i class="fas fa-envelope"></i> إيميل',
                        cancelButtonText: 'إغلاق ومتابعة',
                        confirmButtonColor: '#25d366',
                        denyButtonColor: '#007bff',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            triggerWhatsappPrompt(
                                data.whatsapp_data.phone, 
                                data.whatsapp_data.message, 
                                "إرسال فاتورة المشتريات للمورد",
                                data.whatsapp_data.pdf_url,
                                data.whatsapp_data.pdf_filename
                            );
                        } else if (result.isDenied) {
                            triggerEmailPrompt(
                                data.supplier_email || '', 
                                data.whatsapp_data.message, 
                                "فاتورة مشتريات - " + (data.invoice_no || ''), 
                                data.pdf_url,
                                data.pdf_filename
                            );
                        } else {
                            window.location.href = "{{ route('store.purchases.index') }}";
                        }
                        
                        // نراقب إغلاق المودالات للعودة للفهرس
                        $(document).one('hidden.bs.modal', '#globalWhatsappModal, #globalEmailModal', function() {
                            window.location.href = "{{ route('store.purchases.index') }}";
                        });
                    });
                } else {
                    Swal.fire({icon: 'success', title: 'تم الحفظ بنجاح', timer: 1500, showConfirmButton: false})
                    .then(() => {
                        window.location.href = "{{ route('store.purchases.index') }}";
                    });
                }
            } else {
                Swal.fire({icon: 'error', title: 'خطأ', text: data.message || 'حدث خطأ غير متوقع'});
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({icon: 'error', title: 'خطأ', text: 'فشل الاتصال بالسيرفر'});
        })
        .finally(() => {
            if(btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
// --- دالة فتح نافذة إضافة المنتج ---
    function openCreateProductModal() {
        console.log("Opening Product Modal...");
        const frame = document.getElementById('createProductFrame');
        if (!frame) return console.error("createProductFrame not found");
        
        frame.src = "{{ url('/store-owner/products/create') }}?iframe=1"; 
        
        const modalEl = document.getElementById('quickProductModal');
        if (!modalEl) return console.error("quickProductModal not found");

        if (typeof bootstrap !== 'undefined') {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            myModal.show();
        } else {
            console.error("Bootstrap is not defined!");
            alert("خطأ في تحميل مكتبة Bootstrap");
        }
    }

// --- دالة فتح نافذة إضافة المورد ---
    function openCreateSupplierModal() {
        console.log("Opening Supplier Modal...");
        const frame = document.getElementById('createSupplierFrame');
        if (!frame) return console.error("createSupplierFrame not found");

        frame.src = "{{ route('store.contacts.create') }}?type=supplier&iframe=1"; 
        
        const modalEl = document.getElementById('addSupplierModal');
        if (!modalEl) return console.error("addSupplierModal not found");

        if (typeof bootstrap !== 'undefined') {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            myModal.show();
        } else {
            console.error("Bootstrap is not defined!");
            alert("خطأ في تحميل مكتبة Bootstrap");
        }
    }

    // --- دالة فتح نافذة إضافة وجبة (للمطاعم) ---
    function openCreateMealModal() {
        console.log("Opening Meal Modal...");
        const frame = document.getElementById('createMealFrame');
        if (!frame) return console.error("createMealFrame not found");

        frame.src = "{{ route('store.meals.create') }}?iframe=1"; 
        
        const modalEl = document.getElementById('quickMealModal');
        if (!modalEl) return console.error("quickMealModal not found");

        if (typeof bootstrap !== 'undefined') {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            myModal.show();
        } else {
            console.error("Bootstrap is not defined!");
            alert("خطأ في تحميل مكتبة Bootstrap");
        }
    }
</script>

@endsection