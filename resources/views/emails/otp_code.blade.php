<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق</title>
</head>
<body style="font-family: Arial, sans-serif; text-align: right; direction: rtl; padding: 20px;">
    <h2>رمز التحقق الخاص بك</h2>
    <p>لاتمام عملية استعادة كلمة المرور، يرجى استخدام الرمز التالي:</p>
    
    <div style="background: #f4f4f4; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;">
        {{ $code }}
    </div>

    <p>الرمز صالح لمدة قصيرة.</p>
    
    <hr>
    <p style="font-size: 12px; color: #777;">
        شكراً،<br>
        {{ config('app.name') }}
    </p>
</body>
</html>
