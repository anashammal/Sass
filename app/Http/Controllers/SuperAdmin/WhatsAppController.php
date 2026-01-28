<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;

class WhatsAppController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function getStatus()
    {
        // null يعني جلسة النظام (System)
        return response()->json($this->whatsapp->getStatus(null));
    }

    public function logout()
    {
        $this->whatsapp->logout(null);
        return response()->json(['success' => true]);
    }
}