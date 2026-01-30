<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http; // ضروري جداً للاتصال
use Illuminate\Support\Facades\Log;
use App\Models\Store;

class SettingsController extends Controller
{
    public function index()
    {
        $store = Store::where('owner_id', Auth::id())->firstOrFail();

        // ==========================================================
        // 🔥 توحيد المنطق باستخدام السيرفس 🔥
        // ==========================================================
        $whatsappService = new \App\Services\WhatsAppService();
        $whatsappData = $whatsappService->getStatus($store->id);

        // نمرر المتجر + بيانات الواتساب للعرض
        return view('store_owner.settings.index', compact('store', 'whatsappData'));
    }

    // هذه الدالة المربوطة بصفحة الهوية (identity)
    public function updateIdentity(Request $request)
    {
         // 1. جلب المتجر
         $store = Store::where('owner_id', Auth::id())->firstOrFail();

         // 2. التحقق
         $request->validate([
             'logo' => 'nullable|image|max:5120',
             'stamp' => 'nullable|image|max:5120',
             'signature' => 'nullable|image|max:5120',
             'timezone' => 'nullable|string|max:100', // ✅ التحقق من التوقيت
             'clock_type' => 'nullable|string|in:digital,analog',
             'clock_theme' => 'nullable|string',
         ]);

         // 3. رفع الصور (الكود الأصلي)
         if ($request->hasFile('logo')) {
             if ($store->logo_path) Storage::disk('public')->delete($store->logo_path);
             $store->logo_path = $request->file('logo')->store('stores/'.$store->id, 'public');
         }
         if ($request->hasFile('stamp')) {
             if ($store->stamp_path) Storage::disk('public')->delete($store->stamp_path);
             $store->stamp_path = $request->file('stamp')->store('stores/'.$store->id, 'public');
         }
         if ($request->hasFile('signature')) {
             if ($store->signature_path) Storage::disk('public')->delete($store->signature_path);
             $store->signature_path = $request->file('signature')->store('stores/'.$store->id, 'public');
         }

         // 4. 🔥 حفظ التوقيت والساعة 🔥
         if ($request->has('timezone')) {
             $store->timezone = $request->timezone;
         }
         if($request->has('clock_type')) {
            $store->clock_type = $request->clock_type;
         }
         if($request->has('clock_theme')) {
            $store->clock_theme = $request->clock_theme;
         }
         
         $store->save();

         return back()->with('success', 'تم تحديث الهوية والتوقيت بنجاح');
    }

    // الدالة العامة للتحديث (شاملة الإشعارات والبيانات العامة)
   public function update(Request $request, $id)
    {
        $store = Store::where('id', $id)->where('owner_id', Auth::id())->firstOrFail();

        // 1. معالجة الـ Checkboxes (لأنها لا ترسل قيمة إذا لم تكن مفعلة)
        // هذه القائمة تضمن أن كل خيار له قيمته الخاصة (إيميل أو واتس)
        $checkboxes = [
            'notify_email', 'notify_whatsapp', // مفاتيح التشغيل الرئيسية
            
            // خيارات الإيميل
            'email_notify_sales', 'email_sales_credit_only',
            'email_notify_purchases', 'email_purchases_credit_only',
            'email_notify_stock', 'email_notify_expiry', 'email_daily_report',
            
            // خيارات الواتساب (مكررة ومنفصلة)
            'wa_notify_sales', 'wa_sales_credit_only',
            'wa_notify_purchases', 'wa_purchases_credit_only',
            'wa_notify_stock', 'wa_notify_expiry', 'wa_daily_report',
            'whatsapp_auto_prompt',
        ];

        foreach ($checkboxes as $chk) {
            $request->merge([$chk => $request->has($chk) ? 1 : 0]);
        }

        // 2. التحقق من الأرقام
        $request->validate([
            'store_name_update' => 'required|string|max:100',
            // ... (باقي تحققاتك القديمة)
            
            // تحقق القيم المالية (إيميل)
            'email_sales_min' => 'nullable|numeric|min:0',
            'email_sales_credit_min' => 'nullable|numeric|min:0',
            'email_purchases_min' => 'nullable|numeric|min:0',
            'email_purchases_credit_min' => 'nullable|numeric|min:0',
            
            // تحقق القيم المالية (واتساب)
            'wa_sales_min' => 'nullable|numeric|min:0',
            'wa_sales_credit_min' => 'nullable|numeric|min:0',
            'wa_purchases_min' => 'nullable|numeric|min:0',
            'wa_purchases_credit_min' => 'nullable|numeric|min:0',
        ]);

        // 3. حفظ البيانات الأساسية (كما هي في كودك)
        $store->name = $request->store_name_update;
        $store->phone_number = $request->phone_number;
        $store->email = $request->email;
        // ... (تكملة حفظ الصور والضريبة والوقت...)
        if($request->has('timezone')) $store->timezone = $request->timezone; 
        // ...

        // 4. 🔥 حفظ إعدادات الإشعارات المنفصلة 🔥
        $store->notify_email = $request->notify_email;
        $store->notify_whatsapp = $request->notify_whatsapp;
        $store->daily_report_time = $request->daily_report_time;

        // --- أ) إعدادات الإيميل ---
        $store->email_notify_sales = $request->email_notify_sales;
        $store->email_sales_min = $request->email_sales_min ?? 0;
        $store->email_sales_credit_only = $request->email_sales_credit_only;
        $store->email_sales_credit_min = $request->email_sales_credit_min ?? 0;
        
        $store->email_notify_purchases = $request->email_notify_purchases;
        $store->email_purchases_min = $request->email_purchases_min ?? 0;
        $store->email_purchases_credit_only = $request->email_purchases_credit_only;
        $store->email_purchases_credit_min = $request->email_purchases_credit_min ?? 0;

        $store->email_notify_stock = $request->email_notify_stock;
        $store->email_notify_expiry = $request->email_notify_expiry;
        $store->email_daily_report = $request->email_daily_report;

        // --- ب) إعدادات الواتساب ---
        $store->wa_notify_sales = $request->wa_notify_sales;
        $store->wa_sales_min = $request->wa_sales_min ?? 0;
        $store->wa_sales_credit_only = $request->wa_sales_credit_only;
        $store->wa_sales_credit_min = $request->wa_sales_credit_min ?? 0;

        $store->wa_notify_purchases = $request->wa_notify_purchases;
        $store->wa_purchases_min = $request->wa_purchases_min ?? 0;
        $store->wa_purchases_credit_only = $request->wa_purchases_credit_only;
        $store->wa_purchases_credit_min = $request->wa_purchases_credit_min ?? 0;

        $store->wa_notify_stock = $request->wa_notify_stock;
        $store->wa_notify_expiry = $request->wa_notify_expiry;
        $store->wa_daily_report = $request->wa_daily_report;
        $store->whatsapp_auto_prompt = $request->whatsapp_auto_prompt;

        // حفظ الصور (كما في كودك الأصلي)
        if ($request->hasFile('logo')) { /* ... */ }
        
        $store->save();
        return back()->with('success', 'تم تحديث خيارات الإشعارات بنجاح');
    }
}