<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VerificationController extends Controller
{
    public function showVerifyForm(Request $request)
    {
        return view('auth.verify_code', ['email' => $request->email]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric'
        ]);

        $record = PasswordResetCode::where('email', $request->email)
                    ->where('code', $request->code)
                    // ->where('created_at', '>=', Carbon::now()->subMinutes(15)) // Check expiry if needed
                    ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.']);
        }

        // Code is valid - Show reset form
        // We will pass the code as a "token" to the reset form to allow submitting it
        return view('auth.passwords.reset_otp', ['email' => $request->email, 'code' => $request->code]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required',
            'password' => [
                'required', 
                'confirmed', 
                'min:8',
                'regex:/[a-z]/',      // at least one lowercase letter
                'regex:/[A-Z]/',      // at least one uppercase letter
                'regex:/[0-9]/',      // at least one digit
                'regex:/[@$!%*#?&]/', // at least one special character
            ],
        ], [
            'password.regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير، حرف صغير، رقم، ورمز خاص.',
            'password.min' => 'يجب أن تكون كلمة المرور 8 خانات على الأقل.'
        ]);

        // Re-verify code to prevent bypassing step 1 via direct POST
        $record = PasswordResetCode::where('email', $request->email)
                    ->where('code', $request->code)
                    ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'رمز التحقق غير صالح.']);
        }

        // Update Password
        $user = User::where('email', $request->email)->first();
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->setRememberToken(\Illuminate\Support\Str::random(60));
        $user->save();

        // Delete Code
        $record->delete();

        // Login User
        // \Illuminate\Support\Facades\Auth::login($user); // Optional: auto login

        return redirect()->route('login')->with('status', 'تم تغيير كلمة المرور بنجاح!');
    }
}
