<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Store;

class DashboardController extends Controller
{
    /**
     * التحقق من الصلاحيات
     */
    public function __construct()
    {
        // بدلاً من كتابة 'super_admin'، نكتب المسار الكامل للكلاس
        $this->middleware(['auth', \App\Http\Middleware\CheckRoleSuperAdmin::class]);
    }

    /**
     * عرض الصفحة الرئيسية
     */
    public function index()
    {
        // التعديل: إزالة الشرطة السفلية لتطابق اسم المجلد الحقيقي
        return view('superadmin.dashboard'); 
    }

    /**
     * ✅ الدالة الآن في مكانها الصحيح داخل الكلاس
     * إعادة تشغيل سيرفر الواتساب
     */
    public function restartWhatsappServer()
    {
        try {
            // 1. تنفيذ أمر إعادة التشغيل
            // ملاحظة: يجب أن يكون للمستخدم www-data صلاحية sudo بدون باسوورد لهذا الأمر
            shell_exec('sudo /usr/bin/pm2 restart whatsapp-server');

            // الانتظار 5 ثواني (10 قد تكون طويلة جداً وتسبب timeout للمتصفح)
            sleep(5);

            // 2. إرسال الإشعارات للمتاجر
            $stores = Store::whereNotNull('phone_number')->get();
            
            $message = "📢 *تنبيه من إدارة النظام*\n\n";
            $message .= "تم إجراء تحديث لسيرفر الواتساب.\n";
            $message .= "يرجى التأكد من حالة الاتصال في لوحة تحكم متجرك.\n";
            $message .= "شكراً لتعاونكم.";

            $count = 0;
            foreach ($stores as $store) {
                if (!$store->phone_number) continue;

                $phone = preg_replace('/[^0-9]/', '', $store->phone_number);
                if (str_starts_with($phone, '00')) $phone = substr($phone, 2);

                try {
                    // إرسال الرسالة
                    Http::timeout(2)->post('http://localhost:3000/send-message', [
                        'phone' => $phone,
                        'message' => $message,
                    ]);
                    $count++;
                } catch (\Exception $e) {
                    // تجاهل الأخطاء الفردية
                }
            }

            return back()->with('success', "تم إعادة التشغيل وإرسال إشعارات إلى {$count} متجر بنجاح.");

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}