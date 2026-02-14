<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $arabicService->shape('التقرير اليومي للمتجر') }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url({{ storage_path('fonts/DejaVuSans.ttf') }}) format('truetype');
        }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            direction: rtl; 
            text-align: right; 
            font-size: 11px; 
            color: #333;
            line-height: 1.5;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #3498db; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header h1 { margin: 5px 0; font-size: 20px; color: #2c3e50; }
        .date-info { font-size: 12px; color: #7f8c8d; margin-bottom: 5px; }
        
        .section-title { 
            background-color: #f2f2f2; 
            padding: 8px; 
            margin-top: 20px; 
            margin-bottom: 10px; 
            border-right: 4px solid #3498db;
            font-weight: bold;
            font-size: 13px;
        }

        .summary-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            border: 1px solid #eee;
            padding: 10px;
            text-align: center;
            width: 30%;
        }
        .summary-card.sales { border-top: 3px solid #27ae60; }
        .summary-card.expenses { border-top: 3px solid #e74c3c; }
        .summary-card.profit { border-top: 3px solid #f39c12; }
        .summary-label { font-size: 10px; color: #7f8c8d; display: block; }
        .summary-value { font-size: 16px; font-weight: bold; color: #2c3e50; }

        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        .table th, .table td { 
            border: 1px solid #eee; 
            padding: 6px; 
            text-align: center; 
        }
        .table th { background-color: #f8f9fa; color: #2c3e50; }
        .table td.text-start { text-align: right !important; }
        
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            color: #fff;
        }
        .badge-danger { background-color: #e74c3c; }
        .badge-warning { background-color: #f39c12; }
        
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 9px; 
            color: #bdc3c7; 
            border-top: 1px solid #eee; 
            padding-top: 10px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $arabicService->shape($store->name) }}</h1>
        <div class="date-info">{{ $arabicService->shape('تقرير يوم:') }} {{ $date }}</div>
        <div class="date-info">{{ $arabicService->shape('وقت التوليد:') }} {{ now()->format('H:i') }}</div>
    </div>

    <div class="section-title">{{ $arabicService->shape('الملخص المالي (لليوم السابق)') }}</div>
    <table class="summary-grid">
        <tr>
            <td class="summary-card sales">
                <span class="summary-label">{{ $arabicService->shape('إجمالي المبيعات') }}</span>
                <span class="summary-value">{{ number_format($financials['sales'], 2) }}</span>
            </td>
            <td class="summary-card expenses">
                <span class="summary-label">{{ $arabicService->shape('إجمالي المصاريف') }}</span>
                <span class="summary-value">{{ number_format($financials['expenses'], 2) }}</span>
            </td>
            <td class="summary-card profit">
                <span class="summary-label">{{ $arabicService->shape('صافي الربح') }}</span>
                <span class="summary-value">{{ number_format($financials['profit'], 2) }}</span>
            </td>
        </tr>
    </table>

    @if($expired->count() > 0)
    <div class="section-title">{{ $arabicService->shape('منتجات منتهية الصلاحية') }}</div>
    <table class="table">
        <thead>
            <tr>
                <th width="20%">{{ $arabicService->shape('تاريخ الانتهاء') }}</th>
                <th width="15%">{{ $arabicService->shape('الكمية') }}</th>
                <th width="65%">{{ $arabicService->shape('المنتج') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expired as $b)
            <tr>
                <td>{{ $b->expiry_date }}</td>
                <td><span class="badge badge-danger">{{ floatval($b->quantity) }}</span></td>
                <td class="text-start">{{ $arabicService->shape($b->product->name) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($lowStock->count() > 0)
    <div class="section-title">{{ $arabicService->shape('تنبيهات انخفاض المخزون') }}</div>
    <table class="table">
        <thead>
            <tr>
                <th width="20%">{{ $arabicService->shape('المخزون الحالي') }}</th>
                <th width="20%">{{ $arabicService->shape('حد التنبيه') }}</th>
                <th width="60%">{{ $arabicService->shape('المنتج') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStock as $p)
            <tr>
                <td><span class="badge badge-warning">{{ floatval($p->current_stock) }}</span></td>
                <td>{{ floatval($p->alert_quantity) }}</td>
                <td class="text-start">{{ $arabicService->shape($p->name) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        {{ $arabicService->shape('تم توليد هذا التقرير آلياً بواسطة نظام TechSys') }}
    </div>
</body>
</html>
