<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ทะเบียนส่งตรวจปริมาณแอลกอฮอล์ในเลือด - โรงพยาบาลเชียงแสน</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Flatpickr Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <style>
        :root {
            --primary: #1e88e5;
            --primary-dark: #1565c0;
            --primary-light: #e3f2fd;
            --navy: #1e3a8a;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --purple: #8b5cf6;
            --purple-light: #ede9fe;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Noto Sans Thai', 'Sarabun', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* --- Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 3%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-logo {
            height: 42px;
            width: auto;
            border-radius: 8px;
        }

        .brand-title h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.25;
            letter-spacing: -0.2px;
        }

        .brand-title small {
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 600;
            letter-spacing: 0.5px;
            display: block;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 50px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.25s ease;
            line-height: 1.4;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e88e5, #1976d2);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 136, 229, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }

        .btn-outline {
            background: white;
            border-color: var(--border-color);
            color: #475569;
        }
        .btn-outline:hover {
            background: #f1f5f9;
            color: var(--navy);
            transform: translateY(-1px);
        }

        /* --- Main Container --- */
        .main-content {
            padding: 1.8rem 3% 3rem;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }

        /* --- Page Header Banner --- */
        .page-banner {
            background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
            border: 1px solid #dbeafe;
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 4px 20px rgba(30, 136, 229, 0.05);
        }

        .banner-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .banner-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 14px rgba(2, 132, 199, 0.3);
        }

        .banner-icon .material-symbols-outlined {
            font-size: 30px;
        }

        .banner-text h2 {
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
            letter-spacing: -0.2px;
        }

        .banner-text p {
            font-size: 0.92rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* --- KPI Summary Cards --- */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .kpi-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }

        .kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .kpi-icon.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .kpi-icon.green { background: linear-gradient(135deg, #10b981, #047857); }
        .kpi-icon.teal { background: linear-gradient(135deg, #0d9488, #0f766e); }
        .kpi-icon.amber { background: linear-gradient(135deg, #f59e0b, #b45309); }
        .kpi-icon.gray { background: linear-gradient(135deg, #94a3b8, #475569); }

        .kpi-data small {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            display: block;
            margin-bottom: 2px;
        }

        .kpi-data strong {
            font-size: 1.55rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
        }

        /* --- Filter Card --- */
        .filter-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-group.search-group {
            flex: 2;
            min-width: 250px;
            position: relative;
        }

        .filter-group label {
            display: block;
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-control {
            width: 100%;
            padding: 9px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-family: inherit;
            font-size: 0.9rem;
            color: #1e293b;
            background: #ffffff;
            outline: none;
            transition: all 0.2s;
        }
        .input-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* --- Table Styling (Google Sheet Feel) --- */
        .table-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        }

        .table-header-bar {
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .table-responsive {
            overflow-x: auto;
            max-height: 70vh;
        }

        table.sheet-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.88rem;
            text-align: left;
            min-width: 1350px;
        }

        table.sheet-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            padding: 12px 14px;
            border-bottom: 2px solid #cbd5e1;
            border-right: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            letter-spacing: 0.01em;
        }

        table.sheet-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
            font-weight: 400;
        }

        table.sheet-table tr:hover td {
            background-color: #f8fbff;
        }

        table.sheet-table tr.row-cancelled td {
            background-color: #fff9f9;
            color: #94a3b8;
        }

        /* --- Badges --- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.3;
            white-space: nowrap;
        }

        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        .doc-tag {
            font-size: 0.72rem;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
        }
        .doc-has { background: #dcfce7; color: #166534; }
        .doc-no { background: #f1f5f9; color: #94a3b8; }
        .doc-empty { background: #f8fafc; color: #94a3b8; border: 1px dashed #cbd5e1; }

        .action-btns {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #475569;
            transition: all 0.2s;
        }
        .btn-icon:hover {
            border-color: #3b82f6;
            color: #1d4ed8;
            background: #eff6ff;
            transform: scale(1.05);
        }
        .btn-icon.delete:hover {
            border-color: #ef4444;
            color: #b91c1c;
            background: #fef2f2;
        }

        /* --- Modals --- */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-dialog {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 1060px;
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            display: flex;
            flex-direction: column;
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.97) translateY(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 18px 28px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .modal-header h3 {
            font-size: 1.22rem;
            font-weight: 700;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-close {
            background: #f1f5f9;
            border: none;
            cursor: pointer;
            color: #64748b;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            font-size: 20px;
        }
        .modal-close:hover {
            background: #fee2e2;
            color: #ef4444;
        }

        .modal-body {
            padding: 22px 28px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .modal-section-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .modal-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .modal-section-title-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-section-title .material-symbols-outlined {
            color: var(--primary);
            font-size: 20px;
        }

        .grid-layout {
            display: grid;
            gap: 14px;
        }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .span-2 { grid-column: span 2; }
        .span-3 { grid-column: span 3; }
        .span-full { grid-column: 1 / -1; }

        @media (max-width: 900px) {
            .grid-cols-4, .grid-cols-3 { grid-template-columns: repeat(2, 1fr); }
            .span-3 { grid-column: span 2; }
        }
        @media (max-width: 600px) {
            .grid-cols-4, .grid-cols-3, .grid-cols-2 { grid-template-columns: 1fr; }
            .span-2, .span-3, .span-full { grid-column: 1; }
        }

        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.83rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 5px;
        }

        .form-label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-label-hint {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 400;
        }

        .input-with-icon {
            position: relative;
        }
        .input-with-icon .input-control {
            padding-right: 36px;
            background-color: #ffffff;
            cursor: pointer;
        }
        .input-with-icon .material-symbols-outlined {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            font-size: 19px;
        }

        .hn-input-group {
            display: flex;
            gap: 6px;
        }
        .hn-input-group .input-control {
            font-family: monospace, inherit;
            font-size: 0.96rem;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #0f172a;
        }

        .btn-lookup-cshos {
            padding: 0 13px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .btn-lookup-cshos:hover {
            background: #bfdbfe;
            border-color: #93c5fd;
            transform: translateY(-1px);
        }

        .hn-lookup-feedback {
            font-size: 0.78rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
            min-height: 20px;
        }
        .hn-lookup-feedback.success { color: #15803d; font-weight: 600; }
        .hn-lookup-feedback.loading { color: #2563eb; }
        .hn-lookup-feedback.error { color: #dc2626; }
        .hn-lookup-feedback.info { color: #64748b; }

        .highlight-glow {
            animation: pulseGlow 1.8s ease-in-out;
        }
        @keyframes pulseGlow {
            0% { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25); background: #eff6ff; }
            50% { border-color: #10b981; box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.35); background: #ecfdf5; }
            100% { border-color: #cbd5e1; box-shadow: none; background: #ffffff; }
        }

        .flatpickr-calendar {
            z-index: 99999 !important;
            font-family: 'Noto Sans Thai', 'Sarabun', sans-serif !important;
            border-radius: 14px !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.18) !important;
            border: 1px solid #cbd5e1 !important;
        }

        .modal-footer {
            padding: 16px 28px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            background: #ffffff;
            border-radius: 0 0 20px 20px;
            position: sticky;
            bottom: 0;
            z-index: 20;
        }

        /* Document check buttons */
        .doc-check-group {
            display: flex;
            gap: 8px;
        }
        .doc-check-label {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        .doc-check-label input { display: none; }
        .doc-check-label.active {
            background: #dcfce7;
            border-color: #86efac;
            color: #166534;
            font-weight: 600;
        }

        /* Alert notification */
        .alert-banner {
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        footer {
            background: white;
            padding: 1.5rem 3%;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="brand-wrap">
            <img src="{{ asset('csh-logo.jpg') }}" alt="CSH Logo" class="brand-logo" onerror="this.style.display='none'">
            <div class="brand-title">
                <h1>โรงพยาบาลเชียงแสน</h1>
                <small>CHIANGSAEN DIGITAL HEALTH</small>
            </div>
        </a>
        <div class="nav-actions">
            <a href="{{ route('home') }}" class="btn btn-outline">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                หน้าหลัก CSH
            </a>
            {{-- <a href="{{ route('blood-alc.export', request()->query()) }}" class="btn btn-outline">
                <span class="material-symbols-outlined" style="font-size: 18px; color: #16a34a;">table_view</span>
                ส่งออก Excel (CSV)
            </a> --}}
            <button class="btn btn-primary" onclick="openAddModal()">
                <span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
                บันทึกรายการส่งตรวจ
            </button>
        </div>
    </header>

    <main class="main-content">

        <!-- Flash Message -->
        @if(session('success'))
            <div class="alert-banner">
                <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Banner Header -->
        <div class="page-banner">
            <div class="banner-info">
                <div class="banner-icon">
                    <span class="material-symbols-outlined">bloodtype</span>
                </div>
                <div class="banner-text">
                    <h2>ทะเบียนส่งตรวจปริมาณแอลกอฮอล์ในเลือด</h2>
                    <p>ระบบฐานข้อมูลบันทึก ติดตามผล และการเบิกจ่ายการตรวจแอลกอฮอล์ในเลือด โรงพยาบาลเชียงแสน</p>
                </div>
            </div>
            <div>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon blue">
                    <span class="material-symbols-outlined">analytics</span>
                </div>
                <div class="kpi-data">
                    <small>จำนวนตรวจทั้งหมด</small>
                    <strong>{{ number_format($totalCount) }}</strong>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon green">
                    <span class="material-symbols-outlined">paid</span>
                </div>
                <div class="kpi-data">
                    <small>เบิกจ่ายแล้ว</small>
                    <strong style="color: #15803d;">{{ number_format($claimedCount) }}</strong>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon teal">
                    <span class="material-symbols-outlined">payments</span>
                </div>
                <div class="kpi-data">
                    <small>ได้รับเงินแล้ว</small>
                    <strong style="color: #0d9488;">{{ number_format($paymentReceivedCount) }}</strong>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon amber">
                    <span class="material-symbols-outlined">pending_actions</span>
                </div>
                <div class="kpi-data">
                    <small>รอส่งเบิก / รอผลตรวจ</small>
                    <strong style="color: #b45309;">{{ number_format($pendingCount) }}</strong>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon gray">
                    <span class="material-symbols-outlined">cancel</span>
                </div>
                <div class="kpi-data">
                    <small>ยกเลิกส่งตรวจ</small>
                    <strong style="color: #64748b;">{{ number_format($cancelledCount) }}</strong>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="filter-card">
            <form action="{{ route('blood-alc.index') }}" method="GET" class="filter-form">
                <div class="filter-group search-group">
                    <label>ค้นหาข้อมูล</label>
                    <input type="text" name="search" class="input-control" placeholder="ค้นหา ชื่อผู้ป่วย, HN, สภ., ตำรวจ, หมายเหตุ..." value="{{ request('search') }}">
                </div>

                <div class="filter-group">
                    <label>เดือน</label>
                    <select name="month_year" class="input-control">
                        <option value="">ทั้งหมด (ทุกเดือน)</option>
                        @foreach($monthList as $m)
                            <option value="{{ $m }}" {{ request('month_year') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>สถานีตำรวจ (สภ.)</label>
                    <select name="police_station" class="input-control">
                        <option value="">ทั้งหมด (ทุก สภ.)</option>
                        @foreach($stationList as $st)
                            <option value="{{ $st }}" {{ request('police_station') == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>สถานะการเบิกจ่าย</label>
                    <select name="claim_status" class="input-control">
                        <option value="">ทั้งหมด</option>
                        <option value="เบิกจ่ายแล้ว" {{ request('claim_status') == 'เบิกจ่ายแล้ว' ? 'selected' : '' }}>เบิกจ่ายแล้ว</option>
                        <option value="paid" {{ request('claim_status') == 'paid' ? 'selected' : '' }}>ได้รับเงินแล้ว</option>
                        <option value="pending" {{ request('claim_status') == 'pending' ? 'selected' : '' }}>รอส่งเบิก / รอผล</option>
                        <option value="ยกเลิก" {{ request('claim_status') == 'ยกเลิก' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>

                <div style="display: flex; gap: 8px; align-self: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 9px 18px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">search</span>
                        ค้นหา
                    </button>
                    @if(request()->hasAny(['search', 'month_year', 'police_station', 'claim_status']))
                        <a href="{{ route('blood-alc.index') }}" class="btn btn-outline" style="padding: 9px 14px;">
                            ล้างค่า
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-header-bar">
                <div>
                    แสดงข้อมูล <strong>{{ $records->firstItem() ?? 0 }} - {{ $records->lastItem() ?? 0 }}</strong> จากทั้งหมด <strong>{{ $records->total() }}</strong> รายการ
                </div>
                <div>
                    คลิกไอคอน <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">edit</span> เพื่อแก้ไขข้อมูล
                </div>
            </div>

            <div class="table-responsive">
                <table class="sheet-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">ลำดับ</th>
                            <th style="width: 120px; text-align: center;">วันที่ตรวจ</th>
                            <th style="width: 95px;">HN / ตัวอย่าง</th>
                            <th style="width: 140px;">สถานีตำรวจที่ส่งตรวจ</th>
                            <th style="min-width: 240px;">ตำรวจผู้ร้องขอ / โทร</th>
                            <th style="width: 150px; text-align: center;">เอกสารประกอบ</th>
                            <th style="width: 110px;">วันที่ส่งตรวจ</th>
                            <th style="width: 110px;">วันที่ออกผล HosXP</th>
                            <th style="width: 110px; text-align: center;">สถานะเบิกจ่าย</th>
                            <th style="width: 130px;">วันที่เบิกจ่าย</th>
                            <th style="width: 100px;">วันที่ได้รับเงิน</th>
                            <th style="min-width: 150px;">หมายเหตุ</th>
                            <th style="width: 90px; text-align: center; position: sticky; right: 0; background: #f8fafc; z-index: 15;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $r)
                            @php
                                $isCancelled = str_contains($r->claim_status ?? '', 'ยกเลิก') || str_contains($r->lab_send_date ?? '', 'ยกเลิก');
                            @endphp
                            <tr class="{{ $isCancelled ? 'row-cancelled' : '' }}">
                                <td style="text-align: center; font-weight: 600; color: #475569;">
                                    {{ $r->order_no ?? $r->id }}
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #1e293b; font-family: monospace, inherit; font-size: 0.9rem;">
                                    {{ $r->formatted_test_date }}
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size: 0.88rem; font-weight: 600; color: #0284c7;">
                                        {{ $r->hn ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $r->police_station ?? 'ไม่ระบุ' }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($r->police_officer)
                                        <div style="font-weight: 500; white-space: nowrap;">{{ $r->police_officer }}</div>
                                    @endif
                                    @if($r->contact_phone)
                                        <small style="color: #64748b; white-space: nowrap; display: inline-flex; align-items: center; gap: 3px;">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">call</span>
                                            {{ $r->contact_phone }}
                                        </small>
                                    @endif
                                    @if(!$r->police_officer && !$r->contact_phone)
                                        <span style="color: #94a3b8;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 4px; justify-content: center;">
                                        <span class="doc-tag {{ $r->doc_k8 === 'มี' ? 'doc-has' : ($r->doc_k8 === 'ไม่มี' ? 'doc-no' : 'doc-empty') }}" title="ใบ ค.8">
                                            ค.8: {{ $r->doc_k8 ?: '-' }}
                                        </span>
                                        <span class="doc-tag {{ $r->doc_pher === 'มี' ? 'doc-has' : ($r->doc_pher === 'ไม่มี' ? 'doc-no' : 'doc-empty') }}" title="ใบ Pher">
                                            Pher: {{ $r->doc_pher ?: '-' }}
                                        </span>
                                        <span class="doc-tag {{ $r->doc_lab_send === 'มี' ? 'doc-has' : ($r->doc_lab_send === 'ไม่มี' ? 'doc-no' : 'doc-empty') }}" title="ใบนำส่งตรวจแล็บ">
                                            แล็บ: {{ $r->doc_lab_send ?: '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $r->lab_send_date ?? '-' }}</td>
                                <td>{{ $r->hosxp_result_date ?? '-' }}</td>
                                <td style="text-align: center;">
                                    @if($r->claim_status === 'เบิกจ่ายแล้ว')
                                        <span class="badge badge-success">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>
                                            เบิกจ่ายแล้ว
                                        </span>
                                    @elseif($r->claim_status === 'ยกเลิก' || $isCancelled)
                                        <span class="badge badge-gray">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">cancel</span>
                                            ยกเลิก
                                        </span>
                                    @elseif($r->claim_status)
                                        <span class="badge badge-warning">
                                            {{ $r->claim_status }}
                                        </span>
                                    @else
                                        <span class="badge badge-warning">รอส่งเบิก</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $r->claim_date ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($r->payment_received_date)
                                        <strong style="color: #15803d;">{{ $r->payment_received_date }}</strong>
                                    @else
                                        <span style="color: #94a3b8;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small style="color: #475569;">{{ $r->remarks ?? '-' }}</small>
                                </td>
                                <td style="text-align: center; position: sticky; right: 0; background: white; z-index: 10;">
                                    <div class="action-btns" style="justify-content: center;">
                                        <button class="btn-icon" title="แก้ไขข้อมูล" onclick="openEditModal({{ $r->id }})">
                                            <span class="material-symbols-outlined" style="font-size: 17px;">edit</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" style="text-align: center; padding: 3.5rem 1rem; color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 48px; color: #cbd5e1; display: block; margin-bottom: 8px;">search_off</span>
                                    <p style="font-size: 1.1rem; color: #475569; font-weight: 500;">ไม่พบข้อมูลทะเบียนส่งตรวจแอลกอฮอล์ในเลือด</p>
                                    <small>ลองเปลี่ยนคำค้นหาหรือตัวกรอง หรือกดปุ่ม "บันทึกรายการส่งตรวจ" ด้านบนเพื่อเพิ่มข้อมูลใหม่</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($records->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        หน้า {{ $records->currentPage() }} จากทั้งหมด {{ $records->lastPage() }}
                    </div>
                    <div>
                        {{ $records->links() }}
                    </div>
                </div>
            @endif
        </div>

    </main>

    <!-- Modal: Add New Record -->
    <div id="addModal" class="modal-backdrop">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>
                    <span class="material-symbols-outlined" style="font-size: 26px; color: var(--primary);">biotech</span>
                    บันทึกรายการส่งตรวจแอลกอฮอล์ใหม่
                </h3>
                <button type="button" class="modal-close" onclick="closeAddModal()" title="ปิด">&times;</button>
            </div>
            <form action="{{ route('blood-alc.store') }}" method="POST" id="addForm">
                @csrf
                <div class="modal-body">

                    <!-- หมวดที่ 1: ข้อมูลผู้ป่วยและวันที่ตรวจ -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">person</span>
                                ข้อมูลผู้เข้ารับการตรวจและวันที่
                            </div>
                            <span class="badge badge-info" style="font-size: 0.72rem;">
                                <span class="material-symbols-outlined" style="font-size: 13px;">sync</span>
                                เชื่อมต่อ HOSxP อัตโนมัติ (CSHOS)
                            </span>
                        </div>
                        <div class="grid-layout grid-cols-4">
                            <!-- ลำดับ -->
                            <div>
                                <label class="form-label">
                                    <span>ลำดับ</span>
                                    <span class="form-label-hint">อัตโนมัติ</span>
                                </label>
                                <input type="number" name="order_no" class="input-control" value="{{ $nextOrderNo }}" required>
                            </div>

                            <!-- HN + ค้นหา -->
                            <div class="span-2">
                                <label class="form-label">
                                    <span>HN / หมายเลขตัวอย่าง</span>
                                    <span class="form-label-hint">ปรับ 9 หลัก & ดึงชื่ออัตโนมัติ</span>
                                </label>
                                <div class="hn-input-group">
                                    <input type="text" id="add_hn" name="hn" class="input-control" placeholder="เช่น 185543 หรือ 000185543" autocomplete="off">
                                    <button type="button" class="btn-lookup-cshos" onclick="lookupHnPatient('add_hn', 'add_patient_name', 'add_hn_feedback', true)" title="ค้นหาชื่อจากฐานข้อมูล HOSxP">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">search</span>
                                        ค้นหา HOSxP
                                    </button>
                                </div>
                                <div id="add_hn_feedback" class="hn-lookup-feedback"></div>
                            </div>

                            <!-- วันที่ตรวจ (ปฏิทิน Datepicker) -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ตรวจ (ปฏิทิน)</span>
                                    <span class="form-label-hint">เลือกวันที่</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" id="add_test_date" class="input-control thai-datepicker" placeholder="ว/ด/ป เช่น 16/09/2569" autocomplete="off">
                                    <span class="material-symbols-outlined">calendar_month</span>
                                </div>
                            </div>

                            <!-- ชื่อผู้เข้ารับการตรวจ -->
                            <div class="span-2">
                                <label class="form-label">
                                    <span>ชื่อผู้เข้ารับการตรวจ <span class="required">*</span></span>
                                    <span class="form-label-hint">ดึงจาก HOSxP หรือพิมพ์เอง</span>
                                </label>
                                <input type="text" id="add_patient_name" name="patient_name" class="input-control" placeholder="คำนำหน้า ชื่อ-นามสกุล" required>
                            </div>

                            <!-- วันที่ตรวจ -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ตรวจ</span>
                                    <span class="form-label-hint">1-31</span>
                                </label>
                                <input type="text" id="add_test_day" name="test_day" class="input-control" placeholder="เช่น 16" value="{{ date('j') }}">
                            </div>

                            <!-- เดือนที่ตรวจ -->
                            <div>
                                <label class="form-label">
                                    <span>เดือนที่ตรวจ</span>
                                    <span class="form-label-hint">เช่น ก.ย.69</span>
                                </label>
                                <input type="text" id="add_month_year" name="month_year" class="input-control" placeholder="เช่น ก.ย.69" value="ก.ย.69">
                            </div>
                        </div>
                    </div>

                    <!-- หมวดที่ 2: สถานีตำรวจและเจ้าหน้าที่ผู้ร้องขอ -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">local_police</span>
                                ข้อมูลสถานีตำรวจและเจ้าหน้าที่ผู้ร้องขอ
                            </div>
                        </div>
                        <div class="grid-layout grid-cols-3">
                            <div>
                                <label class="form-label">
                                    <span>สถานีตำรวจที่ส่งตรวจ (สภ.)</span>
                                </label>
                                <select name="police_station" id="add_police_station" class="input-control">
                                    <option value="" selected>-- เลือกสถานีตำรวจ --</option>
                                    <option value="สภ.เชียงแสน">สภ.เชียงแสน</option>
                                    <option value="สภ.บ้านแซว">สภ.บ้านแซว</option>
                                    <option value="ไม่ทราบ สภ.">ไม่ทราบ สภ.</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">ชื่อ-สกุล ตำรวจที่ร้องขอ</label>
                                <input type="text" name="police_officer" class="input-control" placeholder="ยศ ชื่อ นามสกุล">
                            </div>
                            <div>
                                <label class="form-label">เบอร์โทรติดต่อ</label>
                                <input type="text" name="contact_phone" class="input-control" placeholder="เช่น 088-2519691">
                            </div>
                        </div>
                    </div>

                    <!-- หมวดที่ 3: เอกสารประกอบและการส่งตรวจทางห้องปฏิบัติการ -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">folder_shared</span>
                                เอกสารประกอบและการส่งตรวจห้องปฏิบัติการ
                            </div>
                        </div>
                        <div class="grid-layout grid-cols-3">
                            <!-- เอกสาร 3 ชนิด -->
                            <div class="span-3" style="background: #f8fafc; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                                <label class="form-label" style="margin-bottom: 8px;">เอกสารประกอบการส่งตรวจ</label>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                                    <div>
                                        <small style="display: block; margin-bottom: 4px; font-weight: 600; color: #475569;">ใบ ค.8</small>
                                        <select name="doc_k8" id="add_doc_k8" class="input-control">
                                            <option value="" selected>-- ว่าง --</option>
                                            <option value="มี">มี</option>
                                            <option value="ไม่มี">ไม่มี</option>
                                        </select>
                                    </div>
                                    <div>
                                        <small style="display: block; margin-bottom: 4px; font-weight: 600; color: #475569;">ใบ Pher</small>
                                        <select name="doc_pher" id="add_doc_pher" class="input-control">
                                            <option value="" selected>-- ว่าง --</option>
                                            <option value="มี">มี</option>
                                            <option value="ไม่มี">ไม่มี</option>
                                        </select>
                                    </div>
                                    <div>
                                        <small style="display: block; margin-bottom: 4px; font-weight: 600; color: #475569;">ใบนำส่งตรวจแล็บ</small>
                                        <select name="doc_lab_send" id="add_doc_lab_send" class="input-control">
                                            <option value="" selected>-- ว่าง --</option>
                                            <option value="มี">มี</option>
                                            <option value="ไม่มี">ไม่มี</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- วันที่ส่งตรวจแล็บ (Datepicker) -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ส่งตรวจทางแล็บ</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" name="lab_send_date" id="add_lab_send_date" class="input-control thai-datepicker" placeholder="เช่น 16/01/2569" autocomplete="off">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                </div>
                            </div>

                            <!-- วันที่ออกผลใน HosXP (Datepicker) -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ออกผลใน HosXP</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" name="hosxp_result_date" id="add_hosxp_result_date" class="input-control thai-datepicker" placeholder="เช่น 27/01/2569" autocomplete="off">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- หมวดที่ 4: สถานะและการเบิกจ่าย -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">payments</span>
                                สถานะการเบิกจ่ายและการเงิน
                            </div>
                        </div>
                        <div class="grid-layout grid-cols-3">
                            <div>
                                <label class="form-label">สถานะการเบิกจ่าย</label>
                                <select name="claim_status" class="input-control" style="font-weight: 500;">
                                    <option value="">-- ยังไม่ระบุ (รอส่งเบิก) --</option>
                                    <option value="เบิกจ่ายแล้ว">เบิกจ่ายแล้ว</option>
                                    <option value="รอเบิกจ่าย">รอเบิกจ่าย</option>
                                    <option value="ยกเลิก">ยกเลิก</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">
                                    <span>วันที่เบิกจ่าย</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" name="claim_date" id="add_claim_date" class="input-control thai-datepicker" placeholder="เช่น 16/01/2569" autocomplete="off">
                                    <span class="material-symbols-outlined">event_available</span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">
                                    <span>วันที่ได้รับเงิน</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" name="payment_received_date" id="add_payment_received_date" class="input-control thai-datepicker" placeholder="เช่น 17/03/2569" autocomplete="off">
                                    <span class="material-symbols-outlined">paid</span>
                                </div>
                            </div>
                            <div class="span-full">
                                <label class="form-label">หมายเหตุเพิ่มเติม</label>
                                <input type="text" name="remarks" class="input-control" placeholder="ระบุหมายเหตุ เช่น เลขที่ใบเสร็จ หรือสาเหตุการยกเลิก">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeAddModal()">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 0.95rem;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
                        บันทึกข้อมูลการส่งตรวจ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Record -->
    <div id="editModal" class="modal-backdrop">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>
                    <span class="material-symbols-outlined" style="font-size: 26px; color: var(--primary);">edit_note</span>
                    แก้ไขรายการส่งตรวจแอลกอฮอล์
                </h3>
                <button type="button" class="modal-close" onclick="closeEditModal()" title="ปิด">&times;</button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    <!-- หมวดที่ 1: ข้อมูลผู้ป่วยและวันที่ตรวจ -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">person</span>
                                ข้อมูลผู้เข้ารับการตรวจและวันที่
                            </div>
                            <span class="badge badge-info" style="font-size: 0.72rem;">
                                <span class="material-symbols-outlined" style="font-size: 13px;">sync</span>
                                เชื่อมต่อ HOSxP อัตโนมัติ (CSHOS)
                            </span>
                        </div>
                        <div class="grid-layout grid-cols-4">
                            <!-- ลำดับ -->
                            <div>
                                <label class="form-label">
                                    <span>ลำดับ</span>
                                    <span class="form-label-hint">ลำดับที่</span>
                                </label>
                                <input type="number" id="edit_order_no" name="order_no" class="input-control" required>
                            </div>

                            <!-- HN + ค้นหา -->
                            <div class="span-2">
                                <label class="form-label">
                                    <span>HN / หมายเลขตัวอย่าง</span>
                                    <span class="form-label-hint">ปรับ 9 หลัก & ดึงชื่ออัตโนมัติ</span>
                                </label>
                                <div class="hn-input-group">
                                    <input type="text" id="edit_hn" name="hn" class="input-control" placeholder="เช่น 185543 หรือ 000185543" autocomplete="off">
                                    <button type="button" class="btn-lookup-cshos" onclick="lookupHnPatient('edit_hn', 'edit_patient_name', 'edit_hn_feedback', true)" title="ค้นหาชื่อจากฐานข้อมูล HOSxP">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">search</span>
                                        ค้นหา HOSxP
                                    </button>
                                </div>
                                <div id="edit_hn_feedback" class="hn-lookup-feedback"></div>
                            </div>

                            <!-- วันที่ตรวจ (ปฏิทิน Datepicker) -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ตรวจ (ปฏิทิน)</span>
                                    <span class="form-label-hint">เลือกวันที่</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" id="edit_test_date" class="input-control thai-datepicker" placeholder="ว/ด/ป เช่น 16/09/2569" autocomplete="off">
                                    <span class="material-symbols-outlined">calendar_month</span>
                                </div>
                            </div>

                            <!-- ชื่อผู้เข้ารับการตรวจ -->
                            <div class="span-2">
                                <label class="form-label">
                                    <span>ชื่อผู้เข้ารับการตรวจ <span class="required">*</span></span>
                                    <span class="form-label-hint">ดึงจาก HOSxP หรือพิมพ์เอง</span>
                                </label>
                                <input type="text" id="edit_patient_name" name="patient_name" class="input-control" required>
                            </div>

                            <!-- วันที่ตรวจ -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ตรวจ</span>
                                    <span class="form-label-hint">1-31</span>
                                </label>
                                <input type="text" id="edit_test_day" name="test_day" class="input-control">
                            </div>

                            <!-- เดือนที่ตรวจ -->
                            <div>
                                <label class="form-label">
                                    <span>เดือนที่ตรวจ</span>
                                    <span class="form-label-hint">เช่น ก.ย.69</span>
                                </label>
                                <input type="text" id="edit_month_year" name="month_year" class="input-control">
                            </div>
                        </div>
                    </div>

                    <!-- หมวดที่ 2: สถานีตำรวจและเจ้าหน้าที่ผู้ร้องขอ -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">local_police</span>
                                ข้อมูลสถานีตำรวจและเจ้าหน้าที่ผู้ร้องขอ
                            </div>
                        </div>
                        <div class="grid-layout grid-cols-3">
                            <div>
                                <label class="form-label">
                                    <span>สถานีตำรวจที่ส่งตรวจ (สภ.)</span>
                                </label>
                                <select id="edit_police_station" name="police_station" class="input-control">
                                    <option value="">-- เลือกสถานีตำรวจ --</option>
                                    <option value="สภ.เชียงแสน">สภ.เชียงแสน</option>
                                    <option value="สภ.บ้านแซว">สภ.บ้านแซว</option>
                                    <option value="ไม่ทราบ สภ.">ไม่ทราบ สภ.</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">ชื่อ-สกุล ตำรวจที่ร้องขอ</label>
                                <input type="text" id="edit_police_officer" name="police_officer" class="input-control">
                            </div>
                            <div>
                                <label class="form-label">เบอร์โทรติดต่อ</label>
                                <input type="text" id="edit_contact_phone" name="contact_phone" class="input-control">
                            </div>
                        </div>
                    </div>

                    <!-- หมวดที่ 3: เอกสารประกอบและการส่งตรวจทางห้องปฏิบัติการ -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">folder_shared</span>
                                เอกสารประกอบและการส่งตรวจห้องปฏิบัติการ
                            </div>
                        </div>
                        <div class="grid-layout grid-cols-3">
                            <!-- เอกสาร 3 ชนิด -->
                            <div class="span-3" style="background: #f8fafc; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                                <label class="form-label" style="margin-bottom: 8px;">เอกสารประกอบการส่งตรวจ</label>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                                    <div>
                                        <small style="display: block; margin-bottom: 4px; font-weight: 600; color: #475569;">ใบ ค.8</small>
                                        <select id="edit_doc_k8" name="doc_k8" class="input-control">
                                            <option value="">-- ว่าง --</option>
                                            <option value="มี">มี</option>
                                            <option value="ไม่มี">ไม่มี</option>
                                        </select>
                                    </div>
                                    <div>
                                        <small style="display: block; margin-bottom: 4px; font-weight: 600; color: #475569;">ใบ Pher</small>
                                        <select id="edit_doc_pher" name="doc_pher" class="input-control">
                                            <option value="">-- ว่าง --</option>
                                            <option value="มี">มี</option>
                                            <option value="ไม่มี">ไม่มี</option>
                                        </select>
                                    </div>
                                    <div>
                                        <small style="display: block; margin-bottom: 4px; font-weight: 600; color: #475569;">ใบนำส่งตรวจแล็บ</small>
                                        <select id="edit_doc_lab_send" name="doc_lab_send" class="input-control">
                                            <option value="">-- ว่าง --</option>
                                            <option value="มี">มี</option>
                                            <option value="ไม่มี">ไม่มี</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- วันที่ส่งตรวจแล็บ (Datepicker) -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ส่งตรวจทางแล็บ</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" id="edit_lab_send_date" name="lab_send_date" class="input-control thai-datepicker" autocomplete="off">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                </div>
                            </div>

                            <!-- วันที่ออกผลใน HosXP (Datepicker) -->
                            <div>
                                <label class="form-label">
                                    <span>วันที่ออกผลใน HosXP</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" id="edit_hosxp_result_date" name="hosxp_result_date" class="input-control thai-datepicker" autocomplete="off">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- หมวดที่ 4: สถานะและการเบิกจ่าย -->
                    <div class="modal-section-card">
                        <div class="modal-section-title">
                            <div class="modal-section-title-text">
                                <span class="material-symbols-outlined">payments</span>
                                สถานะการเบิกจ่ายและการเงิน
                            </div>
                        </div>
                        <div class="grid-layout grid-cols-3">
                            <div>
                                <label class="form-label">สถานะการเบิกจ่าย</label>
                                <select id="edit_claim_status" name="claim_status" class="input-control" style="font-weight: 500;">
                                    <option value="">-- ยังไม่ระบุ (รอส่งเบิก) --</option>
                                    <option value="เบิกจ่ายแล้ว">เบิกจ่ายแล้ว</option>
                                    <option value="รอเบิกจ่าย">รอเบิกจ่าย</option>
                                    <option value="ยกเลิก">ยกเลิก</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">
                                    <span>วันที่เบิกจ่าย</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" id="edit_claim_date" name="claim_date" class="input-control thai-datepicker" autocomplete="off">
                                    <span class="material-symbols-outlined">event_available</span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">
                                    <span>วันที่ได้รับเงิน</span>
                                    <span class="form-label-hint">ว/ด/ป</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text" id="edit_payment_received_date" name="payment_received_date" class="input-control thai-datepicker" autocomplete="off">
                                    <span class="material-symbols-outlined">paid</span>
                                </div>
                            </div>
                            <div class="span-full">
                                <label class="form-label">หมายเหตุเพิ่มเติม</label>
                                <input type="text" id="edit_remarks" name="remarks" class="input-control" placeholder="ระบุหมายเหตุ เช่น เลขที่ใบเสร็จ หรือสาเหตุการยกเลิก">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 0.95rem;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Delete Confirmation -->
    <div id="deleteModal" class="modal-backdrop">
        <div class="modal-dialog" style="max-width: 440px;">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <h3 style="color: #ef4444; display: flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-outlined">warning</span>
                    ยืนยันการลบรายการ
                </h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body" style="padding-top: 10px;">
                    <p style="font-size: 0.95rem; color: #475569; margin-bottom: 8px;">
                        คุณต้องการลบข้อมูลลำดับที่ <strong id="delete_order_no"></strong> หรือไม่?
                    </p>
                    <div style="background: #f1f5f9; padding: 10px 14px; border-radius: 10px; font-weight: 600; color: #0f172a;" id="delete_patient_name"></div>
                    <small style="color: #ef4444; display: block; margin-top: 8px;">* การดำเนินการนี้ไม่สามารถเรียกคืนข้อมูลได้</small>
                </div>
                <div class="modal-footer" style="border-top: none; background: transparent;">
                    <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="background: #ef4444; border-color: #ef4444;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                        ยืนยันการลบ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2026 กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลเชียงแสน (Chiang Saen Hospital)</p>
    </footer>

    <script>
        // --- Thai Month Abbreviations ---
        const THAI_MONTHS_SHORT = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        // --- Helper: Parse Date String (supports DD/MM/BBBB, DD/MM/YYYY, YYYY-MM-DD) ---
        function parseThaiDate(datestr) {
            if (!datestr) return null;
            const str = String(datestr).trim();
            const parts = str.split(/[\/\-]/);
            if (parts.length === 3) {
                let p1 = parseInt(parts[0], 10);
                let p2 = parseInt(parts[1], 10);
                let p3 = parseInt(parts[2], 10);
                if (!isNaN(p1) && !isNaN(p2) && !isNaN(p3)) {
                    if (p1 > 1000) {
                        // yyyy-mm-dd
                        let y = p1 > 2400 ? p1 - 543 : p1;
                        return new Date(y, p2 - 1, p3);
                    } else {
                        // dd/mm/yyyy
                        let y = p3 > 2400 ? p3 - 543 : (p3 < 100 ? (p3 > 50 ? p3 + 1900 : p3 + 2000) : p3);
                        return new Date(y, p2 - 1, p1);
                    }
                }
            }
            const d = new Date(str);
            return isNaN(d.getTime()) ? null : d;
        }

        // --- Helper: Format Date to Thai Buddhist Era (DD/MM/BBBB) ---
        function formatThaiDateBE(date) {
            if (!date) return '';
            const d = String(date.getDate()).padStart(2, '0');
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const y = date.getFullYear() + 543;
            return `${d}/${m}/${y}`;
        }

        // --- Common Flatpickr Configuration ---
        const commonFpConfig = {
            locale: "th",
            dateFormat: "d/m/Y",
            allowInput: true,
            formatDate: function(date) {
                return formatThaiDateBE(date);
            },
            parseDate: function(datestr) {
                return parseThaiDate(datestr);
            }
        };

        // Initialize Datepickers for Add Modal
        const addLabSendFp = flatpickr("#add_lab_send_date", commonFpConfig);
        const addHosxpResultFp = flatpickr("#add_hosxp_result_date", commonFpConfig);
        const addClaimFp = flatpickr("#add_claim_date", commonFpConfig);
        const addPaymentFp = flatpickr("#add_payment_received_date", commonFpConfig);

        const addTestDateFp = flatpickr("#add_test_date", {
            ...commonFpConfig,
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    const dt = selectedDates[0];
                    const day = dt.getDate();
                    const monthIdx = dt.getMonth();
                    const yearBeShort = String(dt.getFullYear() + 543).slice(-2);
                    document.getElementById('add_test_day').value = day;
                    document.getElementById('add_month_year').value = THAI_MONTHS_SHORT[monthIdx] + yearBeShort;
                }
            }
        });

        // Initialize Datepickers for Edit Modal
        const editLabSendFp = flatpickr("#edit_lab_send_date", commonFpConfig);
        const editHosxpResultFp = flatpickr("#edit_hosxp_result_date", commonFpConfig);
        const editClaimFp = flatpickr("#edit_claim_date", commonFpConfig);
        const editPaymentFp = flatpickr("#edit_payment_received_date", commonFpConfig);

        const editTestDateFp = flatpickr("#edit_test_date", {
            ...commonFpConfig,
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    const dt = selectedDates[0];
                    const day = dt.getDate();
                    const monthIdx = dt.getMonth();
                    const yearBeShort = String(dt.getFullYear() + 543).slice(-2);
                    document.getElementById('edit_test_day').value = day;
                    document.getElementById('edit_month_year').value = THAI_MONTHS_SHORT[monthIdx] + yearBeShort;
                }
            }
        });

        // --- HN Auto-padding (9 digits) and HOSxP Patient Lookup ---
        async function lookupHnPatient(hnInputId, nameInputId, feedbackId, forceAutoPad = true) {
            const hnInput = document.getElementById(hnInputId);
            const nameInput = document.getElementById(nameInputId);
            const feedback = document.getElementById(feedbackId);

            if (!hnInput) return;
            let val = hnInput.value.trim();
            if (!val) {
                if (feedback) feedback.innerHTML = '';
                return;
            }

            // Auto-pad to 9 digits with leading zeros if numeric
            if (/^\d+$/.test(val) && val.length <= 9 && forceAutoPad) {
                val = val.padStart(9, '0');
                hnInput.value = val;
            }

            if (feedback) {
                feedback.className = 'hn-lookup-feedback loading';
                feedback.innerHTML = '<span class="material-symbols-outlined" style="font-size:15px; animation: spin 1s linear infinite;">sync</span> กำลังค้นหาข้อมูลใน HOSxP (CSHOS)...';
            }

            try {
                const response = await fetch(`/blood-alc/lookup-patient?hn=${encodeURIComponent(val)}`);
                const res = await response.json();

                if (res.success && res.patient_name) {
                    nameInput.value = res.patient_name;
                    nameInput.classList.remove('highlight-glow');
                    void nameInput.offsetWidth; // trigger browser reflow
                    nameInput.classList.add('highlight-glow');

                    if (feedback) {
                        feedback.className = 'hn-lookup-feedback success';
                        feedback.innerHTML = `<span class="material-symbols-outlined" style="font-size:15px;">check_circle</span> พบใน HOSxP: <strong>${res.patient_name}</strong> (HN: ${res.hn})`;
                    }
                } else {
                    if (feedback) {
                        feedback.className = 'hn-lookup-feedback error';
                        feedback.innerHTML = `<span class="material-symbols-outlined" style="font-size:15px;">info</span> ไม่พบ HN ในระบบ HOSxP (สามารถพิมพ์ชื่อผู้ตรวจเองได้)`;
                    }
                }
            } catch (err) {
                console.error('HN lookup error:', err);
                if (feedback) {
                    feedback.className = 'hn-lookup-feedback error';
                    feedback.innerHTML = '<span class="material-symbols-outlined" style="font-size:15px;">cloud_off</span> ไม่สามารถเชื่อมต่อฐานข้อมูล HOSxP ได้ชั่วคราว';
                }
            }
        }

        function bindHnEvents(hnInputId, nameInputId, feedbackId) {
            const hnInput = document.getElementById(hnInputId);
            if (!hnInput) return;

            hnInput.addEventListener('blur', function() {
                lookupHnPatient(hnInputId, nameInputId, feedbackId, true);
            });

            hnInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupHnPatient(hnInputId, nameInputId, feedbackId, true);
                }
            });

            hnInput.addEventListener('input', function() {
                const v = hnInput.value.trim();
                if (/^\d{9}$/.test(v)) {
                    lookupHnPatient(hnInputId, nameInputId, feedbackId, false);
                }
            });
        }

        // Bind HN input event listeners
        bindHnEvents('add_hn', 'add_patient_name', 'add_hn_feedback');
        bindHnEvents('edit_hn', 'edit_patient_name', 'edit_hn_feedback');

        // Helper to safely set Flatpickr value
        function setFpValue(fpInstance, inputId, val) {
            const el = document.getElementById(inputId);
            if (!el) return;
            if (val) {
                el.value = val;
                if (fpInstance) {
                    const parsed = parseThaiDate(val);
                    if (parsed) fpInstance.setDate(parsed, false);
                }
            } else {
                el.value = '';
                if (fpInstance) fpInstance.clear();
            }
        }

        // Modal Open / Close Handlers
        function openAddModal() {
            const fb = document.getElementById('add_hn_feedback');
            if (fb) fb.innerHTML = '';
            const st = document.getElementById('add_police_station');
            if (st) st.value = '';
            const dk = document.getElementById('add_doc_k8');
            if (dk) dk.value = '';
            const dp = document.getElementById('add_doc_pher');
            if (dp) dp.value = '';
            const dl = document.getElementById('add_doc_lab_send');
            if (dl) dl.value = '';
            document.getElementById('addModal').style.display = 'flex';
        }
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(id) {
            const fb = document.getElementById('edit_hn_feedback');
            if (fb) fb.innerHTML = '';

            fetch(`/blood-alc/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('edit_order_no').value = data.order_no || '';
                    document.getElementById('edit_month_year').value = data.month_year || '';
                    document.getElementById('edit_test_day').value = data.test_day || '';
                    document.getElementById('edit_hn').value = data.hn || '';
                    document.getElementById('edit_patient_name').value = data.patient_name || '';

                    // Police station dropdown with normalization
                    let station = (data.police_station || '').trim();
                    if (station === 'สภ. เชียงแสน' || station === 'สภ.เชีียงแสน') {
                        station = 'สภ.เชียงแสน';
                    }
                    const stSelect = document.getElementById('edit_police_station');
                    stSelect.value = station;
                    if (station && stSelect.value !== station) {
                        const opt = document.createElement('option');
                        opt.value = station;
                        opt.textContent = station;
                        stSelect.appendChild(opt);
                        stSelect.value = station;
                    }

                    document.getElementById('edit_police_officer').value = data.police_officer || '';
                    document.getElementById('edit_contact_phone').value = data.contact_phone || '';
                    document.getElementById('edit_doc_k8').value = data.doc_k8 || '';
                    document.getElementById('edit_doc_pher').value = data.doc_pher || '';
                    document.getElementById('edit_doc_lab_send').value = data.doc_lab_send || '';

                    // Datepicker values
                    setFpValue(editLabSendFp, 'edit_lab_send_date', data.lab_send_date);
                    setFpValue(editHosxpResultFp, 'edit_hosxp_result_date', data.hosxp_result_date);
                    setFpValue(editClaimFp, 'edit_claim_date', data.claim_date);
                    setFpValue(editPaymentFp, 'edit_payment_received_date', data.payment_received_date);

                    // If month_year and test_day exist, try setting test_date display
                    if (data.test_day && data.month_year) {
                        const mMatch = data.month_year.match(/([ก-๙\.]+)(\d{2})/);
                        if (mMatch) {
                            const mStr = mMatch[1];
                            const yStr = mMatch[2];
                            const mIdx = THAI_MONTHS_SHORT.findIndex(m => m === mStr || m === mStr + '.');
                            if (mIdx !== -1) {
                                const fullBeYear = 2500 + parseInt(yStr, 10);
                                const dayStr = String(data.test_day).padStart(2, '0');
                                const mPad = String(mIdx + 1).padStart(2, '0');
                                const dStr = `${dayStr}/${mPad}/${fullBeYear}`;
                                setFpValue(editTestDateFp, 'edit_test_date', dStr);
                            }
                        }
                    } else {
                        setFpValue(editTestDateFp, 'edit_test_date', '');
                    }

                    document.getElementById('edit_claim_status').value = data.claim_status || '';
                    document.getElementById('edit_remarks').value = data.remarks || '';

                    document.getElementById('editForm').action = `/blood-alc/${id}`;
                    document.getElementById('editModal').style.display = 'flex';
                })
                .catch(err => {
                    alert('ไม่สามารถโหลดข้อมูลได้');
                    console.error(err);
                });
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function confirmDelete(id, name, orderNo) {
            document.getElementById('delete_order_no').innerText = orderNo;
            document.getElementById('delete_patient_name').innerText = name;
            document.getElementById('deleteForm').action = `/blood-alc/${id}`;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            const delModal = document.getElementById('deleteModal');
            if (event.target === addModal) closeAddModal();
            if (event.target === editModal) closeEditModal();
            if (event.target === delModal) closeDeleteModal();
        }
    </script>
</body>
</html>
