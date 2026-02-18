@extends('layouts.app')

@section('content')
{{-- المكتبات المطلوبة --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
{{-- SweetAlert للتنبيهات --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

    /* تنسيق قائمة اختيار الأعمدة */
    .dropdown-menu-columns { max-height: 300px; overflow-y: auto; padding: 10px; min-width: 200px; }
    .column-item { display: block; margin-bottom: 8px; cursor: pointer; padding: 5px; }
    .column-item:hover { background-color: #f8f9fa; }

    /* لون العنصر المحدد في قائمة البحث */
    #searchResults .list-group-item.active {
        background-color: #3498db !important;
        color: #ffffff !important;
        border-color: #3498db !important;
        font-weight: bold;
    }
    
    /* عند مرور الماوس أيضاً */
    #searchResults .list-group-item:hover {
        background-color: #f1f1f1;
        cursor: pointer;
    }

    .pos-layout { height: 88vh; display: flex; gap: 15px; font-family: 'Cairo', sans-serif; }
    .pos-left { flex: 1; display: flex; flex-direction: column; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
    
    /* === تصميم عصري للوحة الدفع === */
    .pos-right { 
        width: 340px; /* عرض أقل */
        flex-shrink: 0; 
        background: linear-gradient(145deg, #2c3e50, #34495e); /* تدرج لوني فخم */
        color: white; 
        border-radius: 16px; 
        display: flex; flex-direction: column;
        padding: 15px; /* هوامش داخلية أقل */
        box-shadow: -5px 0 25px rgba(0,0,0,0.15);
        font-size: 0.85rem; /* تصغير الخط العام */
    }

    /* تحسين حقول الإدخال والقوائم */
    .pos-right .form-select, .pos-right .form-control {
        font-size: 0.85rem;
        border-radius: 8px;
        background-color: rgba(255, 255, 255, 0.1); /* خلفية شفافة */
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
    }
    
    /* ✅ إصلاح مشكلة اختفاء النص داخل القوائم */
    .pos-right select option {
        background-color: #fff !important;
        color: #333 !important;
    }

    .pos-right .form-select:focus, .pos-right .form-control:focus {
        background-color: rgba(255, 255, 255, 0.2);
        border-color: #3498db;
        box-shadow: none;
        color: #fff;
    }
    
    .product-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
    .unit-select { border: 1px solid #3498db; padding: 2px; font-size: 0.9rem; border-radius: 4px; width: 100px; background: #f8fbff; font-weight: bold; color: #2c3e50; }
    .unit-text { font-weight: bold; color: #555; padding: 5px; }
    .search-active { background-color: #3498db !important; color: white !important; }
    
    /* حاوية طرق الدفع المرنة */
    .payment-section-dynamic {
        display: flex;
        flex-direction: column;
        gap: 8px; /* تقليل المسافات */
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }
    
    /* تنسيق الصف الواحد ليكون مستطيلاً مرتباً */
    .pay-row {
        display: flex;
        gap: 5px;
        align-items: center;
        background: rgba(0, 0, 0, 0.2); /* خلفية أغمق قليلاً */
        padding: 4px; /* تقليل الحشو */
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .pay-select { flex: 1; height: 38px; font-size: 0.85rem; background: transparent; border: none; color: #fff; padding: 5px; }
    .pay-input { flex: 1; height: 32px; font-size: 0.95rem; font-weight: bold; text-align: center; background: transparent; border: none; color: #2ecc71; }
    .pay-input::placeholder { color: rgba(255,255,255,0.3); }
    
    .btn-add-pay { background: #27ae60; color: white; border: none; border-radius: 6px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }
    .btn-remove-pay { background: #c0392b; color: white; border: none; border-radius: 6px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }

    .customer-balance-box { background: rgba(0,0,0,0.2); padding: 8px; border-radius: 8px; margin-bottom: 10px; display: none; font-size: 0.85rem; }
    
    /* تصميم الطباعة */
    @media print {
        @page { size: landscape; margin: 10mm; }
        body * { visibility: hidden; }
        #printSection, #printSection * { visibility: visible; }
        #printSection { 
            position: absolute; left: 0; top: 0; width: 100%; 
            background: white; padding: 20px; direction: rtl;
        }
        .no-print, .btn, .dropdown, select, input, .modal-footer, .btn-close { display: none !important; }
        
        table { width: 100% !important; border-collapse: collapse; font-size: 11px; font-family: 'Cairo', sans-serif; }
        th { background-color: #eee !important; -webkit-print-color-adjust: exact; color: #000; padding: 8px; border: 1px solid #000 !important; }
        td { padding: 6px; border: 1px solid #000 !important; text-align: center; }
        
        .report-header { display: flex !important; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .report-logo img { max-height: 70px; }
        
        img { display: block !important; max-width: 100%; }
        .text-decoration-underline { text-decoration: underline !important; }
    }

    /* أزرار الإجراءات الجديدة */
    .pos-action-btn { 
        min-width: 200px; 
        border: none; 
        border-radius: 10px; 
        transition: all 0.3s ease; 
        color: white !important;
        font-size: 1rem; /* تصغير الخط */
    }
    .btn-save-invoice { 
        background: linear-gradient(135deg, #27ae60, #2ecc71); 
        box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
    }
    .btn-save-invoice:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4); 
        background: linear-gradient(135deg, #2ecc71, #27ae60);
    }
    .btn-cancel-invoice { 
        background: linear-gradient(135deg, #c0392b, #e74c3c); 
        box-shadow: 0 4px 15px rgba(192, 57, 43, 0.3);
    }
    .btn-cancel-invoice:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 6px 20px rgba(192, 57, 43, 0.4); 
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    /* تحسينات جدول السجل (Recent Sales) */
    /* تحسينات جدول السجل (Recent Sales) */
    /* تحسينات جدول السجل (Recent Sales) */
    .c-0 { width: 10%; text-align: center; font-size: 0.85rem; } /* # */
    .c-1 { width: 10%; font-size: 0.9rem; } /* Customer */
    .c-2 { width: 9%; font-size: 0.9rem; font-weight: bold; } /* Total */
    .c-3 { width: 9%; font-size: 0.9rem; } /* Paid */
    .c-4 { width: 9%; font-size: 0.9rem; } /* Due */
    .c-5 { width: 10%; text-align: center; font-size: 0.85rem; } /* Status */
    .c-6 { width: 12%; text-align: center; font-size: 0.8rem; white-space: normal !important; line-height: 1.2; } /* Date - التفاف النص */
    .c-7 { width: 10%; text-align: center; font-size: 0.8rem; } /* User */
    .c-9 { width: 10%; text-align: center; } /* Returns */
    .c-8 { width: 11%; text-align: center; white-space: nowrap; } /* Actions */

    /* أزرار إجراءات صغيرة جداً */
    .c-8 .btn-sm-custom {
        padding: 2px 5px;
        font-size: 10px;
        line-height: 1.2;
    }

    /* فرض قص النص الزائد حتى لا يخرب الجدول */
    #historyTable td, #historyTable th {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    /* 📱 Tablet & Mobile Responsive POS 📱 */
    @media (max-width: 992px) {
        .pos-layout {
            flex-direction: column;
            height: auto !important;
        }
        .pos-left, .pos-right {
            width: 100% !important;
            flex: none !important;
            margin-bottom: 15px;
            height: auto !important;
        }
        .pos-left .table-responsive {
            max-height: 300px;
            overflow-y: auto;
        }
    }
</style>

<div class="container-fluid py-3">
    <div class="pos-layout">
        
        {{-- ================= القسم الأيمن: البحث وجدول المنتجات ================= --}}
        <div class="pos-left">
            <div class="p-3 border-bottom">
                <div class="position-relative">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-0 text-primary"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="barcodeInput" class="form-control bg-light border-0" placeholder="{{ __('scan_barcode_or_search_f3') }}" autocomplete="off">
                    </div>
                    <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="top: 100%; z-index: 9999; display: none;"></div>
                </div>
            </div>

            <div class="table-responsive flex-grow-1">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light sticky-top" style="z-index: 1; display: none;">
                        <tr>
                            <th class="ps-4" width="5%">#</th>
                            <th width="15%">{{ __('barcode') }}</th>
                            <th width="30%">{{ __('product') }}</th>
                            <th width="15%">{{ __('unit') }}</th>
                            <th class="text-center" width="10%">{{ __('price') }}</th>
                            <th class="text-center" width="15%">{{ __('quantity') }}</th>
                            <th class="text-center" width="10%">{{ __('total') }}</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody"></tbody>
                </table>
                <div id="emptyCartMsg" class="text-center py-5 mt-5">
                    <i class="fas fa-shopping-cart fa-4x text-light mb-3"></i>
                    <h4 class="text-muted fw-light">{{ __('start_sale') }}</h4>
                </div>
            </div>

            {{-- ✅ أزرار التحكم الجديدة في المنتصف --}}
            <div class="p-3 border-top bg-light d-flex gap-3 justify-content-center">
                <button class="btn btn-lg py-3 fw-bold shadow-sm pos-action-btn btn-save-invoice" onclick="submitInvoice()">
                    <i class="fas fa-save me-2"></i> {{ __('save_and_print_f9') }}
                </button>
                <button class="btn btn-lg py-3 fw-bold shadow-sm pos-action-btn btn-cancel-invoice" onclick="clearCart()">
                    <i class="fas fa-times me-2"></i> {{ __('cancel') }}
                </button>
            </div>
        </div>

        {{-- ================= القسم الأيسر: الشريط الجانبي ================= --}}
        <div class="pos-right">
            
            {{-- رأس القائمة (رقم الفاتورة + الأزرار) --}}
           <div class="d-flex justify-content-between align-items-center mb-2">
                <span id="invoiceNumberDisplay" class="fw-bold m-0 text-warning" style="font-size: 1rem;">#{{ $nextInvoice }}</span>
                <div class="d-flex gap-1">
                    {{-- ✅ زر فتح الصندوق --}}
                    <button id="btnOpenShift" class="btn btn-success btn-sm text-white fw-bold py-1 px-2" 
                            data-bs-toggle="modal" data-bs-target="#openShiftModal"
                            title="{{ __('open_shift') }}">
                        <i class="fas fa-door-open"></i>
                    </button>

                    {{-- زر الإرجاع --}}
                    <button type="button" class="btn btn-warning btn-sm text-dark py-1 px-2" onclick="openReturnModal()" title="{{ __('return_items') }}">
                        <i class="fas fa-undo"></i>
                    </button>

                    {{-- زر إغلاق الصندوق (مخفي، يظهره النظام عند الحاجة) --}}
                    <button id="btnCloseShift" class="btn btn-warning btn-sm text-dark fw-bold d-none py-1 px-2" onclick="openCloseShiftModal()" title="{{ __('close_shift') }}">
                        <i class="fas fa-cash-register"></i>
                    </button>

                    {{-- زر الأرشيف --}}
                    <button type="button" class="btn btn-info btn-sm text-white py-1 px-2" onclick="openHistoryModal()" title="{{ __('invoices_history') }}">
                        <i class="fas fa-history"></i>
                    </button>
                    
                    {{-- زر التصفير --}}
                    <button class="btn btn-danger btn-sm py-1 px-2" onclick="resetPosScreen()" title="{{ __('reset_screen') }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-2">
                <select id="customerSelect" class="form-select form-select-sm" style="width: 100%">
                    <option value="">{{ __('cash_customer_general') }}</option>
                </select>

                <div id="customerBalanceBox" class="customer-balance-box mt-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>{{ __('current_balance') }}</small>
                        <span id="balanceDisplay" class="fw-bold">0.00</span>
                    </div>
                </div>
            </div>

            <hr class="border-secondary my-1">

            {{-- الخصم والتقريب --}}
            <div class="mb-2">
                <div class="d-flex gap-1 mb-2">
                    <select id="discountType" class="form-select form-select-sm" style="width: 35%" onchange="calculateRemaining()">
                        <option value="fixed">{{ __('amount') }}</option>
                        <option value="percent">{{ __('percentage') }}</option>
                    </select>
                    <input type="number" id="discountValue" class="form-control form-control-sm text-center" placeholder="{{ __('discount_value') }}" oninput="calculateRemaining()">
                </div>
                
                <button class="btn btn-sm btn-outline-warning w-100 py-1" onclick="roundTotalAmount()" style="font-size: 0.8rem;">
                    <i class="fas fa-magic me-1"></i> {{ __('round_amount') }}
                </button>
            </div>

            {{-- ملخص المبالغ --}}
            <div class="mb-2">
                <div class="d-flex justify-content-between mb-1 text-white-50" style="font-size: 0.8rem;">
                    <span>{{ __('items_count') }} <span id="itemsCount" class="fw-bold text-white">0</span></span>
                    <span>{{ __('subtotal') }} <span id="subTotalDisplay">0.00</span></span>
                </div>

                <div class="d-flex justify-content-between align-items-end mt-2">
                    <span class="fs-6">{{ __('net_total') }}</span>
                    <span id="footerTotal" class="text-success fs-3">0.00</span>
                </div>
            </div>

            <hr class="border-secondary my-1">

           {{-- طرق الدفع --}}
            <div class="mb-2">
                <label class="small text-white-50 mb-1">{{ __('payment') }}</label>
                <div id="paymentRowsContainer" class="payment-section-dynamic">
                    <div class="pay-row" id="payRow_0">
                        <select class="form-select pay-select method-select">
                            <option value="cash">{{ __('cash') }}</option>
                            <option value="card">{{ __('card') }}</option>
                            <option value="bank">{{ __('bank_transfer') }}</option>
                            <option value="paypal">{{ __('paypal_cards') }}</option>
                        </select>
                        <input type="number" class="form-control pay-input amount-input" placeholder="0.00" oninput="calculateRemaining()">
                        <button class="btn-add-pay" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                {{-- حاوية أزرار باي بال تظهر فقط عند اختيار باي بال --}}
                <div id="paypal-button-container" class="mt-2" style="display: none;"></div>
            </div>

            {{-- المتبقي وزر الحفظ --}}
            <div class="mt-auto pt-2 border-top border-secondary">
                <div class="d-flex justify-content-between align-items-end mb-2">
                    <span id="diffLabel" class="fs-6 fw-bold">{{ __('remaining') }}</span>
                    <span id="remainingAmount" class="fw-bold text-success fs-3">0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- النوافذ المنبثقة (Modals) --}}
{{-- ============================================ --}}

{{-- 1. نافذة سجل الفواتير --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h6 class="modal-title mb-0"><i class="fas fa-history me-2"></i>{{ __('sales_history') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-0 bg-white">
                <div class="p-3 bg-white border-bottom shadow-sm d-print-none">
                    {{-- شريط فلاتر موحد --}}
                    <div class="filter-bar-container border rounded-pill d-flex align-items-center w-100 overflow-hidden bg-white" style="border-color: #ced4da !important;">
                        
                        {{-- 1. العميل --}}
                        <div class="filter-segment flex-fill d-flex align-items-center px-3 border-end position-relative" style="min-width: 300px;">
                            <i class="fas fa-user text-muted small me-2"></i>
                            <select id="filterCustomer" class="form-select form-select-sm border-0 shadow-none bg-transparent p-0 w-100 auto-filter" aria-label="{{ __('customer') }}"></select>
                        </div>

                        {{-- 2. حالة الدفع --}}
                        <div class="filter-segment flex-fill d-flex align-items-center px-3 border-end position-relative" style="min-width: 150px;">
                            <i class="fas fa-filter text-muted small me-2"></i>
                            <select id="filterPaymentStatus" class="form-select form-select-sm border-0 shadow-none bg-transparent p-0 auto-filter" style="width: auto; flex-grow: 1;">
                                <option value="">{{ __('payment_status_all') }}</option>
                                <option value="paid">{{ __('paid') }}</option>
                                <option value="unpaid">{{ __('unpaid') }}</option>
                                <option value="partial">{{ __('partial_payment') }}</option>
                                <option value="overpaid">{{ __('overpaid') }}</option>
                                <option value="has_returns">{{ __('has_returns') }}</option>
                            </select>
                        </div>

                        {{-- 3. التاريخ --}}
                        <div class="filter-segment flex-fill d-flex align-items-center px-3 border-end position-relative" style="min-width: 280px;">
                            <i class="far fa-calendar-alt text-muted small me-2"></i>
                            <input type="date" id="filterDateFrom" class="form-control form-control-sm border-0 shadow-none bg-transparent p-0 auto-filter" style="max-width: 110px;" placeholder="{{ __('from') }}">
                            <i class="fas fa-arrow-left text-muted mx-2 small" style="font-size: 0.7rem;"></i>
                            <input type="date" id="filterDateTo" class="form-control form-control-sm border-0 shadow-none bg-transparent p-0 auto-filter" style="max-width: 110px;" placeholder="{{ __('to') }}">
                        </div>

                        {{-- 4. الترتيب والعرض --}}
                        <div class="filter-segment d-flex align-items-center px-3 gap-2 bg-light">
                            <i class="fas fa-sort text-muted small"></i>
                            <select id="filterSortBy" class="form-select form-select-sm border-0 shadow-none bg-transparent p-0 fw-bold text-primary auto-filter" style="width: auto;"><option value="created_at">{{ __('date') }}</option><option value="total">{{ __('value') }}</option><option value="due">{{ __('debt') }}</option></select>
                            <span class="text-muted small">|</span>
                            <select id="filterSortOrder" class="form-select form-select-sm border-0 shadow-none bg-transparent p-0 text-muted auto-filter" style="width: auto;"><option value="desc">{{ __('descending') }}</option><option value="asc">{{ __('ascending') }}</option></select>
                            <span class="text-muted small">|</span>
                            <select id="filterLimit" class="form-select form-select-sm border-0 shadow-none bg-transparent p-0 fw-bold auto-filter" style="width: auto;"><option value="10">10</option><option value="50">50</option><option value="100">100</option><option value="all">{{ __('all') }}</option></select>
                        </div>
                    </div>

                    <style>
                        /* تحسين مظهر شريط الفلاتر */
                        .filter-bar-container:focus-within {
                            border-color: #86b7fe !important;
                            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                        }
                        .filter-segment:hover {
                            background-color: #f8f9fa;
                        }
                        
                        /* إصلاح عرض Select2 داخل الفلتر */
                        .filter-bar-container .select2-container {
                            width: 100% !important;
                        }
                        .filter-bar-container .select2-selection {
                            border: none !important;
                            background: transparent !important;
                            box-shadow: none !important;
                        }
                        /* توسيع قائمة البحث والنتائج */
                        .select2-container--bootstrap-5 .select2-dropdown .select2-search{
                            padding: 0.5rem;
                        }
                        .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
                            width: 100% !important;
                            padding: 0.5rem;
                        }
                        .select2-container--bootstrap-5 .select2-dropdown {
                            min-width: 300px !important; /* ضمان عرض كافي للقائمة المنسدلة */
                        }
                    </style>

                    {{-- الصف الثاني: الأزرار --}}
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <div class="position-relative">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btn_open_cols">
                                <i class="fas fa-columns me-1"></i> {{ __('customize_columns') }}
                            </button>
                            {{-- قائمة الأعمدة (Dropup) --}}
                            <div id="menu_cols" class="dropdown-menu shadow p-2" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1050; min-width: 200px;">
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="0" checked> # {{ __('invoice') }}</label>
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="1" checked> {{ __('customer') }}</label>
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="2" checked> {{ __('total') }}</label>
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="3" checked> {{ __('paid') }}</label>
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="4" checked> {{ __('remaining') }}</label>
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="5" checked> {{ __('status') }}</label>
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="6" checked> {{ __('date') }}</label>
                                <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="7" checked> {{ __('user') }}</label>
                                <label class="dropdown-item text-warning"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="9" checked> {{ __('returns') }}</label>
                                <div class="dropdown-divider"></div>
                                <label class="dropdown-item text-danger"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="8" checked> {{ __('actions') }}</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                             <button onclick="printSalesReport()" class="btn btn-dark btn-sm rounded-pill px-3"><i class="fas fa-print me-1"></i> {{ __('print_report') }}</button>
                                                          <button onclick="sendSalesReportWhatsapp()" class="btn btn-success btn-sm rounded-pill px-3"><i class="fab fa-whatsapp me-1"></i> {{ __('send_whatsapp') }}</button>
                             <button onclick="sendSalesReportEmail()" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-envelope me-1"></i> {{ __('send_email') }}</button>
                        </div>
                    </div>
                </div>


                <div id="printSection" class="p-3">
                    <div class="report-header d-none d-print-flex">
                        <div class="text-end"><h4 id="printStoreName">{{ __('store') }}</h4><p id="printStoreAddress"></p><p id="printStorePhone"></p></div>
                        <div class="text-center"><h3>{{ __('sales_report') }}</h3><p>{{ date('Y-m-d') }}</p></div>
                        <div class="report-logo"><img id="printStoreLogo" src="" alt="Logo"></div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center w-100" id="historyTable" style="table-layout: fixed;">
                            <thead class="table-light">
                                <tr>
                                    <th class="c-0">#</th>
                                    <th class="c-1">{{ __('customer') }}</th>
                                    <th class="c-2">{{ __('total') }}</th>
                                    <th class="c-3">{{ __('paid') }}</th>
                                    <th class="c-4">{{ __('remaining') }}</th>
                                    <th class="c-5">{{ __('status') }}</th>
                                    <th class="c-6">{{ __('date') }}</th>
                                    <th class="c-7">{{ __('by') }}</th>
                                    <th class="c-9">{{ __('returns') }}</th>
                                    <th class="c-8 no-print">{{ __('options') }}</th>
                                </tr>
                            </thead>
                            <tbody id="historyList"></tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr><td colspan="2">{{ __('total') }}</td><td id="sumTotal">0.00</td><td id="sumPaid">0.00</td><td id="sumDue">0.00</td><td colspan="5"></td></tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="paginationControls" class="mt-3 p-2 border-top d-flex justify-content-center bg-light"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2. نافذة معاينة الفاتورة --}}
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('invoice_preview') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="card mb-3 p-2 d-print-none">
                    <div class="d-flex gap-4 justify-content-center">
                        <div class="form-check" id="ibanOptionDiv" style="display:none;">
                            <input class="form-check-input" type="checkbox" id="showIban" onchange="toggleOfficialMarks()">
                            <label class="form-check-label fw-bold" for="showIban">{{ __('add_iban_details') }}</label>
                        </div>
                        <div class="form-check" id="stampOptionDiv" style="display:none;">
                            <input class="form-check-input" type="checkbox" id="showStamp" onchange="toggleOfficialMarks()">
                            <label class="form-check-label fw-bold" for="showStamp">{{ __('add_store_stamp') }}</label>
                        </div>
                        <div class="form-check" id="signatureOptionDiv" style="display:none;">
                            <input class="form-check-input" type="checkbox" id="showSignature" onchange="toggleOfficialMarks()">
                            <label class="form-check-label fw-bold" for="showSignature">{{ __('add_owner_signature') }}</label>
                        </div>
                    </div>
                </div>

                <div id="printableInvoice" class="bg-white p-4 mx-auto shadow-sm" style="max-width: 210mm; min-height: 297mm; position: relative;">
                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-8 text-end">
                            <h2 class="fw-bold text-primary" id="invStoreName"></h2>
                            <p class="mb-1 text-muted" id="invStoreAddress"></p>
                            <p class="mb-1">{{ __('tax_number') }} <span id="invTaxNumber" class="fw-bold">-</span></p>
                        </div>
                        <div class="col-4 text-start">
                            <img id="invLogo" src="" alt="Logo" style="max-width: 120px; max-height: 100px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <h5 class="fw-bold">{{ __('tax_invoice') }}</h5>
                            <p class="mb-1">{{ __('invoice_number') }} <span id="invNumber" class="fw-bold text-danger"></span></p>
                            <p class="mb-1">{{ __('date') }} <span id="invDate"></span></p>
                        </div>
                        <div class="col-6 text-start">
                            <h6 class="fw-bold">{{ __('invoice_to') }}</h6>
                            <p class="mb-1" id="invCustomerName"></p>
                        </div>
                    </div>
                   <table class="table table-striped table-sm text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>{{ __('product') }}</th>
                                <th>{{ __('barcode') }}</th>
                                <th>{{ __('quantity') }}</th>
                                <th>{{ __('price') }}</th>
                                <th>{{ __('total') }}</th>
                            </tr>
                        </thead>
                        <tbody id="invItemsBody"></tbody>
                        <tfoot class="fw-bold bg-light">
                            <tr>
                                <td colspan="5" class="text-end pe-3">{{ __('grand_total') }}</td>
                                <td id="invTotal" class="text-dark fs-5"></td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- سطر الآيبان المضاف --}}
                    <div id="ibanBox" style="display:none; margin-top: 20px; padding: 10px; border: 1px dashed #ccc; text-align: right; background: #fcfcfc; direction: rtl;">
                        <p class="mb-1 fw-bold text-decoration-underline">{{ __('bank_payment_details') }}</p>
                        <p class="mb-1">{{ __('bank') }} <span id="invIbanBank"></span></p>
                        <p class="mb-1">{{ __('account_holder') }} <span id="invIbanHolder"></span></p>
                        <p class="mb-1">{{ __('iban') }} <span id="invIbanNumber" dir="ltr" class="fw-bold"></span></p>
                        <div id="paymentNotice" class="mt-2 text-primary fw-bold" style="font-size: 0.9rem;">
                            {{ __('payment_notice') }} (<span id="invStoreWhatsapp"></span>)
                        </div>
                    </div>
                    <div class="row mt-4" id="officialMarksArea">
                        <div class="col-6 text-center position-relative">
                            <div id="stampBox" style="display:none;">
                                <p class="mb-2 fw-bold text-decoration-underline">{{ __('store_stamp') }}</p>
                                <img id="invStamp" src="" style="width: 140px; opacity: 0.8; transform: rotate(-15deg);">
                            </div>
                        </div>
                        <div class="col-6 text-center position-relative">
                            <div id="signatureBox" style="display:none;">
                                <p class="mb-2 fw-bold text-decoration-underline">{{ __('admin_signature') }}</p>
                                <img id="invSignature" src="" style="width: 150px; opacity: 0.8;">
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-success" onclick="shareInvoiceWhatsapp()"><i class="fab fa-whatsapp"></i> {{ __('send_whatsapp') }}</button>
                <button type="button" class="btn btn-primary" onclick="shareInvoiceEmail()"><i class="fas fa-envelope"></i> {{ __('send_email') }}</button>
                <button type="button" class="btn btn-primary" onclick="printOfficialInvoice()"><i class="fas fa-print"></i> {{ __('print') }}</button>
            </div>
        </div>
    </div>
</div>
</div>

{{-- 3. نافذة فتح الصندوق (معدلة: بها زر إغلاق وليست إجبارية الظهور) --}}
<div class="modal fade" id="openShiftModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-cash-register me-2"></i>{{ __('open_new_shift') }}</h5>
                {{-- 👇 زر الإغلاق المضاف 👇 --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold">{{ __('cash_in_drawer') }}</label>
                <div class="input-group">
                    <span class="input-group-text">SAR</span>
                    <input type="number" id="startCashInput" class="form-control form-control-lg text-center fw-bold" placeholder="0.00" min="0" step="0.1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success w-100 py-2 fw-bold" onclick="submitOpenShift()">
                    {{ __('confirm_open_drawer') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 4. نافذة إغلاق الصندوق (المفصلة) --}}
<div class="modal fade" id="closeShiftModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">{{ __('close_shift_z_report') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="alert alert-info py-2 text-center mb-3">
                    <span>{{ __('opening_time') }}</span> <strong id="shiftOpenDate" dir="ltr" class="ms-2">-</strong>
                </div>

                {{-- تفاصيل المبالغ الجديدة --}}
                <div class="row g-2 text-center mb-2">
                    <div class="col-6"><div class="border p-2 bg-light rounded"><small class="text-muted">{{ __('opening_cash') }}</small><br><b id="shiftStartCash" class="fs-5">0.00</b></div></div>
                    <div class="col-6"><div class="border p-2 bg-success text-white rounded"><small>{{ __('net_cash_drawer') }}</small><br><b id="shiftCashSales" class="fs-5">0.00</b></div></div>
                </div>
                
                <div class="row g-2 text-center mb-3">
                    <div class="col-4"><div class="border p-1 bg-light rounded"><small>{{ __('card') }}</small><br><b id="shiftCardSales">0</b></div></div>
                    <div class="col-4"><div class="border p-1 bg-light rounded"><small>{{ __('bank_transfer') }}</small><br><b id="shiftBankSales">0</b></div></div>
                    <div class="col-4"><div class="border p-1 bg-light rounded text-danger"><small>{{ __('credit_debt') }}</small><br><b id="shiftCreditSales">0</b></div></div>
                </div>

                <div class="alert alert-warning text-center">
                    <h5 class="m-0">{{ __('expected_in_drawer') }} <span id="shiftExpected" class="fw-bold text-danger fs-3">0.00</span></h5>
                </div>

                <hr>
                <label class="form-label fw-bold">{{ __('actual_amount_counted') }}</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white text-success">💵</span>
                    <input type="number" id="endCashInput" class="form-control text-center fw-bold text-success" placeholder="{{ __('enter_amount_present') }}">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                <button type="button" class="btn btn-warning fw-bold text-dark px-4" onclick="submitCloseShift()">{{ __('close_shift_post') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- 5. نافذة الإرجاع (Return Modal) --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">{{ __('return_product_refund') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label>{{ __('customer_optional') }}</label>
                        <select id="returnCustomerSelect" class="form-select" style="width:100%"></select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                           {{-- ✅ تم تحويله إلى بحث ذكي --}}
<select id="returnProductSelect" class="form-select" style="width:100%"></select>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered text-center table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('invoice_number') }}</th>
                                <th>{{ __('product') }}</th>
                                <th>{{ __('quantity') }}</th>
                                <th>{{ __('price') }}</th>
                                <th>{{ __('action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="returnResultsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 6. نافذة عرض تفاصيل المرتجعات (Returns Details Modal) --}}
<div class="modal fade" id="returnsDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>{{ __('returns_details_invoice') }} <span id="returnsSaleId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="returnsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="original-tab" data-bs-toggle="tab" data-bs-target="#original-content" type="button">
                            <i class="fas fa-file-invoice me-1"></i> {{ __('original_invoice') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="returns-tab" data-bs-toggle="tab" data-bs-target="#returns-content" type="button">
                            <i class="fas fa-undo me-1 text-danger"></i> {{ __('returns') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="final-tab" data-bs-toggle="tab" data-bs-target="#final-content" type="button">
                            <i class="fas fa-check-circle me-1 text-success"></i> {{ __('after_return') }}
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="returnsTabContent">
                    <!-- الفاتورة الأصلية -->
                    <div class="tab-pane fade show active" id="original-content" role="tabpanel">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h6 class="text-muted mb-1">{{ __('customer') }}</h6>
                                        <h5 id="returnsCustomerName" class="fw-bold">-</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted mb-1">{{ __('original_total') }}</h6>
                                        <h5 id="returnsOriginalTotal" class="fw-bold text-primary">0.00</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted mb-1">{{ __('invoice_date') }}</h6>
                                        <h6 id="returnsDate" class="small">-</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h6 class="fw-bold mb-2"><i class="fas fa-list me-1"></i> {{ __('invoice_items_current') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('product') }}</th>
                                        <th>{{ __('unit') }}</th>
                                        <th>{{ __('quantity') }}</th>
                                        <th>{{ __('price') }}</th>
                                        <th>{{ __('total') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="returnsItemsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- المرتجعات -->
                    <div class="tab-pane fade" id="returns-content" role="tabpanel">
                        <div class="alert alert-warning d-flex align-items-center mb-3">
                            <i class="fas fa-info-circle me-2 fs-4"></i>
                            <div>
                                <strong>{{ __('total_returns') }}</strong>
                                <span id="returnsTotalReturns" class="fw-bold fs-5 ms-2">0.00</span>
                            </div>
                        </div>
                        <h6 class="fw-bold mb-2"><i class="fas fa-undo me-1 text-danger"></i> {{ __('returned_items') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center">
                                <thead class="table-danger">
                                    <tr>
                                        <th>{{ __('product') }}</th>
                                        <th>{{ __('unit') }}</th>
                                        <th>{{ __('returned_quantity') }}</th>
                                        <th>{{ __('price') }}</th>
                                        <th>{{ __('refunded_amount') }}</th>
                                        <th>{{ __('reason') }}</th>
                                        <th>{{ __('by') }}</th>
                                        <th>{{ __('date') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="returnsReturnedItemsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- الفاتورة بعد الإرجاع -->
                    <div class="tab-pane fade" id="final-content" role="tabpanel">
                        <div class="card border-success mb-3 shadow-sm">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
                                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>{{ __('final_invoice_current_status') }}</h6>
                                <span class="badge bg-white text-success fw-bold" id="finalStatusBadge">{{ __('modified') }}</span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 border-bottom pb-3">
                                    <div class="col-md-6 border-start">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-hashtag text-muted me-2"></i>
                                            <span class="text-muted small me-2">{{ __('invoice_number_label') }}</span>
                                            <span class="fw-bold" id="finalInvNumber">-</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-user text-muted me-2"></i>
                                            <span class="text-muted small me-2">{{ __('customer_label') }}</span>
                                            <span class="fw-bold" id="finalCustomer">-</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-alt text-muted me-2"></i>
                                            <span class="text-muted small me-2">{{ __('date_label') }}</span>
                                            <span id="finalDate">-</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row g-2 text-center">
                                            <div class="col-4">
                                                <div class="p-2 bg-light rounded shadow-xs border">
                                                    <div class="text-muted x-small mb-1">{{ __('original_total') }}</div>
                                                    <div id="finalOriginalTotal" class="fw-bold text-dark fs-6">0.00</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 bg-danger bg-opacity-10 rounded shadow-xs border border-danger border-opacity-25">
                                                    <div class="text-danger x-small mb-1">{{ __('total_returned') }}</div>
                                                    <div id="finalReturnsAmount" class="fw-bold text-danger fs-6">-0.00</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 bg-success bg-opacity-10 rounded shadow-xs border border-success border-opacity-25">
                                                    <div class="text-success x-small mb-1">{{ __('current_net') }}</div>
                                                    <div id="returnsFinalTotal" class="fw-bold text-success fs-6">0.00</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="fw-bold text-success mb-3"><i class="fas fa-box me-1"></i> {{ __('remaining_invoice_items') }}</h6>
                                <div class="table-responsive rounded shadow-sm border">
                                    <table class="table table-sm table-hover text-center mb-0">
                                        <thead class="table-success text-dark">
                                            <tr>
                                                <th class="py-2">#</th>
                                                <th class="py-2 text-start px-3">{{ __('product') }}</th>
                                                <th class="py-2">{{ __('unit') }}</th>
                                                <th class="py-2">{{ __('quantity') }}</th>
                                                <th class="py-2">{{ __('price') }}</th>
                                                <th class="py-2 text-end px-3">{{ __('total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="finalItemsBody"></tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-4 d-flex justify-content-end">
                                    <div style="min-width: 250px;">
                                        <div class="d-flex justify-content-between py-1 border-bottom border-dashed">
                                            <span class="text-muted small">{{ __('net_label') }}</span>
                                            <span class="fw-bold" id="finalItemsTotal">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-1 border-bottom border-dashed">
                                            <span class="text-muted small text-info">{{ __('paid_label') }}</span>
                                            <span class="fw-bold text-info" id="returnsFinalPaid">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2 border-bottom bg-danger bg-opacity-10 px-2 rounded mt-1">
                                            <span class="text-danger small fw-bold">{{ __('remaining_label') }}</span>
                                            <span class="fw-bold text-danger fs-5" id="returnsFinalDue">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-success" onclick="shareReturnsWhatsapp()"><i class="fab fa-whatsapp me-1"></i> {{ __('whatsapp') }}</button>
                <button type="button" class="btn btn-primary" onclick="shareReturnsEmail()"><i class="fas fa-envelope me-1"></i> {{ __('email') }}</button>
                <button type="button" class="btn btn-primary" onclick="printReturnsReport()"><i class="fas fa-print me-1"></i> {{ __('print') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
{{-- Bootstrap Bundle already loaded in layout --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&currency=USD"></script>

<script>
    // ✅ الحل الجذري والنهائي: اكتشاف الرابط تلقائياً من المتصفح (Auto-Detect)
    // هذا يلغي الحاجة لضبط .env بشكل دقيق، ويعمل فوراً على أي سيرفر أو بروتوكول
    const getBaseUrl = () => {
        const path = window.location.pathname;
        const marker = '/store-owner';
        const markerIndex = path.indexOf(marker);
        if (markerIndex !== -1) {
            return window.location.origin + path.substring(0, markerIndex);
        }
        return window.location.origin;
    };
    const APP_URL = getBaseUrl();
    console.log('🔗 Auto-Detected System URL:', APP_URL);

    // ✅ دالة تصحيح الروابط للعمل في المجلدات الفرعية
    const fixUrl = (url) => {
        if (!url) return url;
        let finalUrl = url;
        // إذا كان الرابط يبدأ بـ http، نجعل المسار فقط
        if (url.startsWith('http')) {
            try {
                const u = new URL(url);
                finalUrl = u.pathname + u.search;
            } catch(e) { return url; }
        }
        
        // جرد المجلد الفرعي من مسار الصفحة الحالي
        // مثال: /system/store-owner/pos -> /system
        const currentPath = window.location.pathname;
        const subDir = currentPath.split('/store-owner')[0];
        
        if (subDir && subDir !== '/' && !finalUrl.startsWith(subDir)) {
            finalUrl = subDir + (finalUrl.startsWith('/') ? '' : '/') + finalUrl;
        }
        return finalUrl;
    };

    let cart = [];
    let currentFocus = -1;
    let debounceTimer;
    let selectedCustomer = null;
    let isShiftOpen = false;
    let roundingDifference = 0; 

    // متغيرات أرقام الفواتير القادمة
    const nextInvoiceNumber = "#{{ $nextInvoice }}";
    const nextWithdrawalNumber = "#{{ $nextWithdrawal ?? 'SOV-Unknown' }}"; 

    // ✅ دالة تنسيق الأرقام (إزالة الأصفار العشرية إذا كان رقماً صحيحاً)
    function formatMoney(amount) {
        let val = parseFloat(amount) || 0;
        return Number.isInteger(val) ? val : val.toFixed(2);
    } 

    // تعريف النوافذ
    let historyModal;
    let invoiceModal;

    $(document).ready(function() {
        // تهيئة يدوية لقوائم Bootstrap
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });
        
        // تهيئة النوافذ
        historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
        invoiceModal = new bootstrap.Modal(document.getElementById('invoiceModal'));

        toastr.options = { "positionClass": "toast-top-left", "timeOut": "2000" };

        let $customerSelect = $('#customerSelect');
        
        // تهيئة Select2 للبحث عن العملاء
        $customerSelect.select2({
            theme: 'bootstrap-5',
            dir: "rtl",
            placeholder: "{{ __('search_customer') }}",
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: fixUrl("{{ route('store.pos.search-customers') }}"),
                dataType: 'json',
                delay: 250,
                data: function (params) { return { term: params.term }; },
                processResults: function (data) {
                    if (data.results.length === 1) {
                        let c = data.results[0];
                        // ✅ تحسين: التأكد من تمرير كل الخصائص بما فيها is_store_owner
                        let isStoreOwner = c.is_store_owner || (c.text && c.text.includes('صاحب المتجر')) || false;
                        
                        selectedCustomer = { 
                            id: c.id, 
                            name: c.text, 
                            balance: parseFloat(c.balance || 0),
                            is_store_owner: isStoreOwner 
                        };
                        
                        updateCustomerBalanceDisplay();
                        toggleWithdrawalMode(isStoreOwner); // ✅ تفعيل وضع المسحوبات فوراً

                        let option = new Option(c.text, c.id, true, true);
                        $customerSelect.append(option).trigger('change');
                        $customerSelect.select2('close');
                    }
                    return { results: data.results }; 
                }
            }
        });

        $customerSelect.on('select2:open', function (e) {
            setTimeout(() => { document.querySelector('.select2-search__field').focus(); }, 50);
        });

        $customerSelect.on('select2:select', function (e) {
            let data = e.params.data;
            if (!data.balance && $(this).find(':selected').data('data')) { data = $(this).find(':selected').data('data'); }
            
            // ✅ فحص هل هو صاحب المتجر (تحسين الفحص ليشمل الاسم أيضاً)
            let isStoreOwner = data.is_store_owner || (data.text && data.text.includes('صاحب المتجر')) || false;
            if (isStoreOwner) isStoreOwner = true; // ضمان التحويل لبوليان

            selectedCustomer = { 
                id: data.id, 
                name: data.text || data.contact_name, 
                balance: parseFloat(data.balance || 0),
                is_store_owner: isStoreOwner // تخزين الحالة
            };
            
            updateCustomerBalanceDisplay();
            toggleWithdrawalMode(isStoreOwner);
        });

        $customerSelect.on('select2:clear', function (e) {
            selectedCustomer = null;
            $('#customerBalanceBox').fadeOut(200);
            toggleWithdrawalMode(false);
        });

        $('#barcodeInput').focus();
        checkShiftStatus(); // فحص الصندوق عند التحميل
    });
    
    // ✅ وظيفة تبديل وضع المسحوبات
    function toggleWithdrawalMode(enable) {
        if(enable) {
            $('.pos-right').css('background', 'linear-gradient(145deg, #7f8c8d, #2c3e50)'); // لون رمادي مميز
            
            // ✅ إخفاء تام لقسم الدفع
            $('#paymentRowsContainer').parent().addClass('d-none'); 
            $('#discountType').parent().parent().addClass('d-none');
            $('#diffLabel').parent().parent().addClass('d-none');
            
            $('.btn-save-invoice').removeClass('btn-save-invoice').addClass('btn-withdrawal-save')
                .html('<i class="fas fa-file-export me-2"></i> {{ __("record_withdrawal") }}')
                .css('background', '#e67e22');
                
            // تغيير رقم الفاتورة
            $('#invoiceNumberDisplay').text(nextWithdrawalNumber);

        } else {
            $('.pos-right').css('background', 'linear-gradient(145deg, #2c3e50, #34495e)');
            
            // ✅ إظهار قسم الدفع
            $('#paymentRowsContainer').parent().removeClass('d-none');
            $('#discountType').parent().parent().removeClass('d-none');
            $('#diffLabel').parent().parent().removeClass('d-none');

            $('.btn-withdrawal-save').removeClass('btn-withdrawal-save').addClass('btn-save-invoice')
                .html('<i class="fas fa-save me-2"></i> {{ __("save_and_print_f9") }}')
                .css('background', '');

            // استعادة رقم الفاتورة الطبيعي
            $('#invoiceNumberDisplay').text(nextInvoiceNumber);
        }
    }

    function updateCustomerBalanceDisplay() {
        let box = $('#customerBalanceBox');
        let display = $('#balanceDisplay');
        if (!selectedCustomer) { box.css('display', 'none'); return; }
        
        // إذا كان صاحب المتجر لا نعرض الرصيد
        if(selectedCustomer.is_store_owner) { 
            box.css('display', 'none'); 
            return; 
        }

        let bal = parseFloat(selectedCustomer.balance);
        box.css('display', 'block');
        let htmlContent = '';
        if (Math.abs(bal) < 0.01) {
            htmlContent = `<span class="text-primary fw-bold fs-5">0.00</span>`;
        } else if (bal > 0) {
            htmlContent = `<span class="text-success fw-bold fs-5" dir="ltr">+${bal.toFixed(2)} <i class="fas fa-arrow-up"></i></span> <small class="text-success fw-bold me-2">({{ __('has_credit') }})</small>`;
        } else {
            htmlContent = `<span class="text-danger fw-bold fs-5" dir="ltr">${Math.abs(bal).toFixed(2)} <i class="fas fa-arrow-down"></i></span> <small class="text-danger fw-bold me-2">({{ __('has_debt') }})</small>`;
        }
        display.html(htmlContent);
    }

    // --- منطق البحث ---
    const barcodeInput = document.getElementById('barcodeInput');
    const searchResults = document.getElementById('searchResults');
    
    function getProductTotalInCart(productId) {
        return cart.reduce((total, item) => {
            if (item.id === productId) {
                let unit = item.units.find(u => u.unit_id == item.selected_unit_id);
                let factor = unit ? parseFloat(unit.factor) : 1;
                return total + (item.qty * factor);
            }
            return total;
        }, 0);
    }

    document.addEventListener('click', function(event) {
        if (event.target !== barcodeInput && !searchResults.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });

    barcodeInput.addEventListener('keydown', function(e) {
        let items = searchResults.getElementsByTagName('a');
        if (e.key === 'ArrowDown') { currentFocus++; addActive(items); e.preventDefault(); }
        else if (e.key === 'ArrowUp') { currentFocus--; addActive(items); e.preventDefault(); }
        else if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(debounceTimer); 
            if (currentFocus > -1 && items && items[currentFocus]) {
                items[currentFocus].click();
            } else {
                performSearch(this.value.trim(), true);
            }
        }
    });

    barcodeInput.addEventListener('input', function(e) {
        let term = this.value.trim();
        currentFocus = -1;
        if (term.length === 0) { searchResults.style.display = 'none'; return; }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => performSearch(term, false), 300);
    });

    function performSearch(term, isEnterKey) {
        if(!term) return;
        fetch(fixUrl("{{ route('store.pos.search-products') }}") + "?term=" + term)
            .then(res => res.json())
            .then(data => {
                if (data.length === 1) {
                    checkExpiryAndAdd(data[0]); // ✅ تم الاستبدال للفحص قبل الإضافة
                    closeSearch();
                    barcodeInput.focus();
                } else if (data.length > 1) {
                    showSuggestions(data);
                } else {
                    if(isEnterKey) { toastr.error("{{ __('product_not_found') }}"); closeSearch(); }
                    else searchResults.style.display = 'none';
                }
            });
    }

    function showSuggestions(products) {
        searchResults.innerHTML = '';
        products.forEach((p) => {
            let item = document.createElement('a');
            item.className = 'list-group-item list-group-item-action d-flex align-items-center';
            item.href = "#";
            let qtyColor = p.quantity > 10 ? 'text-muted' : 'text-danger fw-bold';
            item.innerHTML = `
                <img src="${p.image}" class="product-thumb me-2">
                <div class="flex-grow-1">
                    <div class="fw-bold">${p.name}</div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted font-monospace">${p.default_barcode}</small>
                        <small class="${qtyColor}">{{ __('stock_label') }} ${parseFloat(p.quantity).toFixed(2)}</small>
                    </div>
                </div>
                <span class="badge bg-primary rounded-pill ms-2">${parseFloat(p.default_price).toFixed(2)}</span>
            `;
            item.onclick = function(e) { e.preventDefault(); checkExpiryAndAdd(p); closeSearch(); barcodeInput.focus(); }; // ✅ تم الاستبدال
            searchResults.appendChild(item);
        });
        searchResults.style.display = 'block';
    }

    function closeSearch() {
        barcodeInput.value = '';
        searchResults.style.display = 'none';
        currentFocus = -1;
    }

    // --- السلة ---
    function addToCart(product) {
        let unitId = product.default_unit_id;
        let unit = product.available_units.find(u => u.unit_id == unitId);
        let factor = unit ? parseFloat(unit.factor || 1) : 1;
        let maxStock = parseFloat(product.quantity); 

        let currentUsage = cart.reduce((acc, item) => {
            if(item.id === product.id) {
                let uFactor = item.units.find(u => u.unit_id == item.selected_unit_id)?.factor || 1;
                return acc + (item.qty * uFactor);
            }
            return acc;
        }, 0);
        
        if (currentUsage + (1 * factor) > maxStock) {
            promptStockAdjustment(product.name, product.id, maxStock, function(newStock) {
                product.quantity = newStock;
                addToCart(product);
            });
            return;
        }

        let existing = cart.find(item => item.id === product.id && item.selected_unit_id == unitId);
        if (existing) { 
            existing.qty++; 
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                sku: product.default_barcode,
                image: product.image,
                price: parseFloat(product.default_price),
                qty: 1,
                units: product.available_units,
                selected_unit_id: unitId,
                max_stock: maxStock
            });
        }
        renderCart();
        toastr.success("{{ __('item_added') }}");
    }

    function renderCart() {
        let tbody = document.getElementById('cartTableBody');
        tbody.innerHTML = '';
        let total = 0;
        let count = 0;

        cart.forEach((item, index) => {
            let rowTotal = item.price * item.qty;
            total += rowTotal;
            count += item.qty;

            let unitHtml = '';
            if(item.units && item.units.length > 1) {
                let options = item.units.map(u => `<option value="${u.unit_id}" ${u.unit_id == item.selected_unit_id ? 'selected' : ''}>${u.unit_name}</option>`).join('');
                unitHtml = `<select class="form-select form-select-sm unit-select" onchange="changeUnit(${index}, this.value)">${options}</select>`;
            } else {
                unitHtml = `<span class="unit-text">${item.units[0]?.unit_name || "{{ __('piece') }}"}</span>`;
            }

            let currentUnitObj = (item.units && item.units.length > 1) 
                ? item.units.find(u => u.unit_id == item.selected_unit_id)
                : (item.units[0] || {});
            let currentUnitName = currentUnitObj.unit_name || "{{ __('piece') }}";
            
            // ✅ فحص الكيلو للسماح بالكسور
            let isKilo = /kilo|kg|كيلو|كغ/i.test(currentUnitName);
            let stepVal = isKilo ? "0.001" : "1";

            tbody.innerHTML += `
                <tr>
                    <td><img src="${item.image}" class="product-thumb"></td>
                    <td class="small text-muted font-monospace align-middle">${item.sku}</td>
                    <td class="fw-bold align-middle">${item.name}</td>
                    <td class="align-middle">${unitHtml}</td>
                    <td class="text-center align-middle">${item.price.toFixed(2)}</td>
                    <td class="text-center align-middle">
                        <div class="input-group input-group-sm justify-content-center" style="width: 100px;">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${index}, 1)">+</button>
                            <input type="number" min="0.001" step="${stepVal}" class="form-control text-center p-0 fw-bold text-primary" 
                                   value="${item.qty}" 
                                   onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || (event.charCode == 46 && ${isKilo}) "
                                   oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                   onchange="updateQtyManual(${index}, this.value)" 
                                   onfocus="this.select()">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${index}, -1)">-</button>
                        </div>
                    </td>
                    <td class="text-center fw-bold align-middle">${rowTotal.toFixed(2)}</td>
                    <td class="align-middle"><button class="btn btn-sm text-danger" onclick="removeItem(${index})"><i class="fas fa-times"></i></button></td>
                </tr>
            `;
        });

        document.getElementById('itemsCount').innerText = count;
        document.getElementById('emptyCartMsg').style.display = cart.length ? 'none' : 'block';
        
        // إخفاء/إظهار صف العناوين (thead) بناءً على حالة السلة
        const tableHead = document.querySelector('#cartTableBody').closest('table').querySelector('thead');
        if (tableHead) {
            tableHead.style.display = cart.length ? '' : 'none';
        }
        
        window.currentTotal = total;
        roundingDifference = 0; 
        calculateRemaining();
    }

    window.changeUnit = (index, newUnitId) => {
        let item = cart[index];
        let newUnit = item.units.find(u => u.unit_id == newUnitId);
        
        if (newUnit) {
            let newFactor = parseFloat(newUnit.factor);
            let currentUnit = item.units.find(u => u.unit_id == item.selected_unit_id);
            let currentFactor = currentUnit ? parseFloat(currentUnit.factor) : 1;

            // حساب الكمية المستخدمة من نفس المنتج في أسطر أخرى بالسلة
            // (في حال كان الكاشير قد أضاف نفس المنتج مرتين بوحدات مختلفة)
            let usageOthers = getProductTotalInCart(item.id) - (item.qty * currentFactor);

            // التحقق: هل المخزون يكفي للوحدة الجديدة؟
            if (usageOthers + (item.qty * newFactor) > item.max_stock) {
                
                // 🔥 التعديل هنا: استدعاء نافذة تعديل المخزون بدلاً من رسالة الرفض 🔥
                promptStockAdjustment(item.name, item.id, item.max_stock, function(newStock) {
                    
                    // 1. تحديث المخزون في السلة محلياً لكل تواجد لهذا المنتج
                    cart.forEach(cItem => { if(cItem.id === item.id) cItem.max_stock = newStock; });

                    // 2. تطبيق تغيير الوحدة (الذي طلبه المستخدم) بعد توفر المخزون
                    item.selected_unit_id = newUnitId;
                    item.price = parseFloat(newUnit.price);
                    item.sku = newUnit.barcode;
                    if (newUnit.image) item.image = newUnit.image;

                    // 3. تحديث العرض وإظهار رسالة نجاح
                    renderCart();
                    toastr.success("{{ __('stock_adjusted_unit_changed') }}");
                });

                // إعادة رسم السلة لكي يعود الاختيار للوحدة القديمة (بصرياً) حتى يقرر المستخدم التعديل أو الإلغاء
                renderCart();
                return;
            }

            // في حال كان المخزون كافياً من البداية، يتم التغيير فوراً
            item.selected_unit_id = newUnitId;
            item.price = parseFloat(newUnit.price);
            item.sku = newUnit.barcode;
            if (newUnit.image) item.image = newUnit.image;
            renderCart();
        }
    };

    window.updateQty = (index, change) => {
        let item = cart[index];
        let newQty = item.qty + change;
        
        if (newQty <= 0) return;

        if (change > 0) {
            let unit = item.units.find(u => u.unit_id == item.selected_unit_id);
            let factor = unit ? parseFloat(unit.factor || 1) : 1;
            let otherUsage = cart.reduce((acc, cItem, cIdx) => {
                if(cItem.id === item.id && cIdx !== index) {
                    let uFactor = cItem.units.find(u => u.unit_id == cItem.selected_unit_id)?.factor || 1;
                    return acc + (cItem.qty * uFactor);
                }
                return acc;
            }, 0);

            if (otherUsage + (newQty * factor) > item.max_stock) {
                promptStockAdjustment(item.name, item.id, item.max_stock, function(newStock) {
                    cart.forEach(cItem => { if(cItem.id === item.id) cItem.max_stock = newStock; });
                    updateQty(index, change);
                });
                return;
            }
        }
        item.qty = newQty; 
        renderCart(); 
    };

    window.updateQtyManual = (index, value) => {
        let newQty = parseFloat(value);
        let item = cart[index];
        
        let currentUnitObj = (item.units && item.units.length > 1) 
            ? item.units.find(u => u.unit_id == item.selected_unit_id)
            : (item.units[0] || {});
        let isKilo = /kilo|kg|كيلو|كغ/i.test(currentUnitObj.unit_name || '');

        // ✅ منع الكسور لغير الكيلو
        if (!isKilo && !Number.isInteger(newQty)) {
            toastr.warning("{{ __('unit_no_fractions') }}");
            newQty = Math.round(newQty);
            if(newQty < 1) newQty = 1;
        }

        if (newQty > 0) {
            let unit = item.units.find(u => u.unit_id == item.selected_unit_id);
            let factor = unit ? parseFloat(unit.factor || 1) : 1;
            let otherUsage = cart.reduce((acc, cItem, cIdx) => {
                if(cItem.id === item.id && cIdx !== index) {
                    let uFactor = cItem.units.find(u => u.unit_id == cItem.selected_unit_id)?.factor || 1;
                    return acc + (cItem.qty * uFactor);
                }
                return acc;
            }, 0);

            if (otherUsage + (newQty * factor) > item.max_stock) {
                promptStockAdjustment(item.name, item.id, item.max_stock, function(newStock) {
                    cart.forEach(cItem => { if(cItem.id === item.id) cItem.max_stock = newStock; });
                    item.qty = newQty; 
                    renderCart();
                });
                renderCart();
                return;
            }
            item.qty = newQty;
        } else {
            toastr.warning("{{ __('invalid_qty') }}");
            item.qty = 1;
        }
        renderCart();
    };

    window.removeItem = (index) => { cart.splice(index, 1); renderCart(); };
    window.clearCart = () => { if(confirm("{{ __('confirm_clear_cart') }}")) { cart = []; renderCart(); } };

    function calculateRemaining() {
        let subTotal = window.currentTotal || 0;
        let discountType = document.getElementById('discountType').value;
        let discountVal = parseFloat(document.getElementById('discountValue').value) || 0;
        let discountAmount = 0;

        if (discountVal > 0) {
            if (discountType === 'fixed') discountAmount = discountVal;
            else discountAmount = subTotal * (discountVal / 100);
        }

        let netTotal = (subTotal - discountAmount) + roundingDifference;
        if (netTotal < 0) netTotal = 0;

        document.getElementById('subTotalDisplay').innerText = subTotal.toFixed(2);
        document.getElementById('footerTotal').innerText = netTotal.toFixed(2);

        let totalPaid = 0;
        let hasPaypal = false;
        document.querySelectorAll('.pay-row').forEach(row => {
            let method = row.querySelector('.method-select').value;
            let amount = parseFloat(row.querySelector('.amount-input').value) || 0;
            totalPaid += amount;
            if (method === 'paypal' && amount > 0.01) hasPaypal = true;
        });

        // إظهار/إخفاء أزرار باي بال
        if (hasPaypal) {
            $('#paypal-button-container').show();
            initializePaypal();
        } else {
            $('#paypal-button-container').hide();
        }

        let diff = netTotal - totalPaid;
        let label = "{{ __('remaining_label') }}";
        let colorClass = "text-success";
        let displayText = "0.00";

        if (diff > 0.001) {
            label = "{{ __('remaining_debt_label') }}";
            colorClass = "text-danger";
            displayText = diff.toFixed(2);
        } else if (diff < -0.001) {
            label = "{{ __('remaining_extra_label') }}";
            colorClass = "text-success"; 
            displayText = Math.abs(diff).toFixed(2);
        }

        $('#diffLabel').text(label);
        $('#remainingAmount').text(displayText).removeClass().addClass('fw-bold ' + colorClass);
        
        return { totalBill: netTotal, totalPaid, diff, discountAmount, roundingDifference };
    }

    function roundTotalAmount() {
        let subTotal = window.currentTotal || 0;
        let discountType = document.getElementById('discountType').value;
        let discountVal = parseFloat(document.getElementById('discountValue').value) || 0;
        let discountAmount = (discountType === 'fixed') ? discountVal : (subTotal * (discountVal / 100));
        let currentNet = subTotal - discountAmount;

        let rounded = Math.round(currentNet * 2) / 2;
        roundingDifference = rounded - currentNet;
        calculateRemaining();
        toastr.success("{{ __('amount_rounded') }} " + rounded.toFixed(2));
    }

    function addPaymentRow() {
        let { diff } = calculateRemaining(); 
        let val = diff > 0.001 ? diff.toFixed(2) : '';

        $('#paymentRowsContainer .btn-add-pay').removeClass('btn-add-pay').addClass('btn-remove-pay')
            .attr('onclick', 'removePaymentRow(this)').html('<i class="fas fa-minus"></i>');

        let div = document.createElement('div');
        div.className = 'pay-row';
        div.innerHTML = `
            <select class="form-select pay-select method-select" onchange="calculateRemaining()">
                <option value="cash">{{ __('payment_cash') }}</option>
                <option value="card">{{ __('payment_card') }}</option>
                <option value="bank">{{ __('payment_bank') }}</option>
                <option value="paypal">{{ __('payment_paypal') }}</option>
            </select>
            <input type="number" class="form-control pay-input amount-input" placeholder="0.00" value="${val}" oninput="calculateRemaining()">
            <button class="btn-add-pay" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
        `;
        document.getElementById('paymentRowsContainer').appendChild(div);
        calculateRemaining();
        div.querySelector('input').focus();
    }

    let paypalInitialized = false;
    function initializePaypal() {
        if (paypalInitialized) return;
        paypalInitialized = true;

        paypal.Buttons({
            createOrder: function(data, actions) {
                let { diff, totalBill } = calculateRemaining();
                // نأخذ مبلغ الباي بال فقط
                let paypalAmount = 0;
                document.querySelectorAll('.pay-row').forEach(row => {
                    if (row.querySelector('.method-select').value === 'paypal') {
                        paypalAmount += parseFloat(row.querySelector('.amount-input').value) || 0;
                    }
                });

                return fetch(fixUrl("{{ route('store.paypal.create') }}"), {
                    method: 'post',
                    headers: { 'content-type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ amount: paypalAmount, currency: 'USD' })
                }).then(res => res.json()).then(order => order.id);
            },
            onApprove: function(data, actions) {
                return fetch(fixUrl("{{ route('store.paypal.capture') }}"), {
                    method: 'post',
                    headers: { 'content-type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ orderID: data.orderID })
                }).then(res => res.json()).then(details => {
                    if (details.success) {
                        toastr.success("{{ __('paypal_success') }}");
                        submitInvoice({ paypal_order_id: data.orderID });
                    } else {
                        toastr.error("{{ __('paypal_verify_failed') }}");
                    }
                });
            },
            onError: function(err) {
                toastr.error("{{ __('paypal_error') }}");
                paypalInitialized = false;
            }
        }).render('#paypal-button-container');
    }

    window.removePaymentRow = (btn) => {
        btn.closest('.pay-row').remove();
        calculateRemaining();
    };

    window.submitInvoice = (extraData = {}) => {
        // 🛑 فحص الصندوق أولاً، إلا في حالة المسحوبات 🛑
        if (!selectedCustomer?.is_store_owner && !isShiftOpen) {
            Swal.fire({
                icon: 'info',
                title: "{{ __('الصندوق مغلق') }}",
                text: "{{ __('لا يمكن إتمام عملية البيع قبل فتح الصندوق (الوردية).') }}",
                confirmButtonText: "{{ __('فتح الصندوق الآن') }}",
                confirmButtonColor: '#198754'
            }).then((result) => {
                if (result.isConfirmed) {
                    showOpenShiftModal(); // فتح النافذة
                }
            });
            return; // إيقاف تنفيذ دالة البيع
        }

        if(cart.length === 0) return toastr.error("{{ __('السلة فارغة') }}");
        
        let total = parseFloat($('#footerTotal').text());
        let pay = []; 
        let totalPaid = 0;
        
        // ✅ فحص صارم لصاحب المتجر (بناء على العلامة أو الاسم)
        const isOwner = selectedCustomer?.is_store_owner || (selectedCustomer?.name && selectedCustomer.name.includes('صاحب المتجر'));

        const performAjaxSave = (finalData) => {
            let data = {
                items: cart, total: total, customer_id: selectedCustomer?.id, 
                payments: pay, discount_amount: $('#discountValue').val(), _token: '{{ csrf_token() }}',
                show_iban: $('#pos_show_iban').is(':checked') ? 1 : 0,
                show_stamp: $('#pos_show_stamp').is(':checked') ? 1 : 0,
                show_signature: $('#pos_show_signature').is(':checked') ? 1 : 0,
                ...extraData, ...finalData
            };

            Swal.fire({title: "{{ __('جاري الحفظ...') }}", didOpen: () => Swal.showLoading()});

            $.post(fixUrl("{{ route('store.pos.save') }}"), data)
             .done((res) => { 
                 if (res.whatsapp_data) {
                    Swal.fire({
                        title: "{{ __('تم الحفظ بنجاح') }}",
                        text: "{{ __('كيف ترغب في مشاركة الفاتورة مع العميل؟') }}",
                        icon: 'success',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fab fa-whatsapp"></i> ' + "{{ __('واتساب') }}",
                        denyButtonText: '<i class="fas fa-envelope"></i> ' + "{{ __('إيميل') }}",
                        cancelButtonText: "{{ __('إغلاق') }}",
                        confirmButtonColor: '#25d366',
                        denyButtonColor: '#007bff',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            triggerWhatsappPrompt(res.whatsapp_data.phone, res.whatsapp_data.message);
                        } else if (result.isDenied) {
                            triggerEmailPrompt(res.customer_email || '', res.whatsapp_data.message, "{{ __('إرسال فاتورة مبيعات - رقم #') }}" + (res.invoice_id || ''));
                        }
                        resetPosScreen();
                    });
                 } else {
                     Swal.fire({icon:'success', title: isOwner ? "{{ __('تم تسجيل المسحوبات') }}" : "{{ __('تمت العملية بنجاح') }}", timer:1000, showConfirmButton:false}); 
                     setTimeout(() => location.reload(), 1000);
                 }
             })
             .fail((xhr) => { 
                 let res = xhr.responseJSON || {};
                  if(res.error === 'customer_required') {
                     Swal.fire({icon: 'error', title: "{{ __('alert') }}", text: res.message});
                 } else if (res.error === 'credit_limit_exceeded') {
                     Swal.fire({
                         title: "{{ __('تجاوز حد الدين') }}",
                         html: `<div class="text-end"><p class="text-danger fw-bold">${res.message}</p><p>{{ __('الحد الحالي:') }} <b>${res.current_limit}</b></p><hr><p class="fw-bold">{{ __('هل تريد رفع الحد؟') }}</p></div>`,
                         icon: 'warning',
                         showCancelButton: true, confirmButtonText: "{{ __('نعم') }}", cancelButtonText: "{{ __('إلغاء') }}", confirmButtonColor: '#d33'
                     }).then((r) => { 
                         if (r.isConfirmed) window.submitInvoice({ update_limit_to: parseFloat(res.required_limit) + 10, bypass_confirm: true });
                     });
                 } else if(res.error === 'stock_error') {
                     Swal.fire({
                         title: "{{ __('نقص مخزون') }}", html: res.message, icon: 'warning',
                         showCancelButton: true, confirmButtonText: "{{ __('تعديل المخزون') }}", cancelButtonText: "{{ __('إلغاء') }}"
                     }).then((r) => {
                         if (r.isConfirmed && typeof promptStockAdjustment === 'function') promptStockAdjustment(res.product_id, res.product_name, res.current_stock);
                     });
                 } else {
                     Swal.fire({icon:'error', title:"{{ __('خطأ') }}", text: res.message || "{{ __('حدث خطأ غير معروف') }}"});
                 }
             });
        };

        if (!isOwner) {
           $('.pay-row').each(function() {
                let method = $(this).find('select.method-select').val();
                let amountVal = $(this).find('input.amount-input').val();
                let v = parseFloat(amountVal);

                if (v > 0 && method) {
                    pay.push({ method: method, amount: v });
                    totalPaid += v;
                }
            });
        }
        
        // --- تعديل هام جداً: تجاوز حساب الفروقات للمسحوبات ---
        if(isOwner) {
             performAjaxSave({});
             return;
        }
        
        let diff = total - totalPaid;
        let currentBalance = selectedCustomer ? parseFloat(selectedCustomer.balance) : 0;

        // تجاوز فحوصات الدفع للمسحوبات (تم نقله للأعلى لضمان عدم الوصول للكود السفلي)
        // if (selectedCustomer?.is_store_owner) { ... }

        // تجاوز فحوصات الدفع للمسحوبات (تم نقله للأعلى لضمان عدم الوصول للكود السفلي)
        // if (selectedCustomer?.is_store_owner) { ... }

        if (diff > 0.01 && !extraData.bypass_confirm) {
            if (!selectedCustomer) return Swal.fire({icon: 'error', title: "{{ __('customer_required') }}", text: "{{ __('debt_general_customer_error') }}"});
            let newBal = currentBalance - diff;
            let msg = currentBalance < 0 ? `{{ __('new_debt_total') }} <b class="text-danger">${Math.abs(newBal).toFixed(2)}</b>` : `{{ __('new_balance_total') }} <b>${newBal.toFixed(2)}</b>`;
            Swal.fire({
                title: "{{ __('confirm_debt_recording') }}",
                html: `<div class="text-end fs-6">{{ __('debt_value') }} <span class="text-danger fw-bold">${diff.toFixed(2)}</span><hr><small>${msg}</small></div>`,
                icon: 'question',
                showCancelButton: true, confirmButtonText: "{{ __('yes') }}", cancelButtonText: "{{ __('cancel') }}", confirmButtonColor: '#d33'
            }).then((result) => { if (result.isConfirmed) performAjaxSave({}); });
        } else if (diff < -0.01) {
            let surplus = Math.abs(diff);
            if (!selectedCustomer) {
                return Swal.fire({
                    title: "{{ __('extra_amount') }}", html: `{{ __('extra_amount_val') }} <b class="text-success">${surplus.toFixed(2)}</b>`,
                    confirmButtonText: "{{ __('cash_returned') }}",
                }).then((r) => { if(r.isConfirmed) performAjaxSave({ action: 'return_cash', change_amount: surplus }); });
            }
            Swal.fire({
                title: "{{ __('overpaid_amount') }}",
                html: `<div class="text-end"><h4 class="text-success text-center">${surplus.toFixed(2)}</h4><p class="fw-bold">{{ __('handling_change_method') }}</p></div>`,
                icon: 'info',
                showDenyButton: true, showCancelButton: true,
                confirmButtonText: "{{ __('return_cash') }}", denyButtonText: "{{ __('add_to_customer_balance') }}", cancelButtonText: "{{ __('cancel') }}",
                confirmButtonColor: '#6c757d', denyButtonColor: '#198754'
            }).then((result) => {
                if (result.isConfirmed) performAjaxSave({ action: 'return_cash', change_amount: surplus });
                else if (result.isDenied) performAjaxSave({ action: 'add_to_balance' });
            });
        } else {
            performAjaxSave({});
        }
    };

    function promptStockAdjustment(productName, productId, currentStock, successCallback) {
        Swal.fire({
            title: "{{ __('insufficient_quantity') }}",
            html: `{{ __('product_label') }} <b>${productName}</b><br>{{ __('current_stock_label') }} <b class="text-danger">${currentStock}</b>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{{ __('adjust_stock') }}",
            cancelButtonText: "{{ __('cancel') }}",
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "{{ __('adjust_stock') }}",
                    input: 'number',
                    inputLabel: "{{ __('new_quantity') }}",
                    inputValue: currentStock,
                    showCancelButton: true,
                    confirmButtonText: "{{ __('save') }}",
                    showLoaderOnConfirm: true,
                    preConfirm: (newQty) => {
                        return $.post(fixUrl("{{ route('store.pos.adjustStock') }}"), {
                            product_id: productId, new_qty: newQty, _token: '{{ csrf_token() }}'
                        }).then(response => ({ newQty: newQty, response: response }))
                          .fail(xhr => Swal.showValidationMessage("{{ __('error_label') }} " + (xhr.responseJSON?.message || '')));
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        Swal.fire("{{ __('done_exclamation') }}", "{{ __('stock_updated') }}", 'success');
                        if(successCallback) successCallback(parseFloat(res.value.newQty));
                    }
                });
            }
        });
    }

    function resetPosScreen() {
        cart = []; renderCart();
        $('#discountValue').val('');
        $('#customerSelect').val(null).trigger('change');
        selectedCustomer = null;
        $('#customerBalanceBox').fadeOut(200);
        let container = document.getElementById('paymentRowsContainer');
        container.innerHTML = ''; 
        let uniqueId = Date.now(); 
        let div = document.createElement('div');
        div.className = 'pay-row'; div.id = `payRow_${uniqueId}`;
        div.innerHTML = `
            <select class="form-select pay-select method-select">
                <option value="cash">{{ __('💵 نقدي') }}</option>
                <option value="card">{{ __('💳 شبكة / كرت') }}</option>
                <option value="bank">{{ __('🏦 تحويل بنكي') }}</option>
            </select>
            <input type="number" class="form-control pay-input amount-input" placeholder="0.00" oninput="calculateRemaining()">
            <button class="btn-add-pay" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
        `;
        container.appendChild(div);
        calculateRemaining();
        $('#barcodeInput').focus();
    }

    function addActive(items) {
        if (!items) return false;
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove("active");
            items[i].style.backgroundColor = "";
        }
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (items.length - 1);
        items[currentFocus].classList.add("active");
        items[currentFocus].scrollIntoView({ block: 'nearest' }); 
    }

    function openHistoryModal() {
        historyModal.show();
        if (!$('#filterCustomer').hasClass("select2-hidden-accessible")) {
            $('#filterCustomer').select2({
                dropdownParent: $('#historyModal'),
                theme: 'bootstrap-5', dir: "rtl", placeholder: "{{ __('all') }}", allowClear: true,
                ajax: {
                    url: fixUrl("{{ route('store.pos.search-customers') }}"), dataType: 'json', delay: 250,
                    data: function (params) { return { term: params.term }; }, // ✅ استعادة term
                    processResults: function (data) { return { results: data.results }; } // ✅ استعادة البنية الصحيحة
                }
            }).on('change', function() { getRecentSales(); });
        }
        getRecentSales(); 
    }

    // ...

    function getRecentSales(page = 1) {
        let params = {
            page: page,
            per_page: $('#filterLimit').val(),
            sort_by: $('#filterSortBy').val(),
            sort_order: $('#filterSortOrder').val(),
            from_date: $('#filterDateFrom').val(),
            to_date: $('#filterDateTo').val(),
            invoice_no: $('#filterInvoiceNo').val(),
            customer_id: $('#filterCustomer').val(),
            payment_status: $('#filterPaymentStatus').val()
        };

        $('#historyList').html('<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>');

        $.get(fixUrl("{{ route('store.pos.recent-sales') }}"), params, function(res) {
            let rows = ''; 
            let t=0, p=0, d=0;
            currentStoreInfo = res.store_info || {};

            if(currentStoreInfo.name) {
                $('#printStoreName').text(currentStoreInfo.name);
                if(currentStoreInfo.logo) $('#printStoreLogo').attr('src', currentStoreInfo.logo);
            }

            let list = (res.sales && res.sales.data) ? res.sales.data : [];
            let meta = res.sales;

            if(list.length > 0) {
                list.forEach(s => {
                    t += parseFloat(s.total); p += parseFloat(s.paid); d += parseFloat(s.due);
                    let badge = '';
                    if(s.status === 'overpaid') badge = `<span class="badge bg-info text-dark">${"{{ __('overpaid') }}"}</span>`;
                    else if(s.status === 'paid') badge = `<span class="badge bg-success">${"{{ __('paid') }}"}</span>`;
                    else if(s.status === 'partial') badge = `<span class="badge bg-warning text-dark">${"{{ __('partial') }}"}</span>`;
                    else badge = `<span class="badge bg-danger">${"{{ __('unpaid') }}"}</span>`;

                    // عمود المرتجعات
                    let returnsCell = '';
                    if(s.has_returns) {
                        returnsCell = `<button class="btn btn-sm btn-outline-warning" onclick="viewReturns(${s.id})" title="{{ __('view_returns') }}">
                            <i class="fas fa-eye"></i> ${formatMoney(s.total_returns)}
                        </button>`;
                    } else {
                        returnsCell = '<span class="text-muted small">-</span>';
                    }

                    rows += `<tr>
                        <td class="c-0 fw-bold text-primary">${s.invoice_number}</td>
                        <td class="c-1">${s.customer_name}</td>
                        <td class="c-2">${formatMoney(s.total)}</td>
                        <td class="c-3 text-success">${formatMoney(s.paid)}</td>
                        <td class="c-4 text-danger">${formatMoney(s.due)}</td>
                        <td class="c-5">${badge}</td>
                        <td class="c-6 small">${s.date}</td>
                        <td class="c-7 small text-muted">${s.user_name}</td>
                        <td class="c-9">${returnsCell}</td>
                        <td class="c-8 no-print">
                            <div class="d-flex justify-content-center gap-1 align-items-center">
                                <button class="btn btn-outline-info btn-sm-custom" onclick="viewInvoice(${s.id})"><i class="fas fa-eye"></i></button>
                                                                ${s.contact_phone ? `<button class="btn btn-outline-success btn-sm-custom" onclick="triggerWhatsappPrompt('${s.contact_phone}', '')"><i class="fab fa-whatsapp"></i></button>` : ''}
                                ${s.contact_email ? `<button class="btn btn-outline-primary btn-sm-custom" onclick="triggerEmailPrompt('${s.contact_email}', '', 'إرسال الفاتورة رقم #${s.invoice_number}')"><i class="fas fa-envelope"></i></button>` : ''}
                                <button class="btn btn-outline-danger btn-sm-custom" onclick="deleteInvoice(${s.id})"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>`;
                });
            } else { 
                rows = `<tr><td colspan="10" class="text-center text-muted py-3">${"{{ __('no_data_found') }}"}</td></tr>`; 
            }

            $('#historyList').html(rows);
            $('#sumTotal').text(t.toFixed(2)); $('#sumPaid').text(p.toFixed(2)); $('#sumDue').text(d.toFixed(2));
            $('#historyList').html(rows);
            $('#sumTotal').text(t.toFixed(2)); $('#sumPaid').text(p.toFixed(2)); $('#sumDue').text(d.toFixed(2));
            renderPagination(meta);
            applyColumnVisibility(); // ✅ تطبيق إخفاء الأعمدة على البيانات الجديدة
        }).fail(() => {
            $('#historyList').html(`<tr><td colspan="10" class="text-danger text-center">${"{{ __('connection_error_recent_sales') }}"}</td></tr>`);
        });
    }

    // ...

    window.processReturnItem = function(itemId, maxQty) {
        // حفظ قيمة tabindex الأصلية وإزالتها لمنع منع التركيز من بوتستراب
        let $modal = $('#returnModal');
        let originalTabIndex = $modal.attr('tabindex');
        $modal.removeAttr('tabindex');

        Swal.fire({
            title: "{{ __('return_item') }}",
            target: '#returnModal', // جعل التنبيه جزءاً من المودال لتجاوز قيود التركيز
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">{{ __('return_qty_max') }} ${maxQty})</label>
                    <input type="number" id="return_qty_input" class="form-control form-control-lg text-center" 
                           value="${maxQty}" min="0.1" max="${maxQty}" step="0.1" autocomplete="off">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold small">{{ __('reason_optional') }}</label>
                    <input type="text" id="return_reason_input" class="form-control" placeholder="{{ __('reason_placeholder') }}">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: "{{ __('confirm_return') }}",
            confirmButtonColor: '#dc3545',
            cancelButtonText: "{{ __('cancel') }}",
            allowOutsideClick: false,
            didOpen: () => {
                const input = document.getElementById('return_qty_input');
                // تركيز مباشر وقوي
                input.focus();
                input.select();
                
                // منع أحداث التشتيت
                $(input).on('keydown', function(e) {
                    e.stopPropagation();
                });
            },
            willClose: () => {
                // إعادة قيمة tabindex الأصلية للمودال عند الإغلاق
                if (originalTabIndex !== undefined) {
                    $modal.attr('tabindex', originalTabIndex);
                }
            },
            preConfirm: () => {
                const qty = parseFloat(document.getElementById('return_qty_input').value);
                const reason = document.getElementById('return_reason_input').value;
                if (!qty || qty <= 0) {
                    Swal.showValidationMessage("{{ __('enter_valid_qty') }}");
                    return false;
                }
                if (qty > maxQty) {
                    Swal.showValidationMessage("{{ __('qty_cannot_exceed') }} " + maxQty);
                    return false;
                }
                return { qty: qty, reason: reason };
            }
        }).then((r) => {
            if(r.isConfirmed && r.value) {
                $.post(fixUrl("{{ route('store.pos.return.process') }}"), {
                    item_id: itemId, 
                    return_qty: r.value.qty, 
                    reason: r.value.reason,
                    _token: '{{ csrf_token() }}'
                }).done(() => {
                    toastr.success("{{ __('return_success') }}");
                    searchForReturnInvoices(); 
                }).fail((xhr) => toastr.error(xhr.responseJSON.error || "{{ __('error_occurred') }}"));
            }
        });
    };

    // ...

    function openUpdateExpiryModal(product) {
        let current = product.expiry_date || '';
        Swal.fire({
            title: "{{ __('update_expiry') }}",
            html: `
                <div class="mb-3 text-start">
                    <label>{{ __('product_label') }} ${product.name}</label>
                    <input type="date" id="newExpiryDate" class="form-control" value="${current}">
                </div>
                <div class="mb-3 text-start">
                    <label>{{ __('update_reason') }}</label>
                    <textarea id="updateReason" class="form-control" placeholder="{{ __('update_reason_placeholder') }}"></textarea>
                </div>
            `,
            showCancelButton: true, confirmButtonText: "{{ __('update') }}", cancelButtonText: "{{ __('cancel') }}",
            preConfirm: () => {
                let d = document.getElementById('newExpiryDate').value;
                let r = document.getElementById('updateReason').value;
                if(!d) Swal.showValidationMessage("{{ __('date_required') }}");
                if(!r) Swal.showValidationMessage("{{ __('reason_required') }}");
                return { date: d, reason: r };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // إرسال الطلب للخلفية
                Swal.showLoading();
                $.post(fixUrl("{{ route('store.pos.updateExpiry') }}"), {
                    product_id: product.id,
                    new_date: result.value.date,
                    reason: result.value.reason,
                    _token: '{{ csrf_token() }}'
                }).done((res) => {
                    Swal.fire("{{ __('documented') }}", res.message, 'success');
                    // الآن نسمح بإضافته للسلة
                    product.alert_status = 'ok'; 
                    addToCart(product);
                }).fail((xhr) => {
                    Swal.fire("{{ __('error') }}", xhr.responseJSON.error || "{{ __('system_error') }}", 'error');
                });
            }
        });
    }

    function renderPagination(meta) {
        $('#paginationControls').empty();
        if (!meta || meta.total === 0) return;
        let html = '<nav><ul class="pagination pagination-sm m-0">';
        html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}"><button class="page-link" onclick="getRecentSales(${meta.current_page - 1})">${"{{ __('previous') }}"}</button></li>`;
        let start = Math.max(1, meta.current_page - 2);
        let end = Math.min(meta.last_page, meta.current_page + 2);
        for (let i = start; i <= end; i++) {
            html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}"><button class="page-link" onclick="getRecentSales(${i})">${i}</button></li>`;
        }
        html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}"><button class="page-link" onclick="getRecentSales(${meta.current_page + 1})">${"{{ __('next') }}"}</button></li>`;
        html += '</ul></nav>';
        html += `<div class="ms-3 text-muted small align-self-center">${"{{ __('page') }}"} ${meta.current_page} ${"{{ __('from_of') }}"} ${meta.last_page} (${"{{ __('total') }}"} ${meta.total})</div>`;
        $('#paginationControls').html(html);
    }

    function printSalesReport() {
        if(currentStoreInfo.name) {
            $('#printStoreName').text(currentStoreInfo.name);
            $('#printStoreAddress').text(currentStoreInfo.address || '');
            $('#printStorePhone').text(currentStoreInfo.phone || '');
            if(currentStoreInfo.logo) $('#printStoreLogo').attr('src', currentStoreInfo.logo).show();
        }
        $('.no-print').hide();
        window.print();
        setTimeout(() => {
            $('.no-print').show();
            $('.col-toggle:not(:checked)').each(function() {
                 $(`#historyTable th.c-${$(this).data('col')}, #historyTable td.c-${$(this).data('col')}`).hide();
            });
        }, 500);
    }

    // ========================================================================
    // 🔥 تصحيح روابط الحفظ والحذف (Sales & Deletion) 🔥
    // ========================================================================



    window.deleteInvoice = function(id) {
        Swal.fire({
            title: "{{ __('confirm_delete') }}", text: "{{ __('delete_invoice_warning') }}", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: "{{ __('yes_delete') }}", cancelButtonText: "{{ __('cancel') }}"
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: fixUrl("{{ route('store.pos.delete-sale', ['id' => ':id']) }}").replace(':id', id),
                    type: 'DELETE',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function() { toastr.success("{{ __('deleted_successfully') }}"); getRecentSales(); },
                    error: function(xhr) { 
                        let msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : "{{ __('delete_failed') }}";
                        toastr.error(msg); 
                    }
                });
            }
        });
    };

    let currentViewedInvoiceId = null;

    window.viewInvoice = function(id) {
        currentViewedInvoiceId = id;
        $('#invItemsBody').html(`<tr><td colspan="6" class="text-center py-3">${"{{ __('loading') }}"}</td></tr>`);
        $('#showIban').prop('checked', false);
        $('#showStamp').prop('checked', false);
        $('#showSignature').prop('checked', false);
        $('#ibanBox').hide();
        $('#stampBox').hide();
        $('#signatureBox').hide();
        $('#invoiceModal').modal('show');

        $.ajax({
            url: fixUrl("{{ route('store.pos.sale-details', ['id' => ':id']) }}").replace(':id', id),
            method: 'GET',
            success: function(res) {
                let s = res.sale;
                let st = res.store;
                let items = res.items;
                
                $('#invStoreName').text(st.name); 
                $('#invStoreAddress').text(st.address); 
                $('#invTaxNumber').text(st.tax_number || '-');
                $('#invNumber').text('INV-' + s.id); 
                $('#invDate').text(new Date(s.created_at).toLocaleDateString('en-GB'));
                $('#invCustomerName').text(s.contact ? s.contact.contact_name : "{{ __('cash_customer') }}");
                
                if (st.logo_url) $('#invLogo').attr('src', st.logo_url).show(); else $('#invLogo').hide();
                if (st.stamp_url) { $('#stampOptionDiv').show(); $('#invStamp').attr('src', st.stamp_url); } else { $('#stampOptionDiv').hide(); }
                if (st.signature_url) { $('#signatureOptionDiv').show(); $('#invSignature').attr('src', st.signature_url); } else { $('#signatureOptionDiv').hide(); }

                // خيار الآيبان المشروط (فقط إذا كان هناك متبقي)
                if (s.due > 0.01 && st.iban) {
                    $('#ibanOptionDiv').show();
                    $('#invIbanBank').text(st.iban_bank_name || '-');
                    $('#invIbanHolder').text(st.bank_account_holder || '-');
                    $('#invIbanNumber').text(st.iban || '-');
                    $('#invStoreWhatsapp').text(st.phone_number || '-');
                } else {
                    $('#ibanOptionDiv').hide();
                }

                let h = ''; 
                items.forEach((i, x) => {
                    h += `<tr>
                        <td>${x + 1}</td>
                        <td class="text-start">${i.name}</td>
                        <td><span class="badge bg-light text-dark border">${i.barcode || '---'}</span></td>
                        <td>${i.qty} ${i.unit}</td>
                        <td>${parseFloat(i.price).toFixed(2)}</td>
                        <td class="fw-bold">${parseFloat(i.total).toFixed(2)}</td>
                    </tr>`;
                });
                
                $('#invItemsBody').html(h); 
                $('#invTotal').text(parseFloat(s.total).toFixed(2));
            },
            error: function(xhr) { 
                $('#invoiceModal').modal('hide'); 
                let msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : "{{ __('invoice_load_failed') }}";
                toastr.error(msg); 
                console.error("Invoice Load Error:", xhr);
            }
        });
    };

    window.shareInvoiceEmail = function() {
        if(!currentViewedInvoiceId) return;
        
        Swal.fire({
            title: 'جاري التجهيز...',
            text: 'يتم الآن إنشاء ملف PDF للفاتورة...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        let params = $.param({
            show_iban: $('#showIban').is(':checked') ? 1 : 0,
            show_stamp: $('#showStamp').is(':checked') ? 1 : 0,
            show_signature: $('#showSignature').is(':checked') ? 1 : 0
        });

        $.get(fixUrl("{{ route('store.pos.invoice.pdf', ['id' => ':id']) }}").replace(':id', currentViewedInvoiceId) + '?' + params)
         .done(function(res) {
             Swal.close();
             if(res.success) {
                let msg = "{{ __('invoice_no') }} " + currentViewedInvoiceId + "\n" + "{{ __('store_label') }} " + "{{ auth()->user()->store->name }}\n" + "{{ __('thanks_for_business') }}";
                if(typeof triggerEmailPrompt === 'function') {
                    triggerEmailPrompt((res.customer_email || ''), msg, "{{ __('email_pdf_subject') }}", res.url, res.filename);
                } else {
                    alert("{{ __('email_func_unavailable') }}");
                }
             }
         })
         .fail(function() { Swal.fire("{{ __('error') }}", "{{ __('invoice_prep_failed') }}", 'error'); });
    };

    window.shareInvoiceWhatsapp = function() {
        if(!currentViewedInvoiceId) return;
        
        Swal.fire({
            title: 'جاري التجهيز...',
            text: 'يتم الآن إنشاء ملف PDF للفاتورة...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        let params = $.param({
            show_iban: $('#showIban').is(':checked') ? 1 : 0,
            show_stamp: $('#showStamp').is(':checked') ? 1 : 0,
            show_signature: $('#showSignature').is(':checked') ? 1 : 0
        });

        $.get(fixUrl("{{ route('store.pos.invoice.pdf', ['id' => ':id']) }}").replace(':id', currentViewedInvoiceId) + '?' + params)
         .done(function(res) {
             Swal.close();
             if(res.success) {
                let msg = "{{ __('invoice_no') }} " + currentViewedInvoiceId + "\n" + "{{ __('store_label') }} " + "{{ auth()->user()->store->name }}\n" + "{{ __('thanks_for_business') }}";
                if(typeof triggerWhatsappPrompt === 'function') {
                    // نمرر رقم العميل إن وجد
                    triggerWhatsappPrompt((res.customer_phone || ''), msg, "{{ __('email_pdf_subject') }}", res.url, res.filename);
                } else {
                    // Fallback link if global function missing
                    window.open(res.url, '_blank');
                }
             }
         })
         .fail(function() { Swal.fire("{{ __('error') }}", "{{ __('invoice_prep_failed') }}", 'error'); });
    };

    window.toggleOfficialMarks = function() {
        $('#ibanBox').toggle($('#showIban').is(':checked'));
        $('#stampBox').toggle($('#showStamp').is(':checked'));
        $('#signatureBox').toggle($('#showSignature').is(':checked'));
    };

    window.printOfficialInvoice = function() {
        let c = document.getElementById('printableInvoice').innerHTML;
        let o = document.body.innerHTML;
        document.body.innerHTML = `<div style="width: 210mm; margin: 0 auto;">${c}</div>`;
        window.print(); document.body.innerHTML = o; window.location.reload(); 
    };


    $(document).ready(function() {
        $('#btn_open_cols').click(function(e) { e.stopPropagation(); $('#menu_cols').toggle(); });
        $(document).click(function(e) { if (!$(e.target).closest('#menu_cols, #btn_open_cols').length) { $('#menu_cols').hide(); } });
        $('#menu_cols').click(function(e){ e.stopPropagation(); });

        // ✅ إضافة مستمع لتغيير الفلاتر وتحديث الجدول تلقائياً
        $('.auto-filter').on('change', function() {
            getRecentSales(1); 
        });

        // ✅ منطق التحقق من التاريخ (From/To)
        $('#filterDateFrom').on('change', function() {
            let fromDate = $(this).val();
            $('#filterDateTo').attr('min', fromDate);
            // إذا كان "إلى" أصغر من "من"، قم بتصحيحه
            if(fromDate && $('#filterDateTo').val() && $('#filterDateTo').val() < fromDate) {
                $('#filterDateTo').val(fromDate);
            }
        });

        $('#filterDateTo').on('change', function() {
            let toDate = $(this).val();
            $('#filterDateFrom').attr('max', toDate);
            // إذا كان "من" أكبر من "إلى"، قم بتصحيحه
            if(toDate && $('#filterDateFrom').val() && $('#filterDateFrom').val() > toDate) {
                $('#filterDateFrom').val(toDate);
            }
        });

        // ✅ تفعيل فلتر الأعمدة
        $('.col-toggle').on('change', function() {
            applyColumnVisibility();
        });

        // ✅ فتح فاتورة معينة إذا كان المعرف موجوداً في الرابط
        const urlParams = new URLSearchParams(window.location.search);
        const invId = urlParams.get('invoice_id');
        if (invId) {
            setTimeout(() => viewInvoice(invId), 500);
        }
    });

    function applyColumnVisibility() {
        $('.col-toggle').each(function() {
            let col = $(this).data('col');
            let isVisible = $(this).is(':checked');
            let elements = $('.c-' + col); // يشمل الرؤوس والخلايا
            if(isVisible) elements.show(); else elements.hide();
        });
    }

    // ==========================================
    // دوال الصندوق (Shift)
    // ==========================================
    // ========================================================================
    // 🔥 تصحيح روابط الصندوق (Shift) لتعمل مع المجلدات الفرعية 🔥
    // ========================================================================

    window.checkShiftStatus = function() {
        $.get(fixUrl("{{ route('store.pos.shift.status') }}"), function(res) {
            isShiftOpen = res.has_open_shift; // تحديث الحالة
            
            if (isShiftOpen) {
                $('#btnCloseShift').removeClass('d-none');
                $('#btnOpenShift').addClass('d-none');
            } else {
                $('#btnOpenShift').removeClass('d-none');
                $('#btnCloseShift').addClass('d-none');
                // ❌ تم حذف سطر showOpenShiftModal() من هنا لمنع الفتح التلقائي
            }
        });
    };

    window.showOpenShiftModal = function() {
        const modalEl = document.getElementById('openShiftModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };

    window.submitOpenShift = function() {
        let amount = $('#startCashInput').val();
        if(amount === '') return toastr.error("{{ __('enter_amount') }}");
        
        $.post(fixUrl("{{ route('store.pos.shift.open') }}"), { start_cash: amount, _token: '{{ csrf_token() }}' })
         .done(() => {
             $('#openShiftModal').modal('hide');
             $('.modal-backdrop').remove();       
             $('body').removeClass('modal-open'); 
             $('body').css('padding-right', ''); 
             toastr.success("{{ __('box_opened') }}");
             isShiftOpen = true;
             $('#btnOpenShift').addClass('d-none');
             $('#btnCloseShift').removeClass('d-none');
             $('#barcodeInput').focus();
         })
         .fail(() => toastr.error("{{ __('error_opening_box') }}"));
    };

    window.openCloseShiftModal = function() {
        Swal.showLoading(); 
        $.get(fixUrl("{{ route('store.pos.shift.summary') }}"), function(res) {
            Swal.close();
            $('#shiftOpenDate').text(res.opened_at);
            $('#shiftStartCash').text(res.start_cash);
            $('#shiftCashSales').text(res.cash_sales);
            $('#shiftCardSales').text(res.card_sales);
            $('#shiftBankSales').text(res.bank_sales);
            $('#shiftCreditSales').text(res.credit_sales);
            $('#shiftExpected').text(res.expected_cash);
            $('#endCashInput').val('');
            $('#closeShiftModal').modal('show');
        }).fail(() => {
            Swal.close();
            toastr.error("{{ __('no_open_box') }}");
        });
    };

    window.submitCloseShift = function() {
        let actualCash = $('#endCashInput').val();
        if(actualCash === '') return toastr.error("{{ __('enter_actual_cash') }}");

        Swal.fire({
            title: "{{ __('are_you_sure') }}", text: "{{ __('shift_will_close') }}", icon: 'warning',
            showCancelButton: true, confirmButtonText: "{{ __('yes_close') }}", cancelButtonText: "{{ __('undo') }}", confirmButtonColor: '#ffc107', cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.post(fixUrl("{{ route('store.pos.shift.close') }}"), { end_cash: actualCash, _token: '{{ csrf_token() }}' })
                .done((res) => {
                    Swal.close();
                    $('#closeShiftModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                    let diff = parseFloat(res.difference);
                    let msg = diff === 0 ? "{{ __('perfect_match') }}" : (diff < 0 ? "{{ __('deficit_label') }} " + diff + " ❌" : "{{ __('surplus_label') }} +" + diff + " ⚠️");
                    Swal.fire({ title: "{{ __('closed_successfully') }}", text: msg, icon: diff === 0 ? 'success' : 'warning' }).then(() => location.reload());
                })
                .fail((xhr) => {
                    Swal.close();
                    let msg = "{{ __('unknown_error') }}";
                    if(xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    else if (xhr.status === 500) msg = "{{ __('server_error_500') }}";
                    toastr.error(msg);
                });
            }
        });
    };

    // ==========================================
    // دوال الإرجاع
    // ==========================================
   // 1. فتح نافذة الإرجاع وتشغيل البحث الذكي
    window.openReturnModal = function() {
        $('#returnModal').modal('show');
        
        // تفعيل بحث العملاء
        $('#returnCustomerSelect').select2({
            dropdownParent: $('#returnModal'),
            theme: 'bootstrap-5', placeholder: "{{ __('select_customer_optional') }}", allowClear: true,
            ajax: { url: fixUrl("{{ route('store.pos.search-customers') }}"), dataType: 'json', processResults: data => ({results: data.results}) }
        }).on('change', function() { searchForReturnInvoices(); }); // عند تغيير العميل نبحث فوراً

        // 🔥 تفعيل بحث المنتجات الذكي (مثل شاشة البيع) 🔥
        $('#returnProductSelect').select2({
            dropdownParent: $('#returnModal'),
            theme: 'bootstrap-5', 
            placeholder: "{{ __('search_product_placeholder') }}", 
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: fixUrl("{{ route('store.pos.search-products') }}"), // ✅ تم التصحيح
                dataType: 'json',
                delay: 250,
                data: function (params) { return { term: params.term }; }, // ✅ استعادة term
                processResults: function (data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.name + ' (' + item.default_barcode + ')'
                            };
                        })
                    };
                }
            }
        }).on('select2:select', function(e) {
            // عند اختيار منتج، ابحث عن فواتيره فوراً
            searchForReturnInvoices(e.params.data.id); 
        }).on('select2:clear', function() {
            $('#returnResultsBody').html('');
        });
    };

    // دالة البحث عن الفواتير (تلقائية الآن)
   window.searchForReturnInvoices = function() {
        // نأخذ النص المكتوب في Select2 أو القيمة المختارة
        let termData = $('#returnProductSelect').select2('data');
        let term = (termData && termData.length > 0) ? termData[0].text : ''; 
        
        // تنظيف النص (إزالة السعر والباركود الزائد الذي أضفناه للعرض)
        if(term) term = term.split('(')[0].trim(); 

        let cust = $('#returnCustomerSelect').val();
        
        if(!term && !cust) return;

        $('#returnResultsBody').html(`<tr><td colspan="5">${"{{ __('searching') }}"}</td></tr>`);

        $.get(fixUrl("{{ route('store.pos.return.search') }}"), {term: term, customer_id: cust}, function(res) {
            let rows = '';
            if(res.invoices.length === 0) rows = `<tr><td colspan="5" class="text-muted">${"{{ __('no_invoices_found') }}"}</td></tr>`;
            else {
                res.invoices.forEach(i => {
                    rows += `<tr>
                        <td><span class="fw-bold text-primary">${i.invoice_no}</span><br><small>${i.date}</small></td>
                        <td>${i.product_name} <br> <span class="badge bg-light text-dark border">${i.unit_name}</span></td>
                        <td class="fw-bold">${i.qty}</td>
                        <td>${i.price}</td>
                        <td><button class="btn btn-sm btn-outline-danger fw-bold" onclick="processReturnItem(${i.item_id}, ${i.qty})">${"{{ __('return_action') }}"}</button></td>
                    </tr>`;
                });
            }
            $('#returnResultsBody').html(rows);
        });
    };

    // Duplicate processReturnItem removed

                   // 🔥 دالة حارس الصلاحية (تمنع المنتهي وتنبه القريب) 🔥
    function checkExpiryAndAdd(product) {
        // 1. إذا كان المنتج منتهي (أحمر) -> منع بات
        if (product.alert_status === 'expired') {
            Swal.fire({
                title: "{{ __('product_expired_alert') }}",
                html: `<h4 class="text-danger my-2">${product.name}</h4>
                       <div class="alert alert-danger fw-bold">${product.alert_msg}</div>
                       <p>{{ __('safety_warning') }}</p>`,
                icon: 'error',
                showDenyButton: true,
                confirmButtonText: "{{ __('update_date_risk') }}",
                confirmButtonColor: '#ffc107', // أصفر
                denyButtonText: "{{ __('cancel_sale') }}",
                denyButtonColor: '#6c757d', 
            }).then((result) => {
                if (result.isConfirmed) {
                    // فتح نافذة تعديل التاريخ
                    openUpdateExpiryModal(product);
                }
            });
            return; // 🛑 إيقاف الدالة هنا (لن يتم الإضافة للسلة)
        }

        // 2. إذا كان المنتج قارب على الانتهاء (أصفر) -> تنبيه فقط
        if (product.alert_status === 'near') {
            toastr.warning(product.alert_msg, "{{ __('expiry_alert') }}", {timeOut: 5000, positionClass: "toast-top-center"});
            // تشغيل صوت تنبيه خفيف
            let audio = new Audio('https://media.geeksforgeeks.org/wp-content/uploads/20190531135120/beep.mp3');
            audio.play().catch(e=>{});
        }

        // 3. إضافة للسلة (للسليم أو القريب فقط)
        addToCart(product);
    }

    // نافذة تعديل التاريخ (تظهر فقط عند الضغط على الزر الأصفر)
    function openUpdateExpiryModal(product) {
        Swal.fire({
            title: "{{ __('update_expiry_doc') }}",
            html: `
                <div class="text-start bg-light p-3 rounded border">
                    <label class="fw-bold">{{ __('product_label') }} ${product.name}</label>
                    <hr>
                    <label class="mt-2 text-primary fw-bold">{{ __('new_expiry_date') }}</label>
                    <input type="date" id="newExpiryDate" class="form-control mb-3 border-primary">
                    
                    <label class="text-danger fw-bold">{{ __('modification_reason_mandatory') }}</label>
                    <textarea id="updateReason" class="form-control border-danger" rows="2" placeholder="{{ __('reason_placeholder_admin') }}"></textarea>
                </div>
            `,
            confirmButtonText: "{{ __('save_edit_sell') }}",
            showCancelButton: true,
            cancelButtonText: "{{ __('undo') }}",
            focusConfirm: false,
            preConfirm: () => {
                const date = document.getElementById('newExpiryDate').value;
                const reason = document.getElementById('updateReason').value;
                if (!date || !reason || reason.length < 5) {
                    Swal.showValidationMessage("{{ __('enter_date_reason_valid') }}");
                    return false;
                }
                return { date: date, reason: reason };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // إرسال الطلب للخلفية
                Swal.showLoading();
                $.post("{{ route('store.pos.updateExpiry') }}", {
                    product_id: product.id,
                    new_date: result.value.date,
                    reason: result.value.reason,
                    _token: '{{ csrf_token() }}'
                }).done((res) => {
                    Swal.fire("{{ __('documented') }}", res.message, 'success');
                    // الآن نسمح بإضافته للسلة
                    product.alert_status = 'ok'; 
                    addToCart(product);
                }).fail((xhr) => {
                    Swal.fire("{{ __('error') }}", xhr.responseJSON.error || "{{ __('system_error') }}", 'error');
                });
            }
        });
    }

    // ========== إرسال تقرير المبيعات عبر الواتساب ==========
    window.sendSalesReportEmail = function() {
        const urlParams = new URLSearchParams();
        urlParams.set('limit', $('#filterLimit').val() || 'all');
        urlParams.set('sort_by', $('#filterSortBy').val() || 'created_at');
        urlParams.set('sort_order', $('#filterSortOrder').val() || 'desc');
        urlParams.set('payment_status', $('#filterPaymentStatus').val() || '');
        urlParams.set('customer_id', $('#filterCustomer').val() || '');
        urlParams.set('date_from', $('#filterDateFrom').val() || '');
        urlParams.set('date_to', $('#filterDateTo').val() || '');
        urlParams.set('output', 'url');
        
        const fetchUrl = fixUrl("{{ route('store.pos.sales-report-pdf') }}") + "?" + urlParams.toString();
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: "{{ __('preparing_report') }}",
                html: "{{ __('please_wait_report') }}",
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                if (typeof Swal !== 'undefined') Swal.close();
                if (data.url) {
                    const paymentStatusText = $('#filterPaymentStatus').val() ? $('#filterPaymentStatus option:selected').text().trim() : "{{ __('all') }}";
                    const message = `{{ __('report_greeting') }}\n\n{{ __('report_intro') }} ({{ auth()->user()->store->name }})\n\n` +
                                    `{{ __('report_details') }}\n` +
                                    `- {{ __('report_date') }} {{ now()->format('Y-m-d') }}\n` +
                                    `- {{ __('selected_payment_status') }} ${paymentStatusText}\n\n` +
                                    `{{ __('report_footer_pdf') }}\n` +
                                    `{{ __('report_thanks') }}\n{{ __('sales_management_team') }} Tech-Sys`;
                    
                    const filename = data.filename || "sales_report.pdf";
                    if (typeof triggerEmailPrompt === 'function') {
                        triggerEmailPrompt('', message, "{{ __('sales_report_subject') }} {{ auth()->user()->store->name }}", data.url, filename);
                    } else {
                        alert("{{ __('email_func_unavailable') }}");
                    }
                } else {
                    alert("{{ __('report_prep_failed') }}");
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') Swal.close();
                alert("{{ __('server_connection_error') }}");
            });
    };

    window.sendSalesReportWhatsapp = function() {
        // جمع الفلاتر الحالية من واجهة المستخدم
        const urlParams = new URLSearchParams();
        urlParams.set('limit', $('#filterLimit').val() || 'all');
        urlParams.set('sort_by', $('#filterSortBy').val() || 'created_at');
        urlParams.set('sort_order', $('#filterSortOrder').val() || 'desc');
        urlParams.set('payment_status', $('#filterPaymentStatus').val() || '');
        urlParams.set('customer_id', $('#filterCustomer').val() || '');
        urlParams.set('date_from', $('#filterDateFrom').val() || '');
        urlParams.set('date_to', $('#filterDateTo').val() || '');
        urlParams.set('output', 'url');
        
        const fetchUrl = fixUrl("{{ route('store.pos.sales-report-pdf') }}") + "?" + urlParams.toString();
        
        if (typeof Swal !== 'undefined') {
            let progressTimer;
            Swal.fire({
                title: "{{ __('preparing_report_file') }}",
                html: `
                    <div class="mb-3">${"{{ __('please_wait_pdf') }}"}</div>
                    <div class="progress" style="height: 20px;">
                        <div id="swal-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Swal.showLoading(); // لا نريد الدائرة، نريد الشريط فقط
                    const progressBar = document.getElementById('swal-progress-bar');
                    let progress = 0;
                    const duration = 180000; // 3 دقائق
                    const interval = 1000;
                    const increment = 95 / (duration / interval);

                    progressTimer = setInterval(() => {
                        progress += increment;
                        if(progress > 95) progress = 95;
                        const pct = Math.round(progress) + '%';
                        if(progressBar) {
                            progressBar.style.width = pct;
                            progressBar.innerText = pct;
                        }
                    }, interval);
                },
                willClose: () => {
                    clearInterval(progressTimer);
                }
            });
        }

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                if (typeof Swal !== 'undefined') Swal.close();
                
                if (data.url) {
                    const paymentStatusText = $('#filterPaymentStatus').val() ? $('#filterPaymentStatus option:selected').text().trim() : "{{ __('all') }}";
                    const message = `{{ __('report_greeting') }}\n\n{{ __('report_intro') }} ({{ auth()->user()->store->name }})\n\n` +
                                    `{{ __('report_details') }}\n` +
                                    `- {{ __('report_date') }} {{ now()->format('Y-m-d') }}\n` +
                                    `- {{ __('selected_payment_status') }} ${paymentStatusText}\n\n` +
                                    `{{ __('report_footer_pdf') }}\n` +
                                    `{{ __('report_thanks') }}\n{{ __('sales_management_team') }} Tech-Sys`;
                    
                    const filename = data.filename || "sales_report.pdf";
                    
                    // إرسال المرفق عبر المودال العالمي
                    if (typeof triggerWhatsappPrompt === 'function') {
                        triggerWhatsappPrompt('', message, "{{ __('send_sales_report_pdf') }}", data.url, filename);
                    } else {
                        alert("{{ __('whatsapp_func_unavailable') }}");
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire("{{ __('error') }}", "{{ __('report_prep_failed') }}", 'error');
                    } else {
                        alert("{{ __('report_prep_failed') }}");
                    }
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') Swal.close();
                console.error(err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire("{{ __('error') }}", "{{ __('server_connection_error') }}", 'error');
                } else {
                    alert("{{ __('server_connection_error') }}");
                }
            });
    };

    // ========== عرض تفاصيل المرتجعات ==========
    window.viewReturns = function(saleId) {
        // تحميل البيانات
        Swal.fire({
            title: "{{ __('جاري التحميل...') }}",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.get(fixUrl("{{ url('store-owner/pos/sale-returns') }}/" + saleId))
        .done(function(data) {
            Swal.close();
            
            // ملء البيانات الرئيسية
            $('#returnsSaleId').text('INV-' + data.sale.id);
            $('#returnsCustomerName').text(data.sale.customer);
            $('#returnsOriginalTotal').text(parseFloat(data.sale.original_total).toFixed(2));
            $('#returnsDate').text(data.sale.created_at);
            $('#returnsTotalReturns').text(parseFloat(data.sale.total_returns).toFixed(2));
            
            // الفاتورة بعد الإرجاع - الإجماليات
            let finalTotal = parseFloat(data.sale.total);
            let finalDue = parseFloat(data.sale.due);
            let finalPaid = finalTotal - finalDue;
            let originalTotal = parseFloat(data.sale.original_total);
            let returnsAmount = parseFloat(data.sale.total_returns);
            
            $('#returnsFinalTotal').text(finalTotal.toFixed(2));
            $('#returnsFinalDue').text(finalDue.toFixed(2));
            $('#returnsFinalPaid').text(finalPaid.toFixed(2));
            
            // ملء بيانات تبويب "بعد الإرجاع"
            $('#finalInvNumber').text('INV-' + data.sale.id);
            $('#finalCustomer').text(data.sale.customer);
            $('#finalDate').text(data.sale.created_at);
            $('#finalOriginalTotal').text(originalTotal.toFixed(2));
            $('#finalReturnsAmount').text('-' + returnsAmount.toFixed(2));
            
            // أصناف الفاتورة الأصلية (التبويب الأول - قبل الإرجاع)
            let itemsHtml = '';
            if(data.original_items && data.original_items.length > 0) {
                data.original_items.forEach(item => {
                    itemsHtml += `<tr>
                        <td>${item.product_name}</td>
                        <td>${item.unit_name}</td>
                        <td>${parseFloat(item.quantity).toFixed(2)}</td>
                        <td>${parseFloat(item.price).toFixed(2)}</td>
                        <td class="fw-bold">${parseFloat(item.total).toFixed(2)}</td>
                    </tr>`;
                });
            } else {
                itemsHtml = `<tr><td colspan="5" class="text-muted">${"{{ __('لا توجد أصناف') }}"}</td></tr>`;
            }
            $('#returnsItemsBody').html(itemsHtml);
            
            // أصناف تبويب "بعد الإرجاع" - الأصناف الحالية (التبويب الثالث)
            let finalItemsHtml = '';
            let finalItemsTotal = 0;
            if(data.current_items && data.current_items.length > 0) {
                data.current_items.forEach((item, idx) => {
                    let total = parseFloat(item.total);
                    finalItemsTotal += total;
                    finalItemsHtml += `<tr>
                        <td>${idx + 1}</td>
                        <td>${item.product_name}</td>
                        <td>${item.unit_name}</td>
                        <td>${parseFloat(item.quantity).toFixed(2)}</td>
                        <td>${parseFloat(item.price).toFixed(2)}</td>
                        <td class="fw-bold text-success">${total.toFixed(2)}</td>
                    </tr>`;
                });
            } else {
                finalItemsHtml = `<tr><td colspan="6" class="text-muted text-center py-3"><i class="fas fa-box-open me-2"></i>${"{{ __('تم إرجاع جميع الأصناف - لا توجد أصناف متبقية') }}"}</td></tr>`;
            }
            $('#finalItemsBody').html(finalItemsHtml);
            $('#finalItemsTotal').text(finalItemsTotal.toFixed(2));
            
            // المرتجعات
            let returnsHtml = '';
            if(data.returns && data.returns.length > 0) {
                data.returns.forEach(ret => {
                    returnsHtml += `<tr>
                        <td>${ret.product_name}</td>
                        <td>${ret.unit_name}</td>
                        <td class="text-danger fw-bold">${parseFloat(ret.quantity).toFixed(2)}</td>
                        <td>${parseFloat(ret.price).toFixed(2)}</td>
                        <td class="text-danger fw-bold">${parseFloat(ret.total).toFixed(2)}</td>
                        <td>${ret.reason || '<span class="text-muted">-</span>'}</td>
                        <td>${ret.user}</td>
                        <td class="small">${ret.created_at}</td>
                    </tr>`;
                });
            } else {
                returnsHtml = `<tr><td colspan="8" class="text-muted">${"{{ __('لا توجد مرتجعات مسجلة') }}"}</td></tr>`;
            }
            $('#returnsReturnedItemsBody').html(returnsHtml);
            
            // فتح المودال
            new bootstrap.Modal(document.getElementById('returnsDetailModal')).show();
        })
        .fail(function(xhr) {
            Swal.fire("{{ __('error') }}", "{{ __('returns_load_failed') }}", 'error');
            console.error(xhr);
        });
    };

    // طباعة تقرير المرتجعات
    window.printReturnsReport = function() {
        let content = document.getElementById('returnsDetailModal').querySelector('.modal-body').innerHTML;
        let win = window.open('', '_blank');
        win.document.write(`
            <html dir="rtl"><head><title>${"{{ __('returns_report') }}"}</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
            <style>body{padding:20px;font-family:Cairo,sans-serif;} @media print{.nav-tabs{display:none;} .tab-pane{display:block!important;opacity:1!important;margin-bottom:20px;}}</style>
            </head><body>${content}</body></html>
        `);
        win.document.close();
        setTimeout(() => { win.print(); win.close(); }, 500);
    };

    // مشاركة تقرير المرتجعات عبر واتساب
    window.shareReturnsEmail = function() {
        const saleId = $('#returnsSaleId').text().replace('INV-', '');
        
        Swal.fire({
            title: 'جاري تجهيز ملف PDF...',
            text: 'يرجى الانتظار قليلاً...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.get(fixUrl("{{ url('store-owner/pos/return-pdf') }}/" + saleId))
        .done(function(data) {
            Swal.close();
            if(data.success && data.url) {
                const customer = $('#returnsCustomerName').text();
                const date = $('#returnsDate').text();
                let message = `*${"{{ __('returns_report_title') }} " + saleId}*\n${"{{ __('customer_label') }}"} ${customer}\n${"{{ __('date_label') }}"} ${date}\n${"{{ __('check_attachment') }}"}\n`;

                if (typeof triggerEmailPrompt === 'function') {
                    triggerEmailPrompt('', message, "{{ __('returns_report_pdf') }}", data.url, data.filename);
                }
            } else {
                Swal.fire("{{ __('error') }}", "{{ __('pdf_creation_failed') }}", 'error');
            }
        })
        .fail(function() {
            Swal.close();
            Swal.fire("{{ __('error') }}", "{{ __('server_connection_error') }}", 'error');
        });
    };

    window.shareReturnsWhatsapp = function() {
        const saleId = $('#returnsSaleId').text().replace('INV-', '');
        
        Swal.fire({
            title: 'جاري تجهيز ملف PDF...',
            text: 'يرجى الانتظار قليلاً...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // طلب إنشاء PDF من الخادم
        $.get(fixUrl("{{ url('store-owner/pos/return-pdf') }}/" + saleId))
        .done(function(data) {
            Swal.close();
            
            if(data.success && data.url) {
                const customer = $('#returnsCustomerName').text();
                const date = $('#returnsDate').text();
                
                let message = `*${"{{ __('returns_report_title') }} " + saleId}*\n`;
                message += `${"{{ __('customer_label') }}"} ${customer}\n`;
                message += `${"{{ __('date_label') }}"} ${date}\n`;
                message += `${"{{ __('check_attachment') }}"}\n`;

                if (typeof triggerWhatsappPrompt === 'function') {
                    // إرسال الرابط كملف
                    triggerWhatsappPrompt('', message, "{{ __('returns_report_pdf') }}", data.url, data.filename);
                } else {
                    // Fallback
                    const url = `https://wa.me/?text=${encodeURIComponent(message + "\n" + data.url)}`;
                    window.open(url, '_blank');
                }
            } else {
                Swal.fire("{{ __('error') }}", "{{ __('pdf_creation_failed') }}", 'error');
            }
        })
        .fail(function(xhr) {
            Swal.close();
            console.error(xhr);
            Swal.fire("{{ __('error') }}", "{{ __('server_connection_error') }}", 'error');
        });
    };

</script>

<style>
    /* CSS for Sales History Table */
    #historyTable th.c-1, #historyTable td.c-1 {
        white-space: normal !important;
        word-wrap: break-word;
        max-width: 150px; /* Adjust as needed */
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Localized Flatpickr Initialization
        const userLocale = "{{ app()->getLocale() }}";
        const localeMap = {
            'ar': 'ar',
            'fr': 'fr',
            'de': 'de',
            'es': 'es',
            'tr': 'tr',
            'pt-BR': 'pt',
            'pt': 'pt',
            'zh': 'zh',
            'ja': 'ja',
            'ru': 'ru',
            'hr': 'hr'
        };

        const flatpickrLocale = localeMap[userLocale] || 'default';

        if (flatpickrLocale !== 'default' && flatpickrLocale !== 'en') {
            const script = document.createElement('script');
            script.src = `https://npmcdn.com/flatpickr/dist/l10n/${flatpickrLocale}.js`;
            script.onload = function() {
                initFlatpickr(flatpickrLocale);
            };
            script.onerror = function() {
                console.warn(`Failed to load flatpickr locale: ${flatpickrLocale}, falling back to English.`);
                initFlatpickr('default');
            };
            document.head.appendChild(script);
        } else {
            initFlatpickr('default');
        }

        function initFlatpickr(locale) {
            const config = {
                dateFormat: "Y-m-d",
                allowInput: true
            };
            
            if (locale !== 'default') {
                config.locale = locale;
            }

            flatpickr("#filterDateFrom", config);
            flatpickr("#filterDateTo", config);
        }
    });
</script>
@endsection
