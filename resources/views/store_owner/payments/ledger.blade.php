@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="fas fa-file-invoice me-2 text-info"></i>كشف حساب: {{ $contact->contact_name }}
        </h3>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary no-print">
                <i class="fas fa-print me-1"></i> طباعة كشف الحساب
            </button>
            <a href="{{ route('store.contacts.index') }}" class="btn btn-primary no-print ms-2">
                <i class="fas fa-arrow-right me-1"></i> العودة لجهات الاتصال
            </a>
        </div>
    </div>

    <!-- Contact Info Cards -->
    <div class="row g-3 mb-4 no-print">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-info">
                <small class="text-muted fw-bold">الرصيد الحالي</small>
                <h4 class="fw-bold mb-0 mt-1 {{ $contact->balance < 0 ? 'text-danger' : 'text-success' }}" dir="ltr">
                    {{ number_format($contact->balance, 2) }}
                </h4>
                <small class="text-muted">
                    @if($contact->balance < 0) عليه دين للمحل @elseif($contact->balance > 0) له رصيد طرفنا @else رصيد مطابق @endif
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <small class="text-muted fw-bold">رقم الهاتف</small>
                <h5 class="fw-bold mb-0 mt-1" dir="ltr">{{ $contact->phone ?? '--' }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <small class="text-muted fw-bold">الشركة</small>
                <h5 class="fw-bold mb-0 mt-1">{{ $contact->company_name ?? '--' }}</h5>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">التاريخ</th>
                            <th class="py-3">النوع</th>
                            <th class="py-3">المرجع</th>
                            <th class="py-3">مبلغ الحركة (+/-)</th>
                            <th class="py-3 text-end px-4">المبلغ المتراكم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningBalance = 0; @endphp
                        @forelse($ledger as $row)
                            @php 
                                $effect = $row->effect;
                                $runningBalance += $effect;
                                
                                $typeLabel = 'حركة';
                                $typeClass = 'badge bg-secondary';
                                if($row->type == 'sale') { $typeLabel = 'فاتورة بيع'; $typeClass = 'badge bg-primary'; }
                                elseif($row->type == 'purchase') { $typeLabel = 'فاتورة شراء'; $typeClass = 'badge bg-warning text-dark'; }
                                elseif($row->type == 'payment') { $typeLabel = 'دفعة مالية'; $typeClass = 'badge bg-success'; }
                            @endphp
                            <tr>
                                <td class="px-4 text-nowrap">{{ \Carbon\Carbon::parse($row->date)->format('Y-m-d') }}</td>
                                <td><span class="{{ $typeClass }}">{{ $typeLabel }}</span></td>
                                <td class="text-muted small text-start">
                                    @if(in_array($row->type, ['sale', 'purchase']))
                                        <a href="javascript:void(0)" onclick="viewMovementDetails('{{ $row->type }}', {{ $row->id }})" class="text-info fw-bold text-decoration-none">
                                            <i class="fas fa-search-plus me-1"></i> {{ $row->reference }}
                                        </a>
                                    @else
                                        {{ $row->reference }}
                                    @endif
                                </td>
                                <td class="fw-bold {{ $effect > 0 ? 'text-success' : 'text-danger' }}" dir="ltr">
                                    {{ ($effect > 0 ? '+' : '') . number_format($effect, 2) }}
                                </td>
                                <td class="text-end px-4 fw-bold {{ $runningBalance < 0 ? 'text-danger' : 'text-success' }}" dir="ltr">
                                    {{ number_format($runningBalance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-history fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p>لا توجد حركات مسجلة لهذا الحساب</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end py-3">الرصيد النهائي المسجل حالياً:</td>
                            <td class="text-end px-4 {{ $contact->balance < 0 ? 'text-danger' : 'text-success' }}" dir="ltr">
                                {{ number_format($contact->balance, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .navbar, .sidebar, .no-print { display: none !important; }
        .container-fluid { width: 100%; padding: 0; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
</style>
@endsection

<!-- Movement Detail Modal -->
<div class="modal fade" id="movementDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">تفاصيل الحركة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="movementDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">جاري تحميل التفاصيل...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function viewMovementDetails(type, id) {
        const modal = new bootstrap.Modal(document.getElementById('movementDetailModal'));
        const body = document.getElementById('movementDetailBody');
        
        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">جاري التحميل...</p></div>';
        modal.show();

        let url = '';
        if (type === 'sale') {
            url = "{{ route('store.pos.sales.partial', ':id') }}".replace(':id', id);
        } else if (type === 'purchase') {
            url = "{{ route('store.purchases.show', ':id') }}".replace(':id', id);
        }

        if (url) {
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    body.innerHTML = html;
                })
                .catch(err => {
                    body.innerHTML = '<div class="alert alert-danger">خطأ أثناء تحميل البيانات</div>';
                });
        }
    }

    function printInvoiceContent() {
        const content = document.getElementById('movementDetailBody').innerHTML;
        const originalBody = document.body.innerHTML;
        
        // استخدام نافذة جديدة للطباعة للحفاظ على حالة الصفحة الحالية
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Print Invoice</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>body{direction:rtl; font-family: Cairo, sans-serif; padding:20px;} .no-print{display:none !important;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>
