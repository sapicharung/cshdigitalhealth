<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบตรวจสอบการติดตั้ง CSHOS StaffInfo - โรงพยาบาลเชียงแสน</title>
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

        /* Compact Last Active */
        .time-compact-cell {
            font-size: 0.82rem;
            color: #334155;
            cursor: pointer;
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
                <h1>CSHOS StaffInfo</h1>
                <p>ระบบติดตามและตรวจสอบการติดตั้งคอมพิวเตอร์ลูกข่าย</p>
            </div>
        </div>

        <div class="nav-actions">
            <a href="{{ route('download.shortcut.installer') }}" class="btn-download-app">
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
                        <option value="">-- ทุกช่วงเวลา --</option>
                        <option value="today" {{ request('status') == 'today' ? 'selected' : '' }}>ใช้งานวันนี้</option>
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
                    <button type="button" class="btn-reset-this" onclick="resetThisDevice()">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">restart_alt</span>
                        <span>รีเซ็ตเครื่องนี้</span>
                    </button>

                    <button type="button" class="btn-reset-all" onclick="submitResetAll()">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">history</span>
                        <span>รีเซ็ตทุกเครื่อง</span>
                    </button>

                    <button type="button" class="btn-clear-offline" onclick="submitClearOffline()">
                        <span class="material-symbols-Outlined" style="font-size: 17px;">delete_sweep</span>
                        <span>ลบเครื่องออฟไลน์</span>
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
            <form id="resetAllForm" action="{{ route('admin.installations.reset-all') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <form id="clearOfflineForm" action="{{ route('admin.installations.clear-offline') }}" method="POST" style="display: none;">
                @csrf
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
                            <th style="width: 160px;">ชื่อเครื่องคอมพิวเตอร์</th>
                            <th style="width: 140px;">IP Address</th>
                            <th style="width: 100px; text-align: center;">สเปกเครื่อง</th>
                            <th style="width: 110px; text-align: center;">รีโมท (VNC)</th>
                            <th style="width: 150px;">แผนก / หน่วยงาน</th>
                            <th style="width: 120px;">ใช้งานล่าสุด</th>
                            <th style="width: 115px; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installations as $index => $item)
                            @php
                                $isToday = $item->last_active_at && $item->last_active_at->isToday();
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #64748B;">{{ $installations->firstItem() + $index }}</td>
                                <td style="text-align: center;">
                                    @if($isToday)
                                        <span class="badge badge-online">
                                            <span class="pulse-dot"></span> วันนี้
                                        </span>
                                    @else
                                        <span class="badge badge-offline">ออฟไลน์</span>
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
                                    @if($item->cpu || $item->ram || $item->disk)
                                        <button type="button" class="btn-spec-pill" onclick="openDetailsModal({{ json_encode($item) }})">
                                            <span class="material-symbols-Outlined">memory</span>
                                            <span>ดูสเปก</span>
                                        </button>
                                    @else
                                        <span class="spec-empty-compact" onclick="openDetailsModal({{ json_encode($item) }})" style="cursor: pointer;">- ยังไม่มีสเปก -</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($item->ip_address)
                                        <div class="remote-compact-cell">
                                            <button type="button" class="btn-remote-pill" onclick="launchVnc('{{ $item->ip_address }}')">
                                                <span class="material-symbols-Outlined" style="font-size: 13px;">screen_share</span>
                                                <span>Remote</span>
                                            </button>
                                            <button type="button" class="btn-copy-icon" onclick="copyVnc('{{ $item->ip_address }}', '{{ $vnc_password ?? 'cshos123' }}')">
                                                <span class="material-symbols-Outlined" style="font-size: 13px;">content_copy</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="spec-empty-compact">ไม่พบ IP</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->department)
                                        <span class="dept-compact-tag" onclick="openDetailsModal({{ json_encode($item) }})" style="cursor: pointer;">
                                            {{ $item->department }}
                                        </span>
                                    @else
                                        <span class="dept-empty" onclick="openEditModal({{ json_encode($item) }})" style="cursor: pointer;">ยังไม่ระบุ</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="time-compact-cell" onclick="openDetailsModal({{ json_encode($item) }})" style="cursor: pointer;">
                                        {{ $item->last_active_at ? $item->last_active_at->format('d/m/y H:i') : '-' }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="table-actions-cell">
                                        <button type="button" class="btn-action-icon btn-action-view" 
                                                onclick="openDetailsModal({{ json_encode($item) }})">
                                            <span class="material-symbols-Outlined" style="font-size: 15px;">visibility</span>
                                        </button>
                                        <button type="button" class="btn-action-icon btn-action-edit" 
                                                onclick="openEditModal({{ json_encode($item) }})">
                                            <span class="material-symbols-Outlined" style="font-size: 15px;">edit</span>
                                        </button>
                                        <form action="{{ route('admin.installations.reset', $item->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('ต้องการรีเซ็ตสถานะการติดตั้งของเครื่อง {{ $item->hostname ?: $item->ip_address }} ใช่หรือไม่? \n(สถานะจะเปลี่ยนเป็น \'รอติดตั้งใหม่\' เพื่อให้เครื่องนี้ติดตั้งไอคอนใหม่ได้)')">
                                            @csrf
                                            <button type="submit" class="btn-action-icon btn-action-reset">
                                                <span class="material-symbols-Outlined" style="font-size: 15px;">restart_alt</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.installations.destroy', $item->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('ต้องการลบข้อมูลเครื่อง {{ $item->hostname ?: $item->ip_address }} ออกจากระบบใช่หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-icon btn-action-delete">
                                                <span class="material-symbols-Outlined" style="font-size: 15px;">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <span class="material-symbols-Outlined">devices_off</span>
                                        <h3>ยังไม่มีข้อมูลการติดตั้งในระบบ</h3>
                                        <p>เมื่อเจ้าหน้าที่เปิดใช้งานหรือกดติดตั้งไอคอน CSHOS StaffInfo ระบบจะตรวจจับและบันทึกอัตโนมัติ</p>
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
                    <div class="detail-section-title">
                        <span class="material-symbols-Outlined" style="color: #2563EB;">memory</span>
                        <span>สเปกฮาร์ดแวร์ & ซอฟต์แวร์ระบบ (Hardware & System Specs)</span>
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
                            <label>หมายเลข IP Address</label>
                            <div class="detail-value-with-btn">
                                <span id="dtIp" class="ip-badge-lg">-</span>
                                <button type="button" class="btn-copy-chip" onclick="copyCurrentIp()">
                                    <span class="material-symbols-Outlined" style="font-size: 13px;">content_copy</span>
                                    <span>คัดลอก</span>
                                </button>
                            </div>
                        </div>
                        <div class="detail-item">
                            <label>ชื่อเครื่อง (Computer Name)</label>
                            <div id="dtCompName" class="detail-value" style="font-weight: 700; color: #0F172A;">-</div>
                        </div>
                        <div class="detail-item">
                            <label>แผนก / หน่วยงาน</label>
                            <div id="dtDept" class="detail-value">-</div>
                        </div>
                        <div class="detail-item">
                            <label>ผู้ใช้งานประจำเครื่อง / อุปกรณ์</label>
                            <div id="dtDeviceName" class="detail-value">-</div>
                        </div>
                        <div class="detail-item full-width">
                            <label>รหัสประจำอุปกรณ์ (Device UUID)</label>
                            <div id="dtUuid" class="detail-value font-mono text-muted" style="font-size: 0.76rem;">-</div>
                        </div>
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
                        <div class="detail-item full-width" id="dtNotesContainer">
                            <label>หมายเหตุ</label>
                            <div id="dtNotes" class="detail-value" style="font-style: italic;">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="justify-content: space-between;">
                <div>
                    <button type="button" class="btn-modal-edit-trigger" onclick="switchFromDetailsToEdit()">
                        <span class="material-symbols-Outlined" style="font-size: 16px;">edit</span>
                        <span>แก้ไขข้อมูลแผนก / ผู้ใช้</span>
                    </button>
                </div>
                <button type="button" class="btn-modal-cancel" onclick="closeDetailsModal()">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-notify">
        <span class="material-symbols-Outlined" style="color: #34D399; font-size: 20px;">check_circle</span>
        <span id="toastMessage">คัดลอกคำสั่งสำเร็จ!</span>
    </div>

    <script>
        let currentDetailItem = null;

        function openDetailsModal(item) {
            currentDetailItem = item;
            const modal = document.getElementById('detailsModal');
            if (!modal) return;

            // Header info
            document.getElementById('dtHostname').textContent = (item.hostname ? item.hostname : 'เครื่องคอมพิวเตอร์') + (item.ip_address ? ' (' + item.ip_address + ')' : '');
            
            // Status badge
            const isToday = item.last_active_at && new Date(item.last_active_at).toDateString() === new Date().toDateString();
            const statusBadge = document.getElementById('dtStatusBadge');
            if (statusBadge) {
                if (isToday) {
                    statusBadge.className = 'badge badge-online';
                    statusBadge.innerHTML = '<span class="pulse-dot"></span> วันนี้';
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
            document.getElementById('dtCompName').textContent = item.hostname || '-';
            document.getElementById('dtDept').textContent = item.department || 'ยังไม่ระบุแผนก';
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

            // Usage & Timestamps
            document.getElementById('dtLaunchCount').textContent = (item.launch_count || 0) + ' ครั้ง';
            document.getElementById('dtInstallType').textContent = (item.install_type === 'installed') 
                ? ('ติดตั้งไอคอนแอป (Desktop Shortcut) • ' + (item.client_version ? 'v' + item.client_version : 'v2.5.0') + ' (เปิดโปรแกรมอัตโนมัติเมื่อเปิดเครื่อง & Auto-Update)') 
                : 'เข้าใช้งานผ่านเบราว์เซอร์';
            document.getElementById('dtFirstInstall').textContent = item.first_installed_at ? formatDateTime(item.first_installed_at) : '-';
            document.getElementById('dtLastActive').textContent = item.last_active_at ? formatDateTime(item.last_active_at) : '-';
            
            const notesEl = document.getElementById('dtNotes');
            if (notesEl) {
                notesEl.textContent = item.notes ? item.notes : 'ไม่มีหมายเหตุ';
            }

            modal.classList.add('active');
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

        function launchVnc(ip) {
            if (!ip) {
                showToast('ไม่พบ IP Address ของเครื่องนี้', 'error');
                return;
            }
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

            showToast('กำลังเรียกเปิดโปรแกรม TightVNC สำหรับเครื่อง IP: ' + ip, 'success');
        }

        function launchCurrentVnc() {
            if (!currentDetailItem || !currentDetailItem.ip_address) {
                showToast('ไม่พบ IP Address สำหรับเชื่อมต่อ VNC', 'error');
                return;
            }
            launchVnc(currentDetailItem.ip_address);
        }

        function copyCurrentIp() {
            if (!currentDetailItem || !currentDetailItem.ip_address) return;
            const text = currentDetailItem.ip_address;
            copyTextToClipboard(text, 'คัดลอก IP Address: ' + text + ' สำเร็จ!');
        }

        function copyCurrentVnc() {
            if (!currentDetailItem || !currentDetailItem.ip_address) return;
            copyVnc(currentDetailItem.ip_address, '{{ $vnc_password ?? 'cshos123' }}');
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
            if (confirm('คุณต้องการรีเซ็ตสถานะการติดตั้งของเครื่องปัจจุบันนี้ เพื่อกลับไปหน้าหลักและติดตั้งไอคอน CSHOS StaffInfo ลงหน้าจอ Desktop ใหม่อีกครั้ง ใช่หรือไม่?')) {
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

        function submitClearOffline() {
            if (confirm('ต้องการลบเครื่องที่ออฟไลน์ทั้งหมดออกจากตารางใช่หรือไม่?\n(ระบบจะลบเฉพาะเครื่องที่ไม่ได้ออนไลน์ในวันนี้ และคงเหลือไว้เฉพาะเครื่องที่ออนไลน์สถานะวันนี้)')) {
                document.getElementById('clearOfflineForm').submit();
            }
        }
    </script>
</body>
</html>
