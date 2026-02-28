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
    public function general()
    {
        $store = Store::where('owner_id', Auth::id())->firstOrFail();
        return view('store_owner.settings.general', compact('store'));
    }

    public function notifications()
    {
        $store = Store::where('owner_id', Auth::id())->firstOrFail();
        $whatsappService = new \App\Services\WhatsAppService();
        $whatsappData = $whatsappService->getStatus($store->id);
        
        return view('store_owner.settings.notifications', compact('store', 'whatsappData'));
    }

    public function billing()
    {
        $store = Store::where('owner_id', Auth::id())->firstOrFail();
        return view('store_owner.settings.billing', compact('store'));
    }

    public function identity()
    {
        $store = Store::where('owner_id', Auth::id())->firstOrFail();
        return view('store_owner.settings.identity', compact('store'));
    }

    public function timezone()
    {
        $store = Store::where('owner_id', Auth::id())->firstOrFail();
        return view('store_owner.settings.timezone', compact('store'));
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

    // الدالة العامة لتحديث الإعدادات العامة
    public function updateGeneral(Request $request, $id)
    {
        $store = Store::where('id', $id)->where('owner_id', Auth::id())->firstOrFail();
        $request->validate([
            'store_name_update' => 'required|string|max:100',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'address_details' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'base_currency_id' => 'required|exists:currencies,id',
            'accepted_currencies' => 'nullable|array',
            'accepted_currencies.*' => 'exists:currencies,id',
        ]);

        $store->name = $request->store_name_update;
        $store->phone_number = $request->phone_number;
        $store->email = $request->email;
        $store->tax_number = $request->tax_number;
        $store->country = $request->country;
        $store->city = $request->city;
        $store->address = $request->address;
        $store->address_details = $request->address_details;
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->base_currency_id = $request->base_currency_id;
        $store->save();

        if ($request->has('accepted_currencies')) {
            // Remove base currency from accepted if mistakenly selected
            $accepted = array_diff($request->accepted_currencies, [$request->base_currency_id]);
            
            // Sync with pivot table
            $syncData = [];
            foreach ($accepted as $currencyId) {
                $syncData[$currencyId] = ['store_id' => $store->id];
            }
            $store->acceptedCurrencies()->sync($syncData);
        } else {
            $store->acceptedCurrencies()->detach();
        }

        return back()->with('success', 'تم تحديث الإعدادات العامة بنجاح');
    }

    public function updateNotifications(Request $request, $id)
    {
        $store = Store::where('id', $id)->where('owner_id', Auth::id())->firstOrFail();

        $checkboxes = [
            'notify_email', 'notify_whatsapp',
            'email_notify_sales', 'email_sales_credit_only', 'email_notify_purchases', 'email_purchases_credit_only',
            'email_notify_stock', 'email_notify_expiry', 'email_daily_report',
            'wa_notify_sales', 'wa_sales_credit_only', 'wa_notify_purchases', 'wa_purchases_credit_only',
            'wa_notify_stock', 'wa_notify_expiry', 'wa_daily_report', 'whatsapp_auto_prompt',
        ];

        foreach ($checkboxes as $chk) {
            $request->merge([$chk => $request->has($chk) ? 1 : 0]);
        }

        $request->validate([
            'email_sales_min' => 'nullable|numeric|min:0',
            'email_sales_credit_min' => 'nullable|numeric|min:0',
            'email_purchases_min' => 'nullable|numeric|min:0',
            'email_purchases_credit_min' => 'nullable|numeric|min:0',
            'wa_sales_min' => 'nullable|numeric|min:0',
            'wa_sales_credit_min' => 'nullable|numeric|min:0',
            'wa_purchases_min' => 'nullable|numeric|min:0',
            'wa_purchases_credit_min' => 'nullable|numeric|min:0',
        ]);

        $store->notify_email = $request->notify_email;
        $store->notify_whatsapp = $request->notify_whatsapp;
        $store->daily_report_time = $request->daily_report_time;

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

        $store->save();
        return back()->with('success', 'تم تحديث إعدادات الإشعارات بنجاح');
    }

    public function updateBilling(Request $request, $id)
    {
        $store = Store::where('id', $id)->where('owner_id', Auth::id())->firstOrFail();
        $request->validate([
            'tax_rates' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
            'invoice_mode' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:100',
            'iban_bank_name' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:100',
            'bank_country' => 'nullable|string|max:100',
        ]);

        $store->tax_rates = $request->tax_rates;
        $store->country_code = $request->country_code;
        $store->invoice_mode = $request->invoice_mode;
        $store->iban = $request->iban;
        
        $bankName = $request->iban_bank_name;
        if ($bankName === '__other__' && $request->filled('bank_name_manual')) {
            $bankName = $request->bank_name_manual;
        }
        $store->iban_bank_name = $bankName;
        $store->bank_account_holder = $request->bank_account_holder;
        $store->bank_country = $request->bank_country;

        $store->save();
        return back()->with('success', 'تم تحديث البيانات المالية بنجاح');
    }

    public function updateTimezone(Request $request, $id)
    {
        $store = Store::where('id', $id)->where('owner_id', Auth::id())->firstOrFail();
        $request->validate([
            'timezone' => 'nullable|string|max:100',
            'clock_type' => 'nullable|string|in:digital,analog',
            'clock_theme' => 'nullable|string',
        ]);

        if($request->has('timezone')) $store->timezone = $request->timezone;
        if($request->has('clock_type')) $store->clock_type = $request->clock_type;
        if($request->has('clock_theme')) $store->clock_theme = $request->clock_theme;

        $store->save();
        return back()->with('success', 'تم تحديث التوقيت بنجاح');
    }
}