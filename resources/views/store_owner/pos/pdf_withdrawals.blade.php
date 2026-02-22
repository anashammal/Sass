<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('owner_withdrawals_report_title') }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format('truetype');
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
        }
        .store-info {
            margin-bottom: 10px;
        }
        .summary-box {
            display: inline-block;
            width: 45%;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .summary-box.red { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .summary-box.green { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: left;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $arabicService->shape(__('owner_withdrawals_report_title')) }}</h2>
        <div class="store-info">
            <strong>{{ $arabicService->shape($store->name) }}</strong><br>
            {{ $arabicService->shape(__('report_date') . now()->format('Y-m-d')) }}
        </div>
    </div>

    <div style="text-align: center;">
        <div class="summary-box red">
            <h4 style="margin:0 0 5px 0">{{ $arabicService->shape(__('total_cost_capital_purchase_price')) }}</h4>
            <h2 style="margin:0">{{ number_format($totalCost, 2) }}</h2>
        </div>
        <div class="summary-box green">
            <h4 style="margin:0 0 5px 0">{{ $arabicService->shape(__('total_selling_price_display')) }}</h4>
            <h2 style="margin:0">{{ number_format($totalSale, 2) }}</h2>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ $arabicService->shape(__('withdrawal_number')) }}</th>
                <th>{{ $arabicService->shape(__('items_count_lbl')) }}</th>
                <th>{{ $arabicService->shape(__('total_cost_lbl')) }}</th>
                <th>{{ $arabicService->shape(__('selling_price_lbl')) }}</th>
                <th>{{ $arabicService->shape(__('date_label')) }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdrawals as $sale)
            <tr>
                <td style="font-weight: bold;">{{ 'SOV-' . str_pad($sale->withdrawal_number, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $sale->items_count }}</td>
                <td style="color: red; font-weight: bold;">{{ number_format($sale->calculated_cost, 2) }}</td>
                <td style="color: green; font-weight: bold;">{{ number_format($sale->total, 2) }}</td>
                <td>{{ $sale->created_at->format('Y-m-d h:i A') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">
                    {{ $arabicService->shape(__('no_withdrawals_recorded')) }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $arabicService->shape(__('report_generated_by_smart_store')) }} - {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
