<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            
            // محاولة معرفة النطاق الفرعي الحالي
            $subdomain = $request->route('subdomain');
            
            if (!$subdomain) {
                $parts = explode('.', $request->getHost());
                if (count($parts) >= 3 && $parts!== 'www') {
                    $subdomain = $parts;
                }
            }

            // إذا كان الطلب قادماً من متجر فرعي، وجهه لصفحة دخول المتجر
            if ($subdomain && $subdomain!== 'tech-sys') {
                return route('tenant.login', ['subdomain' => $subdomain]);
            }

            // وإلا، وجهه لصفحة دخول الإدارة الرئيسية
            return route('login');
        }
    }
}