<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Store;

class WhatsAppController extends Controller
{
    protected $whatsapp;

    public function __construct(\App\Services\WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    private function getStoreId()
    {
        return Store::where('owner_id', Auth::id())->value('id');
    }

    public function index()
    {
        return view('store_owner.settings.whatsapp');
    }

    public function getStatus()
    {
        $storeId = $this->getStoreId();
        // نمرر فقط الـ ID لأن السيرفس يضيف البادئة تلقائياً
        return response()->json($this->whatsapp->getStatus($storeId));
    }

    public function logout()
    {
        $storeId = $this->getStoreId();
        $reset = $this->whatsapp->logout($storeId);
        
        return response()->json(['success' => $reset]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'message' => 'required',
        ]);

        $storeId = $this->getStoreId();
        $mediaUrl = $request->input('media_url');
        $filename = $request->input('filename', 'document.pdf');
        
        if ($mediaUrl) {
            // محاولة إرسال ملف مرفق مباشر (بدون رابط)
            $success = $this->whatsapp->sendFile($request->phone, $mediaUrl, $request->message, $storeId, $filename);
            
            if ($success) {
                // تنفيذ طلب المستخدم: حذف الملف المؤقت بعد التأكد من الإرسال
                try {
                    if (str_contains($mediaUrl, 'temp_reports')) {
                        $baseAsset = asset('');
                        $relativePath = str_ireplace($baseAsset, '', $mediaUrl);
                        $fullPath = public_path($relativePath);
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                            \Illuminate\Support\Facades\Log::info("Temporary file deleted after sending: " . $filename);
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not delete temporary file: " . $e->getMessage());
                }

                return response()->json(['success' => true, 'message' => 'تم إرسال الملف كمرفق بنجاح']);
            }
            
            // إذا فشل المرفق المباشر، نعطي رسالة خطأ واضحة
            return response()->json([
                'success' => false, 
                'message' => 'فشل إرسال الملف كمرفق. يرجى التأكد من اتصال خدمة الواتساب أو جرب الإرسال لاحقاً.'
            ], 500);
        }

        // إرسال رسالة نصية عادية
        $success = $this->whatsapp->send($request->phone, $request->message, $storeId);

        if ($success) {
            return response()->json(['success' => true, 'message' => 'تم إرسال الرسالة بنجاح']);
        }

        return response()->json(['success' => false, 'message' => 'فشل إرسال الرسالة، تأكد من اتصال واتساب وحاول مجدداً.'], 500);
    }
    public function searchContacts(Request $request)
    {
        $term = $request->input('q');
        $storeId = $this->getStoreId();
        
        Log::info("WhatsApp Search: Term=[{$term}], StoreID=[{$storeId}]");

        $contacts = \App\Models\Contact::where('store_id', $storeId)
            ->where(function($query) use ($term) {
                $query->where('contact_name', 'LIKE', "%{$term}%")
                      ->orWhere('phone', 'LIKE', "%{$term}%");
            })
            ->take(20)
            ->get();
            
        Log::info("WhatsApp Search: Found " . $contacts->count() . " results.");
            
        $results = $contacts->map(function($c) {
            return [
                'id' => $c->id,
                'name' => $c->contact_name,
                'phone' => $c->phone,
                'text' => $c->contact_name . ' (' . ($c->phone ?? '---') . ')'
            ];
        });

        return response()->json($results);
    }
}
