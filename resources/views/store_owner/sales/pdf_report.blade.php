<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('sales_report_title') }} - {{ $store->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
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
            {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0;
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
            border-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 5px solid #198754;
            padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 12px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 10px;
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
        }
        
        .sale-detail-card {
            margin-bottom: 40px;
            border: 2px solid #ddd;
            page-break-inside: avoid;
            border-radius: 8px;
            overflow: hidden;
        }
        
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
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
        }
        
        .back-to-top {
            float: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }};
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
                0 
            );
        @endphp
        @if($qrData)
            <img src="{{ $qrData }}" class="qr-code">
        @endif

        <div class="store-name">
            {{ $arabicService->shape($store->name) }}
        </div>
        <div class="report-title">
            @if(app()->getLocale() == 'ar')
                {{ $arabicService->shape(__('detailed_sales_report')) }}
            @else
                {{ __('detailed_sales_report') }}
            @endif
        </div>
        <div style="font-size: 10px; color: #888;">
            @if(app()->getLocale() == 'ar')
                {{ $arabicService->shape(__('report_time')) }}: {{ now()->format('Y-m-d H:i') }}
            @else
                {{ __('report_time') }}: {{ now()->format('Y-m-d H:i') }}
            @endif
        </div>
    </div>

    <!-- Summary KPI Box -->
    <table class="summary-box">
        <tr>
            <td width="25%">
                <span class="kpi-label">
                    @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('remaining_amount')) }} @else {{ __('remaining_amount') }} @endif
                </span>
                <span class="kpi-value text-danger">{{ number_format($totals['sum_due'], 2) }}</span>
            </td>
            <td width="25%">
                <span class="kpi-label">
                    @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('paid_amount')) }} @else {{ __('paid_amount') }} @endif
                </span>
                <span class="kpi-value text-success">{{ number_format($totals['sum_paid'], 2) }}</span>
            </td>
            <td width="25%">
                <span class="kpi-label">
                    @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('total_amount')) }} @else {{ __('total_amount') }} @endif
                </span>
                <span class="kpi-value text-primary">{{ number_format($totals['sum_total'], 2) }}</span>
            </td>
            <td width="25%">
                <span class="kpi-label">
                    @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('invoices_count')) }} @else {{ __('invoices_count') }} @endif
                </span>
                <span class="kpi-value">{{ $totals['count'] }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">
        @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('invoices_index_click_to_go')) }} @else {{ __('invoices_index_click_to_go') }} @endif
    </div>
    
    <!-- Index Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th width="18%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('total')) }} @else {{ __('total') }} @endif</th>
                <th width="12%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('status')) }} @else {{ __('status') }} @endif</th>
                <th width="18%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('date')) }} @else {{ __('date') }} @endif</th>
                <th width="22%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('customer')) }} @else {{ __('customer') }} @endif</th>
                <th width="15%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('user')) }} @else {{ __('user') }} @endif</th>
                <th width="12%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('invoice_number')) }} @else {{ __('invoice_number') }} @endif</th>
                <th width="3%">#</th>
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
                        @if($status == 'paid') 
                            @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('paid')) }} @else {{ __('paid') }} @endif
                        @elseif($status == 'partial') 
                            @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('partial')) }} @else {{ __('partial') }} @endif
                        @else 
                            @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('unpaid')) }} @else {{ __('unpaid') }} @endif
                        @endif
                    </span>
                </td>
                <td>{{ $s->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    {{ $arabicService->shape($s->contact->contact_name ?? __('cash_customer')) }}
                </td>
                <td>
                    {{ $arabicService->shape($s->user->name ?? '---') }}
                </td>
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

    <div class="section-title">
        @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('invoices_and_items_details')) }} @else {{ __('invoices_and_items_details') }} @endif
    </div>
    
    @foreach($sales as $s)
    @php
        $paid = $s->total - $s->due;
        $status = $s->due == 0 ? 'paid' : ($s->due >= $s->total ? 'unpaid' : 'partial');
    @endphp
    <div id="sale-{{ $s->id }}" class="sale-detail-card">
        <div class="card-header">
            <a href="#top" class="back-to-top">
                <span class="arrow-up">↑</span> 
                @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('back_to_index')) }} @else {{ __('back_to_index') }} @endif
            </a>
            @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('invoice_number')) }} @else {{ __('invoice_number') }} @endif: INV-{{ $s->id }}
        </div>
        
        <div class="detail-info">
            <table width="100%">
                <tr>
                    <td align="{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" width="50%">
                        <strong>@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('date')) }}: @else {{ __('date') }}: @endif</strong> {{ $s->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td align="{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" width="50%">
                        <strong>@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('customer')) }}: @else {{ __('customer') }}: @endif</strong> 
                        {{ $arabicService->shape($s->contact->contact_name ?? __('cash_customer')) }}
                    </td>
                </tr>
                <tr>
                    <td align="{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <strong>@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('total')) }}: @else {{ __('total') }}: @endif</strong> <span class="text-primary fw-bold">{{ number_format($s->total, 2) }}</span>
                    </td>
                    <td align="{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <strong>@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('status')) }}: @else {{ __('status') }}: @endif</strong> 
                        <span class="badge {{ $status == 'paid' ? 'badge-paid' : ($status == 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                            @if($status == 'paid') 
                                @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('paid')) }} @else {{ __('paid') }} @endif
                            @elseif($status == 'partial') 
                                @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('partial')) }} @else {{ __('partial') }} @endif
                            @else 
                                @if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('unpaid')) }} @else {{ __('unpaid') }} @endif
                            @endif
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th width="20%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('total')) }} @else {{ __('total') }} @endif</th>
                    <th width="15%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('selling_price')) }} @else {{ __('selling_price') }} @endif</th>
                    <th width="15%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('quantity')) }} @else {{ __('quantity') }} @endif</th>
                    <th width="45%">@if(app()->getLocale() == 'ar') {{ $arabicService->shape(__('product')) }} @else {{ __('product') }} @endif</th>
                    <th width="5%">#</th>
                </tr>
            </thead>
            <tbody>
                @foreach($s->items as $item)
                <tr>
                    <td class="fw-bold">{{ number_format($item->total, 2) }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>
                        {{ $arabicService->shape($item->product->name ?? '---') }}
                    </td>
                    <td>{{ $loop->iteration }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

</body>
</html>
