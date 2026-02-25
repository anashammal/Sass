<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KuveytTurkSanalPosService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TestKuveytController extends Controller
{
    protected $kuveytService;

    public function __construct(KuveytTurkSanalPosService $kuveytService)
    {
        $this->kuveytService = $kuveytService;
    }

    public function index()
    {
        return view('hidden_payment');
    }

    public function pay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'card_number' => 'required',
            'expiry' => 'required',
            'cvv' => 'required',
            'card_holder_name' => 'required',
        ]);

        // Parse expiry MM / YY
        $parts = explode('/', $request->expiry);
        $month = trim($parts[0] ?? '');
        $year = trim($parts[1] ?? '');
        $year = trim($parts[1] ?? '');
        // Some Kuveyt Turk XML gateways demand 2-digit (YY), others 4-digit (YYYY). Let's try 4 (YYYY) since 2 (YY) failed.
        if (strlen($year) == 2) $year = '20' . $year;

        $data = [
            'amount' => $request->amount,
            'card_number' => str_replace(' ', '', $request->card_number),
            'expire_month' => $month,
            'expire_year' => $year,
            'cvv' => $request->cvv,
            'card_holder_name' => $request->card_holder_name,
            'order_id' => 'ORD-' . time() . '-' . Str::random(5),
        ];

        $result = $this->kuveytService->startPayment($data);

        // Sanal POS returns HTML form to auto-submit
        if ($result['Success'] && isset($result['HtmlContent'])) {
            return response($result['HtmlContent']);
        }

        return back()->with('error', 'Payment Initiation Failed: ' . ($result['ResponseMessage'] ?? 'Unknown Error'));
    }

    public function callback(Request $request)
    {
        Log::info('Kuveyt Turk Callback:', $request->all());
        
        $result = $this->kuveytService->handleCallback($request);

        if ($result['Success']) {
            return "<h1>Payment Successful! </h1><p>Order ID: {$result['OrderId']}</p><p>Ref: {$result['Ref']}</p>";
        }

        return "<h1>Payment Failed</h1><p>{$result['Message']}</p><p>Code: ".($result['Code'] ?? 'N/A')."</p>";
    }
}
