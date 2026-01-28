<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // قد تحتاجها لاحقاً
use Illuminate\Support\Facades\Redirect; // قد تحتاجها لاحقاً

class OnboardingController extends Controller
{
    /**
     * عرض نموذج إكمال البيانات.
     */
    public function setup()
    {
        // 1. جلب بيانات المتجر 
        $store = Auth::user()->store; 
        
        // جلب الدومين من مسارات الطلب الحالي
        $subdomain = request()->route('subdomain'); 
        
        // 2. قائمة الدول (مبسطة لعمل القائمة المنسدلة)
        $countries = [
            'TR' => 'تركيا',
            'SA' => 'المملكة العربية السعودية',
            'AE' => 'الإمارات العربية المتحدة',
            'EG' => 'مصر',
            'JO' => 'الأردن',
            'SY' => 'سوريا',
        ];

        // 3. عرض الـ View وتمرير $subdomain
        return view('store_owner.onboarding.setup', compact('store', 'countries', 'subdomain'));
    }

    /**
     * حفظ بيانات الإعدادات وتعيين حالة الاكتمال.
     */
    public function complete(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'country' => 'required|string',
            'city' => 'required|string',
            'phone_country_code' => 'required|string',
            'phone_number' => 'required|numeric',
            'iban' => 'required|string',
            'bank_country' => 'nullable|string', // قد لا يكون إلزاميًا
            'address' => 'nullable|string',
        ]);

        // 2. تحديث بيانات المتجر
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

        // 3. التوجيه إلى لوحة التحكم الرئيسية (باستخدام URL ثابت لتجاوز عطل الـ Route Cache)
        $subdomain = request()->route('subdomain');
        $appDomain = env('APP_DOMAIN', 'tech-sys.online'); // يجب أن يكون tech-sys.online

        return redirect()->to("http://$subdomain.$appDomain/dashboard")
             ->with('success', 'تم إكمال إعدادات المتجر بنجاح! مرحباً بك.');
    }
}