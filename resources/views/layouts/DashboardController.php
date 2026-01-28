<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // الخطأ كان هنا: storeowner.dashboard
        // التصحيح هو: store_owner.dashboard (بناءً على اسم المجلد لديك)
        return view('store_owner.dashboard');
    }
}