<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;

class WhatsAppController extends Controller
{
    protected $whatsapp;

    public function __construct(\App\Services\WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    private function getStoreId()
    {
        return Store::where('owner_id', Auth::id())->value('id');
    }

    public function index()
    {
        return view('store_owner.settings.whatsapp');
    }

    public function getStatus()
    {
        $storeId = $this->getStoreId();
        // نمرر فقط الـ ID لأن السيرفس يضيف البادئة تلقائياً
        return response()->json($this->whatsapp->getStatus($storeId));
    }

    public function logout()
    {
        $storeId = $this->getStoreId();
        $reset = $this->whatsapp->logout($storeId);
        
        return response()->json(['success' => $reset]);
    }
}
