<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\StoreOwner\DashboardController;
use App\Http\Controllers\StoreOwner\OnboardingController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Store Owner Routes (Subdomains)
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web']], function () {
    
    // 1. روابط تسجيل الدخول (كما هي)
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // 2. روابط استعادة كلمة المرور (الحل هنا)
    // قمنا بوضعها داخل مجموعة لها اسم 'tenant.' لمنع التعارض مع الرابط الرئيسي
    Route::group(['as' => 'tenant.'], function() {
        Auth::routes(['register' => false, 'login' => false]); 
    });
});

// الروابط المحمية
Route::middleware(['auth', 'store_owner'])->group(function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('storeowner.home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('storeowner.dashboard');

    Route::group(['prefix' => 'onboarding', 'as' => 'storeowner.onboarding.'], function () {
        Route::get('/setup', [OnboardingController::class, 'setup'])->name('setup');
        Route::post('/complete', [OnboardingController::class, 'complete'])->name('complete');
        Route::post('/send-otp', [OnboardingController::class, 'sendOtp'])->name('send-otp');
        Route::post('/verify-otp', [OnboardingController::class, 'verifyOtp'])->name('verify-otp');
    });

});