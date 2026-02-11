<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>تقرير سجل المبيعات - {{ $arabicService->shape($store->name) }}</title>
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
            border-bottom: 3px solid #198754;
            padding-bottom: 20px;
            position: relative;
        }
        .header img.qr-code {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
            height: 80px;
        }
        .store-name {
            font-size: 26px;
            font-weight: bold;
            color: #198754;
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
            background-color: #198754;
            color: white;
            padding: 10px;
            border: 1px solid #198754;
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
            background-color: #e8f5e9;
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
            border-right: 5px solid #198754;
            padding-right: 12px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 10px;
            text-align: right;
        }
        
        /* تصميم بطاقة الفاتورة مع تمييز الهدف */
        .sale-detail-card {
            margin-bottom: 40px;
            border: 2px solid #ddd;
            page-break-inside: avoid;
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* تمييز الفاتورة المستهدفة بلون خفيف */
        .sale-detail-card:target {
            border-color: #198754;
            background-color: #e8f5e9;
            box-shadow: 0 0 15px rgba(25, 135, 84, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, #198754 0%, #28a745 100%);
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            text-align: right;
        }
        
        /* زر العودة للأعلى */
        .back-to-top {
            float: left;
            background-color: #fff;
            color: #198754 !important;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 10px;
            text-decoration: none;
        }
        
        .detail-info {
            padding: 12px;
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
        
        .text-primary { color: #198754; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .fw-bold { font-weight: bold; }
        .page-break { page-break-after: always; }
        
        /* روابط الفهرس */
        .invoice-link {
            text-decoration: none;
            color: #198754;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: #e8f5e9;
            display: inline-block;
        }
        .invoice-link:hover {
            background-color: #c8e6c9;
        }
        
        /* أيقونة السهم */
        .arrow-down { color: #198754; font-size: 14px; }
        .arrow-up { color: #198754; font-size: 12px; }
    </style>
</head>
<body>
    <div id="top" class="header">
        {{-- QR Code Injection (Report Summary) --}}
        @inject('qrService', 'App\Services\ZatcaQrService')
        @php
            $qrData = $qrService->generate(
                $store->name,
                $store->tax_number ?? '000000000000000',
                now()->toIso8601String(),
                $totals['sum_total'] ?? 0,
                // Assuming tax is 15% if not present, or just 0 for report summary
                // Since this is a report, strict ZATCA validation isn't applied like e-invoicing
                0 
            );
        @endphp
        @if($qrData)
            <img src="{{ $qrData }}" class="qr-code">
        @endif

        <div class="store-name">{{ $arabicService->shape($store->name) }}</div>
        <div class="report-title">{{ $arabicService->shape('سجل المبيعات التفصيلي') }}</div>
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

    <div class="section-title">{{ $arabicService->shape('فهرس الفواتير - اضغط على الرقم للانتقال') }}</div>
    
    <!-- Index Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th width="18%">{{ $arabicService->shape('الإجمالي') }}</th>
                <th width="12%">{{ $arabicService->shape('الحالة') }}</th>
                <th width="18%">{{ $arabicService->shape('التاريخ') }}</th>
                <th width="22%">{{ $arabicService->shape('العميل') }}</th>
                <th width="15%">{{ $arabicService->shape('المستخدم') }}</th>
                <th width="12%">{{ $arabicService->shape('رقم الفاتورة') }}</th>
                <th width="3%">{{ $arabicService->shape('م') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $s)
            @php
                $paid = $s->total - $s->due;
                $status = $s->due == 0 ? 'paid' : ($s->due >= $s->total ? 'unpaid' : 'partial');
            @endphp
            <tr>
                <td class="fw-bold">{{ number_format($s->total, 2) }}</td>
                <td>
                    <span class="badge {{ $status == 'paid' ? 'badge-paid' : ($status == 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                        @if($status == 'paid') {{ $arabicService->shape('مدفوع') }} @elseif($status == 'partial') {{ $arabicService->shape('جزئي') }} @else {{ $arabicService->shape('غير مدفوع') }} @endif
                    </span>
                </td>
                <td>{{ $s->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $arabicService->shape($s->contact->contact_name ?? 'عميل نقدي') }}</td>
                <td>{{ $arabicService->shape($s->user->name ?? '---') }}</td>
                <td>
                    <a href="#sale-{{ $s->id }}" class="invoice-link">
                        INV-{{ $s->id }} <span class="arrow-down">↓</span>
                    </a>
                </td>
                <td>{{ $loop->iteration }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">{{ $arabicService->shape('تفاصيل الفواتير والبنود') }}</div>
    
    @foreach($sales as $s)
    @php
        $paid = $s->total - $s->due;
        $status = $s->due == 0 ? 'paid' : ($s->due >= $s->total ? 'unpaid' : 'partial');
    @endphp
    <div id="sale-{{ $s->id }}" class="sale-detail-card">
        <div class="card-header">
            <a href="#top" class="back-to-top">
                <span class="arrow-up">↑</span> {{ $arabicService->shape('العودة للفهرس') }}
            </a>
            {{ $arabicService->shape('فاتورة رقم:') }} INV-{{ $s->id }}
        </div>
        
        <div class="detail-info">
            <table width="100%">
                <tr>
                    <td align="right" width="50%"><strong>{{ $arabicService->shape('التاريخ:') }}</strong> {{ $s->created_at->format('Y-m-d H:i') }}</td>
                    <td align="right" width="50%"><strong>{{ $arabicService->shape('العميل:') }}</strong> {{ $arabicService->shape($s->contact->contact_name ?? 'عميل نقدي') }}</td>
                </tr>
                <tr>
                    <td align="right"><strong>{{ $arabicService->shape('الإجمالي:') }}</strong> <span class="text-primary fw-bold">{{ number_format($s->total, 2) }}</span></td>
                    <td align="right"><strong>{{ $arabicService->shape('الحالة:') }}</strong> 
                        <span class="badge {{ $status == 'paid' ? 'badge-paid' : ($status == 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                            @if($status == 'paid') {{ $arabicService->shape('مدفوع') }} @elseif($status == 'partial') {{ $arabicService->shape('جزئي') }} @else {{ $arabicService->shape('غير مدفوع') }} @endif
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th width="20%">{{ $arabicService->shape('الإجمالي') }}</th>
                    <th width="15%">{{ $arabicService->shape('سعر البيع') }}</th>
                    <th width="15%">{{ $arabicService->shape('الكمية') }}</th>
                    <th width="45%">{{ $arabicService->shape('المنتج') }}</th>
                    <th width="5%">#</th>
                </tr>
            </thead>
            <tbody>
                @foreach($s->items as $item)
                <tr>
                    <td class="fw-bold">{{ number_format($item->total, 2) }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $arabicService->shape($item->product->name_ar ?? $item->product->name ?? '---') }}</td>
                    <td>{{ $loop->iteration }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

</body>
</html>
