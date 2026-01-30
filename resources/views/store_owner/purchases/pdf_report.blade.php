<!DOCTYPE html>
<html dir="rtl" lang="ar">
<body dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير سجل المشتريات - {{ $store->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.6;
        }
        table {
            width: 100%;
            direction: rtl;
            border-collapse: collapse;
            float: right;
            clear: both;
            margin-bottom: 20px;
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
            direction: rtl;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: right;
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
        
        /* تلوين الخلايا يضمن ظهور اللون في الـ PDF */
        .items-table tr.even td {
            background-color: #f0f7ff !important;
            text-align: right;
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

            <table dir="rtl" class="table summary-box" width="100%" style="float: right; clear: both;">
                <tr>
                    <td width="25%" style="text-align: right;"><strong>إجمالي المشتريات:</strong> <br><span class="text-primary">{{ number_format($totals['sum_total'], 2) }}</span></td>
                    <td width="25%" style="text-align: right;"><strong>إجمالي المدفوع:</strong> <br><span class="text-success">{{ number_format($totals['sum_paid'], 2) }}</span></td>
                    <td width="25%" style="text-align: right;"><strong>إجمالي المتبقي:</strong> <br><span class="text-danger">{{ number_format($totals['sum_due'], 2) }}</span></td>
                    <td width="25%" style="text-align: right;"><strong>عدد الفواتير:</strong> <br>{{ $totals['count'] }}</td>
                </tr>
            </table>

    <h3 style="border-bottom: 1px solid #eee; padding-bottom: 5px;">فهرس الفواتير (اضغط على الفاتورة للانتقال للتفاصيل)</h3>
    <table dir="rtl" class="table" style="float: right; clear: both;">
        <thead>
            <tr>
                <th style="text-align: right;">م</th>
                <th style="text-align: right;">رقم الفاتورة</th>
                <th style="text-align: right;">المورد</th>
                <th style="text-align: right;">التاريخ</th>
                <th style="text-align: right;">الحالة</th>
                <th style="text-align: right;">الإجمالي</th>
                <th style="text-align: right;">المتبقي</th>
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
        
        <table dir="rtl" style="width: 100%; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <td width="30%" style="text-align: right;"><strong>المورد:</strong> {{ $p->supplier->contact_name ?? '---' }}</td>
                <td width="40%" style="text-align: right;"><strong>تاريخ الفاتورة:</strong> {{ \Carbon\Carbon::parse($p->invoice_date)->format('Y-m-d H:i') }}</td>
                <td width="30%" style="text-align: right;"><strong>حالة الدفع:</strong> {{ $p->payment_status }}</td>
            </tr>
        </table>

        <table dir="rtl" class="items-table">
            <thead>
                <tr>
                    <th width="5%" style="text-align: center;">#</th>
                    <th width="40%" style="text-align: right;">المنتج</th>
                    <th width="15%" style="text-align: center;">الكمية</th>
                    <th width="15%" style="text-align: center;">سعر الوحدة</th>
                    <th width="10%" style="text-align: center;">ضريبة</th>
                    <th width="15%" style="text-align: center;">الإجمالي</th>
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
                    <td colspan="5" style="text-align: right; font-weight: bold;">الصافي النهائي:</td>
                    <td class="fw-bold text-primary" style="text-align: left;">{{ number_format($p->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;">المدفوع:</td>
                    <td class="text-success" style="text-align: left;">{{ number_format($p->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;">المتبقي:</td>
                    <td class="text-danger" style="text-align: left;">{{ number_format($p->grand_total - $p->paid_amount, 2) }}</td>
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
