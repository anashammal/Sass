@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i> تقرير مسحوبات المالك</h5>
            <a href="{{ route('store.pos.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> العودة للكاشير</a>
        </div>
        
        <div class="card-body">
            {{-- فلاتر البحث --}}
            <div class="row g-3 mb-4 bg-light p-3 rounded">
                <div class="col-md-4">
                    <label class="form-label fw-bold">من تاريخ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" id="filterDateFrom" class="form-control enhanced-date-input auto-filter">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">إلى تاريخ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" id="filterDateTo" class="form-control enhanced-date-input auto-filter">
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-undo me-1"></i> إعادة تعيين
                    </button>
                </div>
            </div>

            {{-- ملخص الأرقام --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded border border-danger">
                        <label class="small text-muted fw-bold">إجمالي التكلفة (رأس المال - سعر الشراء)</label>
                        <h3 class="fw-bold mb-0" id="displayTotalCost">{{ number_format($totalCost, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded border border-success">
                        <label class="small text-muted fw-bold">إجمالي سعر البيع (Selling Price)</label>
                        <h3 class="fw-bold mb-0" id="displayTotalSale">{{ number_format($totalSale, 2) }}</h3>
                    </div>
                </div>
            </div>

            {{-- الجدول --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>رقم المسحوب</th>
                            <th>عدد المواد</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="withdrawalsTableBody">
                        @include('store_owner.pos.partials.withdrawals_table', ['withdrawals' => $withdrawals])
                    </tbody>
                </table>
            </div>

            {{-- روابط التصفح --}}
            <div id="paginationContainer" class="d-flex justify-content-center mt-3">
                {{ $withdrawals->links() }}
            </div>
        </div>
    </div>
</div>

{{-- نافذة التفاصيل --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل المسحوبات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <div class="text-center py-5"><div class="spinner-border"></div></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // 1. التحديث التلقائي
        $('.auto-filter').on('change', function() {
            fetchData(1);
        });

        // 2. التحقق الذكي من التواريخ
        // تطبيق القيود فوراً عند التحميل
        if($('#filterDateFrom').val()) $('#filterDateTo').attr('min', $('#filterDateFrom').val());
        if($('#filterDateTo').val()) $('#filterDateFrom').attr('max', $('#filterDateTo').val());

        $('#filterDateFrom').on('change', function() {
            let fromDate = $(this).val();
            $('#filterDateTo').attr('min', fromDate);
            let currentTo = $('#filterDateTo').val();
            if(fromDate && currentTo && currentTo < fromDate) {
                $('#filterDateTo').val(fromDate).trigger('change');
            }
        });

        $('#filterDateTo').on('change', function() {
            let toDate = $(this).val();
            $('#filterDateFrom').attr('max', toDate);
            let currentFrom = $('#filterDateFrom').val();
            if(toDate && currentFrom && currentFrom > toDate) {
                $('#filterDateFrom').val(toDate).trigger('change');
            }
        });
    });

    function resetFilters() {
        $('#filterDateFrom').val('').trigger('change');
        $('#filterDateTo').val('').trigger('change');
        fetchData(1);
    }

    function fetchData(page = 1) {
        let fromDate = document.getElementById('filterDateFrom').value;
        let toDate = document.getElementById('filterDateTo').value;

        $('#withdrawalsTableBody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>');

        $.ajax({
            url: "{{ route('store.pos.withdrawals') }}",
            data: { from_date: fromDate, to_date: toDate, page: page },
            success: function(response) {
                $('#withdrawalsTableBody').html(response.html);
                $('#displayTotalCost').text(response.totals.cost);
                $('#displayTotalSale').text(response.totals.sale);
                
                // تحديث الروابط
                let paginationLinks = $(response.html).find('#paginationLinks').html();
                if(paginationLinks) $('#paginationContainer').html(paginationLinks);
            }
        });
    }

    $(document).on('click', '.pagination a', function(event) {
        event.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        fetchData(page);
    });

    function viewInvoice(id) {
        $('#detailsModal').modal('show');
        $('#detailsModalBody').html('<div class="text-center py-5"><div class="spinner-border"></div></div>');
        
        // استخدام نفس دالة التفاصيل الموجودة في الكاشير
        $.get("{{ url('store-owner/pos/sale-details') }}/" + id, function(res) {
            let html = `
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <h5>${res.sale.is_withdrawal ? 'مسحوبات رقم ' + res.sale.withdrawal_number : 'فاتورة ' + res.sale.id}</h5>
                    <span>${new Date(res.sale.created_at).toLocaleDateString()}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm text-center align-middle" style="min-width: 600px;">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>المنتج</th>
                                <th>الباركود</th>
                                <th>الكمية</th>
                                <th>التكلفة<br><small>(للوحدة)</small></th>
                                <th>إجمالي<br>التكلفة</th>
                                <th>سعر<br>البيع</th>
                                <th>الإجمالي<br><small>(سعر البيع)</small></th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            let totalCostSum = 0;
            res.items.forEach(item => {
                totalCostSum += parseFloat(item.cost);
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td><span class="badge bg-secondary">${item.barcode}</span></td>
                        <td>${item.qty} ${item.unit}</td>
                        <td>${(item.cost / item.qty).toFixed(2)}</td>
                        <td class="fw-bold">${item.cost.toFixed(2)}</td>
                        <td class="fw-bold">${item.price.toFixed(2)}</td>
                        <td>${item.total.toFixed(2)}</td>
                    </tr>`;
            });
            html += `</tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="4" class="text-end">الإجمالي النهائي</td>
                            <td class="text-danger fs-5">${totalCostSum.toFixed(2)}</td>
                            <td></td>
                            <td class="text-dark fs-5">${res.sale.total}</td>
                        </tr>
                    </tfoot>
                </table>
                </div>`;
            
            $('#detailsModalBody').html(html);
        });
    }

    function returnItems(id) {
        if(confirm('هل أنت متأكد من إرجاع هذه المسحوبات؟ سيتم حذف العملية وإرجاع المواد للمخزون.')) {
            $.ajax({
                url: "{{ route('store.pos.delete-sale', ':id') }}".replace(':id', id),
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    alert(res.message || 'تمت العملية بنجاح');
                    location.reload();
                },
                error: function(xhr) {
                    let msg = 'فشل في إرجاع المسحوبات';
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert('خطأ: ' + msg);
                }
            });
        }
    }
</script>
@endsection
