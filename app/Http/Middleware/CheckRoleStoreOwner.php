<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRoleStoreOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role == 'store_owner') {
            $store = Auth::user()->store;

            // إذا كان التاجر لم يكمل الإعدادات، والصفحة الحالية ليست هي صفحة الإعدادات أو الحفظ
            // نوجهه قسراً لصفحة الإعدادات الصحيحة
            if ($store && !$store->onboarding_complete) {
                if (!$request->routeIs('store.onboarding.setup') && !$request->routeIs('store.onboarding.update')) {
                    return redirect()->route('store.onboarding.setup', ['subdomain' => $store->subdomain]);
                }
            }
        }

        return $next($request);
    }
}