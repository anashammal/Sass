<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayPalService;
use Illuminate\Support\Facades\Log;

class PayPalController extends Controller
{
    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Create PayPal Order
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'string|max:3'
        ]);

        $order = $this->paypalService->createOrder(
            $request->amount, 
            $request->currency ?? 'USD'
        );

        if ($order) {
            return response()->json($order);
        }

        return response()->json(['error' => 'Failed to create PayPal order'], 500);
    }

    /**
     * Capture PayPal Order
     */
    public function captureOrder(Request $request)
    {
        $request->validate([
            'orderID' => 'required|string'
        ]);

        $capture = $this->paypalService->captureOrder($request->orderID);

        if ($capture && isset($capture['status']) && $capture['status'] === 'COMPLETED') {
            return response()->json([
                'success' => true,
                'details' => $capture
            ]);
        }

        return response()->json(['error' => 'Failed to capture PayPal order'], 500);
    }
}
