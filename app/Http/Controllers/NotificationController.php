<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            // التوجيه للرابط المرفق بالإشعار (مثلاً صفحة المنتج)
            return redirect($notification->data['link'] ?? route('home'));
        }
        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }

    // تشغيل القائمة تلقائياً (JS Polling)
    public function runQueueWorker()
    {
        // 1. منع التكرار باستخدام Cache Lock
        // نستخدم Lock لمدة 60 ثانية، لضمان عدم تشغيل نفس الأمر مرتين
        $lock = \Illuminate\Support\Facades\Cache::lock('queue_worker_lock', 60);

        if ($lock->get()) {
            try {
                // 2. زيادة وقت التنفيذ لتجنب Timeout
                set_time_limit(60); 

                // 3. تشغيل الأمر (يعمل على Local و Online بنفس الكفاءة)
                // --stop-when-empty: يتوقف عند انتهاء المهام (مناسب للـ HTTP Request)
                // --max-time=50: أقصى مدة للتنفيذ 50 ثانية (أقل من الـ lock)
                \Illuminate\Support\Facades\Artisan::call('queue:work', [
                    '--stop-when-empty' => true,
                    '--max-time' => 50,
                    '--memory' => 128
                ]);
                
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Queue processed successfully',
                    'output' => \Illuminate\Support\Facades\Artisan::output()
                ]);

            } catch (\Exception $e) {
                // تحرير القفل في حالة الخطأ
                $lock->release();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            } finally {
                // تحرير القفل (اختياري، يمكن تركه لينتهي تلقائياً بعد 60 ثانية لمنع overlapping)
                // لكن الأفضل تركه لينتهي وقته لضمان عدم التداخل السريع
                // $lock->release(); 
            }
        }

        return response()->json(['status' => 'locked', 'message' => 'Worker is already running']);
    }
}