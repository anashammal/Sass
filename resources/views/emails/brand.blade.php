<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: right;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 1px solid #eeeeee; padding-bottom: 20px;">
            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo" style="max-width: 150px; height: auto;">
        </div>

        <h2 style="color: #333333; margin-bottom: 20px;">مرحباً بك في نظام TechSys</h2>
        
        <p style="color: #555555; line-height: 1.6; font-size: 16px;">
            هذه رسالة تجريبية للتأكد من ظهور الشعار بشكل مدمج وصحيح.
            <br>
            نحن سعداء بانضمامك إلينا.
        </p>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}" style="background-color: #007bff; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">زيارة الموقع</a>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eeeeee; text-align: center; color: #999999; font-size: 12px;">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.</p>
            <p>إذا كنت لا ترغب في استلام هذه الإشعارات، يمكنك <a href="{{ config('app.url') }}" style="color: #999999;">إلغاء الاشتراك من هنا</a>.</p>
        </div>
    </div>

</body>
</html>