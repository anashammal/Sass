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
            return response()->json(['success' => false, 'message' => __('please_enter_data_first')]);
        }

        $code = rand(100000, 999999);
        $storeId = $this->getStoreId();

        if ($type === 'phone') {
            $message = __('your_verification_code_is', ['code' => $code]);
            $sent = $this->whatsapp->send($value, $message, $storeId);
            
            if ($sent) {
                if ($contactId) {
                    Contact::where('id', $contactId)->update(['phone_verification_code' => $code]);
                } else {
                    Session::put("verify_phone_{$value}", $code);
                }
                return response()->json(['success' => true, 'message' => __('verification_code_sent_to_whatsapp')]);
            }
            return response()->json(['success' => false, 'message' => __('whatsapp_send_failed')]);
        } else {
            try {
                Mail::to($value)->send(new VerificationCodeMail($code));
                if ($contactId) {
                    Contact::where('id', $contactId)->update(['email_verification_code' => $code]);
                } else {
                    Session::put("verify_email_{$value}", $code);
                }
                return response()->json(['success' => true, 'message' => __('verification_code_sent_to_email')]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => __('email_send_failed') . ': ' . $e->getMessage()]);
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
                return response()->json(['success' => true, 'message' => __('verification_success')]);
            }
        } else {
            $sessionKey = "verify_{$type}_{$value}";
            $storedCode = Session::get($sessionKey);
            
            if ($code == $storedCode) {
                Session::put("{$sessionKey}_verified", true);
                return response()->json(['success' => true, 'message' => __('verification_success_session')]);
            }
        }

        return response()->json(['success' => false, 'message' => __('invalid_verification_code')]);
    }
}
