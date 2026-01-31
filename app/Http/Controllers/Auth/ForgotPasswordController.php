<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(\Illuminate\Http\Request $request)
    {
        $this->validateEmail($request);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendResetLinkFailedResponse($request, \Illuminate\Support\Facades\Password::INVALID_USER);
        }

        // 1. Generate 6-digit Code
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // 2. Store in DB
        \App\Models\PasswordResetCode::updateOrCreate(
            ['email' => $user->email],
            ['code' => $code, 'created_at' => now()]
        );

        // 3. Send WhatsApp
        try {
            $phone = null;
            if ($user->store) {
                $phone = $user->store->phone ?? $user->store->phone_number;
            }
            
            if ($phone) {
                $message = "🔐 *رمز التحقق*\n\n" .
                           "رمز استعادة كلمة المرور الخاص بك هو:\n" .
                           "*" . $code . "*\n\n" .
                           "لا تشارك هذا الرمز مع أحد.";

                $waService = new \App\Services\WhatsAppService();
                $waService->send($phone, $message, $user->store_id);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("WhatsApp OTP Error: " . $e->getMessage());
        }

        // 4. Send Email (OTP View)
        try {
            \Illuminate\Support\Facades\Mail::send('emails.otp_code', ['code' => $code], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('رمز التحقق لاستعادة كلمة المرور');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Email OTP Error: " . $e->getMessage());
        }

        // 5. Redirect to Verification Page with Email (stored in session to avoid URL tampering if possible, or query param)
        // We will pass encrypted email to the route
        return redirect()->route('password.verify', ['email' => $request->email]);
    }
}
