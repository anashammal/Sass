<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب - {{ $contact->contact_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .store-name {
            font-size: 20px;
            font-weight: bold;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .ledger-table th, .ledger-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .ledger-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-danger { color: #dc3545; }
        .text-success { color: #198754; }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #777;
            margin-top: 30px;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="store-name">{{ $store->name }}</div>
        <div>كشف حساب عميل / مورد</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>الاسم:</strong></td>
            <td width="35%">{{ $contact->contact_name }}</td>
            <td width="15%"><strong>التاريخ:</strong></td>
            <td width="35%">{{ now()->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <td><strong>الهاتف:</strong></td>
            <td>{{ $contact->phone ?? '-' }}</td>
            <td><strong>الرصيد النهائي:</strong></td>
            <td class="{{ $contact->balance >= 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format(abs($contact->balance), 2) }} 
                {{ $contact->balance >= 0 ? '(له علينا)' : '(لنا عنده)' }}
            </td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="15%">التاريخ</th>
                <th width="45%">البيان / المرجع</th>
                <th width="20%">المبلغ</th>
                <th width="20%">الرصيد المتراكم</th>
            </tr>
        </thead>
        <tbody>
            @php $currentBalance = 0; @endphp
            @foreach($ledger as $item)
                @php $currentBalance += $item->effect; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->date)->format('Y-m-d') }}</td>
                    <td style="text-align: right;">
                        @if($item->type == 'sale')
                            <a href="{{ url('store-owner/pos?invoice_id=' . $item->reference_id) }}">
                                {{ $item->reference }}
                            </a>
                        @elseif($item->type == 'purchase')
                            <a href="{{ url('store-owner/purchases/' . $item->reference_id) }}">
                                {{ $item->reference }}
                            </a>
                        @else
                            {{ $item->reference }}
                        @endif
                    </td>
                    <td class="{{ $item->effect < 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format(abs($item->effect), 2) }}
                    </td>
                    <td dir="ltr">
                        {{ number_format($currentBalance, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        تم إنشاء هذا التقرير آلياً بواسطة نظام {{ config('app.name') }}
    </div>
</body>
</html>
