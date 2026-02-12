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

         // 3. رفع الصور (الهيكلة الجديدة)
         if ($request->hasFile('logo')) {
             if ($store->logo_path) Storage::disk('public')->delete($store->logo_path);
             $store->logo_path = $request->file('logo')->store('store_'.$store->id.'/settings', 'public');
         }
         if ($request->hasFile('stamp')) {
             if ($store->stamp_path) Storage::disk('public')->delete($store->stamp_path);
             $store->stamp_path = $request->file('stamp')->store('store_'.$store->id.'/settings', 'public');
         }
         if ($request->hasFile('signature')) {
             if ($store->signature_path) Storage::disk('public')->delete($store->signature_path);
             $store->signature_path = $request->file('signature')->store('store_'.$store->id.'/settings', 'public');
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

        // 2. التحقق من البيانات
        $request->validate([
            'store_name_update' => 'required|string|max:100',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:100',
            'tax_rates' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
            'invoice_mode' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:100',
            'clock_type' => 'nullable|string|in:digital,analog',
            'clock_theme' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'iban' => 'nullable|string|max:100',
            'iban_bank_name' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:100',
            'bank_country' => 'nullable|string|max:100',
            
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

        // 3. حفظ البيانات الأساسية
        $store->name = $request->store_name_update;
        $store->phone_number = $request->phone_number;
        $store->email = $request->email;
        $store->tax_number = $request->tax_number;
        $store->tax_rates = $request->tax_rates;
        $store->country_code = $request->country_code;
        $store->invoice_mode = $request->invoice_mode;
        $store->iban = $request->iban;
        
        // Handling bank name from dropdown or manual input
        $bankName = $request->iban_bank_name;
        if ($bankName === '__other__' && $request->filled('bank_name_manual')) {
            $bankName = $request->bank_name_manual;
        }
        $store->iban_bank_name = $bankName;
        
        $store->bank_account_holder = $request->bank_account_holder;
        $store->bank_country = $request->bank_country;
        if($request->has('timezone')) $store->timezone = $request->timezone;
        if($request->has('clock_type')) $store->clock_type = $request->clock_type;
        if($request->has('clock_theme')) $store->clock_theme = $request->clock_theme;
        
        $store->address = $request->address;
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;

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

        // 5. رفع الصور (إذا تم تغييرها - الهيكلة الجديدة)
        if ($request->hasFile('logo')) {
            if ($store->logo_path) Storage::disk('public')->delete($store->logo_path);
            $store->logo_path = $request->file('logo')->store('store_'.$store->id.'/settings', 'public');
        }
        if ($request->hasFile('stamp')) {
            if ($store->stamp_path) Storage::disk('public')->delete($store->stamp_path);
            $store->stamp_path = $request->file('stamp')->store('store_'.$store->id.'/settings', 'public');
        }
        if ($request->hasFile('signature')) {
            if ($store->signature_path) Storage::disk('public')->delete($store->signature_path);
            $store->signature_path = $request->file('signature')->store('store_'.$store->id.'/settings', 'public');
        }

        $store->save();
        return back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }
}