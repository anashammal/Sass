<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير سجل المشتريات - {{ $store->name }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url(http://tweb.com/fonts/DejaVuSans.ttf) format('truetype');
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .store-name {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }
        .report-title {
            font-size: 14px;
            margin-top: 5px;
            color: #666;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        .summary-box td {
            padding: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: center;
        }
        .table th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #0d6efd; }
        .fw-bold { font-weight: bold; }
        .page-break { page-break-after: always; }
        
        /* Details Style */
        .purchase-detail-container {
            margin-top: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #fff;
        }
        .purchase-detail-container.even {
            background-color: #f0f7ff; /* بحري خفيف مشابه للتقرير التفاعلي */
            border-color: #cfe2ff;
        }
        .detail-header {
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            border: 1px solid #eee;
            padding: 5px;
            font-size: 10px;
        }
        .items-table th { background-color: #f1f1f1 !important; }
        .items-table tr.even td { background-color: #f0f7ff !important; }
        
        /* تأثير الشطرنج داخل الـ PDF */
        .purchase-detail-container.even {
            background-color: #f0f7ff !important;
            border-color: #cfe2ff !important;
        }
        /* تلوين الخلايا الفردية يضمن ظهور اللون في أغلب محركات الـ PDF */
        .purchase-detail-container.even .items-table td, 
        .purchase-detail-container.even .items-table th,
        .purchase-detail-container.even table td,
        .purchase-detail-container.even table th,
        .items-table tr.even td {
            background-color: #f0f7ff !important; 
        }
        
        .items-table tr.even td {
            background-color: #f0f7ff !important;
        }
        
        a { text-decoration: none; color: #0d6efd; }
    </style>
</head>
<body>
    <div id="top" class="header">
        <div class="store-name">{{ $store->name }}</div>
        <div class="report-title">سجل المشتريات التفصيلي</div>
        <div style="font-size: 9px; color: #666; margin-top: 5px;">تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <table class="summary-box">
        <tr>
            <td width="25%"><strong>عدد الفواتير:</strong> {{ $totals['count'] }}</td>
            <td width="25%"><strong>إجمالي المشتريات:</strong> <span class="text-primary">{{ number_format($totals['sum_total'], 2) }}</span></td>
            <td width="25%"><strong>إجمالي المدفوع:</strong> <span class="text-success">{{ number_format($totals['sum_paid'], 2) }}</span></td>
            <td width="25%"><strong>إجمالي المتبقي:</strong> <span class="text-danger">{{ number_format($totals['sum_due'], 2) }}</span></td>
        </tr>
    </table>

    <h3 style="border-bottom: 1px solid #eee; padding-bottom: 5px;">فهرس الفواتير (اضغط على الفاتورة للانتقال للتفاصيل)</h3>
    <table class="table">
        <thead>
            <tr>
                <th>م</th>
                <th>رقم الفاتورة</th>
                <th>المورد</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>الإجمالي</th>
                <th>المتبقي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <a href="#purchase-{{ $p->id }}" class="fw-bold" style="background-color: #f0f7ff; padding: 2px 5px; border-radius: 3px;">
                        {{ $p->invoice_number }} ↓
                    </a>
                </td>
                <td>{{ $p->supplier->contact_name ?? '---' }}</td>
                <td>{{ \Carbon\Carbon::parse($p->invoice_date)->format('Y-m-d') }}</td>
                <td>
                    @if($p->payment_status == 'paid') مدفوع
                    @elseif($p->payment_status == 'partial') جزئي
                    @else غير مدفوع @endif
                </td>
                <td class="fw-bold">{{ number_format($p->grand_total, 2) }}</td>
                <td class="text-danger">{{ number_format($p->grand_total - $p->paid_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <h3 style="border-bottom: 2px solid #333; padding-bottom: 10px;">تفاصيل المشتريات</h3>
    @foreach($purchases as $p)
    <div id="purchase-{{ $p->id }}" class="purchase-detail-container {{ $loop->even ? 'even' : '' }}" style="margin-bottom: 30px; page-break-inside: avoid;">
        <div class="detail-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>تفاصيل فاتورة رقم: {{ $p->invoice_number }}</span>
            <a href="#top" style="color: #fff; font-size: 9px; float: left; text-decoration: underline;">العودة للفهرس ↑</a>
        </div>
        
        <table style="width: 100%; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <td width="30%"><strong>المورد:</strong> {{ $p->supplier->contact_name ?? '---' }}</td>
                <td width="40%"><strong>تاريخ الفاتورة:</strong> {{ \Carbon\Carbon::parse($p->invoice_date)->format('Y-m-d H:i') }}</td>
                <td width="30%"><strong>حالة الدفع:</strong> {{ $p->payment_status }}</td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="40%">المنتج</th>
                    <th width="15%">الكمية</th>
                    <th width="15%">سعر الوحدة</th>
                    <th width="10%">ضريبة</th>
                    <th width="15%">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($p->items as $item)
                <tr class="{{ $loop->even ? 'even' : '' }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product->name_ar ?? $item->product->name }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit->unit_name ?? '' }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->tax_rate ?? 0 }}%</td>
                    <td class="fw-bold">{{ number_format($item->total_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa;">
                    <td colspan="5" style="text-align: left; font-weight: bold;">الصافي النهائي:</td>
                    <td class="fw-bold text-primary">{{ number_format($p->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: left;">المدفوع:</td>
                    <td class="text-success">{{ number_format($p->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: left;">المتبقي:</td>
                    <td class="text-danger">{{ number_format($p->grand_total - $p->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        
        @if($p->notes)
        <div style="margin-top: 8px; font-size: 9px; color: #666; border-right: 2px solid #eee; padding-right: 5px;">
            <strong>ملاحظات:</strong> {{ $p->notes }}
        </div>
        @endif
        
        <div style="text-align: left; margin-top: 5px;">
            <a href="#top" style="font-size: 8px; color: #999;">↑ العودة للفهرس</a>
        </div>
    </div>
    @endforeach
</body>
</html>
