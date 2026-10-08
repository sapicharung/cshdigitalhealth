@php
    $clientIp = request()->header('X-Forwarded-For') 
        ? trim(explode(',', request()->header('X-Forwarded-For'))[0]) 
        : request()->ip();
    $isInstalledOnThisIp = false;
    if ($clientIp && !in_array($clientIp, ['127.0.0.1', '::1'])) {
        $isInstalledOnThisIp = \App\Models\AppInstallation::where('ip_address', $clientIp)
            ->where('install_type', 'installed')
            ->exists();
    }
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Digital Health - ระบบบริการสารสนเทศสำหรับเจ้าหน้าที่</title>
    
    <!-- PWA & Desktop Web App Installation -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <meta name="theme-color" content="#64B5F6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CSHOS DATACENTER">

    <!-- PWA Installation Engine & ServiceWorker -->
    <script>
        window.deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            console.log('CSHOS PWA: beforeinstallprompt ready!');
        });

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset("sw.js") }}', { scope: '/' })
                .then((reg) => {
                    console.log('CSHOS PWA ServiceWorker active, scope:', reg.scope);
                })
                .catch((err) => {
                    console.warn('CSHOS PWA ServiceWorker error:', err);
                });
        }

        (function() {
            try {
                if (window.location.search.includes('reset=1')) {
                    localStorage.removeItem('csh_app_installed');
                    localStorage.removeItem('csh_device_uuid');
                }
                const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
                if (isStandalone) {
                    document.documentElement.classList.add('app-installed');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts & Resource Hints for Crisp Thai Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <style>
        :root {
            --pastel-blue-light: #f0f7ff;
            --pastel-blue-dark: #64B5F6;
            --pastel-cream: #FFFDF5;
            --text-blue-dark: #2C3E50;
            --white: #ffffff;
            
            /* Pastel Gradients */
            --grad-blue: linear-gradient(135deg, #7EC8F8, #4D9DE0);
            --grad-teal: linear-gradient(135deg, #7FE3D5, #3DB4A6);
            --grad-orange: linear-gradient(135deg, #FFCBA4, #FF9E6C);
            --grad-purple: linear-gradient(135deg, #D4B2F7, #A777E3);
            --grad-green: linear-gradient(135deg, #A8E6CF, #57C785);
            --grad-rose: linear-gradient(135deg, #FFB2C7, #F0728F);
        }

        * {
            box-sizing: border-box;
        }

        html {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Noto Sans Thai', 'Sarabun', sans-serif;
            margin: 0; padding: 0;
            color: var(--text-blue-dark);
            background-color: var(--pastel-cream);
            overflow-x: hidden;
            line-height: 1.55;
            letter-spacing: 0.01em;
        }

        /* --- Navbar --- */
        .navbar {
            padding: 0.7rem 4%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.03);
            position: sticky; top: 0; z-index: 1000;
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }
        .brand-logo {
            height: 44px;
            width: auto;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .brand-text strong { 
            display: block; 
            font-size: 1.15rem; 
            line-height: 1.35; 
            font-weight: 700; 
            color: #1e3a8a;
            letter-spacing: -0.2px;
        }
        .brand-text small { 
            display: block;
            color: #2563eb; 
            font-size: 0.85rem; 
            letter-spacing: 0.3px; 
            font-weight: 600; 
            margin-top: 1px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Hide Install App button on machines where app is already installed */
        html.app-installed #btnInstallApp,
        .app-installed .btn-install-app {
            display: none !important;
        }

        .btn-install-app {
            padding: 8px 20px; border-radius: 50px; 
            border: none;
            background: linear-gradient(135deg, #42a5f5, #1e88e5);
            color: white;
            font-family: inherit; font-size: 0.88rem; font-weight: 500;
            cursor: pointer; transition: all 0.3s ease;
            display: inline-flex; align-items: center; gap: 6px;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.28);
            line-height: 1.4;
            letter-spacing: 0.01em;
        }
        .btn-install-app:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 136, 229, 0.45);
            background: linear-gradient(135deg, #1e88e5, #1565c0);
        }

        .btn-knowledge { 
            padding: 8px 22px; border-radius: 50px; 
            border: 1px solid var(--pastel-blue-dark);
            background: transparent; color: var(--pastel-blue-dark);
            font-family: inherit; font-size: 0.88rem; font-weight: 500;
            text-decoration: none; transition: 0.3s;
            display: inline-flex; align-items: center; gap: 6px;
            line-height: 1.4;
            letter-spacing: 0.01em;
        }
        .btn-knowledge:hover { background: var(--pastel-blue-dark); color: white; transform: translateY(-1px); }

        /* --- Services Container & Category Blocks --- */
        .services { padding: 1.8rem 4% 3rem; }
        .container { max-width: 1350px; margin: 0 auto; }
        
        .category-block {
            margin-bottom: 2.8rem;
            transition: all 0.3s ease;
        }

        .category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid rgba(226, 232, 240, 0.8);
        }
        .category-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .category-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .category-icon-badge .material-symbols-outlined {
            font-size: 24px;
        }
        .category-title h3 {
            font-size: 1.25rem;
            margin: 0;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.35;
            letter-spacing: -0.2px;
        }
        .category-title small {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 400;
            line-height: 1.4;
            display: block;
            margin-top: 2px;
        }
        .category-count {
            font-family: inherit;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 500;
            padding: 4px 14px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            line-height: 1.4;
        }

        /* --- Service Cards Grid --- */
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
            gap: 16px;
        }

        .service-card {
            background: var(--white);
            padding: 22px 14px 18px;
            border-radius: 20px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.03);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            border: 1px solid rgba(0,0,0,0.04);
            text-align: center;
            position: relative;
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 22px rgba(0,0,0,0.08);
            border-color: var(--pastel-blue-dark);
        }

        /* Network Tag (LAN / Cloud) */
        .badge-network {
            position: absolute;
            top: 10px;
            right: 12px;
            font-family: inherit;
            font-size: 0.68rem;
            font-weight: 600;
            padding: 2.5px 8px;
            border-radius: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .badge-lan {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .badge-cloud {
            background: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }

        /* Pastel Card Themes */
        .service-card.card-blue {
            background: linear-gradient(180deg, #ffffff 20%, #f1f7ff 100%);
            border: 1px solid #ddecfa;
        }
        .service-card.card-blue:hover {
            border-color: #90caf9;
            box-shadow: 0 12px 24px rgba(100, 181, 246, 0.22);
        }

        .service-card.card-teal {
            background: linear-gradient(180deg, #ffffff 20%, #eefbf7 100%);
            border: 1px solid #d3f3ed;
        }
        .service-card.card-teal:hover {
            border-color: #80cbc4;
            box-shadow: 0 12px 24px rgba(61, 180, 166, 0.22);
        }

        .service-card.card-orange {
            background: linear-gradient(180deg, #ffffff 20%, #fff8f2 100%);
            border: 1px solid #fde8db;
        }
        .service-card.card-orange:hover {
            border-color: #ffcc80;
            box-shadow: 0 12px 24px rgba(255, 158, 108, 0.22);
        }

        .service-card.card-purple {
            background: linear-gradient(180deg, #ffffff 20%, #fbf6ff 100%);
            border: 1px solid #efe3fc;
        }
        .service-card.card-purple:hover {
            border-color: #ce93d8;
            box-shadow: 0 12px 24px rgba(167, 119, 227, 0.22);
        }

        .service-card.card-green {
            background: linear-gradient(180deg, #ffffff 20%, #f2faf5 100%);
            border: 1px solid #d8f3e1;
        }
        .service-card.card-green:hover {
            border-color: #a5d6a7;
            box-shadow: 0 12px 24px rgba(87, 199, 133, 0.22);
        }

        .service-card.card-rose {
            background: linear-gradient(180deg, #ffffff 20%, #fff5f7 100%);
            border: 1px solid #fde0e6;
        }
        .service-card.card-rose:hover {
            border-color: #f48fb1;
            box-shadow: 0 12px 24px rgba(240, 114, 143, 0.22);
        }

        .icon-box {
            width: 54px; height: 54px; margin-bottom: 12px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s ease;
        }

        .service-card:hover .icon-box {
            transform: scale(1.08);
        }
        
        .material-symbols-outlined,
        .material-symbols-Outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            vertical-align: middle;
        }

        .icon-box .material-symbols-outlined,
        .icon-box .material-symbols-Outlined {
            font-size: 28px;
            color: var(--white);
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 40;
        }

        .service-card h4 { 
            font-size: 0.94rem; 
            color: #1e293b; 
            line-height: 1.45; 
            margin: 0; 
            font-weight: 600;
            word-break: break-word;
            overflow-wrap: break-word;
            min-height: 2.9em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            letter-spacing: -0.01em;
        }

        .service-card:hover h4 {
            color: #1976d2;
        }

        /* Icon Colors Classes */
        .ic-blue { background: var(--grad-blue); box-shadow: 0 4px 12px rgba(77, 157, 224, 0.35); }
        .ic-teal { background: var(--grad-teal); box-shadow: 0 4px 12px rgba(61, 180, 166, 0.35); }
        .ic-orange { background: var(--grad-orange); box-shadow: 0 4px 12px rgba(255, 158, 108, 0.35); }
        .ic-purple { background: var(--grad-purple); box-shadow: 0 4px 12px rgba(167, 119, 227, 0.35); }
        .ic-green { background: var(--grad-green); box-shadow: 0 4px 12px rgba(87, 199, 133, 0.35); }
        .ic-rose, .ic-red { background: var(--grad-rose); box-shadow: 0 4px 12px rgba(240, 114, 143, 0.35); }


        footer { 
            background: #f8fafc; padding: 2.2rem 1rem; text-align: center; 
            font-size: 0.88rem; color: #64748b; border-top: 1px solid #e2e8f0;
            line-height: 1.6;
        }
        footer p {
            margin: 0;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            .navbar { padding: 0.6rem 4%; }
            .brand-logo { height: 36px; }
            .brand-text strong { font-size: 0.95rem; line-height: 1.3; }
            .brand-text small { font-size: 0.78rem; }
            .service-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
            .service-card { padding: 18px 10px 14px; }
            .service-card h4 { font-size: 0.86rem; line-height: 1.4; min-height: 2.8em; }
            .icon-box { width: 48px; height: 48px; }
            .category-title h3 { font-size: 1.1rem; line-height: 1.35; }
        }

        /* --- Desktop Install Modal --- */
        .install-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 16px;
            animation: fadeIn 0.2s ease-out;
        }

        .install-modal-overlay.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .install-modal-card {
            background: #ffffff;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
            animation: slideUp 0.25s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .install-modal-header {
            background: linear-gradient(135deg, #1e40af, #2563eb);
            color: white;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .install-modal-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .install-modal-title h3 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
        }

        .install-modal-title small {
            color: #bfdbfe;
            font-size: 0.78rem;
            display: block;
        }

        .install-modal-close {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .install-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .install-modal-body {
            padding: 22px 24px;
            color: #334155;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .install-step-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
        }

        .install-step-card.highlight {
            background: #eff6ff;
            border: 1.5px solid #60a5fa;
        }

        .install-step-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .install-step-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #1e3a8a;
            font-size: 0.95rem;
        }

        .install-step-badge {
            background: #2563eb;
            color: white;
            font-size: 0.72rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }
        .install-step-badge.outline {
            background: #e2e8f0;
            color: #475569;
        }

        .install-step-desc {
            font-size: 0.85rem;
            color: #475569;
            margin: 0;
            line-height: 1.55;
        }

        .install-step-desc strong {
            color: #1e293b;
        }

        .install-guide-steps {
            list-style: none;
            padding: 0;
            margin: 8px 0 0 0;
            font-size: 0.83rem;
            color: #475569;
        }

        .install-guide-steps li {
            margin-bottom: 6px;
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .install-guide-steps li .step-num {
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .install-modal-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }

        .btn-modal-primary {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }
        .btn-modal-primary:hover { background: #1d4ed8; }

        .btn-modal-outline {
            background: white;
            color: #3b82f6;
            border: 1px solid #cbd5e1;
            padding: 8px 14px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-modal-outline:hover { background: #f8fafc; border-color: #94a3b8; }

        .btn-modal-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 8px 14px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-modal-secondary:hover { background: #e2e8f0; }

        @keyframes spinIcon {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin-icon {
            animation: spinIcon 1s linear infinite;
            display: inline-block;
        }
        .install-toast {
            position: fixed;
            top: 28px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            color: #0f172a;
            padding: 14px 28px;
            border-radius: 9999px;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.25), 0 0 0 2px #10b981;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1rem;
            font-weight: 600;
            z-index: 999999;
            animation: toastSlide 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes toastSlide {
            from { opacity: 0; transform: translate(-50%, -25px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        .toast-fadeout {
            animation: toastFade 0.4s ease forwards !important;
        }
        @keyframes toastFade {
            from { opacity: 1; transform: translate(-50%, 0); }
            to { opacity: 0; transform: translate(-50%, -20px); }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="/" class="brand">
                <img src="{{ asset('csh-logo.jpg') }}" alt="CSH Logo" class="brand-logo" onerror="this.style.display='none'">
                <div class="brand-text">
                    <strong>ระบบบริการสารสนเทศดิจิทัลสำหรับเจ้าหน้าที่</strong>
                    <small>โรงพยาบาลเชียงแสน</small>
                </div>
            </a>
            <div class="nav-actions">
                <button id="btnInstallApp" class="btn-install-app" onclick="installPWA()">
                    <span class="material-symbols-outlined" style="font-size: 20px;">install_desktop</span>
                    ติดตั้งแอปบนคอมฯ
                </button>
                <a href="http://192.168.1.10:8086/knowledge" target="_blank" class="btn-knowledge">
                    <span class="material-symbols-outlined" style="font-size: 18px; color: inherit;">auto_stories</span>
                    คลังความรู้
                </a>
            </div>
        </div>
    </nav>

    <!-- Services Grid Categories -->
    <section class="services">
        <div class="container">

            <!-- หมวดที่ 1: ระบบบริการการแพทย์และผู้ป่วย -->
            <div class="category-block" data-category="clinical">
                <div class="category-header">
                    <div class="category-title-wrap">
                        <div class="category-icon-badge" style="background: var(--grad-blue);">
                            <span class="material-symbols-outlined">medical_services</span>
                        </div>
                        <div class="category-title">
                            <h3>ระบบบริการการแพทย์และผู้ป่วย</h3>
                            <small>Clinical & Patient Care Systems</small>
                        </div>
                    </div>
                    <span class="category-count">4 ระบบ</span>
                </div>
                <div class="service-grid">
                    <a href="https://cshos.moph.go.th/doctor_schedule/" target="_blank" class="service-card card-blue" data-network="cloud" data-keywords="ตารางเวรแพทย์ ตารางตรวจ แพทย์ หมอ doctor schedule">
                        <span class="badge-network badge-cloud">Cloud</span>
                        <div class="icon-box ic-blue"><span class="material-symbols-outlined">calendar_month</span></div>
                        <h4>ตารางเวรแพทย์</h4>
                    </a>
                    <a href="http://192.168.1.10:8094/duty-rosters" target="_blank" class="service-card card-orange" data-network="lan" data-keywords="ตารางเวร it oncall it-oncall คอมพิวเตอร์ เวรไอที">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-orange"><span class="material-symbols-outlined">contact_phone</span></div>
                        <h4>ตารางเวร IT-Oncall</h4>
                    </a>
                    <a href="https://script.google.com/macros/s/AKfycbwSEdngNprw6H_zdAMj6O0qtCPLiV6xHkhmdDeGQjsi1Le2V_c7lBU9u8jq6jZ-uOl0gQ/exec" target="_blank" class="service-card card-rose" data-network="cloud" data-keywords="blood bank bloodbank คลังเลือด ธนาคารเลือด เลือด">
                        <span class="badge-network badge-cloud">Cloud</span>
                        <div class="icon-box ic-rose"><span class="material-symbols-outlined">bloodtype</span></div>
                        <h4>BLOOD BANK</h4>
                    </a>
                    <a href="{{ route('blood-alc.index') }}" target="_blank" class="service-card card-teal" data-network="lan" data-keywords="bloodalc blood alc ตรวจแอลกอฮอล์ เลือด ทะเบียน แอลกอฮอล์">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-teal"><span class="material-symbols-outlined">bloodtype</span></div>
                        <h4>BloodAlc</h4>
                    </a>
                </div>
            </div>

            <!-- หมวดที่ 2: ศูนย์ข้อมูล สถิติ และรายงาน -->
            <div class="category-block" data-category="data">
                <div class="category-header">
                    <div class="category-title-wrap">
                        <div class="category-icon-badge" style="background: var(--grad-purple);">
                            <span class="material-symbols-outlined">analytics</span>
                        </div>
                        <div class="category-title">
                            <h3>ศูนย์ข้อมูล สถิติ และรายงาน</h3>
                            <small>Data Center, Statistics & HDC Reporting</small>
                        </div>
                    </div>
                    <span class="category-count">4 ระบบ</span>
                </div>
                <div class="service-grid">
                    <a href="http://192.168.1.10:8083/" target="_blank" class="service-card card-blue" data-network="lan" data-keywords="datacenter data center ศูนย์ข้อมูล ฐานข้อมูล server">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-blue"><span class="material-symbols-outlined">database</span></div>
                        <h4>DataCenter</h4>
                    </a>
                    <a href="http://192.168.1.10:8083/reports" target="_blank" class="service-card card-teal" data-network="lan" data-keywords="data-report data report รายงาน สถิติ ข้อมูล">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-teal"><span class="material-symbols-outlined">monitoring</span></div>
                        <h4>Data-Report</h4>
                    </a>
                    <a href="http://192.168.1.10:8090/" target="_blank" class="service-card card-orange" data-network="lan" data-keywords="แปลงข้อมูล hdc ส่งออก hdc 43 แฟ้ม standard">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-orange"><span class="material-symbols-outlined">analytics</span></div>
                        <h4>แปลงข้อมูล HDC</h4>
                    </a>
                    <a href="https://hdc.moph.go.th/cri" target="_blank" class="service-card card-green" data-network="cloud" data-keywords="hdc moph สสจ เชียงราย ตัวชี้วัด กระทรวง">
                        <span class="badge-network badge-cloud">Cloud</span>
                        <div class="icon-box ic-green"><span class="material-symbols-outlined">leaderboard</span></div>
                        <h4>HDC MOPH</h4>
                    </a>
                </div>
            </div>

            <!-- หมวดที่ 3: ระบบบริหารจัดการ บุคลากร และการเงิน -->
            <div class="category-block" data-category="admin">
                <div class="category-header">
                    <div class="category-title-wrap">
                        <div class="category-icon-badge" style="background: var(--grad-orange);">
                            <span class="material-symbols-outlined">corporate_fare</span>
                        </div>
                        <div class="category-title">
                            <h3>ระบบบริหารจัดการ บุคลากร และการเงิน</h3>
                            <small>Administration, HR, Finance & Operations</small>
                        </div>
                    </div>
                    <span class="category-count">6 ระบบ</span>
                </div>
                <div class="service-grid">
                    <a href="http://192.168.1.2/" target="_blank" class="service-card card-blue" data-network="lan" data-keywords="smartoffice smart office สารบรรณ หนังสือเวียน เอกสาร">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-blue"><span class="material-symbols-outlined">corporate_fare</span></div>
                        <h4>SmartOffice</h4>
                    </a>
                    <a href="http://192.168.1.10:8091/" target="_blank" class="service-card card-teal" data-network="lan" data-keywords="สแกนเข้างาน ลงเวลา ทำงาน เข้างาน ออกงาน นิ้ว">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-teal"><span class="material-symbols-outlined">fingerprint</span></div>
                        <h4>สแกนเข้างาน</h4>
                    </a>
                    <a href="http://192.168.1.10:8094/" target="_blank" class="service-card card-orange" data-network="lan" data-keywords="ข้อมูลบุคลากร ทะเบียนประวัติ เจ้าหน้าที่ รายชื่อ">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-orange"><span class="material-symbols-outlined">groups</span></div>
                        <h4>ระบบบริหารทรัพยากรบุคคล</h4>
                    </a>
                    <a href="https://mmis-11193.moph.go.th/" target="_blank" class="service-card card-purple" data-network="cloud" data-keywords="mmis ระบบ mmis พัสดุ moph จัดซื้อจัดจ้าง">
                        <span class="badge-network badge-cloud">Cloud</span>
                        <div class="icon-box ic-purple"><span class="material-symbols-outlined">inventory_2</span></div>
                        <h4>ระบบ MMIS</h4>
                    </a>
                    <a href="http://192.168.1.10:8085" target="_blank" class="service-card card-rose" data-network="lan" data-keywords="รายงานความปลอดภัย ความปลอดภัย รปภ อัคคีภัย สิ่งแวดล้อม">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-rose"><span class="material-symbols-outlined">security</span></div>
                        <h4>รายงานความปลอดภัย</h4>
                    </a>
                    <a href="https://csh.thai-nrls.org/" target="_blank" class="service-card card-green" data-network="cloud" data-keywords="รายงานความเสี่ยง ความเสี่ยง nrls incident อุบัติการณ์">
                        <span class="badge-network badge-cloud">Cloud</span>
                        <div class="icon-box ic-green"><span class="material-symbols-outlined">report_problem</span></div>
                        <h4>รายงานความเสี่ยง</h4>
                    </a>
                </div>
            </div>

            <!-- หมวดที่ 4: ระบบเฉพาะหน่วยงาน -->
            <div class="category-block" data-category="department">
                <div class="category-header">
                    <div class="category-title-wrap">
                        <div class="category-icon-badge" style="background: var(--grad-teal);">
                            <span class="material-symbols-outlined">workspaces</span>
                        </div>
                        <div class="category-title">
                            <h3>ระบบเฉพาะหน่วยงาน</h3>
                            <small>Department-Specific Systems</small>
                        </div>
                    </div>
                    <span class="category-count">12 ระบบ</span>
                </div>
                <div class="service-grid">
                    <a href="http://192.168.1.10:8092/" target="_blank" class="service-card card-teal" data-network="lan" data-keywords="doctororder doctor order คำสั่งแพทย์">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-teal"><span class="material-symbols-outlined">note_add</span></div>
                        <h4>DoctorOrder</h4>
                    </a>
                    <a href="http://192.168.1.10:8097/" target="_blank" class="service-card card-orange" data-network="lan" data-keywords="คัดกรอง triage ฉุกเฉิน er opd">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-orange"><span class="material-symbols-outlined">medical_services</span></div>
                        <h4>คัดกรอง Triage</h4>
                    </a>
                    <a href="http://192.168.1.9/vopds" target="_blank" class="service-card card-purple" data-network="lan" data-keywords="opd scan opd-scan สแกนเวชระเบียน แฟ้มประวัติ">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-purple"><span class="material-symbols-outlined">folder_shared</span></div>
                        <h4>OPD-SCAN</h4>
                    </a>
                    <a href="https://homeward.dms.go.th/" target="_blank" class="service-card card-rose" data-network="cloud" data-keywords="homeward home ward ดูแลผู้ป่วยที่บ้าน เยี่ยมบ้าน">
                        <span class="badge-network badge-cloud">Cloud</span>
                        <div class="icon-box ic-rose"><span class="material-symbols-outlined">home_health</span></div>
                        <h4>HomeWard</h4>
                    </a>
                    <a href="http://192.168.1.10:8093/" target="_blank" class="service-card card-green" data-network="lan" data-keywords="drugrefill drug refill ยา เภสัชกรรม รับยา">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-green"><span class="material-symbols-outlined">medication</span></div>
                        <h4>DrugRefill</h4>
                    </a>
                    <a href="http://192.168.1.10:8082/" target="_blank" class="service-card card-blue" data-network="lan" data-keywords="สารบรรณการเงิน การเงิน ฎีกา เบิกจ่าย บัญชี">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-blue"><span class="material-symbols-outlined">description</span></div>
                        <h4>สารบรรณการเงิน</h4>
                    </a>
                    <a href="http://192.168.1.10:8095/" target="_blank" class="service-card card-teal" data-network="lan" data-keywords="ทะเบียนลูกหนี้ชำระเงิน ลูกหนี้ การเงิน ชำระเงิน ใบเสร็จ">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-teal"><span class="material-symbols-outlined">receipt_long</span></div>
                        <h4>ทะเบียนลูกหนี้ชำระเงิน</h4>
                    </a>
                    <a href="http://192.168.1.10:8089/" target="_blank" class="service-card card-orange" data-network="lan" data-keywords="คลังวัสดุฯ พัสดุ คลัง เบิกพัสดุ เวชภัณฑ์">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-orange"><span class="material-symbols-outlined">vaccines</span></div>
                        <h4>คลังวัสดุฯ</h4>
                    </a>
                    <a href="http://192.168.1.10:8081/" target="_blank" class="service-card card-purple" data-network="lan" data-keywords="ระบบใบคดี ใบคดี ตำรวจ ชันสูตร คดีความ">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-purple"><span class="material-symbols-outlined">gavel</span></div>
                        <h4>ระบบใบคดี</h4>
                    </a>
                    <a href="http://192.168.1.10:8099/" target="_blank" class="service-card card-rose" data-network="lan" data-keywords="ntip reporting ntip reporting วัณโรค รายงาน สถิติ report">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-rose"><span class="material-symbols-outlined">query_stats</span></div>
                        <h4>NTIP-Report</h4>
                    </a>
                    <a href="http://192.168.1.20/kumhosnapapi" target="_blank" class="service-card card-teal" data-network="lan" data-keywords="api_nap-lab nap-lab nap lab แล็บ ชันสูตร">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-teal"><span class="material-symbols-outlined">biotech</span></div>
                        <h4>API_Nap-LAB</h4>
                    </a>
                    <a href="http://192.168.1.20/product" target="_blank" class="service-card card-blue" data-network="lan" data-keywords="productivity เพิ่มผลผลิต งาน ภาระงาน">
                        <span class="badge-network badge-lan">LAN</span>
                        <div class="icon-box ic-blue"><span class="material-symbols-outlined">rocket_launch</span></div>
                        <h4>Productivity</h4>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2026 กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลเชียงแสน (Chiang Saen Hospital)</p>
        <div style="margin-top: 8px;">
            <a href="{{ route('admin.installations') }}" style="color: #94a3b8; font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: color 0.2s;" onmouseover="this.style.color='#64B5F6'" onmouseout="this.style.color='#94a3b8'">
                <span class="material-symbols-outlined" style="font-size: 16px;">query_stats</span>
                <span>ระบบตรวจสอบการติดตั้งไอคอน (สำหรับผู้ดูแลระบบ)</span>
            </a>
        </div>
    </footer>

    <!-- Desktop Install Modal (Native Browser App Installation Guide) -->
    <div id="installDesktopModal" class="install-modal-overlay" onclick="if(event.target===this) closeInstallModal()">
        <div class="install-modal-card">
            <div class="install-modal-header">
                <div class="install-modal-title">
                    <span class="material-symbols-outlined" style="font-size: 28px; color: #93c5fd;">install_desktop</span>
                    <div>
                        <h3>ติดตั้ง CSHOS DATACENTER บนคอมพิวเตอร์</h3>
                        <small>โรงพยาบาลเชียงแสน (Chiang Saen Digital Health)</small>
                    </div>
                </div>
                <button type="button" class="install-modal-close" onclick="closeInstallModal()" title="ปิดหน้าต่าง">
                    <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
                </button>
            </div>

            <div class="install-modal-body">
                <div class="install-step-card highlight" style="margin-bottom: 16px;">
                    <div class="install-step-header">
                        <div class="install-step-title">
                            <span class="material-symbols-outlined" style="color: #2563eb; font-size: 22px;">verified</span>
                            <span>ติดตั้งเป็นแอปพลิเคชันลงเครื่องทันที (ไม่ต้องดาวน์โหลดไฟล์)</span>
                        </div>
                    </div>
                    <p class="install-step-desc">
                        คุณสามารถติดตั้ง CSHOS DATACENTER เป็นแอปพลิเคชันลงบนหน้าจอคอมพิวเตอร์ (Desktop) ได้โดยตรงผ่านเว็บเบราว์เซอร์ โดยมีขั้นตอนง่ายๆ ดังนี้:
                    </p>
                </div>

                <!-- Step Guide for Chrome & Edge -->
                <div class="install-step-card" style="margin-bottom: 16px;">
                    <div class="install-step-header">
                        <div class="install-step-title">
                            <span class="material-symbols-outlined" style="color: #0284c7; font-size: 20px;">laptop_chromebook</span>
                            <span>วิธีติดตั้งผ่าน Google Chrome:</span>
                        </div>
                    </div>
                    <ul class="install-guide-steps">
                        <li>
                            <span class="step-num">1</span>
                            <div>มองที่ <strong>แถบที่อยู่เว็บ (Address Bar ด้านบนขวา)</strong> จะมีไอคอน <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; color: #2563eb;">install_desktop</span> <strong>"ติดตั้ง CSHOS DATACENTER"</strong> &rarr; คลิกแล้วกดปุ่ม <strong>"ติดตั้ง" (Install)</strong></div>
                        </li>
                        <li>
                            <span class="step-num">2</span>
                            <div>หรือกดปุ่มเมนูจุดสามจุด <code>⋮</code> มุมขวาบน &rarr; เลือก <strong>"บันทึกและแชร์" (Save and share)</strong> &rarr; เลือก <strong>"สร้างทางลัด..." (Create shortcut...)</strong> &rarr; ติ๊กถูกที่ช่อง <strong>"เปิดเป็นหน้าต่าง" (Open as window)</strong> &rarr; กด <strong>"สร้าง"</strong></div>
                        </li>
                    </ul>
                </div>

                <div class="install-step-card" style="margin-bottom: 18px;">
                    <div class="install-step-header">
                        <div class="install-step-title">
                            <span class="material-symbols-outlined" style="color: #0284c7; font-size: 20px;">tab</span>
                            <span>วิธีติดตั้งผ่าน Microsoft Edge:</span>
                        </div>
                    </div>
                    <ul class="install-guide-steps">
                        <li>
                            <span class="step-num">1</span>
                            <div>มองที่ <strong>แถบที่อยู่เว็บ (Address Bar ด้านบนขวา)</strong> จะมีไอคอนแอปพลิเคชัน &rarr; คลิกแล้วกดปุ่ม <strong>"ติดตั้ง" (Install)</strong></div>
                        </li>
                        <li>
                            <span class="step-num">2</span>
                            <div>หรือกดปุ่มเมนูจุดสามจุด <code>…</code> มุมขวาบน &rarr; เลือก <strong>"แอป" (Apps)</strong> &rarr; เลือก <strong>"ติดตั้งไซต์นี้เป็นแอป" (Install this site as an app)</strong> &rarr; กด <strong>"ติดตั้ง"</strong></div>
                        </li>
                    </ul>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px 16px; font-size: 0.85rem; color: #166534; display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #16a34a; flex-shrink: 0;">check_circle</span>
                    <span>เมื่อกดติดตั้งแล้ว เบราว์เซอร์จะสร้างไอคอนแอป CSHOS DATACENTER ไว้ที่หน้าจอ Desktop และ Start Menu ให้อัตโนมัติทันที</span>
                </div>

                <div class="install-modal-actions" style="border: none; padding: 0; margin: 0;">
                    <button type="button" class="btn-modal-primary" onclick="closeInstallModal()" style="width: 100%; justify-content: center; padding: 11px 20px; font-size: 0.95rem;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">thumb_up</span>
                        <span>เข้าใจแล้ว</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- App Scripts -->
    <script>
        // --- PWA & App Installation Tracking ---
        function getDeviceId() {
            let id = localStorage.getItem('csh_device_uuid');
            if (!id) {
                if (typeof crypto !== 'undefined' && crypto.randomUUID) {
                    id = crypto.randomUUID();
                } else {
                    id = 'csh-' + Math.random().toString(36).substring(2, 11) + '-' + Date.now().toString(36);
                }
                localStorage.setItem('csh_device_uuid', id);
            }
            return id;
        }

        function getClientInfo() {
            const ua = navigator.userAgent;
            let os = 'Windows';
            if (ua.indexOf('Win') !== -1) {
                if (ua.indexOf('Windows NT 10.0') !== -1) os = 'Windows 10/11';
                else if (ua.indexOf('Windows NT 6.3') !== -1) os = 'Windows 8.1';
                else if (ua.indexOf('Windows NT 6.1') !== -1) os = 'Windows 7';
                else os = 'Windows';
            } else if (ua.indexOf('Mac') !== -1) {
                os = 'macOS';
            } else if (ua.indexOf('Android') !== -1) {
                os = 'Android';
            } else if (ua.indexOf('iPhone') !== -1 || ua.indexOf('iPad') !== -1) {
                os = 'iOS';
            } else if (ua.indexOf('Linux') !== -1) {
                os = 'Linux';
            }

            let browser = 'Other';
            if (ua.indexOf('Edg/') !== -1) browser = 'Microsoft Edge';
            else if (ua.indexOf('Chrome/') !== -1) browser = 'Google Chrome';
            else if (ua.indexOf('Firefox/') !== -1) browser = 'Mozilla Firefox';
            else if (ua.indexOf('Safari/') !== -1 && ua.indexOf('Chrome/') === -1) browser = 'Apple Safari';
            else if (ua.indexOf('OPR/') !== -1 || ua.indexOf('Opera/') !== -1) browser = 'Opera';

            return { os, browser };
        }

        function sendInstallTracking(installType) {
            const deviceId = getDeviceId();
            const { os, browser } = getClientInfo();

            const payload = {
                device_id: deviceId,
                install_type: installType || 'installed',
                os: os,
                browser: browser
            };

            const url = '{{ route("track.install") }}';
            try {
                if (navigator.sendBeacon) {
                    const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                    navigator.sendBeacon(url, blob);
                } else {
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload),
                        keepalive: true
                    }).catch(() => {});
                }
            } catch (e) {
                console.warn('Tracking notice:', e);
            }
        }

        function markAppInstalled() {
            try {
                localStorage.setItem('csh_app_installed', '1');
            } catch (e) {}
            const btn = document.getElementById('btnInstallApp');
            if (btn) {
                btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span> ติดตั้งไอคอนแล้ว';
                btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            }
        }

        // Track when launched as standalone installed application
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) {
            document.documentElement.classList.add('app-installed');
            if (!sessionStorage.getItem('csh_standalone_tracked')) {
                sendInstallTracking('installed');
                sessionStorage.setItem('csh_standalone_tracked', '1');
            }
        }

        // Check native OS-level PWA installation status (Chrome / Edge on Windows)
        if ('getInstalledRelatedApps' in navigator) {
            navigator.getInstalledRelatedApps().then(apps => {
                if (apps && apps.length > 0) {
                    markAppInstalled();
                }
            }).catch(() => {});
        }

        window.addEventListener('appinstalled', () => {
            console.log('CSHOS DATACENTER app was installed');
            window.deferredPrompt = null;
            markAppInstalled();
            sendInstallTracking('installed');
        });

        async function installPWA() {
            const btn = document.getElementById('btnInstallApp');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.innerHTML = '<span class="material-symbols-outlined spin-icon" style="font-size: 20px;">progress_activity</span> ติดตั้งไอคอน...';
                btn.style.pointerEvents = 'none';
            }

            try {
                // 1. เรียก API ติดตั้งไอคอนลงบนหน้าจอ Desktop ให้อัตโนมัติทันที
                const deviceId = getDeviceId();
                const clientInfo = getClientInfo();
                const res = await fetch('{{ route("api.install.desktop") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_id: deviceId,
                        os: clientInfo.os,
                        browser: clientInfo.browser
                    })
                });
                const result = await res.json();

                if (result.shortcut_created) {
                    markAppInstalled();
                    showInstallToast('🎉 ติดตั้งไอคอน CSHOS DATACENTER บนหน้าจอ Desktop เรียบร้อยแล้ว!');
                    if (btn) btn.style.pointerEvents = 'auto';
                    return;
                }
            } catch (err) {
                console.warn('Direct shortcut install notice:', err);
            }

            if (btn) {
                btn.style.pointerEvents = 'auto';
                if (!localStorage.getItem('csh_app_installed')) {
                    btn.innerHTML = originalHtml;
                }
            }

            // 2. ถ้าเบราว์เซอร์มี Native PWA prompt พร้อมแล้ว ให้เรียกแสดงทันที
            if (window.deferredPrompt) {
                try {
                    window.deferredPrompt.prompt();
                    const choiceResult = await window.deferredPrompt.userChoice;
                    if (choiceResult.outcome === 'accepted') {
                        markAppInstalled();
                        sendInstallTracking('installed');
                        showInstallToast('🎉 ติดตั้ง CSHOS DATACENTER ลงบนคอมพิวเตอร์เรียบร้อยแล้ว!');
                    }
                    window.deferredPrompt = null;
                    return;
                } catch (e) {
                    console.warn('Native prompt call error:', e);
                }
            }

            // 3. หากเบราว์เซอร์ยังไม่ได้ส่ง deferredPrompt (เครื่องลูกข่าย LAN) ให้เปิดหน้าต่างแนะนำ
            openInstallModal();
        }

        function showInstallToast(msg) {
            let toast = document.getElementById('installToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'installToast';
                toast.className = 'install-toast';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<span class="material-symbols-outlined" style="font-size: 26px; color: #10B981;">check_circle</span> <span>${msg}</span>`;
            toast.style.display = 'flex';
            setTimeout(() => {
                toast.classList.add('toast-fadeout');
                setTimeout(() => {
                    toast.style.display = 'none';
                    toast.classList.remove('toast-fadeout');
                }, 400);
            }, 4500);
        }

        function openInstallModal() {
            const modal = document.getElementById('installDesktopModal');
            if (modal) modal.classList.add('active');
        }

        function closeInstallModal() {
            const modal = document.getElementById('installDesktopModal');
            if (modal) modal.classList.remove('active');
        }
    </script>

</body>
</html>