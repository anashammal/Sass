<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>تقرير مرتجعات - {{ $sale->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; direction: rtl; text-align: right; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #333; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; color: #555; }
        .value { color: #000; font-weight: bold; }
        
        .section-title { font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; color: #198754; border-bottom: 1px solid #198754; padding-bottom: 5px; }
        .items-table, .returns-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px; }
        .items-table th, .items-table td, .returns-table th, .returns-table td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        .items-table th { background-color: #f8f9fa; }
        .returns-table th { background-color: #fce4e4; color: #dc3545; }
        
        .totals-box { border: 1px solid #ddd; padding: 10px; margin-top: 20px; background-color: #f9f9f9; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .final-total { font-size: 16px; font-weight: bold; color: #198754; margin-top: 5px; border-top: 1px solid #ddd; padding-top: 5px; }
        
        .footer { text-align: center; font-size: 10px; color: #777; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $arabicService->shape('تفاصيل المرتجعات') }}</div>
        <div>{{ $arabicService->shape($store->name) }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td><span class="label">{{ $arabicService->shape('رقم الفاتورة:') }}</span> <span class="value">INV-{{ $sale->id }}</span></td>
            <td><span class="label">{{ $arabicService->shape('العميل:') }}</span> <span class="value">{{ $arabicService->shape(optional($sale->contact)->contact_name ?? '-') }}</span></td>
            <td><span class="label">{{ $arabicService->shape('التاريخ:') }}</span> <span class="value">{{ $sale->created_at->format('Y-m-d H:i') }}</span></td>
        </tr>
    </table>

    <!-- الأصناف المرتجعة -->
    @if($sale->returns->count() > 0)
    <div class="section-title">{{ $arabicService->shape('الأصناف المرتجعة') }}</div>
    <table class="returns-table">
        <thead>
            <tr>
                <th>{{ $arabicService->shape('المنتج') }}</th>
                <th>{{ $arabicService->shape('الوحدة') }}</th>
                <th>{{ $arabicService->shape('الكمية') }}</th>
                <th>{{ $arabicService->shape('السعر') }}</th>
                <th>{{ $arabicService->shape('الإجمالي') }}</th>
                <th>{{ $arabicService->shape('السبب') }}</th>
                <th>{{ $arabicService->shape('بواسطة') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->returns as $ret)
            <tr>
                <td>{{ $arabicService->shape(optional($ret->product)->name_ar ?? '---') }}</td>
                <td>{{ $arabicService->shape(optional($ret->unit)->unit_name ?? 'قطعة') }}</td>
                <td style="color: #dc3545;">{{ number_format($ret->quantity, 2) }}</td>
                <td>{{ number_format($ret->price, 2) }}</td>
                <td style="color: #dc3545;">{{ number_format($ret->total, 2) }}</td>
                <td>{{ $arabicService->shape($ret->reason ?? '-') }}</td>
                <td>{{ $arabicService->shape(optional($ret->user)->name ?? '-') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- الأصناف المتبقية -->
    <div class="section-title">{{ $arabicService->shape('الأصناف الحالية (بعد الإرجاع)') }}</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ $arabicService->shape('المنتج') }}</th>
                <th>{{ $arabicService->shape('الوحدة') }}</th>
                <th>{{ $arabicService->shape('الكمية') }}</th>
                <th>{{ $arabicService->shape('السعر') }}</th>
                <th>{{ $arabicService->shape('الإجمالي') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $arabicService->shape(optional($item->product)->name_ar ?? '---') }}</td>
                <td>{{ $arabicService->shape(optional($item->unit)->unit_name ?? 'قطعة') }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-box">
        <table style="width: 100%">
            <tr>
                <td align="right">{{ $arabicService->shape('الإجمالي الأصلي:') }}</td>
                <td align="left">{{ number_format($sale->total + $sale->total_returns, 2) }}</td>
            </tr>
            <tr>
                <td align="right" style="color: #dc3545;">{{ $arabicService->shape('إجمالي المرتجع:') }}</td>
                <td align="left" style="color: #dc3545;">-{{ number_format($sale->total_returns, 2) }}</td>
            </tr>
            <tr>
                <td align="right" style="font-weight: bold; color: #198754;">{{ $arabicService->shape('الصافي الحالي:') }}</td>
                <td align="left" style="font-weight: bold; color: #198754;">{{ number_format($sale->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ $arabicService->shape('تم الطباعة بواسطة نظام المتجر في') }} {{ now()->format('Y-m-d H:i:s') }}
        <br>
        {{ $arabicService->shape('المستخدم:') }} {{ $arabicService->shape(Auth::user()->name) }}
    </div>
</body>
</html>
