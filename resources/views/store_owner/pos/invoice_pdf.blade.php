<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $arabicService->shape(__('simplified_tax_invoice')) }} / Simplified Tax Invoice #{{ $sale->id }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}; 
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}; 
            font-size: 12px; 
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #ddd; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header h1 { margin: 5px 0; font-size: 24px; color: #333; }
        .store-info { font-size: 12px; color: #555; margin-bottom: 10px; }
        .meta-table { width: 100%; margin-bottom: 20px; direction: rtl; }
        .meta-table td { padding: 3px; vertical-align: top; text-align: right; }
        
        /* Table Styles with Explicit RTL Support */
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            font-size: 12px; 
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}; /* Force RTL on table */
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 5px; 
            text-align: center; 
        }
        /* Right align the first column (Product) in visual RTL (which is logically the 'text' column) */
        .table td.text-start { text-align: right !important; }
        
        .table th { background-color: #f8f9fa; color: #333; font-weight: bold; }
        
        .total-area { 
            width: 40%; 
            margin-right: auto; /* Push to left in RTL */
            margin-left: 0; 
            text-align: left; /* Align text to left inside the box if needed, or right? standard is labels right, values left */
        }
        /* Actually for totals, we want label right, value left. Let's make rows flex-like */
        .total-row { 
            padding: 5px 0; 
            border-bottom: 1px dashed #eee; 
        }
        .total-row span:first-child { float: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}; font-weight: bold; }
        .total-row span:last-child { float: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}; }
        .clearfix { clear: both; }

        .total-row.final { 
            border-top: 2px solid #333; 
            border-bottom: none; 
            font-weight: bold; 
            font-size: 16px; 
            margin-top: 10px; 
            padding-top: 10px; 
        }

        .footer { 
            margin-top: 20px; 
            text-align: center; 
            font-size: 10px; 
            color: #777; 
            border-top: 1px solid #eee; 
            padding-top: 5px; 
        }
        .logo { max-height: 80px; margin-bottom: 10px; }
        
        /* Official marks container */
        .marks-container { 
            width: 100%; 
            margin-top: 20px; 
            text-align: center;
            page-break-inside: avoid;
        }
        .stamp-box { 
            display: inline-block;
            vertical-align: top;
            width: 45%;
            text-align: center; 
            opacity: 0.8; 
            page-break-inside: avoid;
        }
        .signature-box { 
            display: inline-block;
            vertical-align: top;
            width: 45%;
            text-align: center; 
            opacity: 0.8; 
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    @php
        // Helper to convert image to Base64
        $base64Image = function($path) {
            if (!$path) return null;
            $clean = str_replace(['public/', 'storage/'], '', $path);
            
            // Try explicit storage path first
            $possiblePaths = [
                storage_path('app/public/' . $clean),
                public_path('storage/' . $clean),
                public_path($clean)
            ];

            foreach ($possiblePaths as $fullPath) {
                if (file_exists($fullPath)) {
                    $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($fullPath);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
            return null;
        };

        $logo = $base64Image($store->logo_path ?? $store->logo);
        $stamp = $base64Image($store->stamp_path);
        $signature = $base64Image($store->signature_path);
        $baseCurrency = \App\Models\Currency::find($store->base_currency_id);
        $foreignPayments = $sale->payments->filter(function($p) use ($store) {
            return $p->currency_id && $p->currency_id != $store->base_currency_id;
        });
    @endphp

    <div class="header">
        @if($logo)
            <img src="{{ $logo }}" class="logo">
        @endif
        
        {{-- QR Code Injection --}}
        @inject('qrService', 'App\Services\ZatcaQrService')
        @php
            $qrData = $qrService->generate(
                $store->name,
                $store->tax_number ?? '000000000000000',
                $sale->created_at->toIso8601String(),
                $sale->total,
                $sale->tax ?? 0
            );
        @endphp
        @if($qrData)
            <img src="{{ $qrData }}" style="position: absolute; left: 10px; top: 10px; width: 80px; height: 80px;">
        @endif

        <h1 style="margin: 0;">{{ $arabicService->shape($store->name) }}</h1>
        <div class="store-info" style="margin-bottom: 5px;">
            {{ $arabicService->shape($store->address) }} <br>
            @if($store->phone_number) {{ $store->phone_number }} {{ $arabicService->shape(__('phone') . ':') }} / Phone: @endif | 
            @if($store->tax_number) {{ $store->tax_number }} {{ $arabicService->shape(__('vat_no') . ':') }} / VAT No: @endif
        </div>
        <h2 style="margin: 5px 0;">{{ $arabicService->shape(__('simplified_tax_invoice')) }}</h2>
        <h3 style="margin: 0; font-size: 14px; color: #555;">Simplified Tax Invoice</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="50%">
                <strong>{{ $arabicService->shape(__('invoice_number') . ':') }} / Invoice No:</strong> INV-{{ $sale->id }}<br>
                <strong>{{ $arabicService->shape(__('date') . ':') }} / Date:</strong> {{ $sale->created_at->format('Y-m-d h:i A') }}<br>
                <strong>{{ $arabicService->shape(__('cashier') . ':') }} / Cashier:</strong> {{ $arabicService->shape($sale->user->name ?? '---') }}
            </td>
            <td width="50%">
                <strong>{{ $arabicService->shape(__('customer') . ':') }} / Customer:</strong> {{ $arabicService->shape($sale->contact ? $sale->contact->contact_name : 'عميل نقدي') }}<br>
                @if($sale->contact && $sale->contact->phone) <strong>{{ $arabicService->shape(__('phone') . ':') }} / Phone:</strong> {{ $sale->contact->phone }}<br> @endif
                @if($sale->contact && $sale->contact->tax_number) <strong>{{ $arabicService->shape(__('vat_no') . ':') }} / Customer VAT:</strong> {{ $sale->contact->tax_number }} @endif
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="20%">{{ $arabicService->shape(__('total')) }} / Total</th>
                <th width="15%">{{ $arabicService->shape(__('price')) }} / Price</th>
                <th width="10%">{{ $arabicService->shape(__('quantity')) }} / Qty</th>
                <th width="50%">{{ $arabicService->shape(__('product')) }} / Product</th>
                <th width="5%">#</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ number_format($item->total, 2) }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ floatval($item->quantity) }}</td>
                <td>{{ $arabicService->shape(optional($item->product)->name ?? '---') }}</td>
                <td>{{ $index + 1 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-inside: avoid;">
        <div class="total-area">
            <div class="total-row">
                <span>{{ $arabicService->shape(__('subtotal') . ':') }} / Subtotal:</span>
                <span>{{ number_format(($sale->total + $sale->discount) - ($sale->tax ?? 0), 2) }}</span>
                <div class="clearfix"></div>
            </div>
            @if($sale->discount > 0)
            <div class="total-row" style="color: #c0392b;">
                <span>{{ $arabicService->shape(__('discount') . ':') }} / Discount:</span>
                <span>-{{ number_format($sale->discount, 2) }}</span>
                <div class="clearfix"></div>
            </div>
            @endif
            <div class="total-row">
                <span>{{ $arabicService->shape(__('vat_15') . ':') }} / VAT (15%):</span>
                <span>{{ number_format($sale->tax ?? 0, 2) }}</span>
                <div class="clearfix"></div>
            </div>
            <div class="total-row final">
                <span>{{ $arabicService->shape(__('final_total') . ':') }} / Total:</span>
                <span>{{ number_format($sale->total, 2) }} {{ optional($baseCurrency)->code ?: ($store->currency ?? 'SAR') }}</span>
                <div class="clearfix"></div>
            </div>

            @if($foreignPayments->count() > 0)
            <div style="background: #f8f9fa; padding: 5px; border-radius: 4px; margin-top: 5px;">
                <div style="font-size: 10px; font-weight: bold; margin-bottom: 3px; border-bottom: 1px solid #ddd; padding-bottom: 2px;">
                    {{ $arabicService->shape('تفاصيل العملات:') }} / Currency Details:
                </div>
                @foreach($foreignPayments as $fp)
                <div class="total-row">
                    <span style="font-size: 11px;">{{ $arabicService->shape($fp->currency->name ?? $fp->currency->code) }}:</span>
                    <span style="font-size: 11px;">{{ number_format($fp->amount_in_foreign_currency, 2) }} {{ $fp->currency->code }} (Rate: {{ $fp->exchange_rate }})</span>
                    <div class="clearfix"></div>
                </div>
                @endforeach
            </div>
            @endif
            <div class="total-row">
                <span>{{ $arabicService->shape(__('paid') . ':') }} / Paid:</span>
                <span>{{ number_format($sale->paid, 2) }} {{ optional($baseCurrency)->code }}</span>
                <div class="clearfix"></div>
            </div>
            @if($sale->due > 0.01)
            <div class="total-row" style="color: #d35400;">
                <span>{{ $arabicService->shape(__('due') . ':') }} / Due:</span>
                <span>{{ number_format($sale->due, 2) }} {{ optional($baseCurrency)->code }}</span>
                <div class="clearfix"></div>
            </div>
            @endif
        </div>

        <div class="footer" style="padding-top: 5px; margin-top: 10px;">
            <div style="page-break-inside: avoid;">
                @if($sale->show_iban && $store->iban)
                    <div style="margin-bottom: 10px; padding: 10px; border: 1px dashed #ccc; display: inline-block; text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}; width: 85%; direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};">
                        <strong style="font-size: 13px; text-decoration: underline;">{{ $arabicService->shape(__('bank_payment_details') . ':') }}</strong><br>
                        <div style="margin-top: 3px;">
                            {{ $arabicService->shape($store->iban_bank_name) }} {{ $arabicService->shape(__('bank') . ':') }} 
                            @if($store->bank_country) - {{ $arabicService->shape($store->bank_country) }} @endif <br>
                            {{ $arabicService->shape($store->bank_account_holder) }} {{ $arabicService->shape(__('account_holder') . ':') }} <br>
                            <span dir="ltr" style="font-weight: bold;">TR{{ $store->iban }}</span> {{ $arabicService->shape('IBAN:') }} 
                        </div>
                        <div style="margin-top: 5px; color: #2980b9; font-weight: bold; font-size: 10px;">
                            ({{ $store->phone_number }}) {{ $arabicService->shape(__('whatsapp_payment_notice')) }} 
                        </div>
                    </div>
                @endif

                @if(($sale->show_stamp && $stamp) || ($sale->show_signature && $signature))
                <div class="marks-container" style="margin-top: 10px;">
                    @if($sale->show_stamp && $stamp)
                    <div class="stamp-box">
                        <div style="margin-bottom: 3px; font-weight: bold; text-decoration: underline;">{{ $arabicService->shape(__('store_stamp')) }}</div>
                        <img src="{{ $stamp }}" width="110" style="transform: rotate(-10deg);">
                    </div>
                    @endif

                    @if($sale->show_signature && $signature)
                    <div class="signature-box">
                        <div style="margin-bottom: 3px; font-weight: bold; text-decoration: underline;">{{ $arabicService->shape(__('manager_signature')) }}</div>
                        <img src="{{ $signature }}" width="110">
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <div style="margin-top: 15px;">
                <p style="margin: 2px 0;">{{ $arabicService->shape(__('thank_you_for_business')) }}</p>
                @if($store->website) <p style="margin: 2px 0;">{{ $store->website }}</p> @endif
            </div>
        </div>
    </div>
</body>
</html>
