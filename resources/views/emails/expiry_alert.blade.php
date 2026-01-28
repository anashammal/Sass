<h2>مرحباً صاحب المتجر،</h2>
<p>يوجد لديك منتجات ستنتهي صلاحيتها قريباً، يرجى اتخاذ الإجراء اللازم:</p>
<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr style="background: #f8d7da;">
            <th>المنتج</th>
            <th>الكمية المتبقية</th>
            <th>تاريخ الانتهاء</th>
            <th>الأيام المتبقية</th>
        </tr>
    </thead>
    <tbody>
        @foreach($batches as $batch)
        <tr>
            <td>{{ $batch->product->name_ar }}</td>
            <td>{{ $batch->quantity }}</td>
            <td>{{ $batch->expiry_date->format('Y-m-d') }}</td>
            <td style="color: red; font-weight: bold;">
                {{ \Carbon\Carbon::now()->diffInDays($batch->expiry_date, false) }} يوم
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<p>نظام TechSys</p>