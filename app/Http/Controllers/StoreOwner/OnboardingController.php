<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OnboardingController extends Controller
{
    /**
     * عرض نموذج إكمال البيانات.
     */
    public function index()
    {
        // التصحيح: إضافة $store قبل علامة =
        $store = Auth::user()->store; 
        
        // التصحيح: إضافة $subdomain قبل علامة =
        $subdomain = request()->route('subdomain'); 
        
        $countries = [
            'TR' => 'تركيا',
            'SA' => 'المملكة العربية السعودية',
            'AE' => 'الإمارات العربية المتحدة',
            'EG' => 'مصر',
            'JO' => 'الأردن',
            'SY' => 'سوريا',
        ];

        return view('store_owner.onboarding.setup', compact('store', 'countries', 'subdomain'));
    }

    /**
     * حفظ بيانات الإعدادات.
     */
    public function update(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
            'city' => 'required|string',
            'phone_country_code' => 'required|string',
            'phone_number' => 'required|numeric',
            'iban' => 'required|string',
        ]);

        $store = Auth::user()->store;
        
        $store->update([
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'phone_country_code' => $request->phone_country_code,
            'phone_number' => $request->phone_number,
            'iban' => $request->iban,
            'bank_country' => $request->bank_country,
            'onboarding_complete' => true, 
        ]);

        return redirect()->route('store.dashboard', ['subdomain' => $store->subdomain])
             ->with('success', 'تم إكمال إعدادات المتجر بنجاح!');
    }

    /**
     * إرسال رمز التحقق عبر واتساب
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required']);
        
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        Cache::put('phone_otp_' . $phone, $otp, 300); // 5 minutes
        
        $store = Auth::user()->store;
        $storeName = $store->name ?? 'متجرك';
        
        $msg = "مرحباً بك في {$storeName} 👋\n\n";
        $msg .= "رمز التحقق الخاص بك هو:\n";
        $msg .= "*{$otp}*\n\n";
        $msg .= "صلاحية الرمز: 5 دقائق\n";
        $msg .= "⚠️ لا تشارك هذا الرمز مع أي شخص";
        
        try {
            // Use WhatsAppService
            $whatsapp = app(\App\Services\WhatsAppService::class);
            $sent = $whatsapp->send($phone, $msg, $store->id);
            
            \Log::info("OTP Send Attempt: Phone={$phone}, StoreID={$store->id}, Result=" . ($sent ? 'SUCCESS' : 'FAILED'));
            
            if ($sent) {
                return response()->json(['success' => true, 'message' => 'تم إرسال رمز التحقق']);
            }
            
            return response()->json(['success' => false, 'message' => 'فشل إرسال الرسالة - تحقق من اتصال واتساب']);
        } catch (\Exception $e) {
            \Log::error("OTP Exception: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }

    /**
     * التحقق من رمز OTP وحفظ الرقم المتحقق مباشرة
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required|digits:6'
        ]);
        
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $cached = Cache::get('phone_otp_' . $phone);
        
        if ($cached && $cached == $request->otp) {
            Cache::forget('phone_otp_' . $phone);
            
            // حفظ الرقم المتحقق مباشرة في قاعدة البيانات
            $store = Auth::user()->store;
            if ($store) {
                $store->phone_number = $request->phone; // مع الكود
                $store->phone_verified = true;
                $store->phone_verified_at = now();
                $store->save();
                
                \Log::info("Phone verified and saved: Store #{$store->id}, Phone: {$request->phone}");
            }
            
            session()->put('verified_phone', $phone);
            return response()->json([
                'success' => true, 
                'message' => 'تم التحقق وحفظ الرقم بنجاح ✅',
                'phone' => $request->phone
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح']);
    }
}