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

        /* --- Header Navbar --- */
        .navbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 100;
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

        /* --- Container --- */
        .container {
            max-width: 1400px;
            width: 96%;
            margin: 1.5rem auto;
            flex: 1;
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

            <button type="button" class="btn-nav btn-primary" onclick="openAddModal()">
                <span class="material-symbols-Outlined">add_circle</span>
                <span>เพิ่มโปรแกรมเสี่ยงใหม่</span>
            </button>
        </div>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert-success">
                <span class="material-symbols-Outlined">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span class="material-symbols-Outlined" style="color: #EF4444;">policy</span>
                    <span>รายการโปรแกรมเสี่ยงที่ระบบตรวจสอบ ({{ $rules->count() }} รายการ)</span>
                </div>
                <div style="font-size: 0.82rem; color: #64748B;">
                    💡 สามารถเปิด/ปิดการตรวจจับ หรือแก้ไขเงื่อนไขได้ทันที เครื่องลูกจะดึงกติกาใหม่ไปใช้ในการ Sync รอบถัดไป
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">เปิดใช้งาน</th>
                            <th style="width: 180px;">ชื่อโปรแกรม</th>
                            <th style="width: 120px;">หมวดหมู่</th>
                            <th style="width: 100px;">ความเสี่ยง</th>
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
                                    <div style="font-size: 0.8rem; line-height: 1.5;">
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
                                            <div style="color: #64748B; max-width: 480px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $rule->file_paths }}">
                                                <strong>Path:</strong> {{ str_replace("\n", ", ", $rule->file_paths) }}
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
                <h3 style="font-size: 1.05rem; font-weight: 700;" id="modalTitle">เพิ่มโปรแกรมเสี่ยงใหม่</h3>
                <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748B;">&times;</button>
            </div>
            <form id="ruleForm" method="POST" action="{{ route('admin.risk-rules.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">ชื่อโปรแกรมเสี่ยง *</label>
                        <input type="text" name="name" id="ruleName" class="form-control" placeholder="เช่น TeamViewer, AnyViewer, BlueStacks X" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">หมวดหมู่ *</label>
                            <select name="category" id="ruleCategory" class="form-control" required>
                                <option value="remote">รีโมทควบคุมระยะไกล (Remote Tool)</option>
                                <option value="emulator">โปรแกรมจำลอง Android (Emulator)</option>
                                <option value="other">โปรแกรมเสี่ยงอื่นๆ (Other)</option>
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

                    <div class="form-group">
                        <label class="form-label">ชื่อ Process ใน Task Manager (คั่นด้วยจุลภาค ,)</label>
                        <input type="text" name="process_names" id="ruleProcesses" class="form-control" placeholder="เช่น TeamViewer, tv_w32, remoting_host">
                        <div class="form-hint">ระบุชื่อ process ของโปรแกรมที่ต้องการตรวจจับ (ไม่ต้องใส่นามสกุล .exe)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ชื่อ Windows Service (คั่นด้วยจุลภาค ,)</label>
                        <input type="text" name="service_names" id="ruleServices" class="form-control" placeholder="เช่น TeamViewer, UltraViewerService">
                        <div class="form-hint">ระบุชื่อ Service ของโปรแกรม หากติดตั้งเป็น Background Service</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">คีย์เวิร์ดใน Windows Registry Uninstall (คั่นด้วยจุลภาค ,)</label>
                        <input type="text" name="registry_patterns" id="ruleRegistry" class="form-control" placeholder="เช่น BlueStacks, LDPlayer, RustDesk">
                        <div class="form-hint">คำค้นหาชื่อโปรแกรมในรายการ Add/Remove Programs ของ Windows</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ตำแหน่งโฟลเดอร์หรือไฟล์ติดตั้ง (คั่นด้วยบรรทัดใหม่หรือจุลภาค)</label>
                        <textarea name="file_paths" id="rulePaths" class="form-control" rows="3" placeholder="C:\Program Files\Example&#10;C:\Program Files (x86)\Example"></textarea>
                        <div class="form-hint">พาธโฟลเดอร์ที่โปรแกรมมักติดตั้งไว้ในเครื่องลูก</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">คำอธิบายเพิ่มเติม / เหตุผลความเสี่ยง</label>
                        <textarea name="description" id="ruleDescription" class="form-control" rows="2" placeholder="อธิบายรายละเอียดความเสี่ยง หรือคำแนะนำสำหรับแอดมิน"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="ruleIsActive" value="1" checked>
                            <span style="font-size: 0.88rem; font-weight: 500;">เปิดใช้งานการตรวจจับทันที</span>
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

        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'เพิ่มโปรแกรมเสี่ยงใหม่';
            document.getElementById('ruleForm').action = "{{ route('admin.risk-rules.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('ruleName').value = '';
            document.getElementById('ruleCategory').value = 'remote';
            document.getElementById('ruleSeverity').value = 'high';
            document.getElementById('ruleProcesses').value = '';
            document.getElementById('ruleServices').value = '';
            document.getElementById('ruleRegistry').value = '';
            document.getElementById('rulePaths').value = '';
            document.getElementById('ruleDescription').value = '';
            document.getElementById('ruleIsActive').checked = true;
            document.getElementById('btnSubmit').innerText = 'เพิ่มโปรแกรมเสี่ยง';
            document.getElementById('ruleModal').classList.add('show');
        }

        function openEditModal(rule) {
            document.getElementById('modalTitle').innerText = 'แก้ไขเงื่อนไข: ' + rule.name;
            document.getElementById('ruleForm').action = "/admin/risk-rules/" + rule.id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('ruleName').value = rule.name || '';
            document.getElementById('ruleCategory').value = rule.category || 'remote';
            document.getElementById('ruleSeverity').value = rule.severity || 'high';
            document.getElementById('ruleProcesses').value = rule.process_names || '';
            document.getElementById('ruleServices').value = rule.service_names || '';
            document.getElementById('ruleRegistry').value = rule.registry_patterns || '';
            document.getElementById('rulePaths').value = rule.file_paths || '';
            document.getElementById('ruleDescription').value = rule.description || '';
            document.getElementById('ruleIsActive').checked = !!rule.is_active;
            document.getElementById('btnSubmit').innerText = 'บันทึกการแก้ไข';
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

