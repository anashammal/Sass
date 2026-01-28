<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * دالة يتم تنفيذها تلقائياً بعد نجاح تسجيل الدخول.
     * هنا نحدد وجهة المستخدم بناءً على دوره.
     */
    protected function authenticated(Request $request, $user)
    {
        // 1. توجيه السوبر أدمن
        if ($user->role == 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        // 2. توجيه صاحب المتجر
        if ($user->role == 'store_owner') {
            $store = $user->store;
            
            // إذا لم يكن لديه متجر بعد (حالة نادرة)
            if (!$store) {
                return redirect('/home'); 
            }

            // إذا لم يكمل الإعدادات -> وجهه لصفحة الإعدادات الجديدة
            if (!$store->onboarding_complete) {
                return redirect()->route('store.onboarding.setup', ['subdomain' => $store->subdomain]);
            }

            // الوضع الطبيعي -> وجهه للوحة التحكم
            return redirect()->route('store.dashboard', ['subdomain' => $store->subdomain]);
        }

        // المستخدم العادي
        return redirect('/home');
    }
}