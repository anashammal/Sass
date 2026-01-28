<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- أضف هذا

class CheckRoleSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // إذا كان المستخدم مسجلاً ودوره "سوبر أدمن"
        if (Auth::check() && Auth::user()->role == 'superadmin') {
            return $next($request); // اسمح له بالمرور
        }

        // إذا لم يكن كذلك، أعده إلى الصفحة الرئيسية
        return redirect('/home');
    }
}
