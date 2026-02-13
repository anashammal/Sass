<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <script>window.APP_URL = "{{ url('/') }}";</script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tech-Sys') }}</title>

    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- الخط --}}
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    {{-- intl-tel-input --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/css/intlTelInput.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/intlTelInput.min.js"></script>

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

    /* 🔥 تحسين حقول التاريخ (طلب المستخدم) 🔥 */
    .enhanced-date-input {
        font-family: 'Segoe UI', 'Roboto', sans-serif !important; /* خط انجليزي واضح للأرقام */
        font-size: 1.05rem !important; /* تكبير الخط قليلاً */
        font-weight: 600 !important;   /* تعريض الخط */
        direction: ltr !important;     /* إجبار الاتجاه يسار-يمين لظهور الأرقام بشكل صحيح */
        text-align: center !important; /* توسيط التاريخ */
        padding: 4px 8px !important;   /* ضبط الحوامش */
        letter-spacing: 0.5px;         /* تباعد خفيف للأرقام */
        cursor: pointer !important;    /* المؤشر يدل على القابلية للنقر */
    }
    
    /* جعل الحقل بالكامل منطقة نقر لفتح التقويم */
    .enhanced-date-input::-webkit-calendar-picker-indicator {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        color: transparent;
        background: transparent;
        cursor: pointer;
    }
    
    /* تنسيق خاص للطباعة (لإلغاء التصغير عند الطباعة) */
    @media print {
        body { zoom: 1; }
    }

    /* 🔥 تنسيق خاص لوضع الـ Iframe (الإضافة السريعة) 🔥 */
    @if(request('iframe'))
    .sidebar, .navbar, .mobile-header, .mobile-nav-bar, .mobile-bottom-nav { display: none !important; }
    body { padding-top: 0 !important; margin: 0 !important; zoom: 1 !important; background: #fff !important; }
    #app { padding: 0 !important; }
    .col-md-3, .col-lg-2 { display: none !important; }
    .col-md-9, .col-lg-10 { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; border:none !important; }
    .card { box-shadow: none !important; border: 1px solid #eee !important; }
    .container-fluid { padding: 10px !important; }
    @endif

        /* --- تخصيصات الثيم الفخم --- */
        body { 
            font-family: 'Nunito', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eaf6 50%, #f3e5f5 100%);
            background-attachment: fixed;
            overflow-x: hidden; 
        }

        /* تأثير خلفية متحركة خفيفة */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(235, 51, 73, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(17, 153, 142, 0.03) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* القائمة الجانبية الحديثة */
        .sidebar { 
            background: linear-gradient(180deg, #2d2452 0%, #1a1436 100%); 
            /* Fix for zoom: 0.90 (100vh / 0.9 = 111.11vh) */
            height: calc(111.11vh - 70px); 
            position: sticky;
            top: 70px; 
            overflow-y: auto;
            color: #fff; 
            box-shadow: 4px 0 25px rgba(0,0,0,0.1); 
            z-index: 1001;
            padding: 10px;
            transition: all 0.3s ease;
            
            /* Hide Scrollbar */
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .sidebar::-webkit-scrollbar { 
            display: none; 
        }

        .sidebar .nav-item {
            margin-bottom: 5px;
        }

        .sidebar .nav-link { 
            color: rgba(255,255,255,0.7) !important; 
            padding: 12px 15px; 
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            display: flex; 
            align-items: center; 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 600;
            margin: 0 5px;
        }

        .sidebar .nav-link i { 
            width: 32px; 
            height: 32px;
            font-size: 1.1rem; 
            margin-left: 12px; 
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.05);
        }

        .sidebar .nav-link:hover { 
            background-color: rgba(255,255,255,0.1); 
            color: #fff !important; 
            transform: translateX(-5px);
        }

        .sidebar .nav-link.active { 
            background: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%);
            color: #fff !important; 
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .sidebar .nav-link.active i {
            background: rgba(255,255,255,0.2);
        }

        .sidebar-heading { 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            color: rgba(255,255,255,0.3); 
            padding: 20px 20px 10px; 
            font-weight: 800; 
            letter-spacing: 1px;
        }

        /* تنسيق القوائم الفرعية (Sub-menus) */
        .nav-treeview {
            padding-right: 15px;
            margin-top: 5px;
            list-style: none;
        }

        .sidebar .collapse {
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
            margin: 0 5px 10px;
        }

        .sidebar .collapse .nav-link {
            font-size: 0.85rem;
            padding: 10px 15px;
            background: transparent !important;
            border-radius: 8px;
        }

        .sidebar .collapse .nav-link:hover {
            color: #6366f1 !important;
            background: rgba(99, 102, 241, 0.05) !important;
        }

        .sidebar .collapse .nav-link.active {
            color: #fff !important;
            background: rgba(99, 102, 241, 0.2) !important;
            box-shadow: none;
        }

        /* إصلاح السهم في القائمة المنسدلة */
        .nav-link[data-bs-toggle="collapse"] .fa-angle-left {
            transition: transform 0.3s ease;
            margin-right: auto;
            margin-left: 0;
            background: transparent !important;
        }

        .nav-link[aria-expanded="true"] .fa-angle-left {
            transform: rotate(-90deg);
        }

        /* الهيدر العلوي */
        .navbar-custom { background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 70px; z-index: 1002; }
        
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

        /* ========================================= */
        /* 🎨 PREMIUM LUXURY DESIGN SYSTEM 🎨 */
        /* ========================================= */
        
        /* --- CSS Variables for Easy Customization --- */
        :root {
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --gradient-danger: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-gold: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.16);
            --shadow-xl: 0 16px 48px rgba(0, 0, 0, 0.2);
            
            --glow-primary: 0 0 20px rgba(102, 126, 234, 0.4);
            --glow-success: 0 0 20px rgba(17, 153, 142, 0.4);
            --glow-danger: 0 0 20px rgba(235, 51, 73, 0.4);
        }

        /* --- Ultra Premium KPI Cards with Glassmorphism --- */
        .kpi-card {
            border: none;
            border-radius: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 
                0 4px 16px rgba(0, 0, 0, 0.08),
                0 8px 32px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        /* Animated Gradient Background */
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255, 255, 255, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 0;
        }

        .kpi-card:hover::before {
            opacity: 1;
        }

        .kpi-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.15),
                0 20px 60px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        .kpi-card .card-body {
            padding: 2rem 1.75rem;
            position: relative;
            z-index: 1;
        }

        /* Premium Icon Container with 3D Effect */
        .kpi-icon-container {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            font-size: 1.75rem;
            flex-shrink: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            box-shadow: 
                0 8px 16px rgba(0, 0, 0, 0.15),
                inset 0 -2px 8px rgba(0, 0, 0, 0.1),
                inset 0 2px 4px rgba(255, 255, 255, 0.3);
        }

        /* Glow Effect on Icon */
        .kpi-icon-container::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            border-radius: 18px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .kpi-card:hover .kpi-icon-container {
            transform: scale(1.15) rotate(-5deg);
            box-shadow: 
                0 12px 24px rgba(0, 0, 0, 0.2),
                inset 0 -2px 8px rgba(0, 0, 0, 0.15),
                inset 0 2px 4px rgba(255, 255, 255, 0.4);
        }

        .kpi-card:hover .kpi-icon-container::after {
            opacity: 0.6;
        }

        /* Luxury Color Variations with Rich Gradients */
        .kpi-primary { 
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.15) 100%);
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        .kpi-primary .kpi-icon-container { 
            background: var(--gradient-primary);
            color: #fff;
        }
        .kpi-primary .kpi-icon-container::after {
            background: var(--gradient-primary);
            filter: blur(12px);
        }
        .kpi-primary .kpi-value { 
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-success { 
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.08) 0%, rgba(56, 239, 125, 0.15) 100%);
            border: 1px solid rgba(17, 153, 142, 0.1);
        }
        .kpi-success .kpi-icon-container { 
            background: var(--gradient-success);
            color: #fff;
        }
        .kpi-success .kpi-icon-container::after {
            background: var(--gradient-success);
            filter: blur(12px);
        }
        .kpi-success .kpi-value { 
            background: var(--gradient-success);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-danger { 
            background: linear-gradient(135deg, rgba(235, 51, 73, 0.08) 0%, rgba(244, 92, 67, 0.15) 100%);
            border: 1px solid rgba(235, 51, 73, 0.1);
        }
        .kpi-danger .kpi-icon-container { 
            background: var(--gradient-danger);
            color: #fff;
        }
        .kpi-danger .kpi-icon-container::after {
            background: var(--gradient-danger);
            filter: blur(12px);
        }
        .kpi-danger .kpi-value { 
            background: var(--gradient-danger);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-warning { 
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.08) 0%, rgba(245, 87, 108, 0.15) 100%);
            border: 1px solid rgba(240, 147, 251, 0.1);
        }
        .kpi-warning .kpi-icon-container { 
            background: var(--gradient-warning);
            color: #fff;
        }
        .kpi-warning .kpi-icon-container::after {
            background: var(--gradient-warning);
            filter: blur(12px);
        }
        .kpi-warning .kpi-value { 
            background: var(--gradient-warning);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-info { 
            background: linear-gradient(135deg, rgba(79, 172, 254, 0.08) 0%, rgba(0, 242, 254, 0.15) 100%);
            border: 1px solid rgba(79, 172, 254, 0.1);
        }
        .kpi-info .kpi-icon-container { 
            background: var(--gradient-info);
            color: #fff;
        }
        .kpi-info .kpi-icon-container::after {
            background: var(--gradient-info);
            filter: blur(12px);
        }
        .kpi-info .kpi-value { 
            background: var(--gradient-info);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .kpi-label { 
            font-size: 0.9rem; 
            font-weight: 800; 
            color: #64748b; 
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .kpi-value { 
            font-size: 2rem; 
            font-weight: 900; 
            letter-spacing: -0.05em;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        /* RTL Fixes */
        [dir="rtl"] .kpi-icon-container { margin-left: 0; margin-right: 0; }
        [dir="rtl"] .me-3 { margin-left: 1rem !important; margin-right: 0 !important; }

        /* --- Ultra Premium Buttons with Advanced Effects --- */
        .btn {
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-weight: 800;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }

        /* Ripple Effect */
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:active::before {
            width: 300px;
            height: 300px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        /* Primary Button with Gradient */
        .btn-primary {
            background: var(--gradient-primary);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: #fff;
        }
        .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4), var(--glow-primary);
            filter: brightness(1.1);
        }

        /* Success Button */
        .btn-success {
            background: var(--gradient-success);
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
            color: #fff;
        }
        .btn-success:hover {
            box-shadow: 0 8px 24px rgba(17, 153, 142, 0.4), var(--glow-success);
            filter: brightness(1.1);
        }

        /* Danger Button */
        .btn-danger {
            background: var(--gradient-danger);
            box-shadow: 0 4px 12px rgba(235, 51, 73, 0.3);
            color: #fff;
        }
        .btn-danger:hover {
            box-shadow: 0 8px 24px rgba(235, 51, 73, 0.4), var(--glow-danger);
            filter: brightness(1.1);
        }

        /* Warning Button */
        .btn-warning {
            background: var(--gradient-warning);
            box-shadow: 0 4px 12px rgba(240, 147, 251, 0.3);
            color: #fff;
        }
        .btn-warning:hover {
            box-shadow: 0 8px 24px rgba(240, 147, 251, 0.4);
            filter: brightness(1.1);
        }

        /* Info Button */
        .btn-info {
            background: var(--gradient-info);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
            color: #fff;
        }
        .btn-info:hover {
            box-shadow: 0 8px 24px rgba(79, 172, 254, 0.4);
            filter: brightness(1.1);
        }

        /* Outline Buttons with Glassmorphism */
        .btn-outline-primary {
            background: rgba(102, 126, 234, 0.05);
            border: 2px solid rgba(102, 126, 234, 0.3);
            color: #667eea;
            backdrop-filter: blur(10px);
        }
        .btn-outline-primary:hover {
            background: var(--gradient-primary);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-outline-secondary {
            background: rgba(100, 116, 139, 0.05);
            border: 2px solid rgba(100, 116, 139, 0.2);
            color: #64748b;
            backdrop-filter: blur(10px);
        }
        .btn-outline-secondary:hover {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(100, 116, 139, 0.3);
        }

        .btn-outline-danger {
            background: rgba(235, 51, 73, 0.05);
            border: 2px solid rgba(235, 51, 73, 0.3);
            color: #eb3349;
            backdrop-filter: blur(10px);
        }
        .btn-outline-danger:hover {
            background: var(--gradient-danger);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(235, 51, 73, 0.3);
        }

        .btn-outline-info {
            background: rgba(79, 172, 254, 0.05);
            border: 2px solid rgba(79, 172, 254, 0.3);
            color: #4facfe;
            backdrop-filter: blur(10px);
        }
        .btn-outline-info:hover {
            background: var(--gradient-info);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.3);
        }

        /* ========================================= */
        /* 🎨 PREMIUM CARDS & TABLES DESIGN 🎨 */
        /* ========================================= */

        /* --- Luxury Card Design --- */
        .card {
            border: none;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 
                0 4px 16px rgba(0, 0, 0, 0.08),
                0 8px 32px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: 
                0 8px 24px rgba(0, 0, 0, 0.12),
                0 16px 48px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.08) 100%);
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            border-radius: 20px 20px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }

        /* --- Premium Table Design --- */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            border: none;
            padding: 1rem;
            position: relative;
        }

        .table thead th:first-child {
            border-radius: 0 12px 0 0;
        }

        .table thead th:last-child {
            border-radius: 12px 0 0 0;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            background: #fff;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.08) 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        /* Animated row entrance */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table tbody tr {
            animation: fadeInUp 0.4s ease-out backwards;
        }

        .table tbody tr:nth-child(1) { animation-delay: 0.05s; }
        .table tbody tr:nth-child(2) { animation-delay: 0.1s; }
        .table tbody tr:nth-child(3) { animation-delay: 0.15s; }
        .table tbody tr:nth-child(4) { animation-delay: 0.2s; }
        .table tbody tr:nth-child(5) { animation-delay: 0.25s; }

        /* --- Premium Form Inputs --- */
        .form-control, .form-select {
            border: 2px solid rgba(100, 116, 139, 0.15);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 
                0 0 0 3px rgba(102, 126, 234, 0.1),
                0 4px 12px rgba(102, 126, 234, 0.15);
            background: #fff;
            transform: translateY(-2px);
        }

        /* --- Premium Badges --- */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        /* --- تخصيص intl-tel-input للـ RTL والمظهر الفاخر --- */
        :root {
            --iti-path-flags-1x: url("{{ asset('assets/intl-tel-input/flags.png') }}");
            --iti-path-flags-2x: url("{{ asset('assets/intl-tel-input/flags@2x.png') }}");
        }
        
        .iti { width: 100%; display: block; }
        .iti__country-list { 
            direction: rtl !important; 
            text-align: right !important; 
            border-radius: 12px !important; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important; 
            z-index: 9999 !important;
        }

        /* تحسين مظهر شريط البحث */
        .iti__search-input {
            width: 100% !important;
            padding: 10px 15px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            margin: 5px !important;
            direction: rtl !important;
            text-align: right !important;
        }

        /* إصلاح الأعلام - استخدام CDN لضمان الظهور الفوري */
        .iti__flag {
            background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/img/flags.png") !important;
        }
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .iti__flag {
                background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/img/flags@2x.png") !important;
            }
        }

        /* علم سوريا المخصص */
        .iti__sy, .iti__flag.iti__sy {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 3 2'%3E%3Crect width='3' height='2' fill='%23000'/%3E%3Crect width='3' height='1.333' fill='%23fff'/%3E%3Crect width='3' height='0.666' fill='%23007a33'/%3E%3Cg fill='%23ce1126'%3E%3Cpath d='M0.75 1.15l.147.107-.056-.173.147-.107-.182 0-.056-.173L.7 0.98l-.182 0 .147.107-.056.173z'/%3E%3Cpath d='M1.5 1.15l.147.107-.056-.173.147-.107-.182 0-.056-.173L1.45 0.98l-.182 0 .147.107-.056.173z'/%3E%3Cpath d='M2.25 1.15l.147.107-.056-.173.147-.107-.182 0-.056-.173L2.2 0.98l-.182 0 .147.107-.056.173z'/%3E%3C/g%3E%3C/svg%3E") !important;
            background-position: center !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            width: 20px !important;
            height: 14px !important;
            display: inline-block !important;
        }

        /* ضبط حقل الإدخال ليعمل مع المكتبة */
        input#phone_input { 
            padding-left: 100px !important; /* مساحة كافية للعلم والرمز */
            text-align: left !important;
            direction: ltr !important;
            font-family: sans-serif !important;
        }
        [dir="rtl"] .iti__flag-box { margin-right: 0; margin-left: 10px; }
        .iti__selected-flag { background-color: transparent !important; padding: 0 12px !important; }
        .phone-error-msg {
            color: #dc3545;
            font-size: 0.8rem;
            font-weight: 700;
            margin-top: 5px;
            display: none;
        }
        .is-invalid-phone {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }

        /* --- Premium Alerts --- */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.15) 100%);
            color: #0f766e;
            border-left: 4px solid #11998e;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(235, 51, 73, 0.1) 0%, rgba(244, 92, 67, 0.15) 100%);
            color: #b91c1c;
            border-left: 4px solid #eb3349;
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.15) 100%);
            color: #92400e;
            border-left: 4px solid #f093fb;
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.15) 100%);
            color: #075985;
            border-left: 4px solid #4facfe;
        }

        /* --- Premium Modal Design --- */
        .modal-content {
            border: none;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .modal-header {
            border-radius: 24px 24px 0 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem 2rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem 2rem;
            border-radius: 0 0 24px 24px;
        }

        /* --- Smooth Scrollbar --- */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* --- Responsive Table Improvements --- */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* تحسين عرض الأعمدة في الجداول */
        .table th, .table td {
            white-space: normal; /* السماح بالتفاف النص */
            word-wrap: break-word;
            vertical-align: middle;
        }

        /* أعمدة محددة بعرض مناسب */
        .table th:nth-child(1), .table td:nth-child(1) { 
            min-width: 120px;
            max-width: 180px; 
        } /* الاسم */
        
        .table th:nth-child(2), .table td:nth-child(2) { 
            min-width: 100px;
            max-width: 150px; 
        } /* الشركة */
        
        .table th:nth-child(3), .table td:nth-child(3) { 
            min-width: 90px;
            max-width: 120px; 
        } /* النوع */
        
        .table th:nth-child(4), .table td:nth-child(4) { 
            min-width: 110px;
            max-width: 130px;
            white-space: nowrap;
        } /* الهاتف */
        
        .table th:nth-child(5), .table td:nth-child(5) { 
            min-width: 120px;
            max-width: 180px; 
        } /* البريد */
        
        .table th:nth-child(6), .table td:nth-child(6) { 
            min-width: 90px;
            max-width: 120px; 
        } /* الرقم الضريبي */
        
        .table th:nth-child(7), .table td:nth-child(7) { 
            min-width: 140px;
            max-width: 200px; 
        } /* العنوان */
        
        .table th:nth-child(8), .table td:nth-child(8) { 
            min-width: 90px;
            max-width: 120px; 
        } /* الرصيد */
        
        .table th:nth-child(9), .table td:nth-child(9) { 
            min-width: 120px;
            max-width: 160px;
            white-space: nowrap;
        } /* الإجراءات */

        /* للشاشات الصغيرة */
        @media (max-width: 1400px) {
            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.75rem 0.4rem;
            }
        }

        /* --- تصغير أزرار الإجراءات في الجداول --- */
        .table .btn-group .btn,
        .table .btn-group-sm .btn {
            padding: 0.35rem 0.5rem;
            font-size: 0.8rem;
        }

        .table .btn-group .btn i,
        .table .btn-group-sm .btn i {
            font-size: 0.75rem;
        }

        /* تقليل المسافة بين الأزرار */
        .table .btn-group .btn {
            margin-left: 2px;
        }
    
    
    
    
    /* ========================================= */
    /* 📱 MOBILE APP EXPERIENCE (Phone Only) 📱 */
    /* ========================================= */
    @media (max-width: 768px) {
        /* Reset Desktop Enforced Scale */
        :root { --base-scale: 1; }
        body { 
            zoom: 1 !important; 
            padding-bottom: 80px; /* Space for Bottom Nav */
            padding-top: 60px; /* Space for Mobile Header */
            background-color: #f6f7fb; 
        }
        
        /* Hide Desktop Elements */
        .navbar-custom { display: none !important; }
        #sidebarMenu { display: none !important; } 
        
        /* Show Mobile Elements */
        .mobile-header { display: flex !important; }
        .mobile-bottom-nav { display: flex !important; }
        
        /* Improve Touch Areas & Text */
        .btn { min-height: 44px; display: inline-flex; align-items: center; justify-content: center; }
        input, select { min-height: 44px; font-size: 16px !important; } /* 16px prevents iOS zoom */
        
        /* Adjust Container Padding */
        .container-fluid { padding-left: 15px; padding-right: 15px; }
        
        /* Safe Area fix for iPhone X+ */
        .mobile-bottom-nav { padding-bottom: env(safe-area-inset-bottom); height: calc(65px + env(safe-area-inset-bottom)); }

        /* KPI Cards Mobile Optimization */
        .col-md-3 .card { margin-bottom: 15px; }
        
        /* Hide scrollbars for cleaner look */
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    }
    
    /* Mobile Elements Default Hidden on Desktop */
    .mobile-header { display: none; }
    .mobile-bottom-nav { display: none; }
    
    /* Mobile Header Styles */
    .mobile-header {
        position: fixed; top: 0; left: 0; right: 0;
        height: 60px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        z-index: 1040;
        padding: 0 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    /* Mobile Bottom Nav Styles */
    .mobile-bottom-nav {
        position: fixed; bottom: 0; left: 0; right: 0;
        height: 70px;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        z-index: 1050;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
        justify-content: space-around;
        align-items: center;
        border-top: 1px solid rgba(0,0,0,0.05);
        border-radius: 20px 20px 0 0;
    }
    
    .mobile-nav-item {
        display: flex; flex-direction: column; align-items: center;
        text-decoration: none; color: #94a3b8; width: 20%;
        transition: all 0.3s;
        font-size: 0.70rem;
        font-weight: 700;
        position: relative;
        top: -5px;
    }
    
    .mobile-nav-item i { font-size: 1.4rem; margin-bottom: 4px; transition: all 0.3s; }
    
    .mobile-nav-item.active { color: #667eea; }
    .mobile-nav-item.active i { transform: translateY(-3px); color: #764ba2; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    
    /* Center FAB (POS Button) */
    .mobile-nav-fab {
        position: absolute; top: -35px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 60px; height: 60px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.5);
        border: 4px solid #fff;
        transition: transform 0.2s;
    }
    .mobile-nav-fab i { font-size: 1.6rem; }
    .mobile-nav-fab:active { transform: scale(0.90); }
    
    /* Offcanvas Menu Customization */
    .offcanvas-mobile-menu {
        background: linear-gradient(180deg, #2d2452 0%, #1a1436 100%);
        color: white;
    }
    </style>

</head>
<body>
    <div id="app" class="d-flex flex-column h-100">
        
        {{-- 📱 Mobile Custom Header 📱 --}}
        @if(!request('iframe'))
        <div class="mobile-header d-md-none">
            <div class="d-flex align-items-center">
                @auth
                    @php
                         $mobLogo = null;
                         if(auth()->user()->store) {
                             $mobStore = auth()->user()->store;
                             if ($mobStore->logo_path) $mobLogo = route('serve.media.workaround', ['path' => $mobStore->logo_path]);
                         }
                    @endphp
                    <img src="{{ $mobLogo ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->store->name ?? 'T').'&background=random' }}" 
                         style="width: 38px; height: 38px; border-radius: 12px; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" class="me-2">
                    <div style="line-height: 1.1;">
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ Str::limit(auth()->user()->store->name ?? 'TechSys', 20) }}</div>
                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.65rem;">مدير المتجر</span>
                    </div>
                @else
                    <span class="fw-bold text-dark fs-5">TechSys</span>
                @endauth
            </div>
            
            <div class="d-flex align-items-center gap-3">
                 <a href="#" class="position-relative text-secondary p-2">
                    <i class="fa fa-bell fa-lg"></i>
                     @if(isset($expiryAlerts) && ($expiryAlerts['expired']->count() + $expiryAlerts['near']->count()) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    @endif
                </a>
                @auth
                <a href="{{ route('store.settings.index') }}">
                     <div class="bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                          style="width: 35px; height: 35px; font-size: 0.9rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                </a>
                @endauth
            </div>
        </div>
        @endif
        
        @if(!request('iframe'))
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
                                    $logoUrl = $logoPath ? route('serve.media.workaround', ['path' => $logoPath]) : null;
                                    $brandName = 'لوحة الإدارة';
                                } else {
                                    $myStore = \App\Models\Store::where('owner_id', auth()->id())->first();
                                    if ($myStore) {
                                        $brandName = $myStore->name;
                                        if ($myStore->logo_path) $logoUrl = route('serve.media.workaround', ['path' => $myStore->logo_path]);
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
            <span>{{ __('التنبيهات') }}</span>
            @if($totalAlerts > 0)
                <small class="text-muted">{{ $totalAlerts }} {{ __('إشعار') }}</small>
            @endif
        </div>
        
        @if(isset($expiryAlerts))
            
            {{-- 1. قسم الصلاحية المنتهية (الأخطر) --}}
            @if($expiryAlerts['expired']->count() > 0)
                <div class="px-2 py-1 bg-danger text-white small fw-bold"><i class="fas fa-skull-crossbones me-1"></i> {{ __('منتهي الصلاحية') }}</div>
                @foreach($expiryAlerts['expired'] as $batch)
                    <a class="dropdown-item p-2 border-bottom bg-danger bg-opacity-10" href="javascript:void(0)" onclick="openExpiryActionModal({{ $batch->id }}, '{{ addslashes($batch->product->name_ar ?? 'منتج') }}', '{{ $batch->product->baseUnit->unit_name ?? 'قطعة' }}', {{ $batch->quantity }}, '{{ $batch->expiry_date->format('Y-m-d') }}')">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1" style="white-space: normal;">
                                <div class="fw-bold text-danger small">{{ $batch->product->name_ar ?? 'منتج' }}</div>
                                <small class="text-muted" style="font-size: 0.7rem">انتهى: {{ $batch->expiry_date->format('Y-m-d') }}</small>
                            </div>
                            <i class="fas fa-cog text-danger ms-2"></i>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- 2. قسم نقص المخزون --}}
            @if($expiryAlerts['low_stock']->count() > 0)
                <div class="px-2 py-1 bg-dark text-warning small fw-bold"><i class="fas fa-boxes me-1"></i> {{ __('مخزون منخفض') }}</div>
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
                <div class="px-2 py-1 bg-warning bg-opacity-25 text-dark small fw-bold"><i class="fas fa-hourglass-half me-1"></i> {{ __('تنبيهات الصلاحية') }}</div>
                @foreach($expiryAlerts['near'] as $batch)
                    @php $days = $batch->days_remaining_calculated ?? 0; @endphp
                    <a class="dropdown-item p-2 border-bottom bg-warning bg-opacity-10" href="javascript:void(0)" onclick="openExpiryActionModal({{ $batch->id }}, '{{ addslashes($batch->product->name_ar ?? 'منتج') }}', '{{ $batch->product->baseUnit->unit_name ?? 'قطعة' }}', {{ $batch->quantity }}, '{{ $batch->expiry_date->format('Y-m-d') }}')">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1" style="white-space: normal;">
                                <div class="fw-bold text-dark small">{{ $batch->product->name_ar ?? 'منتج' }}</div>
                                <small class="text-warning fw-bold" style="font-size: 0.7rem">
                                    بقي {{ $days }} يوم ({{ (float)$batch->quantity }})
                                </small>
                            </div>
                            <i class="fas fa-cog text-warning ms-2"></i>
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-secondary fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-globe me-1"></i> {{ strtoupper(app()->getLocale()) }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="max-height: 300px; overflow-y: auto;">
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'ar') }}">🇸🇦 العربية <span>AR</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'en') }}">🇺🇸 English <span>EN</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'fr') }}">🇫🇷 Français <span>FR</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'tr') }}">🇹🇷 Türkçe <span>TR</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'de') }}">🇩🇪 Deutsch <span>DE</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'ru') }}">🇷🇺 Русский <span>RU</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'zh') }}">🇨🇳 中文 <span>ZH</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'ja') }}">🇯🇵 日本語 <span>JA</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'pt-BR') }}">🇧🇷 Português (BR) <span>PT-BR</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'pt') }}">🇵🇹 Português (PT) <span>PT</span></a></li>
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('lang.switch', 'hr') }}">🇭🇷 Hrvatski <span>HR</span></a></li>
                            </ul>
                        </li>
                        
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
                                    <i class="fa fa-sign-out-alt ms-2"></i> {{ __('تسجيل الخروج') }}
                                </a>
                            </div>
                        </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
        @endif

        <div class="container-fluid flex-grow-1">
            <div class="row h-100">
                @auth
                @if(!request('iframe'))
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            @if(auth()->id() == 1)
                                <li class="sidebar-heading">{{ __('الإدارة العامة') }}</li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}"><i class="fa fa-tachometer-alt"></i> {{ __('لوحة التحكم') }}</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.stores.*') ? 'active' : '' }}" href="{{ route('superadmin.stores.index') }}"><i class="fa fa-store"></i> {{ __('إدارة المتاجر') }}</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}" href="{{ route('superadmin.settings.index') }}"><i class="fa fa-cogs"></i> {{ __('إعدادات النظام') }}</a></li>
                            @else
                                {{-- <li class="sidebar-heading">إدارة المتجر</li> --}}
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.dashboard') ? 'active' : '' }}" href="{{ route('store.dashboard') }}"><i class="fa fa-home"></i> {{ __('الرئيسية') }}</a></li>
                                <li class="nav-item">
                                    @if(strtolower(Auth::user()->store->type) == 'restaurant')
                                        <a class="nav-link {{ request()->routeIs('store.meals.*') ? 'active' : '' }}" href="{{ route('store.meals.index') }}">
                                            <i class="fa fa-box-open"></i> {{ __('الوجبات والمكونات') }}
                                        </a>
                                    @else
                                        <a class="nav-link {{ request()->routeIs('store.products.*') ? 'active' : '' }}" href="{{ route('store.products.index') }}">
                                            <i class="fa fa-box-open"></i> {{ __('المنتجات') }}
                                        </a>
                                    @endif
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('store.categories.*') ? 'active' : '' }}" href="{{ route('store.categories.index') }}">
                                        <i class="fa fa-tags"></i>
                                        @if(strtolower(Auth::user()->store->type) == 'restaurant')
                                            {{ __('تصنيفات المنيو') }}
                                        @else
                                            {{ __('التصنيفات') }}
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.contacts.*') ? 'active' : '' }}" href="{{ route('store.contacts.index') }}"><i class="fa fa-users"></i> {{ __('جهات الاتصال') }}</a></li>
                                <li class="nav-item">
                                    <a href="{{ route('store.purchases.index') }}" class="nav-link {{ request()->routeIs('store.purchases.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-shopping-cart"></i>
                                        <p>
                                            @if(strtolower(Auth::user()->store->type) == 'restaurant')
                                                {{ __('المواد الخام (المشتريات)') }}
                                            @else
                                                {{ __('المشتريات') }}
                                            @endif
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('store.expenses.index') }}" class="nav-link {{ request()->routeIs('store.expenses.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-wallet"></i>
                                        <p>{{ __('المصاريف') }}</p>
                                    </a>
                                </li>
{{-- قائمة المبيعات --}}
<li class="nav-item">
    <a href="#" class="nav-link {{ request()->routeIs('store.pos.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#salesCollapse" role="button" aria-expanded="{{ request()->routeIs('store.pos.*') ? 'true' : 'false' }}">
        <i class="nav-icon fas fa-cash-register"></i>
        <span>
            @if(strtolower(Auth::user()->store->type) == 'restaurant')
                {{ __('الأوردرات (المبيعات)') }}
            @else
                {{ __('المبيعات') }}
            @endif
        </span>
        <i class="fas fa-angle-left ms-auto"></i>
    </a>
    <div class="collapse {{ request()->routeIs('store.pos.*') && !request()->routeIs('store.pos.withdrawals') ? 'show' : '' }}" id="salesCollapse">
        <ul class="nav flex-column ps-3">
            <li class="nav-item">
                <a href="{{ route('store.pos.index') }}" class="nav-link {{ request()->routeIs('store.pos.index') ? 'active' : '' }}">
                    <i class="far fa-circle"></i>
                    <span>{{ __('نقطة بيع (POS)') }}</span>
                </a>
            </li>
        </ul>
    </div>
</li>
                                <li class="sidebar-heading">{{ __('الإعدادات') }}</li>
    {{-- قسم التقارير --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : 'collapsed' }}" href="#" data-bs-toggle="collapse" data-bs-target="#reportsCollapse" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
        <i class="fas fa-chart-line"></i>
        <span>{{ __('التقارير') }}</span>
        <i class="fas fa-angle-left ms-auto"></i>
    </a>
    <div id="reportsCollapse" class="collapse {{ request()->routeIs('reports.*') || request()->routeIs('store.pos.withdrawals') ? 'show' : '' }}">
        <ul class="nav flex-column ps-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.shifts') ? 'active' : '' }}" href="{{ route('reports.shifts') }}">
                    <i class="fas fa-cash-register"></i> 
                    <span>{{ __('تقرير الصناديق') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.inventory_logs') ? 'active' : '' }}" href="{{ route('reports.inventory_logs') }}">
                    <i class="fas fa-history"></i> 
                    <span>{{ __('سجل تعديلات المخزون') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('store.pos.withdrawals') ? 'active' : '' }}" href="{{ route('store.pos.withdrawals') }}">
                    <i class="fas fa-hand-holding-usd"></i> 
                    <span>{{ __('مسحوبات المالك') }}</span>
                </a>
            </li>
        </ul>
    </div>
</li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('store.settings.*') ? 'active' : '' }}" href="{{ route('store.settings.index') }}"><i class="fa fa-cogs"></i> {{ __('الإعدادات') }}</a></li>
                            @endif
                        </ul>
                    </div>
                </nav>
                @endif
                <main class="{{ request('iframe') ? 'col-12 px-0' : 'col-md-9 ms-sm-auto col-lg-10 px-md-4' }} py-4">
                    @yield('content')
                </main>
                @else
                <main class="col-12 px-md-4 py-4">
                    @yield('content')
                </main>
                @endauth
            </div>
        </div>
        {{-- 📱 Mobile Bottom Navigation 📱 --}}
        @auth
        @if(!request('iframe'))
        <div class="mobile-bottom-nav d-md-none">
            <a href="{{ route('store.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('store.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> <span>الرئيسية</span>
            </a>
            
            @if(auth()->user()->store && strtolower(auth()->user()->store->type) == 'restaurant')
                <a href="{{ route('store.meals.index') }}" class="mobile-nav-item {{ request()->routeIs('store.meals.*') ? 'active' : '' }}">
                    <i class="fas fa-utensils"></i> <span>المنيو</span>
                </a>
            @else
                <a href="{{ route('store.products.index') }}" class="mobile-nav-item {{ request()->routeIs('store.products.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> <span>المنتجات</span>
                </a>
            @endif
        
            <a href="{{ route('store.pos.index') }}" class="mobile-nav-item" style="width: 20%;">
                <div class="mobile-nav-fab d-flex align-items-center justify-content-center">
                    <i class="fas fa-cash-register"></i>
                </div>
                <span style="margin-top: 35px;">البيع</span>
            </a>
        
            <a href="{{ route('reports.shifts') }}" class="mobile-nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> <span>التقارير</span>
            </a>
            
            <a href="#" class="mobile-nav-item" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuOffcanvas">
                <i class="fas fa-th"></i> <span>المزيد</span>
            </a>
        </div>
        @endif

        {{-- 📱 Mobile Sidebar (Offcanvas) 📱 --}}
        <div class="offcanvas offcanvas-start offcanvas-mobile-menu" tabindex="-1" id="mobileMenuOffcanvas" style="width: 75%; border-right: 1px solid rgba(255,255,255,0.1);">
            <div class="offcanvas-header pt-4 pb-2 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-primary rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                        <i class="fas fa-store fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="offcanvas-title fw-bold mb-0 text-white">{{ auth()->user()->store->name ?? 'القائمة' }}</h5>
                        <small class="text-white text-opacity-50">لوحة التحكم</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0 mt-3">
                <div class="list-group list-group-flush bg-transparent">
                    <div class="px-4 py-2 text-white text-opacity-25 small fw-bold text-uppercase ls-1">إدارة المتجر</div>
                    
                    <a href="{{ route('store.contacts.index') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3 px-4">
                        <i class="fas fa-users me-3 text-info opacity-75" style="width:20px"></i> العملاء
                    </a>
                    
                    <a href="{{ route('store.purchases.index') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3 px-4">
                        <i class="fas fa-truck-loading me-3 text-warning opacity-75" style="width:20px"></i> المشتريات
                    </a>
                    
                    <a href="{{ route('store.expenses.index') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3 px-4">
                        <i class="fas fa-file-invoice-dollar me-3 text-danger opacity-75" style="width:20px"></i> المصاريف
                    </a>
                    
                    <div class="my-2 border-top border-white border-opacity-10"></div>
                    
                    <a href="{{ route('store.settings.index') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3 px-4">
                        <i class="fas fa-cog me-3 text-secondary opacity-75" style="width:20px"></i> الإعدادات
                    </a>

                    <div class="mt-5 px-4">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                           class="btn btn-danger w-100 rounded-pill py-2 shadow-sm d-flex align-items-center justify-content-center">
                            <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endauth

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
<div class="modal fade" id="lowStockModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
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

    function openExpiryActionModal(batchId, productName, unitName, currentQty, expiryDate) {
        // Fill Dispose Form
        document.getElementById('ea_dispose_batch_id').value = batchId;
        document.getElementById('ea_dispose_product_info').innerHTML = `<strong>${productName}</strong> <br> <small>تنتهي في: ${expiryDate}</small>`;
        document.getElementById('ea_dispose_max_display').innerText = parseFloat(currentQty);
        document.getElementById('ea_dispose_qty').max = currentQty;
        document.getElementById('ea_dispose_qty').value = currentQty;

        // Fill Extend Form
        document.getElementById('ea_extend_batch_id').value = batchId;
        document.getElementById('ea_extend_product_info').innerHTML = `<strong>${productName}</strong> <br> <small>الكمية الحالية: ${parseFloat(currentQty)} ${unitName}</small>`;
        document.getElementById('ea_extend_max_display').innerText = parseFloat(currentQty);
        document.getElementById('ea_extend_qty').max = currentQty;
        document.getElementById('ea_extend_qty').value = currentQty;

        // Update Title
        document.getElementById('ea_modal_title').innerText = "إدارة الصلاحية: " + productName;

        // Set step for fractions if needed (Kilo/Kg)
        const isKilo = /kilo|kg|كيلو|كغ/i.test(unitName);
        const step = isKilo ? "0.001" : "1";
        document.getElementById('ea_dispose_qty').step = step;
        document.getElementById('ea_extend_qty').step = step;

        var myModal = new bootstrap.Modal(document.getElementById('expiryActionModal'));
        myModal.show();
    }


    // --- نظام البريد الإلكتروني الموحد (Universal Email Logic) ---
    function triggerEmailPrompt(email, message, title = "إرسال تقرير رسمي", mediaUrl = "", filename = "") {
        const modalEl = document.getElementById('globalEmailModal');
        if(!modalEl) return;
        
        document.getElementById('ge_modal_title').innerText = title;
        document.getElementById('ge_email').value = email || '';
        document.getElementById('ge_subject').value = title; // الافتراضي هو العنوان
        document.getElementById('ge_message_preview').value = message || '';
        document.getElementById('ge_media_url').value = mediaUrl || '';
        document.getElementById('ge_filename').value = filename || '';
        
        const mediaSection = document.getElementById('ge_media_status');
        if (mediaUrl) {
            mediaSection.innerHTML = `<div class="alert alert-primary py-2 mb-2 small"><i class="fas fa-paperclip me-1"></i> سيتم إرفاق ملف: ${filename || 'تقرير PDF'}</div>`;
        } else {
            mediaSection.innerHTML = '';
        }

        const myModal = new bootstrap.Modal(modalEl);
        myModal.show();
    }

    /**
     * دالة مساعدة لإنشاء شريط تقدم داخل الزر
     * @param {HTMLElement} btn الزر الذي سيتم تحويله
     * @param {number} duration المدة المتوقعة بالميلي ثانية (للأشرطة الطويلة)
     */
    function simulateProgressBar(btn, duration = 30000) {
        const originalText = btn.innerHTML;
        const originalWidth = btn.offsetWidth; // حفظ العرض الأصلي للحفاظ على الشكل
        
        btn.disabled = true;
        btn.style.width = (originalWidth < 200 ? 200 : originalWidth) + 'px'; // ضمان عرض كافٍ للشريط

        // HTML الخاص بشريط التقدم
        btn.innerHTML = `
            <div class="d-flex align-items-center justify-content-between w-100">
                <span id="prog_lbl_${btn.id}" class="small me-2">0%</span>
                <div class="progress flex-grow-1" style="height: 6px;">
                    <div id="prog_bar_${btn.id}" class="progress-bar progress-bar-striped progress-bar-animated bg-light" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        `;

        let progress = 0;
        const intervalTime = 1000; // تحديث كل ثانية
        const totalSteps = duration / intervalTime;
        const increment = 95 / totalSteps; // نهدف للوصول إلى 95% فقط وترك الـ 5% للنهاية

        const timer = setInterval(() => {
            progress += increment;
            if (progress > 95) progress = 95; // توقف عند 95% حتى يأتي الرد

            const currentPct = Math.round(progress) + '%';
            const bar = document.getElementById(`prog_bar_${btn.id}`);
            const lbl = document.getElementById(`prog_lbl_${btn.id}`);
            
            if (bar) bar.style.width = currentPct;
            if (lbl) lbl.innerText = currentPct;
        }, intervalTime);

        return {
            stop: function() {
                clearInterval(timer);
                // القفز إلى 100%
                const bar = document.getElementById(`prog_bar_${btn.id}`);
                const lbl = document.getElementById(`prog_lbl_${btn.id}`);
                if (bar) {
                    bar.style.width = '100%';
                    bar.classList.remove('progress-bar-animated'); // إيقاف الحركة
                    bar.classList.add('bg-success'); // لون النجاح
                }
                if (lbl) lbl.innerText = '100%';

                // إعادة الزر لحالته الطبيعية بعد لحظات
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.width = ''; // إعادة العرض الافتراضي
                }, 1500);
            }
        };
    }

    function sendGlobalEmail() {
        const email = document.getElementById('ge_email').value;
        const subject = document.getElementById('ge_subject').value;
        const message = document.getElementById('ge_message_preview').value;

        if(!email) return alert('الرجاء إدخال البريد الإلكتروني');
        if(!subject) return alert('الرجاء إدخال عنوان الرسالة');

        const btn = document.getElementById('ge_send_btn');
        // بدء شريط التقدم (لمدة 3 دقائق تقريباً)
        const progressControl = simulateProgressBar(btn, 180000); 

        const mediaUrl = document.getElementById('ge_media_url').value;
        const filename = document.getElementById('ge_filename').value;

        fetch("{{ route('store.email.send') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ email: email, subject: subject, message: message, media_url: mediaUrl, filename: filename })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(data.message || 'تم إرسال البريد بنجاح!');
                bootstrap.Modal.getInstance(document.getElementById('globalEmailModal')).hide();
            } else {
                alert('فشل الإرسال: ' + (data.message || 'حدث خطأ غير متوقع'));
            }
        })
        .catch(err => alert('حدث خطأ أثناء الاتصال بالسيرفر'))
        .finally(() => {
            // إيقاف التقدم وإعادة الزر لطبيعته
            progressControl.stop();
        });
    }

    // --- نظام الواتساب الموحد (Universal WhatsApp Logic) ---
    let searchTimeout = null;

    function triggerWhatsappPrompt(phone, message, title = "إرسال الفاتورة عبر واتساب", mediaUrl = "", filename = "") {
        const modalEl = document.getElementById('globalWhatsappModal');
        if(!modalEl) return;
        
        document.getElementById('gw_modal_title').innerText = title;
        
        // تعيين الرقم
        const phoneInput = document.getElementById('gw_phone');
        phoneInput.value = phone || '';
        
        // إخفاء قائمة البحث عند الفتح
        document.getElementById('gw_search_results').style.display = 'none';

        document.getElementById('gw_message_preview').value = message || '';
        document.getElementById('gw_media_url').value = mediaUrl || '';
        document.getElementById('gw_filename').value = filename || '';
        
        // إذا كان هناك ملف، أظهر تنبيهاً بسيطاً للمستخدم
        const mediaSection = document.getElementById('gw_media_status');
        if (mediaUrl) {
            mediaSection.innerHTML = `<div class="alert alert-info py-2 mb-2 small"><i class="fas fa-paperclip me-1"></i> سيتم إرفاق ملف: ${filename || 'تقرير PDF'}</div>`;
        } else {
            mediaSection.innerHTML = '';
        }

        const myModal = new bootstrap.Modal(modalEl);
        myModal.show();
    }
    
    // دالة البحث الذكي
    // دالة البحث الموحدة (Autocomplete) - Global Definition
    window.setupSearch = function(inputId, resultsId, url, onSelect, autoSelect = false) {
        console.log(`Setting up search for: ${inputId}`);
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        let timeout = null;
        let currentFocus = -1; 

        if (!input || !results) {
            console.error(`Search elements not found: ${inputId} or ${resultsId}`);
            return;
        }

        // Force High Z-Index to prevent hiding behind other elements
        results.style.zIndex = '9999';

        input.addEventListener('input', function() {
            const term = this.value;
            currentFocus = -1; 
            
            if (term.length < 1) { 
                results.style.display = 'none';
                return;
            }

            clearTimeout(timeout);
            timeout = setTimeout(() => {
                console.log(`[DEBUG] Preparing to send request to: ${url}`);
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: { term: term },
                    dataType: 'text', 
                    cache: false,
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    },
                    beforeSend: function(xhr) {
                        console.log(`[DEBUG] Request init. ReadyState: ${xhr.readyState}`);
                    },
                    success: function(rawResponse) {
                        console.log(`[DEBUG] Success! Response lenth: ${rawResponse.length}`);
                        let data;
                        try {
                            data = JSON.parse(rawResponse);
                        } catch (e) {
                            console.error('Failed to parse JSON:', rawResponse);
                            results.innerHTML = `<div class="list-group-item text-danger">
                                <strong>رد غير متوقع من السيرفر!</strong><br>
                                <small>يبدو أن هناك خطأ برمجي (500) أو إعادة توجيه.</small>
                                <div class="mt-2 text-muted" style="font-size: 0.7em; max-height: 100px; overflow: auto;">${rawResponse.substring(0, 200)}...</div>
                            </div>`;
                            results.style.display = 'block';
                            return;
                        }

                        console.log(`Search results received: ${data.length} items`);
                        results.innerHTML = '';
                        if (data.length > 0) {
                            if (autoSelect && data.length === 1) {
                                // Relaxed condition: Auto-select if term length > 2 (numeric or text)
                                if (term.length > 2) {
                                     console.log('Auto-selecting single result');
                                     onSelect(data[0]);
                                     results.style.display = 'none';
                                     input.value = ''; 
                                     return;
                                }
                            }

                            data.forEach((item, index) => {
                                const isList = results.tagName === 'UL' || results.tagName === 'OL';
                                const el = document.createElement(isList ? 'li' : 'a');
                                
                                el.className = 'list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center';
                                el.style.cursor = 'pointer';
                                el.setAttribute('data-index', index);
                                
                                // استخدام text الموحد أو التراجع للحقول الفردية
                                let displayText = item.text || item.contact_name || item.name_ar || item.name;
                                let phoneText = item.phone || '';
                                
                                // تنسيق المحتوى
                                if (phoneText) {
                                    el.innerHTML = `<div><div class="fw-bold">${displayText}</div><small class="text-muted"><i class="fas fa-phone-alt me-1"></i> ${phoneText}</small></div>`;
                                    if(isList) el.innerHTML += `<button class="btn btn-sm btn-outline-primary select-contact-btn">اختيار</button>`;
                                } else {
                                    el.innerHTML = `<div class="fw-bold">${displayText}</div>`;
                                }
                                
                                el.onclick = (e) => {
                                    e.preventDefault();
                                    onSelect(item);
                                    results.style.display = 'none';
                                };
                                results.appendChild(el);
                            });
                            results.style.display = 'block';

                        } else {
                            const isList = results.tagName === 'UL' || results.tagName === 'OL';
                            const el = document.createElement(isList ? 'li' : 'div');
                            el.className = 'list-group-item text-muted';
                            el.textContent = 'لا توجد نتائج';
                            results.innerHTML = '';
                            results.appendChild(el);
                            results.style.display = 'block';
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('[DEBUG] Error Callback Triggered');
                        console.error('State:', xhr.readyState);
                        console.error('Status:', xhr.status);
                        console.error('TextStatus:', status);
                        console.error('Error:', error);
                        console.error('Raw Response:', xhr.responseText);
                        
                        let debugSafe = xhr.responseText ? xhr.responseText.substring(0, 300) : 'No response text';
                        let msg = 'خطأ غير معروف';
                        
                        if (xhr.readyState === 0) {
                            msg = 'فشل الاتصال تماماً! يرجى التأكد من أن السيرفر يعمل وأنك تستخدم الرابط الصحيح (http/https).';
                        } else if (xhr.status === 404) {
                            msg = 'الرابط غير موجود (404)';
                        } else if (xhr.status === 500) {
                            msg = 'خطأ في السيرفر (500)';
                        }

                        results.innerHTML = `<div class="list-group-item text-danger">
                            <strong>خطأ في الاتصال!</strong><br>
                            <small>${msg}</small><br>
                            <small class="text-muted" style="font-size: 0.7em;">Code: ${xhr.status} | State: ${xhr.readyState}</small><br>
                            <small class="text-muted" style="font-size: 0.6em; display:block; margin-top:5px;">Details: ${debugSafe.replace(/</g, '&lt;')}</small>
                        </div>`;
                        results.style.display = 'block';
                    },
                    complete: function(xhr, status) {
                        console.log(`[DEBUG] Request completed with status: ${status}`);
                    }
                });
            }, 300);
        });

        input.addEventListener('keydown', function(e) {
            const isList = results.tagName === 'UL' || results.tagName === 'OL';
            let items = results.getElementsByTagName(isList ? 'li' : 'a');
            
            if (e.key === 'ArrowDown') {
                currentFocus++;
                addActive(items);
            } else if (e.key === 'ArrowUp') {
                currentFocus--;
                addActive(items);
            } else if (e.key === 'Enter') {
                e.preventDefault(); 
                if (currentFocus > -1) {
                    if (items[currentFocus]) items[currentFocus].click();
                } else if (results.style.display === 'block' && items.length === 1) {
                     items[0].click();
                }
            }
        });

        function addActive(x) {
            if (!x) return false;
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            x[currentFocus].classList.add('active'); 
            x[currentFocus].scrollIntoView({block: 'nearest'});
        }

        function removeActive(x) {
            for (var i = 0; i < x.length; i++) {
                x[i].classList.remove('active');
            }
        }

        document.addEventListener('click', function(e) {
            if (e.target !== input && e.target !== results && !results.contains(e.target)) {
                results.style.display = 'none';
            }
        });
    };

    // تهيئة واتساب عند التحميل
    document.addEventListener("DOMContentLoaded", function() {
        const phoneInputEl = document.getElementById('gw_phone');
        
        const searchUrl = "{{ route('store.whatsapp.contacts.search') }}";
        
        setupSearch(
            'gw_phone',
            'gw_search_results',
            searchUrl,
            (contact) => {
                if(contact.phone) {
                    document.getElementById('gw_phone').value = contact.phone;
                    document.getElementById('gw_search_info').innerText = `تم اختيار: ${contact.name || contact.contact_name}`;
                } else {
                    alert('هذا العميل لا يملك رقم هاتف مسجل');
                }
            }
        );
    });
    
    // إخفاء القائمة عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#gw_search_results') && !e.target.closest('#gw_phone')) {
            document.getElementById('gw_search_results').style.display = 'none';
        }
    });

    function sendGlobalWhatsapp() {
        const phone = document.getElementById('gw_phone').value;
        const message = document.getElementById('gw_message_preview').value;

        if(!phone) return alert('الرجاء إدخال رقم الهاتف');

        // تنظيف الرقم
        let cleanPhone = phone.replace(/\D/g, '');
        
        // التحقق من وجود رمز دولي (مثال بسيط)
        if(cleanPhone.length < 9) return alert('رقم الهاتف غير صحيح');
        
        // إظهار لودينغ
        // إظهار شريط التقدم (لمدة 3 دقائق تقريباً)
        const btn = document.getElementById('gw_send_btn');
        const progressControl = simulateProgressBar(btn, 180000); 
        // btn.disabled = true; // يتم تعطيله داخل الدالة already

        const mediaUrl = document.getElementById('gw_media_url').value;
        const filename = document.getElementById('gw_filename').value;

        fetch("{{ route('store.whatsapp.send') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ phone: cleanPhone, message: message, media_url: mediaUrl, filename: filename })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(data.message || 'تم إرسال الرسالة بنجاح!');
                bootstrap.Modal.getInstance(document.getElementById('globalWhatsappModal')).hide();
            } else {
                // إظهار الرسالة القادمة من السيرفر بالتفصيل
                alert('فشل الإرسال: ' + (data.message || data.error || 'تأكد من ربط الواتساب بالسيرفر'));
            }
        })
        .catch(err => alert('حدث خطأ أثناء الاتصال بالسيرفر'))
        .finally(() => {
            // إيقاف التقدم وإعادة الزر لطبيعته
            progressControl.stop();
        });
    }
