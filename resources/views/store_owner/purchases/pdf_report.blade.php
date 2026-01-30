<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>تقرير سجل المشتريات - {{ $arabicService->shape($store->name) }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
            unicode-bidi: bidi-override;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
        }
        .store-name {
            font-size: 26px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 18px;
            color: #555;
            margin-bottom: 5px;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #f8f9fa;
        }
        .summary-box td {
            border: 1px solid #dee2e6;
            padding: 15px;
            text-align: center;
        }
        .kpi-label {
            font-size: 10px;
            color: #777;
            display: block;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .main-table th {
            background-color: #0d6efd;
            color: white;
            padding: 10px;
            border: 1px solid #0d6efd;
            font-size: 12px;
            text-align: center;
        }
        .main-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
            font-size: 11px;
        }
        .main-table tr:nth-child(even) {
            background-color: #f1f7ff;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-paid { background-color: #d1e7dd; color: #0f5132; }
        .badge-partial { background-color: #fff3cd; color: #664d03; }
        .badge-unpaid { background-color: #f8d7da; color: #842029; }
        
        .section-title {
            border-right: 5px solid #0d6efd;
            padding-right: 12px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 10px;
            text-align: right;
        }
        
        .purchase-detail-card {
            margin-bottom: 40px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
        .card-header {
            background-color: #333;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            text-align: right;
        }
        .detail-info {
            padding: 10px;
            background-color: #fcfcfc;
            border-bottom: 1px solid #eee;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            background-color: #f0f0f0;
            color: #333;
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
            text-align: center;
        }
        .items-table td {
            border: 1px solid #eee;
            padding: 8px;
            font-size: 11px;
            text-align: center;
        }
        
        .text-primary { color: #0d6efd; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .fw-bold { font-weight: bold; }
        .page-break { page-break-after: always; }
        
        a { text-decoration: none; color: #0d6efd; }
    </style>
</head>
<body>
    <div id="top" class="header">
        <div class="store-name">{{ $arabicService->shape($store->name) }}</div>
        <div class="report-title">{{ $arabicService->shape('سجل المشتريات التفصيلي') }}</div>
        <div style="font-size: 10px; color: #888;">{{ $arabicService->shape('وقت التقرير:') }} {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <!-- Summary KPI Box -->
    <table class="summary-box">
        <tr>
            <td width="25%">
                <span class="kpi-label">{{ $arabicService->shape('مبلغ المتبقي') }}</span>
                <span class="kpi-value text-danger">{{ number_format($totals['sum_due'], 2) }}</span>
            </td>
            <td width="25%">
                <span class="kpi-label">{{ $arabicService->shape('مبلغ المدفوع') }}</span>
                <span class="kpi-value text-success">{{ number_format($totals['sum_paid'], 2) }}</span>
            </td>
            <td width="25%">
                <span class="kpi-label">{{ $arabicService->shape('مبلغ الإجمالي') }}</span>
                <span class="kpi-value text-primary">{{ number_format($totals['sum_total'], 2) }}</span>
            </td>
            <td width="25%">
                <span class="kpi-label">{{ $arabicService->shape('عدد الفواتير') }}</span>
                <span class="kpi-value">{{ $totals['count'] }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">{{ $arabicService->shape('فهرس الفواتير (نظرة سريعة)') }}</div>
    
    <!-- Index Table with Reversed Columns for LTR Engine looking like RTL -->
    <table class="main-table">
        <thead>
            <tr>
                <th width="20%">{{ $arabicService->shape('الإجمالي') }}</th>
                <th width="15%">{{ $arabicService->shape('الحالة') }}</th>
                <th width="20%">{{ $arabicService->shape('التاريخ') }}</th>
                <th width="25%">{{ $arabicService->shape('المورد') }}</th>
                <th width="15%">{{ $arabicService->shape('رقم الفاتورة') }}</th>
                <th width="5%">{{ $arabicService->shape('م') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $p)
            <tr>
                <td class="fw-bold">{{ number_format($p->grand_total, 2) }}</td>
                <td>
                    <span class="badge {{ $p->payment_status == 'paid' ? 'badge-paid' : ($p->payment_status == 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                        @if($p->payment_status == 'paid') {{ $arabicService->shape('مدفوع') }} @elseif($p->payment_status == 'partial') {{ $arabicService->shape('جزئي') }} @else {{ $arabicService->shape('غير مدفوع') }} @endif
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($p->invoice_date)->format('Y-m-d') }}</td>
                <td>{{ $arabicService->shape($p->supplier->contact_name ?? '---') }}</td>
                <td>
                    <a href="#purchase-{{ $p->id }}" class="fw-bold">
                        {{ $p->invoice_number }} ↓
                    </a>
                </td>
                <td>{{ $loop->iteration }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">{{ $arabicService->shape('تفاصيل الفواتير والبنود') }}</div>
    
    @foreach($purchases as $p)
    <div id="purchase-{{ $p->id }}" class="purchase-detail-card">
        <div class="card-header">
            <span style="float: left;"><a href="#top" style="color: #fff; font-size: 8px;">{{ $arabicService->shape('العودة') }} ↑</a></span>
            {{ $arabicService->shape('تفاصيل فاتورة رقم:') }} {{ $p->invoice_number }}
        </div>
        
        <div class="detail-info">
            <table width="100%">
                <tr>
                    <td align="right" width="50%"><strong>{{ $arabicService->shape('تاريخ الفاتورة:') }}</strong> {{ $p->invoice_date }}</td>
                    <td align="right" width="50%"><strong>{{ $arabicService->shape('المورد:') }}</strong> {{ $arabicService->shape($p->supplier->contact_name ?? '---') }}</td>
                </tr>
            </table>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th width="20%">{{ $arabicService->shape('الإجمالي') }}</th>
                    <th width="15%">{{ $arabicService->shape('سعر الوحدة') }}</th>
                    <th width="15%">{{ $arabicService->shape('الكمية') }}</th>
                    <th width="45%">{{ $arabicService->shape('المنتج') }}</th>
                    <th width="5%">#</th>
                </tr>
            </thead>
            <tbody>
                @foreach($p->items as $item)
                <tr>
                    <td class="fw-bold">{{ number_format($item->total_cost, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $arabicService->shape($item->product->name_ar ?? $item->product->name) }}</td>
                    <td>{{ $loop->iteration }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

</body>
</html>
