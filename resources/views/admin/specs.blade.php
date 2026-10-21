<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>คุณสมบัติและสเปกเครื่องลูกข่าย - CSH Digital Health</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Google Fonts: Prompt & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <style>
        :root {
            --primary: #0284C7;
            --primary-dark: #0369A1;
            --primary-light: #E0F2FE;
            --accent: #0EA5E9;
            --success: #10B981;
            --success-light: #D1FAE5;
            --danger: #EF4444;
            --danger-light: #FEE2E2;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --surface: #FFFFFF;
            --background: #F8FAFC;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.03);
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        button, input, select, textarea {
            font-family: inherit;
        }

        /* Essential: Force Material Symbols Font & Ligatures */
        .material-symbols-outlined,
        .material-symbols-Outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal !important;
            font-style: normal !important;
            font-size: 20px;
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
            user-select: none;
        }

        /* Top Navigation Bar */
        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
        }

        .brand-area {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
            flex-shrink: 0;
        }

        .brand-logo-img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
        }

        .brand-text h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .brand-text p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.42rem 0.8rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-nav .material-symbols-outlined {
            font-size: 17px !important;
        }

        .btn-nav-installations {
            background: #F1F5F9;
            color: #334155 !important;
            border-color: #CBD5E1;
        }
        .btn-nav-installations:hover {
            background: #E2E8F0;
            color: #0F172A !important;
            transform: translateY(-1px);
        }

        .btn-nav-specs-active {
            background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
            color: #FFFFFF !important;
            border-color: transparent;
            box-shadow: 0 3px 8px rgba(2, 132, 199, 0.35);
            font-weight: 600;
        }

        .btn-nav-download {
            background: #F0FDF4;
            color: #15803D !important;
            border-color: #BBF7D0;
        }
        .btn-nav-download:hover {
            background: #DCFCE7;
            transform: translateY(-1px);
        }

        .btn-nav-users {
            background: #EEF2FF;
            color: #4338CA !important;
            border-color: #C7D2FE;
        }
        .btn-nav-users:hover {
            background: #E0E7FF;
            transform: translateY(-1px);
        }

        .btn-nav-risk {
            background: #FFF1F2;
            color: #BE123C !important;
            border-color: #FECDD3;
        }
        .btn-nav-risk:hover {
            background: #FFE4E6;
            transform: translateY(-1px);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding-left: 0.75rem;
            border-left: 1px solid var(--border);
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.84rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .role-pill {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 9999px;
            letter-spacing: 0.04em;
        }

        .role-admin {
            background: #EEF2FF;
            color: #4F46E5;
            border: 1px solid #C7D2FE;
        }

        .role-it {
            background: #E0F2FE;
            color: #0284C7;
            border: 1px solid #BAE6FD;
        }

        .btn-logout {
            background: #F8FAFC;
            color: #64748B;
            border: 1px solid #E2E8F0;
            padding: 0.38rem 0.7rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            background: #FEE2E2;
            color: #DC2626;
            border-color: #FECACA;
        }

        /* Container */
        .container {
            max-width: 1750px;
            width: 100%;
            margin: 0 auto;
            padding: 1.5rem 2rem;
            flex: 1;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-info p {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-info h2 {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
        }

        .stat-subtext {
            font-size: 0.76rem;
            color: var(--text-muted);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            min-width: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon .material-symbols-outlined {
            font-size: 28px !important;
        }

        .stat-icon.blue { background: #E0F2FE; color: #0284C7; }
        .stat-icon.emerald { background: #D1FAE5; color: #059669; }
        .stat-icon.indigo { background: #EEF2FF; color: #4F46E5; }
        .stat-icon.amber { background: #FEF3C7; color: #D97706; }

        /* Filter Section */
        .filter-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .filter-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .filter-header-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1 1 260px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            width: 100%;
            height: 38px;
            padding: 0 1rem 0 2.4rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            background: #F8FAFC;
            transition: all 0.2s;
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .search-box .material-symbols-outlined {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 18px !important;
            pointer-events: none;
        }

        .filter-select {
            height: 38px;
            padding: 0 2rem 0 0.8rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.83rem;
            background: #F8FAFC url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 0.75rem center/14px;
            appearance: none;
            cursor: pointer;
            color: var(--text-main);
            min-width: 130px;
            transition: all 0.2s;
        }
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #FFFFFF;
        }

        .btn-filter-submit {
            height: 38px;
            padding: 0 1.1rem;
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 0.84rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-filter-submit:hover {
            background: var(--primary-dark);
        }

        .btn-filter-reset {
            height: 38px;
            padding: 0 0.85rem;
            background: #F1F5F9;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.84rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-filter-reset:hover {
            background: #E2E8F0;
            color: var(--text-main);
        }

        .btn-export {
            height: 36px;
            padding: 0 1rem;
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
            border-radius: var(--radius-md);
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-export:hover {
            background: #DCFCE7;
            transform: translateY(-1px);
        }

        /* Table Card */
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
            overflow-x: auto;
            width: 100%;
            -webkit-overflow-scrolling: touch;
        }

        .specs-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.84rem;
            min-width: 950px;
        }

        .specs-table th {
            background: #F8FAFC;
            color: #475569;
            font-weight: 600;
            padding: 0.75rem 0.9rem;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            user-select: none;
        }

        .specs-table td {
            padding: 0.65rem 0.9rem;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #1E293B;
            white-space: nowrap;
        }

        .specs-table tbody tr:hover {
            background-color: #F8FAFC;
        }

        /* Badges & Chips */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-online { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        .badge-offline { background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0; }

        .badge-asset {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
            font-family: 'Inter', monospace;
            font-weight: 600;
            font-size: 0.78rem;
        }

        .badge-dept {
            background: #F8FAFC;
            color: #334155;
            border: 1px solid #CBD5E1;
            font-size: 0.75rem;
        }

        .badge-disk-nvme {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .badge-disk-ssd {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .badge-disk-hdd {
            background: #FFFBEB;
            color: #B45309;
            border: 1px solid #FDE68A;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge-cpu {
            background: #EEF2FF;
            color: #4338CA;
            border: 1px solid #C7D2FE;
            font-weight: 600;
        }

        .badge-ram {
            background: #F5F3FF;
            color: #6D28D9;
            border: 1px solid #DDD6FE;
            font-weight: 600;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
            margin-right: 4px;
        }
        .status-dot.online {
            background-color: #10B981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }
        .status-dot.offline {
            background-color: #94A3B8;
        }

        .badge-win11 {
            background: #F0F9FF;
            color: #0369A1;
            border: 1px solid #BAE6FD;
        }
        .badge-win10 {
            background: #F8FAFC;
            color: #475569;
            border: 1px solid #E2E8F0;
        }

        .badge-office-ok {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        .badge-office-none {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* Hardware info components in table */
        .hw-block {
            display: flex;
            flex-direction: column;
            gap: 2px;
            max-width: 250px;
        }

        .hw-title {
            font-weight: 600;
            color: #0F172A;
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hw-sub {
            font-size: 0.75rem;
            color: #64748B;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .copy-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 2px 7px;
            font-family: 'Inter', monospace;
            font-size: 0.76rem;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s;
        }
        .copy-pill:hover {
            background: #E2E8F0;
            color: #0284C7;
        }

        /* Actions Cell */
        .actions-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
        }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }

        .btn-action-view {
            background: #F0F9FF;
            color: #0284C7;
            border-color: #BAE6FD;
        }
        .btn-action-view:hover {
            background: #0284C7;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-action-edit {
            background: #F8FAFC;
            color: #475569;
            border-color: #CBD5E1;
        }
        .btn-action-edit:hover {
            background: #475569;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-action-vnc {
            background: #EFF6FF;
            color: #2563EB;
            border-color: #BFDBFE;
        }
        .btn-action-vnc:hover {
            background: #2563EB;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        /* Pagination Bar */
        .pagination-container {
            padding: 1.1rem 1.5rem;
            background: #FFFFFF;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Modal Base */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background: #FFFFFF;
            width: 100%;
            max-width: 520px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        .modal-card-large {
            max-width: 780px;
        }

        .modal-header {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #F8FAFC;
        }

        .modal-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 4px;
            transition: all 0.2s;
        }
        .modal-close:hover {
            background: #E2E8F0;
            color: var(--text-main);
        }

        .modal-body {
            padding: 1.75rem;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1rem 1.75rem;
            background: #F8FAFC;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }
        .form-control {
            width: 100%;
            height: 40px;
            padding: 0 0.85rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.88rem;
            background: #FFFFFF;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        /* Detail Grid for Modal */
        .detail-section {
            margin-bottom: 1.5rem;
        }
        .detail-section-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--primary-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
            padding-bottom: 0.4rem;
            border-bottom: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem 1rem;
        }
        .detail-item {
            background: #F8FAFC;
            padding: 0.75rem 0.9rem;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
        }
        .detail-item.full-width {
            grid-column: span 2;
        }
        .detail-item label {
            display: block;
            font-size: 0.74rem;
            color: var(--text-muted);
            margin-bottom: 2px;
        }
        .detail-item .detail-value {
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--text-main);
            word-break: break-word;
        }
        .text-highlight {
            color: #0369A1 !important;
        }

        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
            border-radius: 10px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
        }

        /* Empty state */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-state .material-symbols-outlined {
            font-size: 52px !important;
            color: #CBD5E1;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 1024px) {
            .navbar {
                flex-direction: column;
                align-items: stretch;
                padding: 1rem;
            }
            .container {
                padding: 1rem;
            }
            .nav-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="navbar">
        <a href="{{ route('admin.installations') }}" class="brand-area">
            <img src="{{ asset('csh-logo.jpg') }}" alt="CSH Logo" class="brand-logo-img" onerror="this.style.display='none'; document.getElementById('fallbackBrandIcon').style.display='flex';">
            <div id="fallbackBrandIcon" class="brand-icon" style="display: none;">
                <span class="material-symbols-outlined">memory</span>
            </div>
            <div class="brand-text">
                <h1>CSHOS STAFF</h1>
                <p>ระบบคุณสมบัติและสเปกเครื่องลูกข่าย (Hardware Specifications)</p>
            </div>
        </a>

        <div class="nav-actions">
            <!-- สลับไปยังหน้าภาพรวมการติดตั้ง -->
            <a href="{{ route('admin.installations') }}" class="btn-nav btn-nav-installations" title="ดูภาพรวมการติดตั้งและสถานะออนไลน์">
                <span class="material-symbols-outlined">desktop_windows</span>
                <span>ภาพรวมการติดตั้ง</span>
            </a>

            <!-- หน้านี้: สเปกเครื่อง (Active) -->
            <a href="{{ route('admin.specs') }}" class="btn-nav btn-nav-specs-active" title="คุณสมบัติและสเปกเครื่องลูกข่ายทั้งหมด">
                <span class="material-symbols-outlined">memory</span>
                <span>คุณสมบัติสเปกเครื่อง</span>
            </a>

            <!-- ดาวน์โหลดตัวติดตั้ง -->
            <a href="{{ route('download.shortcut.installer') }}" class="btn-nav btn-nav-download" title="ดาวน์โหลดตัวติดตั้ง v{{ $serverVersion }}">
                <span class="material-symbols-outlined">download</span>
                <span>ดาวน์โหลด (v{{ $serverVersion }})</span>
            </a>

            <!-- หน้าหลักเว็บ -->
            <a href="{{ url('/') }}" target="_blank" class="btn-nav btn-nav-installations" title="เปิดหน้าหลัก CSH Digital Health">
                <span class="material-symbols-outlined">open_in_new</span>
                <span>หน้าหลักเว็บ</span>
            </a>

            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="btn-nav btn-nav-users" title="จัดการผู้ใช้งานระบบ">
                    <span class="material-symbols-outlined">manage_accounts</span>
                    <span>จัดการผู้ใช้</span>
                </a>

                <a href="{{ route('admin.risk-rules.index') }}" class="btn-nav btn-nav-risk" title="เงื่อนไขตรวจสอบโปรแกรมเสี่ยง">
                    <span class="material-symbols-outlined">shield_lock</span>
                    <span>โปรแกรมเสี่ยง</span>
                </a>
            @endif

            <div class="user-menu">
                <div class="user-badge" title="ผู้ใช้งานปัจจุบัน">
                    <span class="material-symbols-outlined" style="font-size: 19px; color: var(--primary);">account_circle</span>
                    <span>{{ Auth::user()->fullname ?: (Auth::user()->name ?? 'ผู้ใช้งาน') }}</span>
                    @if(Auth::user()->isAdmin())
                        <span class="role-pill role-admin">ADMIN</span>
                    @else
                        <span class="role-pill role-it">IT</span>
                    @endif
                </div>

                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout" title="ออกจากระบบ">
                        <span class="material-symbols-outlined" style="font-size: 16px;">logout</span>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">

        @if(session('success'))
            <div class="alert-success">
                <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Summary KPI Cards -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <p>เครื่องที่ลงทะเบียนสเปก</p>
                    <h2>{{ number_format($stats['total_devices']) }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">เครื่อง</span></h2>
                    <div class="stat-subtext">
                        <span class="material-symbols-outlined" style="font-size: 15px; color: #0284C7;">devices</span>
                        <span>เก็บข้อมูลฮาร์ดแวร์ครบถ้วน</span>
                    </div>
                </div>
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">computer</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>สัดส่วน Storage (SSD vs HDD)</p>
                    <h2>{{ number_format($stats['ssd_count']) }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">SSD</span></h2>
                    <div class="stat-subtext">
                        <span class="material-symbols-outlined" style="font-size: 15px; color: #059669;">speed</span>
                        <span>ใช้ SSD {{ $stats['total_devices'] > 0 ? round(($stats['ssd_count'] / $stats['total_devices']) * 100) : 0 }}% (HDD จานหมุน {{ number_format($stats['hdd_only_count']) }} เครื่อง)</span>
                    </div>
                </div>
                <div class="stat-icon emerald">
                    <span class="material-symbols-outlined">hard_drive</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>หน่วยความจำ RAM (>= 8 GB)</p>
                    <h2>{{ number_format($stats['ram_8plus_count']) }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">เครื่อง</span></h2>
                    <div class="stat-subtext">
                        @if($stats['ram_low_count'] > 0)
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #D97706;">warning</span>
                            <span style="color: #B45309; font-weight: 600;">ต่ำกว่า 8 GB อยู่ {{ number_format($stats['ram_low_count']) }} เครื่อง</span>
                        @else
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #059669;">check_circle</span>
                            <span>สเปก RAM ผ่านเกณฑ์ทั้งหมด</span>
                        @endif
                    </div>
                </div>
                <div class="stat-icon indigo">
                    <span class="material-symbols-outlined">memory</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>ระบบปฏิบัติการ Windows 11</p>
                    <h2>{{ number_format($stats['win11_count']) }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">เครื่อง</span></h2>
                    <div class="stat-subtext">
                        <span class="material-symbols-outlined" style="font-size: 15px; color: #0284C7;">grid_view</span>
                        <span>Win 11 ({{ $stats['win11_count'] }}) | Win 10 ({{ $stats['win10_count'] }})</span>
                    </div>
                </div>
                <div class="stat-icon amber">
                    <span class="material-symbols-outlined">desktop_windows</span>
                </div>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="filter-section">
            <div class="filter-header-bar">
                <div class="filter-header-title">
                    <span class="material-symbols-outlined" style="color: var(--primary); font-size: 21px;">tune</span>
                    <span>ค้นหาและกรองสเปกเครื่องลูกข่าย</span>
                </div>
                <a href="{{ route('admin.specs.export') }}" class="btn-export" title="ส่งออกสเปกเครื่องทั้งหมดเป็นไฟล์ CSV (เปิดใน Excel ภาษาไทยไม่เพี้ยน)">
                    <span class="material-symbols-outlined" style="font-size: 18px;">download</span>
                    <span>ส่งออกสเปก (CSV)</span>
                </a>
            </div>

            <form action="{{ route('admin.specs') }}" method="GET" class="filter-form">
                <!-- Search Input -->
                <div class="search-box">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="ค้นหา: ชื่อเครื่อง, IP, CPU, RAM, ดิสก์, แผนก, รหัสครุภัณฑ์...">
                </div>

                <!-- Department Filter -->
                <select name="department" class="filter-select">
                    <option value="">ทุกแผนก/หน่วยงาน</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>

                <!-- RAM Filter -->
                <select name="ram" class="filter-select">
                    <option value="">ขนาด RAM ทั้งหมด</option>
                    <option value="4" {{ request('ram') == '4' ? 'selected' : '' }}>RAM 4 GB</option>
                    <option value="8" {{ request('ram') == '8' ? 'selected' : '' }}>RAM 8 GB</option>
                    <option value="16" {{ request('ram') == '16' ? 'selected' : '' }}>RAM 16 GB</option>
                    <option value="32+" {{ request('ram') == '32+' ? 'selected' : '' }}>RAM 32 GB ขึ้นไป</option>
                    <option value="low" {{ request('ram') == 'low' ? 'selected' : '' }}>⚠️ ต่ำกว่า 8 GB (ควรเพิ่ม)</option>
                </select>

                <!-- Disk Type Filter -->
                <select name="disk_type" class="filter-select">
                    <option value="">ประเภทดิสก์ทั้งหมด</option>
                    <option value="nvme" {{ request('disk_type') == 'nvme' ? 'selected' : '' }}>SSD NVMe M.2 (เร็วสุด)</option>
                    <option value="ssd" {{ request('disk_type') == 'ssd' ? 'selected' : '' }}>SSD ทั้งหมด</option>
                    <option value="hdd" {{ request('disk_type') == 'hdd' ? 'selected' : '' }}>HDD จานหมุน (ช้า)</option>
                </select>

                <!-- OS Filter -->
                <select name="os" class="filter-select">
                    <option value="">ระบบปฏิบัติการทั้งหมด</option>
                    <option value="win11" {{ request('os') == 'win11' ? 'selected' : '' }}>Windows 11</option>
                    <option value="win10" {{ request('os') == 'win10' ? 'selected' : '' }}>Windows 10</option>
                    <option value="other" {{ request('os') == 'other' ? 'selected' : '' }}>รุ่นอื่นๆ</option>
                </select>

                <!-- Office Filter -->
                <select name="office" class="filter-select">
                    <option value="">โปรแกรม Office ทั้งหมด</option>
                    <option value="installed" {{ request('office') == 'installed' ? 'selected' : '' }}>มี Office</option>
                    <option value="not_installed" {{ request('office') == 'not_installed' ? 'selected' : '' }}>ไม่มี Office</option>
                    <option value="2024" {{ request('office') == '2024' ? 'selected' : '' }}>Office 2024</option>
                    <option value="2021" {{ request('office') == '2021' ? 'selected' : '' }}>Office 2021</option>
                    <option value="2019" {{ request('office') == '2019' ? 'selected' : '' }}>Office 2019</option>
                    <option value="2016" {{ request('office') == '2016' ? 'selected' : '' }}>Office 2016</option>
                </select>

                <!-- Online Status -->
                <select name="status" class="filter-select">
                    <option value="">สถานะทั้งหมด</option>
                    <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>ออนไลน์ (Online)</option>
                    <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>ออฟไลน์ (Offline)</option>
                </select>

                <button type="submit" class="btn-filter-submit">
                    <span class="material-symbols-outlined" style="font-size: 18px;">filter_alt</span>
                    <span>กรอง</span>
                </button>

                @if(request()->hasAny(['search', 'department', 'ram', 'disk_type', 'os', 'office', 'status']))
                    <a href="{{ route('admin.specs') }}" class="btn-filter-reset" title="ล้างตัวกรองทั้งหมด">
                        <span class="material-symbols-outlined" style="font-size: 17px;">restart_alt</span>
                        <span>ล้างค่า</span>
                    </a>
                @endif
            </form>
        </section>

        <!-- Hardware Specifications Table -->
        <section class="table-card">
            <div class="table-responsive">
                <table class="specs-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th style="width: 140px;">แผนก</th>
                            <th style="width: 170px;">ชื่อเครื่อง</th>
                            <th style="min-width: 150px;">CPU</th>
                            <th style="min-width: 120px;">RAM</th>
                            <th style="min-width: 140px;">Disk</th>
                            <th style="min-width: 150px;">OS</th>
                            <th style="min-width: 130px;">Microsoft</th>
                            <th style="width: 90px; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installations as $idx => $item)
                            <tr>
                                <!-- Index -->
                                <td style="text-align: center; color: var(--text-muted); font-size: 0.82rem; font-weight: 500;">
                                    {{ $installations->firstItem() + $idx }}
                                </td>

                                <!-- แผนก -->
                                <td>
                                    @if($item->department)
                                        <span class="badge badge-dept" title="แผนก/หน่วยงาน: {{ $item->department }}">
                                            <span class="material-symbols-outlined" style="font-size: 13px; color: var(--primary);">domain</span>
                                            <span>{{ $item->department }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8; font-size: 0.8rem;">- ไม่ระบุ -</span>
                                    @endif
                                </td>

                                <!-- ชื่อเครื่อง -->
                                <td>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;" title="IP: {{ $item->ip_address }} | ผู้ใช้: {{ $item->device_name ?: '-' }} | สถานะ: {{ $item->computed_online ? 'ออนไลน์' : 'ออฟไลน์' }}">
                                        <span class="status-dot {{ $item->computed_online ? 'online' : 'offline' }}" title="{{ $item->computed_online ? 'ออนไลน์' : 'ออฟไลน์' }}"></span>
                                        <span style="font-weight: 600; color: #0F172A;">{{ $item->hostname ?: '-' }}</span>
                                    </div>
                                </td>

                                <!-- CPU -->
                                <td>
                                    @if($item->short_cpu && $item->short_cpu !== '-')
                                        <span class="badge badge-cpu" title="{{ $item->cpu }}">
                                            <span class="material-symbols-outlined" style="font-size: 13px; color: #4F46E5;">developer_board</span>
                                            <span>{{ $item->short_cpu }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- RAM -->
                                <td>
                                    @if($item->short_ram && $item->short_ram !== '-')
                                        <span class="badge badge-ram" title="{{ $item->ram }}">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">memory</span>
                                            <span>{{ $item->short_ram }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- Disk -->
                                <td>
                                    @if($item->short_disk && $item->short_disk !== '-')
                                        @php
                                            $isNvme = str_contains($item->short_disk, 'NVMe');
                                            $isSsd = str_contains($item->short_disk, 'SSD');
                                            $diskBadgeClass = $isNvme ? 'badge-disk-nvme' : ($isSsd ? 'badge-disk-ssd' : 'badge-disk-hdd');
                                        @endphp
                                        <span class="badge {{ $diskBadgeClass }}" title="{{ $item->disk }}">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">hard_drive</span>
                                            <span>{{ $item->short_disk }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- OS -->
                                <td>
                                    @if($item->short_os && $item->short_os !== '-')
                                        @php
                                            $isWin11 = str_contains($item->short_os, 'Windows 11');
                                            $isWin10 = str_contains($item->short_os, 'Windows 10');
                                            $osBadgeClass = $isWin11 ? 'badge-win11' : ($isWin10 ? 'badge-win10' : '');
                                        @endphp
                                        <span class="badge {{ $osBadgeClass }}" title="{{ $item->os_version ?: $item->os }}">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">grid_view</span>
                                            <span>{{ $item->short_os }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- Microsoft -->
                                <td>
                                    @if($item->short_office && $item->short_office !== 'ไม่มี')
                                        <span class="badge badge-office-ok" title="{{ $item->office_version }}">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">description</span>
                                            <span>{{ $item->short_office }}</span>
                                        </span>
                                    @else
                                        <span class="badge badge-office-none" title="ไม่ได้ติดตั้ง / ไม่พบข้อมูล">
                                            <span>ไม่มี</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- จัดการ -->
                                <td style="text-align: center;">
                                    <div class="actions-cell">
                                        <button type="button" class="btn-action-icon btn-action-view" 
                                                onclick="openDetailsModal({{ json_encode($item) }})" 
                                                title="ดูสเปกเต็ม (CPU, RAM, Disk, OS, IP, MAC)">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                        </button>
                                        <button type="button" class="btn-action-icon btn-action-edit" 
                                                onclick="openEditModal({{ json_encode($item) }})" 
                                                title="แก้ไขข้อมูล (แผนก, รหัสครุภัณฑ์, หมายเหตุ)">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                        </button>
                                        @if($item->remote_ready && $item->ip_address)
                                            <a href="{{ route('admin.installations.vnc', $item->id) }}" 
                                               class="btn-action-icon btn-action-vnc" 
                                               title="รีโมท VNC (Port 5900)">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">desktop_windows</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <span class="material-symbols-outlined">devices_off</span>
                                        <h3>ไม่พบข้อมูลสเปกเครื่องลูกข่ายตามเงื่อนไข</h3>
                                        <p style="font-size: 0.85rem; margin-top: 4px;">ลองเปลี่ยนคำค้นหา หรือกดปุ่ม "ล้างค่า" เพื่อดูเครื่องทั้งหมด</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if($installations->hasPages())
                <div class="pagination-container">
                    <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                        แสดง <strong style="color: var(--text-main);">{{ $installations->firstItem() }}</strong> ถึง <strong style="color: var(--text-main);">{{ $installations->lastItem() }}</strong> จากทั้งหมด <strong style="color: var(--text-main);">{{ $installations->total() }}</strong> รายการ
                    </div>
                    <div>
                        {{ $installations->links() }}
                    </div>
                </div>
            @endif
        </section>

    </main>

    <!-- Modal: Details Spec Sheet -->
    <div class="modal-overlay" id="detailsModal">
        <div class="modal-card modal-card-large">
            <div class="modal-header">
                <h3>
                    <span class="material-symbols-outlined" style="color: var(--primary); font-size: 22px;">memory</span>
                    <span id="dtHostname">รายละเอียดคุณสมบัติสเปกเครื่อง</span>
                </h3>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="dtStatusBadge" class="badge"></span>
                    <button type="button" class="modal-close" onclick="closeDetailsModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <!-- Section 1: ข้อมูลฮาร์ดแวร์ -->
                <div class="detail-section">
                    <div class="detail-section-title">
                        <span class="material-symbols-outlined" style="font-size: 18px;">developer_board</span>
                        <span>สเปกฮาร์ดแวร์ (Hardware Specifications)</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item full-width">
                            <label>หน่วยประมวลผล (CPU)</label>
                            <div id="dtCpu" class="detail-value text-highlight">-</div>
                        </div>
                        <div class="detail-item">
                            <label>หน่วยความจำ (RAM)</label>
                            <div id="dtRam" class="detail-value text-highlight">-</div>
                        </div>
                        <div class="detail-item">
                            <label>พื้นที่จัดเก็บข้อมูล (Hard Disk / SSD)</label>
                            <div id="dtDisk" class="detail-value text-highlight">-</div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: ข้อมูลซอฟต์แวร์ระบบ -->
                <div class="detail-section">
                    <div class="detail-section-title">
                        <span class="material-symbols-outlined" style="font-size: 18px;">terminal</span>
                        <span>ระบบปฏิบัติการ & ซอฟต์แวร์สำนักงาน (System & Software)</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>ระบบปฏิบัติการ (OS & Version)</label>
                            <div id="dtOs" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>Microsoft Office</label>
                            <div id="dtOffice" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>AnyDesk ID</label>
                            <div id="dtAnydesk" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ระยะเวลาเปิดเครื่องต่อเนื่อง (Uptime)</label>
                            <div id="dtUptime" class="detail-value">-</div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: เครือข่ายและที่ตั้ง -->
                <div class="detail-section" style="margin-bottom: 0;">
                    <div class="detail-section-title">
                        <span class="material-symbols-outlined" style="font-size: 18px;">lan</span>
                        <span>ข้อมูลเครือข่าย & ข้อมูลครุภัณฑ์ (Network & Asset)</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>หมายเลข IP Address</label>
                            <div id="dtIp" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>MAC Address</label>
                            <div id="dtMac" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>รหัสครุภัณฑ์ (Asset Code)</label>
                            <div id="dtAssetCode" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>แผนก / หน่วยงาน</label>
                            <div id="dtDepartment" class="detail-value">-</div>
                        </div>
                        <div class="detail-item full-width">
                            <label>หมายเหตุ</label>
                            <div id="dtNotes" class="detail-value" style="font-weight: normal; color: #475569;">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-filter-reset" onclick="closeDetailsModal()">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- Modal: Quick Edit Info -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>
                    <span class="material-symbols-outlined" style="color: var(--primary); font-size: 20px;">edit_note</span>
                    <span>แก้ไขข้อมูลเครื่องคอมพิวเตอร์</span>
                </h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>รหัสครุภัณฑ์ (Asset Code)</label>
                        <input type="text" name="asset_code" id="editAssetCode" class="form-control" placeholder="เช่น COM-67-001">
                    </div>

                    <div class="form-group">
                        <label>แผนก / หน่วยงาน</label>
                        <input type="text" name="department" id="editDepartment" class="form-control" placeholder="เช่น OPD, ER, ห้องยา, ห้องบัตร">
                    </div>

                    <div class="form-group">
                        <label>ชื่อเครื่อง / ผู้ใช้งาน (Device / User Name)</label>
                        <input type="text" name="device_name" id="editDeviceName" class="form-control" placeholder="เช่น พยาบาลคัดกรอง 1">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label>หมายเหตุ (Notes)</label>
                        <textarea name="notes" id="editNotes" class="form-control" style="height: 80px; padding: 0.6rem 0.85rem;" placeholder="บันทึกข้อมูลเพิ่มเติม..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-filter-reset" onclick="closeEditModal()">ยกเลิก</button>
                    <button type="submit" class="btn-filter-submit">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        <span>บันทึกข้อมูล</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function openDetailsModal(item) {
            document.getElementById('dtHostname').textContent = item.hostname || 'ไม่ระบุชื่อเครื่อง';
            
            const isOnline = item.computed_online;
            const badge = document.getElementById('dtStatusBadge');
            badge.className = 'badge ' + (isOnline ? 'badge-online' : 'badge-offline');
            badge.innerHTML = `<span class="material-symbols-outlined" style="font-size: 13px;">${isOnline ? 'check_circle' : 'power_off'}</span> ${isOnline ? 'ออนไลน์' : 'ออฟไลน์'}`;

            document.getElementById('dtCpu').textContent = item.cpu || '- ไม่พบข้อมูล -';
            document.getElementById('dtRam').textContent = item.ram || '- ไม่พบข้อมูล -';
            document.getElementById('dtDisk').textContent = item.disk || '- ไม่พบข้อมูล -';
            document.getElementById('dtOs').textContent = item.os_version || (item.os || '-');
            document.getElementById('dtOffice').textContent = item.office_version || 'ไม่ได้ติดตั้ง / ไม่พบข้อมูล';
            document.getElementById('dtAnydesk').textContent = item.anydesk_id ? item.anydesk_id : (item.anydesk_installed ? 'ติดตั้งแล้ว (ไม่พบ ID)' : 'ไม่ได้ติดตั้ง');
            document.getElementById('dtUptime').textContent = item.dynamic_uptime || (item.uptime_text || '-');
            document.getElementById('dtIp').textContent = item.ip_address || '-';
            document.getElementById('dtMac').textContent = item.mac_address || '-';
            document.getElementById('dtAssetCode').textContent = item.asset_code || '- ไม่ระบุ -';
            document.getElementById('dtDepartment').textContent = item.department || '- ไม่ระบุ -';
            document.getElementById('dtNotes').textContent = item.notes || '-';

            document.getElementById('detailsModal').classList.add('active');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }

        function openEditModal(item) {
            const form = document.getElementById('editForm');
            form.action = `/admin/installations/${item.id}`;

            document.getElementById('editAssetCode').value = item.asset_code || '';
            document.getElementById('editDepartment').value = item.department || '';
            document.getElementById('editDeviceName').value = item.device_name || '';
            document.getElementById('editNotes').value = item.notes || '';

            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function copyToClipboard(text, elem) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = elem.innerHTML;
                elem.innerHTML = '<span style="color: #059669; font-weight: 600;">คัดลอกแล้ว!</span>';
                setTimeout(() => {
                    elem.innerHTML = originalHtml;
                }, 1500);
            }).catch(err => {
                console.error('Copy failed: ', err);
            });
        }

        // Close modal on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailsModal();
                closeEditModal();
            }
        });
    </script>
</body>
</html>
