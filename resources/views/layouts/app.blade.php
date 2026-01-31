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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            font-weight: 700;
            letter-spacing: 0.3px;
            font-size: 0.8rem;
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
                                {{-- <li class="sidebar-heading">إدارة المتجر</li> --}}
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
                                <li class="nav-item">
                                    <a href="{{ route('store.expenses.index') }}" class="nav-link {{ request()->routeIs('store.expenses.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-wallet"></i>
                                        <p>المصاريف</p>
                                    </a>
                                </li>
{{-- قائمة المبيعات --}}
<li class="nav-item">
    <a href="#" class="nav-link {{ request()->routeIs('store.pos.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#salesCollapse" role="button" aria-expanded="{{ request()->routeIs('store.pos.*') ? 'true' : 'false' }}">
        <i class="nav-icon fas fa-cash-register"></i>
        <span>المبيعات</span>
        <i class="fas fa-angle-left ms-auto"></i>
    </a>
    <div class="collapse {{ request()->routeIs('store.pos.*') && !request()->routeIs('store.pos.withdrawals') ? 'show' : '' }}" id="salesCollapse">
        <ul class="nav flex-column ps-3">
            <li class="nav-item">
                <a href="{{ route('store.pos.index') }}" class="nav-link {{ request()->routeIs('store.pos.index') ? 'active' : '' }}">
                    <i class="far fa-circle"></i>
                    <span>نقطة بيع (POS)</span>
                </a>
            </li>
        </ul>
    </div>
</li>
                                <li class="sidebar-heading">الإعدادات</li>
    {{-- قسم التقارير --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : 'collapsed' }}" href="#" data-bs-toggle="collapse" data-bs-target="#reportsCollapse" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
        <i class="fas fa-chart-line"></i>
        <span>التقارير</span>
        <i class="fas fa-angle-left ms-auto"></i>
    </a>
    <div id="reportsCollapse" class="collapse {{ request()->routeIs('reports.*') || request()->routeIs('store.pos.withdrawals') ? 'show' : '' }}">
        <ul class="nav flex-column ps-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.shifts') ? 'active' : '' }}" href="{{ route('reports.shifts') }}">
                    <i class="fas fa-cash-register"></i> 
                    <span>تقرير الصناديق</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('store.pos.withdrawals') ? 'active' : '' }}" href="{{ route('store.pos.withdrawals') }}">
                    <i class="fas fa-hand-holding-usd"></i> 
                    <span>مسحوبات المالك</span>
                </a>
            </li>
        </ul>
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
    document.addEventListener("DOMContentLoaded", function() {
        const phoneInputEl = document.getElementById('gw_phone');
        if(phoneInputEl) {
            console.log('WhatsApp Search Listener Attached');
            phoneInputEl.addEventListener('keyup', handleSearch);
            phoneInputEl.addEventListener('paste', function() {
                setTimeout(handleSearch, 100);
            });
        } else {
            console.error('WhatsApp Phone Input Not Found!');
        }
    });

    function handleSearch() {
        const query = document.getElementById('gw_phone').value;
        const resultsInfo = document.getElementById('gw_search_info');
        const resultsList = document.getElementById('gw_search_results');
        
        if(query.length < 1) {
            resultsList.style.display = 'none';
            resultsInfo.innerText = '';
            return;
        }
        
        resultsInfo.innerText = 'جاري البحث...';
        
        if(searchTimeout) clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            fetch("{{ route('store.whatsapp.contacts.search') }}?q=" + query)
            .then(res => res.json())
            .then(data => {
                resultsList.innerHTML = '';
                
                if(data.length > 0) {
                    data.forEach(contact => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center';
                        li.style.cursor = 'pointer'; // Ensure pointer cursor
                        li.innerHTML = `
                            <div>
                                <div class="fw-bold">${contact.name}</div>
                                <small class="text-muted"><i class="fas fa-phone-alt me-1"></i> ${contact.phone || 'بدون رقم'}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary select-contact-btn">اختيار</button>
                        `;
                        
                        // عند الضغط على العنصر
                        li.onclick = function() {
                            if(contact.phone) {
                                document.getElementById('gw_phone').value = contact.phone;
                                resultsList.style.display = 'none';
                                resultsInfo.innerText = `تم اختيار: ${contact.name}`;
                            } else {
                                alert('هذا العميل لا يملك رقم هاتف مسجل');
                            }
                        };
                        
                        resultsList.appendChild(li);
                    });
                    resultsList.style.display = 'block';
                    resultsInfo.innerText = '';
                } else {
                    resultsList.style.display = 'none';
                    resultsInfo.innerText = 'لا توجد نتائج';
                }
            })
            .catch(err => {
                console.error(err);
                resultsInfo.innerText = ''; // Hide error text to not confuse user if it's just a network blip
            });
        }, 300);
    }
    
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
        const btn = document.getElementById('gw_send_btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري الإرسال...';
        btn.disabled = true;

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
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>

<!-- Global WhatsApp Confirmation Modal -->
<div class="modal fade" id="globalWhatsappModal" tabindex="-1" aria-hidden="true">
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
</script>
</body>
</html>