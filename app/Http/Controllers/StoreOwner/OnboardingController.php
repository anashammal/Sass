<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    /**
     * عرض نموذج إكمال البيانات.
     */
    public function index()
    {
        // التصحيح: إضافة $store قبل علامة =
        $store = Auth::user()->store; 
        
        // التصحيح: إضافة $subdomain قبل علامة =
        $subdomain = request()->route('subdomain'); 
        
        $countries = [
            'TR' => 'تركيا',
            'SA' => 'المملكة العربية السعودية',
            'AE' => 'الإمارات العربية المتحدة',
            'EG' => 'مصر',
            'JO' => 'الأردن',
            'SY' => 'سوريا',
        ];

        return view('store_owner.onboarding.setup', compact('store', 'countries', 'subdomain'));
    }

    /**
     * حفظ بيانات الإعدادات.
     */
    public function update(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
            'city' => 'required|string',
            'phone_country_code' => 'required|string',
            'phone_number' => 'required|numeric',
            'iban' => 'required|string',
        ]);

        $store = Auth::user()->store;
        
        $store->update([
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'phone_country_code' => $request->phone_country_code,
            'phone_number' => $request->phone_number,
            'iban' => $request->iban,
            'bank_country' => $request->bank_country,
            'onboarding_complete' => true, 
        ]);

        return redirect()->route('store.dashboard', ['subdomain' => $store->subdomain])
             ->with('success', 'تم إكمال إعدادات المتجر بنجاح!');
    }
}