<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\VerificationCodeMail;
use App\Models\Contact;
use App\Services\WhatsAppService;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class ContactVerificationController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    private function getStoreId()
    {
        return Store::where('owner_id', Auth::id())->value('id');
    }

    public function sendCode(Request $request)
    {
        $type = $request->input('type'); // 'phone' or 'email'
        $value = $request->input('value'); // The phone number or email address
        $contactId = $request->input('contact_id'); // Optional for existing contacts

        if (!$value) {
            return response()->json(['success' => false, 'message' => 'يرجى إدخال البيانات أولاً']);
        }

        $code = rand(100000, 999999);
        $storeId = $this->getStoreId();

        if ($type === 'phone') {
            $message = "رمز التحقق الخاص بك هو: {$code}";
            $sent = $this->whatsapp->send($value, $message, $storeId);
            
            if ($sent) {
                if ($contactId) {
                    Contact::where('id', $contactId)->update(['phone_verification_code' => $code]);
                } else {
                    Session::put("verify_phone_{$value}", $code);
                }
                return response()->json(['success' => true, 'message' => 'تم إرسال رمز التحقق إلى واتساب']);
            }
            return response()->json(['success' => false, 'message' => 'فشل إرسال الرسالة، تأكد من اتصال واتساب']);
        } else {
            try {
                Mail::to($value)->send(new VerificationCodeMail($code));
                if ($contactId) {
                    Contact::where('id', $contactId)->update(['email_verification_code' => $code]);
                } else {
                    Session::put("verify_email_{$value}", $code);
                }
                return response()->json(['success' => true, 'message' => 'تم إرسال رمز التحقق إلى البريد الإلكتروني']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل إرسال البريد الإلكتروني: ' . $e->getMessage()]);
            }
        }
    }

    public function verifyCode(Request $request)
    {
        $type = $request->input('type');
        $value = $request->input('value');
        $code = $request->input('code');
        $contactId = $request->input('contact_id');

        if ($contactId) {
            $contact = Contact::find($contactId);
            $storedCode = ($type === 'phone') ? $contact->phone_verification_code : $contact->email_verification_code;
            
            if ($code == $storedCode) {
                if ($type === 'phone') {
                    $contact->update(['phone_verified_at' => now(), 'phone_verification_code' => null]);
                } else {
                    $contact->update(['email_verified_at' => now(), 'email_verification_code' => null]);
                }
                return response()->json(['success' => true, 'message' => 'تم التحقق بنجاح']);
            }
        } else {
            $sessionKey = "verify_{$type}_{$value}";
            $storedCode = Session::get($sessionKey);
            
            if ($code == $storedCode) {
                Session::put("{$sessionKey}_verified", true);
                return response()->json(['success' => true, 'message' => 'تم التحقق بنجاح (سيتم حفظ الحالة عند حفظ جهة الاتصال)']);
            }
        }

        return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح']);
    }
}
