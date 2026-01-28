<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class StoreController extends Controller
{
    private $sessionId = 'system';

    public function index()
    {
        $stores = Store::with('owner')->latest()->paginate(10);
        return view('superadmin.stores.index', compact('stores'));
    }

    public function create()
    {
        $isWhatsappActive = false;
        try {
            // إضافة withoutVerifying ضروري جداً
            $response = Http::withoutVerifying()->timeout(5)->get('https://wa.tech-sys.online/session-status', [
                'session_id' => $this->sessionId
            ]);
            
            if ($response->successful() && $response->json('connected')) {
                $isWhatsappActive = true; 
            }
        } catch (\Exception $e) {
            $isWhatsappActive = false;
        }
        return view('superadmin.stores.create', compact('isWhatsappActive'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'store_name' => 'required|string|max:255',
            'subdomain' => 'required|string|alpha_dash|unique:stores,subdomain',
            'status' => 'required',
            'owner_phone' => 'nullable|numeric'
        ]);

        if ($request->filled('owner_phone')) {
            if (session('verified_phone') != $request->owner_phone) {
                return back()->withInput()->withErrors(['owner_phone' => 'يجب التحقق من الرقم أولاً.']);
            }
        }

        DB::beginTransaction();
        try {
            $password = Str::random(10);
            $user = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->owner_phone,
                'password' => Hash::make($password),
                'role' => 'store_owner',
            ]);

            $store = Store::create([
                'owner_id' => $user->id,
                'name' => $request->store_name,
                'subdomain' => $request->subdomain, 
                'status' => $request->status,
            ]);

            $user->store_id = $store->id;
            $user->save();

            $loginLink = "http://tech-sys.online/login";
            
            if ($request->filled('owner_phone')) {
                $msg = "🎉 *تهانينا! تم إنشاء متجرك ({$store->name}) بنجاح.*\n\n";
                $msg .= "👤 البريد: {$user->email}\n";
                $msg .= "🔑 كلمة المرور: {$password}\n";
                $msg .= "🔗 الرابط: {$loginLink}";
                
                try {
                    Http::withoutVerifying()->post('https://wa.tech-sys.online/send-message', [
                        'phone' => $request->owner_phone,
                        'message' => $msg,
                        'session_id' => $this->sessionId
                    ]);
                } catch (\Exception $e) {
                    Log::error("Welcome Msg Error: " . $e->getMessage());
                }
                session()->forget('verified_phone');
            }

            try {
                $emailData = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                    'store_name' => $store->name,
                    'link' => $loginLink
                ];

                Mail::send([], [], function ($message) use ($emailData) {
                    $message->to($emailData['email'])
                        ->subject('🎉 بيانات الدخول لمتجرك الجديد - TechSys')
                        ->html("
                            <div style='direction: rtl; text-align: right; font-family: Arial, sans-serif;'>
                                <h2>مرحباً {$emailData['name']}،</h2>
                                <p>تم إنشاء متجرك <b>({$emailData['store_name']})</b> بنجاح!</p>
                                <hr>
                                <p><b>📧 البريد الإلكتروني:</b> {$emailData['email']}</p>
                                <p><b>🔑 كلمة المرور:</b> {$emailData['password']}</p>
                                <p><b>🔗 رابط الدخول:</b> <a href='{$emailData['link']}'>{$emailData['link']}</a></p>
                            </div>
                        ");
                });
            } catch (\Exception $e) { }

            DB::commit();
            return redirect()->route('superadmin.stores.index')->with('success', 'تم الإنشاء وإرسال البيانات بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function sendCreationOtp(Request $request) {
        $request->validate(['phone' => 'required']);
        
        $otp = rand(100000, 999999);
        Cache::put('store_otp_' . $request->phone, $otp, 300);

        $msg = "مرحباً بك عزيزي الشريك في TechSys 👋\n\n";
        $msg .= "لإكمال توثيق المتجر، رمز التحقق هو:\n";
        $msg .= "*{$otp}*\n\n";
        $msg .= "توقيت الطلب: " . date('H:i:s'); 

        try {
            Http::withoutVerifying()->post('https://wa.tech-sys.online/send-message', [
                'phone' => $request->phone, 
                'message' => $msg, 
                'session_id' => $this->sessionId
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) { 
            return response()->json(['success' => false]); 
        }
    }

    public function verifyCreationOtp(Request $request) {
        $cached = Cache::get('store_otp_' . $request->phone);
        if($cached && $cached == $request->otp) {
            session()->put('verified_phone', $request->phone);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    // دوال إعدادات واتساب النظام - تم إصلاحها
    public function getSystemWhatsappStatus()
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get('https://wa.tech-sys.online/session-status', [
                'session_id' => 'system'
            ]);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
        }
    }

    public function logoutSystemWhatsapp()
    {
        try {
            Http::withoutVerifying()->post('https://wa.tech-sys.online/logout', [
                'session_id' => 'system'
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false]);
        }
    }

    public function restartWhatsappServer()
    {
        try {
            Http::withoutVerifying()->timeout(5)->post('https://wa.tech-sys.online/restart-server');
            return back()->with('success', 'تم إرسال أمر إعادة التشغيل');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل الاتصال بالسيرفر.');
        }
    }
    
    public function edit($id) { $store = Store::findOrFail($id); return view('superadmin.stores.edit', compact('store')); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { Store::findOrFail($id)->delete(); return redirect()->back(); }
}