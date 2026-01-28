<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SetTenantContext
{
    public function handle(Request $request, Closure $next)
    {
        // محاولة استخراج النطاق الفرعي من الرابط الحالي
        $subdomain = $request->route('subdomain');

        // إذا لم يكن موجوداً في المسار، نحاول استخلاصه من الهوست (Host)
        // هذا يحل مشكلة إعادة التوجيه عند حدوث خطأ أو في الصفحات التي لا تحتوي على بارامتر
        if (!$subdomain) {
            $host = $request->getHost();
            $parts = explode('.', $host);
            // نتأكد أننا لسنا في النطاق الرئيسي tech-sys.online
            // عادةً يكون الترتيب: subdomain.domain.com (3 أجزاء)
            if (count($parts) >= 3 && $parts!== 'www' && $parts!== 'tech-sys') {
                $subdomain = $parts;
            }
        }

        // إذا وجدنا نطاقاً فرعياً، نعممه على النظام بالكامل
        if ($subdomain) {
            // هذا السطر يخبر Laravel باستخدام هذه القيمة تلقائياً لأي رابط يتم توليده لاحقاً
            URL::defaults(['subdomain' => $subdomain]);
            
            // دمج النطاق في الطلب ليكون متاحاً للكود الآخر
            $request->route()->setParameter('subdomain', $subdomain);
        }

        return $next($request);
    }
}