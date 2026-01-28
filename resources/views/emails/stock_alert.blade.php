<div dir="rtl" style="font-family: Arial, sans-serif; line-height: 1.6;">
    <h2 style="color: #d9534f;">⚠️ تنبيه انخفاض المخزون</h2>
    <p>مرحباً صاحب متجر <strong>{{ $storeName }}</strong>،</p>
    
    @if($reason)
        <p style="background-color: #fcf8e3; padding: 10px; border-radius: 5px; border: 1px solid #faebcc;">
            <strong>ملاحظة:</strong> {{ $reason }}
        </p>
    @endif

    <p>لقد وصلت الأصناف التالية إلى حد الطلب أو قاربت على النفاذ:</p>
    
    <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%; text-align: right;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>اسم المنتج</th>
                <th>الكمية المتبقية</th>
                <th>حالة التنبيه</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alertLines as $line)
                <tr>
                    <td>{{ $line['name'] }}</td>
                    <td style="font-weight: bold; color: {{ $line['stock'] <= 0 ? 'red' : 'orange' }};">
                        {{ $line['stock'] }}
                    </td>
                    <td>
                        @if($line['stock'] <= 0)
                            <span style="color: red;">نفذ المخزون!</span>
                        @else
                            <span style="color: orange;">تحت حد الطلب</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <p style="margin-top: 20px;">يرجى مراجعة المخزون وتوفير النواقص في أقرب وقت.</p>
    <p>نظام <strong>{{ config('app.name') }}</strong></p>
</div>
