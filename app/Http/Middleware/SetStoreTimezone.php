<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetStoreTimezone
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // التحقق من وجود مستخدم مسجل ولديه متجر
        if (Auth::check() && ($user = Auth::user()) && $user->store) {
            
            $timezone = $user->store->timezone;

            if ($timezone) {
                // 1. ضبط توقيت التطبيق (يؤثر على Carbon، Eloquent، والواجهات)
                Config::set('app.timezone', $timezone);
                date_default_timezone_set($timezone);

                // 2. 🔥 ضبط توقيت قاعدة البيانات (SQL) 🔥
                // هذا هو السر لجعل التقارير والاستعلامات المباشرة تعمل بالتوقيت الجديد
                try {
                    // نحسب فرق التوقيت الحالي (مثلاً +03:00)
                    $offset = (new \DateTime('now', new \DateTimeZone($timezone)))->format('P');
                    
                    // نرسل أمر لقاعدة البيانات لاعتماد هذا التوقيت في الجلسة الحالية
                    DB::statement("SET time_zone = '$offset'");
                } catch (\Exception $e) {
                    // نتجاهل الخطأ في حال كانت قاعدة البيانات لا تدعم تغيير التوقيت (نادر)
                }
            }
        }

        return $next($request);
    }
}