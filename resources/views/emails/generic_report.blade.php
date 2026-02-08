<!DOCTYPE html>
<html dir="rtl">
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; direction: rtl; text-align: right; }
        .footer { margin-top: 20px; font-size: 0.8em; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div style="white-space: pre-wrap;">{!! nl2br(e($messageBody)) !!}</div>
    
    <div class="footer">
        <p>هذا التقرير تم توليده آلياً بناءً على طلبكم من لوحة تحكم TechSys.</p>
        <hr style="border:0; border-top:1px solid #eee; margin:10px 0;">
        <p style="color:#999; font-size:11px;">
            Tech-Sys Software Solutions | Istanbul, Turkey<br>
            يمكنك التحكم في وتيرة استلام هذه التقارير من إعدادات المتجر داخل النظام.
        </p>
        <p>© {{ date('Y') }} Tech-Sys Digital. All rights reserved.</p>
    </div>
</body>
</html>
