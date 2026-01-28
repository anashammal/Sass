<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'تقرير جهات الاتصال' }}</title>
    <style>
        body { font-family: 'XB Zar', sans-serif; font-size: 10pt; margin: 0; padding: 0; direction: rtl; }
        h2 { text-align: center; color: #3e2d5f; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #3e2d5f; color: white; }
        tfoot th { background-color: #f0f0f0; border-top: 2px solid #333; }
        .watermark { opacity: 0.1; position: fixed; top: 30%; left: 30%; font-size: 80pt; color: gray; transform: rotate(-45deg); }
        /* !!! -- هذا كود الـ QR Code (يتطلب مكتبة milon/barcode-svg -- */
        .qr-code { text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="watermark">{{ config('app.name', 'TechSys') }}</div>

    <h2>{{ $title ?? 'تقرير جهات الاتصال' }}</h2>

    <table cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الشركة</th>
                <th>النوع</th>
                <th>الهاتف</th>
                <th>البريد الإلكتروني</th>
                <th>الرقم الضريبي</th>
                <th>العنوان</th>
                <th>الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @php $totalBalance = 0; @endphp
            @foreach ($contacts as $contact)
                @php $totalBalance += $contact->balance; @endphp
                <tr>
                    <td>{{ $contact->contact_name }}</td>
                    <td>{{ $contact->company_name ?? '-' }}</td>
                    <td>{{ $contact->is_supplier ? ($contact->is_customer ? 'مورد/زبون' : 'مورد') : 'زبون' }}</td>
                    <td>{{ $contact->phone }}</td>
                    <td>{{ $contact->email ?? '-' }}</td>
                    <td>{{ $contact->tax_number ?? '-' }}</td>
                    <td>{{ $contact->address ?? '-' }}</td>
                    <td>{{ number_format($contact->balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" style="text-align: left;">الإجمالي العام للرصيد / الدين:</th>
                <th>{{ number_format($totalBalance, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="qr-code">
        <p><strong>QR Code للتحقق من التقرير</strong></p>
    </div>

</body>
</html>
