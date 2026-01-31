<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ReportMail;
use App\Models\Store;

class EmailController extends Controller
{
    private function getStoreId()
    {
        return Store::where('owner_id', Auth::id())->value('id');
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $mediaUrl = $request->input('media_url');
        $filename = $request->input('filename', 'report.pdf');
        
        $attachmentPath = null;
        if ($mediaUrl) {
            // تحويل الرابط إلى مسار محلي
            $baseAsset = asset('');
            $relativePath = str_ireplace($baseAsset, '', $mediaUrl);
            $attachmentPath = public_path($relativePath);
            
            // تصحيح المسار في حال وجود / في البداية
            if (!file_exists($attachmentPath)) {
                $attachmentPath = public_path(ltrim($relativePath, '/'));
            }
        }

        try {
            Mail::to($request->email)->send(new ReportMail($request->subject, $request->message, $attachmentPath, $filename));
            
            // حذف الملف المؤقت إذا كان تقريراً (بناءً على طلب المستخدم في الواتساب سابقاً)
            if ($attachmentPath && str_contains($attachmentPath, 'temp_reports') && file_exists($attachmentPath)) {
                @unlink($attachmentPath);
            }

            return response()->json(['success' => true, 'message' => 'تم إرسال البريد الإلكتروني بنجاح!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'فشل الإرسال: ' . $e->getMessage()], 500);
        }
    }
}
