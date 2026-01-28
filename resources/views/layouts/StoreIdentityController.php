<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // <-- تأكد من إضافة هذا

class StoreIdentityController extends Controller
{
    /**
     * عرض صفحة إعدادات الهوية البصرية.
     */
    public function show()
    {
        // جلب المتجر المرتبط بالمستخدم المسجل حالياً
        // (تأكد من أن لديك علاقة 'store' في مودل 'User')
        $store = auth()->user()->store; 

        // جلب الروابط (أو null) لكل مجموعة وسائط
        $logoUrl = $store->getFirstMediaUrl('logo');
        $sealUrl = $store->getFirstMediaUrl('seal');
        $signatureUrl = $store->getFirstMediaUrl('signature');

        return view('tenant.settings.identity', compact('store', 'logoUrl', 'sealUrl', 'signatureUrl'));
    }

    /**
     * تحديث (رفع) أصول الهوية البصرية.
     */
    public function update(Request $request)
    {
        // التحقق من صحة الطلب (Validation)
        $request->validate();

        $store = auth()->user()->store;

        // معالجة رفع الشعار [5, 6]
        if ($request->hasFile('logo')) {
            $store->addMediaFromRequest('logo')
                  ->toMediaCollection('logo', 'public'); // تحديد القرص 'public'
        }

        // معالجة رفع الختم
        if ($request->hasFile('seal')) {
            $store->addMediaFromRequest('seal')
                  ->toMediaCollection('seal', 'public');
        }

        // معالجة رفع التوقيع
        if ($request->hasFile('signature')) {
            $store->addMediaFromRequest('signature')
                  ->toMediaCollection('signature', 'public');
        }

        return back()->with('success', 'تم تحديث إعدادات الهوية البصرية بنجاح.');
    }

    /**
     * حذف أصل معين (مثل الشعار أو الختم).
     */
    public function destroyAsset(Request $request)
    {
        $request->validate(['type' => 'required|in:logo,seal,signature']);
        
        $store = auth()->user()->store;
        $type = $request->input('type');

        // حذف جميع الوسائط في هذه المجموعة (وبما أنها singleFile، سيحذف ملف واحد)
        $store->clearMediaCollection($type);

        return back()->with('success', 'تم حذف الملف بنجاح.');
    }
}