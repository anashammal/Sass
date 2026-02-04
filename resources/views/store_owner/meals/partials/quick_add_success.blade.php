<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تمت الإضافة بنجاح</title>
</head>
<body>
    <script>
        // إرسال رسالة للنافذة الأم (سواء كانت Window Opener أو Parent Iframe)
        const messageData = {
            type: 'quick_add_success',
            productId: {{ $product_id }},
            productName: '{{ $product_name }}'
        };

        if (window.opener) {
            // في حال تم الفتح كـ Tab جديد
            window.opener.postMessage(messageData, '*');
            window.close();
        } else if (window.parent && window.parent !== window) {
            // في حال تم الفتح داخل Iframe
            window.parent.postMessage(messageData, '*');
        }
    </script>
    <div style="text-align: center; padding: 50px; font-family: sans-serif;">
        <h2 style="color: #28a745;">✅ تم حفظ الصنف بنجاح!</h2>
        <p>جاري تحديث القائمة...</p>
    </div>
</body>
</html>
