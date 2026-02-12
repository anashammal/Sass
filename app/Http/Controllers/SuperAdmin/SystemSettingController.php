<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        return view('superadmin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'system_default_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // معالجة رفع الشعار
        if ($request->hasFile('system_default_logo')) {
            // حذف القديم إن وجد
            $oldLogo = SystemSetting::where('key', 'system_default_logo')->value('value');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // رفع الجديد
            $path = $request->file('system_default_logo')->store('system', 'public');
            
            // تحديث القاعدة
            SystemSetting::updateOrCreate(
                ['key' => 'system_default_logo'],
                ['value' => $path]
            );
        }

        // معالجة بيانات البنك
        $bankFields = ['system_bank_name', 'system_iban', 'system_bank_account_holder'];
        foreach ($bankFields as $field) {
            if ($request->has($field)) {
                SystemSetting::updateOrCreate(
                    ['key' => $field],
                    ['value' => $request->get($field)]
                );
            }
        }

        return redirect()->back()->with('success', 'تم تحديث إعدادات النظام بنجاح.');
    }
}