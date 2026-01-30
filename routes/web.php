<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

// المتحكمات العامة
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;

// متحكمات السوبر أدمن
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\StoreController;
use App\Http\Controllers\SuperAdmin\SystemSettingController;
use App\Http\Controllers\SuperAdmin\WhatsAppController;

// متحكمات صاحب المتجر
use App\Http\Controllers\StoreOwner\CategoryController;
use App\Http\Controllers\StoreOwner\ContactController;
use App\Http\Controllers\StoreOwner\OnboardingController;
use App\Http\Controllers\StoreOwner\DashboardController as StoreOwnerDashboardController;
use App\Http\Controllers\StoreOwner\ProductController;
use App\Http\Controllers\StoreOwner\SettingsController;
use App\Http\Controllers\StoreOwner\PurchaseController;
use App\Http\Controllers\StoreOwner\PosController;
use App\Http\Controllers\StoreOwner\ReportController;
use App\Http\Controllers\StoreOwner\ShiftController;
use App\Http\Controllers\StoreOwner\WhatsAppController as StoreWhatsAppController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// رابط للتنظيف الإجباري
Route::get('/force-clear', function() {
    Artisan::call('optimize:clear');
    return '<h1>تم تنظيف النظام بنجاح! ✅</h1> <a href="/store-owner/pos">العودة للبيع</a>';
});

// حل مشكلة عرض الصور من التخزين
Route::get('storage-files/{path}', [ProductController::class, 'serveMedia'])
    ->where('path', '.*')->name('serve.media.workaround');

Route::get('/', function () { return view('welcome'); });

// إيقاف التسجيل العام
Auth::routes(['register' => false]);

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

// ------------------------------------------------------------------------
// (1) لوحة السوبر أدمن
// ------------------------------------------------------------------------
Route::middleware(['auth', 'super_admin'])->prefix('superadmin')->as('superadmin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('stores', StoreController::class)->except(['show']);
    Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SystemSettingController::class, 'update'])->name('settings.update');
    
    // 🔥 روابط إعدادات الواتساب (يجب أن تكون هنا داخل المجموعة) 🔥
    // الاسم النهائي سيكون: superadmin.settings.whatsapp.status
    Route::get('/settings/whatsapp-status', [StoreController::class, 'getSystemWhatsappStatus'])->name('settings.whatsapp.status');
    Route::post('/settings/whatsapp-logout', [StoreController::class, 'logoutSystemWhatsapp'])->name('settings.whatsapp.logout');
    Route::post('/settings/whatsapp-restart', [StoreController::class, 'restartSystemWhatsapp'])->name('whatsapp.restart');

    // AJAX
    Route::post('stores/otp/send', [StoreController::class, 'sendCreationOtp'])->name('stores.otp.send');
    Route::post('stores/otp/verify', [StoreController::class, 'verifyCreationOtp'])->name('stores.otp.verify');
});

// ✅✅✅ الرابط هنا (خارج المجموعة) لكي لا يتغير اسمه ✅✅✅
Route::post('/super-admin/whatsapp/restart', [DashboardController::class, 'restartWhatsappServer'])
    ->name('super_admin.whatsapp.restart') // الآن الاسم مطابق تماماً لملف الـ Blade
    ->middleware(['auth', 'super_admin']);

