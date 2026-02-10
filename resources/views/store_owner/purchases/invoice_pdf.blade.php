<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $arabicService->shape('فاتورة شراء') }} #{{ $purchase->invoice_number }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            direction: rtl; 
            text-align: right; 
            font-size: 12px; 
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #ddd; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header h1 { margin: 5px 0; font-size: 24px; color: #333; }
        .store-info { font-size: 12px; color: #555; margin-bottom: 10px; }
        .meta-table { width: 100%; margin-bottom: 20px; direction: rtl; }
        .meta-table td { padding: 5px; vertical-align: top; text-align: right; }
        
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            font-size: 12px; 
            direction: rtl; 
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: center; 
        }
        .table td.text-start { text-align: right !important; }
        
        .table th { background-color: #f8f9fa; color: #333; font-weight: bold; }
        
        .total-area { 
            width: 40%; 
            margin-right: auto; 
            margin-left: 0; 
            text-align: left; 
        }
        .total-row { 
            padding: 5px 0; 
            border-bottom: 1px dashed #eee; 
        }
        .total-row span:first-child { float: right; font-weight: bold; }
        .total-row span:last-child { float: left; }
        .clearfix { clear: both; }

        .total-row.final { 
            border-top: 2px solid #333; 
            border-bottom: none; 
            font-weight: bold; 
            font-size: 16px; 
            margin-top: 10px; 
            padding-top: 10px; 
        }

        .footer { 
            margin-top: 50px; 
            text-align: center; 
            font-size: 10px; 
            color: #777; 
            border-top: 1px solid #eee; 
            padding-top: 10px; 
        }
    </style>
</head>
<body>
    @php
        $base64Image = function($path) {
            if (!$path) return null;
            $clean = str_replace(['public/', 'storage/'], '', $path);
            $possiblePaths = [
                storage_path('app/public/' . $clean),
                public_path('storage/' . $clean),
                public_path($clean)
            ];
            foreach ($possiblePaths as $fullPath) {
                if (file_exists($fullPath)) {
                    $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($fullPath);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
            return null;
        };

        $logo = $base64Image($store->logo_path ?? $store->logo);
    @endphp

    <div class="header">
        @if($logo)
            <img src="{{ $logo }}" width="100" style="max-height: 80px;">
        @endif
        <h1>{{ $arabicService->shape($store->name) }}</h1>
        <div class="store-info">
            {{ $arabicService->shape($store->address) }} <br>
            @if($store->phone_number) {{ $arabicService->shape('هاتف:') }} {{ $store->phone_number }} @endif <br>
            @if($store->tax_number) {{ $arabicService->shape('الرقم الضريبي:') }} {{ $store->tax_number }} @endif
        </div>
        <h2>{{ $arabicService->shape('فاتورة مشتريات') }}</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td width="50%">
                <strong>{{ $arabicService->shape('رقم الفاتورة:') }}</strong> #{{ $purchase->invoice_number }}<br>
                <strong>{{ $arabicService->shape('التاريخ:') }}</strong> {{ $purchase->invoice_date->format('Y-m-d') }}<br>
                <strong>{{ $arabicService->shape('بواسطة:') }}</strong> {{ $arabicService->shape($purchase->user->name ?? '---') }}
            </td>
            <td width="50%">
                <strong>{{ $arabicService->shape('المورد:') }}</strong> {{ $arabicService->shape($purchase->supplier ? ($purchase->supplier->contact_name ?? $purchase->supplier->company_name) : 'مورد عام') }}<br>
                @if($purchase->supplier && $purchase->supplier->phone) <strong>{{ $arabicService->shape('الهاتف:') }}</strong> {{ $purchase->supplier->phone }}<br> @endif
                @if($purchase->supplier && $purchase->supplier->tax_number) <strong>{{ $arabicService->shape('الرقم الضريبي:') }}</strong> {{ $purchase->supplier->tax_number }} @endif
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="20%">{{ $arabicService->shape('الإجمالي') }}</th>
                <th width="15%">{{ $arabicService->shape('السعر') }}</th>
                <th width="15%">{{ $arabicService->shape('الكمية') }}</th>
                <th width="45%">{{ $arabicService->shape('المنتج') }}</th>
                <th width="5%">#</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $index => $item)
            <tr>
                <td>{{ number_format($item->total_cost, 2) }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ floatval($item->quantity) }}</td>
                <td class="text-start">
                    {{ $arabicService->shape($item->product->name_ar ?? 'منتج محذوف') }}
                    @if($item->unit) <small>({{ $arabicService->shape($item->unit->unit_name) }})</small> @endif
                </td>
                <td>{{ $index + 1 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-area">
        <div class="total-row">
            <span>{{ $arabicService->shape('المجموع الفرعي:') }}</span>
            <span>{{ number_format($purchase->sub_total, 2) }}</span>
            <div class="clearfix"></div>
        </div>
        @if($purchase->discount_amount > 0)
        <div class="total-row" style="color: #c0392b;">
            <span>{{ $arabicService->shape('الخصم:') }}</span>
            <span>-{{ number_format($purchase->discount_amount, 2) }}</span>
            <div class="clearfix"></div>
        </div>
        @endif
        <div class="total-row final">
            <span>{{ $arabicService->shape('الإجمالي النهائي:') }}</span>
            <span>{{ number_format($purchase->grand_total, 2) }} {{ $store->currency ?? 'SAR' }}</span>
            <div class="clearfix"></div>
        </div>
        <div class="total-row">
            <span>{{ $arabicService->shape('المدفوع:') }}</span>
            <span>{{ number_format($purchase->paid_amount, 2) }}</span>
            <div class="clearfix"></div>
        </div>
        @php $due = $purchase->grand_total - $purchase->paid_amount; @endphp
        @if($due > 0)
        <div class="total-row" style="color: #d35400;">
            <span>{{ $arabicService->shape('المتبقي (آجل):') }}</span>
            <span>{{ number_format($due, 2) }}</span>
            <div class="clearfix"></div>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>{{ $store->website ?? '' }}</p>
    </div>
</body>
</html>
