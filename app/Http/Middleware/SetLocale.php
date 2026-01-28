<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // إذا كانت هناك لغة محفوظة في الجلسة، نستخدمها
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // اللغة الافتراضية
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}