</script>

<!-- Global Email Confirmation Modal -->
<div class="modal fade" id="globalEmailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="ge_modal_title"><i class="fas fa-envelope me-2"></i> إرسال عبر البريد الإلكتروني</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">البريد الإلكتروني للمستلم</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary"><i class="fas fa-at"></i></span>
                        <input type="email" id="ge_email" class="form-control fw-bold border-primary text-center" placeholder="example@mail.com" autocomplete="off">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">عنوان الرسالة (Subject)</label>
                    <input type="text" id="ge_subject" class="form-control fw-bold border-light bg-light" dir="rtl">
                </div>
                
                <div class="mb-0">
                    <label class="form-label fw-bold">نص الرسالة</label>
                    <textarea id="ge_message_preview" class="form-control text-start border-light bg-light" rows="10" dir="rtl"></textarea>
                </div>
                <div id="ge_media_status" class="mt-2"></div>
                <input type="hidden" id="ge_media_url">
                <input type="hidden" id="ge_filename">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="ge_send_btn" class="btn btn-primary px-5 shadow fw-bold" onclick="sendGlobalEmail()">
                    <i class="fas fa-paper-plane me-2"></i> إرسال الآن
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Global Expiry Action Modal (Dispose/Extend) -->
<div class="modal fade" id="expiryActionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="ea_modal_title">إدارة الصلاحية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-pills nav-fill p-2 bg-light rounded-top border-bottom" role="tablist">
                    <li class="nav-item p-1">
                        <button class="nav-link active fw-bold py-2" id="ea_dispose_tab" data-bs-toggle="pill" data-bs-target="#ea_dispose_pane" style="transition: all 0.3s;">
                            <i class="fas fa-trash-alt me-1"></i> إتلاف مخزون
                        </button>
                    </li>
                    <li class="nav-item p-1">
                        <button class="nav-link fw-bold py-2" id="ea_extend_tab" data-bs-toggle="pill" data-bs-target="#ea_extend_pane" style="transition: all 0.3s;">
                            <i class="fas fa-calendar-plus me-1"></i> تمديد/تصحيح تاريخ
                        </button>
                    </li>
                </ul>
                <style>
                    #expiryActionModal .nav-pills .nav-link.active {
                        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    }
                    #ea_dispose_tab.active { background-color: #dc3545 !important; color: white !important; }
                    #ea_extend_tab.active { background-color: #0d6efd !important; color: white !important; }
                    #ea_dispose_tab:not(.active) { color: #dc3545; }
                    #ea_extend_tab:not(.active) { color: #0d6efd; }
                </style>

                <div class="tab-content p-4">
                    {{-- TAB 1: DISPOSE --}}
                    <div class="tab-pane fade show active" id="ea_dispose_pane">
                        <form action="{{ route('store.products.dispose') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="batch_id" id="ea_dispose_batch_id">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">المنتج والتاريخ:</label>
                                <div id="ea_dispose_product_info" class="alert alert-secondary py-2 mb-0"></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">الكمية للإتلاف:</label>
                                    <input type="number" name="quantity" id="ea_dispose_qty" class="form-control" step="0.01" min="0.01" required>
                                    <div class="form-text text-muted">الحد الأقصى المتوفر: <span id="ea_dispose_max_display">0</span></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">السبب:</label>
                                    <input type="text" name="reason" class="form-control" placeholder="مثلاً: انتهاء الصلاحية، تلف، كسر.." required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-danger fw-bold star">صورة إثبات الإتلاف (مطلوب):</label>
                                <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                                <div class="form-text small">يرجى تصوير المنتجات لتوثيق الحالة.</div>
                            </div>

                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('هل أنت متأكد؟ هذا الإجراء سيخصم الكمية ويسجلها كتالف.')">
                                تأكيد الإتلاف
                            </button>
                        </form>
                    </div>

                    {{-- TAB 2: EXTEND --}}
                    <div class="tab-pane fade" id="ea_extend_pane">
                        <form action="{{ route('store.products.extend') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="batch_id" id="ea_extend_batch_id">

                            <div class="mb-3">
                                <label class="form-label fw-bold">المنتج والكمية:</label>
                                <div id="ea_extend_product_info" class="alert alert-secondary py-2 mb-0"></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">الكمية للتصحيح:</label>
                                    <input type="number" name="quantity" id="ea_extend_qty" class="form-control" step="0.01" min="0.01" required>
                                    <div class="form-text text-muted">الحد الأقصى: <span id="ea_extend_max_display">0</span></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-primary fw-bold">تاريخ الصلاحية الجديد:</label>
                                    <input type="date" name="new_date" class="form-control" required min="{{ date('Y-m-d') }}" style="position: relative; z-index: 10;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">صورة المنتج/التاريخ الجديد (إثبات):</label>
                                <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">سبب التعديل:</label>
                                <input type="text" name="reason" class="form-control" placeholder="مثلاً: خطأ في الإدخال، تمديد من الشركة.." required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                حفظ التعديلات
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Global WhatsApp Confirmation Modal -->
<div class="modal fade" id="globalWhatsappModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold" id="gw_modal_title"><i class="fab fa-whatsapp me-2"></i> إرسال عبر واتساب</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 position-relative">
                    <label class="form-label fw-bold">رقم المستلم (اكتب للبحث في جهات الاتصال)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-success"><i class="fas fa-search"></i></span>
                        <input type="text" id="gw_phone" class="form-control fw-bold border-success text-center" placeholder="اكتب الاسم أو الرقم..." autocomplete="off">
                    </div>
                    
                    {{-- قائمة نتائج البحث --}}
                    <ul id="gw_search_results" class="list-group position-absolute w-100 shadow mt-1" style="display:none; z-index: 9999; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd;">
                        <!-- Results will be injected here -->
                    </ul>
                    <small id="gw_search_info" class="text-primary fw-bold mt-1 d-block"></small>
                </div>
                
                <div class="mb-0">
                    <label class="form-label fw-bold">نص الرسالة</label>
                    <textarea id="gw_message_preview" class="form-control text-start border-light bg-light" rows="10" dir="rtl"></textarea>
                </div>
                <div id="gw_media_status" class="mt-2"></div>
                <input type="hidden" id="gw_media_url">
                <input type="hidden" id="gw_filename">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="gw_send_btn" class="btn btn-success px-5 shadow fw-bold" onclick="sendGlobalWhatsapp()">
                    <i class="fas fa-paper-plane me-2"></i> تأكيد وإرسال الآن
                </button>
            </div>
        </div>
    </div>
</div>
    <!-- ================================================== -->
    <!-- ⚡ AUTOMATIC QUEUE WORKER (LOCAL & ONLINE) ⚡ -->
    <!-- ================================================== -->
    <script>
        $(document).ready(function() {
            // function to run queue worker
            function runQueueWorker() {
                $.ajax({
                    url: "{{ route('queue.run') }}",
                    type: "GET",
                    timeout: 45000, // Timeout slightly less than PHP execution time
                    success: function(response) {
                        if(response.status === 'success') {
                            console.log("Queue processed:", response.message);
                        } else {
                            console.log("Queue status:", response.message);
                        }
                    },
                    error: function(xhr) {
                        // Silent fail (don't annoy user)
                        console.log("Queue worker pause/error");
                    },
                    complete: function() {
                        // Schedule next run after 20 seconds (prevent request flooding)
                        setTimeout(runQueueWorker, 20000);
                    }
                });
            }
            
            // Start the worker loop after 5 seconds of page load
            setTimeout(runQueueWorker, 5000);
        });
    </script>
</body>
</html>