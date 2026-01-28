<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * تبديل لغة الواجهة.
     */
    public function switch($locale)
    {
        // 1. التحقق من أن اللغة مدعومة
        $supportedLocales = ['ar', 'en', 'tr', 'es', 'de', 'fr', 'zh', 'ja'];
        if (in_array($locale, $supportedLocales)) {
            // 2. حفظ اللغة المختارة في الجلسة
            Session::put('locale', $locale);
        }

        // 3. العودة إلى الصفحة السابقة
        return redirect()->back();
    }
}
