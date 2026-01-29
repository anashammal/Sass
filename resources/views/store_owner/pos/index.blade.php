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
    .pay-select { flex: 1; height: 32px; font-size: 0.8rem; background: transparent; border: none; color: #fff; }
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
</style>

<div class="container-fluid py-3">
    <div class="pos-layout">
        
        {{-- ================= القسم الأيمن: البحث وجدول المنتجات ================= --}}
        <div class="pos-left">
            <div class="p-3 border-bottom">
                <div class="position-relative">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-0 text-primary"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="barcodeInput" class="form-control bg-light border-0" placeholder="امسح الباركود أو ابحث بالاسم (F3)..." autocomplete="off">
                    </div>
                    <div id="searchResults" class="list-group position-absolute w-100 shadow-lg" style="top: 100%; z-index: 9999; display: none;"></div>
                </div>
            </div>

            <div class="table-responsive flex-grow-1">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light sticky-top" style="z-index: 1;">
                        <tr>
                            <th class="ps-4" width="5%">#</th>
                            <th width="15%">الباركود</th>
                            <th width="30%">المادة</th>
                            <th width="15%">الوحدة</th>
                            <th class="text-center" width="10%">السعر</th>
                            <th class="text-center" width="15%">الكمية</th>
                            <th class="text-center" width="10%">الإجمالي</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody"></tbody>
                </table>
                <div id="emptyCartMsg" class="text-center py-5 mt-5">
                    <i class="fas fa-shopping-cart fa-4x text-light mb-3"></i>
                    <h4 class="text-muted fw-light">ابدأ عملية البيع</h4>
                </div>
            </div>

            {{-- ✅ أزرار التحكم الجديدة في المنتصف --}}
            <div class="p-3 border-top bg-light d-flex gap-3 justify-content-center">
                <button class="btn btn-lg py-3 fw-bold shadow-sm pos-action-btn btn-save-invoice" onclick="submitInvoice()">
                    <i class="fas fa-save me-2"></i> حفظ وطباعة (F9)
                </button>
                <button class="btn btn-lg py-3 fw-bold shadow-sm pos-action-btn btn-cancel-invoice" onclick="clearCart()">
                    <i class="fas fa-times me-2"></i> إلغاء
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
                            title="فتح وردية">
                        <i class="fas fa-door-open"></i>
                    </button>

                    {{-- زر الإرجاع --}}
                    <button type="button" class="btn btn-warning btn-sm text-dark py-1 px-2" onclick="openReturnModal()" title="إرجاع مواد">
                        <i class="fas fa-undo"></i>
                    </button>

                    {{-- زر إغلاق الصندوق (مخفي، يظهره النظام عند الحاجة) --}}
                    <button id="btnCloseShift" class="btn btn-warning btn-sm text-dark fw-bold d-none py-1 px-2" onclick="openCloseShiftModal()" title="إغلاق الوردية">
                        <i class="fas fa-cash-register"></i>
                    </button>

                    {{-- زر الأرشيف --}}
                    <button type="button" class="btn btn-info btn-sm text-white py-1 px-2" onclick="openHistoryModal()" title="سجل الفواتير">
                        <i class="fas fa-history"></i>
                    </button>
                    
                    {{-- زر التصفير --}}
                    <button class="btn btn-danger btn-sm py-1 px-2" onclick="resetPosScreen()" title="تصفير الشاشة">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-2">
                <select id="customerSelect" class="form-select form-select-sm" style="width: 100%">
                    <option value="">عميل نقدي (عام)</option>
                </select>

                <div id="customerBalanceBox" class="customer-balance-box mt-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>الرصيد الحالي:</small>
                        <span id="balanceDisplay" class="fw-bold">0.00</span>
                    </div>
                </div>
            </div>

            <hr class="border-secondary my-1">

            {{-- الخصم والتقريب --}}
            <div class="mb-2">
                <div class="d-flex gap-1 mb-2">
                    <select id="discountType" class="form-select form-select-sm" style="width: 35%" onchange="calculateRemaining()">
                        <option value="fixed">مبلغ</option>
                        <option value="percent">نسبة %</option>
                    </select>
                    <input type="number" id="discountValue" class="form-control form-control-sm text-center" placeholder="قيمة الخصم" oninput="calculateRemaining()">
                </div>
                
                <button class="btn btn-sm btn-outline-warning w-100 py-1" onclick="roundTotalAmount()" style="font-size: 0.8rem;">
                    <i class="fas fa-magic me-1"></i> تقريب المبلغ
                </button>
            </div>

            {{-- ملخص المبالغ --}}
            <div class="mb-2">
                <div class="d-flex justify-content-between mb-1 text-white-50" style="font-size: 0.8rem;">
                    <span>المواد: <span id="itemsCount" class="fw-bold text-white">0</span></span>
                    <span>المجموع: <span id="subTotalDisplay">0.00</span></span>
                </div>

                <div class="d-flex justify-content-between align-items-end mt-2">
                    <span class="fs-6">الصافي:</span>
                    <span id="footerTotal" class="text-success fs-3">0.00</span>
                </div>
            </div>

            <hr class="border-secondary my-1">

           {{-- طرق الدفع --}}
            <div class="mb-2">
                <label class="small text-white-50 mb-1">الدفع</label>
                <div id="paymentRowsContainer" class="payment-section-dynamic">
                    <div class="pay-row" id="payRow_0">
                        <select class="form-select pay-select method-select">
                            <option value="cash">💵 نقدي</option>
                            <option value="card">💳 شبكة</option>
                            <option value="bank">🏦 تحويل</option>
                        </select>
                        <input type="number" class="form-control pay-input amount-input" placeholder="0.00" oninput="calculateRemaining()">
                        <button class="btn-add-pay" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>

            {{-- المتبقي وزر الحفظ --}}
            <div class="mt-auto pt-2 border-top border-secondary">
                <div class="d-flex justify-content-between align-items-end mb-2">
                    <span id="diffLabel" class="fs-6 fw-bold">المتبقي:</span>
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
                <h6 class="modal-title mb-0"><i class="fas fa-history me-2"></i>سجل المبيعات</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-0 bg-white">
                <div class="p-3 bg-white border-bottom shadow-sm d-print-none">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-1 col-3">
                            <label class="small fw-bold text-muted">عرض</label>
                            <select id="filterLimit" class="form-select form-select-sm auto-filter border-primary"><option value="10">10</option><option value="50">50</option><option value="100">100</option><option value="all">الكل</option></select>
                        </div>
                        <div class="col-lg-2 col-6">
                            <label class="small fw-bold text-muted">ترتيب</label>
                            <div class="input-group input-group-sm">
                                <select id="filterSortBy" class="form-select auto-filter border-primary"><option value="created_at">التاريخ</option><option value="total">القيمة</option><option value="due">الدين</option></select>
                                <select id="filterSortOrder" class="form-select auto-filter border-primary" style="max-width: 90px"><option value="desc">تنازلي</option><option value="asc">تصاعدي</option></select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <label class="small fw-bold text-muted">حالة الدفع</label>
                            <select id="filterPaymentStatus" class="form-select form-select-sm auto-filter border-secondary">
                                <option value="">الكل</option>
                                <option value="paid">✅مدفوعة</option>
                                <option value="unpaid">❌غير مدفوعة</option>
                                <option value="partial">⚠️دفع جزئي</option>
                                <option value="overpaid">⏫دفعة زائدة</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-6">
                            <label class="small fw-bold text-muted">بحث بالعميل</label>
                            <select id="filterCustomer" class="form-select form-select-sm w-100"></select>
                        </div>

                        <div class="col-lg-2 col-6"><label class="small fw-bold text-muted">من</label><input type="date" id="filterDateFrom" class="form-control form-control-sm auto-filter"></div>
                        <div class="col-lg-2 col-6"><label class="small fw-bold text-muted">إلى</label><input type="date" id="filterDateTo" class="form-control form-control-sm auto-filter"></div>
                        
                        <div class="col-12 mt-2 d-flex justify-content-between">
                            <div class="position-relative d-inline-block">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_open_cols">
                                    <i class="fas fa-columns"></i> الأعمدة
                                </button>
                                <div id="menu_cols" class="dropdown-menu dropdown-menu-end shadow p-2" style="display: none; position: absolute; top: 100%; left: 0; z-index: 9999; min-width: 200px; max-height: 300px; overflow-y: auto;">
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="0" checked> # الفاتورة</label>
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="1" checked> العميل</label>
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="2" checked> الإجمالي</label>
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="3" checked> المدفوع</label>
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="4" checked> المتبقي</label>
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="5" checked> الحالة</label>
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="6" checked> التاريخ</label>
                                    <label class="dropdown-item"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="7" checked> المستخدم</label>
                                    <div class="dropdown-divider"></div>
                                    <label class="dropdown-item text-danger"><input type="checkbox" class="col-toggle form-check-input me-2" data-col="8" checked> الإجراءات</label>
                                </div>
                            </div>
                            <button onclick="printSalesReport()" class="btn btn-dark btn-sm"><i class="fas fa-print me-1"></i> طباعة التقرير</button>
                        </div>
                    </div>
                </div>

                <div id="printSection" class="p-3">
                    <div class="report-header d-none d-print-flex">
                        <div class="text-end"><h4 id="printStoreName">المتجر</h4><p id="printStoreAddress"></p><p id="printStorePhone"></p></div>
                        <div class="text-center"><h3>تقرير المبيعات</h3><p>{{ date('Y-m-d') }}</p></div>
                        <div class="report-logo"><img id="printStoreLogo" src="" alt="Logo"></div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center w-100" id="historyTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="c-0">#</th>
                                    <th class="c-1">العميل</th>
                                    <th class="c-2">الإجمالي</th>
                                    <th class="c-3">المدفوع</th>
                                    <th class="c-4">المتبقي</th>
                                    <th class="c-5">الحالة</th>
                                    <th class="c-6">التاريخ</th>
                                    <th class="c-7">بواسطة</th>
                                    <th class="c-8 no-print">خيارات</th>
                                </tr>
                            </thead>
                            <tbody id="historyList"></tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr><td colspan="2">المجموع</td><td id="sumTotal">0.00</td><td id="sumPaid">0.00</td><td id="sumDue">0.00</td><td colspan="4"></td></tr>
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
                <h5 class="modal-title">معاينة الفاتورة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="card mb-3 p-2 d-print-none">
                    <div class="d-flex gap-4 justify-content-center">
                        <div class="form-check" id="stampOptionDiv" style="display:none;">
                            <input class="form-check-input" type="checkbox" id="showStamp" onchange="toggleOfficialMarks()">
                            <label class="form-check-label fw-bold" for="showStamp">إضافة ختم المتجر</label>
                        </div>
                        <div class="form-check" id="signatureOptionDiv" style="display:none;">
                            <input class="form-check-input" type="checkbox" id="showSignature" onchange="toggleOfficialMarks()">
                            <label class="form-check-label fw-bold" for="showSignature">إضافة توقيع المالك</label>
                        </div>
                    </div>
                </div>

                <div id="printableInvoice" class="bg-white p-4 mx-auto shadow-sm" style="max-width: 210mm; min-height: 297mm; position: relative;">
                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-8 text-end">
                            <h2 class="fw-bold text-primary" id="invStoreName"></h2>
                            <p class="mb-1 text-muted" id="invStoreAddress"></p>
                            <p class="mb-1">الرقم الضريبي: <span id="invTaxNumber" class="fw-bold">-</span></p>
                        </div>
                        <div class="col-4 text-start">
                            <img id="invLogo" src="" alt="Logo" style="max-width: 120px; max-height: 100px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <h5 class="fw-bold">فاتورة ضريبية</h5>
                            <p class="mb-1">رقم الفاتورة: <span id="invNumber" class="fw-bold text-danger"></span></p>
                            <p class="mb-1">التاريخ: <span id="invDate"></span></p>
                        </div>
                        <div class="col-6 text-start">
                            <h6 class="fw-bold">فاتورة إلى:</h6>
                            <p class="mb-1" id="invCustomerName"></p>
                        </div>
                    </div>
                   <table class="table table-bordered border-dark text-center">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>الباركود</th>
                                <th>الكمية</th>
                                <th class="text-danger">ت. الوحدة</th>
                                <th class="text-danger">إجمالي التكلفة</th>
                                <th>سعر البيع</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody id="invItemsBody"></tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="5" class="text-end">الإجمالي النهائية</td>
                                <td id="invTotalCost" class="text-danger fs-5"></td>
                                <td></td>
                                <td id="invTotal" class="text-dark fs-5"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="row mt-5 pt-5" id="officialMarksArea">
                        <div class="col-6 text-center position-relative">
                            <div id="stampBox" style="display:none;">
                                <p class="mb-2 fw-bold text-decoration-underline">ختم المتجر</p>
                                <img id="invStamp" src="" style="width: 140px; opacity: 0.8; transform: rotate(-15deg);">
                            </div>
                        </div>
                        <div class="col-6 text-center position-relative">
                            <div id="signatureBox" style="display:none;">
                                <p class="mb-2 fw-bold text-decoration-underline">توقيع المسؤول</p>
                                <img id="invSignature" src="" style="width: 150px; opacity: 0.8;">
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="printOfficialInvoice()"><i class="fas fa-print"></i> طباعة</button>
            </div>
        </div>
    </div>
</div>
</div>

{{-- 3. نافذة فتح الصندوق (معدلة: بها زر إغلاق وليست إجبارية الظهور) --}}
<div class="modal fade" id="openShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-cash-register me-2"></i>فتح وردية جديدة</h5>
                {{-- 👇 زر الإغلاق المضاف 👇 --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold">المبلغ النقدي في الدرج (العهدة):</label>
                <div class="input-group">
                    <span class="input-group-text">SAR</span>
                    <input type="number" id="startCashInput" class="form-control form-control-lg text-center fw-bold" placeholder="0.00" min="0" step="0.1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success w-100 py-2 fw-bold" onclick="submitOpenShift()">
                    تأكيد وفتح الصندوق 🚀
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 4. نافذة إغلاق الصندوق (المفصلة) --}}
<div class="modal fade" id="closeShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">إغلاق الوردية (التقفيل)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="alert alert-info py-2 text-center mb-3">
                    <span>توقيت الفتح:</span> <strong id="shiftOpenDate" dir="ltr" class="ms-2">-</strong>
                </div>

                {{-- تفاصيل المبالغ الجديدة --}}
                <div class="row g-2 text-center mb-2">
                    <div class="col-6"><div class="border p-2 bg-light rounded"><small class="text-muted">العهدة (البداية)</small><br><b id="shiftStartCash" class="fs-5">0.00</b></div></div>
                    <div class="col-6"><div class="border p-2 bg-success text-white rounded"><small>صافي الكاش (بالدرج)</small><br><b id="shiftCashSales" class="fs-5">0.00</b></div></div>
                </div>
                
                <div class="row g-2 text-center mb-3">
                    <div class="col-4"><div class="border p-1 bg-light rounded"><small>💳 شبكة</small><br><b id="shiftCardSales">0</b></div></div>
                    <div class="col-4"><div class="border p-1 bg-light rounded"><small>🏦 تحويل</small><br><b id="shiftBankSales">0</b></div></div>
                    <div class="col-4"><div class="border p-1 bg-light rounded text-danger"><small>📝 آجل (دين)</small><br><b id="shiftCreditSales">0</b></div></div>
                </div>

                <div class="alert alert-warning text-center">
                    <h5 class="m-0">المتوقع في الدرج: <span id="shiftExpected" class="fw-bold text-danger fs-3">0.00</span></h5>
                </div>

                <hr>
                <label class="form-label fw-bold">المبلغ الفعلي (جرد):</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white text-success">💵</span>
                    <input type="number" id="endCashInput" class="form-control text-center fw-bold text-success" placeholder="أدخل المبلغ الموجود">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-warning fw-bold text-dark px-4" onclick="submitCloseShift()">إغلاق الوردية وترحيل</button>
            </div>
        </div>
    </div>
</div>

{{-- 5. نافذة الإرجاع (Return Modal) --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">استرجاع منتج (Refund)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label>العميل (اختياري)</label>
                        <select id="returnCustomerSelect" class="form-select" style="width:100%"></select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                           {{-- ✅ تم تحويله إلى بحث ذكي --}}
<select id="returnProductSelect" class="form-select" style="width:100%"></select>
                        </div>
                    </div>
                </div>
                
                <table class="table table-bordered text-center table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="returnResultsBody"></tbody>
                </table>
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

<script>
    let cart = [];
    let currentFocus = -1;
    let debounceTimer;
    let selectedCustomer = null;
    let isShiftOpen = false;
    let roundingDifference = 0; 

    // متغيرات أرقام الفواتير القادمة
    const nextInvoiceNumber = "#{{ $nextInvoice }}";
    const nextWithdrawalNumber = "#{{ $nextWithdrawal ?? 'SOV-Unknown' }}"; 

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
            placeholder: "بحث عن عميل...",
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('store.pos.search-customers') }}",
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
                .html('<i class="fas fa-file-export me-2"></i> تسجيل مسحوبات')
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
                .html('<i class="fas fa-save me-2"></i> حفظ وطباعة (F9)')
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
            htmlContent = `<span class="text-success fw-bold fs-5" dir="ltr">+${bal.toFixed(2)} <i class="fas fa-arrow-up"></i></span> <small class="text-success fw-bold me-2">(له رصيد)</small>`;
        } else {
            htmlContent = `<span class="text-danger fw-bold fs-5" dir="ltr">${Math.abs(bal).toFixed(2)} <i class="fas fa-arrow-down"></i></span> <small class="text-danger fw-bold me-2">(عليه دين)</small>`;
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
        fetch("{{ route('store.pos.search-products') }}?term=" + term)
            .then(res => res.json())
            .then(data => {
                if (data.length === 1) {
                    checkExpiryAndAdd(data[0]); // ✅ تم الاستبدال للفحص قبل الإضافة
                    closeSearch();
                    barcodeInput.focus();
                } else if (data.length > 1) {
                    showSuggestions(data);
                } else {
                    if(isEnterKey) { toastr.error('المنتج غير موجود'); closeSearch(); }
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
                    <div class="fw-bold">${p.name_ar}</div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted font-monospace">${p.default_barcode}</small>
                        <small class="${qtyColor}">مخزون: ${parseFloat(p.quantity).toFixed(2)}</small>
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
            promptStockAdjustment(product.name_ar, product.id, maxStock, function(newStock) {
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
                name: product.name_ar,
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
        toastr.success('تمت الإضافة');
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
                unitHtml = `<span class="unit-text">${item.units[0]?.unit_name || 'قطعة'}</span>`;
            }

            let currentUnitObj = (item.units && item.units.length > 1) 
                ? item.units.find(u => u.unit_id == item.selected_unit_id)
                : (item.units[0] || {});
            let currentUnitName = currentUnitObj.unit_name || 'قطعة';
            
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
                    toastr.success('تم تعديل المخزون وتغيير الوحدة بنجاح');
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
            toastr.warning('هذه الوحدة لا تقبل الكسور');
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
            toastr.warning('الكمية غير صحيحة');
            item.qty = 1;
        }
        renderCart();
    };

    window.removeItem = (index) => { cart.splice(index, 1); renderCart(); };
    window.clearCart = () => { if(confirm('مسح الفاتورة؟')) { cart = []; renderCart(); } };

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
        document.querySelectorAll('.amount-input').forEach(input => totalPaid += parseFloat(input.value) || 0);

        let diff = netTotal - totalPaid;
        let label = "المتبقي:";
        let colorClass = "text-success";
        let displayText = "0.00";

        if (diff > 0.001) {
            label = "المتبقي (عليه):";
            colorClass = "text-danger";
            displayText = diff.toFixed(2);
        } else if (diff < -0.001) {
            label = "رصيد إضافي (له):";
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
        toastr.success('تم تقريب المبلغ: ' + rounded.toFixed(2));
    }

    function addPaymentRow() {
        let { diff } = calculateRemaining(); 
        let val = diff > 0.001 ? diff.toFixed(2) : '';

        $('#paymentRowsContainer .btn-add-pay').removeClass('btn-add-pay').addClass('btn-remove-pay')
            .attr('onclick', 'removePaymentRow(this)').html('<i class="fas fa-minus"></i>');

        let div = document.createElement('div');
        div.className = 'pay-row';
        div.innerHTML = `
            <select class="form-select pay-select method-select">
                <option value="cash">💵 نقدي</option>
                <option value="card">💳 شبكة / كرت</option>
                <option value="bank">🏦 تحويل بنكي</option>
            </select>
            <input type="number" class="form-control pay-input amount-input" placeholder="0.00" value="${val}" oninput="calculateRemaining()">
            <button class="btn-add-pay" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
        `;
        document.getElementById('paymentRowsContainer').appendChild(div);
        calculateRemaining();
        div.querySelector('input').focus();
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
                title: 'الصندوق مغلق',
                text: 'لا يمكن إتمام عملية البيع قبل فتح الصندوق (الوردية).',
                confirmButtonText: 'فتح الصندوق الآن',
                confirmButtonColor: '#198754'
            }).then((result) => {
                if (result.isConfirmed) {
                    showOpenShiftModal(); // فتح النافذة
                }
            });
            return; // إيقاف تنفيذ دالة البيع
        }

        if(cart.length === 0) return toastr.error('السلة فارغة');
        
        let total = parseFloat($('#footerTotal').text());
        let pay = []; 
        let totalPaid = 0;
        
        // ✅ فحص صارم لصاحب المتجر (بناء على العلامة أو الاسم)
        const isOwner = selectedCustomer?.is_store_owner || (selectedCustomer?.name && selectedCustomer.name.includes('صاحب المتجر'));

        const performAjaxSave = (finalData) => {
            let data = {
                items: cart, total: total, customer_id: selectedCustomer?.id, 
                payments: pay, discount_amount: $('#discountValue').val(), _token: '{{ csrf_token() }}',
                ...extraData, ...finalData
            };

            Swal.fire({title: 'جاري الحفظ...', didOpen: () => Swal.showLoading()});

            $.post("{{ route('store.pos.save') }}", data)
             .done(() => { 
                 Swal.fire({icon:'success', title: isOwner ? 'تم تسجيل المسحوبات' : 'تمت العملية بنجاح', timer:1000, showConfirmButton:false}); 
                 setTimeout(() => location.reload(), 1000);
             })
             .fail((xhr) => { 
                 let res = xhr.responseJSON || {};
                 if(res.error === 'customer_required') {
                     Swal.fire({icon: 'error', title: 'تنبيه', text: res.message});
                 } else if (res.error === 'credit_limit_exceeded') {
                     Swal.fire({
                         title: 'تجاوز حد الدين',
                         html: `<div class="text-end"><p class="text-danger fw-bold">${res.message}</p><p>الحد الحالي: <b>${res.current_limit}</b></p><hr><p class="fw-bold">هل تريد رفع الحد؟</p></div>`,
                         icon: 'warning',
                         showCancelButton: true, confirmButtonText: 'نعم', cancelButtonText: 'إلغاء', confirmButtonColor: '#d33'
                     }).then((r) => { 
                         if (r.isConfirmed) window.submitInvoice({ update_limit_to: parseFloat(res.required_limit) + 10, bypass_confirm: true });
                     });
                 } else if(res.error === 'stock_error') {
                     Swal.fire({
                         title: 'نقص مخزون', html: res.message, icon: 'warning',
                         showCancelButton: true, confirmButtonText: 'تعديل المخزون', cancelButtonText: 'إلغاء'
                     }).then((r) => {
                         if (r.isConfirmed && typeof promptStockAdjustment === 'function') promptStockAdjustment(res.product_id, res.product_name, res.current_stock);
                     });
                 } else {
                     Swal.fire({icon:'error', title:'خطأ', text: res.message || 'حدث خطأ غير معروف'});
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
            if (!selectedCustomer) return Swal.fire({icon: 'error', title: 'مطلوب عميل', text: 'لا يمكن تسجيل دين لعميل عام'});
            let newBal = currentBalance - diff;
            let msg = currentBalance < 0 ? `سيصبح إجمالي الدين: <b class="text-danger">${Math.abs(newBal).toFixed(2)}</b>` : `سيصبح رصيده: <b>${newBal.toFixed(2)}</b>`;
            Swal.fire({
                title: 'تأكيد تسجيل الدين',
                html: `<div class="text-end fs-6">قيمة الدين: <span class="text-danger fw-bold">${diff.toFixed(2)}</span><hr><small>${msg}</small></div>`,
                icon: 'question',
                showCancelButton: true, confirmButtonText: 'نعم', cancelButtonText: 'إلغاء', confirmButtonColor: '#d33'
            }).then((result) => { if (result.isConfirmed) performAjaxSave({}); });
        } else if (diff < -0.01) {
            let surplus = Math.abs(diff);
            if (!selectedCustomer) {
                return Swal.fire({
                    title: 'مبلغ زائد', html: `المبلغ الزائد: <b class="text-success">${surplus.toFixed(2)}</b>`,
                    confirmButtonText: '✅ تم إرجاع الكاش',
                }).then((r) => { if(r.isConfirmed) performAjaxSave({ action: 'return_cash', change_amount: surplus }); });
            }
            Swal.fire({
                title: 'مبلغ مدفوع زائد',
                html: `<div class="text-end"><h4 class="text-success text-center">${surplus.toFixed(2)}</h4><p class="fw-bold">كيف تريد التعامل مع الباقي؟</p></div>`,
                icon: 'info',
                showDenyButton: true, showCancelButton: true,
                confirmButtonText: '💰 إرجاع كاش', denyButtonText: '📥 إضافة لرصيد العميل', cancelButtonText: 'إلغاء',
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
            title: 'الكمية غير كافية!',
            html: `المنتج: <b>${productName}</b><br>المخزون الحالي: <b class="text-danger">${currentStock}</b>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'تعديل المخزون',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'تعديل المخزون',
                    input: 'number',
                    inputLabel: 'الكمية الجديدة:',
                    inputValue: currentStock,
                    showCancelButton: true,
                    confirmButtonText: 'حفظ',
                    showLoaderOnConfirm: true,
                    preConfirm: (newQty) => {
                        return $.post("{{ route('store.pos.adjustStock') }}", {
                            product_id: productId, new_qty: newQty, _token: '{{ csrf_token() }}'
                        }).then(response => ({ newQty: newQty, response: response }))
                          .fail(xhr => Swal.showValidationMessage(`خطأ: ${xhr.responseJSON.message}`));
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        Swal.fire('تم!', 'تم تحديث المخزون.', 'success');
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
            <select class="form-select pay-select method-select"><option value="cash">💵 نقدي</option><option value="card">💳 شبكة / كرت</option><option value="bank">🏦 تحويل بنكي</option></select>
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
                theme: 'bootstrap-5', dir: "rtl", placeholder: "الكل", allowClear: true,
                ajax: {
                    url: "{{ route('store.pos.search-customers') }}", dataType: 'json', delay: 250,
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

        $('#historyList').html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>');

        $.get("{{ route('store.pos.recent-sales') }}", params, function(res) {
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
                    if(s.status === 'overpaid') badge = '<span class="badge bg-info text-dark">زائدة</span>';
                    else if(s.status === 'paid') badge = '<span class="badge bg-success">مدفوعة</span>';
                    else if(s.status === 'partial') badge = '<span class="badge bg-warning text-dark">جزئي</span>';
                    else badge = '<span class="badge bg-danger">غير مدفوعة</span>';

                    rows += `<tr>
                        <td class="c-0 fw-bold text-primary">${s.invoice_number}</td>
                        <td class="c-1">${s.customer_name}</td>
                        <td class="c-2">${parseFloat(s.total).toFixed(2)}</td>
                        <td class="c-3 text-success">${parseFloat(s.paid).toFixed(2)}</td>
                        <td class="c-4 text-danger">${parseFloat(s.due).toFixed(2)}</td>
                        <td class="c-5">${badge}</td>
                        <td class="c-6 small">${s.date}</td>
                        <td class="c-7 small text-muted">${s.user_name}</td>
                        <td class="c-8 no-print">
                            <button class="btn btn-sm btn-outline-info" onclick="viewInvoice(${s.id})"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(${s.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            } else { 
                rows = '<tr><td colspan="9" class="text-center text-muted py-3">لا توجد بيانات</td></tr>'; 
            }

            $('#historyList').html(rows);
            $('#sumTotal').text(t.toFixed(2)); $('#sumPaid').text(p.toFixed(2)); $('#sumDue').text(d.toFixed(2));
            $('#historyList').html(rows);
            $('#sumTotal').text(t.toFixed(2)); $('#sumPaid').text(p.toFixed(2)); $('#sumDue').text(d.toFixed(2));
            renderPagination(meta);
            applyColumnVisibility(); // ✅ تطبيق إخفاء الأعمدة على البيانات الجديدة
        }).fail(() => {
            $('#historyList').html('<tr><td colspan="9" class="text-danger text-center">خطأ في الاتصال (Recent Sales)</td></tr>');
        });
    }

    // ...

    window.processReturnItem = function(itemId, maxQty) {
        Swal.fire({
            title: 'الكمية المسترجعة',
            input: 'number',
            inputAttributes: {min: 0.1, max: maxQty, step: 0.1},
            inputValue: maxQty,
            showCancelButton: true, confirmButtonText: 'تأكيد'
        }).then((r) => {
            if(r.isConfirmed) {
                $.post("{{ route('store.pos.return.process') }}", {
                    item_id: itemId, return_qty: r.value, _token: '{{ csrf_token() }}'
                }).done(() => {
                    toastr.success('تم الإرجاع');
                    searchForReturnInvoices(); 
                }).fail((xhr) => toastr.error(xhr.responseJSON.error || 'خطأ'));
            }
        });
    };

    // ...

    function openUpdateExpiryModal(product) {
        let current = product.expiry_date || '';
        Swal.fire({
            title: 'تحديث صلاحية',
            html: `
                <div class="mb-3 text-start">
                    <label>المنتج: ${product.name_ar}</label>
                    <input type="date" id="newExpiryDate" class="form-control" value="${current}">
                </div>
                <div class="mb-3 text-start">
                    <label>سبب التحديث:</label>
                    <textarea id="updateReason" class="form-control" placeholder="مثلاً: خطأ في الادخال، تمديد من المورد..."></textarea>
                </div>
            `,
            showCancelButton: true, confirmButtonText: 'تحديث', cancelButtonText: 'إلغاء',
            preConfirm: () => {
                let d = document.getElementById('newExpiryDate').value;
                let r = document.getElementById('updateReason').value;
                if(!d) Swal.showValidationMessage('التاريخ مطلوب');
                if(!r) Swal.showValidationMessage('السبب مطلوب');
                return { date: d, reason: r };
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
                    Swal.fire('تم التوثيق!', res.message, 'success');
                    // الآن نسمح بإضافته للسلة
                    product.alert_status = 'ok'; 
                    addToCart(product);
                }).fail((xhr) => {
                    Swal.fire('خطأ', xhr.responseJSON.error || 'حدث خطأ في النظام', 'error');
                });
            }
        });
    }

    function renderPagination(meta) {
        $('#paginationControls').empty();
        if (!meta || meta.total === 0) return;
        let html = '<nav><ul class="pagination pagination-sm m-0">';
        html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}"><button class="page-link" onclick="getRecentSales(${meta.current_page - 1})">السابق</button></li>`;
        let start = Math.max(1, meta.current_page - 2);
        let end = Math.min(meta.last_page, meta.current_page + 2);
        for (let i = start; i <= end; i++) {
            html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}"><button class="page-link" onclick="getRecentSales(${i})">${i}</button></li>`;
        }
        html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}"><button class="page-link" onclick="getRecentSales(${meta.current_page + 1})">التالي</button></li>`;
        html += '</ul></nav>';
        html += `<div class="ms-3 text-muted small align-self-center">صفحة ${meta.current_page} من ${meta.last_page} (إجمالي ${meta.total})</div>`;
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
            title: 'تأكيد الحذف', text: "سيتم حذف الفاتورة وإرجاع المخزون.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'نعم، حذف', cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: "{{ url('store-owner/pos/delete-sale') }}/" + id, // استخدام Helper لضمان المسار
                    type: 'DELETE',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function() { toastr.success('تم الحذف'); getRecentSales(); },
                    error: function() { toastr.error('فشل الحذف'); }
                });
            }
        });
    };

   window.viewInvoice = function(id) {
        $('#invItemsBody').html('<tr><td colspan="6" class="text-center py-3">جاري التحميل...</td></tr>');
        $('#showStamp').prop('checked', false);
        $('#showSignature').prop('checked', false);
        $('#stampBox').hide();
        $('#signatureBox').hide();
        $('#invoiceModal').modal('show');

        $.ajax({
            url: "{{ url('store-owner/pos/sale-details') }}/" + id,
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
                $('#invCustomerName').text(s.contact ? s.contact.contact_name : 'عميل نقدي');
                
                if (st.logo_url) $('#invLogo').attr('src', st.logo_url).show(); else $('#invLogo').hide();
                if (st.stamp_url) { $('#stampOptionDiv').show(); $('#invStamp').attr('src', st.stamp_url); } else { $('#stampOptionDiv').hide(); }
                if (st.signature_url) { $('#signatureOptionDiv').show(); $('#invSignature').attr('src', st.signature_url); } else { $('#signatureOptionDiv').hide(); }

                let h = ''; 
                let totalCostSum = 0;
                items.forEach((i, x) => {
                    totalCostSum += parseFloat(i.cost);
                    h += `<tr>
                        <td>${x + 1}</td>
                        <td>${i.name}</td>
                        <td><span class="badge bg-secondary">${i.barcode || '---'}</span></td>
                        <td>${i.qty} ${i.unit}</td>
                        <td class="text-danger">${(i.cost / i.qty).toFixed(2)}</td> 
                        <td class="text-danger fw-bold">${i.cost.toFixed(2)}</td>
                        <td>${parseFloat(i.price).toFixed(2)}</td>
                        <td>${parseFloat(i.total).toFixed(2)}</td>
                    </tr>`;
                });
                
                $('#invItemsBody').html(h); 
                $('#invTotal').text(parseFloat(s.total).toFixed(2));
                $('#invTotalCost').text(totalCostSum.toFixed(2));
            },
            error: function(err) { $('#invoiceModal').modal('hide'); toastr.error('فشل تحميل الفاتورة'); }
        });
    };

    window.toggleOfficialMarks = function() {
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

        // ✅ تفعيل فلتر الأعمدة
        $('.col-toggle').on('change', function() {
            applyColumnVisibility();
        });
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
        $.get("{{ route('store.pos.shift.status') }}", function(res) {
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
        if(amount === '') return toastr.error('الرجاء إدخال المبلغ');
        
        $.post("{{ route('store.pos.shift.open') }}", { start_cash: amount, _token: '{{ csrf_token() }}' })
         .done(() => {
             $('#openShiftModal').modal('hide');
             $('.modal-backdrop').remove();       
             $('body').removeClass('modal-open'); 
             $('body').css('padding-right', ''); 
             toastr.success('تم فتح الصندوق');
             isShiftOpen = true;
             $('#btnOpenShift').addClass('d-none');
             $('#btnCloseShift').removeClass('d-none');
             $('#barcodeInput').focus();
         })
         .fail(() => toastr.error('حدث خطأ أثناء الفتح'));
    };

    window.openCloseShiftModal = function() {
        Swal.showLoading(); 
        $.get("{{ route('store.pos.shift.summary') }}", function(res) {
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
            toastr.error('لا يوجد صندوق مفتوح حالياً');
        });
    };

    window.submitCloseShift = function() {
        let actualCash = $('#endCashInput').val();
        if(actualCash === '') return toastr.error('الرجاء إدخال المبلغ الموجود فعلياً');

        Swal.fire({
            title: 'هل أنت متأكد؟', text: "سيتم إغلاق الوردية.", icon: 'warning',
            showCancelButton: true, confirmButtonText: 'نعم، إغلاق', cancelButtonText: 'تراجع', confirmButtonColor: '#ffc107', cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.post("{{ route('store.pos.shift.close') }}", { end_cash: actualCash, _token: '{{ csrf_token() }}' })
                .done((res) => {
                    Swal.close();
                    $('#closeShiftModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                    let diff = parseFloat(res.difference);
                    let msg = diff === 0 ? 'مطابقة ممتازة ✅' : (diff < 0 ? `عجز: ${diff} ❌` : `زيادة: +${diff} ⚠️`);
                    Swal.fire({ title: 'تم الإغلاق', text: msg, icon: diff === 0 ? 'success' : 'warning' }).then(() => location.reload());
                })
                .fail((xhr) => {
                    Swal.close();
                    let msg = 'حدث خطأ غير معروف';
                    if(xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    else if (xhr.status === 500) msg = 'خطأ في السيرفر (500)';
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
            theme: 'bootstrap-5', placeholder: "اختر العميل (اختياري)", allowClear: true,
            ajax: { url: "{{ route('store.pos.search-customers') }}", dataType: 'json', processResults: data => ({results: data.results}) }
        }).on('change', function() { searchForReturnInvoices(); }); // عند تغيير العميل نبحث فوراً

        // 🔥 تفعيل بحث المنتجات الذكي (مثل شاشة البيع) 🔥
        $('#returnProductSelect').select2({
            dropdownParent: $('#returnModal'),
            theme: 'bootstrap-5', 
            placeholder: "ابحث عن المنتج (اسم أو باركود)...", 
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('store.pos.search-products') }}", // ✅ تم التصحيح
                dataType: 'json',
                delay: 250,
                data: function (params) { return { term: params.term }; }, // ✅ استعادة term
                processResults: function (data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.name_ar + ' (' + item.default_barcode + ')'
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

        $('#returnResultsBody').html('<tr><td colspan="5">جاري البحث...</td></tr>');

        $.get("{{ route('store.pos.return.search') }}", {term: term, customer_id: cust}, function(res) {
            let rows = '';
            if(res.invoices.length === 0) rows = '<tr><td colspan="5" class="text-muted">لا توجد فواتير لهذا المنتج/العميل</td></tr>';
            else {
                res.invoices.forEach(i => {
                    rows += `<tr>
                        <td><span class="fw-bold text-primary">${i.invoice_no}</span><br><small>${i.date}</small></td>
                        <td>${i.product_name} <br> <span class="badge bg-light text-dark border">${i.unit_name}</span></td>
                        <td class="fw-bold">${i.qty}</td>
                        <td>${i.price}</td>
                        <td><button class="btn btn-sm btn-outline-danger fw-bold" onclick="processReturnItem(${i.item_id}, ${i.qty})">↩️ إرجاع</button></td>
                    </tr>`;
                });
            }
            $('#returnResultsBody').html(rows);
        });
    };

    window.processReturnItem = function(itemId, maxQty) {
        Swal.fire({
            title: 'الكمية المسترجعة',
            input: 'number',
            inputAttributes: {min: 0.1, max: maxQty, step: 0.1},
            inputValue: maxQty,
            showCancelButton: true, confirmButtonText: 'تأكيد'
        }).then((r) => {
            if(r.isConfirmed) {
                $.post("{{ route('store.pos.return.process') }}", {
                    item_id: itemId, return_qty: r.value, _token: '{{ csrf_token() }}'
                }).done(() => {
                    toastr.success('تم الإرجاع');
                    searchForReturnInvoices(); 
                }).fail((xhr) => toastr.error(xhr.responseJSON.error || 'خطأ'));
            }
        });
    };
                   // 🔥 دالة حارس الصلاحية (تمنع المنتهي وتنبه القريب) 🔥
    function checkExpiryAndAdd(product) {
        // 1. إذا كان المنتج منتهي (أحمر) -> منع بات
        if (product.alert_status === 'expired') {
            Swal.fire({
                title: '⛔ منتج منتهي الصلاحية!',
                html: `<h4 class="text-danger my-2">${product.name_ar}</h4>
                       <div class="alert alert-danger fw-bold">${product.alert_msg}</div>
                       <p>حفاظاً على السلامة، لا يمكن بيع هذا المنتج.</p>`,
                icon: 'error',
                showDenyButton: true,
                confirmButtonText: '📝 تحديث التاريخ (على مسؤوليتي)',
                confirmButtonColor: '#ffc107', // أصفر
                denyButtonText: 'إلغاء البيع',
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
            toastr.warning(product.alert_msg, 'تنبيه صلاحية', {timeOut: 5000, positionClass: "toast-top-center"});
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
            title: 'تحديث الصلاحية وتوثيق العملية',
            html: `
                <div class="text-start bg-light p-3 rounded border">
                    <label class="fw-bold">المنتج: ${product.name_ar}</label>
                    <hr>
                    <label class="mt-2 text-primary fw-bold">تاريخ الانتهاء الجديد:</label>
                    <input type="date" id="newExpiryDate" class="form-control mb-3 border-primary">
                    
                    <label class="text-danger fw-bold">سبب التعديل (إلزامي للتوثيق):</label>
                    <textarea id="updateReason" class="form-control border-danger" rows="2" placeholder="اكتب السبب بوضوح ليظهر في تقرير الإدارة..."></textarea>
                </div>
            `,
            confirmButtonText: 'حفظ التعديل والبيع',
            showCancelButton: true,
            cancelButtonText: 'تراجع',
            focusConfirm: false,
            preConfirm: () => {
                const date = document.getElementById('newExpiryDate').value;
                const reason = document.getElementById('updateReason').value;
                if (!date || !reason || reason.length < 5) {
                    Swal.showValidationMessage('الرجاء إدخال التاريخ وسبب مقنع (5 أحرف على الأقل)');
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
                    Swal.fire('تم التوثيق!', res.message, 'success');
                    // الآن نسمح بإضافته للسلة
                    product.alert_status = 'ok'; 
                    addToCart(product);
                }).fail((xhr) => {
                    Swal.fire('خطأ', xhr.responseJSON.error || 'حدث خطأ في النظام', 'error');
                });
            }
        });
    }
</script>
@endsection