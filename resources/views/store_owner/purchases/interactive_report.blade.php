<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير سجل المشتريات التفاعلي - {{ $store->name }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }
        .report-header {
            background-color: #fff;
            padding: 30px;
            border-bottom: 3px solid #0d6efd;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .kpi-box {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            margin-bottom: 15px;
        }
        .kpi-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .kpi-label {
            font-size: 0.9rem;
            color: #666;
        }
        .invoice-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .invoice-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .invoice-header {
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
        }
        .invoice-header.active {
            background-color: #f8f9fa;
        }
        .invoice-details {
            display: none;
            padding: 20px;
            border-top: 1px solid #eee;
            background-color: #fafafa;
        }
        .badge-paid { background-color: #d1e7dd; color: #0f5132; }
        .badge-partial { background-color: #fff3cd; color: #664d03; }
        .badge-unpaid { background-color: #f8d7da; color: #842029; }
        
        /* ألوان متبادلة (ستايل الشطرنج) لسهولة التفريق */
        .bg-alternating { 
            background-color: #f0f7ff !important; /* بحري خفيف */
            border-color: #cfe2ff;
        }
        .invoice-card.bg-alternating { border-color: #cfe2ff; }
        .invoice-card.bg-alternating .invoice-header { background-color: #f0f7ff !important; }
        .invoice-card.bg-alternating .invoice-details { background-color: #e9f2ff; }
        
        .btn-print {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        @media print {
            .btn-print, .no-print { display: none !important; }
            body { background: #fff; }
            .invoice-details { display: block !important; }
            .invoice-card { border: 1px solid #ccc; break-inside: avoid; }
            .report-header { padding: 10px; }
        }
    </style>
</head>
<body>

<div class="report-header text-center">
    <h2 class="fw-bold mb-1">{{ $store->name }}</h2>
    <h4 class="text-muted">{{ __('interactive_report_title') }}</h4>
    <div class="small text-secondary mt-2">
        <i class="fas fa-calendar-alt me-1"></i> {{ __('report_date') }} {{ now()->format('Y-m-d H:i') }}
    </div>
</div>

<div class="container mb-5">
    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="kpi-box">
                <div class="kpi-label">{{ __('invoices_count') }}</div>
                <div class="kpi-value text-dark">{{ $totals['count'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="kpi-box">
                <div class="kpi-label">{{ __('total_purchases') }}</div>
                <div class="kpi-value">{{ number_format($totals['sum_total'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="kpi-box">
                <div class="kpi-label">{{ __('total_paid') }}</div>
                <div class="kpi-value text-success">{{ number_format($totals['sum_paid'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="kpi-box">
                <div class="kpi-label">{{ __('total_due') }}</div>
                <div class="kpi-value text-danger">{{ number_format($totals['sum_due'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <p class="text-center text-muted mb-4 no-print">
        <i class="fas fa-info-circle me-1"></i> {{ __('click_invoice_details') }}
    </p>

    <!-- Invoices List -->
    <div class="accordion-reports">
        @foreach($purchases as $p)
        <div class="invoice-card {{ $loop->even ? 'bg-alternating' : '' }}" id="inv-{{ $p->id }}">
            <div class="invoice-header" onclick="toggleDetails({{ $p->id }})">
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-3">#{{ $p->invoice_number }}</span>
                    <span class="text-muted small d-none d-md-inline ms-2">{{ \Carbon\Carbon::parse($p->invoice_date)->format('Y-m-d') }}</span>
                    <span class="ms-3 badge {{ $p->payment_status == 'paid' ? 'badge-paid' : ($p->payment_status == 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                        @if($p->payment_status == 'paid') {{ __('paid_status') }} @elseif($p->payment_status == 'partial') {{ __('partial_status') }} @else {{ __('unpaid_status') }} @endif
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3 fw-bold text-primary">{{ number_format($p->grand_total, 2) }}</span>
                    <i class="fas fa-chevron-down text-muted toggle-icon-{{ $p->id }}"></i>
                </div>
            </div>
            <div class="invoice-details" id="details-{{ $p->id }}">
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">{{ __('supplier_label') }}:</small>
                        <strong>{{ $p->supplier->contact_name ?? '---' }}</strong>
                        @if($p->supplier && $p->supplier->company_name)
                            <div class="small text-muted">{{ $p->supplier->company_name }}</div>
                        @endif
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted d-block">{{ __('invoice_date_time') }}:</small>
                        <strong>{{ \Carbon\Carbon::parse($p->invoice_date)->format('Y-m-d H:i') }}</strong>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('table_product') }}</th>
                                <th class="text-center">{{ __('table_quantity') }}</th>
                                <th class="text-center">{{ __('table_price') }}</th>
                                <th class="text-end">{{ __('table_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($p->items as $item)
                            <tr class="{{ $loop->even ? 'bg-alternating' : '' }}">
                                <td>{{ $item->product->name ?? $item->product->name }}</td>
                                <td class="text-center">{{ $item->quantity }} {{ $item->unit->unit_name ?? '' }}</td>
                                <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->total_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">{{ __('net_total_label') }}</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($p->grand_total, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">{{ __('paid_label') }}:</td>
                                <td class="text-end text-success">{{ number_format($p->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">{{ __('remaining_label') }}:</td>
                                <td class="text-end text-danger">{{ number_format($p->grand_total - $p->paid_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($p->notes)
                    <div class="mt-2 p-2 bg-light border-start border-primary small">
                        <strong>{{ __('notes_label') }}:</strong> {{ $p->notes }}
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<button class="btn btn-primary btn-print shadow-lg px-4 py-2" onclick="window.print()">
    <i class="fas fa-print me-2"></i> {{ __('print_full_report') }}
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleDetails(id) {
        const details = document.getElementById('details-' + id);
        const icon = document.querySelector('.toggle-icon-' + id);
        const header = details.previousElementSibling;
        
        if (details.style.display === 'block') {
            details.style.display = 'none';
            icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            header.classList.remove('active');
        } else {
            details.style.display = 'block';
            icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            header.classList.add('active');
        }
    }
</script>

</body>
</html>
