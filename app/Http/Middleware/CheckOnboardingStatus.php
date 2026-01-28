<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckOnboardingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // نتحقق أولاً أن المستخدم مسجل دخوله وصاحب متجر
        if ($user && $user->role === 'store_owner') {
            
            // نفترض أن لدينا علاقة hasOne في User model باسم 'store'
            $store = $user->store;

            // إذا كان المتجر موجوداً وحقل 'onboarding_complete' قيمته false
            if ($store && !$store->onboarding_complete) {
                
                // نتأكد أن المستخدم ليس بالفعل في صفحة إكمال الإعدادات أو يرسل بياناتها
                $onboardingRoutes = ['storeowner.onboarding.setup', 'storeowner.onboarding.complete'];
                
                if (!in_array($request->route()->getName(), $onboardingRoutes)) {
                    // نحوله قسراً إلى صفحة الإعدادات
                    return redirect()->route('storeowner.onboarding.setup');
                }
            }
        }
        
        return $next($request);
    }
}