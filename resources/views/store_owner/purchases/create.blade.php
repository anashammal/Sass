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
                            <div class="col-md-2">
                                <label class="form-label fw-bold"> {{ __('العملة') }} </label>
                                <select name="currency_id" id="currency_id" class="form-select" onchange="onInvoiceCurrencyChange(this)">
                                    @foreach($currencies as $cur)
                                        <option value="{{ $cur->id }}"
                                            data-code="{{ $cur->code }}"
                                            data-symbol="{{ $cur->symbol ?? $cur->code }}"
                                            data-is-base="{{ $cur->id == optional($baseCurrency)->id ? '1' : '0' }}"
                                            {{ $cur->id == optional($baseCurrency)->id ? 'selected' : '' }}>
                                            {{ $cur->code }} — {{ $cur->name_ar ?? $cur->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="exchange_rate" id="invoice_exchange_rate" value="1">
                                <div id="invoice_rate_info" class="small text-info mt-1 d-none" style="font-size: 0.7rem;">
                                    {{ __('سعر الصرف:') }} 1 <span id="selected_curr_code"></span> = <span id="selected_curr_rate">1</span> {{ optional($baseCurrency)->code }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold"> {{ __('رقم الفاتورة') }} </label>
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
                            <span> {{ __('المجموع الفرعي:') }} </span> 
                            <div><span id="subTotalDisplay" class="fw-bold">0.00</span> <span class="text-muted small">{{ optional($baseCurrency)->symbol ?? optional($baseCurrency)->code }}</span></div>
                        </div>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text"> {{ __('خصم إضافي') }} </span>
                            <input type="text" inputmode="decimal" name="discount" id="discountInput" class="form-control text-center fw-bold text-danger" value="0" oninput="calculateGrandTotal()">
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 mb-3">
                            <span class="fs-5 fw-bold"> {{ __('الصافي النهائي:') }} </span>
                            <div><span id="grandTotalDisplay" class="fs-4 fw-bold text-primary">0.00</span> <span class="fw-bold text-primary">{{ optional($baseCurrency)->symbol ?? optional($baseCurrency)->code }}</span></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small text-muted mb-1"> {{ __('المدفوعات') }} </label>
                            <div id="paymentsContainer">
                                <div class="payment-row mb-2">
                                    <div class="input-group mb-1">
                                        <select name="payments[0][method]" class="form-select method-select" style="max-width: 120px;">
                                            <option value="cash"> {{ __('💰 نقدي') }} </option>
                                            <option value="card"> {{ __('💳 بطاقة') }} </option>
                                            <option value="bank"> {{ __('🏦 تحويل') }} </option>
                                        </select>
                                        <input type="text" inputmode="decimal" name="payments[0][amount]" class="form-control text-center payment-input" value="0" oninput="calculateGrandTotal()">
                                        <select class="form-select currency-select pay-currency" style="max-width:110px;" onchange="onPayCurrencyChange(this, 0)">
                                            <option value="{{ optional($baseCurrency)->id }}" data-rate="1" data-is-base="1" selected>{{ optional($baseCurrency)->code }}</option>
                                            @foreach($currencies as $cur)
                                                @if(!$baseCurrency || $cur->id != $baseCurrency->id)
                                                <option value="{{ $cur->id }}" data-rate="1" data-is-base="0">{{ $cur->code }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-success" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                                    </div>
                                    {{-- حقول مخفية لإرسال العملة وسعر الصرف --}}
                                    <input type="hidden" name="payments[0][currency_id]" class="pay-currency-id" value="{{ optional($baseCurrency)->id }}">
                                    <input type="hidden" name="payments[0][exchange_rate]" class="pay-rate-hidden" value="1">
                                    {{-- حقل المعادل بالعملة الأساسية (يظهر عند اختيار عملة أخرى) --}}
                                    <div class="rate-row d-none">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text text-muted small">يعادل ({{ optional($baseCurrency)->code }})</span>
                                            <input type="text" readonly class="form-control bg-light text-center fw-bold rate-input" value="0.00" placeholder="المعادل">
                                            <span class="input-group-text rate-note small text-info"></span>
                                        </div>
                                    </div>
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

    // Currency setup
    const defaultCurrencyCode = "{{ optional($baseCurrency)->code }}";
    const baseCurrencyId = "{{ optional($baseCurrency)->id }}";

    // --- عند تحميل الصفحة ---
    document.addEventListener("DOMContentLoaded", function() {
        console.log("✅ Main Script Loaded");

        // إعداد بحث الموردين (مع الاختيار التلقائي)
        console.log("Initializing Supplier Search...");
        
        // Currency change listener
        document.getElementById('currency_id').addEventListener('change', function() {
            let selectedCode = this.options[this.selectedIndex].text.split(' — ')[0];
            document.querySelectorAll('.currency-label').forEach(el => el.innerText = selectedCode);
            // Optionally, we could show an exchange rate warning here if it's different from base currency
        });

        // ✅ بحث المورد - مع رقم الهاتف والأسهم
        (function() {
            const supplierInput = document.getElementById('supplierSearchInput');
            const supplierResultsEl = document.getElementById('supplierResults');
            if (!supplierInput || !supplierResultsEl) { console.error('Supplier search elements missing!'); return; }

            let supTimer;
            let activeIndex = -1;

            function selectSupplier(item) {
                supplierInput.value = item.contact_name || item.company_name;
                document.getElementById('supplierId').value = item.id;
                let balance = parseFloat(item.current_balance || 0);
                document.getElementById('currentSupplierBalance').value = balance;
                const displayDiv = document.getElementById('supplierBalanceDisplay');
                if (balance > 0) displayDiv.innerHTML = `<span class="text-danger fs-3"><i class="fas fa-arrow-down"></i> له علينا: ${formatNum(balance)}</span>`;
                else if (balance < 0) displayDiv.innerHTML = `<span class="text-success fs-3"><i class="fas fa-arrow-up"></i> لنا عنده: ${formatNum(Math.abs(balance))}</span>`;
                else displayDiv.innerHTML = `<span class="text-primary fs-3">الرصيد: 0.00</span>`;
                supplierResultsEl.style.display = 'none';
                activeIndex = -1;
            }

            supplierInput.addEventListener('input', function() {
                clearTimeout(supTimer);
                activeIndex = -1;
                const term = this.value.trim();
                if (term.length < 1) { supplierResultsEl.style.display = 'none'; return; }
                supTimer = setTimeout(function() {
                    fetch('{{ route("store.contacts.search") }}?term=' + encodeURIComponent(term), {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    })
                    .then(r => r.json())
                    .then(data => {
                        supplierResultsEl.innerHTML = '';
                        if (!Array.isArray(data) || data.length === 0) { supplierResultsEl.style.display = 'none'; return; }
                        // اختيار تلقائي عند نتيجة واحدة
                        if (data.length === 1) { selectSupplier(data[0]); return; }
                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.className = 'list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center';
                            const name = item.contact_name || item.company_name || '';
                            const phone = item.phone || '';
                            a.innerHTML = `<span class="fw-bold">${name}</span>${phone ? `<small class="text-muted ms-2"><i class="fas fa-phone-alt"></i> ${phone}</small>` : ''}`;
                            a.addEventListener('click', function() { selectSupplier(item); });
                            supplierResultsEl.appendChild(a);
                        });
                        supplierResultsEl.style.display = 'block';
                    })
                    .catch(err => console.error('Supplier search error:', err));
                }, 300);
            });

            // ⬆️⬇️ التنقل بالأسهم
            supplierInput.addEventListener('keydown', function(e) {
                const items = supplierResultsEl.querySelectorAll('a');
                if (!items.length) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
                    items[activeIndex].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
                    items[activeIndex].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter' && activeIndex >= 0) {
                    e.preventDefault();
                    items[activeIndex].click();
                } else if (e.key === 'Escape') {
                    supplierResultsEl.style.display = 'none';
                    activeIndex = -1;
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target !== supplierInput) supplierResultsEl.style.display = 'none';
            });
        })();

        // ✅ بحث المنتج - مباشر بدون أي تبعية
        (function() {
            const productInput = document.getElementById('productSearch');
            const productResultsEl = document.getElementById('searchResults');
            if (!productInput || !productResultsEl) { console.error('Product search elements missing!'); return; }

            let prodTimer;
            productInput.addEventListener('input', function() {
                clearTimeout(prodTimer);
                const term = this.value.trim();
                if (term.length < 1) { productResultsEl.style.display = 'none'; return; }
                prodTimer = setTimeout(function() {
                    fetch('{{ route("store.core.lookup") }}?term=' + encodeURIComponent(term), {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    })
                    .then(r => r.json())
                    .then(data => {
                        productResultsEl.innerHTML = '';
                        if (!Array.isArray(data) || data.length === 0) { productResultsEl.style.display = 'none'; return; }

                        // اختيار تلقائي عند نتيجة واحدة (باركود أو اسم)
                        if (data.length === 1) {
                            productInput.value = '';
                            productResultsEl.style.display = 'none';
                            addProductRow(data[0]);
                            productInput.value = '';
                            productInput.focus();
                            return;
                        }

                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.className = 'list-group-item list-group-item-action cursor-pointer';
                            a.innerHTML = `<strong>${item.name || ''}</strong> <small class="text-muted">${item.sku || ''}</small>`;
                            a.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                productInput.value = '';
                                productResultsEl.style.display = 'none';
                                addProductRow(item);
                                productInput.value = '';
                                productInput.focus();
                            });
                            productResultsEl.appendChild(a);
                        });
                        productResultsEl.style.display = 'block';
                    })
                    .catch(err => console.error('Product search error:', err));
                }, 300);
            });
            document.addEventListener('click', function(e) {
                if (e.target !== productInput) productResultsEl.style.display = 'none';
            });
        })();
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
        let val = parseFloat(num) || 0;
        return parseFloat(val.toFixed(4)); 
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
        
        // 1. حساب التكلفة بناءً على العملة (جديد)
        let preferredPrice = parseFloat(selectedUnit.cost_price) || parseFloat(selectedUnit.purchase_price) || 0;
        let pCurrId = selectedUnit.purchase_currency_id || baseCurrencyId;
        let pRate = (pCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.purchase_exchange_rate) || parseFloat(selectedUnit.store_custom_purchase_rate) || ratesMap[pCurrId]?.exchange_rate || 1);
        
        // התكلفة بالعملة الأساسية (TRY)
        let priceInBase = preferredPrice * pRate;
        // جلب سعر صرف الفاتورة
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        
        // حساب التكلفة بعملة الفاتورة - هي ما سيتم إدخاله في الحقل الأحمر
        let calculatedCost = priceInBase / invRate; 

        // 2. إذا كانت صفر، نحاول استنتاجها من أكبر وحدة (المنطق الاحتياطي)
        if (calculatedCost === 0) {
            let maxUnit = product.units.reduce((prev, curr) => (parseFloat(prev.conversion_factor) > parseFloat(curr.conversion_factor)) ? prev : curr);
            let maxUnitCost = parseFloat(maxUnit.cost_price) || parseFloat(maxUnit.purchase_price) || 0;
            let maxFactor = parseFloat(maxUnit.conversion_factor) || 1;
            
            let mCurrId = maxUnit.purchase_currency_id || baseCurrencyId;
            let mRate = (mCurrId == baseCurrencyId) ? 1 : (parseFloat(maxUnit.purchase_exchange_rate) || parseFloat(maxUnit.store_custom_purchase_rate) || ratesMap[mCurrId]?.exchange_rate || 1);
            
            // تكلفة الوحدة الأساسية بالعملة الأساسية
            calculatedCost = (maxUnitCost * mRate / maxFactor) * selectedFactor;
        }

        // 3. حساب تكلفة الوحدة الأساسية (بالليرة) للاستخدام في باقي الوحدات
        let trueBaseCost = (selectedFactor > 0) ? (calculatedCost / selectedFactor) : 0;
        
        let initialBarcode = selectedUnit.barcode || '-';
        
        // --- تصحيح تحويل سعر البيع إلى عملة الفاتورة ---
        let rawSellPrice = parseFloat(selectedUnit.sale_price) || 0;
        let sCurrId = selectedUnit.sell_currency_id || baseCurrencyId;
        let sRate = (sCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.sell_exchange_rate) || parseFloat(selectedUnit.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
        // سعر البيع بالعملة الأساسية (TRY)
        let sellInBase = rawSellPrice * sRate;
        // سعر البيع بعملة الفاتورة - هو ما سيتم إدخاله في الحقل الأخضر
        let sellPrice = sellInBase / invRate;

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

            // تحويل سعر البيع لكل وحدة أيضاً
            let rSell = parseFloat(u.sale_price) || 0;
            let rCurrId = u.sell_currency_id || baseCurrencyId;
            let rRate = (rCurrId == baseCurrencyId) ? 1 : (ratesMap[rCurrId]?.exchange_rate || 1);
            let rSellInBase = rSell * rRate;
            let rSellInInv = rSellInBase / invRate; // بعملة الفاتورة

            let rProfit = (mathPrice > 0) ? ((rSellInInv - mathPrice) / mathPrice) * 100 : 0;

            return `<option value="${u.id}" 
                    data-barcode="${u.barcode || '-'}" 
                    data-price="${mathPrice.toFixed(4)}" 
                    data-sell="${formatNum(rSellInInv)}" 
                    data-profit="${formatNum(rProfit)}"
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
                <div class="fw-bold small">${product.name}</div>
            </td>
            <td class="col-shrink"><input type="text" class="form-control form-control-sm text-center bg-white barcode-display input-barcode" id="barcode_${rowIdx}" value="${initialBarcode}" readonly></td>
            <td class="col-shrink">
                <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm unit-select input-unit" onchange="updateRowData(${rowIdx})">${optionsHtml}</select>
            </td>
            <td class="col-shrink"><input type="text" inputmode="decimal" name="items[${rowIdx}][quantity]" class="form-control form-control-sm text-center qty input-qty" value="1" oninput="calcTotals(${rowIdx})" onfocus="this.select()"></td>
            <td class="col-shrink">
                <div class="input-group input-group-sm">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control text-center price text-danger fw-bold input-price" value="${parseFloat(calculatedCost.toFixed(4))}" oninput="syncSubUnits(${rowIdx}, 'purchase')" onfocus="this.select()">
                    <span id="inv-badge-${rowIdx}" class="input-group-text py-0 px-1 small text-danger fw-bold" style="display:none;"></span>
                </div>
                <div class="cost-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1;">
                   <span id="cost-base-${rowIdx}" class="d-block text-info fw-bold"></span>
                </div>
            </td>
            <td class="col-shrink"><input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary input-profit" value="${formatNum(profitPercent)}" oninput="calcSellPrice(${rowIdx}, this.value)" onfocus="this.select()"></td>
            <td class="col-shrink">
                <div class="input-group input-group-sm discount-group input-discount">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="0" oninput="calcTotals(${rowIdx})">
                    <select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" onchange="calcTotals(${rowIdx})">
                        <option value="fixed" class="currency-label">{{ optional($baseCurrency)->symbol ?? optional($baseCurrency)->code }}</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </td>
            <td class="col-shrink">
                <div class="input-group input-group-sm">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control text-center sell text-success fw-bold input-price" value="${formatNum(sellPrice)}" oninput="calcProfitPercent(${rowIdx}, this.value)" onfocus="this.select()">
                    <span id="sell-badge-${rowIdx}" class="input-group-text py-0 px-1 small text-success fw-bold" style="display:none;"></span>
                </div>
                <div class="sell-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1;">
                   <span id="sell-base-${rowIdx}" class="d-block text-info fw-bold"></span>
                </div>
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
        updateDualPriceDisplay(rowIdx); // ✅ تحديث عرض العملتين
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
        updateDualPriceDisplay(idx);
        calcTotals(idx);
    }

    // updateDualPriceDisplay is defined below (correct version using getElementById)

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
            let rawSell = parseFloat(u.sale_price) || 0;
            let sCurrId = u.sell_currency_id || "{{ optional($baseCurrency)->id }}";
            let sRate = (sCurrId == "{{ optional($baseCurrency)->id }}") ? 1 : (parseFloat(u.sell_exchange_rate) || parseFloat(u.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
            
            let sInBase = rawSell * sRate;
            let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
            let uSell = sInBase / invRate;

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

    // --- Search Logic: Using global window.setupSearch from app.blade.php ---
    // (No local definition needed - global version handles this)



    // ✅ عند تغيير سعر المبيع → تحديث نسبة الربح والعملة الأساسية فوراً
    function calcProfitPercent(idx, rawVal) {
        let row = document.getElementById(`row_${idx}`);
        if (!row) return;
        let cost = parseFloat(row.querySelector('.price').value) || 0;
        let sell = parseFloat(rawVal) || 0;
        // كتب القيمة الجديدة في الحقل بشكل صريح
        row.querySelector('.sell').value = sell;
        let profitEl = row.querySelector('.profit');
        if (cost > 0 && profitEl) {
            profitEl.value = formatNum(((sell - cost) / cost) * 100);
        }
        calcTotals(idx);
    }

    // ✅ عند تغيير نسبة الربح → تحديث سعر المبيع والعملة الأساسية فوراً
    function calcSellPrice(idx, rawVal) {
        let row = document.getElementById(`row_${idx}`);
        if (!row) return;
        let cost = parseFloat(row.querySelector('.price').value) || 0;
        let profit = parseFloat(rawVal) || 0;
        let sellEl = row.querySelector('.sell');
        let newSell = cost * (1 + profit / 100);
        if (sellEl) sellEl.value = formatNum(newSell);
        // تحديث فوري للمعادل بالعملة الافتراضية
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        let bCode = "{{ optional($baseCurrency)->code }}";
        let sellBaseEl = document.getElementById('sell-base-' + idx);
        if (sellBaseEl) sellBaseEl.innerText = formatNum(newSell * invRate) + ' ' + bCode;
        calcTotals(idx);
    }

    // --- بقية الدوال (calcTotals, removeRow, etc...) ضروري تكون موجودة ---
    function calcTotals(idx) { 
        let row = document.getElementById(`row_${idx}`);
        if(!row) return;
        let qty = parseMoney(row.querySelector('.qty').value);
        let priceInvoice = parseMoney(row.querySelector('.price').value);
        let invoiceRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;

        let taxRate = parseMoney(row.querySelector('.tax').value);
        let discountVal = parseMoney(row.querySelector('.discount').value);
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

        // ✅ تحديث نسبة الربح عند تغيير سعر الشراء أو البيع
        let sellPriceInvoice = parseFloat(row.querySelector('.sell').value) || 0;
        let currentProfit = 0;
        let profitEl = row.querySelector('.profit');
        if (priceInvoice > 0) {
            currentProfit = sellPriceInvoice > 0 ? ((sellPriceInvoice - priceInvoice) / priceInvoice) * 100 : 0;
            if (profitEl) profitEl.value = formatNum(currentProfit);
        } else if (profitEl) {
             currentProfit = parseFloat(profitEl.value) || 0;
        }

        // 🟢 منطق إخفاء/إظهار التحذير المباشر للربح
        let select = row.querySelector('.unit-select');
        let originalMainProfit = select ? (parseFloat(select.options[select.selectedIndex].getAttribute('data-profit')) || 0) : 0;
        let mainWarningDiv = row.querySelector('.main-warning-container');
        if (mainWarningDiv) {
            mainWarningDiv.innerHTML = ''; // مسح القديم
            if (currentProfit <= 0 && priceInvoice > 0) {
                mainWarningDiv.innerHTML = `<span class="text-danger flash-warning">خسارة ⚠️</span>`;
            } else if (currentProfit < originalMainProfit - 0.1 && priceInvoice > 0) {
                mainWarningDiv.innerHTML = `<span class="text-warning text-dark flash-warning">📉 انخفاض الربح</span>`;
            }
        }

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
            let pCurrId = selectedUnit.purchase_currency_id || "{{ optional($baseCurrency)->id }}";
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
            let rawSellPrice = parseFloat(selectedUnit.sale_price) || 0;
            let sCurrId = selectedUnit.sell_currency_id || baseCurrencyId;
            let sRate = (sCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.sell_exchange_rate) || parseFloat(selectedUnit.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
            
            let sellInBase = rawSellPrice * sRate;
            let sellPrice = sellInBase / invRate;
            row.querySelector('.sell').value = formatNum(sellPrice);

            // 5. نسبة الربح
            let newProfitPercent = (calculatedCost > 0) ? ((sellPrice - calculatedCost) / calculatedCost) * 100 : 0;
            row.querySelector('.profit').value = formatNum(newProfitPercent);

            // 6. تحديث قيم الـ data attributes (بعملة الفاتورة)
            let trueBaseCost = (selectedFactor > 0) ? (calculatedCost / selectedFactor) : 0;
            Array.from(select.options).forEach(opt => {
                let uId = opt.value;
                let u = product.units.find(ux => ux.id == uId);
                if (u) {
                    let fac = (u.is_base_unit) ? 1 : (parseFloat(u.conversion_factor) || 1);
                    let mPrice = trueBaseCost * fac;
                    let rs = parseFloat(u.sale_price) || 0;
                    let rci = u.sell_currency_id || baseCurrencyId;
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

    // 🟢 عرض ما يعادل سعر الشراء والبيع بالعملة الافتراضية وإظهار badge العملة
    function updateDualPriceDisplay(idx) {
        let row = document.getElementById(`row_${idx}`);
        if (!row) return;
        
        // 1. جلب القيم الحالية من الحقول (الآن أصبحت بعملة الفاتورة)
        let priceInvoice = parseFloat(row.querySelector('.price').value) || 0;
        let sellInvoice = parseFloat(row.querySelector('.sell').value) || 0;

        // 2. التحويل لليرة التركية / العملة الأساسية
        let invRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        let priceBase = priceInvoice * invRate;
        let sellBase = sellInvoice * invRate;
        let baseCurrCode = "{{ optional($baseCurrency)->code }}";

        // 🟢 سعر الشراء — badge العملة + بالعملة الأساسية
        let costBaseEl = document.getElementById('cost-base-' + idx);
        let costBadge  = document.getElementById('inv-badge-' + idx);
        
        // badge العملة جنب الحقل — يظهر فقط إذا كانت غير الافتراضية
        let invCurrSel = document.getElementById('currency_id');
        let selOpt = invCurrSel ? invCurrSel.options[invCurrSel.selectedIndex] : null;
        let isBaseInv = selOpt ? (selOpt.dataset.isBase === '1') : true;
        let invCurrCode = selOpt ? (selOpt.dataset.code || baseCurrCode) : baseCurrCode;
        let showBadge = !isBaseInv && invCurrCode && invCurrCode !== baseCurrCode;

        if (costBadge) {
            costBadge.textContent = invCurrCode;
            costBadge.style.display = showBadge ? '' : 'none';
        }
        if (costBaseEl) costBaseEl.innerText = `${formatNum(priceBase)} ${baseCurrCode}`;

        // 🟢 سعر البيع — badge العملة + بالعملة الأساسية
        let sellBaseEl = document.getElementById('sell-base-' + idx);
        let sellBadge  = document.getElementById('sell-badge-' + idx);

        if (sellBadge) {
            sellBadge.textContent = invCurrCode;
            sellBadge.style.display = showBadge ? '' : 'none';
        }
        if (sellBaseEl) sellBaseEl.innerText = `${formatNum(sellBase)} ${baseCurrCode}`;
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
        let invoiceRate = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        let baseCurrCode = "{{ optional($baseCurrency)->code }}";

        // 1. حساب الإجمالي بعملة الفاتورة أولاً
        let subTotalInvoice = 0;
        document.querySelectorAll('tr[id^="row_"]').forEach(row => {
            subTotalInvoice += parseFloat(row.getAttribute('data-total-invoice')) || 0;
        });

        // 2. تحويل وعرض المجموع الفرعي بالعملة الأساسية 
        let subTotalBase = subTotalInvoice * invoiceRate;
        document.getElementById('subTotalDisplay').innerHTML = `${formatNum(subTotalBase)} <span class="fs-6 text-muted">(${formatNum(subTotalInvoice)} Invoice)</span>`;

        // 3. تطبيق الخصم (يدخل المستخدم الخصم بعملة الفاتورة)
        let discountInput = parseMoney(document.getElementById('discountInput').value);
        let grandTotalInvoice = subTotalInvoice - discountInput; // افتراضاً الخصم هنا fixed بعملة الفاتورة بالمجمل
        
        // 4. تحويل وعرض الصافي النهائي بالعملة الأساسية
        let grandTotalBase = grandTotalInvoice * invoiceRate;
        document.getElementById('grandTotalDisplay').innerHTML = `${formatNum(grandTotalBase)} <span class="fs-6 text-muted">(${formatNum(grandTotalInvoice)} Invoice)</span>`;
        // نحتفظ بهذه القيم للإستخدام في الدفعات لاحقاً
        document.getElementById('grandTotalDisplay').setAttribute('data-grand-total', grandTotalBase);

        // حساب مجموع المدفوعات بالعملة الأساسية
        let totalPaid = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let amountInput = row.querySelector('.payment-input');
            let rateInp = row.querySelector('.rate-input'); // هذا للعرض فقط (المعادل)
            let hiddenRate = row.querySelector('.pay-rate-hidden'); // هذا سعر الصرف الحقيقي
            let currSel = row.querySelector('.currency-select');
            if (!amountInput) return;
            let amount = parseMoney(amountInput.value);
            let isBase = currSel ? (currSel.options[currSel.selectedIndex]?.dataset?.isBase === '1') : true;
            let rate = hiddenRate ? (parseMoney(hiddenRate.value) || 1) : 1;
            
            let amtInBase = isBase ? amount : (rate > 0 ? amount * rate : 0);
            totalPaid += amtInBase;

            // تحديث حقل "المعادل" في الواجهة
            if (rateInp && !isBase) {
                rateInp.value = formatNum(amtInBase);
            }
        });
        
        const diff = parseFloat((totalPaid - grandTotalInBase).toFixed(2));
        const balDiv = document.getElementById('balanceAlert');
        const balLbl = document.getElementById('balanceLabel');
        const balAmt = document.getElementById('balanceAmount');
        // baseCurrCode already declared above

        balDiv.style.display = 'block';
        if (Math.abs(diff) < 0.01) {
            balDiv.className = 'alert p-2 text-center fw-bold alert-success';
            balLbl.innerText = 'خالص (تم الدفع بالكامل)';
            balAmt.innerText = '';
        } else if (diff < 0) {
            balDiv.className = 'alert p-2 text-center fw-bold alert-danger';
            balLbl.innerText = 'متبقي (عليك):';
            balAmt.innerText = formatNum(Math.abs(diff)) + " " + baseCurrCode;
        } else {
            balDiv.className = 'alert p-2 text-center fw-bold alert-info';
            balLbl.innerText = 'رصيد (لك):';
            balAmt.innerText = formatNum(diff) + " " + baseCurrCode;
        }
    }

    // عند تغيير العملة في صف الدفع
    // مزامنة سعر الصرف مع الحقل المخفي للـ form
    function syncRateHidden(rateInp) {
        let row = rateInp.closest('.payment-row');
        let hidden = row ? row.querySelector('.pay-rate-hidden') : null;
        if (hidden) hidden.value = parseFloat(rateInp.value) || 1;
    }

    // أسعار الصرف المحملة مسبقاً من الخادم
    const preloadedRates = @json($currenciesData ?? []);
    const ratesMap = {};
    preloadedRates.forEach(c => { ratesMap[c.id] = c; });

    function onInvoiceCurrencyChange(select) {
        let opt = select.options[select.selectedIndex];
        let currId = select.value;
        let currCode = opt.dataset.code;
        let baseCurrId = "{{ optional($baseCurrency)->id }}";
        let baseCurrCode = "{{ optional($baseCurrency)->code }}";

        // Update labels in UI (Priority: Symbol > Code)
        let label = opt.dataset.symbol || currCode;
        document.querySelectorAll('.currency-label').forEach(el => el.innerText = label);

        if (currId == baseCurrId) {
            document.getElementById('invoice_exchange_rate').value = 1;
            document.getElementById('invoice_rate_info').classList.add('d-none');
            calculateGrandTotal();
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
        }).then(result => {
            if (result.isConfirmed) {
                let rate = result.value;
                document.getElementById('invoice_exchange_rate').value = rate;
                document.getElementById('selected_curr_code').innerText = currCode;
                document.getElementById('selected_curr_rate').innerText = rate;
                document.getElementById('invoice_rate_info').classList.remove('d-none');
                
                // تحديث المبالغ المدفوعة المقترحة إذا كانت الفاتورة فارغة أو المتبقي كبير
                recalculateAllRows();
                calculateGrandTotal();
            } else {
                select.value = baseCurrId;
                document.getElementById('invoice_exchange_rate').value = 1;
                document.getElementById('invoice_rate_info').classList.add('d-none');
                document.querySelectorAll('.currency-label').forEach(el => el.innerText = baseCurrCode);
                recalculateAllRows();
                calculateGrandTotal();
            }
        });
    }

    function updatePayRate(row, rate, currCode) {
        let rateRow = row.querySelector('.rate-row');
        let rateInput = row.querySelector('.rate-input');
        let hiddenInput = row.querySelector('.pay-rate-hidden');
        let noteSpan = row.querySelector('.rate-note');
        let baseCurrCode = '{{ optional($baseCurrency)->code }}';

        if (hiddenInput) hiddenInput.value = rate;
        
        if (rate == 1) {
            if (rateRow) rateRow.classList.add('d-none');
        } else {
            if (rateRow) rateRow.classList.remove('d-none');
            // ملاحظة: الحساب الفعلي يتم في calculateGrandTotal
            if (noteSpan) noteSpan.innerText = `1 ${currCode} = ${rate} ${baseCurrCode}`;
        }
    }

    function onPayCurrencyChange(select, idx) {
        let row = select.closest('.payment-row');
        let rateInput = row.querySelector('.rate-input');
        let selectedOpt = select.options[select.selectedIndex];
        let isBase = selectedOpt.dataset.isBase === '1';
        let currCode = selectedOpt.text.trim();
        let currId   = select.value;
        let baseCurrCode = '{{ optional($baseCurrency)->code }}';
        let baseCurrId = '{{ optional($baseCurrency)->id }}';

        // 1. حساب الإجمالي والمتبقي بالعملة الأساسية (مرجع موحد لكافة الحالات)
        const invoiceId = document.getElementById('currency_id').value;
        const invoiceRateVal = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        const grandTotalInInvoice = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
        const grandTotalInBase = grandTotalInInvoice * invoiceRateVal;

        let otherPaidBase = 0;
        document.querySelectorAll('.payment-row').forEach(r2 => {
            if (r2 === row) return;
            let ai = r2.querySelector('.payment-input');
            let hr = r2.querySelector('.pay-rate-hidden');
            let cs = r2.querySelector('.currency-select');
            if (!ai) return;
            let amt = parseMoney(ai.value);
            let isB = cs ? (cs.options[cs.selectedIndex]?.dataset?.isBase === '1') : true;
            let r = hr ? (parseMoney(hr.value) || 1) : 1;
            otherPaidBase += isB ? amt : (r > 0 ? amt * r : 0);
        });
        
        let remainingInBase = grandTotalInBase - otherPaidBase;
        if (remainingInBase < 0.0001) remainingInBase = 0;
        const amountInput = row.querySelector('.payment-input');

        // 2. حالة العملة الأساسية (مثل الليرة التركية)
        if (isBase) {
            let hiddenCurr = row.querySelector('.pay-currency-id');
            if (hiddenCurr) hiddenCurr.value = currId;
            let hiddenRate = row.querySelector('.pay-rate-hidden');
            if (hiddenRate) hiddenRate.value = 1;

            if (rateInput) rateInput.value = 1;
            let rateRow = row.querySelector('.rate-row');
            if (rateRow) rateRow.classList.add('d-none');

            if (amountInput && remainingInBase > 1e-6) {
                amountInput.value = formatNum(remainingInBase);
            } else if (amountInput) {
                amountInput.value = 0;
            }

            calculateGrandTotal();
            return;
        }

        // 3. حالة عملة الفاتورة (نفس سعر الصرف تلقائياً)
        if (currId == invoiceId) {
            let hiddenCurr = row.querySelector('.pay-currency-id');
            if (hiddenCurr) hiddenCurr.value = currId;
            
            updatePayRate(row, invoiceRateVal, currCode);

            let remInPaymentCurr = remainingInBase / invoiceRateVal;
            if (amountInput && remInPaymentCurr > 1e-6) {
                amountInput.value = formatNum(remInPaymentCurr);
            } else if (amountInput) {
                amountInput.value = 0;
            }

            calculateGrandTotal();
            return;
        }

        // 4. حالة العملات الأخرى (تطلب سعر صرف وتظهر نافذة منبثقة)
        let currData = ratesMap[currId];
        let suggestedRate = currData ? parseFloat(currData.exchange_rate).toFixed(6) : '1.000000';

        Swal.fire({
            title: `💱 سعر الصرف: ${baseCurrCode} ↔ ${currCode}`,
            icon: 'info',
            width: '36rem',
            html: `
                <div class="text-start mb-3">
                    <label class="form-label text-muted small">📡 السعر المستورد (المقترح):</label>
                    <div class="input-group mb-1">
                        <span class="input-group-text bg-light fw-bold">1 ${currCode}</span>
                        <input type="number" id="swalSuggestedRate" class="form-control text-center text-info fw-bold" value="${suggestedRate}" readonly>
                        <span class="input-group-text">${baseCurrCode}</span>
                    </div>
                    <small class="text-muted">المصدر: open.er-api.com</small>
                </div>
                <hr>
                <div class="text-start mb-3">
                    <label class="form-label fw-bold">✏️ سعر الصرف المعتمد للفاتورة:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white fw-bold">1 ${currCode}</span>
                        <input type="text" inputmode="decimal" id="swalConfirmedRate" class="form-control text-center fw-bold fs-5" value="${suggestedRate}">
                        <span class="input-group-text fw-bold">${baseCurrCode}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="document.getElementById('swalConfirmedRate').value=document.getElementById('swalSuggestedRate').value; updateCalcPreview(${remainingInBase})">
                        ↩️ استخدم المقترح
                    </button>
                </div>
                <hr>
                <div class="alert alert-success p-2 text-center" id="calcPreview">
                    <div class="small text-muted mb-1">💡 لتسديد المتبقي:</div>
                    <div class="fw-bold fs-5">
                        <span class="text-danger">${remainingInBase.toFixed(2)} ${baseCurrCode}</span>
                        <span class="mx-2">←</span>
                        <span class="text-success" id="calcResult">${(remainingInBase / parseFloat(suggestedRate)).toFixed(4)}</span>
                        <span class="text-success"> ${currCode}</span>
                    </div>
                </div>
            `,
            confirmButtonText: '✅ تأكيد واملأ المبلغ',
            cancelButtonText: '❌ إلغاء',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            focusConfirm: false,
            didOpen: () => {
                let inp = document.getElementById('swalConfirmedRate');
                window.updateCalcPreview = function(rem) {
                    let r = parseFloat(inp.value) || 0;
                    let res = document.getElementById('calcResult');
                    if (res && r > 0) res.innerText = (rem / r).toFixed(4);
                };
                inp.addEventListener('input', function() {
                    let pos = this.selectionStart;
                    let old = this.value;
                    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\./g, '$1');
                    if (this.value !== old) this.setSelectionRange(pos - 1, pos - 1);
                    updateCalcPreview(remainingInBase);
                });
                inp.addEventListener('keydown', function(e) {
                    if (['e', 'E', '+', '-'].includes(e.key)) e.preventDefault();
                });
            },
            preConfirm: () => {
                let val = parseFloat(document.getElementById('swalConfirmedRate').value);
                if (!val || val <= 0) {
                    Swal.showValidationMessage('⚠️ يرجى إدخال سعر صرف صحيح أكبر من صفر');
                    return false;
                }
                return val;
            }
        }).then(result => {
            if (result.isConfirmed) {
                let rate = result.value;
                let hiddenCurr = row.querySelector('.pay-currency-id');
                if (hiddenCurr) hiddenCurr.value = currId;
                
                updatePayRate(row, rate, currCode);

                if (amountInput && remainingInBase > 0) {
                    amountInput.value = formatNum(remainingInBase / rate);
                }
                calculateGrandTotal();
            } else {
                // العودة للعملة الأساسية في حال الإلغاء
                select.value = baseCurrId;
                onPayCurrencyChange(select, idx);
            }
        });
    }

    function addPaymentRow() {
        const invoiceRateVal = parseFloat(document.getElementById('invoice_exchange_rate').value) || 1;
        const grandTotalInInvoice = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
        const grandTotalInBase = grandTotalInInvoice * invoiceRateVal;

        let currentPaidInBase = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let amountInput = row.querySelector('.payment-input');
            let hiddenRate = row.querySelector('.pay-rate-hidden'); // السعر الحقيقي
            let currSel = row.querySelector('.currency-select');
            if (!amountInput) return;
            let amount = parseMoney(amountInput.value);
            let isBase = currSel ? (currSel.options[currSel.selectedIndex]?.dataset?.isBase === '1') : true;
            let rate = hiddenRate ? (parseMoney(hiddenRate.value) || 1) : 1;
            // التحويل للعملة الأساسية
            currentPaidInBase += isBase ? amount : (rate > 0 ? amount * rate : 0);
        });

        let remainingInBase = grandTotalInBase - currentPaidInBase;
        if (remainingInBase < 0.001) remainingInBase = 0;
        
        const invoiceCurrId = document.getElementById('currency_id').value;
        const invoiceCurrCode = document.getElementById('currency_id').options[document.getElementById('currency_id').selectedIndex]?.dataset?.code || '';
        const baseCurrId = "{{ optional($baseCurrency)->id }}";

        let selectedCurrId = baseCurrId;
        let selectedRate = 1;
        let isNonBase = false;

        // إذا كانت الفاتورة بعملة غير الأساسية، نجعل الدفع الأول (أو كل دفع جديد) يتبع عملة الفاتورة افتراضياً
        if (invoiceCurrId != baseCurrId) {
            selectedCurrId = invoiceCurrId;
            selectedRate = invoiceRateVal;
            isNonBase = true;
        }

        // خيارات العملات
        let currencyOptions = `<option value="${baseCurrId}" data-is-base="1" ${selectedCurrId == baseCurrId ? 'selected' : ''}>{{ optional($baseCurrency)->code }}</option>`;
        @foreach($currencies as $cur)
            @if(!$baseCurrency || $cur->id != $baseCurrency->id)
            currencyOptions += `<option value="{{ $cur->id }}" data-code="{{ $cur->code }}" data-is-base="0" ${selectedCurrId == "{{ $cur->id }}" ? 'selected' : ''}>{{ $cur->code }}</option>`;
            @endif
        @endforeach

        let finalDefaultVal = (remainingInBase > 0 && selectedRate > 0) ? formatNum(remainingInBase / selectedRate) : 0;
        
        const div = document.createElement('div');
        div.className = 'payment-row mb-2';
        div.innerHTML = `
            <div class="input-group mb-1">
                <select name="payments[${paymentIdx}][method]" class="form-select method-select" style="max-width: 120px;">
                    <option value="cash"> {{ __('💰 نقدي') }} </option>
                    <option value="card"> {{ __('💳 بطاقة') }} </option>
                    <option value="bank"> {{ __('🏦 تحويل') }} </option>
                </select>
                <input type="text" inputmode="decimal" name="payments[${paymentIdx}][amount]" class="form-control text-center payment-input" value="${finalDefaultVal}" oninput="calculateGrandTotal()" onfocus="this.select()">
                <select class="form-select currency-select pay-currency" style="max-width:110px;" onchange="onPayCurrencyChange(this, ${paymentIdx})">${currencyOptions}</select>
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.payment-row').remove(); calculateGrandTotal();"><i class="fas fa-trash"></i></button>
            </div>
            <input type="hidden" name="payments[${paymentIdx}][currency_id]" class="pay-currency-id" value="${selectedCurrId}">
            <input type="hidden" name="payments[${paymentIdx}][exchange_rate]" class="pay-rate-hidden" value="${selectedRate}">
            <div class="rate-row ${isNonBase ? '' : 'd-none'}">
                <div class="input-group input-group-sm">
                    <span class="input-group-text text-muted small">{{ __('يعادل') }} ({{ optional($baseCurrency)->code }})</span>
                    <input type="text" readonly class="form-control bg-light text-center fw-bold rate-input" value="0.00" placeholder="المعادل">
                    <span class="input-group-text rate-note small text-info">${isNonBase ? `1 ${invoiceCurrCode} = ${selectedRate} {{ optional($baseCurrency)->code }}` : ''}</span>
                </div>
            </div>
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
    
    // The duplicate calcProfitPercent and calcSellPrice functions were removed to fix live UI calculation bugs
    
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
        document.querySelectorAll('.payment-row').forEach(row => {
            let amount = parseMoney(row.querySelector('.payment-input')?.value || 0);
            let hiddenRate = row.querySelector('.pay-rate-hidden');
            let currSel = row.querySelector('.currency-select');
            let isBase = currSel ? (currSel.options[currSel.selectedIndex]?.dataset?.isBase === '1') : true;
            let rate = hiddenRate ? (parseMoney(hiddenRate.value) || 1) : 1;
            totalPaid += isBase ? amount : (rate > 0 ? amount * rate : 0);
        });
        
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