<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>จัดการโปรแกรมเสี่ยง (Risk Software Management) - CSHOS STAFF INFO</title>
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

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.55;
        }

        .material-symbols-Outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
            vertical-align: middle;
        }

        /* --- Header Navbar (Full Screen) --- */
        .navbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
        }

        .brand-area {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: #FFF1F2;
            color: #E11D48;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            border: 1px solid #FFE4E6;
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
            background: #EF4444;
            color: #FFFFFF;
            border-color: #DC2626;
        }

        .btn-primary:hover {
            background: #DC2626;
        }

        /* --- Full-Screen Container --- */
        .container {
            max-width: 100%;
            width: 100%;
            margin: 0;
            padding: 1.25rem 2rem 3rem 2rem;
            flex: 1;
        }

        /* --- Stats Row --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
            width: 100%;
        }

        .stat-card {
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon-all { background: #EFF6FF; color: #2563EB; }
        .stat-icon-active { background: #ECFDF5; color: #059669; }
        .stat-icon-remote { background: #FEF2F2; color: #DC2626; }
        .stat-icon-emulator { background: #FFFBEB; color: #D97706; }
        .stat-icon-other { background: #F3E8FF; color: #7C3AED; }

        .stat-content {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: #0F172A;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.78rem;
            color: #64748B;
            font-weight: 500;
        }

        /* --- Preset Pill Buttons --- */
        .btn-preset-pill {
            display: inline-flex;
            align-items: center;
            background: #F1F5F9;
            color: #334155;
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            padding: 0.2rem 0.55rem;
            font-size: 0.74rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            font-family: inherit;
        }

        .btn-preset-pill:hover {
            background: #E2E8F0;
            border-color: #94A3B8;
            color: #0F172A;
        }

        /* --- Cards & Headers --- */
        .card {
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0F172A;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* --- Table --- */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }

        th {
            background: #F8FAFC;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }

        tr:hover td {
            background-color: #F8FAFC;
        }

        /* --- Badges --- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .badge-remote {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .badge-emulator {
            background: #FFFBEB;
            color: #92400E;
            border: 1px solid #FDE68A;
        }

        .badge-other {
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #E2E8F0;
        }

        .badge-severity-critical {
            background: #7F1D1D;
            color: #FEF2F2;
        }

        .badge-severity-high {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        .badge-severity-medium {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #FCD34D;
        }

        .badge-severity-low {
            background: #E0E7FF;
            color: #3730A3;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #CBD5E1;
            transition: .2s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .2s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #10B981;
        }

        input:checked + .slider:before {
            transform: translateX(18px);
        }

        /* --- Action Buttons --- */
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

        /* --- Modal --- */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-card {
            background: #FFFFFF;
            border-radius: 20px;
            width: 100%;
            max-width: 640px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 1.5rem;
            max-height: 75vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
            background: #F8FAFC;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        .form-control {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.88rem;
            color: #1E293B;
            transition: border-color 0.15s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .form-hint {
            font-size: 0.75rem;
            color: #64748B;
            margin-top: 0.25rem;
        }

        .alert-success {
            padding: 0.9rem 1.2rem;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
            border-radius: 12px;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.88rem;
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="navbar">
        <div class="brand-area">
            <div class="brand-icon">
                <span class="material-symbols-Outlined">shield_lock</span>
            </div>
            <div class="brand-text">
                <h1>จัดการเงื่อนไขตรวจสอบโปรแกรมเสี่ยง</h1>
                <p>CSHOS Security Policy & Detection Rules</p>
            </div>
        </div>

        <div class="nav-actions">
            <a href="{{ route('admin.installations') }}" class="btn-nav">
                <span class="material-symbols-Outlined">arrow_back</span>
                <span>กลับหน้ารายการเครื่องคอมพิวเตอร์</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="btn-nav" title="จัดการผู้ใช้งานระบบ (Admin & IT)">
                <span class="material-symbols-Outlined" style="color: #4F46E5;">manage_accounts</span>
                <span>จัดการผู้ใช้งาน</span>
            </a>

            <button type="button" class="btn-nav btn-primary" onclick="openAddModal()">
                <span class="material-symbols-Outlined">add_circle</span>
                <span>เพิ่มโปรแกรมเสี่ยงใหม่</span>
            </button>

            @if(Auth::check())
                <div class="user-badge" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.75rem; background: #F1F5F9; border-radius: 10px; font-size: 0.85rem; font-weight: 600; color: #334155;">
                    <span class="material-symbols-Outlined" style="font-size: 18px; color: #DC2626;">account_circle</span>
                    <span>{{ Auth::user()->name }}</span>
                    <span style="font-size: 0.7rem; padding: 2px 7px; background: #FEE2E2; color: #DC2626; border-radius: 9999px; font-weight: 700;">ADMIN</span>
                </div>
            @endif
        </div>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert-success">
                <span class="material-symbols-Outlined">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Stats Summary Row (Full Screen) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-all">
                    <span class="material-symbols-Outlined">policy</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $rules->count() }}</div>
                    <div class="stat-label">เงื่อนไขตรวจสอบทั้งหมด</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-active">
                    <span class="material-symbols-Outlined">verified</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $rules->where('is_active', true)->count() }}</div>
                    <div class="stat-label">กำลังเปิดตรวจจับ</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-remote">
                    <span class="material-symbols-Outlined">screen_share</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $rules->where('category', 'remote')->count() }}</div>
                    <div class="stat-label">โปรแกรมรีโมทภายนอก</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-emulator">
                    <span class="material-symbols-Outlined">phone_android</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $rules->where('category', 'emulator')->count() }}</div>
                    <div class="stat-label">โปรแกรมจำลอง Android</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-other">
                    <span class="material-symbols-Outlined">warning</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $rules->where('category', 'other')->count() }}</div>
                    <div class="stat-label">โปรแกรมเสี่ยงอื่นๆ</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-symbols-Outlined" style="color: #EF4444;">shield_lock</span>
                    <span>รายการโปรแกรมเสี่ยงที่ระบบตรวจสอบ ({{ $rules->count() }} รายการ)</span>
                </div>
                <div style="font-size: 0.82rem; color: #64748B;">
                    💡 สามารถเปิด/ปิดการตรวจจับ หรือแก้ไขเงื่อนไขได้ทันที เครื่องลูกจะดึงกติกาใหม่ไปใช้ในการ Sync ทุกๆ 5 นาที
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">เปิดใช้งาน</th>
                            <th style="width: 220px;">ชื่อโปรแกรม</th>
                            <th style="width: 140px;">หมวดหมู่</th>
                            <th style="width: 110px;">ความเสี่ยง</th>
                            <th>เงื่อนไขตรวจจับ (Processes / Services / Registry / Paths)</th>
                            <th style="width: 100px; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                            <tr id="rule-row-{{ $rule->id }}">
                                <td style="text-align: center;">
                                    <label class="toggle-switch" title="{{ $rule->is_active ? 'คลิกเพื่อปิดใช้งาน' : 'คลิกเพื่อเปิดใช้งาน' }}">
                                        <input type="checkbox" {{ $rule->is_active ? 'checked' : '' }} onchange="toggleRule({{ $rule->id }})">
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <strong style="color: #0F172A; font-size: 0.92rem;">{{ $rule->name }}</strong>
                                    @if($rule->description)
                                        <div style="font-size: 0.75rem; color: #64748B; margin-top: 2px;">{{ $rule->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->category === 'remote')
                                        <span class="badge badge-remote">
                                            <span class="material-symbols-Outlined" style="font-size: 14px;">screen_share</span>
                                            รีโมทภายนอก
                                        </span>
                                    @elseif($rule->category === 'emulator')
                                        <span class="badge badge-emulator">
                                            <span class="material-symbols-Outlined" style="font-size: 14px;">phone_android</span>
                                            จำลอง Android
                                        </span>
                                    @else
                                        <span class="badge badge-other">
                                            <span class="material-symbols-Outlined" style="font-size: 14px;">warning</span>
                                            โปรแกรมเสี่ยง
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-severity-{{ $rule->severity }}">
                                        {{ strtoupper($rule->severity) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; line-height: 1.55;">
                                        @if($rule->process_names)
                                            <div><strong style="color: #334155;">Process:</strong> <code>{{ $rule->process_names }}</code></div>
                                        @endif
                                        @if($rule->service_names)
                                            <div><strong style="color: #334155;">Service:</strong> <code>{{ $rule->service_names }}</code></div>
                                        @endif
                                        @if($rule->registry_patterns)
                                            <div><strong style="color: #334155;">Registry:</strong> <code>{{ $rule->registry_patterns }}</code></div>
                                        @endif
                                        @if($rule->file_paths)
                                            <div style="color: #64748B; word-break: break-word; margin-top: 2px;" title="{{ $rule->file_paths }}">
                                                <strong style="color: #334155;">Path:</strong> {{ str_replace("\n", " | ", $rule->file_paths) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <button type="button" class="btn-action btn-action-edit" title="แก้ไขเงื่อนไข" onclick="openEditModal({{ json_encode($rule) }})">
                                            <span class="material-symbols-Outlined" style="font-size: 17px;">edit</span>
                                        </button>
                                        <button type="button" class="btn-action btn-action-delete" title="ลบเงื่อนไขนี้" onclick="deleteRule({{ $rule->id }}, '{{ addslashes($rule->name) }}')">
                                            <span class="material-symbols-Outlined" style="font-size: 17px;">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: #64748B;">
                                    ยังไม่มีเงื่อนไขตรวจสอบโปรแกรมเสี่ยงในระบบ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form (Add / Edit) -->
    <div class="modal-backdrop" id="ruleModal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #0F172A;" id="modalTitle">เพิ่มโปรแกรมเสี่ยงใหม่</h3>
                    <p style="font-size: 0.78rem; color: #64748B; margin-top: 2px;" id="modalSubtitle">ระบุชื่อโปรแกรม หมวดหมู่ และระดับความเสี่ยง ระบบจะช่วยเติมเงื่อนไขให้ครบถ้วนอัตโนมัติ</p>
                </div>
                <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748B; line-height: 1;">&times;</button>
            </div>
            <form id="ruleForm" method="POST" action="{{ route('admin.risk-rules.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <!-- Helper Notice -->
                    <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1.15rem; display: flex; align-items: flex-start; gap: 0.65rem;">
                        <span class="material-symbols-Outlined" style="color: #16A34A; font-size: 20px; flex-shrink: 0; margin-top: 1px;">auto_awesome</span>
                        <div style="font-size: 0.8rem; color: #166534; line-height: 1.45;">
                            <strong>กรอกเพียง 3 ช่องหลักเท่านั้น:</strong> ระบุชื่อโปรแกรม หมวดหมู่ และความเสี่ยง ส่วนเงื่อนไข Process, Registry และ โฟลเดอร์ติดตั้ง ระบบจะช่วยเติมให้อัตโนมัติทันที
                        </div>
                    </div>

                    <!-- 1. Program Name -->
                    <div class="form-group">
                        <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>ชื่อโปรแกรมเสี่ยง *</span>
                            <span id="autoPresetBadge" style="display: none; font-size: 0.72rem; background: #DCFCE7; color: #15803D; padding: 2px 8px; border-radius: 12px; font-weight: 600;">
                                <span class="material-symbols-Outlined" style="font-size: 13px; vertical-align: middle;">check_circle</span> เติมข้อมูลอัตโนมัติแล้ว
                            </span>
                        </label>
                        <input type="text" name="name" id="ruleName" class="form-control" placeholder="เช่น AnyViewer, ToDesk, DBeaver, Wireshark" required autocomplete="off" oninput="handleNameInput(this.value)">
                        
                        <!-- Quick Select Pills -->
                        <div style="margin-top: 0.45rem; display: flex; flex-wrap: wrap; gap: 0.3rem; align-items: center;" id="presetPillsContainer">
                            <span style="font-size: 0.74rem; color: #64748B;">คลิกเร็ว:</span>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('AnyViewer')">+ AnyViewer</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('ToDesk')">+ ToDesk</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('Sunlogin')">+ Sunlogin</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('Parsec')">+ Parsec</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('Splashtop')">+ Splashtop</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('DBeaver')">+ DBeaver</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('Wireshark')">+ Wireshark</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('BitTorrent')">+ BitTorrent</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('CCleaner')">+ CCleaner</button>
                            <button type="button" class="btn-preset-pill" onclick="applyPreset('Cheat Engine')">+ Cheat Engine</button>
                        </div>
                    </div>

                    <!-- 2. Category & 3. Severity -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">หมวดหมู่ *</label>
                            <select name="category" id="ruleCategory" class="form-control" required onchange="handleCategoryChange()">
                                <option value="remote">รีโมทควบคุมระยะไกล (Remote Tool)</option>
                                <option value="emulator">โปรแกรมจำลอง Android (Emulator)</option>
                                <option value="other" selected>โปรแกรมเสี่ยงอื่นๆ (Other)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ระดับความเสี่ยง (Severity) *</label>
                            <select name="severity" id="ruleSeverity" class="form-control" required>
                                <option value="critical">Critical (วิกฤติ)</option>
                                <option value="high" selected>High (สูง)</option>
                                <option value="medium">Medium (ปานกลาง)</option>
                                <option value="low">Low (ต่ำ)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Collapsible Advanced Section -->
                    <div style="margin-top: 1rem; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden;">
                        <button type="button" id="btnToggleAdvanced" onclick="toggleAdvancedSettings()" style="width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: #F8FAFC; border: none; cursor: pointer; font-family: inherit; font-size: 0.82rem; font-weight: 600; color: #475569;">
                            <span style="display: flex; align-items: center; gap: 0.4rem;">
                                <span class="material-symbols-Outlined" style="font-size: 18px; color: #3B82F6;">tune</span>
                                <span>เงื่อนไขตรวจจับ (ระบบเติมให้อัตโนมัติแล้ว)</span>
                            </span>
                            <span style="display: flex; align-items: center; gap: 0.3rem; color: #2563EB;">
                                <span id="advancedToggleText">คลิกเพื่อดูหรือปรับแต่ง</span>
                                <span class="material-symbols-Outlined" id="advancedToggleIcon" style="font-size: 18px; transition: transform 0.2s;">expand_more</span>
                            </span>
                        </button>
                        <div id="advancedSettingsBody" style="display: none; padding: 1rem 1.25rem; background: #FFFFFF; border-top: 1px solid #E2E8F0;">
                            <div class="form-group">
                                <label class="form-label">ชื่อ Process ใน Task Manager (คั่นด้วยจุลภาค ,)</label>
                                <input type="text" name="process_names" id="ruleProcesses" class="form-control" placeholder="ระบบเติมให้อัตโนมัติ">
                                <div class="form-hint">ชื่อกระบวนการทำงานของโปรแกรม (ระบบตัด .exe ให้อัตโนมัติ)</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">ชื่อ Windows Service (ถ้ามี)</label>
                                <input type="text" name="service_names" id="ruleServices" class="form-control" placeholder="เว้นว่างได้หากไม่มี Background Service">
                                <div class="form-hint">ชื่อ Service สำหรับโปรแกรมที่รันเป็น Background Service</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">คีย์เวิร์ดใน Windows Registry Uninstall</label>
                                <input type="text" name="registry_patterns" id="ruleRegistry" class="form-control" placeholder="ระบบเติมให้อัตโนมัติ">
                                <div class="form-hint">คำค้นหาชื่อโปรแกรมในรายการ Add/Remove Programs ของ Windows</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">โฟลเดอร์ติดตั้งโปรแกรม File Paths (คั่นด้วยบรรทัดใหม่)</label>
                                <textarea name="file_paths" id="rulePaths" class="form-control" rows="3" placeholder="ระบบเติมให้อัตโนมัติ"></textarea>
                                <div class="form-hint">รองรับเครื่องหมายดอกจัน เช่น C:\Program Files\Example*</div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">คำอธิบายเพิ่มเติม / เหตุผลความเสี่ยง</label>
                                <textarea name="description" id="ruleDescription" class="form-control" rows="2" placeholder="ระบบเติมให้อัตโนมัติ"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="form-group" style="margin-top: 1.15rem; margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="ruleIsActive" value="1" checked>
                            <span style="font-size: 0.88rem; font-weight: 500; color: #0F172A;">เปิดใช้งานการตรวจจับทันที</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-nav" onclick="closeModal()">ยกเลิก</button>
                    <button type="submit" class="btn-nav btn-primary" id="btnSubmit">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let currentMode = 'add';
        let advancedExpanded = false;

        const presets = {
            'anyviewer': {
                name: 'AnyViewer',
                category: 'remote',
                severity: 'high',
                processes: 'AnyViewer, AnyViewerCore',
                services: 'AnyViewer Service',
                registry: 'AnyViewer',
                paths: "C:\\Program Files\\AnyViewer*\nC:\\Program Files (x86)\\AnyViewer*",
                description: 'โปรแกรมรีโมทควบคุมคอมพิวเตอร์ระยะไกล AnyViewer'
            },
            'todesk': {
                name: 'ToDesk',
                category: 'remote',
                severity: 'high',
                processes: 'ToDesk, ToDesk_Service, ToDesk_Lite',
                services: 'ToDesk_Service',
                registry: 'ToDesk',
                paths: "C:\\Program Files\\ToDesk*\nC:\\Program Files (x86)\\ToDesk*",
                description: 'โปรแกรมรีโมทระยะไกล ToDesk'
            },
            'sunlogin': {
                name: 'Sunlogin',
                category: 'remote',
                severity: 'high',
                processes: 'SunloginClient, SunloginService',
                services: 'SunloginService',
                registry: 'Sunlogin',
                paths: "C:\\Program Files\\Oray\\Sunlogin*\nC:\\Program Files (x86)\\Oray\\Sunlogin*",
                description: 'โปรแกรมรีโมทระยะไกล Sunlogin (Oray)'
            },
            'parsec': {
                name: 'Parsec',
                category: 'remote',
                severity: 'high',
                processes: 'parsecd, parsec',
                services: '',
                registry: 'Parsec',
                paths: "C:\\Program Files\\Parsec*",
                description: 'โปรแกรมสตรีมหน้าจอและรีโมทเกม Parsec'
            },
            'splashtop': {
                name: 'Splashtop',
                category: 'remote',
                severity: 'high',
                processes: 'SRFeature, StrStart, SplashtopStreamer',
                services: 'SplashtopRemoteService',
                registry: 'Splashtop',
                paths: "C:\\Program Files\\Splashtop*\nC:\\Program Files (x86)\\Splashtop*",
                description: 'โปรแกรมรีโมทเดสก์ท็อป Splashtop Streamer'
            },
            'radmin': {
                name: 'Radmin',
                category: 'remote',
                severity: 'high',
                processes: 'rserver3, famserver',
                services: 'Radmin Server',
                registry: 'Radmin',
                paths: "C:\\Program Files\\Radmin*\nC:\\Program Files (x86)\\Radmin*",
                description: 'โปรแกรมรีโมทคอมพิวเตอร์ Famatech Radmin'
            },
            'dbeaver': {
                name: 'DBeaver',
                category: 'other',
                severity: 'high',
                processes: 'dbeaver, dbeaver-cli',
                services: '',
                registry: 'DBeaver',
                paths: "C:\\Program Files\\DBeaver*",
                description: 'โปรแกรมจัดการฐานข้อมูล DBeaver Universal Database Tool'
            },
            'wireshark': {
                name: 'Wireshark',
                category: 'other',
                severity: 'critical',
                processes: 'Wireshark, tshark, dumpcap',
                services: 'npcap',
                registry: 'Wireshark, Npcap',
                paths: "C:\\Program Files\\Wireshark*",
                description: 'เครื่องมือดักจับแพ็กเก็ตข้อมูลในเครือข่าย Wireshark Packet Analyzer'
            },
            'bittorrent': {
                name: 'BitTorrent / uTorrent',
                category: 'other',
                severity: 'high',
                processes: 'uTorrent, BitTorrent, qbittorrent',
                services: '',
                registry: 'uTorrent, BitTorrent, qBittorrent',
                paths: "C:\\Program Files\\qBittorrent*\nC:\\Program Files (x86)\\uTorrent*",
                description: 'โปรแกรมดาวน์โหลดไฟล์ Peer-to-Peer BitTorrent'
            },
            'ccleaner': {
                name: 'CCleaner',
                category: 'other',
                severity: 'medium',
                processes: 'CCleaner, CCleaner64',
                services: 'CCleaner Crash Reporting',
                registry: 'CCleaner',
                paths: "C:\\Program Files\\CCleaner*",
                description: 'โปรแกรมล้างไฟล์ขยะและ Registry CCleaner'
            },
            'cheatengine': {
                name: 'Cheat Engine',
                category: 'other',
                severity: 'critical',
                processes: 'cheatengine-x86_64, cheatengine-i386, Cheat Engine',
                services: '',
                registry: 'Cheat Engine',
                paths: "C:\\Program Files\\Cheat Engine*",
                description: 'โปรแกรมแก้ไขหน่วยความจำและดัดแปลงระบบ Cheat Engine'
            },
            'apowermirror': {
                name: 'ApowerMirror',
                category: 'other',
                severity: 'medium',
                processes: 'ApowerMirror, Apowersoft',
                services: '',
                registry: 'ApowerMirror',
                paths: "C:\\Program Files\\Apowersoft\\ApowerMirror*\nC:\\Program Files (x86)\\Apowersoft\\ApowerMirror*",
                description: 'โปรแกรมสะท้อนหน้าจอมือถือ ApowerMirror'
            },
            'scrcpy': {
                name: 'Scrcpy',
                category: 'other',
                severity: 'medium',
                processes: 'scrcpy, adb',
                services: '',
                registry: 'scrcpy',
                paths: "C:\\scrcpy*\nD:\\scrcpy*",
                description: 'โปรแกรมควบคุมหน้าจอมือถือ Android บน PC (Scrcpy)'
            }
        };

        function toggleAdvancedSettings(forceOpen = null) {
            const body = document.getElementById('advancedSettingsBody');
            const icon = document.getElementById('advancedToggleIcon');
            const text = document.getElementById('advancedToggleText');

            if (forceOpen !== null) {
                advancedExpanded = forceOpen;
            } else {
                advancedExpanded = !advancedExpanded;
            }

            if (advancedExpanded) {
                body.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
                text.innerText = 'ซ่อนรายละเอียด';
            } else {
                body.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
                text.innerText = 'คลิกเพื่อดูหรือปรับแต่ง';
            }
        }

        function handleNameInput(val) {
            const trimmed = (val || '').trim();
            const badge = document.getElementById('autoPresetBadge');

            if (!trimmed) {
                badge.style.display = 'none';
                if (currentMode === 'add') {
                    document.getElementById('ruleProcesses').value = '';
                    document.getElementById('ruleServices').value = '';
                    document.getElementById('ruleRegistry').value = '';
                    document.getElementById('rulePaths').value = '';
                    document.getElementById('ruleDescription').value = '';
                }
                return;
            }

            badge.style.display = 'inline-flex';

            const cleanKey = trimmed.toLowerCase().replace(/[^a-z0-9]/g, '');
            let matchedPreset = null;

            for (const k in presets) {
                if (cleanKey === k || cleanKey.includes(k) || k.includes(cleanKey)) {
                    matchedPreset = presets[k];
                    break;
                }
            }

            if (matchedPreset) {
                if (currentMode === 'add') {
                    document.getElementById('ruleCategory').value = matchedPreset.category;
                    document.getElementById('ruleSeverity').value = matchedPreset.severity;
                }
                document.getElementById('ruleProcesses').value = matchedPreset.processes;
                document.getElementById('ruleServices').value = matchedPreset.services || '';
                document.getElementById('ruleRegistry').value = matchedPreset.registry;
                document.getElementById('rulePaths').value = matchedPreset.paths;
                document.getElementById('ruleDescription').value = matchedPreset.description;
            } else {
                // Intelligent generic generation
                const noSpace = trimmed.replace(/\s+/g, '');
                const processes = Array.from(new Set([trimmed, noSpace, noSpace.toLowerCase()])).join(', ');
                const paths = `C:\\Program Files\\${trimmed}*\nC:\\Program Files (x86)\\${trimmed}*\nC:\\ProgramData\\${trimmed}*`;
                const cat = document.getElementById('ruleCategory').value;
                const catText = cat === 'remote' ? 'โปรแกรมรีโมทควบคุมระยะไกล' : (cat === 'emulator' ? 'โปรแกรมจำลองแอนดรอยด์' : 'โปรแกรมเสี่ยง');

                document.getElementById('ruleProcesses').value = processes;
                document.getElementById('ruleServices').value = '';
                document.getElementById('ruleRegistry').value = trimmed;
                document.getElementById('rulePaths').value = paths;
                document.getElementById('ruleDescription').value = `ตรวจจับ${catText} ${trimmed}`;
            }
        }

        function handleCategoryChange() {
            const name = document.getElementById('ruleName').value;
            if (name && currentMode === 'add') {
                const descInput = document.getElementById('ruleDescription');
                if (!descInput.value || descInput.value.startsWith('ตรวจจับ')) {
                    const cat = document.getElementById('ruleCategory').value;
                    const catText = cat === 'remote' ? 'โปรแกรมรีโมทควบคุมระยะไกล' : (cat === 'emulator' ? 'โปรแกรมจำลองแอนดรอยด์' : 'โปรแกรมเสี่ยง');
                    descInput.value = `ตรวจจับ${catText} ${name.trim()}`;
                }
            }
        }

        function applyPreset(name) {
            document.getElementById('ruleName').value = name;
            handleNameInput(name);
        }

        function openAddModal() {
            currentMode = 'add';
            document.getElementById('modalTitle').innerText = 'เพิ่มโปรแกรมเสี่ยงใหม่';
            document.getElementById('modalSubtitle').innerText = 'ระบุชื่อโปรแกรม หมวดหมู่ และระดับความเสี่ยง ระบบจะช่วยเติมเงื่อนไขให้ครบถ้วนอัตโนมัติ';
            document.getElementById('ruleForm').action = "{{ route('admin.risk-rules.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('ruleName').value = '';
            document.getElementById('ruleCategory').value = 'other';
            document.getElementById('ruleSeverity').value = 'high';
            document.getElementById('ruleProcesses').value = '';
            document.getElementById('ruleServices').value = '';
            document.getElementById('ruleRegistry').value = '';
            document.getElementById('rulePaths').value = '';
            document.getElementById('ruleDescription').value = '';
            document.getElementById('ruleIsActive').checked = true;
            document.getElementById('autoPresetBadge').style.display = 'none';
            document.getElementById('presetPillsContainer').style.display = 'flex';
            document.getElementById('btnSubmit').innerText = 'เพิ่มโปรแกรมเสี่ยง';

            toggleAdvancedSettings(false); // Collapsed by default for clean add
            document.getElementById('ruleModal').classList.add('show');
            setTimeout(() => document.getElementById('ruleName').focus(), 100);
        }

        function openEditModal(rule) {
            currentMode = 'edit';
            document.getElementById('modalTitle').innerText = 'แก้ไขเงื่อนไข: ' + rule.name;
            document.getElementById('modalSubtitle').innerText = 'ปรับแต่งรายละเอียดเงื่อนไขการตรวจจับของโปรแกรมนี้';
            document.getElementById('ruleForm').action = "/admin/risk-rules/" + rule.id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('ruleName').value = rule.name || '';
            document.getElementById('ruleCategory').value = rule.category || 'other';
            document.getElementById('ruleSeverity').value = rule.severity || 'high';
            document.getElementById('ruleProcesses').value = rule.process_names || '';
            document.getElementById('ruleServices').value = rule.service_names || '';
            document.getElementById('ruleRegistry').value = rule.registry_patterns || '';
            document.getElementById('rulePaths').value = rule.file_paths || '';
            document.getElementById('ruleDescription').value = rule.description || '';
            document.getElementById('ruleIsActive').checked = !!rule.is_active;
            document.getElementById('autoPresetBadge').style.display = 'none';
            document.getElementById('presetPillsContainer').style.display = 'none';
            document.getElementById('btnSubmit').innerText = 'บันทึกการแก้ไข';

            toggleAdvancedSettings(true); // Expanded in edit mode so full details can be inspected
            document.getElementById('ruleModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('ruleModal').classList.remove('show');
        }

        async function toggleRule(id) {
            try {
                const res = await fetch(`/admin/risk-rules/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (!data.success) {
                    alert('เกิดข้อผิดพลาดในการเปลี่ยนสถานะ');
                }
            } catch (e) {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        }

        async function deleteRule(id, name) {
            if (!confirm(`คุณต้องการลบเงื่อนไขโปรแกรมเสี่ยง "${name}" หรือไม่?`)) {
                return;
            }

            try {
                const res = await fetch(`/admin/risk-rules/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.getElementById(`rule-row-${id}`);
                    if (row) row.remove();
                } else {
                    alert('ไม่สามารถลบรายการได้');
                }
            } catch (e) {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        }

        // Close modal on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>

