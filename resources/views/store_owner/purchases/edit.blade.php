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
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> {{ __('edit_invoice_title') }} (#{{ $purchase->invoice_number }})</h5>
                        <div class="d-flex align-items-center">
                             <span id="saveStatus" class="badge bg-white text-success me-3 d-none"><i class="fas fa-check"></i> {{ __('saved_status') }}</span>
                             <a href="{{ route('store.purchases.index') }}" class="btn btn-sm btn-light text-dark fw-bold">{{ __('back_btn') }}</a>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('supplier_label') }} <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSupplierModal" title="{{ __('new_supplier') }}"><i class="fas fa-plus"></i></button>
                                        {{-- ملء بيانات المورد القديم --}}
                                        <input type="text" id="supplierSearchInput" class="form-control" 
                                               value="{{ $purchase->supplier ? ($purchase->supplier->company_name ?? $purchase->supplier->contact_name) : '' }}" 
                                               placeholder="{{ __('search_supplier_placeholder') }}" autocomplete="off">
                                        <input type="hidden" name="supplier_id" id="supplierId" value="{{ $purchase->supplier_id }}" required>
                                    </div>
                                    <div id="supplierResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; display: none;"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">{{ __('invoice_date_time') }}</label>
                                <input type="text" name="invoice_date" class="form-control custom-date-input" 
                                       value="{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('Y-m-d H:i') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">{{ __('العملة') }}</label>
                                <select name="currency_id" id="currency_id" class="form-select" onchange="onInvoiceCurrencyChange(this)">
                                    @foreach($currencies as $cur)
                                        <option value="{{ $cur->id }}" data-code="{{ $cur->code }}" data-symbol="{{ $cur->symbol ?? $cur->code }}" 
                                            {{ $cur->id == $purchase->currency_id ? 'selected' : ($purchase->currency_id == null && $cur->id == optional($baseCurrency)->id ? 'selected' : '') }}>
                                            {{ $cur->code }} — {{ $cur->name_ar ?? $cur->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="exchange_rate" id="invoice_exchange_rate" value="{{ $purchase->exchange_rate ?? 1 }}">
                                <div id="invoice_rate_info" class="small text-info mt-1 {{ ($purchase->exchange_rate && $purchase->exchange_rate != 1) ? '' : 'd-none' }}" style="font-size: 0.7rem;">
                                    {{ __('سعر الصرف:') }} 1 <span id="selected_curr_code">{{ $purchase->currency ? $purchase->currency->code : '' }}</span> = <span id="selected_curr_rate">{{ $purchase->exchange_rate ?? 1 }}</span> {{ optional($baseCurrency)->code }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('invoice_number_label') }}</label>
                                <input type="text" name="invoice_number" class="form-control" value="{{ $purchase->invoice_number }}" placeholder="{{ __('invoice_number_placeholder') }}">
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
                                       placeholder="{{ __('search_product_placeholder') }}" autocomplete="off">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickProductModal"><i class="fas fa-plus-circle me-1"></i> {{ __('new_product') }}</button>
                            </div>
                            <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; top: 100%; display: none;"></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle mb-0" id="itemsTable">
                                <thead class="bg-dark text-white small">
                                     <tr>
                                        <th style="width: 5%">{{ __('image_label') }}</th>
                                        <th style="width: 10%">{{ __('product_label') }}</th>
                                        <th style="min-width: 130px; width: 12%">{{ __('barcode_label') }}</th>
                                        <th style="width: 8%">{{ __('unit_label') }}</th>
                                        <th style="width: 6%">{{ __('qty_label') }}</th>
                                        <th style="min-width: 100px; width: 10%">{{ __('buy_price_label') }}</th>
                                        <th style="width: 7%">{{ __('profit_percent_label') }}</th> 
                                        <th style="min-width: 110px; width: 11%">{{ __('discount_label') }}</th>
                                        <th style="min-width: 100px; width: 10%">{{ __('sell_price_label') }}</th>
                                        <th style="min-width: 110px; width: 10%">{{ __('expiry_date_label') }}</th>
                                        <th style="width: 7%">{{ __('alert_days_label') }}</th>
                                        <th style="width: 8%">{{ __('tax_label') }}</th>
                                        <th style="width: 15%">{{ __('total_label') }}</th>
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
                        <h6 class="mb-0 fw-bold text-primary">{{ __('payment_summary') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('subtotal_label') }}:</span> 
                            <div><span id="subTotalDisplay" class="fw-bold">{{ $purchase->sub_total }}</span> <span class="currency-label text-muted small">{{ $purchase->currency ? $purchase->currency->code : optional($baseCurrency)->code }}</span></div>
                        </div>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text">{{ __('additional_discount') }}</span>
                            <input type="number" name="discount" id="discountInput" class="form-control text-center fw-bold text-danger" value="{{ $purchase->discount_amount }}" step="any" oninput="calculateGrandTotal()">
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 mb-3">
                            <span class="fs-5 fw-bold">{{ __('final_net_label') }}:</span>
                            <div><span id="grandTotalDisplay" class="fs-4 fw-bold text-primary">{{ $purchase->grand_total }}</span> <span class="currency-label fw-bold text-primary">{{ $purchase->currency ? $purchase->currency->code : optional($baseCurrency)->code }}</span></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small text-muted mb-1">{{ __('payments_label') }}</label>
                             <div id="paymentsContainer">
                                @php
                                    $baseCurrencyId = optional($baseCurrency)->id;
                                @endphp
                                @forelse($purchase->payments as $idx => $payment)
                                    <div class="payment-row mb-2">
                                        <div class="input-group mb-1">
                                            <select name="payments[{{ $idx }}][method]" class="form-select method-select" style="max-width: 120px;">
                                                <option value="cash" {{ $payment->method == 'cash' ? 'selected' : '' }}>{{ __('💰 نقدي') }}</option>
                                                <option value="card" {{ $payment->method == 'card' ? 'selected' : '' }}>{{ __('💳 بطاقة') }}</option>
                                                <option value="bank" {{ $payment->method == 'bank' ? 'selected' : '' }}>{{ __('🏦 تحويل') }}</option>
                                            </select>
                                            <input type="text" inputmode="decimal" name="payments[{{ $idx }}][amount]" class="form-control text-center payment-input" value="{{ number_format($payment->amount_in_foreign_currency, 2, '.', '') }}" oninput="calculateGrandTotal()">
                                            <select class="form-select currency-select pay-currency" style="max-width:110px;" onchange="onPayCurrencyChange(this, {{ $idx }})">
                                                <option value="{{ $baseCurrencyId }}" data-rate="1" data-is-base="1" {{ $payment->currency_id == $baseCurrencyId ? 'selected' : '' }}>{{ optional($baseCurrency)->code }}</option>
                                                @foreach($currencies as $cur)
                                                    @if($baseCurrencyId != $cur->id)
                                                        <option value="{{ $cur->id }}" data-rate="{{ $cur->id == $payment->currency_id ? $payment->exchange_rate : 1 }}" data-is-base="0" {{ $payment->currency_id == $cur->id ? 'selected' : '' }}>{{ $cur->code }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @if($loop->first)
                                                <button type="button" class="btn btn-outline-success" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                                            @else
                                                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.payment-row').remove(); calculateGrandTotal();"><i class="fas fa-trash"></i></button>
                                            @endif
                                        </div>
                                        <input type="hidden" name="payments[{{ $idx }}][currency_id]" class="pay-currency-id" value="{{ $payment->currency_id }}">
                                        <input type="hidden" name="payments[{{ $idx }}][exchange_rate]" class="pay-rate-hidden" value="{{ $payment->exchange_rate }}">
                                        <div class="rate-row {{ $payment->currency_id == $baseCurrencyId ? 'd-none' : '' }}">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text text-muted small">{{ __('يعادل') }} ({{ optional($baseCurrency)->code }})</span>
                                                <input type="text" readonly class="form-control bg-light text-center fw-bold rate-input" value="{{ number_format($payment->amount, 2, '.', '') }}">
                                                <span class="input-group-text rate-note small text-info">1 {{ optional($payment->currency)->code }} = {{ $payment->exchange_rate }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    {{-- صف افتراضي في حال عدم وجود مدفوعات --}}
                                    <div class="payment-row mb-2">
                                        <div class="input-group mb-1">
                                            <select name="payments[0][method]" class="form-select method-select" style="max-width: 120px;">
                                                <option value="cash">{{ __('💰 نقدي') }}</option>
                                                <option value="card">{{ __('💳 بطاقة') }}</option>
                                                <option value="bank">{{ __('🏦 تحويل') }}</option>
                                            </select>
                                            <input type="text" inputmode="decimal" name="payments[0][amount]" class="form-control text-center payment-input" value="0" oninput="calculateGrandTotal()">
                                            <select class="form-select currency-select pay-currency" style="max-width:110px;" onchange="onPayCurrencyChange(this, 0)">
                                                <option value="{{ $baseCurrencyId }}" data-rate="1" data-is-base="1" selected>{{ optional($baseCurrency)->code }}</option>
                                                @foreach($currencies as $cur)
                                                    @if($baseCurrencyId != $cur->id)
                                                        <option value="{{ $cur->id }}" data-rate="1" data-is-base="0">{{ $cur->code }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-success" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <input type="hidden" name="payments[0][currency_id]" class="pay-currency-id" value="{{ $baseCurrencyId }}">
                                        <input type="hidden" name="payments[0][exchange_rate]" class="pay-rate-hidden" value="1">
                                        <div class="rate-row d-none">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text text-muted small">{{ __('يعادل') }} ({{ optional($baseCurrency)->code }})</span>
                                                <input type="text" readonly class="form-control bg-light text-center fw-bold rate-input" value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="alert p-2 text-center fw-bold" id="balanceAlert" style="display: none;">
                            <span id="balanceLabel">{{ __('remaining_label') }}:</span> <span id="balanceAmount">0.00</span>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100 btn-lg mt-3" id="saveBtn"><i class="fas fa-save me-2"></i> {{ __('save_invoice_btn') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- نفس المودالات --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">{{ __('add_new_supplier_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="quickSupplierForm"><div class="mb-2"><label class="small fw-bold">{{ __('name_label') }} *</label><input type="text" id="suppName" name="name" class="form-control" required></div><div class="mb-2"><label class="small">{{ __('company_label') }}</label><input type="text" id="suppComp" name="company" class="form-control"></div><button type="submit" class="btn btn-success w-100">{{ __('save_add_btn') }}</button></form></div></div></div></div>
<div class="modal fade" id="quickProductModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">{{ __('quick_add_product_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">{{ __('coming_soon_msg') }}</div></div></div></div>
{{-- 🔥 كود تجهيز الصور للمنتجات القديمة 🔥 --}}
@php
    // هذا الكود لم يعد ضرورياً لأن البيانات تأتي جاهزة من الكنترولر (itemsData)
@endphp

@section('scripts')
{{-- 🔥 Flatpickr Localization 🔥 --}}
@if(app()->getLocale() != 'en')
    <script src="https://npmcdn.com/flatpickr/dist/l10n/{{ app()->getLocale() == 'pt-BR' ? 'pt' : app()->getLocale() }}.js"></script>
@endif

<script>
    // --- المتغيرات العامة ---
    let rowIdx = {{ count($purchase->items) }};
    const storeTaxRates = @json($taxRates); 
    const oldItems = @json($itemsData ?? []); 
    window.productsData = {}; 

    // خريطة أسعار الصرف
    let paymentIdx = {{ count($purchase->payments) > 0 ? count($purchase->payments) : 1 }};
    const preloadedRates = @json($currenciesData ?? []);
    const ratesMap = {};
    if (Array.isArray(preloadedRates)) {
        preloadedRates.forEach(r => ratesMap[r.id] = r);
    }

    // Currency setup
    const defaultCurrencyCode = "{{ $purchase->currency ? $purchase->currency->code : optional($baseCurrency)->code }}";
    const baseCurrencyId = "{{ optional($baseCurrency)->id }}";
    const baseCurrencyCode = "{{ optional($baseCurrency)->code }}";

    // Current Locale for JS
    const CURRENT_LOCALE = "{{ app()->getLocale() == 'pt-BR' ? 'pt' : app()->getLocale() }}";

    function parseMoney(value) {
        if (!value) return 0;
        let clean = String(value).replace(/,/g, '.').replace(/[^0-9.]/g, ''); 
        return parseFloat(clean) || 0;
    }

    // Localization helper
    const LANG = {
        buy: "{{ __('buy_label') }}",
        profit_percent: "{{ __('profit_percent_label') }}",
        sell: "{{ __('sell_label') }}",
        loss: "{{ __('loss_warning') }}",
        low_profit: "{{ __('low_profit_warning') }}",
        credit_for_you: "{{ __('credit_for_you') }}",
        paid_settled: "{{ __('paid_settled') }}",
        remaining_due: "{{ __('remaining_due') }}",
        updated_related_units: "{{ __('updated_related_units') }}"
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
    let baseCurrId = "{{ optional($baseCurrency)->id }}";
    let baseCurrCode = "{{ optional($baseCurrency)->code }}";

    // Update labels in UI (Priority: Symbol > Code)
    let label = opt.dataset.symbol || currCode;
    updateInvoiceCurrencySymbols();

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
            updateInvoiceCurrencySymbols();
        }
    });
}

let prevCurrencyId = "{{ $purchase->currency_id ?? optional($baseCurrency)->id }}";
document.getElementById('currency_id').addEventListener('focus', function() {
    prevCurrencyId = this.value;
});

    // إضافة صف منتج (سواء جديد أو قادم من الداتابيس)
    function addProductRow(product, savedItem = null) {
        console.log("Adding row for product:", product?.name, "savedItem:", savedItem?.id);
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
        let sCurrId = selectedUnit.sell_price_currency_id || baseCurrencyId;
        let sRate = (sCurrId == baseCurrencyId) ? 1 : (parseFloat(selectedUnit.sell_exchange_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
        
        // سعر البيع بالعملة الأساسية (TRY)
        let sellInBase = rawSellPrice * sRate;
        // سعر البيع بعملة الفاتورة
        let sellPrice = (invRate > 0) ? (sellInBase / invRate) : sellInBase;

        // القيم الافتراضية
        let initialBarcode = selectedUnit ? (selectedUnit.barcode || '-') : '-';
        
        // استرجاع القيم المحفوظة (مع الحفاظ على السعر القديم إذا كان مخصصاً)
        // ملاحظة: في حالة الإضافة الجديدة نستخدم السعر المحسوب لتفادي القفزات
        let qty = savedItem ? parseFloat(savedItem.quantity) : 1;
        
        // السعر موجود في الداتابيس بعملة الفاتورة
        let price = savedItem ? parseFloat(savedItem.unit_price) : parseFloat(calculatedPrice.toFixed(4));
        
        // جلب سعر المبيع من الداتابيس (يكون مخزناً بعملة الفاتورة)
        // ✅ منع ظهور NaN للفواتير القديمة التي لم يكن مخزناً فيها سعر البيع بعد
        if (savedItem && savedItem.selling_price !== undefined && savedItem.selling_price !== null) {
             sellPrice = parseFloat(savedItem.selling_price);
        }

        // 🟢🟢 استرجاع تاريخ الانتهاء وأيام التنبيه من قاعدة البيانات 🟢🟢
        let expiryValue = savedItem ? (savedItem.expiry_date || '') : '';
        let alertValue = savedItem ? (savedItem.alert_days || 10) : 10;

        // الخصم
        let discountVal = savedItem ? parseFloat(savedItem.discount) : 0;
        let discountType = (savedItem && savedItem.discount_type) ? savedItem.discount_type : 'fixed';

        // حساب نسبة الربح
        let profitPercent = (price > 0 && sellPrice > 0) ? ((sellPrice - price) / price) * 100 : 0;
        
        let imgUrl = product.image_url; 
        
        // تجهيز خيارات الضريبة مع تحديد المختار منها
        let savedTax = savedItem ? parseFloat(savedItem.tax_percent) : 0;
        let taxOptionsHtml = storeTaxRates.map(rate => {
            let r = parseFloat(rate);
            return `<option value="${r}" ${r == savedTax ? 'selected' : ''}>${r}%</option>`;
        }).join('');

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
                        let rCurrId = u.sell_price_currency_id || baseCurrencyId;
                        let rRate = (rCurrId == baseCurrencyId) ? 1 : (parseFloat(u.sell_exchange_rate) || ratesMap[rCurrId]?.exchange_rate || 1);
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
                <div class="input-group input-group-sm" style="min-width: 90px;">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm text-center price text-danger fw-bold" value="${price}" oninput="syncSubUnits(${rowIdx})">
                    <span class="input-group-text p-1 currency-symbol-invoice small bg-light"></span>
                </div>
                <div class="cost-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1;">
                   <span class="cost-base d-block text-info fw-bold"></span>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm" style="min-width: 80px;">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][profit_percent]" class="form-control form-control-sm text-center profit text-primary px-1" value="${profitPercent}" oninput="calcSellPrice(${rowIdx})">
                    <span class="input-group-text p-1 small bg-light text-primary fw-bold">%</span>
                </div>
            </td>

            <td>
                <div class="input-group input-group-sm" style="min-width: 100px;">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][discount]" class="form-control text-center discount px-1" value="${discountVal}" oninput="calcTotals(${rowIdx})">
                    <select name="items[${rowIdx}][discount_type]" class="form-select discount-type px-0" style="max-width: 45px;" onchange="calcTotals(${rowIdx})">
                        <option value="fixed" ${discountType === 'fixed' ? 'selected' : ''} class="currency-label"></option>
                        <option value="percent" ${discountType === 'percent' ? 'selected' : ''}>%</option>
                    </select>
                </div>
            </td>

            <td>
                <div class="input-group input-group-sm" style="min-width: 90px;">
                    <input type="text" inputmode="decimal" name="items[${rowIdx}][selling_price]" class="form-control form-control-sm text-center sell fw-bold text-success" value="${sellPrice}" oninput="calcProfitPercent(${rowIdx})">
                    <span class="input-group-text p-1 currency-symbol-invoice small bg-light"></span>
                </div>
                <div class="sell-dual-price mt-1 text-center" style="font-size: 0.72rem; line-height: 1.1;">
                   <span class="sell-base d-block text-info fw-bold"></span>
                </div>
                <div class="main-warning-container mt-1" style="min-height:18px;"></div>
            </td>

            {{-- 🟢 عرض التاريخ المخزن 🟢 --}}
            <td><input type="text" name="items[${rowIdx}][expiry_date]" 
                       class="form-control form-control-sm text-center expiry-date-input" 
                       style="min-width: 100px;"
                       value="${expiryValue}" title="${LANG.expiry_date || 'تاريخ الانتهاء'}" placeholder="YYYY-MM-DD">
            </td>

            {{-- 🟢 عرض أيام التنبيه المخزنة 🟢 --}}
            <td>
                <input type="number" name="items[${rowIdx}][alert_days]" 
                       class="form-control form-control-sm text-center text-danger fw-bold" 
                       style="min-width: 50px;"
                       value="${alertValue}" placeholder="10">
            </td>

            <td><select name="items[${rowIdx}][tax]" class="form-select form-select-sm tax bg-warning bg-opacity-10" style="min-width: 70px;" onchange="calcTotals(${rowIdx})">${taxOptionsHtml}</select></td>
            <td><input type="text" class="form-control form-control-sm text-center bg-light fw-bold total" style="min-width: 120px;" readonly></td>
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
        updateInvoiceCurrencySymbols(); // 🟢 تحديث رموز العملة في الصف الجديد
        calcTotals(rowIdx); 
        rowIdx++;
    }

    function updateInvoiceCurrencySymbols() {
        let select = document.getElementById('currency_id');
        let opt = select.options[select.selectedIndex];
        let symbol = opt.dataset.symbol || opt.dataset.code;
        
        document.querySelectorAll('.currency-symbol-invoice').forEach(el => el.innerText = symbol);
        document.querySelectorAll('.currency-label').forEach(el => el.innerText = symbol);
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
        let baseCurrCode = "{{ optional($baseCurrency)->code }}";

        // 🟢 سعر الشراء الإضافي
        let costBaseEl = row.querySelector('.cost-base');
        if(costBaseEl) costBaseEl.innerText = `${formatNum(priceBase)} ${baseCurrCode}`;
        
        // 🟢 سعر البيع الإضافي
        let sellBaseEl = row.querySelector('.sell-base');
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
        
        updateDualPriceDisplay(idx);
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
        let discountInput = parseFloat(document.getElementById('discountInput').value) || 0;
        let grandTotalInvoice = subTotalInvoice - discountInput; // افتراضاً الخصم هنا fixed بعملة الفاتورة بالمجمل
        
        // 4. تحويل وعرض الصافي النهائي بالعملة الأساسية
        let grandTotalBase = grandTotalInvoice * invoiceRate;
        document.getElementById('grandTotalDisplay').innerHTML = `${formatNum(grandTotalBase)} <span class="fs-6 text-muted">(${formatNum(grandTotalInvoice)} Invoice)</span>`;
        // نحتفظ بهذه القيم للإستخدام في الدفعات لاحقاً
        document.getElementById('grandTotalDisplay').setAttribute('data-grand-total', grandTotalBase);

        // حساب مجموع المدفوعات بالعملة الأساسية
        let totalPaidInBase = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            const amount = parseFloat(row.querySelector('.payment-input').value) || 0;
            const rate = parseFloat(row.querySelector('.pay-rate-hidden').value) || 1;
            totalPaidInBase += amount * rate;
            
            // تحديث حقل "المعادل" المرئي
            const rateInput = row.querySelector('.rate-input');
            if(rateInput) rateInput.value = formatNum(amount * rate);
        });

        const diff = parseFloat((totalPaidInBase - grandTotalBase).toFixed(2));
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

    function onPayCurrencyChange(select, idx) {
        let opt = select.options[select.selectedIndex];
        let row = select.closest('.payment-row');
        let isBase = opt.dataset.isBase === '1';
        let currId = select.value;
        let rateHidden = row.querySelector('.pay-rate-hidden');
        let currIdHidden = row.querySelector('.pay-currency-id');
        let rateRow = row.querySelector('.rate-row');
        let rateNote = row.querySelector('.rate-note');
        let code = opt.text;
        let baseCurrId = '{{ optional($baseCurrency)->id }}';
        let baseCurrCode = '{{ optional($baseCurrency)->code }}';

        currIdHidden.value = currId;

        if (isBase) {
            rateHidden.value = 1;
            rateRow.classList.add('d-none');
            calculateGrandTotal();
            return;
        }

        // 🔄 البحث عن سعر صرف محلي تم استخدامه لنفس العملة في صفوف أخرى
        let existingRate = null;
        document.querySelectorAll('.payment-row').forEach(r => {
            if (r === row) return;
            let rCurrId = r.querySelector('.pay-currency-id').value;
            if (rCurrId == currId) {
                existingRate = r.querySelector('.pay-rate-hidden').value;
            }
        });

        if (existingRate) {
            // إذا كان السعر موجود مسبقاً في صف آخر، نستخدمه مباشرة دون سؤال
            applyRateToAll(currId, existingRate, code);
            return;
        }

        // جلب السعر المقترح (من البيانات المحملة أو API)
        let preRate = preloadedRates.find(r => r.id == currId);
        let suggestedRate = preRate ? parseFloat(preRate.exchange_rate).toFixed(6) : '1.000000';

        const performShowModal = (finalSuggested) => {
            Swal.fire({
                title: `💱 سعر صرف الفاتورة: ${code} ↔ ${baseCurrCode}`,
                icon: 'info',
                width: '32rem',
                html: `
                    <div class="text-start mb-3">
                        <label class="form-label fw-bold">✏️ سعر صرف (1 ${code} = ؟ ${baseCurrCode}):</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white fw-bold">1 ${code}</span>
                            <input type="text" inputmode="decimal" id="swalPayRate" class="form-control text-center fw-bold fs-5" value="${finalSuggested}">
                            <span class="input-group-text fw-bold">${baseCurrCode}</span>
                        </div>
                        <small class="text-muted">يمكنك تعديل السعر قبل التأكيد</small>
                    </div>
                `,
                confirmButtonText: '✅ تأكيد',
                showCancelButton: true,
                cancelButtonText: '❌ إلغاء',
                preConfirm: () => {
                    let val = parseFloat(document.getElementById('swalPayRate').value);
                    if (!val || val <= 0) {
                        Swal.showValidationMessage('⚠️ يرجى إدخال سعر صرف صحيح');
                        return false;
                    }
                    return val;
                }
            }).then(result => {
                if (result.isConfirmed) {
                    applyRateToAll(currId, result.value, code);
                } else {
                    // العودة للعملة الأساسية في حال الإلغاء
                    select.value = baseCurrId;
                    onPayCurrencyChange(select, idx);
                }
            });
        };

        if (preRate) {
            performShowModal(suggestedRate);
        } else {
            fetch(`{{ route('store.purchases.exchange-rate') }}?from=${code}&to=${baseCurrCode}`)
                .then(r => r.json())
                .then(data => performShowModal(parseFloat(data.rate || 1).toFixed(6)));
        }
    }

    function applyRateToAll(currId, rate, code) {
        // حساب المتبقي قبل تحديث هذه العملة (بالعملة الأساسية)
        const grandTotalInBase = parseFloat(document.getElementById('grandTotalDisplay').getAttribute('data-grand-total')) || 0;
        let otherPaidBase = 0;
        
        // نحتاج لمعرفة أي صف هو الذي يتم تعديله حالياً لتعبئة مبلغه
        // نعتبر الصف "النشط" هو أول صف يحمل هذه العملة ومبلغه 0 أو هو قيد التعديل
        let targetRow = null;

        document.querySelectorAll('.payment-row').forEach(r => {
            let rCurrIdField = r.querySelector('.pay-currency-id');
            if (rCurrIdField && rCurrIdField.value == currId) {
                r.querySelector('.pay-rate-hidden').value = rate;
                let rateRow = r.querySelector('.rate-row');
                let rateNote = r.querySelector('.rate-note');
                if (rateRow) rateRow.classList.remove('d-none');
                if (rateNote) rateNote.innerText = `1 ${code} = ${rate} {{ optional($baseCurrency)->code }}`;
                targetRow = r; // آخر صف بهذه العملة
            } else {
                // حساب مبالغ العملات الأخرى
                let amt = parseFloat(r.querySelector('.payment-input').value) || 0;
                let rRate = parseFloat(r.querySelector('.pay-rate-hidden').value) || 1;
                otherPaidBase += amt * rRate;
            }
        });

        // إذا وجدنا الصف المستهدف، نقوم بتعبئة المبلغ المتبقي فيه
        if (targetRow) {
            let remainingInBase = grandTotalInBase - otherPaidBase;
            if (remainingInBase < 0) remainingInBase = 0;
            
            let amountInput = targetRow.querySelector('.payment-input');
            if (amountInput) {
                // تحويل المتبقي من العملة الأساسية إلى عملة الدفع بدقة عالية
                let valInPayCurr = rate > 0 ? (remainingInBase / rate) : 0;
                amountInput.value = valInPayCurr.toFixed(8).replace(/\.?0+$/, '');
            }
        }

        calculateGrandTotal();
    }

    function addPaymentRow() {
        const grandTotalInBase = parseFloat(document.getElementById('grandTotalDisplay').getAttribute('data-grand-total')) || 0;
        let currentPaidInBase = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let amount = parseFloat(row.querySelector('.payment-input').value) || 0;
            let rate = parseFloat(row.querySelector('.pay-rate-hidden').value) || 1;
            currentPaidInBase += amount * rate;
        });

        let remainingInBase = grandTotalInBase - currentPaidInBase;
        if (remainingInBase < 0) remainingInBase = 0;

        const baseCurrId = "{{ optional($baseCurrency)->id }}";
        const baseCurrCode = "{{ optional($baseCurrency)->code }}";

        let currencyOptions = `<option value="${baseCurrId}" data-is-base="1" selected>${baseCurrCode}</option>`;
        @foreach($currencies as $cur)
            @if($baseCurrency && $cur->id != $baseCurrency->id)
                currencyOptions += `<option value="{{ $cur->id }}" data-is-base="0">{{ $cur->code }}</option>`;
            @endif
        @endforeach

        const div = document.createElement('div');
        div.className = 'payment-row mb-2';
        div.innerHTML = `
            <div class="input-group mb-1">
                <select name="payments[${paymentIdx}][method]" class="form-select method-select" style="max-width: 120px;">
                    <option value="cash">💰 {{ __('cash_method') }}</option>
                    <option value="card">💳 {{ __('card_method') }}</option>
                    <option value="bank">🏦 {{ __('bank_method') }}</option>
                </select>
                <input type="text" inputmode="decimal" name="payments[${paymentIdx}][amount]" class="form-control text-center payment-input" value="${formatNum(remainingInBase)}" oninput="calculateGrandTotal()">
                <select class="form-select currency-select pay-currency" style="max-width:110px;" onchange="onPayCurrencyChange(this, ${paymentIdx})">${currencyOptions}</select>
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.payment-row').remove(); calculateGrandTotal();"><i class="fas fa-trash"></i></button>
            </div>
            <input type="hidden" name="payments[${paymentIdx}][currency_id]" class="pay-currency-id" value="${baseCurrId}">
            <input type="hidden" name="payments[${paymentIdx}][exchange_rate]" class="pay-rate-hidden" value="1">
            <div class="rate-row d-none">
                <div class="input-group input-group-sm">
                    <span class="input-group-text text-muted small">{{ __('يعادل') }} (${baseCurrCode})</span>
                    <input type="text" readonly class="form-control bg-light text-center fw-bold rate-input" value="0.00">
                    <span class="input-group-text rate-note small text-info"></span>
                </div>
            </div>
        `;
        document.getElementById('paymentsContainer').appendChild(div);
        paymentIdx++;
        calculateGrandTotal();
    }

    function toggleDetails(idx) {
        let row = document.getElementById(`details_${idx}`);
        row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
    }

    function syncSubUnits(idx) {
        calcTotals(idx);
        let row = document.getElementById(`row_${idx}`);
        
        let mainPrice = parseMoney(row.querySelector('.price').value); 
        
        // تحديث ربح الوحدة الأساسية
        let mainSellInput = row.querySelector('.sell');
        let mainProfitInput = row.querySelector('.profit');
        let currentSell = parseMoney(mainSellInput.value);
        
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

                let currentSubSell = parseMoney(subRow.querySelector('.sub-sell').value);
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
            let sCurrId = u.sell_currency_id || u.sell_price_currency_id || "{{ optional($baseCurrency)->id }}";
            let sRate = (sCurrId == "{{ optional($baseCurrency)->id }}") ? 1 : (parseFloat(u.sell_exchange_rate) || parseFloat(u.store_custom_sell_rate) || ratesMap[sCurrId]?.exchange_rate || 1);
            
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

        updateInvoiceCurrencySymbols();

        // 1. تشغيل البحث عن الموردين والمنتجات
        
        // Currency change listener
        // document.getElementById('currency_id').addEventListener('change', function() {
        //     let selectedCode = this.options[this.selectedIndex].text.split(' — ')[0];
        //     document.querySelectorAll('.currency-label').forEach(el => el.innerText = selectedCode);
        // });

        setupSearch('supplierSearchInput', 'supplierResults', "{{ url('store-owner/contacts/search') }}", function(s) {
            document.getElementById('supplierSearchInput').value = s.contact_name;
            document.getElementById('supplierId').value = s.id;
        });

        setupSearch('productSearch', 'searchResults', "{{ url('store-owner/products/search') }}", function(p) {
            addProductRow(p); // منتج جديد
            document.getElementById('productSearch').value = ''; 
            document.getElementById('productSearch').focus();
        });

        // 2. 🔥 تعبئة المنتجات القديمة (Fix) 🔥
        console.log("oldItems raw:", oldItems);
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


</script>
@endsection
@endsection