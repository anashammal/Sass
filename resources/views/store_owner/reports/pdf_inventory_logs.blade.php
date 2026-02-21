<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>سجل تعديلات المخزون</title>
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
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
        }
        .bg-info { background-color: #d1ecf1; }
        .bg-danger { background-color: #f8d7da; }
        .bg-warning { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $arabicService->shape(__('inventory_logs_title')) }}</h2>
        <div class="store-info">
            <strong>{{ $arabicService->shape($store->name) }}</strong><br>
            {{ $arabicService->shape(__('report_date') . now()->format('Y-m-d')) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 20%">{{ $arabicService->shape(__('product_label')) }}</th>
                <th style="width: 15%">{{ $arabicService->shape(__('barcode')) }}</th>
                <th style="width: 12%">{{ $arabicService->shape(__('date_label')) }}</th>
                <th style="width: 10%">{{ $arabicService->shape(__('action_label')) }}</th>
                <th style="width: 10%">{{ $arabicService->shape(__('before_label')) }}</th>
                <th style="width: 10%">{{ $arabicService->shape(__('after_label')) }}</th>
                <th style="width: 10%">{{ $arabicService->shape(__('by_user')) }}</th>
                <th style="width: 8%">{{ $arabicService->shape(__('action_reason')) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">{{ $arabicService->shape($log->product->name ?? __('deleted_product')) }}</td>
                <td>{{ $log->product->sku ?? '---' }}</td>
                <td>{{ $log->created_at->format('Y-m-d') }}</td>
                <td>
                    @switch($log->action)
                        @case('manual_adjustment') {{ $arabicService->shape(__('manual_adjustment')) }} @break
                        @case('dispose') {{ $arabicService->shape(__('dispose_label')) }} @break
                        @case('extend_expiry') {{ $arabicService->shape(__('extend_label')) }} @break
                        @default {{ $log->action }}
                    @endswitch
                </td>
                <td>
                    @if($log->action == 'extend_expiry')
                        {{ $log->old_date ?? '---' }}
                    @else
                        {{ floatval($log->old_quantity) }}
                    @endif
                </td>
                <td>
                    @if($log->action == 'extend_expiry')
                        {{ $log->new_date ?? '---' }}
                    @else
                        {{ floatval($log->new_quantity) }}
                    @endif
                </td>
                <td>{{ $arabicService->shape($log->user->name ?? 'System') }}</td>
                <td>
                    @if($log->reason == 'تعديل مخزون سريع')
                        {{ $arabicService->shape(__('reason_quick_adjustment')) }}
                    @elseif($log->reason == 'تعديل مخزون سريع من نقطة البيع')
                        {{ $arabicService->shape(__('reason_quick_adjustment_pos')) }}
                    @else
                        {{ $arabicService->shape($log->reason ?? '---') }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ $arabicService->shape(__('report_generated_by_smart_store')) }} - {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
