<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบตรวจสอบการติดตั้ง CSHOS DATACENTER - โรงพยาบาลเชียงแสน</title>
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
            border-radius: 18px;
            padding: 1.2rem 1.4rem;
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: center;
            justify-content: space-between;
        }

        .filter-inputs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            flex: 1;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box .material-symbols-Outlined {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 19px;
        }

        .search-box input {
            width: 100%;
            padding: 0.55rem 0.8rem 0.55rem 2.4rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            background: #F8FAFC;
        }

        .search-box input:focus {
            background: #fff;
            border-color: var(--primary);
        }

        .select-filter {
            padding: 0.55rem 0.9rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.88rem;
            font-family: inherit;
            background: #F8FAFC;
            color: var(--text-dark);
            outline: none;
            cursor: pointer;
        }

        .select-filter:focus {
            border-color: var(--primary);
            background: #fff;
        }

        .btn-filter {
            padding: 0.55rem 1rem;
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
            gap: 0.35rem;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background: var(--primary-dark);
        }

        .btn-reset {
            padding: 0.55rem 0.85rem;
            background: #F1F5F9;
            color: #64748B;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.88rem;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-reset:hover {
            background: #E2E8F0;
            color: #334155;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1.1rem;
            background: #10B981;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
            transition: all 0.2s;
        }

        .btn-export:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-refresh {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.55rem 0.9rem;
            background: #FFFFFF;
            color: #475569;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.88rem;
            cursor: pointer;
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
            font-size: 0.9rem;
            text-align: left;
            min-width: 1350px;
        }

        thead {
            background: #F8FAFC;
            border-bottom: 1.5px solid var(--border);
        }

        th {
            padding: 1rem 1.1rem;
            font-weight: 600;
            color: #475569;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid #F1F5F9;
            transition: background 0.15s;
        }

        tbody tr:hover {
            background-color: #F8FAFC;
        }

        td {
            padding: 1rem 1.1rem;
            vertical-align: middle;
            color: #334155;
            white-space: nowrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.78rem;
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
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10B981;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        }

        .ip-badge {
            font-family: monospace;
            background: #F1F5F9;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.88rem;
            color: #0F172A;
            font-weight: 600;
        }

        .hostname-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        /* Spec list badges */
        .spec-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 0.78rem;
            max-width: 280px;
        }

        .spec-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 0.76rem;
            line-height: 1.35;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .spec-cpu {
            background: #EFF6FF;
            color: #1E40AF;
            border: 1px solid #BFDBFE;
        }

        .spec-ram {
            background: #FAF5FF;
            color: #6B21A8;
            border: 1px solid #E9D5FF;
        }

        .spec-disk {
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .spec-empty {
            color: #94A3B8;
            font-size: 0.78rem;
            font-style: italic;
        }

        /* Remote VNC / Desktop actions */
        .remote-actions {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 145px;
            align-items: center;
        }

        .btn-remote-vnc {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            width: 100%;
            padding: 5px 9px;
            background: linear-gradient(135deg, #059669, #047857);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
            transition: all 0.15s;
        }

        .btn-remote-vnc:hover {
            background: linear-gradient(135deg, #047857, #065F46);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.35);
        }

        .btn-remote-rdp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 4px 8px;
            background: #F1F5F9;
            color: #475569 !important;
            text-decoration: none;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 500;
            transition: all 0.15s;
        }

        .btn-remote-rdp:hover {
            background: #E2E8F0;
            color: #0F172A !important;
        }

        .rdp-alt-link {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 0.72rem;
            color: #64748B;
            text-decoration: none;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.15s;
        }

        .rdp-alt-link:hover {
            background: #F1F5F9;
            color: #0284C7;
            text-decoration: underline;
        }

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

        .vnc-pass-hint {
            font-size: 0.72rem;
            color: #64748B;
            margin-top: 1px;
            text-align: center;
        }

        .vnc-pass-hint code {
            background: #F1F5F9;
            padding: 1px 4px;
            border-radius: 4px;
            color: #0F172A;
            font-weight: 600;
            font-family: monospace;
        }

        .btn-remote-copy {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            width: 100%;
            padding: 4px 8px;
            background: #F8FAFC;
            color: #475569;
            border: 1px solid #CBD5E1;
            border-radius: 7px;
            font-size: 0.74rem;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-remote-copy:hover {
            background: #F1F5F9;
            color: #0F172A;
            border-color: #94A3B8;
        }

        .remote-ready-pill {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 0.7rem;
            color: #059669;
            font-weight: 600;
            background: #ECFDF5;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #A7F3D0;
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

        .dept-tag {
            background: #F3E8FF;
            color: #6B21A8;
            padding: 0.25rem 0.65rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 500;
            display: inline-block;
        }

        .dept-empty {
            color: #94A3B8;
            font-style: italic;
            font-size: 0.85rem;
        }

        .table-actions-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-action-edit {
            background: none;
            border: 1px solid var(--border);
            padding: 0.35rem 0.6rem;
            border-radius: 8px;
            color: var(--primary);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .btn-action-edit:hover {
            background: var(--primary-light);
            border-color: var(--primary);
        }

        .btn-action-reset {
            background: none;
            border: 1px solid #FDE68A;
            padding: 0.35rem 0.6rem;
            border-radius: 8px;
            color: #D97706;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            font-family: inherit;
            transition: all 0.2s;
        }
        .btn-action-reset:hover {
            background: #FEF3C7;
            border-color: #F59E0B;
        }

        .btn-action-delete {
            background: none;
            border: 1px solid #FECACA;
            padding: 0.35rem 0.5rem;
            border-radius: 8px;
            color: #DC2626;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-family: inherit;
            transition: all 0.2s;
        }
        .btn-action-delete:hover {
            background: #FEE2E2;
            border-color: #EF4444;
        }

        .btn-download-toolbar {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            padding: 0.55rem 1rem;
            border-radius: 10px;
            color: #047857;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-download-toolbar:hover {
            background: #D1FAE5;
            border-color: #6EE7B7;
            transform: translateY(-1px);
        }

        .btn-download-autosync {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            padding: 0.55rem 1rem;
            border-radius: 10px;
            color: #1D4ED8;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-download-autosync:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
            transform: translateY(-1px);
        }

        .btn-reset-this {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            padding: 0.55rem 1rem;
            border-radius: 10px;
            color: #1D4ED8;
            font-weight: 500;
            font-size: 0.88rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-reset-this:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
            transform: translateY(-1px);
        }

        .btn-reset-all {
            background: none;
            border: 1px solid #E2E8F0;
            padding: 0.55rem 0.85rem;
            border-radius: 10px;
            color: #64748B;
            font-weight: 500;
            font-size: 0.88rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-reset-all:hover {
            background: #FEE2E2;
            border-color: #FCA5A5;
            color: #DC2626;
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
                padding: 1rem;
            }
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons {
                justify-content: flex-end;
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
                <h1>CSHOS DATACENTER</h1>
                <p>ระบบติดตามและตรวจสอบการติดตั้งคอมพิวเตอร์ลูกข่าย</p>
            </div>
        </div>

        <div class="nav-actions">
            <a href="{{ asset('downloads/tightvnc-64bit.msi') }}" class="btn-download-vnc" title="ดาวน์โหลดโปรแกรม TightVNC Viewer สำหรับเครื่องแอดมิน/ช่างไอทีใช้รีโมท">
                <span class="material-symbols-Outlined" style="font-size: 18px;">tv</span>
                <span>ดาวน์โหลด VNC Viewer</span>
            </a>

            <a href="{{ route('download.shortcut.installer') }}" class="btn-download-app" title="ดาวน์โหลดตัวสร้างไอคอนและเปิดระบบ Remote VNC สำหรับนำไปติดตั้งบนเครื่องลูกข่าย">
                <span class="material-symbols-Outlined" style="font-size: 18px;">download</span>
                <span>downloadตัวติดตั้งApp</span>
            </a>

            <a href="{{ url('/') }}" target="_blank" class="btn-view-site">
                <span class="material-symbols-Outlined" style="font-size: 18px;">open_in_new</span>
                <span>เปิดหน้าหลักเว็บ</span>
            </a>

            <div class="user-menu">
                <div class="user-badge">
                    <span class="material-symbols-Outlined" style="font-size: 20px; color: var(--primary);">account_circle</span>
                    <span>{{ Auth::user()->name ?? 'ผู้ดูแลระบบ' }}</span>
                </div>

                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout" title="ออกจากระบบ">
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
        </section>

        <!-- Search & Action Filters -->
        <section class="controls-card">
            <form action="{{ route('admin.installations') }}" method="GET" class="filter-form">
                <div class="filter-inputs">
                    <div class="search-box">
                        <span class="material-symbols-Outlined">search</span>
                        <input type="text" name="search" placeholder="ค้นหา IP, Hostname, สเปก CPU/RAM, แผนก, ชื่อเครื่อง..." 
                               value="{{ request('search') }}">
                    </div>

                    <select name="department" class="select-filter">
                        <option value="">-- ทุกแผนก/หน่วยงาน --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="select-filter">
                        <option value="">-- ทุกช่วงเวลา --</option>
                        <option value="today" {{ request('status') == 'today' ? 'selected' : '' }}>ใช้งานวันนี้</option>
                        <option value="week" {{ request('status') == 'week' ? 'selected' : '' }}>ใช้งานใน 7 วันล่าสุด</option>
                        <option value="installed_only" {{ request('status') == 'installed_only' ? 'selected' : '' }}>ติดตั้งไอคอนแล้วเท่านั้น</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">tune</span>
                        <span>กรองข้อมูล</span>
                    </button>

                    @if(request()->hasAny(['search', 'department', 'status']))
                        <a href="{{ route('admin.installations') }}" class="btn-reset">
                            <span class="material-symbols-Outlined" style="font-size: 16px;">close</span>
                            <span>ล้างตัวกรอง</span>
                        </a>
                    @endif
                </div>

                <div class="action-buttons">
                    <a href="{{ asset('downloads/tightvnc-64bit.msi') }}" class="btn-download-vnc" title="ดาวน์โหลดโปรแกรม TightVNC Viewer สำหรับเครื่องแอดมิน/ช่างไอทีใช้รีโมท">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">tv</span>
                        <span>ดาวน์โหลด VNC Viewer</span>
                    </a>

                    <a href="{{ route('download.shortcut.installer') }}" class="btn-download-toolbar" title="ดาวน์โหลดตัวสร้างไอคอนและเปิดระบบ Remote VNC สำหรับนำไปติดตั้งบนเครื่องลูกข่าย (พร้อมตั้งค่า Auto-Sync เมื่อเปิดเครื่อง)">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">download</span>
                        <span>downloadตัวติดตั้งApp</span>
                    </a>

                    <a href="{{ route('download.autosync.installer') }}" class="btn-download-autosync" title="ดาวน์โหลดตัวเปิดระบบ Auto-Sync สเปกและ IP อัตโนมัติทุกครั้งเมื่อเปิด/รีสตาร์ตเครื่อง (สำหรับเครื่องที่ลงไว้แล้ว โดยไม่ต้องลงแอปใหม่)">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">autorenew</span>
                        <span>เปิด Auto-Sync เมื่อเปิดเครื่อง</span>
                    </a>

                    <button type="button" class="btn-reset-this" onclick="resetThisDevice()" title="รีเซ็ตสถานะเครื่องปัจจุบันและกลับไปหน้าหลักเพื่อติดตั้งไอคอนใหม่">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">restart_alt</span>
                        <span>รีเซ็ตเครื่องนี้ & ติดตั้งใหม่</span>
                    </button>

                    <button type="button" class="btn-reset-all" onclick="submitResetAll()" title="รีเซ็ตสถานะของทุกเครื่องในระบบ">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">history</span>
                        <span>รีเซ็ตทุกเครื่อง</span>
                    </button>

                    <button type="button" class="btn-refresh" onclick="location.reload()" title="รีเฟรชข้อมูล">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">refresh</span>
                        <span>รีเฟรช</span>
                    </button>

                    <a href="{{ route('admin.installations.export') }}" class="btn-export" title="ส่งออกข้อมูลเป็นไฟล์ Excel/CSV">
                        <span class="material-symbols-Outlined" style="font-size: 18px;">download</span>
                        <span>ส่งออก Excel/CSV</span>
                    </a>
                </div>
            </form>
            <form id="resetAllForm" action="{{ route('admin.installations.reset-all') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </section>

        <!-- Installations Table -->
        <section class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 45px;">ลำดับ</th>
                            <th>สถานะ</th>
                            <th>เครื่องคอมพิวเตอร์ & IP</th>
                            <th>สเปกเครื่อง (CPU / RAM / DISK)</th>
                            <th style="text-align: center;">รีโมทภายใน (VNC / RDP)</th>
                            <th>แผนก / หน่วยงาน</th>
                            <th>ผู้ใช้ / OS</th>
                            <th>เปิดใช้งาน</th>
                            <th>ใช้งานล่าสุด</th>
                            <th style="text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installations as $index => $item)
                            @php
                                $isToday = $item->last_active_at && $item->last_active_at->isToday();
                            @endphp
                            <tr>
                                <td>{{ $installations->firstItem() + $index }}</td>
                                <td>
                                    @if($isToday)
                                        <span class="badge badge-online">
                                            <span class="pulse-dot"></span> วันนี้
                                        </span>
                                    @else
                                        <span class="badge badge-offline">ออฟไลน์</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span class="material-symbols-Outlined" style="font-size: 18px; color: var(--primary);">computer</span>
                                        <strong>{{ $item->hostname ?: '-' }}</strong>
                                    </div>
                                    <div style="margin-top: 3px;">
                                        <span class="ip-badge">{{ $item->ip_address ?: 'ไม่พบ IP' }}</span>
                                    </div>
                                    <div class="hostname-text" title="{{ $item->device_id }}">UUID: {{ Str::limit($item->device_id, 10) }}</div>
                                </td>
                                <td>
                                    @if($item->cpu || $item->ram || $item->disk)
                                        <div class="spec-list">
                                            @if($item->cpu)
                                                <span class="spec-badge spec-cpu" title="CPU: {{ $item->cpu }}">
                                                    <span class="material-symbols-Outlined" style="font-size: 13px;">memory</span>
                                                    <span>{{ Str::limit($item->cpu, 32) }}</span>
                                                </span>
                                            @endif
                                            @if($item->ram)
                                                <span class="spec-badge spec-ram" title="RAM: {{ $item->ram }}">
                                                    <span class="material-symbols-Outlined" style="font-size: 13px;">developer_board</span>
                                                    <span>RAM: {{ $item->ram }}</span>
                                                </span>
                                            @endif
                                            @if($item->disk)
                                                <span class="spec-badge spec-disk" title="DISK: {{ $item->disk }}">
                                                    <span class="material-symbols-Outlined" style="font-size: 13px;">storage</span>
                                                    <span>DISK: {{ $item->disk }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="spec-empty">รอรันตัวติดตั้งเพื่อตรวจจับสเปก</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($item->ip_address)
                                        <div class="remote-actions">
                                            <a href="{{ route('admin.installations.vnc', $item->id) }}" class="btn-remote-vnc" title="ดาวน์โหลดไฟล์ .vnc เพื่อเปิดดูหน้าจอและควบคุมเครื่องนี้ทันที (หน้าจอไม่ดับ)">
                                                <span class="material-symbols-Outlined" style="font-size: 14px;">screen_share</span>
                                                <span>Remote (VNC)</span>
                                            </a>
                                            <button type="button" class="btn-remote-copy" onclick="copyVnc('{{ $item->ip_address }}', '{{ $vnc_password ?? 'cshos123' }}')" title="คัดลอก IP และรหัสผ่าน VNC สำหรับโปรแกรม VNC Viewer">
                                                <span class="material-symbols-Outlined" style="font-size: 13px;">content_copy</span>
                                                <span>{{ $item->ip_address }}:5900</span>
                                            </button>
                                            <div class="vnc-pass-hint">รหัส: <code>{{ $vnc_password ?? 'cshos123' }}</code></div>
                                            @if($item->remote_ready)
                                                <div class="remote-ready-pill" title="เปิดระบบ Remote VNC พอร์ต 5900 แล้ว (หน้าจอไม่ดับ)">
                                                    <span class="material-symbols-Outlined" style="font-size: 12px;">verified</span>
                                                    <span>VNC Ready</span>
                                                </div>
                                            @endif
                                            <div style="margin-top: 2px;">
                                                <a href="{{ route('admin.installations.rdp', $item->id) }}" class="rdp-alt-link" title="สลับไปใช้ Windows Remote Desktop แบบปกติ (หน้าจอจะล็อก)">
                                                    <span class="material-symbols-Outlined" style="font-size: 11px;">desktop_windows</span>
                                                    <span>RDP สำรอง</span>
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <span class="spec-empty">ไม่พบ IP</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->department)
                                        <span class="dept-tag">{{ $item->department }}</span>
                                    @else
                                        <span class="dept-empty">ยังไม่ระบุ</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ $item->device_name ?: '-' }}</div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                        {{ $item->os ?: 'Windows' }}
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--primary);">{{ number_format($item->launch_count) }}</span> ครั้ง
                                    <div style="margin-top: 0.2rem;">
                                        @if($item->install_type === 'installed')
                                            <span class="badge badge-installed">
                                                <span class="material-symbols-Outlined" style="font-size: 14px;">check</span> ติดตั้งไอคอนแล้ว
                                            </span>
                                        @else
                                            <span class="badge badge-browser">ผ่านเบราว์เซอร์</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem;">
                                    <div style="font-weight: 500;">
                                        {{ $item->last_active_at ? $item->last_active_at->format('d/m/Y H:i') : '-' }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ $item->last_active_at ? $item->last_active_at->diffForHumans() : '' }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">
                                        ติดตั้ง: {{ $item->first_installed_at ? $item->first_installed_at->format('d/m/Y H:i') : '-' }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="table-actions-cell">
                                        <button type="button" class="btn-action-edit" 
                                                onclick="openEditModal({{ json_encode($item) }})"
                                                title="แก้ไขแผนก / ข้อมูลเครื่อง">
                                            <span class="material-symbols-Outlined" style="font-size: 16px;">edit</span>
                                            <span>แก้ไข</span>
                                        </button>
                                        <form action="{{ route('admin.installations.reset', $item->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('ต้องการรีเซ็ตสถานะการติดตั้งของเครื่อง {{ $item->hostname ?: $item->ip_address }} ใช่หรือไม่? \n(สถานะจะเปลี่ยนเป็น \'รอติดตั้งใหม่\' เพื่อให้เครื่องนี้ติดตั้งไอคอนใหม่ได้)')">
                                            @csrf
                                            <button type="submit" class="btn-action-reset" title="รีเซ็ตสถานะ ให้เครื่องนี้ติดตั้งใหม่ได้">
                                                <span class="material-symbols-Outlined" style="font-size: 16px;">restart_alt</span>
                                                <span>รีเซ็ต</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.installations.destroy', $item->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('ต้องการลบข้อมูลเครื่อง {{ $item->hostname ?: $item->ip_address }} ออกจากระบบใช่หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-delete" title="ลบข้อมูลเครื่องนี้">
                                                <span class="material-symbols-Outlined" style="font-size: 16px;">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="empty-state">
                                        <span class="material-symbols-Outlined">devices_off</span>
                                        <h3>ยังไม่มีข้อมูลการติดตั้งในระบบ</h3>
                                        <p>เมื่อเจ้าหน้าที่เปิดใช้งานหรือกดติดตั้งไอคอน CSHOS DATACENTER ระบบจะตรวจจับและบันทึกอัตโนมัติ</p>
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

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-notify">
        <span class="material-symbols-Outlined" style="color: #34D399; font-size: 20px;">check_circle</span>
        <span id="toastMessage">คัดลอกคำสั่งสำเร็จ!</span>
    </div>

    <script>
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
                specs.push('<span style="color:#059669; font-weight:600;">✓ เปิดระบบ Remote VNC (พอร์ต 5900 / รหัส: {{ $vnc_password ?? 'cshos123' }} - หน้าจอไม่ดับ)</span>');
                specs.push('<span style="color:#64748B; font-size:0.75rem;">(และเปิด Windows Remote Desktop RDP สำรองแล้ว)</span>');
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

        function resetThisDevice() {
            if (confirm('คุณต้องการรีเซ็ตสถานะการติดตั้งของเครื่องปัจจุบันนี้ เพื่อกลับไปหน้าหลักและติดตั้งไอคอน CSHOS DATACENTER ลงหน้าจอ Desktop ใหม่อีกครั้ง ใช่หรือไม่?')) {
                try {
                    localStorage.removeItem('csh_app_installed');
                    localStorage.removeItem('csh_device_uuid');
                } catch(e) {}
                window.location.href = '{{ route("home") }}?reset=1';
            }
        }

        function submitResetAll() {
            if (confirm('คำเตือน: คุณต้องการรีเซ็ตสถานะการติดตั้งของ "ทุกเครื่องในระบบ" ให้กลับเป็นสถานะ "รอติดตั้งใหม่" ใช่หรือไม่?')) {
                document.getElementById('resetAllForm').submit();
            }
        }
    </script>
</body>
</html>
