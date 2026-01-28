<?php
// تحديد المسارات بدقة حسب سيرفرك
$target = '/home/tech-sys/system/storage/app/public'; // مكان الصور الحقيقي
$link   = '/home/tech-sys/public_html/storage';      // مكان الجسر المطلوب

echo "<h2>تقرير إصلاح الصور:</h2>";

// 1. تنظيف الرابط القديم (لإزالة أي بقايا تسبب المشاكل)
if (file_exists($link)) {
    if (is_link($link)) {
        unlink($link);
        echo "<p style='color:orange'>تم حذف الرابط الرمزي القديم.</p>";
    } elseif (is_dir($link)) {
        // دالة لحذف المجلد إذا كان قد تم إنشاؤه كمجلد حقيقي بالخطأ
        system("rm -rf ".escapeshellarg($link));
        echo "<p style='color:orange'>تم حذف مجلد storage الخاطئ (directory).</p>";
    }
}

// 2. إنشاء الجسر الجديد
if (symlink($target, $link)) {
    echo "<h3 style='color:green'>✅ تم إنشاء الجسر (Symlink) بنجاح!</h3>";
    echo "الآن يربط بين:<br> $link <br>-><br> $target";
} else {
    echo "<h3 style='color:red'>❌ فشل إنشاء الرابط. تأكد من الصلاحيات.</h3>";
}

// 3. اختبار إمكانية الوصول
if (file_exists($link . '/.gitignore') || count(scandir($link)) > 2) {
    echo "<p style='color:blue'>✅ الاختبار ناجح: النظام يستطيع رؤية الملفات داخل مجلد التخزين.</p>";
} else {
    echo "<p style='color:red'>⚠️ تنبيه: الرابط تم إنشاؤه لكن المجلد يبدو فارغاً أو لا يمكن قراءته.</p>";
}
?>