// ------------------------------------------------------------------------
// (2) لوحة صاحب المتجر (محمية بـ Auth)
// ------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    
    // الإشعارات
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // === مجموعة روابط صاحب المتجر (Store Owner) ===
    Route::group(['prefix' => 'store-owner', 'as' => 'store.'], function () {
        
        // البحث والحفظ السريع
        Route::get('products/search', [PurchaseController::class, 'searchProducts'])->name('products.search');
        Route::get('purchases/product-history/{id}', [PurchaseController::class, 'getProductHistory'])->name('purchases.history'); // ✅ New Route
        Route::get('contacts/search', [PurchaseController::class, 'searchSuppliers'])->name('contacts.search');
        Route::post('products/quick-store', [PurchaseController::class, 'quickStoreProduct'])->name('products.quick_store');
        Route::post('contacts/quick-store', [PurchaseController::class, 'quickStoreSupplier'])->name('contacts.quick_store');
        Route::post('products/check-barcode', [ProductController::class, 'checkBarcode'])->name('products.check_barcode');

        // الموارد الأساسية
        Route::get('/dashboard', [StoreOwnerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/{id}', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/store/setup', [OnboardingController::class, 'index'])->name('onboarding.setup');
        Route::post('/store/setup', [OnboardingController::class, 'update'])->name('onboarding.update');

        // روابط الحذف الآمن للتصنيفات
        Route::get('categories/check-status/{category}', [CategoryController::class, 'checkStatus'])->name('categories.check_status');
        Route::post('categories/move-delete/{category}', [CategoryController::class, 'moveProductsAndDelete'])->name('categories.move_delete');
        Route::delete('categories/force-delete/{category}', [CategoryController::class, 'forceDelete'])->name('categories.force_delete');

        Route::resource('categories', CategoryController::class);
        Route::resource('contacts', ContactController::class);
        // ==========================================
        // ✅ روابط المنتجات (الترتيب هنا مهم جداً)
        // ==========================================
        
        // 1. الروابط المخصصة (يجب أن تكون في الأعلى لتجنب تضارب الـ ID)
        Route::get('products/expired-manager', [ProductController::class, 'expiredManager'])->name('products.expired_manager');
        
        // New Routes for Advanced Expiry Management
        Route::post('products/dispose-stock', [ProductController::class, 'disposeStock'])->name('products.dispose');
        Route::post('products/extend-expiry', [ProductController::class, 'extendExpiry'])->name('products.extend');
        
        // Legacy routes (kept for safety if old links exist, though UI is updated)
        Route::post('products/expired/dispose', [ProductController::class, 'disposeExpired'])->name('products.expired.dispose');
        Route::post('products/expired/renew', [ProductController::class, 'renewExpiry'])->name('products.expired.renew');
        
        // روابط التحديث السريع (من الجرس)
        Route::post('/products/quick-update-stock', [ProductController::class, 'quickUpdateStock'])->name('products.quick_update_stock');
        Route::post('/products/quick-update-alert', [ProductController::class, 'quickUpdateAlert'])->name('products.quick_update_alert');

        // 2. رابط الموارد الأساسي (مع استثناء show)
        Route::resource('products', ProductController::class)->except(['show']);
        
        


        Route::resource('purchases', PurchaseController::class);

        // Purchase Management
        Route::get('purchases/report/pdf', [\App\Http\Controllers\StoreOwner\PurchaseController::class, 'pdfReport'])->name('purchases.report.pdf');
        Route::get('purchases/report/interactive', [\App\Http\Controllers\StoreOwner\PurchaseController::class, 'interactiveReport'])->name('purchases.report.interactive');
        Route::resource('expenses', \App\Http\Controllers\StoreOwner\ExpenseController::class);
        Route::resource('expense-categories', \App\Http\Controllers\StoreOwner\ExpenseCategoryController::class);

        // Debt/Payment Management
        Route::post('payments', [\App\Http\Controllers\StoreOwner\PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/ledger/{contact_id}', [\App\Http\Controllers\StoreOwner\PaymentController::class, 'ledger'])->name('payments.ledger');
        Route::get('payments/ledger/{contact_id}/pdf', [\App\Http\Controllers\StoreOwner\PaymentController::class, 'ledgerPdf'])->name('payments.ledger.pdf');

        // إدارة الصندوق (Shift Management)
        Route::get('/pos/shift/status', [ShiftController::class, 'checkStatus'])->name('pos.shift.status');
        Route::post('/pos/shift/open', [ShiftController::class, 'openShift'])->name('pos.shift.open');
        Route::get('/pos/shift/summary', [ShiftController::class, 'getSummary'])->name('pos.shift.summary');
        Route::post('/pos/shift/close', [ShiftController::class, 'closeShift'])->name('pos.shift.close');

        // نقطة البيع (POS)
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('pos/search-products', [PosController::class, 'searchProducts'])->name('pos.search-products');
        Route::get('pos/search-customers', [PosController::class, 'searchCustomers'])->name('pos.search-customers');
        Route::post('pos/save', [PosController::class, 'storeInvoice'])->name('pos.save');
        Route::get('pos/recent-sales', [PosController::class, 'getRecentSales'])->name('pos.recent-sales');
        Route::get('pos/withdrawals', [PosController::class, 'getWithdrawalsReport'])->name('pos.withdrawals');
        Route::get('pos/sale-details/{id}', [PosController::class, 'getSaleDetails'])->name('pos.sale-details');
        Route::get('pos/sales/{id}/partial', [PosController::class, 'showSalePartial'])->name('pos.sales.partial');
        Route::delete('pos/delete-sale/{id}', [PosController::class, 'deleteSale'])->name('pos.delete-sale');
        Route::post('/pos/adjust-stock', [PosController::class, 'quickAdjustStock'])->name('pos.adjustStock');
        // رابط تحديث الصلاحية (للمنتجات المنتهية)
        Route::post('/pos/update-expiry', [PosController::class, 'updateProductExpiry'])->name('pos.updateExpiry');
        // روابط الإرجاع
        Route::get('pos/return/search', [PosController::class, 'searchReturnInvoices'])->name('pos.return.search');
        Route::post('pos/return/process', [PosController::class, 'processReturn'])->name('pos.return.process');

        // رابط إغلاق النافذة
        Route::post('/settings/mark-popup-seen', function() {
            session(['expiry_popup_seen' => true]);
            return response()->json(['status' => 'ok']);
        })->name('settings.mark_popup_seen');

        // ==========================================
        // ✅ روابط واتساب المتجر (نسخة واحدة نظيفة)
        // ==========================================
        Route::get('/whatsapp', [StoreWhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::get('/whatsapp/status', [StoreWhatsAppController::class, 'getStatus'])->name('whatsapp.status');
        Route::post('/whatsapp/logout', [StoreWhatsAppController::class, 'logout'])->name('whatsapp.logout');
        Route::post('/whatsapp/send', [StoreWhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    // صفحة إدارة المنتجات المنتهية وقريبة الانتهاء
Route::get('products/expired-manager', [ProductController::class, 'expiredManager'])->name('products.expired_manager');

// إجراء الإتلاف (إنقاص الكمية من الدفعة المحددة فقط)
Route::post('products/expired/dispose', [ProductController::class, 'disposeExpired'])->name('products.expired.dispose');

// إجراء تجديد الصلاحية (تحديث التاريخ للدفعة المحددة)
Route::post('products/expired/renew', [ProductController::class, 'renewExpiry'])->name('products.expired.renew');
   
    }); // <-- نهاية مجموعة store-owner

    // تقارير الصناديق (خارج مجموعة store-owner ليتطابق مع الاسم في القائمة الجانبية إذا لزم الأمر)
    Route::get('/reports/shifts', [ReportController::class, 'shifts'])->name('reports.shifts');

}); // <-- نهاية مجموعة auth

// ------------------------------------------------------------------------
// (3) أدوات مساعدة وإصلاح النظام (للمطور)
// ------------------------------------------------------------------------

Route::get('/fix-images-now', function () {
    $targetFolder = base_path('storage/app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
    if (file_exists($linkFolder)) { @unlink($linkFolder); @rmdir($linkFolder); }
    try { symlink($targetFolder, $linkFolder); return "تم الإصلاح!"; } 
    catch (\Exception $e) { return "خطأ: " . $e->getMessage(); }
});

Route::get('storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) abort(404);
    $file = file_get_contents($filePath);
    $type = mime_content_type($filePath);
    return response($file, 200)->header("Content-Type", $type);
})->where('path', '.*');

Route::get('/test-whatsapp', function () {
    $phone = '905527932389'; 
    $message = "🎉 تجربة ناجحة! نظام TechSys متصل الآن.";
    
    $response = \Illuminate\Support\Facades\Http::post('http://localhost:3000/send-message', [
        'phone' => $phone,
        'message' => $message
    ]);
    
    return $response->json();
});
Route::get('/sync-expiry-dates', function () {
    $products = \App\Models\Product::with('batches')->get();
    $count = 0;
    
    foreach ($products as $product) {
        // نأخذ أقرب دفعة ستنتهي (أو انتهت) ولديها كمية
        $oldestBatch = $product->batches()->where('quantity', '>', 0)->orderBy('expiry_date', 'asc')->first();
        
        if ($oldestBatch) {
            $product->expiry_date = $oldestBatch->expiry_date;
            $product->save();
            $count++;
        }
    }
    
    return "تم تحديث تواريخ صلاحية $count منتج من نظام الدفعات القديم.";
});
Route::get('/add-expiry-days-column', function () {
    try {
        \Illuminate\Support\Facades\Schema::table('products', function ($table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'expiry_warning_days')) {
                $table->integer('expiry_warning_days')->default(30)->after('alert_quantity');
            }
        });
        return "تم إضافة عمود (أيام التنبيه) بنجاح! القيمة الافتراضية 30 يوم.";
    } catch (\Exception $e) {
        return "حدث خطأ أو العمود موجود مسبقاً: " . $e->getMessage();
    }
});
// مسار لإصلاح قاعدة البيانات وإضافة الأعمدة الناقصة فوراً
Route::get('/fix-database-columns', function () {
    try {
        $table = 'stores';
        $added = [];
        
        \Illuminate\Support\Facades\Schema::table($table, function ($t) use (&$added) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('stores', 'notify_email')) {
                $t->boolean('notify_email')->default(true)->after('tax_number');
                $added[] = 'notify_email';
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('stores', 'notify_whatsapp')) {
                $t->boolean('notify_whatsapp')->default(false)->after('notify_email');
                $added[] = 'notify_whatsapp';
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('stores', 'daily_report_time')) {
                $t->time('daily_report_time')->nullable()->after('notify_whatsapp');
                $added[] = 'daily_report_time';
            }
        });
        
        if(empty($added)) {
            return "✅ قاعدة البيانات سليمة، جميع الأعمدة موجودة مسبقاً.";
        }
        return "✅ تم إصلاح القاعدة وإضافة الأعمدة: " . implode(', ', $added);
        
    } catch (\Exception $e) {
        return "❌ خطأ: " . $e->getMessage();
    }
});
Route::get('/fix-missing-contact-columns', function () {
    try {
        $table = 'stores';
        $added = [];
        
        \Illuminate\Support\Facades\Schema::table($table, function ($t) use (&$added) {
            // إضافة عمود الإيميل إذا كان ناقصاً
            if (!\Illuminate\Support\Facades\Schema::hasColumn('stores', 'email')) {
                $t->string('email')->nullable()->after('name');
                $added[] = 'email';
            }
            // إضافة عمود الهاتف إذا كان ناقصاً
            if (!\Illuminate\Support\Facades\Schema::hasColumn('stores', 'phone_number')) {
                $t->string('phone_number')->nullable()->after('email');
                $added[] = 'phone_number';
            }
        });
        
        if(empty($added)) {
            return "✅ قاعدة البيانات سليمة، الأعمدة موجودة مسبقاً.";
        }
        return "✅ تم الإصلاح وإضافة: " . implode(', ', $added);
        
    } catch (\Exception $e) {
        return "❌ خطأ: " . $e->getMessage();
    }
});
Route::get('/test-whatsapp-direct', function () {
    // تفعيل عرض الأخطاء مؤقتاً لهذه الصفحة
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $phone = '963954602299'; // رقم للتجربة
    $message = "Test Message Direct";

    try {
        // استخدام 127.0.0.1 بدلاً من localhost
        $response = \Illuminate\Support\Facades\Http::timeout(10)->post('http://127.0.0.1:3000/send-message', [
            'phone' => $phone,
            'message' => $message,
            'session_id' => 'system'
        ]);

        return "
        <h1>نتائج الفحص:</h1>
        <ul>
            <li><b>الحالة (Status):</b> {$response->status()}</li>
            <li><b>النجاح (Successful):</b> " . ($response->successful() ? 'نعم ✅' : 'لا ❌') . "</li>
            <li><b>جسم الرد (Body):</b> {$response->body()}</li>
        </ul>";

    } catch (\Exception $e) {
        return "
        <h1>فشل كارثي ❌:</h1>
        <p><b>الرسالة:</b> {$e->getMessage()}</p>
        <hr>
        <p>تأكد أن السيرفر يعمل على المنفذ 3000 وأن الجدار الناري لا يحجب الاتصال المحلي.</p>
        ";
    }
});