<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแล - CSHOS DATACENTER</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <style>
        :root {
            --primary: #4D9DE0;
            --primary-dark: #2F7EC2;
            --primary-light: #EBF4FC;
            --bg-color: #F4F8FC;
            --card-bg: #FFFFFF;
            --text-main: #2C3E50;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --danger: #EF4444;
            --success: #10B981;
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
            background: linear-gradient(135deg, #E6F3FE 0%, #F5F9FD 50%, #E3EFFF 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
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

        .login-card {
            background: var(--card-bg);
            width: 100%;
            max-width: 440px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(77, 157, 224, 0.12), 0 5px 15px rgba(0, 0, 0, 0.04);
            padding: 2.5rem 2.2rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #7EC8F8, #4D9DE0, #7FE3D5);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-circle {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.2rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #7EC8F8, #4D9DE0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 10px 20px rgba(77, 157, 224, 0.3);
        }

        .logo-circle .material-symbols-outlined {
            font-size: 38px;
        }

        .brand-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1E293B;
            margin-bottom: 0.35rem;
            line-height: 1.35;
            letter-spacing: -0.2px;
        }

        .brand-header p {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 12px;
            font-size: 0.88rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            line-height: 1.45;
        }

        .alert-error {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .alert-success {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .form-group {
            margin-bottom: 1.3rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: #334155;
            margin-bottom: 0.45rem;
            line-height: 1.4;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper .material-symbols-outlined {
            position: absolute;
            left: 14px;
            color: #94A3B8;
            font-size: 20px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border: 1.5px solid var(--border-color);
            border-radius: 14px;
            font-size: 0.95rem;
            font-family: inherit;
            color: #1E293B;
            background: #FAFAFC;
            transition: all 0.2s ease;
            outline: none;
            line-height: 1.5;
        }

        .form-control:focus {
            background: #FFFFFF;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(77, 157, 224, 0.15);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .toggle-password:hover {
            color: #64748B;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
            line-height: 1.4;
        }

        .remember-checkbox input {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
        }

        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #7EC8F8, #4D9DE0);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(77, 157, 224, 0.35);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            line-height: 1.4;
            letter-spacing: 0.01em;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px rgba(77, 157, 224, 0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .back-home {
            text-align: center;
            margin-top: 1.8rem;
        }

        .back-home a {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            transition: color 0.2s ease;
            line-height: 1.4;
        }

        .back-home a:hover {
            color: var(--primary);
        }

        .credentials-tip {
            margin-top: 1.5rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            font-size: 0.82rem;
            color: #64748B;
            text-align: center;
            line-height: 1.5;
        }
        .credentials-tip strong {
            color: #334155;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <div class="logo-circle">
                <span class="material-symbols-outlined">query_stats</span>
            </div>
            <h1>CSHOS DATACENTER</h1>
            <p>ระบบตรวจสอบการติดตั้งสำหรับผู้ดูแลระบบ</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <span class="material-symbols-outlined" style="font-size: 18px;">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="login">ชื่อผู้ใช้งาน หรือ อีเมล</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined">person</span>
                    <input type="text" id="login" name="login" class="form-control" 
                           placeholder="admin หรือ admin@csh.local" 
                           value="{{ old('login', 'admin') }}" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label for="password">รหัสผ่าน</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined">lock</span>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility()" title="แสดง/ซ่อนรหัสผ่าน">
                        <span class="material-symbols-outlined" id="togglePasswordIcon">visibility</span>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-checkbox">
                    <input type="checkbox" name="remember" value="1" checked>
                    <span>จดจำการเข้าสู่ระบบ</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span class="material-symbols-outlined">login</span>
                <span>เข้าสู่ระบบ</span>
            </button>
        </form>

        <div class="credentials-tip">
            บัญชีเริ่มต้น: ชื่อผู้ใช้ <strong>admin</strong> | รหัสผ่าน <strong>admin1234</strong>
        </div>

        <div class="back-home">
            <a href="{{ url('/') }}">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                <span>กลับสู่หน้าหลัก CSH Digital Health</span>
            </a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
