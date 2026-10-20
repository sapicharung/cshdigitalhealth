<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>จัดการผู้ใช้งานระบบ - CSHOS STAFF INFO</title>
    
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Google Fonts: Prompt & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --primary-light: #EEF2FF;
            --secondary: #64748B;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #0284C7;
            --bg-main: #F8FAFC;
            --surface: #FFFFFF;
            --border: #E2E8F0;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --radius-lg: 14px;
            --radius-md: 10px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.07);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            line-height: 1.55;
        }

        .material-symbols-outlined,
        .material-symbols-Outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none !important;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            vertical-align: middle;
        }

        /* --- Header Navbar (Full Screen) --- */
        .navbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .brand-area {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #4F46E5, #6366F1);
            color: #FFFFFF;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            flex-shrink: 0;
        }

        .brand-icon .material-symbols-Outlined {
            font-size: 24px !important;
        }

        .brand-text h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.01em;
        }

        .brand-text p {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            background: #FFFFFF;
            color: #334155;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-nav:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4F46E5, #6366F1);
            color: white;
            border: none;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338CA, #4F46E5);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);
            transform: translateY(-1px);
        }

        .btn-risk-nav {
            background: #FFF1F2;
            color: #BE123C;
            border-color: #FECDD3;
            font-weight: 600;
        }

        .btn-risk-nav:hover {
            background: #FFE4E6;
            border-color: #FDA4AF;
        }

        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            background: #F1F5F9;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
        }

        .role-pill {
            font-size: 0.72rem;
            padding: 2px 8px;
            border-radius: 9999px;
            font-weight: 700;
            letter-spacing: 0.03em;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .role-pill-admin {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .role-pill-it {
            background: #E0E7FF;
            color: #4338CA;
            border: 1px solid #C7D2FE;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.8rem;
            background: #FFFFFF;
            color: var(--danger);
            border: 1px solid #FCA5A5;
            border-radius: 9px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #FEF2F2;
        }

        /* --- Full Width Container --- */
        .container {
            width: 96%;
            max-width: 1750px;
            margin: 1.25rem auto 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            flex: 1;
        }

        /* --- Stats Row --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.15rem 1.35rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon .material-symbols-Outlined {
            font-size: 26px !important;
        }

        .stat-icon-all {
            background: #EEF2FF;
            color: #4F46E5;
        }

        .stat-icon-admin {
            background: #FEE2E2;
            color: #DC2626;
        }

        .stat-icon-it {
            background: #E0F2FE;
            color: #0284C7;
        }

        .stat-content {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.15;
            font-family: 'Inter', sans-serif;
        }

        .stat-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 3px;
            font-weight: 500;
        }

        /* --- Controls & Filter Bar --- */
        .controls-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1rem 1.35rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            flex: 1;
        }

        .search-box {
            position: relative;
            min-width: 260px;
            flex: 1;
            max-width: 400px;
        }

        .search-box .material-symbols-Outlined {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 20px !important;
            pointer-events: none;
        }

        .search-box input {
            width: 100%;
            padding: 0.55rem 1rem 0.55rem 2.4rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            outline: none;
            transition: all 0.2s;
            background: #F8FAFC;
        }

        .search-box input:focus {
            border-color: var(--primary);
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .select-filter {
            padding: 0.55rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            outline: none;
            background: #FFFFFF;
            color: var(--text-dark);
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .select-filter:focus {
            border-color: var(--primary);
        }

        .btn-filter {
            padding: 0.55rem 1.1rem;
            background: #334155;
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background: #1E293B;
        }

        .btn-reset {
            padding: 0.55rem 0.9rem;
            background: #F1F5F9;
            color: #64748B;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }

        .btn-reset:hover {
            background: #E2E8F0;
            color: var(--text-dark);
        }

        /* --- Users Table Card --- */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        thead {
            background-color: #F8FAFC;
            border-bottom: 1px solid var(--border);
        }

        th {
            padding: 0.85rem 1rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid #F1F5F9;
            transition: background-color 0.15s ease;
        }

        tbody tr:hover {
            background-color: #F8FAFC;
        }

        td {
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
            color: #334155;
            vertical-align: middle;
        }

        .user-name-cell {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #EEF2FF;
            color: #4F46E5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
            border: 1px solid #E0E7FF;
        }

        .user-avatar-admin {
            background: #FEE2E2;
            color: #DC2626;
            border-color: #FECACA;
        }

        .user-email-text {
            color: #64748B;
            font-family: 'Inter', monospace;
            font-size: 0.84rem;
        }

        /* --- Action Buttons --- */
        .action-group {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #FFFFFF;
            cursor: pointer;
            transition: all 0.15s ease;
            color: #475569;
        }

        .btn-action:hover {
            background: #F1F5F9;
            color: #0F172A;
        }

        .btn-action-edit:hover {
            color: #2563EB;
            border-color: #93C5FD;
            background: #EFF6FF;
        }

        .btn-action-delete:hover {
            color: #DC2626;
            border-color: #FCA5A5;
            background: #FEF2F2;
        }

        .btn-action:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* --- Alert Messages --- */
        .alert-box {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1.25rem;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-danger {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-card {
            background: #FFFFFF;
            border-radius: 16px;
            width: 95%;
            max-width: 540px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(15px);
            transition: transform 0.2s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-overlay.active .modal-card {
            transform: translateY(0);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #F8FAFC;
        }

        .modal-header h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-close {
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 4px;
            transition: all 0.15s;
        }

        .btn-close:hover {
            background: #E2E8F0;
            color: var(--text-dark);
        }

        .modal-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
            max-height: 80vh;
            overflow-y: auto;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
        }

        .form-group label span.required {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
            background: #FFFFFF;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrapper input {
            padding-right: 2.5rem;
        }

        .btn-toggle-password {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 4px;
        }

        .btn-toggle-password:hover {
            color: #334155;
        }

        .role-cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .role-card-option {
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.9rem 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            background: #FFFFFF;
        }

        .role-card-option:hover {
            border-color: #CBD5E1;
            background: #F8FAFC;
        }

        .role-card-option.selected {
            border-color: var(--primary);
            background: #EEF2FF;
        }

        .role-card-option input[type="radio"] {
            position: absolute;
            top: 10px;
            right: 10px;
            accent-color: var(--primary);
        }

        .role-card-header {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 700;
            font-size: 0.92rem;
            margin-bottom: 4px;
        }

        .role-card-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.35;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background: #F8FAFC;
        }

        .btn-secondary {
            padding: 0.55rem 1.1rem;
            background: #FFFFFF;
            color: #475569;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #F1F5F9;
            border-color: #CBD5E1;
        }

        .btn-save {
            padding: 0.55rem 1.25rem;
            background: linear-gradient(135deg, #4F46E5, #6366F1);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
            transition: all 0.2s;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #4338CA, #4F46E5);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);
            transform: translateY(-1px);
        }

        /* Toast Container */
        #toastContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-msg {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            background: #0F172A;
            color: white;
            border-radius: 10px;
            font-size: 0.88rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.25s ease-out;
            max-width: 380px;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Pagination */
        .pagination-bar {
            padding: 0.9rem 1.25rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="navbar">
        <div class="brand-area">
            <div class="brand-icon">
                <span class="material-symbols-Outlined">manage_accounts</span>
            </div>
            <div class="brand-text">
                <h1>จัดการผู้ใช้งานระบบ (User Management)</h1>
                <p>กำหนดระดับสิทธิ์การเข้าถึง CSHOS: ผู้ดูแลระบบ (Admin) & ฝ่ายไอที (IT)</p>
            </div>
        </div>

        <div class="nav-actions">
            <a href="{{ route('admin.installations') }}" class="btn-nav">
                <span class="material-symbols-Outlined">arrow_back</span>
                <span>หน้ารายการเครื่องคอมพิวเตอร์</span>
            </a>

            <a href="{{ route('admin.risk-rules.index') }}" class="btn-nav btn-risk-nav" title="จัดการเงื่อนไขตรวจสอบโปรแกรมเสี่ยง">
                <span class="material-symbols-Outlined" style="color: #E11D48;">shield_lock</span>
                <span>จัดการโปรแกรมเสี่ยง</span>
            </a>

            <button type="button" class="btn-nav btn-primary" onclick="openAddUserModal()">
                <span class="material-symbols-Outlined">person_add</span>
                <span>เพิ่มผู้ใช้งานใหม่</span>
            </button>

            <div class="user-badge">
                <span class="material-symbols-Outlined" style="font-size: 18px; color: var(--primary);">account_circle</span>
                <span>{{ Auth::user()->name }}</span>
                <span class="role-pill role-pill-{{ Auth::user()->role }}">{{ strtoupper(Auth::user()->role) }}</span>
            </div>

            <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-logout">
                    <span class="material-symbols-Outlined" style="font-size: 16px;">logout</span>
                    <span>ออก</span>
                </button>
            </form>
        </div>
    </header>

    <main class="container">

        <!-- Flash messages -->
        @if(session('success'))
            <div class="alert-box alert-success">
                <span class="material-symbols-Outlined">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-danger">
                <span class="material-symbols-Outlined">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-box alert-danger">
                <span class="material-symbols-Outlined">error</span>
                <div>
                    <strong>เกิดข้อผิดพลาด:</strong>
                    <ul style="margin-left: 1.25rem; margin-top: 4px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Stats Grid -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-all">
                    <span class="material-symbols-Outlined">group</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="stat-label">บัญชีผู้ใช้งานทั้งหมด</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-admin">
                    <span class="material-symbols-Outlined">admin_panel_settings</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value" style="color: #DC2626;">{{ number_format($stats['admin'] ?? 0) }}</div>
                    <div class="stat-label">ผู้ดูแลระบบสูงสุด (Admin)</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-it">
                    <span class="material-symbols-Outlined">engineering</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value" style="color: #0284C7;">{{ number_format($stats['it'] ?? 0) }}</div>
                    <div class="stat-label">เจ้าหน้าที่ฝ่ายไอที (IT Staff)</div>
                </div>
            </div>
        </section>

        <!-- Search & Filter Card -->
        <section class="controls-card">
            <form action="{{ route('admin.users.index') }}" method="GET" class="filter-form">
                <div class="search-box">
                    <span class="material-symbols-Outlined">search</span>
                    <input type="text" name="search" placeholder="ค้นหาชื่อผู้ใช้งาน หรือ อีเมล..." value="{{ request('search') }}">
                </div>

                <select name="role" class="select-filter">
                    <option value="">-- ทุกระดับสิทธิ์ (All Roles) --</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบ (Admin)</option>
                    <option value="it" {{ request('role') === 'it' ? 'selected' : '' }}>💻 เจ้าหน้าที่ไอที (IT)</option>
                </select>

                <button type="submit" class="btn-filter">
                    <span class="material-symbols-Outlined" style="font-size: 17px;">tune</span>
                    <span>ค้นหา</span>
                </button>

                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="btn-reset">
                        <span class="material-symbols-Outlined" style="font-size: 16px;">close</span>
                        <span>ล้างกรอง</span>
                    </a>
                @endif
            </form>
        </section>

        <!-- Users Table Card -->
        <section class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th style="min-width: 200px;">ชื่อผู้ใช้งาน (Username)</th>
                            <th style="min-width: 220px;">อีเมล (Email)</th>
                            <th style="width: 140px; text-align: center;">ระดับสิทธิ์ (Role)</th>
                            <th style="width: 220px;">สิทธิ์การใช้งาน (Permissions)</th>
                            <th style="width: 160px;">วันที่ลงทะเบียน</th>
                            <th style="width: 110px; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td style="text-align: center; color: #94A3B8;">
                                    {{ $users->firstItem() + $index }}
                                </td>
                                <td>
                                    <div class="user-name-cell">
                                        <div class="user-avatar {{ $user->role === 'admin' ? 'user-avatar-admin' : '' }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-dark);">
                                                {{ $user->name }}
                                                @if($user->id === Auth::id())
                                                    <span style="font-size: 0.72rem; padding: 1px 6px; background: #FEF3C7; color: #B45309; border-radius: 6px; margin-left: 4px; font-weight: 600;">บัญชีคุณ</span>
                                                @endif
                                            </div>
                                            <div style="font-size: 0.75rem; color: #94A3B8;">ID: #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="user-email-text">{{ $user->email }}</span>
                                </td>
                                <td style="text-align: center;">
                                    @if($user->role === 'admin')
                                        <span class="role-pill role-pill-admin" style="display: inline-flex; align-items: center; gap: 3px;">
                                            <span class="material-symbols-Outlined" style="font-size: 13px;">admin_panel_settings</span>
                                            <span>Admin</span>
                                        </span>
                                    @else
                                        <span class="role-pill role-pill-it" style="display: inline-flex; align-items: center; gap: 3px;">
                                            <span class="material-symbols-Outlined" style="font-size: 13px;">engineering</span>
                                            <span>IT</span>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span style="font-size: 0.8rem; color: #047857; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-Outlined" style="font-size: 15px;">check_circle</span>
                                            <span>สิทธิ์เต็มทุกฟังก์ชันระบบ</span>
                                        </span>
                                    @else
                                        <span style="font-size: 0.78rem; color: #64748B; display: inline-flex; align-items: center; gap: 4px;" title="ดูเครื่องลูกข่าย, สเปก, VNC, AnyDesk (ไม่เห็นโปรแกรมเสี่ยง, ไม่เห็นปุ่มจัดการหลัก)">
                                            <span class="material-symbols-Outlined" style="font-size: 15px; color: #0284C7;">visibility</span>
                                            <span>ตรวจเครื่อง & สเปก (ตัดโปรแกรมเสี่ยง/จัดการ)</span>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; color: #64748B;">
                                        {{ $user->created_at ? $user->created_at->format('d/m/Y H:i น.') : '-' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-group">
                                        <button type="button" class="btn-action btn-action-edit" onclick="openEditUserModal({{ json_encode($user) }})" title="แก้ไขข้อมูลผู้ใช้">
                                            <span class="material-symbols-Outlined" style="font-size: 16px;">edit</span>
                                        </button>

                                        @if($user->id !== Auth::id())
                                            <button type="button" class="btn-action btn-action-delete" onclick="confirmDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" title="ลบผู้ใช้งาน">
                                                <span class="material-symbols-Outlined" style="font-size: 16px;">delete</span>
                                            </button>
                                        @else
                                            <button type="button" class="btn-action" disabled title="ไม่สามารถลบบัญชีที่กำลังล็อกอินอยู่ได้" style="opacity: 0.3;">
                                                <span class="material-symbols-Outlined" style="font-size: 16px;">block</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem 1rem;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; color: #94A3B8;">
                                        <span class="material-symbols-Outlined" style="font-size: 48px; color: #CBD5E1;">group_off</span>
                                        <p style="font-weight: 600; font-size: 1rem; color: #64748B;">ไม่พบข้อมูลผู้ใช้งาน</p>
                                        <p style="font-size: 0.82rem;">ลองเปลี่ยนคำค้นหา หรือเพิ่มผู้ใช้งานใหม่</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="pagination-bar">
                    <div>
                        แสดง {{ $users->firstItem() }} ถึง {{ $users->lastItem() }} จากทั้งหมด {{ $users->total() }} บัญชี
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        </section>

    </main>

    <!-- Modal Form (Add / Edit User) -->
    <div class="modal-overlay" id="userModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalTitle">
                    <span class="material-symbols-Outlined" id="modalIcon" style="color: var(--primary);">person_add</span>
                    <span id="modalTitleText">เพิ่มผู้ใช้งานใหม่</span>
                </h3>
                <button type="button" class="btn-close" onclick="closeUserModal()">
                    <span class="material-symbols-Outlined">close</span>
                </button>
            </div>

            <form id="userForm" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" id="userId" value="">

                <div class="modal-body">
                    <!-- Username -->
                    <div class="form-group">
                        <label for="userName">ชื่อผู้ใช้งาน (Username) <span class="required">*</span></label>
                        <input type="text" id="userName" name="name" class="form-control" required placeholder="เช่น admin_it หรือ somchai">
                        <small style="font-size: 0.74rem; color: var(--text-muted);">ใช้สำหรับเข้าสู่ระบบระบบ CSHOS</small>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="userEmail">อีเมล (Email) <span class="required">*</span></label>
                        <input type="email" id="userEmail" name="email" class="form-control" required placeholder="เช่น it@csh.local หรือ user@chiangsaenhospital.go.th">
                    </div>

                    <!-- Role Selection -->
                    <div class="form-group">
                        <label>ระดับสิทธิ์การใช้งาน (Role) <span class="required">*</span></label>
                        <div class="role-cards-grid">
                            <!-- Admin Card -->
                            <div class="role-card-option" id="roleCardAdmin" onclick="selectRole('admin')">
                                <input type="radio" name="role" id="roleAdmin" value="admin" required>
                                <div class="role-card-header" style="color: #DC2626;">
                                    <span class="material-symbols-Outlined" style="font-size: 18px;">admin_panel_settings</span>
                                    <span>ADMIN</span>
                                </div>
                                <div class="role-card-desc">
                                    ผู้ดูแลระบบสูงสุด สิทธิ์เต็มทุกฟังก์ชัน จัดการโปรแกรมเสี่ยง, จัดการผู้ใช้, อัปเดตเซิร์ฟเวอร์
                                </div>
                            </div>

                            <!-- IT Card -->
                            <div class="role-card-option" id="roleCardIt" onclick="selectRole('it')">
                                <input type="radio" name="role" id="roleIt" value="it" required>
                                <div class="role-card-header" style="color: #0284C7;">
                                    <span class="material-symbols-Outlined" style="font-size: 18px;">engineering</span>
                                    <span>IT</span>
                                </div>
                                <div class="role-card-desc">
                                    เจ้าหน้าที่ไอที ดูเครื่องลูกข่าย, สเปก, รีโมท VNC/AnyDesk (ตัดโปรแกรมเสี่ยงและจัดการ)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="userPassword">
                            <span id="passwordLabel">รหัสผ่าน (Password)</span>
                            <span class="required" id="passwordRequiredStar">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" id="userPassword" name="password" class="form-control" placeholder="ความยาวอย่างน้อย 4 ตัวอักษร">
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('userPassword', this)">
                                <span class="material-symbols-Outlined" style="font-size: 18px;">visibility</span>
                            </button>
                        </div>
                        <small id="passwordHelp" style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px;">
                            ตั้งรหัสผ่านสำหรับเข้าสู่ระบบ
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeUserModal()">ยกเลิก</button>
                    <button type="submit" class="btn-save" id="btnSubmitUser">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">save</span>
                        <span id="btnSubmitText">บันทึกข้อมูล</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteUserForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Toast Notification Container -->
    <div id="toastContainer"></div>

    <script>
        const baseUrl = "{{ url('admin/users') }}";

        function selectRole(role) {
            const adminRadio = document.getElementById('roleAdmin');
            const itRadio = document.getElementById('roleIt');
            const adminCard = document.getElementById('roleCardAdmin');
            const itCard = document.getElementById('roleCardIt');

            if (role === 'admin') {
                adminRadio.checked = true;
                adminCard.classList.add('selected');
                itCard.classList.remove('selected');
            } else {
                itRadio.checked = true;
                itCard.classList.add('selected');
                adminCard.classList.remove('selected');
            }
        }

        function openAddUserModal() {
            document.getElementById('modalTitleText').innerText = 'เพิ่มผู้ใช้งานใหม่';
            document.getElementById('modalIcon').innerText = 'person_add';
            document.getElementById('modalIcon').style.color = '#4F46E5';
            
            const form = document.getElementById('userForm');
            form.action = "{{ route('admin.users.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('userId').value = '';

            document.getElementById('userName').value = '';
            document.getElementById('userEmail').value = '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = true;
            document.getElementById('passwordRequiredStar').style.display = 'inline';
            document.getElementById('passwordHelp').innerText = 'รหัสผ่านสำหรับเข้าสู่ระบบ (อย่างน้อย 4 ตัวอักษร)';
            document.getElementById('btnSubmitText').innerText = 'บันทึกผู้ใช้งาน';

            selectRole('it'); // default to it

            const modal = document.getElementById('userModal');
            modal.classList.add('active');
            setTimeout(() => document.getElementById('userName').focus(), 100);
        }

        function openEditUserModal(user) {
            document.getElementById('modalTitleText').innerText = 'แก้ไขข้อมูลผู้ใช้งาน: ' + user.name;
            document.getElementById('modalIcon').innerText = 'edit';
            document.getElementById('modalIcon').style.color = '#2563EB';

            const form = document.getElementById('userForm');
            form.action = `${baseUrl}/${user.id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('userId').value = user.id;

            document.getElementById('userName').value = user.name || '';
            document.getElementById('userEmail').value = user.email || '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = false;
            document.getElementById('passwordRequiredStar').style.display = 'none';
            document.getElementById('passwordHelp').innerText = 'เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน';
            document.getElementById('btnSubmitText').innerText = 'บันทึกการแก้ไข';

            selectRole(user.role || 'it');

            const modal = document.getElementById('userModal');
            modal.classList.add('active');
            setTimeout(() => document.getElementById('userName').focus(), 100);
        }

        function closeUserModal() {
            const modal = document.getElementById('userModal');
            modal.classList.remove('active');
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('.material-symbols-Outlined');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerText = 'visibility_off';
            } else {
                input.type = 'password';
                icon.innerText = 'visibility';
            }
        }

        function confirmDeleteUser(userId, userName) {
            if (!confirm(`คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้งาน "${userName}" ?\n\nการกระทำนี้ไม่สามารถยกเลิกได้!`)) {
                return;
            }

            const deleteForm = document.getElementById('deleteUserForm');
            deleteForm.action = `${baseUrl}/${userId}`;
            deleteForm.submit();
        }

        // Close on clicking outside modal card
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });

        // Close on Esc key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUserModal();
            }
        });

        function showToast(message, isError = false) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast-msg';
            if (isError) {
                toast.style.background = '#991B1B';
            }
            toast.innerHTML = `<span class="material-symbols-Outlined" style="font-size: 18px; color: ${isError ? '#FCA5A5' : '#86EFAC'};">${isError ? 'error' : 'check_circle'}</span><span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }
    </script>
</body>
</html>

