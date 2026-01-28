<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tech-Sys') }}</title>

    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- الخط --}}
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <style>
/* تصغير واجهة الموقع بالكامل لتظهر بشكل أرتب */
    :root {
        --base-scale: 0.90; /* هذا الرقم يعادل تقريبا 90% زووم */
    }

    body {
        /* الطريقة الأولى والأسهل (تعمل على أغلب المتصفحات الحديثة) */
        zoom: 0.90; 
        
        /* تحسينات إضافية للخطوط عند التصغير */
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* إصلاح مشاكل الـ Select2 (القوائم المنسدلة) عند التصغير */
    .select2-container .select2-selection--single {
        height: 38px !important; /* تثبيت الارتفاع */
        font-size: 14px !important;
    }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px !important;
        padding-top: 5px !important;
    }

    /* إصلاح خاص لمنطقة الدفع لتظهر كاملة دائماً */
    .pos-right {
        display: flex;
        flex-direction: column;
        height: 100%; /* ضمان أخذ الطول الكامل */
        overflow-y: auto; /* السماح بالسكرول إذا الشاشة صغيرة جدا */
    }
    
    /* تحسين منطقة طرق الدفع */
    #paymentRowsContainer {
        max-height: 250px !important; /* زيادة المساحة المتاحة */
        overflow-y: auto;
        padding-right: 5px;
    }
    
    /* تصغير الأزرار والحقول قليلاً لتتناسب */
    .form-control, .form-select, .btn {
        font-size: 0.9rem !important;
    }
    
    /* تنسيق خاص للطباعة (لإلغاء التصغير عند الطباعة) */
    @media print {
        body { zoom: 1; }
    }
        /* --- تخصيصات الثيم --- */
        body { font-family: 'Nunito', sans-serif; background-color: #f3f4f6; overflow-x: hidden; }

        /* القائمة الجانبية */
        .sidebar { background-color: #342d50; min-height: 100vh; color: #fff; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: rgba(255,255,255,0.8) !important; padding: 12px 20px; border-right: 4px solid transparent; transition: all 0.2s; display: flex; align-items: center; text-decoration: none; font-size: 1rem; }
        .sidebar .nav-link i { width: 30px; font-size: 1.2rem; margin-left: 10px; text-align: center; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #4e3a88; color: #fff !important; border-right-color: #00d2d3; }
        .sidebar-heading { font-size: 0.85rem; text-transform: uppercase; color: rgba(255,255,255,0.4); padding: 15px 20px 5px; font-weight: bold; }

        /* الهيدر العلوي */
        .navbar-custom { background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 70px; z-index: 1000; }
        
        /* الشعار */
        .brand-container { display: flex; align-items: center; }
        .brand-logo img { max-height: 50px; width: auto; background-color: transparent !important; mix-blend-mode: multiply; }
        .brand-text { color: #342d50; font-weight: 800; font-size: 1.2rem; margin-right: 12px; text-decoration: none; }
        
        /* إصلاحات التوافق */
        .dropdown-menu { text-align: right; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .dropdown-item:hover { background-color: #f8f9fa; color: #4e3a88; }
        a { text-decoration: none; }
        .btn-group .btn { margin-left: 5px; }
    </style>

    <style>
        @media print {
            @page { size: landscape; margin: 5mm; }
            body { zoom: 85%; background-color: white !important; -webkit-print-color-adjust: exact !important; }
            .no-print, .btn, .pagination, .dataTables_filter, .input-group, header, footer, .sidebar, ::-webkit-scrollbar { display: none !important; }
            .table-responsive { overflow: visible !important; display: block !important; width: 100% !important; }
            table { width: 100% !important; border-collapse: collapse !important; font-size: 10px !important; }
            th, td { border: 1px solid #333 !important; padding: 4px !important; text-align: center !important; white-space: nowrap; color: #000 !important; }
            td[style*="white-space: normal"] { white-space: normal !important; font-size: 9px !important; }
            .card { border: none !important; box-shadow: none !important; }
            .card-header { display: none !important; }
            .container-fluid { width: 100% !important; padding: 0 !important; }
            .print-header { display: block !important; text-align: center; margin-bottom: 10px; border-bottom: 2px solid #000; }
        }
        .print-header { display: none; }
    </style>

    {{-- 🔥 CSS الخاص بالأرقام 🔥 --}}
    <style>
        /* التنسيق الأساسي للأرقام */
        .force-english-num,
        input[type="number"],
        input[type="tel"],
        input[type="text"].numeric-field,
        select,
        .qty, .price, .profit, .discount, .sell, .total, 
        .sub-cost, .sub-profit, .sub-sell,
        .tax, .discount-type,
        .english-num,
        [id*="TotalDisplay"],
        [id*="balance"]
        {
            font-family: 'Nunito', sans-serif !important; 
            font-weight: 800 !important;        
            text-align: center;
            direction: ltr !important;
            unicode-bidi: plaintext !important;
        }

        /* تنسيق خاص للقوائم */
        select.tax, select.discount-type {
            text-align-last: center;
        }
    </style>

</head>
<body>
    <div id="app" class="d-flex flex-column h-100">
        
        <nav class="navbar navbar-expand-md navbar-custom sticky-top">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="navbar-brand ms-2 d-flex align-items-center" href="{{ url('/') }}">
                    <div class="brand-container">
                        @auth
                            @php
                                $logoUrl = null;
                                $brandName = 'TechSys';
                                if(auth()->id() == 1) {
                                    $logoPath = \App\Models\SystemSetting::where('key', 'system_default_logo')->value('value');
                                    $logoUrl = $logoPath ? url('storage/'.$logoPath) : null;
                                    $brandName = 'لوحة الإدارة';
                                } else {
                                    $myStore = \App\Models\Store::where('owner_id', auth()->id())->first();
                                    if ($myStore) {
                                        $brandName = $myStore->name;
                                        if ($myStore->logo_path) $logoUrl = url('storage/' . $myStore->logo_path);
                                    }
                                }
                            @endphp

                            @if($logoUrl)
                                <div class="brand-logo"><img src="{{ $logoUrl }}?t={{ time() }}" alt="Logo"></div>
                            @else
                                <i class="fa fa-layer-group fa-2x text-primary ms-2"></i>
                            @endif
                            
                            <span class="brand-text d-none d-sm-inline">{{ $brandName }}</span>
                        @else
                            <span class="brand-text">TechSys</span>
                        @endauth
                    </div>
                </a>
{{-- ========================================================= --}}
{{-- 🕒 ساعة النظام الاحترافية (Smart Watch V3) --}}
{{-- ========================================================= --}}
@auth
    @php
        $store = auth()->user()->store;
        $sysTz = $store->timezone ?? 'Asia/Riyadh';
        $clockType = $store->clock_type ?? 'digital';
        $clockTheme = $store->clock_theme ?? 'galaxy';
        
        $citiesNames = [ 
            'Asia/Riyadh' => 'مكة المكرمة', 'Africa/Cairo' => 'القاهرة', 'Asia/Dubai' => 'دبي', 
            'Europe/Istanbul' => 'إسطنبول', 'Asia/Amman' => 'عمّان', 'Asia/Baghdad' => 'بغداد',
            'UTC' => 'توقيت عالمي'
        ];
        $displayCity = $citiesNames[$sysTz] ?? explode('/', $sysTz)[1] ?? 'Local';
    @endphp

    <div class="d-none d-md-flex align-items-center ms-3 me-3 user-select-none" 
         id="system_clock_container" title="توقيت {{ $displayCity }}">

        {{-- 🅰️ النوع: عقارب (Analog) --}}
        @if($clockType == 'analog')
            <div class="analog-wrapper me-2">
                <div class="analog-face {{ $clockTheme }}">
                    {{-- العلامات --}}
                    @for($i=1; $i<=12; $i++)
                        <div class="marker m-{{$i}}"></div>
                    @endfor
                    
                    {{-- العقارب --}}
                    <div class="hand hour-hand" id="hand_h"></div>
                    <div class="hand min-hand" id="hand_m"></div>
                    <div class="hand sec-hand" id="hand_s"></div>
                    <div class="center-cap"></div>
                </div>
            </div>
            
            <div class="d-flex flex-column justify-content-center" style="line-height: 1.1;">
                <span class="fw-bold" style="font-size: 0.7rem; color: #888;">{{ $displayCity }}</span>
                <span id="analog_text_date" class="fw-bold text-dark" style="font-size: 0.75rem; font-family: 'Nunito', sans-serif;">--/--</span>
            </div>

            <style>
                .analog-wrapper { position: relative; }
                .analog-face { width: 50px; height: 50px; border-radius: 50%; position: relative; overflow: hidden; }
                .hand { position: absolute; bottom: 50%; left: 50%; transform-origin: bottom center; border-radius: 50px; z-index: 2; }
                .marker { position: absolute; background: rgba(0,0,0,0.3); width: 2px; height: 4px; left: 50%; transform-origin: 50% 25px; top: 0; margin-left: -1px; }
                
                /* توزيع العلامات */
                @for($i=1; $i<=12; $i++)
                    .m-{{$i}} { transform: rotate({{ $i * 30 }}deg); }
                @endfor

                .center-cap { position: absolute; top: 50%; left: 50%; width: 6px; height: 6px; border-radius: 50%; transform: translate(-50%, -50%); z-index: 10; }

                /* 1. Theme: Royal Gold */
                .analog-face.royal_gold { background: #111; border: 2px solid #d4af37; box-shadow: inset 0 0 5px rgba(0,0,0,0.5); }
                .royal_gold .marker { background: #d4af37; }
                .royal_gold .hour-hand { width: 4px; height: 14px; background: #d4af37; margin-left: -2px; }
                .royal_gold .min-hand { width: 2px; height: 20px; background: #fff; margin-left: -1px; }
                .royal_gold .sec-hand { width: 1px; height: 22px; background: red; margin-left: -0.5px; }
                .royal_gold .center-cap { background: #d4af37; }

                /* 2. Theme: Sport Red */
                .analog-face.sport_red { background: #f0f0f0; border: 2px solid #333; }
                .sport_red .marker { background: #333; height: 3px; }
                .sport_red .hour-hand { width: 4px; height: 12px; background: #333; margin-left: -2px; }
                .sport_red .min-hand { width: 2px; height: 18px; background: #666; margin-left: -1px; }
                .sport_red .sec-hand { width: 1px; height: 22px; background: #d32f2f; margin-left: -0.5px; }
                .sport_red .center-cap { background: #333; }

                /* 3. Theme: Ocean */
                .analog-face.ocean { background: radial-gradient(circle, #0f2027, #203a43, #2c5364); border: 2px solid #fff; }
                .ocean .marker { background: rgba(255,255,255,0.6); }
                .ocean .hour-hand { width: 3px; height: 13px; background: #fff; margin-left: -1.5px; }
                .ocean .min-hand { width: 2px; height: 19px; background: #00d2ff; margin-left: -1px; }
                .ocean .sec-hand { width: 1px; height: 24px; background: #a8c0ff; margin-left: -0.5px; }
                .ocean .center-cap { background: #fff; }
            </style>

        {{-- 🅱️ النوع: رقمي (Digital) --}}
        @else 
            <div class="digital-container {{ $clockTheme }} d-flex align-items-center">
                <div class="time-box">
                    <span id="live_sys_time">--:--:--</span>
                    <span id="live_sys_ampm" class="ampm">AM</span>
                </div>
                <div class="date-box ms-2 ps-2 border-start">
                    <div class="city">{{ $displayCity }}</div>
                    <div id="live_sys_date" class="date">--/--</div>
                </div>
            </div>

            <style>
                .digital-container { padding: 4px 15px; border-radius: 50px; font-family: 'Nunito', sans-serif; transition: all 0.3s; min-width: 160px; }
                .time-box { font-size: 1.25rem; font-weight: 800; letter-spacing: 1.5px; line-height: 1; display: flex; align-items: baseline; }
                .ampm { font-size: 0.6rem; font-weight: 700; margin-left: 4px; text-transform: uppercase; opacity: 0.8; }
                .city { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; opacity: 0.7; margin-bottom: 2px; }
                .date { font-size: 0.75rem; font-weight: 700; line-height: 1; }

                /* 1. Theme: Galaxy (تدرج بنفسجي وأزرق) */
                .galaxy { background: linear-gradient(135deg, #4158D0 0%, #C850C0 100%); color: #fff; box-shadow: 0 4px 15px rgba(200, 80, 192, 0.25); border: 1px solid rgba(255,255,255,0.2); }
                .galaxy .border-start { border-color: rgba(255,255,255,0.3) !important; }
                .galaxy .time-box { text-shadow: 0 2px 4px rgba(0,0,0,0.1); }

                /* 2. Theme: HUD (Cyber - أسود وأخضر) */
                .hud { background: #0a0a0a; border: 1px solid #00ff41; color: #00ff41; box-shadow: 0 0 8px rgba(0,255,65,0.15); font-family: 'Courier New', monospace; border-radius: 8px; }
                .hud .border-start { border-color: rgba(0,255,65,0.4) !important; }
                .hud .time-box { letter-spacing: 0px; text-shadow: 0 0 5px rgba(0,255,65,0.6); font-family: 'Courier New', monospace; }
                .hud .date { font-family: 'Courier New', monospace; }

                /* 3. Theme: Ultra (برتقالي وأسود - Apple Style) */
                .modern_white { background: #1c1c1e; border: 1px solid #333; color: #ff9f0a; border-radius: 12px; }
                .modern_white .time-box { color: #fff; font-family: -apple-system, BlinkMacSystemFont, sans-serif; font-weight: 700; }
                .modern_white .ampm { color: #ff9f0a; }
                .modern_white .city { color: #8e8e93; }
                .modern_white .date { color: #ff9f0a; }
                .modern_white .border-start { border-color: #3a3a3c !important; }
            </style>
        @endif
    </div>

    {{-- المحرك (Script) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const targetTz = "{{ $sysTz }}";
            const type = "{{ $clockType }}";

            function updateSystemClock() {
                const now = new Date();
                const localTimeStr = now.toLocaleString("en-US", { timeZone: targetTz });
                const storeTime = new Date(localTimeStr);

                if (type === 'analog') {
                    const seconds = storeTime.getSeconds();
                    const minutes = storeTime.getMinutes();
                    const hours = storeTime.getHours();
                    
                    const secDeg = ((seconds / 60) * 360);
                    const minDeg = ((minutes / 60) * 360) + ((seconds/60)*6);
                    const hourDeg = ((hours / 12) * 360) + ((minutes/60)*30);

                    const sHand = document.getElementById('hand_s');
                    const mHand = document.getElementById('hand_m');
                    const hHand = document.getElementById('hand_h');
                    
                    if(sHand) sHand.style.transform = `rotate(${secDeg}deg)`;
                    if(mHand) mHand.style.transform = `rotate(${minDeg}deg)`;
                    if(hHand) hHand.style.transform = `rotate(${hourDeg}deg)`;
                    
                    const dateEl = document.getElementById('analog_text_date');
                    if(dateEl) dateEl.innerText = new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(storeTime);

                } else {
                    const timeEl = document.getElementById('live_sys_time');
                    const ampmEl = document.getElementById('live_sys_ampm');
                    const dateEl = document.getElementById('live_sys_date');
                    
                    if(timeEl) {
                        let hours = storeTime.getHours();
                        let minutes = storeTime.getMinutes();
                        let seconds = storeTime.getSeconds();
                        let ampm = hours >= 12 ? 'PM' : 'AM';
                        
                        hours = hours % 12;
                        hours = hours ? hours : 12; 
                        minutes = minutes < 10 ? '0'+minutes : minutes;
                        seconds = seconds < 10 ? '0'+seconds : seconds;
                        
                        timeEl.innerText = hours + ':' + minutes + ':' + seconds;
                        if(ampmEl) ampmEl.innerText = ampm;
                    }
                    if(dateEl) {
                        dateEl.innerText = new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(storeTime);
                    }
                }
            }
            setInterval(updateSystemClock, 1000);
            updateSystemClock();
        });
    </script>
@endauth
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto align-items-center">
                       {{-- جرس التنبيهات الشامل (صلاحية + كمية) --}}
<li class="nav-item dropdown mx-2">
    <a class="nav-link text-secondary position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell fa-lg {{ isset($expiryAlerts) && ($expiryAlerts['expired']->count() > 0 || $expiryAlerts['near']->count() > 0 || $expiryAlerts['low_stock']->count() > 0) ? 'text-danger fa-shake' : '' }}"></i>
        
        @php
            $totalAlerts = 0;
            if(isset($expiryAlerts)) {
                $totalAlerts = $expiryAlerts['expired']->count() + $expiryAlerts['near']->count() + $expiryAlerts['low_stock']->count();
            }
        @endphp

        @if($totalAlerts > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                {{ $totalAlerts }}
            </span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0" style="width: 340px; max-height: 450px; overflow-y: auto; z-index: 9999;">
        <div class="p-2 border-bottom fw-bold bg-light d-flex justify-content-between align-items-center sticky-top">
            <span>التنبيهات</span>
            @if($totalAlerts > 0)
                <small class="text-muted">{{ $totalAlerts }} إشعار</small>
            @endif
        </div>
        
        @if(isset($expiryAlerts))
            
            {{-- 1. قسم الصلاحية المنتهية (الأخطر) --}}
            @if($expiryAlerts['expired']->count() > 0)
                <div class="px-2 py-1 bg-danger text-white small fw-bold"><i class="fas fa-skull-crossbones me-1"></i> منتهي الصلاحية</div>
                @foreach($expiryAlerts['expired'] as $batch)
                    <a class="dropdown-item p-2 border-bottom bg-danger bg-opacity-10" href="{{ route('store.products.expired_manager') }}">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1" style="white-space: normal;">
                                <div class="fw-bold text-danger small">{{ $batch->product->name_ar ?? 'منتج' }}</div>
                                <small class="text-muted" style="font-size: 0.7rem">انتهى: {{ $batch->expiry_date->format('Y-m-d') }}</small>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- 2. قسم نقص المخزون --}}
            @if($expiryAlerts['low_stock']->count() > 0)
                <div class="px-2 py-1 bg-dark text-warning small fw-bold"><i class="fas fa-boxes me-1"></i> مخزون منخفض</div>
                @foreach($expiryAlerts['low_stock'] as $prod)
                    {{-- لاحظ: استخدمنا javascript:void(0) واستدعينا دالة الفتح --}}
                    <a class="dropdown-item p-2 border-bottom" href="javascript:void(0)" onclick="openLowStockModal({{ $prod->id }}, '{{ $prod->name_ar }}', {{ $prod->current_stock }}, {{ $prod->alert_quantity }})">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1" style="white-space: normal;">
                                <div class="fw-bold text-dark small">{{ $prod->name_ar }}</div>
                                <div class="d-flex justify-content-between">
                                    {{-- 🔥 هنا الإصلاح: (float) تزيل الأصفار الزائدة --}}
                                    <small class="text-danger fw-bold" style="font-size: 0.75rem">
                                        الباقي: {{ (float)$prod->current_stock }}
                                    </small>
                                    <small class="text-muted" style="font-size: 0.7rem">
                                        (حد التنبيه: {{ (float)$prod->alert_quantity }})
                                    </small>
                                </div>
                            </div>
                            <i class="fas fa-cog text-secondary ms-2"></i>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- 3. قسم قرب الانتهاء --}}
            @if($expiryAlerts['near']->count() > 0)
                <div class="px-2 py-1 bg-warning bg-opacity-25 text-dark small fw-bold"><i class="fas fa-hourglass-half me-1"></i> تنبيهات الصلاحية</div>
                @foreach($expiryAlerts['near'] as $batch)
                    @php $days = $batch->days_remaining_calculated ?? 0; @endphp
                    <a class="dropdown-item p-2 border-bottom bg-warning bg-opacity-10" href="{{ route('store.products.expired_manager') }}">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1" style="white-space: normal;">
                                <div class="fw-bold text-dark small">{{ $batch->product->name_ar ?? 'منتج' }}</div>
                                <small class="text-warning fw-bold" style="font-size: 0.7rem">
                                    بقي {{ $days }} يوم
                                </small>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- فارغ --}}
            @if($totalAlerts == 0)
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="mb-0 small">لا توجد تنبيهات حالياً.</p>
                </div>
            @endif

        @endif
    </div>
</li>
                        <li class="nav-item"><a class="nav-link text-secondary fw-bold" href="#">AR</a></li>
                        
                        @auth
                        <li class="nav-item dropdown ms-3">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center text-dark" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold ms-2" style="width: 38px; height: 38px;">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="fw-bold">{{ Auth::user()->name }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-sign-out-alt ms-2"></i> تسجيل الخروج
                                </a>
                            </div>
                        </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid flex-grow-1">
            <div class="row h-100">
                @auth
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            @if(auth()->id() == 1)
                                <li class="sidebar-heading">الإدارة العامة</li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}"><i class="fa fa-tachometer-alt"></i> لوحة التحكم</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.stores.*') ? 'active' : '' }}" href="{{ route('superadmin.stores.index') }}"><i class="fa fa-store"></i> إدارة المتاجر</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}" href="{{ route('superadmin.settings.index') }}"><i class="fa fa-cogs"></i> إعدادات النظام</a></li>
                            @else
                                <li class="sidebar-heading">إدارة المتجر</li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.dashboard') ? 'active' : '' }}" href="{{ route('store.dashboard') }}"><i class="fa fa-home"></i> الرئيسية</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.products.*') ? 'active' : '' }}" href="{{ route('store.products.index') }}"><i class="fa fa-box-open"></i> المنتجات</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.categories.*') ? 'active' : '' }}" href="{{ route('store.categories.index') }}"><i class="fa fa-tags"></i> التصنيفات</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.contacts.*') ? 'active' : '' }}" href="{{ route('store.contacts.index') }}"><i class="fa fa-users"></i> جهات الاتصال</a></li>
                                <li class="nav-item">
                                    <a href="{{ route('store.purchases.index') }}" class="nav-link {{ request()->routeIs('store.purchases.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-shopping-cart"></i>
                                        <p>المشتريات</p>
                                    </a>
                                </li>
{{-- قائمة المبيعات --}}
<li class="nav-item {{ request()->routeIs('store.pos.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('store.pos.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cash-register"></i>
        <p>
            المبيعات
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        {{-- رابط نقطة البيع --}}
        <li class="nav-item">
            <a href="{{ route('store.pos.index') }}" class="nav-link {{ request()->routeIs('store.pos.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>نقطة بيع (POS)</p>
            </a>
        </li>
        
        {{-- يمكنك إضافة رابط الفواتير هنا مستقبلاً --}}
        {{-- 
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>سجل الفواتير</p>
            </a>
        </li> 
        --}}
    </ul>
</li>
                                <li class="sidebar-heading">الإعدادات</li>
    {{-- قسم التقارير --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#reportsCollapse">
        <i class="fas fa-chart-line fa-fw me-2"></i>
        <span>التقارير</span>
        <i class="fas fa-angle-down ms-auto"></i>
    </a>
    <div id="reportsCollapse" class="collapse" data-bs-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">التقارير المالية:</h6>
            <a class="collapse-item" href="{{ route('reports.shifts') }}">
                <i class="fas fa-cash-register me-1"></i> تقرير الصناديق
            </a>
        </div>
    </div>
</li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.settings.*') ? 'active' : '' }}" href="{{ route('store.settings.index') }}"><i class="fa fa-cogs"></i> الإعدادات</a></li>
                            @endif
                        </ul>
                    </div>
                </nav>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    @yield('content')
                </main>
                @else
                <main class="col-12 px-md-4 py-4">
                    @yield('content')
                </main>
                @endauth
            </div>
        </div>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var oldDropdowns = document.querySelectorAll('[data-toggle="dropdown"]');
            oldDropdowns.forEach(function(dropdown) {
                dropdown.setAttribute('data-bs-toggle', 'dropdown');
                new bootstrap.Dropdown(dropdown);
            });
        });
    </script>
    
    {{-- 🔥 الحل الجذري والنهائي للأرقام بالجافاسكربت 🔥 --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. منع Enter العشوائي
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    if (event.target.type !== 'submit' && event.target.tagName !== 'TEXTAREA') {
                        event.preventDefault();
                        return false;
                    }
                }
            });

            // 2. تحديد النص عند الفوكس
            document.body.addEventListener('focusin', function(e) {
                if (e.target.tagName === 'INPUT' && (e.target.type === 'text' || e.target.type === 'number' || e.target.type === 'tel')) {
                    e.target.select();
                }
            });

            // 3. التحويل القسري لنوع الحقل (الحل السحري)
            // المتصفح لن يترجم الأرقام في حقول type="text" أو type="tel"
            // لذلك سنحول حقول الأرقام إليها برمجياً
            function convertNumberInputs() {
                const selectors = [
                    'input[type="number"]', 
                    'input[name*="price"]', 
                    'input[name*="qty"]', 
                    'input[name*="quantity"]',
                    'input[name*="total"]',
                    'input[name*="cost"]',
                    'input[name*="discount"]',
                    '.english-num'
                ];
                
                document.querySelectorAll(selectors.join(',')).forEach(input => {
                    // إذا كان الحقل مخفي، اتركه
                    if(input.type === 'hidden') return;

                    // تغيير النوع إلى 'tel' (أفضل للموبايل) أو 'text'
                    // هذا يمنع المتصفح من تطبيق التنسيق العربي للأرقام
                    if (input.type === 'number') {
                        input.setAttribute('type', 'tel'); 
                    }
                    
                    // إضافة كلاس لتمييزه
                    input.classList.add('numeric-field');
                    
                    // تحسين تجربة المستخدم على الموبايل
                    input.setAttribute('inputmode', 'decimal');
                    input.setAttribute('pattern', '[0-9]*');
                    
                    // إجبار اللغة الإنجليزية (زيادة تأكيد)
                    input.setAttribute('lang', 'en'); 
                    input.style.direction = 'ltr';

                    // تنظيف القيمة من أي أحرف غريبة عند التحميل
                    if(input.value && !isNaN(input.value)) {
                         let cleanNum = parseFloat(parseFloat(input.value).toFixed(10));
                         input.value = cleanNum;
                    }
                });
            }

            // تشغيل الدالة
            convertNumberInputs();
            setInterval(convertNumberInputs, 1000); // للتأكد من الحقول الجديدة (Ajax)
        });

        // 4. دالة التنسيق العامة
        window.formatNum = function(num) {
            if (isNaN(num) || num === '' || num === null) return '';
            return parseFloat(parseFloat(num).toFixed(10));
        };
    </script>

    @yield('scripts')

        {{-- نافذة المعالجة السريعة للمخزون المنخفض --}}
<div class="modal fade" id="lowStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">⚠️ معالجة نقص المخزون</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h5 id="ls_product_name" class="text-center fw-bold mb-3 text-primary"></h5>
                <input type="hidden" id="ls_product_id">

                {{-- الخيار 1: تعديل المخزون الفعلي --}}
                <div class="card p-3 mb-3 border-success">
                    <label class="fw-bold text-success mb-2">1. تصحيح المخزون (جرد فعلي)</label>
                    <div class="input-group">
                        <span class="input-group-text">الموجود حالياً</span>
                        <input type="number" id="ls_current_stock" class="form-control text-center fw-bold fs-5" step="0.01">
                        <button class="btn btn-success" onclick="saveNewStock()">حفظ وتحديث</button>
                    </div>
                    <small class="text-muted mt-1">* على مسؤوليتك: سيتم تغيير الكمية في النظام.</small>
                </div>

                {{-- الخيار 2: تعديل حد التنبيه --}}
                <div class="card p-3 mb-3 border-info">
                    <label class="fw-bold text-info mb-2">2. خفض حد التنبيه</label>
                    <div class="input-group">
                        <span class="input-group-text">نبهني عند</span>
                        <input type="number" id="ls_alert_limit" class="form-control text-center fw-bold" step="1">
                        <button class="btn btn-info text-white" onclick="saveNewLimit()">تغيير الحد</button>
                    </div>
                    <small class="text-muted mt-1">* سيختفي التنبيه حتى يصل المخزون لهذا الرقم.</small>
                </div>

            </div>
            <div class="modal-footer justify-content-center">
                {{-- الخيار 3: أعي ذلك (إغلاق فقط) --}}
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                    <i class="fas fa-check-double me-2"></i> أعي ذلك (إبقاء التنبيه)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openLowStockModal(id, name, stock, limit) {
        document.getElementById('ls_product_id').value = id;
        document.getElementById('ls_product_name').innerText = name;
        document.getElementById('ls_current_stock').value = parseFloat(stock); // إزالة الأصفار هنا أيضاً
        document.getElementById('ls_alert_limit').value = parseFloat(limit);
        
        var myModal = new bootstrap.Modal(document.getElementById('lowStockModal'));
        myModal.show();
    }

    function saveNewStock() {
        let id = document.getElementById('ls_product_id').value;
        let qty = document.getElementById('ls_current_stock').value;

        if(qty === '') return alert('الرجاء إدخال الكمية');

        fetch("{{ route('store.products.quick_update_stock') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ product_id: id, new_stock: qty })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload(); // تحديث الصفحة لإخفاء التنبيه إذا تحقق الشرط
        });
    }

    function saveNewLimit() {
        let id = document.getElementById('ls_product_id').value;
        let limit = document.getElementById('ls_alert_limit').value;

        if(limit === '') return alert('الرجاء إدخال الحد الجديد');

        fetch("{{ route('store.products.quick_update_alert') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ product_id: id, new_alert: limit })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload(); // تحديث الصفحة
        });
    }
</script>
</body>
</html>