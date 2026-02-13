<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $arabicService->shape('فاتورة ضريبية') }} #{{ $sale->id }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            direction: rtl; 
            text-align: right; 
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
            direction: rtl; /* Force RTL on table */
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
        .total-row span:first-child { float: right; font-weight: bold; }
        .total-row span:last-child { float: left; }
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
            @if($store->phone_number) {{ $store->phone_number }} {{ $arabicService->shape('هاتف:') }} @endif | 
            @if($store->tax_number) {{ $store->tax_number }} {{ $arabicService->shape('الرقم الضريبي:') }} @endif
        </div>
        <h2 style="margin: 5px 0;">{{ $arabicService->shape('فاتورة ضريبية') }}</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td width="50%">
                INV-{{ $sale->id }} <strong>{{ $arabicService->shape('رقم الفاتورة:') }}</strong><br>
                {{ $sale->created_at->format('Y-m-d h:i A') }} <strong>{{ $arabicService->shape('التاريخ:') }}</strong><br>
                {{ $arabicService->shape($sale->user->name ?? '---') }} <strong>{{ $arabicService->shape('بواسطة:') }}</strong>
            </td>
            <td width="50%">
                {{ $arabicService->shape($sale->contact ? $sale->contact->contact_name : 'عميل نقدي') }} <strong>{{ $arabicService->shape('العميل:') }}</strong><br>
                @if($sale->contact && $sale->contact->phone) {{ $sale->contact->phone }} <strong>{{ $arabicService->shape('الهاتف:') }}</strong><br> @endif
                @if($sale->contact && $sale->contact->tax_number) {{ $sale->contact->tax_number }} <strong>{{ $arabicService->shape('الرقم الضريبي:') }}</strong> @endif
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="25%">{{ $arabicService->shape('الإجمالي') }}</th>
                <th width="15%">{{ $arabicService->shape('السعر') }}</th>
                <th width="10%">{{ $arabicService->shape('الكمية') }}</th>
                <th width="45%">{{ $arabicService->shape('المنتج') }}</th>
                <th width="5%">#</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ number_format($item->total, 2) }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ floatval($item->quantity) }}</td>
                <td class="text-start">{{ $arabicService->shape($item->product->name_ar ?? 'منتج محذوف') }}</td>
                <td>{{ $index + 1 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-inside: avoid;">
        <div class="total-area">
            <div class="total-row">
                <span>{{ $arabicService->shape('المجموع الفرحي:') }}</span>
                <span>{{ number_format($sale->total + $sale->discount, 2) }}</span>
                <div class="clearfix"></div>
            </div>
            @if($sale->discount > 0)
            <div class="total-row" style="color: #c0392b;">
                <span>{{ $arabicService->shape('الخصم:') }}</span>
                <span>-{{ number_format($sale->discount, 2) }}</span>
                <div class="clearfix"></div>
            </div>
            @endif
            <div class="total-row final">
                <span>{{ $arabicService->shape('الإجمالي النهائي:') }}</span>
                <span>{{ number_format($sale->total, 2) }} {{ $store->currency ?? 'SAR' }}</span>
                <div class="clearfix"></div>
            </div>
            <div class="total-row">
                <span>{{ $arabicService->shape('المدفوع:') }}</span>
                <span>{{ number_format($sale->paid, 2) }}</span>
                <div class="clearfix"></div>
            </div>
            @if($sale->due > 0)
            <div class="total-row" style="color: #d35400;">
                <span>{{ $arabicService->shape('المتبقي (آجل):') }}</span>
                <span>{{ number_format($sale->due, 2) }}</span>
                <div class="clearfix"></div>
            </div>
            @endif
        </div>

        <div class="footer" style="padding-top: 5px; margin-top: 10px;">
            <div style="page-break-inside: avoid;">
                @if($sale->show_iban && $store->iban)
                    <div style="margin-bottom: 10px; padding: 10px; border: 1px dashed #ccc; display: inline-block; text-align: right; width: 85%; direction: rtl;">
                        <strong style="font-size: 13px; text-decoration: underline;">{{ $arabicService->shape('تفاصيل الدفع البنكي:') }}</strong><br>
                        <div style="margin-top: 3px;">
                            {{ $arabicService->shape($store->iban_bank_name) }} {{ $arabicService->shape('البنك:') }} 
                            @if($store->bank_country) - {{ $arabicService->shape($store->bank_country) }} @endif <br>
                            {{ $arabicService->shape($store->bank_account_holder) }} {{ $arabicService->shape('صاحب الحساب:') }} <br>
                            <span dir="ltr" style="font-weight: bold;">TR{{ $store->iban }}</span> {{ $arabicService->shape('IBAN:') }} 
                        </div>
                        <div style="margin-top: 5px; color: #2980b9; font-weight: bold; font-size: 10px;">
                            ({{ $store->phone_number }}) {{ $arabicService->shape('في حال أي سداد، يرجى إرسال إشعار السداد لرقم الواتساب') }} 
                        </div>
                    </div>
                @endif

                @if(($sale->show_stamp && $stamp) || ($sale->show_signature && $signature))
                <div class="marks-container" style="margin-top: 10px;">
                    @if($sale->show_stamp && $stamp)
                    <div class="stamp-box">
                        <div style="margin-bottom: 3px; font-weight: bold; text-decoration: underline;">{{ $arabicService->shape('ختم المتجر') }}</div>
                        <img src="{{ $stamp }}" width="110" style="transform: rotate(-10deg);">
                    </div>
                    @endif

                    @if($sale->show_signature && $signature)
                    <div class="signature-box">
                        <div style="margin-bottom: 3px; font-weight: bold; text-decoration: underline;">{{ $arabicService->shape('توقيع المسؤول') }}</div>
                        <img src="{{ $signature }}" width="110">
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <div style="margin-top: 15px;">
                <p style="margin: 2px 0;">{{ $arabicService->shape('شكرًا لتعاملكم معنا!') }}</p>
                @if($store->website) <p style="margin: 2px 0;">{{ $store->website }}</p> @endif
            </div>
        </div>
    </div>
</body>
</html>
