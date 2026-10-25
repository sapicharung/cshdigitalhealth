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
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 1.15rem 1.3rem;
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
        .stat-icon.rose { background: #FFE4E6; color: #E11D48; }
        .stat-icon.purple { background: #F3E8FF; color: #7E22CE; }

        /* Filter Section */
        /* Filter Section - Compact 1-Row Toolbar */
        .filter-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.55rem 0.85rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
            position: relative;
            z-index: 50;
            overflow: visible;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-wrap: wrap;
            position: relative;
            overflow: visible;
        }

        .search-box {
            flex: 1 1 170px;
            min-width: 130px;
            max-width: 250px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            width: 100%;
            height: 33px;
            padding: 0 0.75rem 0 2rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.8rem;
            background: #F8FAFC;
            transition: all 0.2s;
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: #FFFFFF;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        .search-box .material-symbols-outlined {
            position: absolute;
            left: 0.55rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px !important;
            pointer-events: none;
        }

        .filter-select {
            height: 33px;
            padding: 0 1.5rem 0 0.6rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.79rem;
            background: #F8FAFC url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 0.45rem center/12px;
            appearance: none;
            cursor: pointer;
            color: var(--text-main);
            min-width: 100px;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #FFFFFF;
        }

        .btn-filter-submit {
            height: 33px;
            padding: 0 0.8rem;
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.8rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-filter-submit:hover {
            background: var(--primary-dark);
        }

        .btn-filter-reset {
            height: 33px;
            padding: 0 0.7rem;
            background: #F1F5F9;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-filter-reset:hover {
            background: #E2E8F0;
            color: var(--text-main);
        }

        .btn-export {
            height: 33px;
            padding: 0 0.8rem;
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
            margin-left: auto;
        }
        .btn-export:hover {
            background: #DCFCE7;
            transform: translateY(-1px);
        }

        /* Multi-Select Dropdown with Checkboxes */
        .filter-dropdown-multiselect {
            position: relative;
            flex-shrink: 0;
        }

        .filter-dropdown-btn {
            height: 33px;
            padding: 0 0.65rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.79rem;
            background: #F8FAFC;
            cursor: pointer;
            color: var(--text-main);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            white-space: nowrap;
            user-select: none;
        }
        .filter-dropdown-btn:hover {
            background-color: #FFFFFF;
            border-color: #CBD5E1;
        }
        .filter-dropdown-btn.active {
            border-color: var(--primary);
            background-color: #FFFFFF;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }
        .filter-dropdown-btn.has-value {
            background-color: #EFF6FF;
            color: #1D4ED8;
            border-color: #BFDBFE;
            font-weight: 600;
        }
        .filter-dropdown-btn .dropdown-arrow {
            font-size: 15px !important;
            color: var(--text-muted);
            transition: transform 0.2s;
        }
        .filter-dropdown-btn.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .filter-dropdown-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            z-index: 9999;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.18), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
            min-width: 200px;
            max-height: 320px;
            overflow-y: auto;
            padding: 0.4rem 0 0 0;
            display: none;
        }
        .filter-dropdown-menu.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .filter-dropdown-footer {
            padding: 0.45rem 0.75rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            background: #F8FAFC;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            margin-top: 4px;
        }
        .btn-multi-apply {
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: 4px;
            padding: 3px 12px;
            font-size: 0.76rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-multi-apply:hover {
            background: var(--primary-dark);
        }

        .filter-dropdown-header {
            padding: 0.3rem 0.75rem 0.45rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .btn-clear-multi {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 0.73rem;
            padding: 0;
            text-decoration: underline;
        }

        .multi-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            color: var(--text-main);
            cursor: pointer;
            transition: background-color 0.15s;
            user-select: none;
        }
        .multi-item:hover {
            background-color: #F1F5F9;
        }
        .multi-item input[type="checkbox"] {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: var(--primary);
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
            font-size: 0.82rem;
            min-width: 900px;
        }

        .specs-table th {
            background: #F8FAFC;
            color: #475569;
            font-weight: 600;
            padding: 0.55rem 0.75rem;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            user-select: none;
            font-size: 0.78rem;
        }

        th.th-sortable {
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease, color 0.15s ease;
        }

        th.th-sortable:hover {
            background: #EFF6FF;
            color: #1D4ED8;
        }

        th.th-sortable .th-content {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }

        th.th-sortable .sort-icon {
            font-size: 14px;
            color: #94A3B8;
            transition: color 0.15s ease;
            vertical-align: middle;
            user-select: none;
        }

        th.th-sortable:hover .sort-icon {
            color: #2563EB;
        }

        th.th-sortable.sorted-desc,
        th.th-sortable.sorted-asc {
            background: #EEF2FF;
            color: #3730A3;
        }

        th.th-sortable.sorted-desc .sort-icon,
        th.th-sortable.sorted-asc .sort-icon {
            color: #4F46E5;
        }

        /* Toast Notification */
        .toast-notify {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0F172A;
            color: #FFFFFF;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            z-index: 99999;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }

        .toast-notify.show {
            opacity: 1;
            transform: translateY(0);
        }

        .specs-table td {
            padding: 0.38rem 0.75rem;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #1E293B;
            white-space: nowrap;
            line-height: 1.3;
        }

        .specs-table tbody tr:hover {
            background-color: #F8FAFC;
        }

        /* Badges & Chips */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 1.5px 7px;
            border-radius: 5px;
            font-size: 0.73rem;
            font-weight: 600;
            white-space: nowrap;
            line-height: 1.35;
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
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
            margin-right: 3px;
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

        /* Performance Badges (5 Tiers) */
        .badge-perf-highest {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
            font-weight: 700;
        }
        .badge-perf-high {
            background: #F0F9FF;
            color: #0369A1;
            border: 1px solid #BAE6FD;
            font-weight: 700;
        }
        .badge-perf-fair {
            background: #F0FDFA;
            color: #0F766E;
            border: 1px solid #99F6E4;
            font-weight: 700;
        }
        .badge-perf-low {
            background: #FFFBEB;
            color: #B45309;
            border: 1px solid #FDE68A;
            font-weight: 700;
        }
        .badge-perf-lowest {
            background: #FEF2F2;
            color: #B91C1C;
            border: 1px solid #FECACA;
            font-weight: 700;
        }

        .badge-cores {
            background: #F8FAFC;
            color: #334155;
            border: 1px solid #CBD5E1;
            font-family: 'Inter', monospace;
            font-weight: 600;
            font-size: 0.72rem;
        }

        /* Speed & Separated RAM Badges */
        .badge-speed {
            background: #F0F9FF;
            color: #0369A1;
            border: 1px solid #BAE6FD;
            font-family: 'Inter', monospace;
            font-weight: 600;
            font-size: 0.72rem;
        }
        .badge-ram-size {
            background: #F5F3FF;
            color: #6D28D9;
            border: 1px solid #DDD6FE;
            font-family: 'Inter', monospace;
            font-weight: 700;
            font-size: 0.72rem;
        }
        .badge-ram-ddr5 {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
            font-weight: 700;
            font-size: 0.72rem;
        }
        .badge-ram-ddr4 {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
            font-weight: 700;
            font-size: 0.72rem;
        }
        .badge-ram-ddr3 {
            background: #FFFBEB;
            color: #B45309;
            border: 1px solid #FDE68A;
            font-weight: 700;
            font-size: 0.72rem;
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
            gap: 4px;
            justify-content: center;
        }

        .btn-action-icon {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }

        .btn-action-icon .material-symbols-outlined {
            font-size: 14px !important;
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

        /* Clickable Performance, Storage, RAM & Windows Stat Cards */
        .stat-card-clickable {
            cursor: pointer;
            position: relative;
            user-select: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .stat-card-clickable:hover {
            transform: translateY(-2px);
        }

        .stat-card-total:hover {
            border-color: #38BDF8 !important;
            box-shadow: 0 8px 24px rgba(2, 132, 199, 0.12);
        }
        .stat-card-total:active {
            transform: scale(0.98);
        }

        .stat-card-perf:hover {
            border-color: #C084FC !important;
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.12);
        }
        .stat-card-perf.is-expanded {
            border-color: #A855F7 !important;
            box-shadow: 0 0 0 1px #A855F7, 0 8px 25px rgba(168, 85, 247, 0.16) !important;
            background: linear-gradient(135deg, #FAF5FF 0%, #FFFFFF 100%) !important;
        }

        .stat-card-storage:hover {
            border-color: #34D399 !important;
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.12);
        }
        .stat-card-storage.is-expanded {
            border-color: #059669 !important;
            box-shadow: 0 0 0 1px #059669, 0 8px 25px rgba(5, 150, 105, 0.16) !important;
            background: linear-gradient(135deg, #ECFDF5 0%, #FFFFFF 100%) !important;
        }

        .stat-card-ram:hover {
            border-color: #818CF8 !important;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.12);
        }
        .stat-card-ram.is-expanded {
            border-color: #4F46E5 !important;
            box-shadow: 0 0 0 1px #4F46E5, 0 8px 25px rgba(79, 70, 229, 0.16) !important;
            background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 100%) !important;
        }

        .stat-card-windows:hover {
            border-color: #38BDF8 !important;
            box-shadow: 0 8px 24px rgba(2, 132, 199, 0.12);
        }
        .stat-card-windows.is-expanded {
            border-color: #0284C7 !important;
            box-shadow: 0 0 0 1px #0284C7, 0 8px 25px rgba(2, 132, 199, 0.16) !important;
            background: linear-gradient(135deg, #F0F9FF 0%, #FFFFFF 100%) !important;
        }

        .stat-card-toggle-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            transition: all 0.2s;
        }
        .stat-card-toggle-pill .expand-arrow {
            font-size: 16px;
            transition: transform 0.25s ease;
        }
        .stat-card-clickable.is-expanded .stat-card-toggle-pill .expand-arrow {
            transform: rotate(180deg);
        }

        .stat-card-toggle-pill.pill-purple {
            color: #7E22CE;
            background: #F3E8FF;
            border: 1px solid #E9D5FF;
        }
        .stat-card-clickable.is-expanded .stat-card-toggle-pill.pill-purple {
            background: #7E22CE;
            color: #FFFFFF;
            border-color: #7E22CE;
        }

        .stat-card-toggle-pill.pill-emerald {
            color: #059669;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
        }
        .stat-card-clickable.is-expanded .stat-card-toggle-pill.pill-emerald {
            background: #059669;
            color: #FFFFFF;
            border-color: #059669;
        }

        .stat-card-toggle-pill.pill-indigo {
            color: #4F46E5;
            background: #EEF2FF;
            border: 1px solid #C7D2FE;
        }
        .stat-card-clickable.is-expanded .stat-card-toggle-pill.pill-indigo {
            background: #4F46E5;
            color: #FFFFFF;
            border-color: #4F46E5;
        }

        .stat-card-toggle-pill.pill-sky {
            color: #0284C7;
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
        }
        .stat-card-clickable.is-expanded .stat-card-toggle-pill.pill-sky {
            background: #0284C7;
            color: #FFFFFF;
            border-color: #0284C7;
        }

        /* Sub-cards Container */
        .perf-subcards-wrapper {
            margin-bottom: 1.25rem;
            animation: perfSlideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes perfSlideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .perf-subcards-box {
            background: var(--surface);
            border: 1px solid #E9D5FF;
            border-radius: var(--radius-lg);
            padding: 1.15rem 1.3rem;
            box-shadow: 0 4px 20px rgba(126, 34, 206, 0.06);
            background: linear-gradient(180deg, #FAF5FF 0%, #FFFFFF 65px);
        }
        .perf-subcards-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.95rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .perf-subcards-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .perf-subcards-guide {
            font-size: 0.78rem;
            font-weight: 400;
            color: var(--text-muted);
            margin-left: 4px;
        }
        .perf-subcards-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-subcard-clear {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #7E22CE;
            background: #F3E8FF;
            border: 1px solid #E9D5FF;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-subcard-clear:hover {
            background: #E9D5FF;
            color: #6B21A8;
        }
        .btn-subcard-close {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 50%;
            transition: all 0.2s;
        }
        .btn-subcard-close:hover {
            background: #E2E8F0;
            color: var(--text-main);
        }
        .perf-subcards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.85rem;
        }
        @media (max-width: 1200px) {
            .perf-subcards-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        .perf-subcard {
            background: #FFFFFF;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 0.9rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            user-select: none;
        }
        .perf-subcard:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            border-color: var(--tier-theme, #7E22CE);
        }
        .perf-subcard.is-selected {
            border-color: var(--tier-theme, #7E22CE) !important;
            background: var(--tier-bg, #FAF5FF) !important;
            box-shadow: 0 0 0 2px var(--tier-theme, #7E22CE), 0 6px 18px rgba(0, 0, 0, 0.08) !important;
        }
        .perf-subcard-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.55rem;
        }
        .perf-subcard-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.76rem;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .perf-subcard-badge .material-symbols-outlined {
            font-size: 15px;
        }
        .perf-active-indicator {
            display: inline-flex;
            align-items: center;
            color: var(--tier-theme, #059669);
        }
        .perf-subcard-body {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 0.45rem;
        }
        .perf-subcard-count {
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1;
        }
        .perf-subcard-unit {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-left: 2px;
        }
        .perf-subcard-pct {
            font-size: 0.74rem;
            color: var(--text-muted);
            font-weight: 600;
        }
        .perf-subcard-bar-wrapper {
            width: 100%;
            height: 5px;
            background: #F1F5F9;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 0.55rem;
        }
        .perf-subcard-bar-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.4s ease;
        }
        .perf-subcard-spec {
            font-size: 0.72rem;
            color: var(--text-muted);
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
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

        @php
            $rawPerf = request('perf', []);
            $selectedPerfs = is_array($rawPerf) ? $rawPerf : ($rawPerf ? explode(',', (string)$rawPerf) : []);
            $perfCount = count($selectedPerfs);

            $rawRam = request('ram', []);
            $selectedRams = is_array($rawRam) ? $rawRam : ($rawRam ? explode(',', (string)$rawRam) : []);
            $ramCount = count($selectedRams);

            $rawRamType = request('ram_type', []);
            $selectedRamTypes = is_array($rawRamType) ? $rawRamType : ($rawRamType ? explode(',', (string)$rawRamType) : []);
            $ramTypeCount = count($selectedRamTypes);

            $hasStorageFilter = (bool) request('disk_type');
            $hasWindowsFilter = (bool) request('os');
        @endphp

        <!-- Summary KPI Cards -->
        <section class="stats-grid">
            <!-- Total Devices Registered Card (Click to Reset & Show All) -->
            <div class="stat-card stat-card-clickable stat-card-total" 
                 onclick="resetAllFiltersAndShowAll()" 
                 title="คลิกเพื่อล้างตัวกรองและแสดงเครื่องทั้งหมด ({{ number_format($stats['total_devices']) }} เครื่อง)">
                <div class="stat-info">
                    <p style="display: flex; align-items: center; justify-content: space-between;">
                        <span>เครื่องที่ลงทะเบียนสเปก</span>
                        @if(request()->hasAny(['search', 'department', 'perf', 'ram', 'ram_type', 'disk_type', 'os', 'office', 'status']))
                            <span style="font-size: 0.72rem; color: #0284C7; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                <span class="material-symbols-outlined" style="font-size: 13px;">restart_alt</span> แสดงทั้งหมด
                            </span>
                        @endif
                    </p>
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

            <!-- Performance Overview Card (Clickable to Expand Sub-Cards) -->
            <div class="stat-card stat-card-clickable stat-card-perf {{ $perfCount > 0 ? 'is-expanded' : '' }}" 
                 id="perfMainCard" 
                 onclick="toggleSubCardPanel('perf')" 
                 title="คลิกเพื่อเปิด/ปิดรายละเอียดระดับประสิทธิภาพ">
                <div class="stat-info">
                    <p style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span>ประสิทธิภาพ (ดี - สูงมาก)</span>
                        <span class="stat-card-toggle-pill pill-purple" title="คลิกเพื่อเปิด/ปิด">
                            <span class="material-symbols-outlined expand-arrow" id="perfToggleIcon">{{ $perfCount > 0 ? 'expand_less' : 'expand_more' }}</span>
                        </span>
                    </p>
                    <h2>{{ number_format($stats['good_perf_count']) }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">เครื่อง</span></h2>
                    <div class="stat-subtext" title="สูงมาก {{ $stats['perf_counts']['highest'] }} | สูง {{ $stats['perf_counts']['high'] }} | พอใช้ {{ $stats['perf_counts']['fair'] }} | ต่ำ {{ $stats['perf_counts']['low'] }} | ต่ำมาก {{ $stats['perf_counts']['lowest'] }}">
                        <span class="material-symbols-outlined" style="font-size: 15px; color: #7E22CE;">rocket_launch</span>
                        <span>สูง/มาก {{ $stats['good_perf_count'] }} | พอใช้ {{ $stats['perf_counts']['fair'] }} | ต่ำ {{ $stats['perf_counts']['low'] + $stats['perf_counts']['lowest'] }}</span>
                    </div>
                </div>
                <div class="stat-icon purple">
                    <span class="material-symbols-outlined">speed</span>
                </div>
            </div>

            <!-- Storage Overview Card (Clickable to Expand Sub-Cards) -->
            <div class="stat-card stat-card-clickable stat-card-storage {{ $hasStorageFilter ? 'is-expanded' : '' }}" 
                 id="storageMainCard" 
                 onclick="toggleSubCardPanel('storage')" 
                 title="คลิกเพื่อเปิด/ปิดรายละเอียดสัดส่วน Storage">
                <div class="stat-info">
                    <p style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span>สัดส่วน Storage (SSD vs HDD)</span>
                        <span class="stat-card-toggle-pill pill-emerald" title="คลิกเพื่อเปิด/ปิด">
                            <span class="material-symbols-outlined expand-arrow" id="storageToggleIcon">{{ $hasStorageFilter ? 'expand_less' : 'expand_more' }}</span>
                        </span>
                    </p>
                    <h2>{{ number_format($stats['ssd_count']) }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">SSD</span></h2>
                    <div class="stat-subtext" title="NVMe {{ $stats['nvme_count'] }} | SATA SSD {{ $stats['sata_ssd_count'] }} | HDD {{ $stats['hdd_only_count'] }}">
                        <span class="material-symbols-outlined" style="font-size: 15px; color: #059669;">hard_drive</span>
                        <span>ใช้ SSD {{ $stats['total_devices'] > 0 ? round(($stats['ssd_count'] / $stats['total_devices']) * 100) : 0 }}% (HDD {{ number_format($stats['hdd_only_count']) }} เครื่อง)</span>
                    </div>
                </div>
                <div class="stat-icon emerald">
                    <span class="material-symbols-outlined">hard_drive</span>
                </div>
            </div>

            <!-- RAM Overview Card (Clickable to Expand Sub-Cards) -->
            <div class="stat-card stat-card-clickable stat-card-ram {{ ($ramCount > 0 || $ramTypeCount > 0) ? 'is-expanded' : '' }}" 
                 id="ramMainCard" 
                 onclick="toggleSubCardPanel('ram')" 
                 title="คลิกเพื่อเปิด/ปิดรายละเอียดหน่วยความจำ RAM">
                <div class="stat-info">
                    <p style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span>หน่วยความจำ RAM (>= 8 GB)</span>
                        <span class="stat-card-toggle-pill pill-indigo" title="คลิกเพื่อเปิด/ปิด">
                            <span class="material-symbols-outlined expand-arrow" id="ramToggleIcon">{{ ($ramCount > 0 || $ramTypeCount > 0) ? 'expand_less' : 'expand_more' }}</span>
                        </span>
                    </p>
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

            <!-- Windows OS Overview Card (Clickable to Expand Sub-Cards) -->
            <div class="stat-card stat-card-clickable stat-card-windows {{ $hasWindowsFilter ? 'is-expanded' : '' }}" 
                 id="windowsMainCard" 
                 onclick="toggleSubCardPanel('windows')" 
                 title="คลิกเพื่อเปิด/ปิดรายละเอียดระบบปฏิบัติการ Windows">
                <div class="stat-info">
                    <p style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span>ระบบปฏิบัติการ Windows 11</span>
                        <span class="stat-card-toggle-pill pill-sky" title="คลิกเพื่อเปิด/ปิด">
                            <span class="material-symbols-outlined expand-arrow" id="windowsToggleIcon">{{ $hasWindowsFilter ? 'expand_less' : 'expand_more' }}</span>
                        </span>
                    </p>
                    <h2>{{ number_format($stats['win11_count']) }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">เครื่อง</span></h2>
                    <div class="stat-subtext" title="Win 11 Pro: {{ $stats['win11_pro_count'] }} | Win 11 Home: {{ $stats['win11_home_count'] }}">
                        <span class="material-symbols-outlined" style="font-size: 15px; color: #0284C7;">grid_view</span>
                        <span>Win 11 ({{ $stats['win11_count'] }}) | Win 10 ({{ $stats['win10_count'] }})</span>
                    </div>
                </div>
                <div class="stat-icon amber">
                    <span class="material-symbols-outlined">desktop_windows</span>
                </div>
            </div>
        </section>

        <!-- Performance Sub-Cards (5 Tiers Expandable) -->
        <section class="perf-subcards-wrapper" id="perfSubCardsPanel" style="display: {{ $perfCount > 0 ? 'block' : 'none' }};">
            <div class="perf-subcards-box">
                <div class="perf-subcards-header">
                    <div class="perf-subcards-title">
                        <span class="material-symbols-outlined" style="color: #7E22CE; font-size: 21px;">layers</span>
                        <span>ระดับประสิทธิภาพคอมพิวเตอร์รายกลุ่ม (5 ระดับ)</span>
                        <span class="perf-subcards-guide">• คลิกเพื่อกรองข้อมูลเฉพาะระดับนั้น</span>
                    </div>
                    <div class="perf-subcards-actions">
                        @if($perfCount > 0)
                            <button type="button" class="btn-subcard-clear" onclick="filterByPerfTier('clear')" title="ล้างการกรองประสิทธิภาพ">
                                <span class="material-symbols-outlined" style="font-size: 15px;">filter_alt_off</span>
                                <span>แสดงทั้งหมด ({{ $stats['total_devices'] }})</span>
                            </button>
                        @endif
                        <button type="button" class="btn-subcard-close" onclick="togglePerfSubCards(false)" title="ปิด">
                            <span class="material-symbols-outlined" style="font-size: 16px;">close</span>
                        </button>
                    </div>
                </div>

                <div class="perf-subcards-grid">
                    @php
                        $perfTiersList = [
                            [
                                'key' => 'highest',
                                'num' => 5,
                                'label' => 'สูงมาก',
                                'icon' => 'rocket_launch',
                                'theme' => '#059669',
                                'bg' => '#ECFDF5',
                                'border' => '#A7F3D0',
                                'spec' => 'CPU 16C+ (Core i9/Ryzen 9), RAM 64GB+, NVMe Gen 5/4',
                            ],
                            [
                                'key' => 'high',
                                'num' => 4,
                                'label' => 'สูง',
                                'icon' => 'verified',
                                'theme' => '#0284C7',
                                'bg' => '#F0F9FF',
                                'border' => '#BAE6FD',
                                'spec' => 'CPU 8-14C (Core i7/Ryzen 7), RAM 32GB, NVMe Gen 4',
                            ],
                            [
                                'key' => 'fair',
                                'num' => 3,
                                'label' => 'พอใช้',
                                'icon' => 'speed',
                                'theme' => '#0D9488',
                                'bg' => '#F0FDFA',
                                'border' => '#99F6E4',
                                'spec' => 'CPU 6C (Core i5/Ryzen 5), RAM 16GB, NVMe Gen 3',
                            ],
                            [
                                'key' => 'low',
                                'num' => 2,
                                'label' => 'ต่ำ',
                                'icon' => 'warning',
                                'theme' => '#D97706',
                                'bg' => '#FFFBEB',
                                'border' => '#FDE68A',
                                'spec' => 'CPU 4C (Core i3/Ryzen 3), RAM 8GB, SATA SSD',
                            ],
                            [
                                'key' => 'lowest',
                                'num' => 1,
                                'label' => 'ต่ำมาก',
                                'icon' => 'error',
                                'theme' => '#DC2626',
                                'bg' => '#FEF2F2',
                                'border' => '#FECACA',
                                'spec' => 'CPU <= 2C (Pentium), RAM 4GB, HDD จานหมุน',
                            ],
                        ];
                    @endphp

                    @foreach($perfTiersList as $t)
                        @php
                            $cnt = $stats['perf_counts'][$t['key']] ?? 0;
                            $pct = $stats['total_devices'] > 0 ? round(($cnt / $stats['total_devices']) * 100, 1) : 0;
                            $isSelected = in_array($t['key'], $selectedPerfs);
                        @endphp
                        <div class="perf-subcard {{ $isSelected ? 'is-selected' : '' }}" 
                             style="--tier-theme: {{ $t['theme'] }}; --tier-bg: {{ $t['bg'] }};"
                             onclick="filterByPerfTier('{{ $t['key'] }}')"
                             title="คลิกเพื่อกรองเฉพาะระดับ {{ $t['num'] }}. {{ $t['label'] }}{{ $isSelected ? ' (คลิกซ้ำเพื่อยกเลิก)' : '' }}">
                             
                            <div class="perf-subcard-top">
                                <div class="perf-subcard-badge" style="background: {{ $t['bg'] }}; color: {{ $t['theme'] }}; border-color: {{ $t['border'] }};">
                                    <span class="material-symbols-outlined">{{ $t['icon'] }}</span>
                                    <span>ระดับ {{ $t['num'] }}. {{ $t['label'] }}</span>
                                </div>
                                @if($isSelected)
                                    <span class="perf-active-indicator" title="กำลังกรองระดับนี้">
                                        <span class="material-symbols-outlined" style="font-size: 17px;">check_circle</span>
                                    </span>
                                @endif
                            </div>

                            <div class="perf-subcard-body">
                                <div class="perf-subcard-count" style="color: {{ $t['theme'] }};">
                                    {{ number_format($cnt) }}
                                    <span class="perf-subcard-unit">เครื่อง</span>
                                </div>
                                <div class="perf-subcard-pct">
                                    {{ $pct }}%
                                </div>
                            </div>

                            <div class="perf-subcard-bar-wrapper">
                                <div class="perf-subcard-bar-fill" style="width: {{ $pct }}%; background: {{ $t['theme'] }};"></div>
                            </div>

                            <div class="perf-subcard-spec" title="{{ $t['spec'] }}">
                                {{ $t['spec'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Storage Sub-Cards (Expandable) -->
        <section class="perf-subcards-wrapper" id="storageSubCardsPanel" style="display: {{ $hasStorageFilter ? 'block' : 'none' }};">
            <div class="perf-subcards-box">
                <div class="perf-subcards-header">
                    <div class="perf-subcards-title">
                        <span class="material-symbols-outlined" style="color: #059669; font-size: 21px;">hard_drive</span>
                        <span>สัดส่วนพื้นที่จัดเก็บข้อมูล (Storage Breakdown)</span>
                        <span class="perf-subcards-guide">• คลิกเพื่อกรองข้อมูลเฉพาะประเภทดิสก์</span>
                    </div>
                    <div class="perf-subcards-actions">
                        @if($hasStorageFilter)
                            <button type="button" class="btn-subcard-clear" onclick="filterByStorage('clear')" title="ล้างการกรอง Storage">
                                <span class="material-symbols-outlined" style="font-size: 15px;">filter_alt_off</span>
                                <span>แสดงทั้งหมด ({{ $stats['total_devices'] }})</span>
                            </button>
                        @endif
                        <button type="button" class="btn-subcard-close" onclick="toggleSubCardPanel('storage', false)" title="ปิด">
                            <span class="material-symbols-outlined" style="font-size: 16px;">close</span>
                        </button>
                    </div>
                </div>

                <div class="perf-subcards-grid" style="grid-template-columns: repeat(4, 1fr);">
                    @php
                        $storageTiers = [
                            [
                                'key' => 'nvme',
                                'label' => 'M.2 NVMe SSD',
                                'icon' => 'bolt',
                                'theme' => '#4F46E5',
                                'bg' => '#EEF2FF',
                                'border' => '#C7D2FE',
                                'cnt' => $stats['nvme_count'],
                                'spec' => 'ความเร็วสูงสุด PCIe Gen 3/4/5 (2,000 - 7,500+ MB/s)',
                            ],
                            [
                                'key' => 'sata_ssd',
                                'label' => 'SATA SSD (2.5" / M.2)',
                                'icon' => 'hard_drive',
                                'theme' => '#059669',
                                'bg' => '#ECFDF5',
                                'border' => '#A7F3D0',
                                'cnt' => $stats['sata_ssd_count'],
                                'spec' => 'Solid State Drive มาตรฐาน SATA III (~500 MB/s)',
                            ],
                            [
                                'key' => 'hdd',
                                'label' => 'HDD จานหมุน',
                                'icon' => 'disc_full',
                                'theme' => '#D97706',
                                'bg' => '#FFFBEB',
                                'border' => '#FDE68A',
                                'cnt' => $stats['hdd_only_count'],
                                'spec' => 'ฮาร์ดดิสก์แม่เหล็กจานหมุน (~100-150 MB/s ควรพิจารณาอัปเกรด)',
                            ],
                            [
                                'key' => 'other',
                                'label' => 'SCSI / อื่นๆ',
                                'icon' => 'dns',
                                'theme' => '#64748B',
                                'bg' => '#F8FAFC',
                                'border' => '#E2E8F0',
                                'cnt' => $stats['disk_other_count'],
                                'spec' => 'ดิสก์เครื่องแม่ข่าย Server SCSI หรือไม่ระบุชนิดดิสก์',
                            ],
                        ];
                    @endphp

                    @foreach($storageTiers as $t)
                        @php
                            $pct = $stats['total_devices'] > 0 ? round(($t['cnt'] / $stats['total_devices']) * 100, 1) : 0;
                            $isSelected = request('disk_type') === $t['key'];
                        @endphp
                        <div class="perf-subcard {{ $isSelected ? 'is-selected' : '' }}" 
                             style="--tier-theme: {{ $t['theme'] }}; --tier-bg: {{ $t['bg'] }};"
                             onclick="filterByStorage('{{ $t['key'] }}')"
                             title="คลิกเพื่อกรองเฉพาะ {{ $t['label'] }}{{ $isSelected ? ' (คลิกซ้ำเพื่อยกเลิก)' : '' }}">
                             
                            <div class="perf-subcard-top">
                                <div class="perf-subcard-badge" style="background: {{ $t['bg'] }}; color: {{ $t['theme'] }}; border-color: {{ $t['border'] }};">
                                    <span class="material-symbols-outlined">{{ $t['icon'] }}</span>
                                    <span>{{ $t['label'] }}</span>
                                </div>
                                @if($isSelected)
                                    <span class="perf-active-indicator" title="กำลังกรองกลุ่มนี้">
                                        <span class="material-symbols-outlined" style="font-size: 17px;">check_circle</span>
                                    </span>
                                @endif
                            </div>

                            <div class="perf-subcard-body">
                                <div class="perf-subcard-count" style="color: {{ $t['theme'] }};">
                                    {{ number_format($t['cnt']) }}
                                    <span class="perf-subcard-unit">เครื่อง</span>
                                </div>
                                <div class="perf-subcard-pct">
                                    {{ $pct }}%
                                </div>
                            </div>

                            <div class="perf-subcard-bar-wrapper">
                                <div class="perf-subcard-bar-fill" style="width: {{ $pct }}%; background: {{ $t['theme'] }};"></div>
                            </div>

                            <div class="perf-subcard-spec" title="{{ $t['spec'] }}">
                                {{ $t['spec'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- RAM Sub-Cards (Expandable) -->
        <section class="perf-subcards-wrapper" id="ramSubCardsPanel" style="display: {{ ($ramCount > 0 || $ramTypeCount > 0) ? 'block' : 'none' }};">
            <div class="perf-subcards-box">
                <div class="perf-subcards-header">
                    <div class="perf-subcards-title">
                        <span class="material-symbols-outlined" style="color: #4F46E5; font-size: 21px;">memory</span>
                        <span>สัดส่วนหน่วยความจำ RAM (ความจุ & เทคโนโลยี DDR)</span>
                        <span class="perf-subcards-guide">• คลิกเพื่อกรองข้อมูลตามความจุหรือชนิด RAM</span>
                    </div>
                    <div class="perf-subcards-actions">
                        @if($ramCount > 0 || $ramTypeCount > 0)
                            <button type="button" class="btn-subcard-clear" onclick="filterByRam('clear')" title="ล้างการกรอง RAM">
                                <span class="material-symbols-outlined" style="font-size: 15px;">filter_alt_off</span>
                                <span>แสดงทั้งหมด ({{ $stats['total_devices'] }})</span>
                            </button>
                        @endif
                        <button type="button" class="btn-subcard-close" onclick="toggleSubCardPanel('ram', false)" title="ปิด">
                            <span class="material-symbols-outlined" style="font-size: 16px;">close</span>
                        </button>
                    </div>
                </div>

                <div class="perf-subcards-grid" style="grid-template-columns: repeat(6, 1fr);">
                    @php
                        $ramTiers = [
                            [
                                'type' => 'size',
                                'val' => '16',
                                'label' => 'RAM 16 GB+',
                                'icon' => 'memory',
                                'theme' => '#7E22CE',
                                'bg' => '#FAF5FF',
                                'border' => '#E9D5FF',
                                'cnt' => $stats['ram_16plus_count'],
                                'spec' => 'ความจุสูง 16GB-32GB+ รองรับงานประมวลผลหนักและเปิดหลายแอป',
                            ],
                            [
                                'type' => 'size',
                                'val' => '8',
                                'label' => 'RAM 8 GB',
                                'icon' => 'check_circle',
                                'theme' => '#4F46E5',
                                'bg' => '#EEF2FF',
                                'border' => '#C7D2FE',
                                'cnt' => $stats['ram_8_count'],
                                'spec' => 'ขนาดมาตรฐานโรงพยาบาล 8 GB ทำงานทั่วไปได้อย่างราบรื่น',
                            ],
                            [
                                'type' => 'size',
                                'val' => 'low',
                                'label' => 'ต่ำกว่า 8 GB (< 8GB)',
                                'icon' => 'warning',
                                'theme' => '#D97706',
                                'bg' => '#FFFBEB',
                                'border' => '#FDE68A',
                                'cnt' => $stats['ram_low_count'],
                                'spec' => 'RAM 4 GB หรือน้อยกว่า ควรพิจารณาอัปเกรดเพื่อประสิทธิภาพที่ดี',
                            ],
                            [
                                'type' => 'type',
                                'val' => 'DDR5',
                                'label' => 'DDR5 (บัสใหม่)',
                                'icon' => 'rocket_launch',
                                'theme' => '#059669',
                                'bg' => '#ECFDF5',
                                'border' => '#A7F3D0',
                                'cnt' => $stats['ram_ddr5_count'],
                                'spec' => 'แรมมาตรฐาน DDR5 บัสความเร็วสูง 4800 - 5600+ MHz',
                            ],
                            [
                                'type' => 'type',
                                'val' => 'DDR4',
                                'label' => 'DDR4 (มาตรฐาน)',
                                'icon' => 'speed',
                                'theme' => '#0284C7',
                                'bg' => '#F0F9FF',
                                'border' => '#BAE6FD',
                                'cnt' => $stats['ram_ddr4_count'],
                                'spec' => 'แรมมาตรฐาน DDR4 บัสความเร็ว 2400 - 3200 MHz',
                            ],
                            [
                                'type' => 'type',
                                'val' => 'DDR3',
                                'label' => 'DDR3 (รุ่นเก่า)',
                                'icon' => 'history',
                                'theme' => '#DC2626',
                                'bg' => '#FEF2F2',
                                'border' => '#FECACA',
                                'cnt' => $stats['ram_ddr3_count'],
                                'spec' => 'แรมเทคโนโลยีเดิม DDR3 บัส 1333 - 1600 MHz เครื่องรุ่นเก่า',
                            ],
                        ];
                    @endphp

                    @foreach($ramTiers as $t)
                        @php
                            $pct = $stats['total_devices'] > 0 ? round(($t['cnt'] / $stats['total_devices']) * 100, 1) : 0;
                            $isSelected = ($t['type'] === 'size') 
                                ? in_array($t['val'], $selectedRams)
                                : in_array($t['val'], $selectedRamTypes);
                        @endphp
                        <div class="perf-subcard {{ $isSelected ? 'is-selected' : '' }}" 
                             style="--tier-theme: {{ $t['theme'] }}; --tier-bg: {{ $t['bg'] }};"
                             onclick="filterByRam('{{ $t['type'] }}', '{{ $t['val'] }}')"
                             title="คลิกเพื่อกรองเฉพาะ {{ $t['label'] }}{{ $isSelected ? ' (คลิกซ้ำเพื่อยกเลิก)' : '' }}">
                             
                            <div class="perf-subcard-top">
                                <div class="perf-subcard-badge" style="background: {{ $t['bg'] }}; color: {{ $t['theme'] }}; border-color: {{ $t['border'] }};">
                                    <span class="material-symbols-outlined">{{ $t['icon'] }}</span>
                                    <span>{{ $t['label'] }}</span>
                                </div>
                                @if($isSelected)
                                    <span class="perf-active-indicator" title="กำลังกรองกลุ่มนี้">
                                        <span class="material-symbols-outlined" style="font-size: 17px;">check_circle</span>
                                    </span>
                                @endif
                            </div>

                            <div class="perf-subcard-body">
                                <div class="perf-subcard-count" style="color: {{ $t['theme'] }};">
                                    {{ number_format($t['cnt']) }}
                                    <span class="perf-subcard-unit">เครื่อง</span>
                                </div>
                                <div class="perf-subcard-pct">
                                    {{ $pct }}%
                                </div>
                            </div>

                            <div class="perf-subcard-bar-wrapper">
                                <div class="perf-subcard-bar-fill" style="width: {{ $pct }}%; background: {{ $t['theme'] }};"></div>
                            </div>

                            <div class="perf-subcard-spec" title="{{ $t['spec'] }}">
                                {{ $t['spec'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Windows OS Sub-Cards (Expandable) -->
        <section class="perf-subcards-wrapper" id="windowsSubCardsPanel" style="display: {{ $hasWindowsFilter ? 'block' : 'none' }};">
            <div class="perf-subcards-box">
                <div class="perf-subcards-header">
                    <div class="perf-subcards-title">
                        <span class="material-symbols-outlined" style="color: #0284C7; font-size: 21px;">desktop_windows</span>
                        <span>สัดส่วนระบบปฏิบัติการ Windows (OS Editions Breakdown)</span>
                        <span class="perf-subcards-guide">• คลิกเพื่อกรองข้อมูลเฉพาะรุ่นระบบปฏิบัติการ</span>
                    </div>
                    <div class="perf-subcards-actions">
                        @if($hasWindowsFilter)
                            <button type="button" class="btn-subcard-clear" onclick="filterByWindows('clear')" title="ล้างการกรอง OS">
                                <span class="material-symbols-outlined" style="font-size: 15px;">filter_alt_off</span>
                                <span>แสดงทั้งหมด ({{ $stats['total_devices'] }})</span>
                            </button>
                        @endif
                        <button type="button" class="btn-subcard-close" onclick="toggleSubCardPanel('windows', false)" title="ปิด">
                            <span class="material-symbols-outlined" style="font-size: 16px;">close</span>
                        </button>
                    </div>
                </div>

                <div class="perf-subcards-grid" style="grid-template-columns: repeat(5, 1fr);">
                    @php
                        $winTiers = [
                            [
                                'key' => 'win11_pro',
                                'label' => 'Windows 11 Pro',
                                'icon' => 'desktop_windows',
                                'theme' => '#0284C7',
                                'bg' => '#F0F9FF',
                                'border' => '#BAE6FD',
                                'cnt' => $stats['win11_pro_count'],
                                'spec' => 'ระบบปฏิบัติการระดับองค์กรรุ่นใหม่ รองรับ BitLocker, Domain, Remote',
                            ],
                            [
                                'key' => 'win11_home',
                                'label' => 'Windows 11 Home',
                                'icon' => 'grid_view',
                                'theme' => '#2563EB',
                                'bg' => '#EFF6FF',
                                'border' => '#BFDBFE',
                                'cnt' => $stats['win11_home_count'],
                                'spec' => 'ระบบปฏิบัติการ Windows 11 รุ่นสำหรับงานทั่วไป',
                            ],
                            [
                                'key' => 'win10_pro',
                                'label' => 'Windows 10 Pro',
                                'icon' => 'computer',
                                'theme' => '#0D9488',
                                'bg' => '#F0FDFA',
                                'border' => '#99F6E4',
                                'cnt' => $stats['win10_pro_count'],
                                'spec' => 'ระบบปฏิบัติการ Windows 10 Pro เสถียรภาพสูง ประจำจุดบริการและแผนก',
                            ],
                            [
                                'key' => 'win10_other',
                                'label' => 'Windows 10 อื่นๆ',
                                'icon' => 'devices',
                                'theme' => '#D97706',
                                'bg' => '#FFFBEB',
                                'border' => '#FDE68A',
                                'cnt' => $stats['win10_other_count'],
                                'spec' => 'Windows 10 Home, Enterprise หรือรุ่นอื่นๆ',
                            ],
                            [
                                'key' => 'server',
                                'label' => 'Windows Server',
                                'icon' => 'dns',
                                'theme' => '#7E22CE',
                                'bg' => '#FAF5FF',
                                'border' => '#E9D5FF',
                                'cnt' => $stats['server_count'],
                                'spec' => 'ระบบปฏิบัติการสำหรับเครื่องแม่ข่ายเซิร์ฟเวอร์ (CSH-API, Gateway)',
                            ],
                        ];
                    @endphp

                    @foreach($winTiers as $t)
                        @php
                            $pct = $stats['total_devices'] > 0 ? round(($t['cnt'] / $stats['total_devices']) * 100, 1) : 0;
                            $isSelected = request('os') === $t['key'];
                        @endphp
                        <div class="perf-subcard {{ $isSelected ? 'is-selected' : '' }}" 
                             style="--tier-theme: {{ $t['theme'] }}; --tier-bg: {{ $t['bg'] }};"
                             onclick="filterByWindows('{{ $t['key'] }}')"
                             title="คลิกเพื่อกรองเฉพาะ {{ $t['label'] }}{{ $isSelected ? ' (คลิกซ้ำเพื่อยกเลิก)' : '' }}">
                             
                            <div class="perf-subcard-top">
                                <div class="perf-subcard-badge" style="background: {{ $t['bg'] }}; color: {{ $t['theme'] }}; border-color: {{ $t['border'] }};">
                                    <span class="material-symbols-outlined">{{ $t['icon'] }}</span>
                                    <span>{{ $t['label'] }}</span>
                                </div>
                                @if($isSelected)
                                    <span class="perf-active-indicator" title="กำลังกรองกลุ่มนี้">
                                        <span class="material-symbols-outlined" style="font-size: 17px;">check_circle</span>
                                    </span>
                                @endif
                            </div>

                            <div class="perf-subcard-body">
                                <div class="perf-subcard-count" style="color: {{ $t['theme'] }};">
                                    {{ number_format($t['cnt']) }}
                                    <span class="perf-subcard-unit">เครื่อง</span>
                                </div>
                                <div class="perf-subcard-pct">
                                    {{ $pct }}%
                                </div>
                            </div>

                            <div class="perf-subcard-bar-wrapper">
                                <div class="perf-subcard-bar-fill" style="width: {{ $pct }}%; background: {{ $t['theme'] }};"></div>
                            </div>

                            <div class="perf-subcard-spec" title="{{ $t['spec'] }}">
                                {{ $t['spec'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Filters Section - 1 Compact Row -->
        <section class="filter-section">
            <form action="{{ route('admin.specs') }}" method="GET" class="filter-form">
                <input type="hidden" name="sort" value="{{ request('sort', 'last_active') }}">
                <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

                <!-- Search Input -->
                <div class="search-box">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="ค้นหา: เครื่อง, IP, CPU, แผนก...">
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

                <!-- Performance Tier Filter (Multi-select Checkboxes) -->
                @php
                    $rawPerf = request('perf', []);
                    $selectedPerfs = is_array($rawPerf) ? $rawPerf : ($rawPerf ? explode(',', (string)$rawPerf) : []);
                    $perfCount = count($selectedPerfs);
                @endphp
                <div class="filter-dropdown-multiselect" id="perfDropdown">
                    <button type="button" class="filter-dropdown-btn {{ $perfCount > 0 ? 'has-value' : '' }}" onclick="toggleMultiDropdown(event, 'perfDropdown')">
                        <span id="perfDropdownLabel">
                            @if($perfCount === 0)
                                ประสิทธิภาพ
                            @elseif($perfCount === 1)
                                @php
                                    $perfTitles = ['highest' => '🚀 สูงมาก', 'high' => '✨ สูง', 'fair' => '⚡ พอใช้', 'low' => '⚠️ ต่ำ', 'lowest' => '🛑 ต่ำมาก'];
                                @endphp
                                {{ $perfTitles[$selectedPerfs[0]] ?? $selectedPerfs[0] }}
                            @else
                                ประสิทธิภาพ ({{ $perfCount }})
                            @endif
                        </span>
                        <span class="material-symbols-outlined dropdown-arrow">expand_more</span>
                    </button>
                    <div class="filter-dropdown-menu" id="perfDropdownMenu" onclick="event.stopPropagation();">
                        <div class="filter-dropdown-header">
                            <span>ติ๊กเลือกระดับประสิทธิภาพ</span>
                            <button type="button" class="btn-clear-multi" onclick="clearMulti('perfDropdown', 'ประสิทธิภาพ')">ล้าง</button>
                        </div>
                        <label class="multi-item">
                            <input type="checkbox" name="perf[]" value="highest" onchange="updateMultiLabel('perfDropdown', 'ประสิทธิภาพ')" {{ in_array('highest', $selectedPerfs) ? 'checked' : '' }}>
                            <span>🚀 5. สูงมาก</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="perf[]" value="high" onchange="updateMultiLabel('perfDropdown', 'ประสิทธิภาพ')" {{ in_array('high', $selectedPerfs) ? 'checked' : '' }}>
                            <span>✨ 4. สูง</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="perf[]" value="fair" onchange="updateMultiLabel('perfDropdown', 'ประสิทธิภาพ')" {{ in_array('fair', $selectedPerfs) ? 'checked' : '' }}>
                            <span>⚡ 3. พอใช้</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="perf[]" value="low" onchange="updateMultiLabel('perfDropdown', 'ประสิทธิภาพ')" {{ in_array('low', $selectedPerfs) ? 'checked' : '' }}>
                            <span>⚠️ 2. ต่ำ</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="perf[]" value="lowest" onchange="updateMultiLabel('perfDropdown', 'ประสิทธิภาพ')" {{ in_array('lowest', $selectedPerfs) ? 'checked' : '' }}>
                            <span>🛑 1. ต่ำมาก</span>
                        </label>
                        <div class="filter-dropdown-footer">
                            <button type="submit" class="btn-multi-apply">ตกลง</button>
                        </div>
                    </div>
                </div>

                <!-- RAM Filter (Multi-select Checkboxes) -->
                @php
                    $rawRam = request('ram', []);
                    $selectedRams = is_array($rawRam) ? $rawRam : ($rawRam ? explode(',', (string)$rawRam) : []);
                    $ramCount = count($selectedRams);
                @endphp
                <div class="filter-dropdown-multiselect" id="ramDropdown">
                    <button type="button" class="filter-dropdown-btn {{ $ramCount > 0 ? 'has-value' : '' }}" onclick="toggleMultiDropdown(event, 'ramDropdown')">
                        <span id="ramDropdownLabel">
                            @if($ramCount === 0)
                                ความจุ RAM
                            @elseif($ramCount === 1)
                                RAM {{ $selectedRams[0] == '32+' ? '32GB+' : ($selectedRams[0] == 'low' ? '< 8GB' : $selectedRams[0] . ' GB') }}
                            @else
                                ความจุ RAM ({{ $ramCount }})
                            @endif
                        </span>
                        <span class="material-symbols-outlined dropdown-arrow">expand_more</span>
                    </button>
                    <div class="filter-dropdown-menu" id="ramDropdownMenu" onclick="event.stopPropagation();">
                        <div class="filter-dropdown-header">
                            <span>ติ๊กเลือกเพื่อเทียบ RAM</span>
                            <button type="button" class="btn-clear-multi" onclick="clearMulti('ramDropdown', 'ความจุ RAM')">ล้าง</button>
                        </div>
                        <label class="multi-item">
                            <input type="checkbox" name="ram[]" value="4" onchange="updateMultiLabel('ramDropdown', 'ความจุ RAM')" {{ in_array('4', $selectedRams) ? 'checked' : '' }}>
                            <span>RAM 4 GB</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram[]" value="8" onchange="updateMultiLabel('ramDropdown', 'ความจุ RAM')" {{ in_array('8', $selectedRams) ? 'checked' : '' }}>
                            <span>RAM 8 GB</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram[]" value="12" onchange="updateMultiLabel('ramDropdown', 'ความจุ RAM')" {{ in_array('12', $selectedRams) ? 'checked' : '' }}>
                            <span>RAM 12 GB</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram[]" value="16" onchange="updateMultiLabel('ramDropdown', 'ความจุ RAM')" {{ in_array('16', $selectedRams) ? 'checked' : '' }}>
                            <span>RAM 16 GB</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram[]" value="32+" onchange="updateMultiLabel('ramDropdown', 'ความจุ RAM')" {{ in_array('32+', $selectedRams) ? 'checked' : '' }}>
                            <span>RAM 32 GB ขึ้นไป</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram[]" value="low" onchange="updateMultiLabel('ramDropdown', 'ความจุ RAM')" {{ in_array('low', $selectedRams) ? 'checked' : '' }}>
                            <span>⚠️ ต่ำกว่า 8 GB</span>
                        </label>
                        <div class="filter-dropdown-footer">
                            <button type="submit" class="btn-multi-apply">ตกลง</button>
                        </div>
                    </div>
                </div>

                <!-- RAM Type Filter (Multi-select Checkboxes) -->
                @php
                    $rawRamType = request('ram_type', []);
                    $selectedRamTypes = is_array($rawRamType) ? $rawRamType : ($rawRamType ? explode(',', (string)$rawRamType) : []);
                    $ramTypeCount = count($selectedRamTypes);
                @endphp
                <div class="filter-dropdown-multiselect" id="ramTypeDropdown">
                    <button type="button" class="filter-dropdown-btn {{ $ramTypeCount > 0 ? 'has-value' : '' }}" onclick="toggleMultiDropdown(event, 'ramTypeDropdown')">
                        <span id="ramTypeDropdownLabel">
                            @if($ramTypeCount === 0)
                                ประเภท RAM
                            @elseif($ramTypeCount === 1)
                                {{ strtoupper($selectedRamTypes[0]) === 'OTHER' ? 'อื่นๆ / ไม่ระบุ' : strtoupper($selectedRamTypes[0]) }}
                            @else
                                ประเภท RAM ({{ $ramTypeCount }})
                            @endif
                        </span>
                        <span class="material-symbols-outlined dropdown-arrow">expand_more</span>
                    </button>
                    <div class="filter-dropdown-menu" id="ramTypeDropdownMenu" onclick="event.stopPropagation();">
                        <div class="filter-dropdown-header">
                            <span>ติ๊กเลือกประเภท RAM</span>
                            <button type="button" class="btn-clear-multi" onclick="clearMulti('ramTypeDropdown', 'ประเภท RAM')">ล้าง</button>
                        </div>
                        <label class="multi-item">
                            <input type="checkbox" name="ram_type[]" value="DDR5" onchange="updateMultiLabel('ramTypeDropdown', 'ประเภท RAM')" {{ in_array('DDR5', $selectedRamTypes) ? 'checked' : '' }}>
                            <span>🚀 DDR5</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram_type[]" value="DDR4" onchange="updateMultiLabel('ramTypeDropdown', 'ประเภท RAM')" {{ in_array('DDR4', $selectedRamTypes) ? 'checked' : '' }}>
                            <span>⚡ DDR4</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram_type[]" value="DDR3" onchange="updateMultiLabel('ramTypeDropdown', 'ประเภท RAM')" {{ in_array('DDR3', $selectedRamTypes) ? 'checked' : '' }}>
                            <span>⏳ DDR3</span>
                        </label>
                        <label class="multi-item">
                            <input type="checkbox" name="ram_type[]" value="other" onchange="updateMultiLabel('ramTypeDropdown', 'ประเภท RAM')" {{ in_array('other', $selectedRamTypes) ? 'checked' : '' }}>
                            <span>❓ อื่นๆ / ไม่ระบุ</span>
                        </label>
                        <div class="filter-dropdown-footer">
                            <button type="submit" class="btn-multi-apply">ตกลง</button>
                        </div>
                    </div>
                </div>

                <!-- Disk Type Filter -->
                <select name="disk_type" class="filter-select">
                    <option value="">ประเภทดิสก์ทั้งหมด</option>
                    <option value="nvme" {{ request('disk_type') == 'nvme' ? 'selected' : '' }}>M.2 NVMe SSD</option>
                    <option value="sata_ssd" {{ request('disk_type') == 'sata_ssd' ? 'selected' : '' }}>SATA SSD</option>
                    <option value="ssd" {{ request('disk_type') == 'ssd' ? 'selected' : '' }}>SSD ทั้งหมด</option>
                    <option value="hdd" {{ request('disk_type') == 'hdd' ? 'selected' : '' }}>HDD จานหมุน</option>
                    <option value="other" {{ request('disk_type') == 'other' ? 'selected' : '' }}>SCSI / อื่นๆ</option>
                </select>

                <!-- OS Filter -->
                <select name="os" class="filter-select">
                    <option value="">ระบบปฏิบัติการทั้งหมด</option>
                    <option value="win11" {{ request('os') == 'win11' ? 'selected' : '' }}>Windows 11 (ทั้งหมด)</option>
                    <option value="win11_pro" {{ request('os') == 'win11_pro' ? 'selected' : '' }}>Windows 11 Pro</option>
                    <option value="win11_home" {{ request('os') == 'win11_home' ? 'selected' : '' }}>Windows 11 Home</option>
                    <option value="win10" {{ request('os') == 'win10' ? 'selected' : '' }}>Windows 10 (ทั้งหมด)</option>
                    <option value="win10_pro" {{ request('os') == 'win10_pro' ? 'selected' : '' }}>Windows 10 Pro</option>
                    <option value="win10_other" {{ request('os') == 'win10_other' ? 'selected' : '' }}>Windows 10 อื่นๆ</option>
                    <option value="server" {{ request('os') == 'server' ? 'selected' : '' }}>Windows Server</option>
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
                    <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>ออนไลน์</option>
                    <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>ออฟไลน์</option>
                </select>

                <button type="submit" class="btn-filter-submit" title="ค้นหาตามเงื่อนไข">
                    <span class="material-symbols-outlined" style="font-size: 17px;">filter_alt</span>
                    <span>กรอง</span>
                </button>

                @if(request()->hasAny(['search', 'department', 'perf', 'ram', 'ram_type', 'disk_type', 'os', 'office', 'status', 'sort', 'dir']))
                    <a href="{{ route('admin.specs') }}" class="btn-filter-reset" title="ล้างการกรองและการเรียงลำดับทั้งหมด">
                        <span class="material-symbols-outlined" style="font-size: 16px;">restart_alt</span>
                        <span>ล้างค่า</span>
                    </a>
                @endif

                <a href="{{ route('admin.specs.export') }}" class="btn-export" title="ส่งออกสเปกเครื่องทั้งหมดเป็นไฟล์ CSV">
                    <span class="material-symbols-outlined" style="font-size: 17px;">download</span>
                    <span>ส่งออก CSV</span>
                </a>
            </form>
        </section>

        <!-- Hardware Specifications Table -->
        <section class="table-card">
            <div class="table-responsive">
                <table class="specs-table">
                    @php
                        $currSort = request('sort', 'last_active');
                        $currDir  = strtolower(request('dir', 'desc'));
                    @endphp
                    <thead>
                        <tr>
                            <th class="th-sortable {{ $currSort === 'id' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('id')" style="width: 40px; text-align: center;" title="คลิกเรียงลำดับ ID (ข้อมูลทั้งหมด)">
                                <div class="th-content" style="justify-content: center;">
                                    <span class="th-label">#</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'id' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'department' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('department')" style="width: 125px;" title="คลิกเรียงตามแผนก (ข้อมูลทั้งหมด)">
                                <div class="th-content">
                                    <span class="th-label">แผนก</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'department' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'hostname' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('hostname')" style="width: 155px;" title="คลิกเรียงตามชื่อเครื่อง (ข้อมูลทั้งหมด)">
                                <div class="th-content">
                                    <span class="th-label">ชื่อเครื่อง</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'hostname' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'perf' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('perf')" style="width: 90px; text-align: center;" title="คลิกเรียงตามระดับประสิทธิภาพ สูงมาก-ต่ำมาก (ข้อมูลทั้งหมด)">
                                <div class="th-content" style="justify-content: center;">
                                    <span class="th-label">ประสิทธิภาพ</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'perf' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'cpu' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('cpu')" style="min-width: 130px;" title="คลิกเรียงตามรุ่น CPU (ข้อมูลทั้งหมด)">
                                <div class="th-content">
                                    <span class="th-label">CPU</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'cpu' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'cores' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('cores')" style="width: 95px; text-align: center;" title="คลิกเรียงตามจำนวน Core/Threads (ข้อมูลทั้งหมด)">
                                <div class="th-content" style="justify-content: center;">
                                    <span class="th-label">Core/Threads</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'cores' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'speed' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('speed')" style="width: 85px; text-align: center;" title="คลิกเรียงตามความเร็ว CPU Speed GHz (ข้อมูลทั้งหมด)">
                                <div class="th-content" style="justify-content: center;">
                                    <span class="th-label">Speed</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'speed' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'ram_size' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('ram_size')" style="width: 85px; text-align: center;" title="คลิกเรียงตามขนาดความจุ RAM GB (ข้อมูลทั้งหมด)">
                                <div class="th-content" style="justify-content: center;">
                                    <span class="th-label">ความจุ RAM</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'ram_size' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'ram_type' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('ram_type')" style="width: 85px; text-align: center;" title="คลิกเรียงตามประเภท RAM DDR5/4/3 (ข้อมูลทั้งหมด)">
                                <div class="th-content" style="justify-content: center;">
                                    <span class="th-label">ประเภท RAM</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'ram_type' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'disk' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('disk')" style="min-width: 130px;" title="คลิกเรียงตามประเภทดิสก์ NVMe/SSD/HDD (ข้อมูลทั้งหมด)">
                                <div class="th-content">
                                    <span class="th-label">Disk</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'disk' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'os' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('os')" style="min-width: 140px;" title="คลิกเรียงตามระบบปฏิบัติการ Windows (ข้อมูลทั้งหมด)">
                                <div class="th-content">
                                    <span class="th-label">OS</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'os' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th class="th-sortable {{ $currSort === 'office' ? ($currDir === 'desc' ? 'sorted-desc' : 'sorted-asc') : '' }}" onclick="sortByServer('office')" style="min-width: 140px;" title="คลิกเรียงตามโปรแกรม Microsoft Office (ข้อมูลทั้งหมด)">
                                <div class="th-content">
                                    <span class="th-label">Microsoft</span>
                                    <span class="material-symbols-outlined sort-icon">
                                        {{ $currSort === 'office' ? ($currDir === 'desc' ? 'arrow_downward' : 'arrow_upward') : 'unfold_more' }}
                                    </span>
                                </div>
                            </th>
                            <th style="width: 85px; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installations as $idx => $item)
                            <tr>
                                <!-- Index -->
                                <td class="col-row-index" style="text-align: center; color: var(--text-muted); font-size: 0.82rem; font-weight: 500;" data-sort-value="{{ $installations->firstItem() + $idx }}">
                                    {{ $installations->firstItem() + $idx }}
                                </td>

                                <!-- แผนก -->
                                <td data-sort-value="{{ $item->department ?: '' }}">
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
                                <td data-sort-value="{{ $item->hostname ?: '' }}">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;" title="IP: {{ $item->ip_address }} | ผู้ใช้: {{ $item->device_name ?: '-' }} | สถานะ: {{ $item->computed_online ? 'ออนไลน์' : 'ออฟไลน์' }}">
                                        <span class="status-dot {{ $item->computed_online ? 'online' : 'offline' }}" title="{{ $item->computed_online ? 'ออนไลน์' : 'ออฟไลน์' }}"></span>
                                        <span style="font-weight: 600; color: #0F172A;">{{ $item->hostname ?: '-' }}</span>
                                    </div>
                                </td>

                                <!-- ประสิทธิภาพ -->
                                @php
                                    $perf = $item->performance_level;
                                    $perfTierScore = match($perf['key'] ?? '') {
                                        'highest' => 5,
                                        'high'    => 4,
                                        'fair'    => 3,
                                        'low'     => 2,
                                        'lowest'  => 1,
                                        default   => 0
                                    };
                                @endphp
                                <td style="text-align: center;" data-sort-value="{{ $perfTierScore }}">
                                    <span class="badge badge-{{ $perf['class'] }}" title="{{ $perf['desc'] }}">
                                        <span class="material-symbols-outlined" style="font-size: 13px;">{{ $perf['icon'] }}</span>
                                        <span>{{ $perf['level'] }}</span>
                                    </span>
                                </td>

                                <!-- CPU -->
                                <td data-sort-value="{{ $item->short_cpu ?: '' }}">
                                    @if($item->short_cpu && $item->short_cpu !== '-')
                                        <span class="badge badge-cpu" title="{{ $item->cpu }}">
                                             <span class="material-symbols-outlined" style="font-size: 13px; color: #4F46E5;">developer_board</span>
                                            <span>{{ $item->short_cpu }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- Core/Threads -->
                                @php
                                    // Extract core number for sorting e.g. "4C / 8T" -> 4
                                    $coreNum = 0;
                                    if (preg_match('/(\d+)\s*C/i', (string)$item->short_cpu_cores, $cm)) {
                                        $coreNum = (int)$cm[1];
                                    }
                                @endphp
                                <td style="text-align: center;" data-sort-value="{{ $coreNum }}">
                                    @if($item->short_cpu_cores && $item->short_cpu_cores !== '-')
                                        <span class="badge badge-cores" title="{{ $item->cpu_cores_detail }}">
                                            <span>{{ $item->short_cpu_cores }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- Speed -->
                                @php
                                    // Extract GHz for numeric sorting e.g. "3.20 GHz" -> 3.20
                                    $ghzNum = 0;
                                    if (preg_match('/([\d\.]+)\s*GHz/i', (string)$item->short_cpu_speed, $sm)) {
                                        $ghzNum = (float)$sm[1];
                                    }
                                @endphp
                                <td style="text-align: center;" data-sort-value="{{ $ghzNum }}">
                                    @if($item->short_cpu_speed && $item->short_cpu_speed !== '-')
                                        <span class="badge badge-speed" title="ความเร็ว CPU: {{ $item->short_cpu_speed }}">
                                            <span>{{ $item->short_cpu_speed }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- ความจุ RAM -->
                                @php
                                    // Extract RAM GB number e.g. "16 GB" -> 16
                                    $ramGb = 0;
                                    if (preg_match('/(\d+)\s*GB/i', (string)$item->short_ram_size, $rm)) {
                                        $ramGb = (int)$rm[1];
                                    }
                                @endphp
                                <td style="text-align: center;" data-sort-value="{{ $ramGb }}">
                                    @if($item->short_ram_size && $item->short_ram_size !== '-')
                                        <span class="badge badge-ram-size" title="{{ $item->ram }}">
                                            <span>{{ $item->short_ram_size }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- ประเภท RAM -->
                                @php
                                    $ramTypeWeight = match($item->short_ram_type) {
                                        'DDR5' => 5,
                                        'DDR4' => 4,
                                        'DDR3' => 3,
                                        'DDR2' => 2,
                                        default => 0,
                                    };
                                @endphp
                                <td style="text-align: center;" data-sort-value="{{ $ramTypeWeight }}">
                                    @if($item->short_ram_type && $item->short_ram_type !== '-')
                                        @php
                                            $typeClass = match($item->short_ram_type) {
                                                'DDR5' => 'badge-ram-ddr5',
                                                'DDR4' => 'badge-ram-ddr4',
                                                'DDR3' => 'badge-ram-ddr3',
                                                default => 'badge-ram',
                                            };
                                        @endphp
                                        <span class="badge {{ $typeClass }}" title="{{ $item->ram }}">
                                            <span>{{ $item->short_ram_type }}</span>
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>

                                <!-- Disk -->
                                @php
                                    $diskWeight = 0;
                                    if (str_contains($item->short_disk, 'NVMe')) {
                                        $diskWeight = 3;
                                    } elseif (str_contains($item->short_disk, 'SSD')) {
                                        $diskWeight = 2;
                                    } elseif (str_contains($item->short_disk, 'HDD')) {
                                        $diskWeight = 1;
                                    }
                                @endphp
                                <td data-sort-value="{{ $diskWeight }}_{{ $item->short_disk }}">
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
                                <td data-sort-value="{{ $item->short_os ?: '' }}">
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
                                <td data-sort-value="{{ $item->short_office === 'ไม่มี' ? '' : $item->short_office }}">
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
                                <td colspan="13">
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
                    <span id="dtPerfBadge" class="badge"></span>
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

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-notify">
        <span class="material-symbols-outlined" style="color: #34D399; font-size: 18px;">check_circle</span>
        <span id="toastMessage">เรียงลำดับเรียบร้อย</span>
    </div>

    <!-- JavaScript -->
    <script>
        // --- Show All / Reset All Filters ---
        function resetAllFiltersAndShowAll() {
            sessionStorage.setItem('cshos_active_subpanel', 'none');
            window.location.href = "{{ route('admin.specs') }}";
        }

        // --- Multi-Panel Toggle (Performance, Storage, RAM, Windows) ---
        function toggleSubCardPanel(panelType, forceState) {
            const panels = {
                perf: document.getElementById('perfSubCardsPanel'),
                storage: document.getElementById('storageSubCardsPanel'),
                ram: document.getElementById('ramSubCardsPanel'),
                windows: document.getElementById('windowsSubCardsPanel')
            };
            const cards = {
                perf: document.getElementById('perfMainCard'),
                storage: document.getElementById('storageMainCard'),
                ram: document.getElementById('ramMainCard'),
                windows: document.getElementById('windowsMainCard')
            };
            const icons = {
                perf: document.getElementById('perfToggleIcon'),
                storage: document.getElementById('storageToggleIcon'),
                ram: document.getElementById('ramToggleIcon'),
                windows: document.getElementById('windowsToggleIcon')
            };

            const targetPanel = panels[panelType];
            if (!targetPanel) return;

            let shouldOpen;
            if (typeof forceState === 'boolean') {
                shouldOpen = forceState;
            } else {
                shouldOpen = (targetPanel.style.display === 'none' || targetPanel.style.display === '');
            }

            // Close all other panels
            Object.keys(panels).forEach(key => {
                if (panels[key]) panels[key].style.display = 'none';
                if (cards[key]) cards[key].classList.remove('is-expanded');
                if (icons[key]) icons[key].textContent = 'expand_more';
            });

            if (shouldOpen) {
                targetPanel.style.display = 'block';
                if (cards[panelType]) cards[panelType].classList.add('is-expanded');
                if (icons[panelType]) icons[panelType].textContent = 'expand_less';
                sessionStorage.setItem('cshos_active_subpanel', panelType);
            } else {
                sessionStorage.setItem('cshos_active_subpanel', 'none');
            }
        }

        // Backward compatibility
        function togglePerfSubCards(forceState) {
            toggleSubCardPanel('perf', forceState);
        }

        function filterByPerfTier(tierKey) {
            sessionStorage.setItem('cshos_active_subpanel', 'perf');
            const form = document.querySelector('.filter-form');
            if (!form) return;

            if (tierKey === 'clear') {
                form.querySelectorAll('input[name="perf[]"]').forEach(i => i.checked = false);
                form.submit();
                return;
            }

            const target = form.querySelector(`input[name="perf[]"][value="${tierKey}"]`);
            const all = form.querySelectorAll('input[name="perf[]"]');
            if (target) {
                const wasChecked = target.checked;
                const checkedCount = Array.from(all).filter(i => i.checked).length;
                if (wasChecked && checkedCount === 1) {
                    target.checked = false;
                } else {
                    all.forEach(i => i.checked = false);
                    target.checked = true;
                }
                form.submit();
            }
        }

        function filterByStorage(diskKey) {
            sessionStorage.setItem('cshos_active_subpanel', 'storage');
            const form = document.querySelector('.filter-form');
            if (!form) return;

            const select = form.querySelector('select[name="disk_type"]');
            if (select) {
                if (diskKey === 'clear' || select.value === diskKey) {
                    select.value = '';
                } else {
                    select.value = diskKey;
                }
                form.submit();
            }
        }

        function filterByRam(type, val) {
            sessionStorage.setItem('cshos_active_subpanel', 'ram');
            const form = document.querySelector('.filter-form');
            if (!form) return;

            if (type === 'clear') {
                form.querySelectorAll('input[name="ram[]"]').forEach(i => i.checked = false);
                form.querySelectorAll('input[name="ram_type[]"]').forEach(i => i.checked = false);
                form.submit();
                return;
            }

            if (type === 'size') {
                const target = form.querySelector(`input[name="ram[]"][value="${val}"]`);
                const all = form.querySelectorAll('input[name="ram[]"]');
                if (target) {
                    const wasChecked = target.checked;
                    const checkedCount = Array.from(all).filter(i => i.checked).length;
                    if (wasChecked && checkedCount === 1) {
                        target.checked = false;
                    } else {
                        all.forEach(i => i.checked = false);
                        target.checked = true;
                    }
                    form.submit();
                }
            } else if (type === 'type') {
                const target = form.querySelector(`input[name="ram_type[]"][value="${val}"]`);
                const all = form.querySelectorAll('input[name="ram_type[]"]');
                if (target) {
                    const wasChecked = target.checked;
                    const checkedCount = Array.from(all).filter(i => i.checked).length;
                    if (wasChecked && checkedCount === 1) {
                        target.checked = false;
                    } else {
                        all.forEach(i => i.checked = false);
                        target.checked = true;
                    }
                    form.submit();
                }
            }
        }

        function filterByWindows(osKey) {
            sessionStorage.setItem('cshos_active_subpanel', 'windows');
            const form = document.querySelector('.filter-form');
            if (!form) return;

            const select = form.querySelector('select[name="os"]');
            if (select) {
                if (osKey === 'clear' || select.value === osKey) {
                    select.value = '';
                } else {
                    select.value = osKey;
                }
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const hasPerf = {{ $perfCount > 0 ? 'true' : 'false' }};
            const hasStorage = {{ $hasStorageFilter ? 'true' : 'false' }};
            const hasRam = {{ ($ramCount > 0 || $ramTypeCount > 0) ? 'true' : 'false' }};
            const hasWindows = {{ $hasWindowsFilter ? 'true' : 'false' }};

            const saved = sessionStorage.getItem('cshos_active_subpanel');

            if (hasPerf) {
                toggleSubCardPanel('perf', true);
            } else if (hasStorage) {
                toggleSubCardPanel('storage', true);
            } else if (hasRam) {
                toggleSubCardPanel('ram', true);
            } else if (hasWindows) {
                toggleSubCardPanel('windows', true);
            } else if (saved && saved !== 'none') {
                toggleSubCardPanel(saved, true);
            }
        });

        // --- Server-side Sorting for all records across all pages ---
        function sortByServer(column) {
            const url = new URL(window.location.href);
            const currentSort = url.searchParams.get('sort') || 'last_active';
            const currentDir = (url.searchParams.get('dir') || 'desc').toLowerCase();

            let newDir = 'desc';
            if (currentSort === column) {
                newDir = currentDir === 'desc' ? 'asc' : 'desc';
            }

            url.searchParams.set('sort', column);
            url.searchParams.set('dir', newDir);
            url.searchParams.delete('page'); // กลับไปหน้า 1 เสมอเมื่อเปลี่ยนการเรียง

            window.location.href = url.toString();
        }

        function showToast(msg) {
            const toast = document.getElementById('toastNotification');
            const toastMsg = document.getElementById('toastMessage');
            if (toast && toastMsg) {
                toastMsg.textContent = msg;
                toast.classList.add('show');
                clearTimeout(window.toastTimer);
                window.toastTimer = setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
        }

        function openDetailsModal(item) {
            document.getElementById('dtHostname').textContent = item.hostname || 'ไม่ระบุชื่อเครื่อง';
            
            const isOnline = item.computed_online;
            const badge = document.getElementById('dtStatusBadge');
            badge.className = 'badge ' + (isOnline ? 'badge-online' : 'badge-offline');
            badge.innerHTML = `<span class="material-symbols-outlined" style="font-size: 13px;">${isOnline ? 'check_circle' : 'power_off'}</span> ${isOnline ? 'ออนไลน์' : 'ออฟไลน์'}`;

            const perfBadge = document.getElementById('dtPerfBadge');
            if (perfBadge && item.performance_level) {
                const perf = item.performance_level;
                perfBadge.className = 'badge badge-' + perf.class;
                perfBadge.innerHTML = `<span class="material-symbols-outlined" style="font-size: 13px;">${perf.icon}</span> ${perf.level}`;
                perfBadge.title = perf.desc || '';
            }

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
                document.querySelectorAll('.filter-dropdown-menu.show').forEach(m => m.classList.remove('show'));
                document.querySelectorAll('.filter-dropdown-btn.active').forEach(b => b.classList.remove('active'));
            }
        });

        // Multi-Select Dropdowns
        function toggleMultiDropdown(e, id) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const dropdown = document.getElementById(id);
            if (!dropdown) return;
            const menu = dropdown.querySelector('.filter-dropdown-menu');
            const btn = dropdown.querySelector('.filter-dropdown-btn');
            const isShown = menu.classList.contains('show');

            // Close other dropdowns
            document.querySelectorAll('.filter-dropdown-menu.show').forEach(m => m.classList.remove('show'));
            document.querySelectorAll('.filter-dropdown-btn.active').forEach(b => b.classList.remove('active'));

            if (!isShown) {
                menu.classList.add('show');
                btn.classList.add('active');
            }
        }

        function updateMultiLabel(dropdownId, defaultLabel) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]:checked');
            const labelSpan = dropdown.querySelector('.filter-dropdown-btn span:first-child');
            const btn = dropdown.querySelector('.filter-dropdown-btn');

            if (checkboxes.length === 0) {
                labelSpan.textContent = defaultLabel;
                btn.classList.remove('has-value');
            } else if (checkboxes.length === 1) {
                const labelText = checkboxes[0].parentElement.querySelector('span').textContent.trim();
                labelSpan.textContent = labelText;
                btn.classList.add('has-value');
            } else {
                labelSpan.textContent = `${defaultLabel} (${checkboxes.length} รายการ)`;
                btn.classList.add('has-value');
            }
        }

        function clearMulti(dropdownId, defaultLabel) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            updateMultiLabel(dropdownId, defaultLabel);
        }

        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.filter-dropdown-multiselect')) {
                document.querySelectorAll('.filter-dropdown-menu.show').forEach(m => m.classList.remove('show'));
                document.querySelectorAll('.filter-dropdown-btn.active').forEach(b => b.classList.remove('active'));
            }
        });
    </script>
</body>
</html>
