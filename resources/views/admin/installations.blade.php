<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบตรวจสอบการติดตั้ง CSHOS STAFF INFO - โรงพยาบาลเชียงแสน</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <style>
        :root {
            --primary: #4D9DE0;
            --primary-light: #EBF4FC;
            --primary-dark: #2F7EC2;
            --bg-color: #F8FAFC;
            --surface: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --success: #10B981;
            --success-light: #D1FAE5;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --danger: #EF4444;
            --danger-light: #FEE2E2;
            --purple: #8B5CF6;
            --purple-light: #EDE9FE;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Noto Sans Thai', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.55;
            letter-spacing: 0.01em;
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

        /* --- Header Navbar --- */
        .navbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 1.8%;
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
            border-radius: 12px;
            background: linear-gradient(135deg, #7EC8F8, #4D9DE0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 10px rgba(77, 157, 224, 0.3);
        }

        .brand-text h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .brand-text p {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-download-app {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
            transition: all 0.2s;
        }

        .btn-download-app:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .btn-download-uninstaller {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            background: #FFF1F2;
            color: #E11D48;
            border: 1px solid #FECDD3;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.86rem;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(225, 29, 72, 0.1);
            transition: all 0.2s;
        }

        .btn-download-uninstaller:hover {
            background: #FFE4E6;
            color: #BE123C;
            border-color: #FDA4AF;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(225, 29, 72, 0.2);
        }

        .btn-view-site {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: var(--primary-light);
            color: var(--primary-dark);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-view-site:hover {
            background: #dbeafe;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-left: 1rem;
            border-left: 1px solid var(--border);
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.88rem;
            color: #475569;
            font-weight: 500;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.45rem 0.85rem;
            background: #FFF1F2;
            color: #E11D48;
            border: 1px solid #FFE4E6;
            border-radius: 8px;
            font-size: 0.85rem;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #FFE4E6;
        }

        /* --- Container --- */
        .container {
            max-width: 1760px;
            width: 98%;
            margin: 0 auto;
            padding: 1.5rem 1.25rem;
            flex: 1;
        }

        /* --- Stats Row --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.8rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 18px;
            padding: 1.25rem 1.4rem;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
        }

        .stat-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.35rem;
            font-weight: 500;
        }

        .stat-info h2 {
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.blue { background: #E0F2FE; color: #0284C7; }
        .stat-icon.green { background: #D1FAE5; color: #059669; }
        .stat-icon.purple { background: #EDE9FE; color: #7C3AED; }
        .stat-icon.amber { background: #FEF3C7; color: #D97706; }

        /* --- Alert Messages --- */
        .alert {
            padding: 0.85rem 1.2rem;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        /* --- Controls Bar --- */
        .controls-card {
            background: var(--surface);
            border-radius: 14px;
            padding: 0.65rem 1rem;
            border: 1px solid var(--border);
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .filter-form {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.6rem;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 2px;
            scrollbar-width: thin;
        }

        .filter-form::-webkit-scrollbar {
            height: 4px;
        }
        .filter-form::-webkit-scrollbar-track {
            background: transparent;
        }
        .filter-form::-webkit-scrollbar-thumb {
            background: #E2E8F0;
            border-radius: 4px;
        }

        .filter-inputs {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.45rem;
            align-items: center;
            flex-shrink: 0;
        }

        .search-box {
            position: relative;
            width: 200px;
            flex-shrink: 0;
        }

        .search-box .material-symbols-Outlined {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 17px;
        }

        .search-box input {
            width: 100%;
            height: 35px;
            padding: 0 0.65rem 0 2rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.82rem;
            font-family: inherit;
            outline: none;
            background: #F8FAFC;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .search-box input:focus {
            background: #fff;
            border-color: var(--primary);
        }

        .select-filter {
            height: 35px;
            padding: 0 0.65rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.82rem;
            font-family: inherit;
            background: #F8FAFC;
            color: var(--text-dark);
            outline: none;
            cursor: pointer;
            box-sizing: border-box;
            max-width: 150px;
            white-space: nowrap;
            text-overflow: ellipsis;
            flex-shrink: 0;
        }

        .select-filter:focus {
            border-color: var(--primary);
            background: #fff;
        }

        .btn-filter {
            height: 35px;
            padding: 0 0.8rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
            flex-shrink: 0;
            box-sizing: border-box;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background: var(--primary-dark);
        }

        .btn-reset {
            height: 35px;
            padding: 0 0.65rem;
            background: #F1F5F9;
            color: #64748B;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.82rem;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
            flex-shrink: 0;
            box-sizing: border-box;
        }

        .btn-reset:hover {
            background: #E2E8F0;
            color: #334155;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-shrink: 0;
            flex-wrap: nowrap;
        }

        .action-buttons a,
        .action-buttons button {
            height: 35px;
            padding: 0 0.65rem;
            border-radius: 8px;
            font-size: 0.82rem;
            white-space: nowrap;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            flex-shrink: 0;
        }

        .btn-export {
            background: #10B981;
            color: white;
            border: none;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
            transition: all 0.2s;
        }

        .btn-export:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-refresh {
            background: #FFFFFF;
            color: #475569;
            border: 1.5px solid var(--border);
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-refresh:hover {
            background: #F8FAFC;
            color: var(--primary);
            border-color: var(--primary);
        }

        /* --- Table View --- */
        .table-card {
            background: var(--surface);
            border-radius: 18px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
            text-align: left;
        }

        thead {
            background: #F8FAFC;
            border-bottom: 1.5px solid var(--border);
        }

        th {
            padding: 0.7rem 0.65rem;
            font-weight: 600;
            color: #475569;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid #F1F5F9;
            transition: background 0.15s;
            height: 48px;
        }

        tbody tr:hover {
            background-color: #F8FAFC;
        }

        td {
            padding: 0.55rem 0.65rem;
            vertical-align: middle;
            color: #334155;
            white-space: nowrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.55rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .badge-installed {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .badge-browser {
            background: #E0F2FE;
            color: #0369A1;
            border: 1px solid #BAE6FD;
        }

        .badge-online {
            background: #ECFDF5;
            color: #059669;
        }

        .badge-offline {
            background: #F1F5F9;
            color: #64748B;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #10B981;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        }

        /* Computer Name Column */
        .host-name-cell {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            max-width: 150px;
        }

        .host-name-cell:hover .host-name-text {
            color: var(--primary);
            text-decoration: underline;
        }

        .host-icon {
            font-size: 16px;
            color: var(--primary);
            flex-shrink: 0;
        }

        .host-name-text {
            font-weight: 600;
            color: #1E293B;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.84rem;
        }

        /* IP Address Column */
        .ip-cell-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .ip-badge-cell {
            font-family: monospace;
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 0.78rem;
            color: #0F172A;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .btn-copy-icon-mini {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 5px;
            color: #64748B;
            cursor: pointer;
            transition: all 0.15s;
            padding: 0;
        }

        .btn-copy-icon-mini:hover {
            background: #EFF6FF;
            color: var(--primary);
            border-color: #93C5FD;
        }

        /* Spec View Button (Click to open modal) */
        .btn-spec-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 3px 9px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 6px;
            font-size: 0.76rem;
            font-weight: 600;
            color: #1D4ED8;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            font-family: inherit;
            white-space: nowrap;
        }

        .btn-spec-pill:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
            color: #1E40AF;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.18);
        }

        .btn-spec-pill .material-symbols-Outlined {
            font-size: 15px;
            color: #2563EB;
        }

        .spec-empty-compact {
            color: #94A3B8;
            font-size: 0.76rem;
            font-style: italic;
            cursor: pointer;
        }

        /* Remote Compact (1 row) */
        .remote-compact-cell {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-remote-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 7px;
            background: linear-gradient(135deg, #059669, #047857);
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.73rem;
            font-weight: 600;
            box-shadow: 0 1px 4px rgba(5, 150, 105, 0.2);
            transition: all 0.15s;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-remote-pill:hover {
            background: linear-gradient(135deg, #047857, #065F46);
            transform: translateY(-1px);
        }

        .btn-remote-pill.vnc-unready {
            background: linear-gradient(135deg, #D97706, #B45309);
            box-shadow: 0 1px 4px rgba(217, 119, 6, 0.25);
        }

        .btn-remote-pill.vnc-unready:hover {
            background: linear-gradient(135deg, #B45309, #92400E);
        }

        .btn-anydesk-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: #FFF1F2;
            color: #BE123C;
            border: 1px solid #FECDD3;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-anydesk-pill:hover {
            background: #FFE4E6;
            border-color: #FDA4AF;
            transform: translateY(-1px);
        }

        .uptime-badge-cell {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            color: #0369A1;
            white-space: nowrap;
        }

        .badge-risk-warning {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: #FEF2F2;
            color: #B91C1C;
            border: 1px solid #FECACA;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-risk-warning:hover {
            background: #FEE2E2;
            border-color: #FCA5A5;
            color: #991B1B;
            transform: translateY(-1px);
        }

        .badge-risk-safe {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 7px;
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .btn-copy-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s;
            padding: 0;
        }

        .btn-copy-icon:hover {
            background: #F1F5F9;
            color: #0F172A;
            border-color: #94A3B8;
        }

        /* Compact Dept */
        .dept-compact-tag {
            background: #F3E8FF;
            color: #6B21A8;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 500;
            display: inline-block;
            max-width: 135px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dept-empty {
            color: #94A3B8;
            font-style: italic;
            font-size: 0.78rem;
        }

        /* Staff Name Cell */
        .staff-compact-cell {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            color: #1D4ED8;
            padding: 2px 8px;
            border-radius: 7px;
            max-width: 175px;
            transition: all 0.15s;
        }

        .staff-compact-cell:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
            color: #1E40AF;
        }

        .staff-badge-icon {
            font-size: 15px;
            color: #2563EB;
            flex-shrink: 0;
        }

        .staff-name-text {
            font-size: 0.82rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Compact User & OS */
        .user-os-compact {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            max-width: 155px;
        }

        .user-name-compact {
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.82rem;
            max-width: 70px;
        }

        .os-mini-tag {
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            color: #475569;
            padding: 1px 4px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .office-mini-tag {
            background: #FFF7ED;
            border: 1px solid #FED7AA;
            color: #C2410C;
            padding: 1px 4px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .office-mini-tag.office-none {
            background: #F8FAFC;
            border-color: #E2E8F0;
            color: #94A3B8;
        }

        .office-mini-tag.office-m365 {
            background: #EFF6FF;
            border-color: #BFDBFE;
            color: #1D4ED8;
        }

        /* Compact Launch & Type */
        .launch-compact {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .launch-count-badge {
            font-weight: 600;
            color: var(--primary);
            font-size: 0.82rem;
        }

        .app-type-tag {
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 0.69rem;
            font-weight: 500;
        }

        .app-type-tag.installed {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .app-type-tag.web {
            background: #E0F2FE;
            color: #0369A1;
            border: 1px solid #BAE6FD;
        }

        /* Compact Notes */
        .note-compact-cell {
            font-size: 0.82rem;
            color: #334155;
            cursor: pointer;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: color 0.15s;
        }

        .note-compact-cell:hover {
            color: var(--primary);
        }

        .note-text {
            font-weight: 500;
            color: #1E293B;
        }

        .note-empty {
            color: #94A3B8;
            font-style: italic;
            font-size: 0.78rem;
        }

        /* Compact Action Buttons */
        .table-actions-cell {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .btn-action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 27px;
            height: 27px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: #FFFFFF;
            cursor: pointer;
            transition: all 0.15s;
            padding: 0;
        }

        .btn-action-view {
            color: #0284C7;
            border-color: #BAE6FD;
        }
        .btn-action-view:hover {
            background: #E0F2FE;
            color: #0369A1;
            border-color: #7DD3FC;
            transform: translateY(-1px);
        }

        .btn-action-edit {
            color: var(--primary);
            border-color: #BFDBFE;
        }
        .btn-action-edit:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        .btn-action-reset {
            color: #D97706;
            border-color: #FDE68A;
        }
        .btn-action-reset:hover {
            background: #FEF3C7;
            border-color: #F59E0B;
            transform: translateY(-1px);
        }

        .btn-action-delete {
            color: #DC2626;
            border-color: #FECACA;
        }
        .btn-action-delete:hover {
            background: #FEE2E2;
            border-color: #EF4444;
            transform: translateY(-1px);
        }

        .btn-action-update {
            color: #7C3AED;
            border-color: #DDD6FE;
            background: #FAF5FF;
        }
        .btn-action-update:hover {
            background: #EDE9FE;
            color: #6D28D9;
            border-color: #8B5CF6;
            transform: translateY(-1px);
        }
        .btn-action-update.needs-update {
            background: #FEF3C7;
            color: #D97706;
            border-color: #FCD34D;
            animation: pulse-update-border 2s infinite;
        }

        .btn-action-refresh {
            color: #059669;
            border-color: #A7F3D0;
            background: #ECFDF5;
        }
        .btn-action-refresh:hover {
            background: #D1FAE5;
            color: #047857;
            border-color: #6EE7B7;
            transform: translateY(-1px);
        }
        .btn-action-refresh.spinning .material-symbols-Outlined {
            animation: spinMini 0.8s linear infinite;
        }

        .btn-action-resync {
            color: #2563EB;
            border-color: #BFDBFE;
            background: #EFF6FF;
        }
        .btn-action-resync:hover {
            background: #DBEAFE;
            color: #1D4ED8;
            border-color: #93C5FD;
            transform: translateY(-1px);
        }
        .btn-action-resync.spinning .material-symbols-Outlined {
            animation: spinMini 0.8s linear infinite;
        }

        .btn-resync-all {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.48rem 0.9rem;
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-resync-all:hover {
            background: #DBEAFE;
            color: #1D4ED8;
            border-color: #93C5FD;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.15);
        }
        .btn-resync-all.spinning .material-symbols-Outlined {
            animation: spinMini 0.8s linear infinite;
        }

        @keyframes pulse-update-border {
            0% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.4); }
            70% { box-shadow: 0 0 0 4px rgba(217, 119, 6, 0); }
            100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0); }
        }

        .spin-mini {
            animation: spinMini 1.2s linear infinite;
            display: inline-block;
        }
        @keyframes spinMini {
            100% { transform: rotate(360deg); }
        }

        /* Version Tags */
        .ver-tag-current {
            background: #ECFDF5;
            color: #059669;
            border: 1px solid #A7F3D0;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 600;
        }
        .ver-tag-old {
            background: #FFFBEB;
            color: #B45309;
            border: 1px solid #FDE68A;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .ver-tag-old:hover {
            background: #FEF3C7;
            color: #92400E;
            border-color: #F59E0B;
        }
        .ver-tag-pending {
            background: #F5F3FF;
            color: #7C3AED;
            border: 1px solid #DDD6FE;
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 0.72rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .ver-tag-pending:hover {
            background: #EDE9FE;
            border-color: #8B5CF6;
            color: #6D28D9;
            transform: translateY(-1px);
        }

        .btn-clear-pending {
            background: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FECACA;
            border-radius: 8px;
            padding: 0 0.8rem;
            height: 35px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }
        .btn-clear-pending:hover {
            background: #FEE2E2;
            border-color: #EF4444;
            transform: translateY(-1px);
        }

        .btn-update-all {
            background: linear-gradient(135deg, #7C3AED, #6D28D9);
            border: none;
            padding: 0 0.85rem;
            height: 35px;
            border-radius: 8px;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
            box-sizing: border-box;
            box-shadow: 0 2px 6px rgba(124, 58, 237, 0.25);
        }
        .btn-update-all:hover {
            background: linear-gradient(135deg, #6D28D9, #5B21B6);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(124, 58, 237, 0.35);
            color: #FFFFFF;
        }
        .update-badge-pill {
            background: #F59E0B;
            color: #78350F;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-left: 2px;
        }

        /* Toolbar Download VNC Button */
        .btn-download-vnc {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            background: #F3E8FF;
            color: #6B21A8;
            border: 1px solid #D8B4FE;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-download-vnc:hover {
            background: #E9D5FF;
            color: #581C87;
            transform: translateY(-1px);
        }

        /* Toast Notification */
        .toast-notify {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0F172A;
            color: #FFFFFF;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            z-index: 9999;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }

        .toast-notify.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Details Modal Styles */
        #detailsModal {
            padding: 12px 18px;
        }

        #detailsModal .modal-card,
        .modal-card.modal-card-large {
            width: 96vw !important;
            max-width: 96vw !important;
            max-height: 94vh;
            display: flex;
            flex-direction: column;
            padding: 1.25rem 1.75rem;
            border-radius: 16px;
            box-sizing: border-box;
        }

        .details-modal-body {
            overflow-y: auto;
            padding-right: 4px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.1rem;
            align-items: stretch;
            margin-top: 0.5rem;
        }

        .detail-section {
            background: #F8FAFC;
            border: 1px solid var(--border);
            border-radius: 13px;
            padding: 0.9rem 1.1rem;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .detail-section-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.65rem;
            padding-bottom: 0.45rem;
            border-bottom: 1px dashed var(--border);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem 0.75rem;
            flex: 1;
        }

        @media (max-width: 1024px) {
            .details-modal-body {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            #detailsModal {
                padding: 6px;
            }
            #detailsModal .modal-card,
            .modal-card.modal-card-large {
                width: 98vw !important;
                max-width: 98vw !important;
                padding: 1rem;
                max-height: 98vh;
            }
            .details-modal-body {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .detail-item.full-width {
            grid-column: span 2;
        }

        .detail-item label {
            font-size: 0.73rem;
            color: #64748B;
            font-weight: 500;
        }

        .detail-value {
            font-size: 0.85rem;
            color: #1E293B;
            font-weight: 500;
            line-height: 1.4;
            word-break: break-word;
        }

        .detail-value.text-highlight {
            color: #1D4ED8;
            font-weight: 600;
        }

        .detail-value-with-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .ip-badge-lg {
            font-family: monospace;
            font-size: 0.92rem;
            font-weight: 700;
            background: #E2E8F0;
            color: #0F172A;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .btn-copy-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            font-size: 0.74rem;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-copy-chip:hover {
            background: #F1F5F9;
            color: #0F172A;
        }

        .remote-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .remote-banner-info {
            flex: 1;
            min-width: 220px;
        }

        .remote-banner-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-modal-vnc {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            background: linear-gradient(135deg, #059669, #047857);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
            transition: all 0.15s;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-modal-vnc:hover {
            background: linear-gradient(135deg, #047857, #065F46);
            transform: translateY(-1px);
        }

        .btn-modal-copy-vnc {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 9px;
            background: #FFFFFF;
            border: 1.5px solid #86EFAC;
            border-radius: 8px;
            font-size: 0.78rem;
            color: #047857;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-modal-copy-vnc:hover {
            background: #DCFCE7;
        }

        .btn-modal-rdp {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 9px;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-size: 0.78rem;
            color: #475569 !important;
            text-decoration: none;
            transition: all 0.15s;
        }

        .btn-modal-rdp:hover {
            background: #F1F5F9;
            color: #0F172A !important;
        }

        .btn-modal-edit-trigger {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0.5rem 0.9rem;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 10px;
            color: var(--primary);
            font-size: 0.83rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-modal-edit-trigger:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
        }

        .btn-download-toolbar {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            padding: 0 0.65rem;
            height: 35px;
            border-radius: 8px;
            color: #047857;
            font-weight: 600;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .btn-download-toolbar:hover {
            background: #D1FAE5;
            border-color: #6EE7B7;
            transform: translateY(-1px);
        }

        .btn-download-autosync {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            padding: 0 0.65rem;
            height: 35px;
            border-radius: 8px;
            color: #1D4ED8;
            font-weight: 600;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .btn-download-autosync:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
            transform: translateY(-1px);
        }

        .btn-reset-this {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            padding: 0 0.65rem;
            height: 35px;
            border-radius: 8px;
            color: #1D4ED8;
            font-weight: 500;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .btn-reset-this:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
            transform: translateY(-1px);
        }

        .btn-reset-all {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            padding: 0 0.65rem;
            height: 35px;
            border-radius: 8px;
            color: #64748B;
            font-weight: 500;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s;
            font-family: inherit;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .btn-reset-all:hover {
            background: #FEE2E2;
            border-color: #FCA5A5;
            color: #DC2626;
        }

        .btn-clear-offline {
            background: #FFFFFF;
            border: 1px solid #FECACA;
            padding: 0 0.65rem;
            height: 35px;
            border-radius: 8px;
            color: #DC2626;
            font-weight: 500;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s;
            font-family: inherit;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .btn-clear-offline:hover {
            background: #DC2626;
            border-color: #B91C1C;
            color: #FFFFFF;
        }

        .empty-state {
            text-align: center;
            padding: 3.5rem 1.5rem;
            color: var(--text-muted);
        }

        .empty-state .material-symbols-Outlined {
            font-size: 54px;
            color: #CBD5E1;
            margin-bottom: 0.8rem;
        }

        .pagination-container {
            padding: 1.1rem 1.5rem;
            background: #FFFFFF;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* --- Edit Modal --- */
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
            max-width: 500px;
            border-radius: 20px;
            padding: 1.8rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            position: relative;
            animation: modalFadeIn 0.2s ease-out;
        }

        .modal-card.modal-card-large {
            max-width: 96vw;
        }

        /* Confirmation Modal Styles */
        .modal-card-confirm {
            max-width: 440px !important;
            text-align: center;
            padding: 2.2rem 2rem 1.8rem;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
            animation: confirmPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes confirmPop {
            from { opacity: 0; transform: scale(0.92) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .confirm-icon-wrapper {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.1rem;
            transition: transform 0.2s;
        }

        .confirm-icon-wrapper .material-symbols-Outlined {
            font-size: 34px;
        }

        .confirm-icon-wrapper.theme-danger {
            background: #FEE2E2;
            color: #DC2626;
            box-shadow: 0 0 0 6px #FEF2F2;
        }

        .confirm-icon-wrapper.theme-warning {
            background: #FEF3C7;
            color: #D97706;
            box-shadow: 0 0 0 6px #FFFBEB;
        }

        .confirm-icon-wrapper.theme-info {
            background: #DBEAFE;
            color: #2563EB;
            box-shadow: 0 0 0 6px #EFF6FF;
        }

        .confirm-icon-wrapper.theme-primary {
            background: #EDE9FE;
            color: #7C3AED;
            box-shadow: 0 0 0 6px #F5F3FF;
        }

        .confirm-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 0.5rem;
        }

        .confirm-message {
            font-size: 0.92rem;
            color: #475569;
            line-height: 1.55;
            margin-bottom: 1rem;
        }

        .confirm-target-box {
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
            text-align: left;
        }

        .confirm-target-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #EEF2F6;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .confirm-target-info {
            min-width: 0;
            flex: 1;
        }

        .confirm-target-name {
            font-size: 0.92rem;
            font-weight: 700;
            color: #1E293B;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .confirm-target-sub {
            font-size: 0.78rem;
            font-family: monospace;
            color: #64748B;
        }

        .confirm-hint {
            font-size: 0.8rem;
            color: #991B1B;
            background: #FEF2F2;
            border: 1px dashed #FECACA;
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            margin-bottom: 1.2rem;
            line-height: 1.5;
            text-align: left;
        }

        .confirm-hint.hint-warning {
            color: #92400E;
            background: #FFFBEB;
            border-color: #FDE68A;
        }

        .confirm-hint.hint-info {
            color: #1E40AF;
            background: #EFF6FF;
            border-color: #BFDBFE;
        }

        .confirm-hint.hint-primary {
            color: #5B21B6;
            background: #F5F3FF;
            border-color: #DDD6FE;
        }

        .confirm-actions {
            display: flex;
            gap: 10px;
            margin-top: 0.8rem;
        }

        .btn-confirm-cancel {
            flex: 1;
            padding: 0.7rem 1.1rem;
            border-radius: 11px;
            border: 1.5px solid #CBD5E1;
            background: #FFFFFF;
            color: #475569;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-confirm-cancel:hover {
            background: #F1F5F9;
            color: #1E293B;
            border-color: #94A3B8;
        }

        .btn-confirm-proceed {
            flex: 1.35;
            padding: 0.7rem 1.2rem;
            border-radius: 11px;
            border: none;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #FFFFFF;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-confirm-proceed.btn-theme-danger {
            background: #DC2626;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
        }
        .btn-confirm-proceed.btn-theme-danger:hover {
            background: #B91C1C;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
        }

        .btn-confirm-proceed.btn-theme-warning {
            background: #D97706;
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3);
        }
        .btn-confirm-proceed.btn-theme-warning:hover {
            background: #B45309;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(217, 119, 6, 0.4);
        }

        .btn-confirm-proceed.btn-theme-info {
            background: #2563EB;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        }
        .btn-confirm-proceed.btn-theme-info:hover {
            background: #1D4ED8;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4);
        }

        .btn-confirm-proceed.btn-theme-primary {
            background: linear-gradient(135deg, #7C3AED, #6D28D9);
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35);
        }
        .btn-confirm-proceed.btn-theme-primary:hover {
            background: linear-gradient(135deg, #6D28D9, #5B21B6);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(124, 58, 237, 0.45);
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h3 {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            display: flex;
            padding: 0.2rem;
            border-radius: 6px;
        }

        .modal-close:hover {
            color: var(--text-dark);
            background: #F1F5F9;
        }

        .modal-form-group {
            margin-bottom: 1.1rem;
        }

        .modal-form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.4rem;
        }

        .modal-input {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.92rem;
            font-family: inherit;
            outline: none;
        }

        .modal-input:focus {
            border-color: var(--primary);
        }

        .quick-depts {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.5rem;
        }

        .quick-dept-btn {
            font-size: 0.75rem;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #E2E8F0;
            cursor: pointer;
            transition: all 0.15s;
        }

        .quick-dept-btn:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            border-color: var(--primary);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.7rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .btn-modal-cancel {
            padding: 0.6rem 1.1rem;
            background: #F1F5F9;
            color: #64748B;
            border: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-family: inherit;
            cursor: pointer;
        }

        .btn-modal-save {
            padding: 0.6rem 1.3rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-modal-save:hover {
            background: var(--primary-dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.8rem 1rem;
            }
            .container {
                padding: 0.85rem;
            }
            .filter-form {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="navbar">
        <div class="brand-area">
            <div class="brand-icon">
                <span class="material-symbols-Outlined">desktop_windows</span>
            </div>
            <div class="brand-text">
                <h1>CSHOS STAFF INFO</h1>
                <p>ระบบติดตามและตรวจสอบการติดตั้งคอมพิวเตอร์ลูกข่าย</p>
            </div>
        </div>

        <div class="nav-actions">
            <a href="{{ route('download.shortcut.installer') }}" class="btn-download-app" title="ดาวน์โหลดตัวติดตั้ง Install-CSHOS_v{{ $serverVersion }}.bat">
                <span class="material-symbols-Outlined" style="font-size: 18px;">download</span>
                <span>downloadตัวติดตั้งApp (v{{ $serverVersion }})</span>
            </a>

            <a href="{{ route('download.uninstaller') }}" class="btn-download-uninstaller" title="ดาวน์โหลดตัวถอนการติดตั้งและลบข้อมูลระบบทั้งหมด">
                <span class="material-symbols-Outlined" style="font-size: 18px;">delete_sweep</span>
                <span>downloadตัวถอนการติดตั้ง</span>
            </a>

            <a href="{{ url('/') }}" target="_blank" class="btn-view-site">
                <span class="material-symbols-Outlined" style="font-size: 18px;">open_in_new</span>
                <span>เปิดหน้าหลักเว็บ</span>
            </a>

            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem; background: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s;" title="จัดการผู้ใช้งานระบบ เพิ่ม/ลบ/แก้ไข (Admin & IT)">
                    <span class="material-symbols-Outlined" style="font-size: 18px; color: #4F46E5;">manage_accounts</span>
                    <span>จัดการผู้ใช้งาน</span>
                </a>

                <a href="{{ route('admin.risk-rules.index') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem; background: #FFF1F2; color: #BE123C; border: 1px solid #FECDD3; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s;" title="จัดการเงื่อนไขตรวจสอบโปรแกรมเสี่ยง เพิ่ม/ลบ/แก้ไข">
                    <span class="material-symbols-Outlined" style="font-size: 18px; color: #E11D48;">shield_lock</span>
                    <span>จัดการโปรแกรมเสี่ยง</span>
                </a>

                <button type="button" class="btn-sync-server" onclick="triggerServerGitPull(this)" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem; background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" title="ดึงโค้ดล่าสุดจาก GitHub และล้างแคชบนเซิร์ฟเวอร์">
                    <span class="material-symbols-Outlined" style="font-size: 18px;">sync</span>
                    <span>อัปเดตเซิร์ฟเวอร์ (Git Pull)</span>
                </button>
            @endif

            <div class="user-menu">
                <div class="user-badge">
                    <span class="material-symbols-Outlined" style="font-size: 20px; color: var(--primary);">account_circle</span>
                    <span>{{ Auth::user()->fullname ?: (Auth::user()->name ?? 'ผู้ใช้งาน') }}</span>
                    @if(Auth::user()->isAdmin())
                        <span style="font-size: 0.7rem; padding: 2px 7px; background: #FEE2E2; color: #DC2626; border-radius: 9999px; font-weight: 700;">ADMIN</span>
                    @else
                        <span style="font-size: 0.7rem; padding: 2px 7px; background: #E0E7FF; color: #4338CA; border-radius: 9999px; font-weight: 700;">IT</span>
                    @endif
                </div>

                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">logout</span>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">

        @if(session('success'))
            <div class="alert alert-success">
                <span class="material-symbols-Outlined" style="font-size: 20px;">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Summary Statistics Cards -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <p>เครื่องที่ลงทะเบียนทั้งหมด</p>
                    <h2>{{ number_format($stats['total_devices']) }}</h2>
                </div>
                <div class="stat-icon blue">
                    <span class="material-symbols-Outlined">devices</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>ติดตั้งเป็นไอคอน (App)</p>
                    <h2>{{ number_format($stats['installed_count']) }}</h2>
                </div>
                <div class="stat-icon green">
                    <span class="material-symbols-Outlined">install_desktop</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>เปิดใช้งานวันนี้</p>
                    <h2>{{ number_format($stats['active_today']) }}</h2>
                </div>
                <div class="stat-icon purple">
                    <span class="material-symbols-Outlined">today</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>จำนวน IP ภายใน รพ.</p>
                    <h2>{{ number_format($stats['unique_ips']) }}</h2>
                </div>
                <div class="stat-icon amber">
                    <span class="material-symbols-Outlined">hub</span>
                </div>
            </div>

            @if(Auth::user()->isAdmin() && !empty($stats['risk_count']) && $stats['risk_count'] > 0)
                <div class="stat-card" style="border: 1px solid #FECACA; background: linear-gradient(135deg, #FEF2F2, #FFFFFF); cursor: pointer;" onclick="location.href='{{ route('admin.installations', ['status' => 'risk_only']) }}'" title="คลิกเพื่อกรองเฉพาะเครื่องที่พบโปรแกรมเสี่ยง">
                    <div class="stat-info">
                        <p style="color: #991B1B; font-weight: 700;">พบโปรแกรมเสี่ยง</p>
                        <h2 style="color: #DC2626;">{{ number_format($stats['risk_count']) }} <span style="font-size: 0.85rem; font-weight: 500;">เครื่อง</span></h2>
                    </div>
                    <div class="stat-icon red" style="background: #FEE2E2; color: #DC2626;">
                        <span class="material-symbols-Outlined">warning</span>
                    </div>
                </div>
            @endif
        </section>

        <!-- Search & Action Filters -->
        <section class="controls-card">
            <form action="{{ route('admin.installations') }}" method="GET" class="filter-form">
                <div class="filter-inputs">
                    <div class="search-box">
                        <span class="material-symbols-Outlined">search</span>
                        <input type="text" name="search" placeholder="ค้นหา IP, สเปก, แผนก, เครื่อง..." 
                               value="{{ request('search') }}">
                    </div>

                    <select name="department" class="select-filter">
                        <option value="">-- แผนกทั้งหมด --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="select-filter">
                        <option value="">-- ทุกสถานะ / ช่วงเวลา --</option>
                        <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>🟢 เฉพาะเครื่องออนไลน์</option>
                        <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>⚪ เฉพาะเครื่องออฟไลน์</option>
                        @if(Auth::user()->isAdmin())
                            <option value="risk_only" {{ request('status') == 'risk_only' ? 'selected' : '' }}>⚠️ ตรวจพบโปรแกรมเสี่ยง ({{ $stats['risk_count'] ?? 0 }})</option>
                        @endif
                        <option value="today" {{ request('status') == 'today' ? 'selected' : '' }}>เปิดใช้งานวันนี้</option>
                        <option value="week" {{ request('status') == 'week' ? 'selected' : '' }}>7 วันล่าสุด</option>
                        <option value="installed_only" {{ request('status') == 'installed_only' ? 'selected' : '' }}>ติดตั้งไอคอนแล้ว</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">tune</span>
                        <span>กรองข้อมูล</span>
                    </button>

                    @if(request()->hasAny(['search', 'department', 'status']))
                        <a href="{{ route('admin.installations') }}" class="btn-reset">
                            <span class="material-symbols-Outlined" style="font-size: 16px;">close</span>
                            <span>ล้างกรอง</span>
                        </a>
                    @endif
                </div>

                <div class="action-buttons">

                    @if(!empty($stats['pending_count']) && $stats['pending_count'] > 0)
                        <button type="button" class="btn-clear-pending" onclick="confirmClearAllPending({{ $stats['pending_count'] }})" title="ล้างสถานะรออัพเดททั้งหมด">
                            <span class="material-symbols-Outlined" style="font-size: 16px;">sync_disabled</span>
                            <span>ล้างสถานะรออัพเดท ({{ $stats['pending_count'] }})</span>
                        </button>
                    @endif

                    <button type="button" class="btn-resync-all" onclick="confirmResyncAllSpecs({{ $stats['total_devices'] ?? 0 }})" title="สั่งเครื่องลูกข่ายทุกเครื่องสแกนและอัปเดตสเปกฮาร์ดแวร์ใหม่ทันที">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">sync_saved_locally</span>
                        <span>ตรวจสเปกทุกเครื่อง</span>
                    </button>

                    <button type="button" class="btn-refresh" onclick="location.reload()">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">refresh</span>
                        <span>รีเฟรช</span>
                    </button>

                    <a href="{{ route('admin.installations.export') }}" class="btn-export">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">file_download</span>
                        <span>ส่งออก Excel</span>
                    </a>
                </div>
            </form>
        </section>

        <!-- Installations Table -->
        <section class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">ลำดับ</th>
                            <th style="width: 75px; text-align: center;">สถานะ</th>
                            <th style="width: 150px;">ชื่อเครื่องคอมพิวเตอร์</th>
                            <th style="width: 130px;">IP Address</th>
                            <th style="width: 85px; text-align: center;">รีโมท (VNC)</th>
                            <th style="width: 135px; text-align: center;">AnyDesk</th>
                            @if(Auth::user()->isAdmin())
                                <th style="width: 140px; text-align: center;">โปรแกรมเสี่ยง</th>
                            @endif
                            <th style="width: 135px; text-align: center;">System Uptime</th>
                            <th style="width: 160px;">ชื่อเจ้าหน้าที่</th>
                            <th style="width: 95px; text-align: center;">เวอร์ชัน</th>
                            <th style="width: 130px;">หมายเหตุ</th>
                            @if(Auth::user()->isAdmin())
                                <th style="width: 145px; text-align: center;">จัดการ</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installations as $index => $item)
                            @php
                                $isOnline = $item->computed_online ?? ($item->is_online && $item->last_active_at && $item->last_active_at->diffInMinutes(now()) <= 15);
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #64748B;">{{ $installations->firstItem() + $index }}</td>
                                <td style="text-align: center;">
                                    @if($isOnline)
                                        <span class="badge badge-online" id="status-badge-{{ $item->id }}">
                                            <span class="pulse-dot"></span> ออนไลน์
                                        </span>
                                    @else
                                        <span class="badge badge-offline" id="status-badge-{{ $item->id }}">ออฟไลน์</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="host-name-cell" onclick="openDetailsModal({{ json_encode($item) }})" style="cursor: pointer;">
                                        <span class="material-symbols-Outlined host-icon">computer</span>
                                        <span class="host-name-text">{{ $item->hostname ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($item->ip_address)
                                        <div class="ip-cell-wrapper">
                                            <span class="ip-badge-cell font-mono">{{ $item->ip_address }}</span>
                                            <button type="button" class="btn-copy-icon-mini" onclick="copyTextToClipboard('{{ $item->ip_address }}', 'คัดลอก IP: {{ $item->ip_address }} สำเร็จ!')">
                                                <span class="material-symbols-Outlined" style="font-size: 13px;">content_copy</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="spec-empty-compact">ไม่พบ IP</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($item->ip_address)
                                        <div class="remote-compact-cell" style="justify-content: center;">
                                            <button type="button" 
                                                    id="btn-remote-{{ $item->id }}"
                                                    class="btn-remote-pill {{ !$item->remote_ready ? 'vnc-unready' : '' }}" 
                                                    onclick="launchVnc('{{ $item->ip_address }}', {{ $item->remote_ready ? 'true' : 'false' }}, '{{ addslashes($item->hostname ?: ($item->device_name ?: $item->ip_address)) }}')"
                                                    title="{{ $item->remote_ready ? 'Remote VNC (พร้อมเชื่อมต่อ)' : 'พอร์ต VNC (5900) ยังไม่ตอบสนอง' }}">
                                                <span class="material-symbols-Outlined" style="font-size: 13px;">screen_share</span>
                                                <span>Remote</span>
                                                @if(!$item->remote_ready)
                                                    <span class="material-symbols-Outlined remote-warn-icon" style="font-size: 12px; margin-left: 1px;" title="พอร์ต 5900 ปิดอยู่">warning</span>
                                                @endif
                                            </button>
                                        </div>
                                    @else
                                        <span class="spec-empty-compact">ไม่พบ IP</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($item->anydesk_installed)
                                        <div style="display: inline-flex; align-items: center; justify-content: center; gap: 3px;">
                                            <button type="button" class="btn-anydesk-pill" 
                                                    onclick="{{ $item->anydesk_id ? 'launchAnydesk(\'' . str_replace(' ', '', $item->anydesk_id) . '\')' : 'openDetailsModal(' . json_encode($item) . ')' }}"
                                                    title="{{ $item->anydesk_id ? 'AnyDesk ID: ' . $item->anydesk_id . ' (คลิกเพื่อเชื่อมต่อ)' : 'เครื่องนี้มีโปรแกรม AnyDesk ติดตั้งอยู่' }}">
                                                <span class="material-symbols-Outlined" style="font-size: 13px; color: #E11D48;">screen_share</span>
                                                @if($item->anydesk_id)
                                                    <span style="font-family: monospace; font-weight: 700;">{{ $item->anydesk_id }}</span>
                                                @else
                                                    <span>ติดตั้งแล้ว</span>
                                                @endif
                                            </button>
                                            @if($item->anydesk_id)
                                                <button type="button" class="btn-copy-icon-mini" onclick="copyTextToClipboard('{{ str_replace(' ', '', $item->anydesk_id) }}', 'คัดลอก AnyDesk ID: {{ $item->anydesk_id }} สำเร็จ!')" title="คัดลอก AnyDesk ID">
                                                    <span class="material-symbols-Outlined" style="font-size: 12px;">content_copy</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="spec-empty-compact" style="color: #94A3B8;">-</span>
                                    @endif
                                </td>
                                @if(Auth::user()->isAdmin())
                                    <td style="text-align: center;" id="cell-risk-{{ $item->id }}">
                                        @if($item->has_risk_software && !empty($item->risk_software_list))
                                            <div style="display: inline-flex; align-items: center; justify-content: center; cursor: pointer;"
                                                 onclick="openDetailsModal({{ json_encode($item) }})" 
                                                 title="ตรวจพบโปรแกรมเสี่ยง: {{ $item->risk_software_list }} (คลิกเพื่อดูรายละเอียด)">
                                                <span class="badge-risk-warning">
                                                    <span class="material-symbols-Outlined" style="font-size: 13px;">warning</span>
                                                    <span>{{ Str::limit($item->risk_software_list, 18) }}</span>
                                                </span>
                                            </div>
                                        @else
                                            <span class="badge-risk-safe" title="ไม่พบโปรแกรมเสี่ยง (TeamViewer, Chrome Remote Desktop, UltraViewer, RustDesk, Windows App, BlueStacks, BlueStacks X, Android Emulator)">
                                                <span class="material-symbols-Outlined" style="font-size: 12px;">check_circle</span>
                                                <span>ไม่พบ</span>
                                            </span>
                                        @endif
                                    </td>
                                @endif
                                <td style="text-align: center;">
                                    @php
                                        $uptime = $item->dynamic_uptime ?: $item->uptime_text;
                                    @endphp
                                    @if($uptime)
                                        <div class="uptime-badge-cell" title="{{ $item->last_boot_time ? 'เปิดเครื่องเมื่อ: ' . $item->last_boot_time->format('d/m/Y H:i น.') : '' }}">
                                            <span class="material-symbols-Outlined" style="font-size: 13px; color: #0284C7;">timer</span>
                                            <span>{{ $uptime }}</span>
                                        </div>
                                    @else
                                        <span class="spec-empty-compact" style="color: #94A3B8;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->staff_name)
                                        <div class="staff-compact-cell" onclick="openDetailsModal({{ json_encode($item) }})" style="cursor: pointer;">
                                            <span class="material-symbols-Outlined staff-badge-icon">badge</span>
                                            <span class="staff-name-text">{{ $item->staff_name }}</span>
                                        </div>
                                    @elseif($item->department)
                                        <span class="dept-compact-tag" onclick="openDetailsModal({{ json_encode($item) }})" style="cursor: pointer;">
                                            {{ $item->department }}
                                        </span>
                                    @else
                                        <span class="dept-empty" onclick="openEditModal({{ json_encode($item) }})" style="cursor: pointer;">ยังไม่ระบุ</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($item->pending_update)
                                        <span class="ver-tag-pending" onclick="openPendingUpdateModal({{ json_encode($item) }})" title="คลิกเพื่อจัดการสถานะรออัพเดท (ตรวจสอบ / ยืนยัน / ยกเลิก)">
                                            <span class="material-symbols-Outlined spin-mini" style="font-size: 12px;">sync</span>
                                            <span>รออัพเดท</span>
                                        </span>
                                    @elseif($item->client_version && $item->client_version === $serverVersion)
                                        <span class="ver-tag-current font-mono" title="เวอร์ชันล่าสุด v{{ $serverVersion }}" style="display: inline-flex; align-items: center; gap: 3px;">
                                            <span class="material-symbols-Outlined" style="font-size: 11px;">check_circle</span>
                                            <span>v{{ $item->client_version }}</span>
                                        </span>
                                    @else
                                        <span class="ver-tag-old font-mono" title="เวอร์ชัน v{{ $item->client_version ?: '1.0.0' }} (เซิร์ฟเวอร์ v{{ $serverVersion }})" style="display: inline-flex; align-items: center; gap: 2px;">
                                            <span>v{{ $item->client_version ?: '1.0.0' }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="note-compact-cell" onclick="openEditModal({{ json_encode($item) }})" style="cursor: pointer;" title="{{ $item->notes ? $item->notes . ' (คลิกเพื่อแก้ไข)' : 'คลิกเพื่อระบุหมายเหตุ' }}">
                                        @if($item->notes)
                                            <span class="note-text">{{ $item->notes }}</span>
                                        @else
                                            <span class="note-empty">-</span>
                                        @endif
                                    </div>
                                </td>
                                @if(Auth::user()->isAdmin())
                                    <td style="text-align: center;">
                                        <div class="table-actions-cell">
                                            <button type="button" class="btn-action-icon btn-action-refresh" 
                                                    onclick="refreshSingleDevice('{{ route('admin.installations.refresh', $item->id) }}', '{{ addslashes($item->hostname ?: '') }}', '{{ $item->ip_address }}', this, {{ $item->id }})" 
                                                    title="รีเฟรชสถานะ / ตรวจสอบการเชื่อมต่อเครื่องนี้">
                                                <span class="material-symbols-Outlined" style="font-size: 15px;">refresh</span>
                                            </button>
                                            <button type="button" class="btn-action-icon btn-action-resync" 
                                                    onclick="resyncSingleDevice('{{ route('admin.installations.resync-specs', $item->id) }}', '{{ addslashes($item->hostname ?: '') }}', '{{ $item->ip_address }}', this, {{ $item->id }})" 
                                                    title="สั่งตรวจสอบสเปกเครื่องใหม่ (CPU, RAM, DISK, OS, Office)">
                                                <span class="material-symbols-Outlined" style="font-size: 15px;">sync_saved_locally</span>
                                            </button>
                                            <button type="button" class="btn-action-icon btn-action-view" 
                                                    onclick="openDetailsModal({{ json_encode($item) }})" title="ดูรายละเอียดสเปก">
                                                <span class="material-symbols-Outlined" style="font-size: 15px;">visibility</span>
                                            </button>
                                            <button type="button" class="btn-action-icon btn-action-edit" 
                                                    onclick="openEditModal({{ json_encode($item) }})" title="แก้ไขข้อมูล">
                                                <span class="material-symbols-Outlined" style="font-size: 15px;">edit</span>
                                            </button>
                                            <button type="button" class="btn-action-icon btn-action-delete"
                                                    onclick="confirmDeleteRow('{{ route('admin.installations.destroy', $item->id) }}', '{{ addslashes($item->hostname ?: '') }}', '{{ $item->ip_address }}')" title="ลบข้อมูลเครื่อง">
                                                <span class="material-symbols-Outlined" style="font-size: 15px;">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? '12' : '10' }}">
                                    <div class="empty-state">
                                        <span class="material-symbols-Outlined">devices_off</span>
                                        <h3>ยังไม่มีข้อมูลการติดตั้งในระบบ</h3>
                                        <p>เมื่อเจ้าหน้าที่เปิดใช้งานหรือกดติดตั้งไอคอน CSHOS STAFF INFO ระบบจะตรวจจับและบันทึกอัตโนมัติ</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($installations->hasPages())
                <div class="pagination-container">
                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        แสดง {{ $installations->firstItem() }} ถึง {{ $installations->lastItem() }} จากทั้งหมด {{ $installations->total() }} รายการ
                    </div>
                    <div>
                        {{ $installations->links() }}
                    </div>
                </div>
            @endif
        </section>

    </main>

    <!-- Edit Department Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>
                    <span class="material-symbols-Outlined" style="color: var(--primary);">edit_note</span>
                    <span>แก้ไขข้อมูลเครื่องคอมพิวเตอร์</span>
                </h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">
                    <span class="material-symbols-Outlined">close</span>
                </button>
            </div>

            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="modal-form-group">
                    <label>ที่อยู่ IP & Hostname</label>
                    <input type="text" id="modalIpHost" class="modal-input" readonly style="background: #F1F5F9; color: #64748B;">
                </div>

                <div class="modal-form-group">
                    <label>สเปกเครื่อง & สถานะ Remote Desktop</label>
                    <div id="modalSpecsDisplay" style="font-size: 0.82rem; color: #334155; background: #F8FAFC; border: 1.5px solid var(--border); border-radius: 10px; padding: 0.65rem 0.85rem; line-height: 1.6;">
                    </div>
                </div>

                <div class="modal-form-group">
                    <label for="modalDept">แผนก / หน่วยงาน</label>
                    <input type="text" id="modalDept" name="department" class="modal-input" placeholder="เช่น OPD, IPD, ER, ห้องยา, ห้องผ่าตัด...">
                    
                    <div class="quick-depts">
                        <span style="font-size: 0.75rem; color: #94A3B8; margin-right: 0.2rem;">เลือกด่วน:</span>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('ผู้ป่วยนอก (OPD)')">OPD</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('ผู้ป่วยใน (IPD)')">IPD</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('อุบัติเหตุ-ฉุกเฉิน (ER)')">ER</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('ห้องยา')">ห้องยา</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('ห้องชันสูตร (Lab)')">Lab</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('รังสีวิทยา (X-Ray)')">X-Ray</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('ทันตกรรม')">ทันตกรรม</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('เวชระเบียน')">เวชระเบียน</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('ศูนย์คอมพิวเตอร์/IT')">IT</button>
                        <button type="button" class="quick-dept-btn" onclick="setQuickDept('บริหารทั่วไป/การเงิน')">การเงิน</button>
                    </div>
                </div>

                <div class="modal-form-group">
                    <label for="modalDeviceName">ชื่อเครื่อง / ผู้ใช้งานประจำ</label>
                    <input type="text" id="modalDeviceName" name="device_name" class="modal-input" placeholder="เช่น PC-OPD-01, พยาบาลวิชาชีพสมศรี">
                </div>

                <div class="modal-form-group">
                    <label for="modalNotes">หมายเหตุ</label>
                    <input type="text" id="modalNotes" name="notes" class="modal-input" placeholder="ระบุหมายเหตุเพิ่มเติม (ถ้ามี)">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">ยกเลิก</button>
                    <button type="submit" class="btn-modal-save">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">save</span>
                        <span>บันทึกข้อมูล</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Machine Details Modal -->
    <div class="modal-overlay" id="detailsModal">
        <div class="modal-card modal-card-large">
            <div class="modal-header">
                <h3>
                    <span class="material-symbols-Outlined" style="color: var(--primary); font-size: 22px;">desktop_windows</span>
                    <span id="dtHostname">รายละเอียดเครื่องคอมพิวเตอร์</span>
                </h3>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="dtStatusBadge" class="badge"></span>
                    <button type="button" class="modal-close" onclick="closeDetailsModal()">
                        <span class="material-symbols-Outlined">close</span>
                    </button>
                </div>
            </div>

            <div class="details-modal-body">
                <!-- Section 1: สเปกฮาร์ดแวร์ & ซอฟต์แวร์ -->
                <div class="detail-section">
                    <div class="detail-section-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-Outlined" style="color: #2563EB;">memory</span>
                            <span>สเปกฮาร์ดแวร์ & ซอฟต์แวร์ระบบ (Hardware & System Specs)</span>
                        </div>
                        <button type="button" class="btn-resync-chip" onclick="resyncSpecsFromDetails(this)" title="สั่งเครื่องนี้สแกนและอัปเดตสเปกใหม่ทันที" style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; font-size: 0.74rem; font-weight: 600; color: #1D4ED8; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 6px; cursor: pointer;">
                            <span class="material-symbols-Outlined" style="font-size: 14px;">sync_saved_locally</span>
                            <span>ตรวจสเปกใหม่</span>
                        </button>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item full-width">
                            <label>หน่วยประมวลผลกลาง (CPU)</label>
                            <div id="dtCpu" class="detail-value text-highlight">-</div>
                        </div>
                        <div class="detail-item">
                            <label>หน่วยความจำ (RAM)</label>
                            <div id="dtRam" class="detail-value text-highlight">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ระบบปฏิบัติการ & เวอร์ชั่น (OS & Version)</label>
                            <div id="dtOs" class="detail-value text-highlight">-</div>
                        </div>
                        <div class="detail-item full-width">
                            <label>ชุดโปรแกรมสำนักงาน (Microsoft Office Version)</label>
                            <div id="dtOffice" class="detail-value text-highlight" style="background: #FFF7ED; border-color: #FED7AA; color: #C2410C;">-</div>
                        </div>
                        <div class="detail-item full-width">
                            <label>พื้นที่จัดเก็บข้อมูลฮาร์ดดิสก์ (DISK)</label>
                            <div id="dtDisk" class="detail-value font-mono" style="font-size: 0.82rem; background: #FFF; padding: 5px 8px; border-radius: 6px; border: 1px solid #E2E8F0;">-</div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: ข้อมูลเครือข่าย & ผู้ใช้งาน -->
                <div class="detail-section">
                    <div class="detail-section-title">
                        <span class="material-symbols-Outlined" style="color: #059669;">lan</span>
                        <span>ข้อมูลเครือข่าย & ผู้ใช้งาน (Network & Identity)</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>หมายเลข IP Address (DHCP)</label>
                            <div class="detail-value-with-btn">
                                <span id="dtIp" class="ip-badge-lg">-</span>
                                <button type="button" class="btn-copy-chip" onclick="copyCurrentIp()">
                                    <span class="material-symbols-Outlined" style="font-size: 13px;">content_copy</span>
                                    <span>คัดลอก</span>
                                </button>
                            </div>
                        </div>
                        <div class="detail-item">
                            <label>หมายเลข MAC Address (ฮาร์ดแวร์คงที่)</label>
                            <div id="dtMac" class="detail-value font-mono" style="font-weight: 600; color: #475569;">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ชื่อเครื่อง (Computer Name)</label>
                            <div id="dtCompName" class="detail-value" style="font-weight: 700; color: #0F172A;">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ชื่อเจ้าหน้าที่ (ผู้ใช้งาน CSHOS)</label>
                            <div id="dtStaff" class="detail-value text-highlight" style="font-size: 0.92rem;">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ผู้ใช้งานประจำเครื่อง / อุปกรณ์</label>
                            <div id="dtDeviceName" class="detail-value">-</div>
                        </div>
                        <div class="detail-item full-width">
                            <label>รหัสประจำอุปกรณ์ (Device UUID)</label>
                            <div id="dtUuid" class="detail-value font-mono text-muted" style="font-size: 0.76rem;">-</div>
                        </div>
                        <div class="detail-item full-width" style="background: #FFF1F2; border: 1px solid #FECDD3; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <label style="color: #BE123C; font-weight: 700; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-Outlined" style="font-size: 16px;">screen_share</span>
                                    <span>โปรแกรมรีโมท AnyDesk</span>
                                </label>
                                <div id="dtAnyDeskStatus" style="font-weight: 600; color: #9F1239; font-size: 0.88rem;">-</div>
                            </div>
                            <div id="dtAnyDeskActions" style="display: none; gap: 6px; align-items: center;">
                                <button type="button" class="btn-copy-chip" onclick="copyAnydeskId()" title="คัดลอกหมายเลข AnyDesk ID">
                                    <span class="material-symbols-Outlined" style="font-size: 13px;">content_copy</span>
                                    <span>คัดลอก ID</span>
                                </button>
                                <a id="dtAnyDeskLink" href="#" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: #E11D48; color: white; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-decoration: none;">
                                    <span class="material-symbols-Outlined" style="font-size: 14px;">open_in_new</span>
                                    <span>เปิด AnyDesk</span>
                                </a>
                            </div>
                        </div>
                        @if(Auth::user()->isAdmin())
                            <div class="detail-item full-width" id="dtRiskSoftwareBox" style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 8px 12px; margin-top: 4px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                    <div style="flex: 1;">
                                        <label style="color: #B45309; font-weight: 700; margin-bottom: 3px; display: flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-Outlined" style="font-size: 16px;">security</span>
                                            <span>การตรวจสอบโปรแกรมเสี่ยง (Risk Software Check)</span>
                                        </label>
                                        <div id="dtRiskSoftwareStatus" style="font-weight: 600; color: #92400E; font-size: 0.88rem;">-</div>
                                    </div>
                                    <button type="button" id="btnResyncRiskRealtime" onclick="resyncRiskFromDetails(this)" title="สั่งตรวจสอบโปรแกรมเสี่ยงตามกฎปัจจุบันแบบเรียลไทม์ทันที" style="display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                                        <span class="material-symbols-Outlined" style="font-size: 15px; color: #0284C7;">sync</span>
                                        <span>ตรวจสอบเรียลไทม์</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Section 3: ประวัติการใช้งาน & วันที่บันทึก -->
                <div class="detail-section">
                    <div class="detail-section-title">
                        <span class="material-symbols-Outlined" style="color: #D97706;">history</span>
                        <span>สถิติการใช้งาน & วันที่บันทึก (Activity & History)</span>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>จำนวนครั้งที่เปิดใช้งาน</label>
                            <div id="dtLaunchCount" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ประเภทการติดตั้ง</label>
                            <div id="dtInstallType" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ติดตั้งครั้งแรก</label>
                            <div id="dtFirstInstall" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ใช้งานล่าสุด</label>
                            <div id="dtLastActive" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>เวลาเปิดเครื่องล่าสุด (Last Boot)</label>
                            <div id="dtLastBoot" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>เปิดใช้งานต่อเนื่องมาแล้ว (System Uptime)</label>
                            <div id="dtUptime" class="detail-value" style="font-weight: 700; color: #0284C7;">-</div>
                        </div>
                        <div class="detail-item full-width" style="background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 8px; padding: 6px 12px; display: flex; align-items: center; justify-content: space-between; margin-top: 4px;">
                            <div>
                                <label style="color: #6D28D9; font-weight: 600; margin-bottom: 2px;">เวอร์ชันโปรแกรม (Client Version)</label>
                                <div id="dtClientVersion" style="font-weight: 700; color: #4C1D95; font-size: 0.92rem;">-</div>
                            </div>
                        </div>
                        <div class="detail-item full-width" id="dtNotesContainer">
                            <label>หมายเหตุ</label>
                            <div id="dtNotes" class="detail-value" style="font-style: italic;">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="justify-content: space-between;">
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn-modal-edit-trigger" onclick="switchFromDetailsToEdit()">
                        <span class="material-symbols-Outlined" style="font-size: 16px;">edit</span>
                        <span>แก้ไขข้อมูลแผนก / ผู้ใช้</span>
                    </button>
                    <button type="button" onclick="resyncSpecsFromDetails(this)" style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 13px; background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                        <span class="material-symbols-Outlined" style="font-size: 16px;">sync_saved_locally</span>
                        <span>สั่งตรวจสอบสเปกใหม่</span>
                    </button>
                </div>
                <button type="button" class="btn-modal-cancel" onclick="closeDetailsModal()">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- Pending Update Management Modal -->
    <div class="modal-overlay" id="pendingUpdateModal" style="display: none;">
        <div class="modal-card modal-card-confirm" style="max-width: 540px; text-align: left;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border); padding-bottom: 0.8rem; margin-bottom: 1rem;">
                <h3 style="display: flex; align-items: center; gap: 8px; font-size: 1.1rem; color: #6D28D9;">
                    <span class="material-symbols-Outlined spin-mini" style="font-size: 24px;">sync</span>
                    <span>จัดการสถานะรออัพเดท (Pending Update)</span>
                </h3>
                <button type="button" class="modal-close" onclick="closePendingUpdateModal()">
                    <span class="material-symbols-Outlined">close</span>
                </button>
            </div>

            <div style="background: #F5F3FF; border: 1.5px solid #DDD6FE; border-radius: 12px; padding: 1rem; margin-bottom: 1.1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: #6D28D9; font-weight: 600;">ชื่อเครื่อง / IP Address</div>
                        <div id="pendingHostIp" style="font-size: 1.05rem; font-weight: 700; color: #2E1065;">-</div>
                    </div>
                    <span class="badge" style="background: #EDE9FE; color: #6D28D9; border: 1px solid #C4B5FD; font-size: 0.75rem;">
                        เป้าหมาย: v{{ $serverVersion }}
                    </span>
                </div>
                <div style="font-size: 0.8rem; color: #4C1D95; display: flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-Outlined" style="font-size: 16px;">schedule</span>
                    <span id="pendingTimeText">ส่งคำสั่งเมื่อ: -</span>
                </div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.2rem; font-size: 0.82rem; color: #334155; line-height: 1.6;">
                <strong style="color: #0F172A; display: block; margin-bottom: 4px;">💡 เหตุผลที่เครื่องยังอยู่ในสถานะ "รออัพเดท":</strong>
                • เครื่องลูกข่ายจะเริ่มดาวน์โหลดและติดตั้งเวอร์ชันใหม่ <strong>v{{ $serverVersion }}</strong> อัตโนมัติเมื่อ:
                <div style="margin: 4px 0 0 12px; color: #475569;">
                    1. <strong>มีการเปิดเครื่องใหม่หรือรีสตาร์ท (Reboot)</strong><br>
                    2. <strong>เจ้าหน้าที่เปิดใช้งานหน้าเว็บ CSHOS STAFF INFO บนเบราว์เซอร์</strong><br>
                    3. <strong>ตามรอบการซิงค์อัตโนมัติประจำชั่วโมง</strong>
                </div>
                <div style="font-size: 0.75rem; color: #94A3B8; margin-top: 6px;">
                    * ระบบความปลอดภัยของ Windows จะไม่อนุญาตให้เซิร์ฟเวอร์สั่งรันคำสั่งข้ามเครื่องผ่าน LAN ได้โดยตรง (Access Denied)
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <!-- Action 1: Mark as Updated -->
                <button type="button" id="btnMarkComplete" onclick="executeCompleteUpdate()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px; background: #059669; color: white; border: none; border-radius: 9px; font-weight: 600; font-size: 0.88rem; cursor: pointer; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);">
                    <span class="material-symbols-Outlined" style="font-size: 18px;">check_circle</span>
                    <span>ทำเครื่องหมายว่าอัพเดทแล้ว (บันทึกเป็น v{{ $serverVersion }})</span>
                </button>

                <!-- Action 2: Copy manual PowerShell command -->
                <button type="button" onclick="copyManualUpdateCommand()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px; background: #EEF2FF; color: #4338CA; border: 1.5px solid #C7D2FE; border-radius: 9px; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                    <span class="material-symbols-Outlined" style="font-size: 18px;">terminal</span>
                    <span>คัดลอกคำสั่งรันอัพเดททันที (นำไปวางใน PowerShell เครื่องลูก)</span>
                </button>

                <div style="display: flex; gap: 8px; margin-top: 4px;">
                    <!-- Action 3: Cancel pending -->
                    <button type="button" id="btnCancelPending" onclick="executeCancelUpdate()" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 8px; background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; border-radius: 8px; font-weight: 600; font-size: 0.82rem; cursor: pointer;">
                        <span class="material-symbols-Outlined" style="font-size: 16px;">close</span>
                        <span>ยกเลิกสถานะรออัพเดท</span>
                    </button>
                    <!-- Close button -->
                    <button type="button" onclick="closePendingUpdateModal()" style="flex: 1; padding: 8px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; border-radius: 8px; font-weight: 600; font-size: 0.82rem; cursor: pointer;">
                        ปิดหน้าต่าง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Beautiful Confirmation Modal -->
    <div class="modal-overlay" id="confirmActionModal">
        <div class="modal-card modal-card-confirm">
            <div class="confirm-icon-wrapper" id="confirmIconWrapper">
                <span class="material-symbols-Outlined" id="confirmIcon">warning</span>
            </div>
            
            <h3 class="confirm-title" id="confirmTitle">ยืนยันการดำเนินการ</h3>
            
            <p class="confirm-message" id="confirmMessage">
                คุณแน่ใจหรือไม่ว่าต้องการดำเนินการนี้?
            </p>

            <div class="confirm-target-box" id="confirmTargetBox" style="display: none;">
                <div class="confirm-target-icon">
                    <span class="material-symbols-Outlined" style="font-size: 20px;">computer</span>
                </div>
                <div class="confirm-target-info">
                    <div class="confirm-target-name" id="confirmTargetName">-</div>
                    <div class="confirm-target-sub" id="confirmTargetSub">-</div>
                </div>
            </div>

            <div class="confirm-hint" id="confirmHint" style="display: none;"></div>

            <div class="confirm-actions">
                <button type="button" class="btn-confirm-cancel" onclick="closeConfirmModal()">ยกเลิก</button>
                <button type="button" class="btn-confirm-proceed" id="confirmProceedBtn">
                    <span class="material-symbols-Outlined" id="confirmProceedIcon" style="font-size: 18px;">check</span>
                    <span id="confirmProceedText">ยืนยัน</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Beautiful Server Git Pull / Status Modal -->
    <div id="serverGitModal" class="modal-overlay" style="display: none; z-index: 1050;">
        <div class="modal-card" style="max-width: 600px; width: 100%; text-align: left; padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.4); border: 1px solid rgba(226, 232, 240, 0.8); animation: confirmPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
            <!-- Modal Header with Animated Icon -->
            <div id="serverGitHeader" style="padding: 1.8rem 1.8rem 1.2rem; text-align: center; border-bottom: 1px solid #F1F5F9; position: relative;">
                <button type="button" onclick="closeServerGitModal()" style="position: absolute; right: 1.2rem; top: 1.2rem; background: #F1F5F9; border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; color: #64748B; display: flex; align-items: center; justify-content: center; transition: all 0.15s;">&times;</button>
                
                <div id="serverGitIconWrapper" style="width: 64px; height: 64px; border-radius: 20px; margin: 0 auto 0.85rem; display: flex; align-items: center; justify-content: center; transition: all 0.3s; background: #EEF2FF; color: #4F46E5;">
                    <span class="material-symbols-Outlined" id="serverGitIcon" style="font-size: 34px;">sync</span>
                </div>
                
                <h3 id="serverGitTitle" style="font-size: 1.2rem; font-weight: 700; color: #0F172A; margin-bottom: 0.3rem;">อัปเดตระบบเซิร์ฟเวอร์</h3>
                <p id="serverGitSub" style="font-size: 0.88rem; color: #64748B; margin: 0 auto; max-width: 480px; line-height: 1.5;">กำลังเชื่อมต่อและดึงโค้ดล่าสุดจาก GitHub...</p>
                <div id="serverGitVersionBadge" style="margin-top: 0.6rem; display: none;"></div>
            </div>

            <!-- Modal Body (Terminal & Details) -->
            <div style="padding: 1.2rem 1.6rem; background: #F8FAFC;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem;">
                    <span style="font-size: 0.78rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.35rem;">
                        <span class="material-symbols-Outlined" style="font-size: 15px;">terminal</span>
                        ผลลัพธ์จาก Git & เซิร์ฟเวอร์
                    </span>
                    <button type="button" onclick="copyGitOutput()" style="background: #FFFFFF; border: 1px solid #CBD5E1; padding: 0.25rem 0.65rem; border-radius: 8px; font-size: 0.75rem; font-weight: 500; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; font-family: inherit; transition: all 0.15s;">
                        <span class="material-symbols-Outlined" style="font-size: 13px;">content_copy</span>
                        <span id="copyGitBtnText">คัดลอกผลลัพธ์</span>
                    </button>
                </div>
                
                <pre id="serverGitOutput" style="background: #0F172A; color: #38BDF8; font-family: 'Consolas', 'Courier New', monospace; font-size: 0.8rem; line-height: 1.55; padding: 0.9rem 1.1rem; border-radius: 12px; max-height: 190px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; margin: 0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); border: 1px solid #1E293B;"></pre>

                <!-- Helpful Guide Box if error occurs -->
                <div id="serverGitAdviceBox" style="display: none; margin-top: 0.85rem; padding: 0.85rem 1rem; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 12px; font-size: 0.82rem; color: #92400E; line-height: 1.5;"></div>
            </div>

            <!-- Modal Footer Buttons -->
            <div style="padding: 1rem 1.6rem; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 0.6rem; background: #FFFFFF;">
                <button type="button" class="btn-confirm-cancel" onclick="closeServerGitModal()" id="serverGitCloseBtn" style="padding: 0.5rem 1.1rem; font-size: 0.88rem; border-radius: 10px;">ปิดหน้าต่าง</button>
                <button type="button" class="btn-confirm-proceed" onclick="location.reload()" id="serverGitReloadBtn" style="display: none; padding: 0.5rem 1.25rem; font-size: 0.88rem; border-radius: 10px; background: #10B981; border-color: #059669; color: #FFFFFF; font-weight: 600;">
                    <span class="material-symbols-Outlined" style="font-size: 18px;">refresh</span>
                    <span>โหลดหน้านี้ใหม่ (Reload)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Dynamic POST/DELETE Actions -->
    <form id="genericActionForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="_method" id="genericActionMethod" value="POST">
    </form>

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-notify">
        <span class="material-symbols-Outlined" style="color: #34D399; font-size: 20px;">check_circle</span>
        <span id="toastMessage">คัดลอกคำสั่งสำเร็จ!</span>
    </div>

    <script>
        let currentDetailItem = null;

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // --- Beautiful Server Git Pull Modal System ---
        function triggerServerGitPull(btn) {
            openConfirmModal({
                type: 'primary',
                icon: 'sync',
                title: 'อัปเดตระบบเซิร์ฟเวอร์ (Git Pull)',
                message: 'ระบบจะทำการดึงโค้ดเวอร์ชันล่าสุดจาก GitHub (git pull origin main) รันการปรับปรุงฐานข้อมูลอัตโนมัติ และล้างแคชระบบบนเซิร์ฟเวอร์หลัก<br><br>ต้องการเริ่มดำเนินการเลยหรือไม่?',
                proceedIcon: 'sync',
                proceedText: 'เริ่มอัปเดตทันที',
                onConfirm: function() {
                    executeServerGitPull(btn);
                }
            });
        }

        function openServerGitModal() {
            const modal = document.getElementById('serverGitModal');
            if (modal) {
                modal.classList.add('active');
                modal.style.display = 'flex';
            }
        }

        function closeServerGitModal() {
            const modal = document.getElementById('serverGitModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        }

        function copyGitOutput() {
            const text = document.getElementById('serverGitOutput').innerText;
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                const btnText = document.getElementById('copyGitBtnText');
                if (btnText) {
                    btnText.innerText = 'คัดลอกแล้ว!';
                    setTimeout(() => { btnText.innerText = 'คัดลอกผลลัพธ์'; }, 2000);
                }
            });
        }

        function executeServerGitPull(btn) {
            const modal = document.getElementById('serverGitModal');
            const iconWrapper = document.getElementById('serverGitIconWrapper');
            const icon = document.getElementById('serverGitIcon');
            const title = document.getElementById('serverGitTitle');
            const sub = document.getElementById('serverGitSub');
            const output = document.getElementById('serverGitOutput');
            const badge = document.getElementById('serverGitVersionBadge');
            const reloadBtn = document.getElementById('serverGitReloadBtn');
            const adviceBox = document.getElementById('serverGitAdviceBox');

            // Set loading state
            openServerGitModal();
            iconWrapper.style.background = '#EEF2FF';
            iconWrapper.style.color = '#4F46E5';
            icon.textContent = 'sync';
            icon.style.animation = 'spin 1.2s linear infinite';
            title.textContent = 'กำลังอัปเดตระบบเซิร์ฟเวอร์...';
            sub.textContent = 'กำลังดึงโค้ดล่าสุดจาก GitHub และปรับปรุงฐานข้อมูล กรุณารอสักครู่ (ประมาณ 3-5 วินาที)';
            output.textContent = '[1/3] Connecting to Git repository...\n[2/3] Pulling origin main...\n[3/3] Running migrations and clearing caches...';
            badge.style.display = 'none';
            reloadBtn.style.display = 'none';
            adviceBox.style.display = 'none';

            if (btn) btn.disabled = true;

            fetch("{{ route('admin.installations.git-pull') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (btn) btn.disabled = false;
                icon.style.animation = 'none';

                if (data.success) {
                    // Success state
                    iconWrapper.style.background = '#D1FAE5';
                    iconWrapper.style.color = '#059669';
                    icon.textContent = 'check_circle';
                    title.textContent = 'อัปเดตระบบเซิร์ฟเวอร์สำเร็จ!';
                    sub.textContent = 'ระบบเซิร์ฟเวอร์ได้รับการอัปเกรดเป็นเวอร์ชันล่าสุดและล้างแคชเรียบร้อยแล้ว';
                    
                    if (data.version) {
                        badge.style.display = 'inline-block';
                        badge.innerHTML = `<span style="background: #D1FAE5; color: #065F46; padding: 0.3rem 0.85rem; border-radius: 20px; font-weight: 700; font-size: 0.88rem; border: 1px solid #A7F3D0;">เวอร์ชันปัจจุบัน: v${data.version}</span>`;
                    }

                    output.textContent = data.output || 'Git pull succeeded. All files are up to date.';
                    output.style.color = '#34D399';
                    reloadBtn.style.display = 'inline-flex';
                } else {
                    // Error state
                    iconWrapper.style.background = '#FEE2E2';
                    iconWrapper.style.color = '#DC2626';
                    icon.textContent = 'error';
                    title.textContent = 'พบข้อผิดพลาดในการดึงข้อมูล Git';
                    sub.textContent = 'Git รายงานข้อผิดพลาด ไม่สามารถดึงโค้ดลงมาได้ กรุณาตรวจสอบผลลัพธ์ด้านล่าง';

                    output.textContent = data.output || data.message || 'Unknown error occurred.';
                    output.style.color = '#F87171';
                    reloadBtn.style.display = 'none';

                    // Check if error is due to local changes conflict
                    const outText = (data.output || '') + ' ' + (data.message || '');
                    if (outText.includes('overwritten by merge') || outText.includes('Aborting')) {
                        adviceBox.style.display = 'block';
                        adviceBox.innerHTML = `
                            <div style="font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.3rem;">
                                <span class="material-symbols-Outlined" style="font-size: 17px; color: #D97706;">lightbulb</span>
                                วิธีแก้ไขปัญหานี้ (Local changes conflict):
                            </div>
                            <div>บนเซิร์ฟเวอร์มีการแก้ไขไฟล์ค้างไว้ ให้เปิด Terminal ของเซิร์ฟเวอร์ (192.168.1.15) แล้วรันคำสั่งนี้เพียงครั้งเดียว:</div>
                            <div style="background: #FEF3C7; padding: 0.5rem 0.75rem; border-radius: 8px; font-family: monospace; font-size: 0.78rem; margin-top: 0.4rem; user-select: all; border: 1px solid #FCD34D;">
                                cd /var/www/html/cshdigitalhealth && git checkout -- . && git pull origin main && php artisan migrate --force && php artisan optimize:clear
                            </div>
                        `;
                    }
                }
            })
            .catch(err => {
                if (btn) btn.disabled = false;
                icon.style.animation = 'none';
                iconWrapper.style.background = '#FEE2E2';
                iconWrapper.style.color = '#DC2626';
                icon.textContent = 'wifi_off';
                title.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                sub.textContent = 'ไม่สามารถส่งคำขอไปยังเซิร์ฟเวอร์ได้ กรุณาตรวจสอบสถานะเครือข่าย';
                output.textContent = err.message || 'Connection failed.';
                output.style.color = '#F87171';
            });
        }

        function openDetailsModal(item) {
            currentDetailItem = item;
            const modal = document.getElementById('detailsModal');
            if (!modal) return;

            // Header info
            document.getElementById('dtHostname').textContent = (item.hostname ? item.hostname : 'เครื่องคอมพิวเตอร์') + (item.ip_address ? ' (' + item.ip_address + ')' : '');
            
            // Status badge
            const isOnline = item.computed_online !== undefined 
                ? item.computed_online 
                : (item.is_online && item.last_active_at && (new Date() - new Date(item.last_active_at)) < 15 * 60 * 1000);
            const statusBadge = document.getElementById('dtStatusBadge');
            if (statusBadge) {
                if (isOnline) {
                    statusBadge.className = 'badge badge-online';
                    statusBadge.innerHTML = '<span class="pulse-dot"></span> ออนไลน์';
                } else {
                    statusBadge.className = 'badge badge-offline';
                    statusBadge.textContent = 'ออฟไลน์';
                }
            }

            // Hardware Specs & Software
            document.getElementById('dtCpu').textContent = item.cpu || 'ไม่สามารถระบุ CPU ได้';
            document.getElementById('dtRam').textContent = item.ram || 'ไม่สามารถระบุ RAM ได้';
            document.getElementById('dtDisk').textContent = item.disk || 'ไม่สามารถระบุ DISK ได้';
            document.getElementById('dtOs').textContent = item.os_version || item.os || 'Windows';
            const officeEl = document.getElementById('dtOffice');
            if (officeEl) {
                const offVal = item.office_version;
                if (!offVal) {
                    officeEl.textContent = 'ยังไม่มีข้อมูลการตรวจสอบ (รอการซิงก์ข้อมูลรอบถัดไป)';
                } else if (offVal === 'Not Installed') {
                    officeEl.textContent = 'ไม่ได้ติดตั้งโปรแกรม Microsoft Office บนเครื่องนี้';
                } else {
                    officeEl.textContent = offVal;
                }
            }

            // Network & Identity
            document.getElementById('dtIp').textContent = item.ip_address || 'ไม่พบ IP';
            const macEl = document.getElementById('dtMac');
            if (macEl) {
                macEl.textContent = item.mac_address || 'ยังไม่มีข้อมูล MAC (จะบันทึกเมื่อเครื่องซิงก์รอบถัดไป)';
            }
            document.getElementById('dtCompName').textContent = item.hostname || '-';
            const staffEl = document.getElementById('dtStaff');
            if (staffEl) {
                if (item.staff_name) {
                    staffEl.innerHTML = '<span style="color: #1D4ED8; font-weight: 700;">' + item.staff_name + '</span>' +
                        (item.staff_login ? ' <span style="font-size: 0.78rem; color: #64748B;">(' + item.staff_login + ')</span>' : '') +
                        (item.hos_dept ? '<div style="font-size: 0.78rem; color: #059669; margin-top: 2px;">' + item.hos_dept + '</div>' : '');
                } else if (item.department) {
                    staffEl.textContent = item.department;
                } else {
                    staffEl.textContent = 'ยังไม่ระบุ / ไม่อยู่ในระบบออนไลน์';
                }
            }
            document.getElementById('dtDeviceName').textContent = item.device_name || '-';
            document.getElementById('dtUuid').textContent = item.device_id || '-';

            // Remote links
            const vncFileLink = document.getElementById('dtVncFileLink');
            if (vncFileLink) {
                vncFileLink.href = "{{ url('/admin/installations') }}/" + item.id + "/vnc";
            }
            const rdpLink = document.getElementById('dtRdpLink');
            if (rdpLink) {
                rdpLink.href = "{{ url('/admin/installations') }}/" + item.id + "/rdp";
            }

            // AnyDesk Details
            const anydeskStatusEl = document.getElementById('dtAnyDeskStatus');
            const anydeskActionsEl = document.getElementById('dtAnyDeskActions');
            const anydeskLinkEl = document.getElementById('dtAnyDeskLink');
            if (anydeskStatusEl) {
                if (item.anydesk_installed) {
                    if (item.anydesk_id) {
                        anydeskStatusEl.innerHTML = '<span style="color: #059669; font-weight: 700;">✅ มีการติดตั้ง AnyDesk</span> • หมายเลข ID: <strong style="font-family: monospace; font-size: 0.98rem; color: #BE123C; letter-spacing: 0.5px;">' + item.anydesk_id + '</strong>';
                        if (anydeskActionsEl) anydeskActionsEl.style.display = 'inline-flex';
                        if (anydeskLinkEl) anydeskLinkEl.href = 'anydesk:' + item.anydesk_id.replace(/\s+/g, '');
                    } else {
                        anydeskStatusEl.innerHTML = '<span style="color: #059669; font-weight: 700;">✅ มีการติดตั้ง AnyDesk บนเครื่องนี้</span> <span style="font-size: 0.78rem; color: #64748B;">(ยังไม่พบหมายเลข ID)</span>';
                        if (anydeskActionsEl) anydeskActionsEl.style.display = 'none';
                    }
                } else {
                    anydeskStatusEl.innerHTML = '<span style="color: #64748B;">เครื่องนี้ไม่ได้ติดตั้งโปรแกรม AnyDesk</span>';
                    if (anydeskActionsEl) anydeskActionsEl.style.display = 'none';
                }
            }

            // Risk Software Details
            renderRiskSoftwareBox(item.has_risk_software, item.risk_software_list);

            // Usage & Timestamps
            document.getElementById('dtLaunchCount').textContent = (item.launch_count || 0) + ' ครั้ง';
            const serverVer = '{{ $serverVersion }}';
            const cVer = item.client_version || '1.0.0';
            document.getElementById('dtInstallType').textContent = (item.install_type === 'installed') 
                ? ('ติดตั้งไอคอนแอป (Desktop Shortcut) • v' + cVer + ' (เปิดโปรแกรมอัตโนมัติเมื่อเปิดเครื่อง & Auto-Update)') 
                : 'เข้าใช้งานผ่านเบราว์เซอร์';
            document.getElementById('dtFirstInstall').textContent = item.first_installed_at ? formatDateTime(item.first_installed_at) : '-';
            document.getElementById('dtLastActive').textContent = item.last_active_at ? formatDateTime(item.last_active_at) : '-';
            
            // Uptime & Last Boot
            const lastBootEl = document.getElementById('dtLastBoot');
            if (lastBootEl) {
                lastBootEl.textContent = item.last_boot_time ? formatDateTime(item.last_boot_time) : 'ยังไม่มีข้อมูล (จะอัปเดตเมื่อซิงก์รอบถัดไป)';
            }
            const uptimeEl = document.getElementById('dtUptime');
            if (uptimeEl) {
                const up = item.dynamic_uptime || item.uptime_text;
                if (up) {
                    uptimeEl.innerHTML = '<span style="color: #0284C7; font-weight: 700;">' + up + '</span>' + 
                        (item.last_boot_time ? ' <span style="font-size: 0.78rem; color: #64748B; font-weight: 400;">(เปิดเครื่องเมื่อ ' + formatDateTime(item.last_boot_time) + ')</span>' : '');
                } else {
                    uptimeEl.textContent = '-';
                }
            }

            // Client Version Details
            const verEl = document.getElementById('dtClientVersion');
            if (verEl) {
                if (cVer === serverVer) {
                    verEl.innerHTML = '<span style="color: #059669;">v' + cVer + ' (เวอร์ชันล่าสุด)</span>';
                } else {
                    verEl.innerHTML = '<span style="color: #DC2626;">v' + cVer + '</span> <span style="font-size: 0.78rem; color: #64748B;">(เวอร์ชันเซิร์ฟเวอร์คือ v' + serverVer + ')</span>';
                }
            }

            const notesEl = document.getElementById('dtNotes');
            if (notesEl) {
                notesEl.textContent = item.notes ? item.notes : 'ไม่มีหมายเหตุ';
            }

            modal.classList.add('active');
        }

        function copyAnydeskId() {
            if (currentDetailItem && currentDetailItem.anydesk_id) {
                copyTextToClipboard(currentDetailItem.anydesk_id.replace(/\s+/g, ''), '📋 คัดลอก AnyDesk ID (' + currentDetailItem.anydesk_id + ') เรียบร้อยแล้ว!');
            }
        }

        function launchAnydesk(id) {
            if (!id) return;
            const cleanId = id.toString().replace(/\s+/g, '');
            window.location.href = 'anydesk:' + cleanId;
            showToast('🚀 กำลังเรียกเปิดโปรแกรม AnyDesk เชื่อมต่อไปยัง ID: ' + id);
        }

        function closeDetailsModal() {
            const modal = document.getElementById('detailsModal');
            if (modal) modal.classList.remove('active');
        }

        function formatDateTime(dtStr) {
            if (!dtStr) return '-';
            try {
                const d = new Date(dtStr);
                if (isNaN(d.getTime())) return dtStr;
                return d.toLocaleDateString('th-TH', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' +
                       d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
            } catch(e) {
                return dtStr;
            }
        }

        function launchVnc(ip, isReady = true, hostName = '') {
            if (!ip) {
                showToast('ไม่พบ IP Address ของเครื่องนี้');
                return;
            }
            if (!isReady) {
                openConfirmModal({
                    type: 'warning',
                    icon: 'desktop_access_disabled',
                    title: 'พอร์ต VNC ยังไม่พร้อมเชื่อมต่อ',
                    message: 'เครื่องปลายทางยังไม่ได้เปิด Service TightVNC (พอร์ต 5900 ปิดอยู่) หากเชื่อมต่อจะขึ้นข้อผิดพลาด <i>No connection could be made because the target machine actively refused it</i>',
                    targetName: hostName || ip,
                    targetSub: 'IP: ' + ip + ' (พอร์ต 5900 ไม่ตอบสนอง)',
                    hint: '<b>วิธีแก้ไข:</b> ที่เครื่องลูกข่าย ให้เปิด PowerShell (Run as Administrator) แล้วสั่ง:<br><code style="background:#FFF; padding:3px 8px; border-radius:4px; display:inline-block; margin-top:5px; font-weight:700; color:#0369A1; font-size:0.88rem;">Start-Service tvnserver</code>',
                    proceedText: 'ลองเปิด VNC ต่อไป',
                    proceedIcon: 'screen_share',
                    onConfirm: function() {
                        doLaunchVnc(ip);
                    }
                });
                return;
            }
            doLaunchVnc(ip);
        }

        function doLaunchVnc(ip) {
            const vncUri = 'vnc://' + ip + ':5900';
            
            // Trigger custom protocol handler safely without browser navigating away
            const a = document.createElement('a');
            a.href = vncUri;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                if (document.body.contains(a)) {
                    document.body.removeChild(a);
                }
            }, 600);

            showToast('กำลังเรียกเปิดโปรแกรม TightVNC สำหรับเครื่อง IP: ' + ip + ' (กรุณากรอกรหัสผ่านเพื่อเชื่อมต่อ)');
        }

        function launchCurrentVnc() {
            if (!currentDetailItem || !currentDetailItem.ip_address) {
                showToast('ไม่พบ IP Address สำหรับเชื่อมต่อ VNC');
                return;
            }
            launchVnc(currentDetailItem.ip_address, !!currentDetailItem.remote_ready, currentDetailItem.hostname || currentDetailItem.device_name || currentDetailItem.ip_address);
        }

        function copyCurrentIp() {
            if (!currentDetailItem || !currentDetailItem.ip_address) return;
            const text = currentDetailItem.ip_address;
            copyTextToClipboard(text, 'คัดลอก IP Address: ' + text + ' สำเร็จ!');
        }

        function copyCurrentVnc() {
            if (!currentDetailItem || !currentDetailItem.ip_address) return;
            copyVnc(currentDetailItem.ip_address, '{{ $vnc_password ?? "4Sh0sp@remote" }}');
        }

        function copyTextToClipboard(text, msg) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast(msg);
                }).catch(() => {
                    fallbackCopy(text, msg);
                });
            } else {
                fallbackCopy(text, msg);
            }
        }

        function switchFromDetailsToEdit() {
            if (!currentDetailItem) return;
            const item = currentDetailItem;
            closeDetailsModal();
            openEditModal(item);
        }

        function openEditModal(item) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            
            form.action = "{{ url('/admin/installations') }}/" + item.id;
            document.getElementById('modalIpHost').value = (item.ip_address || '') + (item.hostname ? ' (' + item.hostname + ')' : '');
            document.getElementById('modalDept').value = item.department || '';
            document.getElementById('modalDeviceName').value = item.device_name || '';
            document.getElementById('modalNotes').value = item.notes || '';

            let specs = [];
            if (item.cpu) specs.push('<strong>CPU:</strong> ' + item.cpu);
            if (item.ram) specs.push('<strong>RAM:</strong> ' + item.ram);
            if (item.disk) specs.push('<strong>DISK:</strong> ' + item.disk);
            if (item.remote_ready) {
                specs.push('<span style="color:#059669; font-weight:600;">✓ เปิดระบบ Remote VNC (พอร์ต 5900 / รหัส: {{ $vnc_password ?? "4Sh0sp@remote" }} - หน้าจอไม่ดับ)</span>');
                specs.push('<span style="color:#64748B; font-size:0.75rem;">(และเปิด Windows Remote Desktop RDP สำรองแล้ว)</span>');
            } else {
                specs.push('<span style="color:#D97706; font-weight:600;">⚠ บริการ Remote VNC (พอร์ต 5900) ยังไม่พร้อม / Service tvnserver ปิดอยู่</span>');
            }
            if (specs.length === 0) {
                specs.push('<span style="color:#94A3B8; font-style:italic;">ยังไม่มีข้อมูลสเปก (ระบบจะตรวจจับอัตโนมัติเมื่อรันตัวติดตั้งบนเครื่องนี้)</span>');
            }
            document.getElementById('modalSpecsDisplay').innerHTML = specs.join('<br>');

            modal.classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function setQuickDept(dept) {
            document.getElementById('modalDept').value = dept;
        }

        // Close on background click
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        function copyVnc(ip, pass) {
            const text = ip + ':5900';
            const msg = 'คัดลอก "' + text + '" แล้ว! (รหัสผ่าน VNC: ' + pass + ') เปิด VNC Viewer เพื่อเชื่อมต่อได้ทันที (หน้าจอไม่ดับ)';
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast(msg);
                }).catch(() => {
                    fallbackCopy(text, msg);
                });
            } else {
                fallbackCopy(text, msg);
            }
        }

        function copyMstsc(ip) {
            const cmd = 'mstsc /v:' + ip;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(cmd).then(() => {
                    showToast('คัดลอก "' + cmd + '" แล้ว! กด Win+R แล้ววางเพื่อรีโมท RDP ได้ทันที');
                }).catch(() => {
                    fallbackCopy(cmd, 'คัดลอก "' + cmd + '" แล้ว! กด Win+R แล้ววางเพื่อรีโมท RDP ได้ทันที');
                });
            } else {
                fallbackCopy(cmd, 'คัดลอก "' + cmd + '" แล้ว! กด Win+R แล้ววางเพื่อรีโมท RDP ได้ทันที');
            }
        }

        function fallbackCopy(text, customMsg) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            try {
                document.execCommand('copy');
                showToast(customMsg || ('คัดลอก "' + text + '" สำเร็จ!'));
            } catch (err) {
                alert('คัดลอก: ' + text);
            }
            document.body.removeChild(textarea);
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
                }, 4000);
            }
        }

        // --- Beautiful Confirmation Modal System ---
        let confirmActionCallback = null;

        function openConfirmModal(config) {
            const modal = document.getElementById('confirmActionModal');
            const iconWrapper = document.getElementById('confirmIconWrapper');
            const iconEl = document.getElementById('confirmIcon');
            const titleEl = document.getElementById('confirmTitle');
            const msgEl = document.getElementById('confirmMessage');
            const targetBox = document.getElementById('confirmTargetBox');
            const targetName = document.getElementById('confirmTargetName');
            const targetSub = document.getElementById('confirmTargetSub');
            const hintEl = document.getElementById('confirmHint');
            const proceedBtn = document.getElementById('confirmProceedBtn');
            const proceedIcon = document.getElementById('confirmProceedIcon');
            const proceedText = document.getElementById('confirmProceedText');

            const type = config.type || 'warning';
            iconWrapper.className = 'confirm-icon-wrapper theme-' + type;
            iconEl.textContent = config.icon || 'warning';
            titleEl.textContent = config.title || 'ยืนยันการดำเนินการ';
            msgEl.innerHTML = config.message || '';

            if (config.targetName) {
                targetBox.style.display = 'flex';
                targetName.textContent = config.targetName;
                targetSub.textContent = config.targetSub || '';
            } else {
                targetBox.style.display = 'none';
            }

            if (config.hint) {
                hintEl.style.display = 'block';
                hintEl.className = 'confirm-hint hint-' + type;
                hintEl.innerHTML = config.hint;
            } else {
                hintEl.style.display = 'none';
            }

            proceedBtn.className = 'btn-confirm-proceed btn-theme-' + type;
            proceedIcon.textContent = config.proceedIcon || 'check';
            proceedText.textContent = config.proceedText || 'ยืนยัน';

            confirmActionCallback = config.onConfirm;
            modal.classList.add('active');
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmActionModal');
            if (modal) modal.classList.remove('active');
            confirmActionCallback = null;
        }

        document.getElementById('confirmProceedBtn').addEventListener('click', function() {
            if (typeof confirmActionCallback === 'function') {
                const cb = confirmActionCallback;
                closeConfirmModal();
                cb();
            }
        });

        document.getElementById('confirmActionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        const serverGitModalEl = document.getElementById('serverGitModal');
        if (serverGitModalEl) {
            serverGitModalEl.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeServerGitModal();
                }
            });
        }

        function confirmDeleteRow(url, hostname, ip) {
            openConfirmModal({
                type: 'danger',
                icon: 'delete',
                title: 'ยืนยันการลบข้อมูลเครื่อง',
                message: 'คุณต้องการลบข้อมูลเครื่องคอมพิวเตอร์นี้ออกจากระบบ ใช่หรือไม่?',
                targetName: hostname ? (hostname + (ip ? ' (' + ip + ')' : '')) : (ip || 'ไม่ระบุชื่อเครื่อง'),
                targetSub: 'IP Address: ' + (ip || '-'),
                hint: '⚠️ หมายเหตุ: การลบจะนำรายการเครื่องนี้ออกจากตารางทันที (หากเครื่องเปิดใช้งานใหม่ ระบบจะบันทึกกลับเข้ามาอัตโนมัติ)',
                proceedIcon: 'delete',
                proceedText: 'ยืนยันการลบ',
                onConfirm: function() {
                    const form = document.getElementById('genericActionForm');
                    form.action = url;
                    document.getElementById('genericActionMethod').value = 'DELETE';
                    form.submit();
                }
            });
        }

        function refreshSingleDevice(url, hostname, ip, btnEl, itemId) {
            const displayName = hostname ? (hostname + (ip ? ' (' + ip + ')' : '')) : (ip || 'เครื่องลูกข่าย');
            const iconEl = btnEl ? btnEl.querySelector('.material-symbols-Outlined') : null;
            if (btnEl) {
                btnEl.classList.add('spinning');
                btnEl.disabled = true;
            }
            showToast('🔄 กำลังตรวจสอบการเชื่อมต่อ ' + displayName + '...');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.is_online) {
                        showToast('🟢 ' + data.message, 'success');
                    } else {
                        showToast('⚪ ' + data.message, 'info');
                    }
                    if (itemId) {
                        const badgeEl = document.getElementById('status-badge-' + itemId);
                        if (badgeEl) {
                            if (data.is_online) {
                                badgeEl.className = 'badge badge-online';
                                badgeEl.innerHTML = '<span class="pulse-dot"></span> ออนไลน์';
                            } else {
                                badgeEl.className = 'badge badge-offline';
                                badgeEl.textContent = 'ออฟไลน์';
                            }
                        }
                    }
                    setTimeout(() => { location.reload(); }, 900);
                } else {
                    showToast('❌ ไม่สามารถรีเฟรชข้อมูลเครื่องได้');
                    if (btnEl) {
                        btnEl.classList.remove('spinning');
                        btnEl.disabled = false;
                    }
                }
            })
            .catch(() => {
                showToast('❌ เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย');
                if (btnEl) {
                    btnEl.classList.remove('spinning');
                    btnEl.disabled = false;
                }
            });
        }

        function renderRiskSoftwareBox(hasRisk, rawList) {
            const riskBoxEl = document.getElementById('dtRiskSoftwareBox');
            const riskStatusEl = document.getElementById('dtRiskSoftwareStatus');
            if (!riskStatusEl) return;

            if (hasRisk && rawList && rawList.trim().length > 0) {
                if (riskBoxEl) {
                    riskBoxEl.style.background = '#FEF2F2';
                    riskBoxEl.style.borderColor = '#FECACA';
                }
                const listHtml = rawList.split(',')
                    .map(s => s.trim())
                    .filter(s => s.length > 0)
                    .map(s => '<span style="display: inline-flex; align-items: center; gap: 3px; background: #FFF; border: 1px solid #FCA5A5; color: #991B1B; padding: 2px 8px; border-radius: 4px; font-weight: 700; margin: 2px; font-size: 0.82rem;"><span class="material-symbols-outlined" style="font-size: 13px; color: #DC2626;">warning</span>' + escapeHtml(s) + '</span>')
                    .join(' ');
                riskStatusEl.innerHTML = '<div style="color: #DC2626; font-weight: 700; display: flex; align-items: center; gap: 4px; margin-bottom: 6px;"><span class="material-symbols-outlined" style="font-size: 16px;">gpp_maybe</span> ตรวจพบโปรแกรมเสี่ยงบนเครื่องนี้:</div><div style="display: flex; flex-wrap: wrap; gap: 4px;">' + (listHtml || escapeHtml(rawList)) + '</div>';
            } else {
                if (riskBoxEl) {
                    riskBoxEl.style.background = '#F0FDF4';
                    riskBoxEl.style.borderColor = '#BBF7D0';
                }
                riskStatusEl.innerHTML = '<span style="color: #16A34A; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><span class="material-symbols-outlined" style="font-size: 16px;">verified_user</span> ปลอดภัย ไม่พบโปรแกรมเสี่ยงตามกฎปัจจุบัน</span>';
            }
        }

        function updateTableRowRisk(itemId, hasRisk, riskList) {
            const cell = document.getElementById('cell-risk-' + itemId);
            if (!cell) return;
            if (hasRisk && riskList && riskList.trim().length > 0) {
                const limitText = riskList.length > 18 ? riskList.substring(0, 18) + '...' : riskList;
                cell.innerHTML = `
                    <div style="display: inline-flex; align-items: center; justify-content: center; cursor: pointer;"
                         title="ตรวจพบโปรแกรมเสี่ยง: ${escapeHtml(riskList)} (คลิกเพื่อดูรายละเอียด)"
                         onclick="if(currentDetailItem && currentDetailItem.id == ${itemId}) { openDetailsModal(currentDetailItem); } else { location.reload(); }">
                        <span class="badge-risk-warning">
                            <span class="material-symbols-Outlined" style="font-size: 13px;">warning</span>
                            <span>${escapeHtml(limitText)}</span>
                        </span>
                    </div>
                `;
            } else {
                cell.innerHTML = `
                    <span class="badge-risk-safe" title="ไม่พบโปรแกรมเสี่ยงตามกฎปัจจุบัน">
                        <span class="material-symbols-Outlined" style="font-size: 12px;">check_circle</span>
                        <span>ไม่พบ</span>
                    </span>
                `;
            }
        }

        function resyncRiskFromDetails(btnEl) {
            if (!currentDetailItem) return;
            const item = currentDetailItem;
            const url = "{{ url('/admin/installations') }}/" + item.id + "/resync-risk";
            
            if (btnEl) {
                btnEl.classList.add('spinning');
                btnEl.disabled = true;
            }
            showToast('🛡️ กำลังตรวจสอบโปรแกรมเสี่ยงแบบเรียลไทม์...');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (btnEl) {
                    btnEl.classList.remove('spinning');
                    btnEl.disabled = false;
                }
                if (data.success) {
                    item.has_risk_software = data.has_risk_software;
                    item.risk_software_list = data.risk_software_list;
                    renderRiskSoftwareBox(data.has_risk_software, data.risk_software_list);
                    updateTableRowRisk(item.id, data.has_risk_software, data.risk_software_list);
                    showToast('✅ ' + data.message, 'success');
                } else {
                    showToast('❌ ตรวจสอบไม่สำเร็จ: ' + (data.message || ''));
                }
            })
            .catch(err => {
                if (btnEl) {
                    btnEl.classList.remove('spinning');
                    btnEl.disabled = false;
                }
                showToast('❌ ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้: ' + err.message);
            });
        }

        function resyncSingleDevice(url, hostname, ip, btnEl, itemId) {
            const displayName = hostname ? (hostname + (ip ? ' (' + ip + ')' : '')) : (ip || 'เครื่องลูกข่าย');
            if (btnEl) {
                btnEl.classList.add('spinning');
                btnEl.disabled = true;
            }
            showToast('🔍 กำลังส่งคำสั่งตรวจสเปกเครื่อง ' + displayName + '...');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('✅ ' + data.message, 'success');
                    if (itemId) {
                        const badgeEl = document.getElementById('status-badge-' + itemId);
                        if (badgeEl && data.is_online !== undefined) {
                            if (data.is_online) {
                                badgeEl.className = 'badge badge-online';
                                badgeEl.innerHTML = '<span class="pulse-dot"></span> ออนไลน์';
                            }
                        }
                        if (data.has_risk_software !== undefined) {
                            updateTableRowRisk(itemId, data.has_risk_software, data.risk_software_list);
                        }
                    }
                    if (currentDetailItem && currentDetailItem.id === itemId) {
                        currentDetailItem.has_risk_software = data.has_risk_software;
                        currentDetailItem.risk_software_list = data.risk_software_list;
                        renderRiskSoftwareBox(data.has_risk_software, data.risk_software_list);
                    }
                    setTimeout(() => { location.reload(); }, 1200);
                } else {
                    showToast('❌ ไม่สามารถสั่งตรวจสเปกได้: ' + (data.message || ''));
                    if (btnEl) {
                        btnEl.classList.remove('spinning');
                        btnEl.disabled = false;
                    }
                }
            })
            .catch(() => {
                showToast('❌ เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย');
                if (btnEl) {
                    btnEl.classList.remove('spinning');
                    btnEl.disabled = false;
                }
            });
        }

        function resyncSpecsFromDetails(btnEl) {
            if (!currentDetailItem) return;
            const item = currentDetailItem;
            const url = "{{ url('/admin/installations') }}/" + item.id + "/resync-specs";
            resyncSingleDevice(url, item.hostname, item.ip_address, btnEl, item.id);
        }

        function confirmResyncAllSpecs(totalCount) {
            openConfirmModal({
                type: 'primary',
                icon: 'sync_saved_locally',
                title: 'สั่งตรวจสอบสเปกเครื่องลูกข่ายทั้งหมด',
                message: 'คุณต้องการส่งคำสั่งสั่งให้เครื่องลูกข่ายทั้งหมด (ประมาณ <strong>' + totalCount + ' เครื่อง</strong>) รันสแกนและอัปเดตสเปกฮาร์ดแวร์ (CPU, RAM, DISK, OS, Office) ใหม่ ใช่หรือไม่?',
                targetName: 'เครื่องลูกข่ายทั้งหมดในระบบ (' + totalCount + ' เครื่อง)',
                targetSub: 'สแกน CPU ละเอียด, Bus RAM, พาร์ทิชัน DISK, Build OS, รุ่น Office',
                hint: '💡 <b>การทำงาน:</b> ระบบจะส่งสัญญาณผ่าน Task Scheduler ไปยังเครื่องลูกข่ายในเครือข่าย LAN เพื่อสั่งรันตรวจจับสเปกและส่งรายงานกลับมาที่ฐานข้อมูลทันที',
                proceedText: 'ยืนยันสั่งตรวจสเปกทั้งหมด',
                proceedIcon: 'sync_saved_locally',
                onConfirm: function() {
                    showToast('🚀 กำลังส่งคำสั่งตรวจสเปกไปยังเครื่องลูกข่ายทั้งหมด...');
                    fetch('{{ route("admin.installations.resync-all-specs") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast('✅ ' + data.message, 'success');
                            setTimeout(() => { location.reload(); }, 1500);
                        } else {
                            showToast('❌ เกิดข้อผิดพลาด: ' + (data.message || ''));
                        }
                    })
                    .catch(() => {
                        showToast('❌ เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    });
                }
            });
        }

        // Auto live-check status for displayed devices on page load
        document.addEventListener('DOMContentLoaded', function() {
            const badges = document.querySelectorAll('[id^="status-badge-"]');
            if (!badges.length) return;
            const ids = Array.from(badges).map(el => parseInt(el.id.replace('status-badge-', ''))).filter(n => !isNaN(n));
            if (!ids.length) return;

            fetch('{{ route("admin.installations.check-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.results) {
                    for (const [id, info] of Object.entries(data.results)) {
                        const badgeEl = document.getElementById('status-badge-' + id);
                        if (badgeEl) {
                            if (info.is_online) {
                                badgeEl.className = 'badge badge-online';
                                badgeEl.innerHTML = '<span class="pulse-dot"></span> ออนไลน์';
                            } else {
                                badgeEl.className = 'badge badge-offline';
                                badgeEl.textContent = 'ออฟไลน์';
                            }
                        }

                        // Live update Remote VNC button based on real-time probe
                        const remoteBtn = document.getElementById('btn-remote-' + id);
                        if (remoteBtn && info.remote_ready !== undefined) {
                            const warnIcon = remoteBtn.querySelector('.remote-warn-icon');
                            if (info.remote_ready) {
                                remoteBtn.classList.remove('vnc-unready');
                                remoteBtn.setAttribute('title', 'Remote VNC (พร้อมเชื่อมต่อ)');
                                if (warnIcon) warnIcon.remove();
                                const curClick = remoteBtn.getAttribute('onclick') || '';
                                remoteBtn.setAttribute('onclick', curClick.replace(', false,', ', true,'));
                            } else if (!info.is_online) {
                                remoteBtn.classList.add('vnc-unready');
                                remoteBtn.setAttribute('title', 'เครื่องออฟไลน์');
                                const curClick = remoteBtn.getAttribute('onclick') || '';
                                remoteBtn.setAttribute('onclick', curClick.replace(', true,', ', false,'));
                            }
                        }
                    }
                }
            })
            .catch(() => {});
        });

        function triggerUpdateFromDetails() {
            if (!currentDetailItem) return;
            const item = currentDetailItem;
            closeDetailsModal();
            confirmUpdateRow(
                "{{ url('/admin/installations') }}/" + item.id + "/trigger-update",
                item.hostname,
                item.ip_address,
                item.client_version
            );
        }

        function confirmUpdateRow(url, hostname, ip, currentVer) {
            const targetServerVer = '{{ $serverVersion }}';
            openConfirmModal({
                type: 'primary',
                icon: 'system_update_alt',
                title: 'สั่งอัพเดทเครื่องลูกข่าย (Update Client)',
                message: 'คุณต้องการส่งคำสั่งอัพเดทโปรแกรม <strong>CSHOS STAFF INFO</strong> เป็นเวอร์ชัน <strong>v' + targetServerVer + '</strong> สำหรับเครื่องนี้ ใช่หรือไม่?',
                targetName: hostname ? (hostname + (ip ? ' (' + ip + ')' : '')) : (ip || 'เครื่องลูกข่าย'),
                targetSub: 'เวอร์ชันปัจจุบัน: ' + (currentVer ? 'v' + currentVer : '1.0.0') + ' ➜ เวอร์ชันเป้าหมาย: v' + targetServerVer,
                hint: '🚀 <strong>ระบบการอัพเดท:</strong><br>• เครื่องจะเริ่มดาวน์โหลดและปรับปรุงโปรแกรมอัตโนมัติทันทีหากเชื่อมต่อ LAN ได้<br>• หากเครื่องปิดอยู่ จะอัพเดทอัตโนมัติทันทีเมื่อเปิดเครื่อง/รีสตาร์ท หรือตามรอบเวลาประจำชั่วโมง',
                proceedIcon: 'system_update_alt',
                proceedText: 'สั่งอัพเดททันที',
                onConfirm: function() {
                    showToast('🚀 กำลังส่งคำสั่งอัพเดทไปยัง ' + (hostname || ip) + '...');
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast('✅ ' + data.message);
                            setTimeout(() => { location.reload(); }, 1200);
                        } else {
                            showToast('❌ เกิดข้อผิดพลาดในการส่งคำสั่ง');
                        }
                    })
                    .catch(() => {
                        showToast('❌ ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
                    });
                }
            });
        }

        function confirmUpdateAll(serverVer, outdatedCount) {
            openConfirmModal({
                type: 'primary',
                icon: 'system_update_alt',
                title: 'สั่งอัพเดทเครื่องลูกข่ายทั้งหมด (Broadcast Update)',
                message: 'คุณต้องการส่งคำสั่งอัพเดทโปรแกรม <strong>CSHOS STAFF INFO</strong> เป็นเวอร์ชัน <strong>v' + serverVer + '</strong> ให้กับเครื่องลูกข่ายทุกเครื่องในระบบ ใช่หรือไม่?',
                targetName: 'เครื่องลูกข่ายทั้งหมดในระบบ (' + (outdatedCount > 0 ? outdatedCount + ' เครื่องที่ยังไม่เป็นเวอร์ชันล่าสุด' : 'ทุกเครื่อง') + ')',
                targetSub: 'เวอร์ชันเป้าหมายของเซิร์ฟเวอร์: v' + serverVer,
                hint: '📢 <strong>การทำงานของระบบ:</strong><br>1. เครื่องที่เปิดและเชื่อมต่อ LAN อยู่จะเริ่มรับคำสั่งอัพเดททันที<br>2. เครื่องที่ปิดอยู่ จะอัพเดทอัตโนมัติทันทีที่มีการเปิดเครื่อง/รีสตาร์ท หรือตามรอบเวลาที่กำหนด',
                proceedIcon: 'system_update_alt',
                proceedText: 'สั่งอัพเดททุกเครื่อง',
                onConfirm: function() {
                    showToast('📢 กำลังส่งคำสั่งอัพเดทไปยังเครื่องลูกข่ายทั้งหมด...');
                    fetch('{{ route("admin.installations.trigger-update-all") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast('✅ ' + data.message);
                            setTimeout(() => { location.reload(); }, 1500);
                        } else {
                            showToast('❌ เกิดข้อผิดพลาดในการส่งคำสั่ง');
                        }
                    })
                    .catch(() => {
                        showToast('❌ ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
                    });
                }
            });
        }

        let currentPendingItem = null;

        function openPendingUpdateModal(item) {
            currentPendingItem = item;
            const modal = document.getElementById('pendingUpdateModal');
            if (!modal) return;

            const host = item.hostname || 'เครื่องลูกข่าย';
            const ip = item.ip_address || '';
            document.getElementById('pendingHostIp').textContent = host + (ip ? ' (' + ip + ')' : '');

            let timeStr = 'ไม่ระบุเวลา';
            if (item.update_requested_at) {
                try {
                    const d = new Date(item.update_requested_at);
                    timeStr = d.toLocaleDateString('th-TH', { hour: '2-digit', minute: '2-digit' });
                } catch(e) {}
            }
            document.getElementById('pendingTimeText').textContent = 'ส่งคำสั่งเมื่อ: ' + timeStr;

            modal.style.display = 'flex';
        }

        function closePendingUpdateModal() {
            const modal = document.getElementById('pendingUpdateModal');
            if (modal) modal.style.display = 'none';
            currentPendingItem = null;
        }

        function executeCompleteUpdate() {
            if (!currentPendingItem) return;
            const item = currentPendingItem;
            const url = "{{ url('/admin/installations') }}/" + item.id + "/complete-update";

            showToast('⏳ กำลังบันทึกสถานะเป็นเวอร์ชัน v{{ $serverVersion }}...');
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('✅ ' + data.message);
                    closePendingUpdateModal();
                    setTimeout(() => { location.reload(); }, 900);
                } else {
                    showToast('❌ ไม่สามารถบันทึกสถานะได้');
                }
            })
            .catch(() => {
                showToast('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        }

        function executeCancelUpdate() {
            if (!currentPendingItem) return;
            const item = currentPendingItem;
            const url = "{{ url('/admin/installations') }}/" + item.id + "/cancel-update";

            showToast('⏳ กำลังยกเลิกสถานะรออัพเดท...');
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('✅ ' + data.message);
                    closePendingUpdateModal();
                    setTimeout(() => { location.reload(); }, 900);
                } else {
                    showToast('❌ ไม่สามารถยกเลิกสถานะได้');
                }
            })
            .catch(() => {
                showToast('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        }

        function copyManualUpdateCommand() {
            const cmd = 'powershell -NoP -ep bypass -c "irm {{ url("/api/client/update-script") }} | iex"';
            copyTextToClipboard(cmd, '📋 คัดลอกคำสั่งรันอัพเดทสำเร็จ! นำไปวางใน PowerShell บนเครื่องลูกข่ายได้ทันที');
        }

        function confirmClearAllPending(count) {
            openConfirmModal({
                type: 'danger',
                icon: 'sync_disabled',
                title: 'ล้างสถานะรออัพเดททั้งหมด',
                message: 'คุณต้องการล้างสถานะ "รออัพเดท" ของเครื่องคอมพิวเตอร์ทั้งหมดจำนวน <strong>' + count + ' เครื่อง</strong> ให้กลับสู่สถานะปกติ ใช่หรือไม่?',
                hint: '💡 คำสั่งนี้จะปลดป้าย "รออัพเดท" ของทุกเครื่องในระบบออกทันที',
                proceedIcon: 'sync_disabled',
                proceedText: 'ยืนยันล้างสถานะ',
                onConfirm: function() {
                    showToast('⏳ กำลังล้างสถานะรออัพเดททั้งหมด...');
                    fetch('{{ route("admin.installations.clear-pending") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast('✅ ' + data.message);
                            setTimeout(() => { location.reload(); }, 1000);
                        } else {
                            showToast('❌ เกิดข้อผิดพลาดในการล้างสถานะ');
                        }
                    })
                    .catch(() => {
                        showToast('❌ ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
                    });
                }
            });
        }
    </script>
</body>
</html>
