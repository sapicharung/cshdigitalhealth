<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Digital Health - ระบบบริการสารสนเทศสำหรับเจ้าหน้าที่</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --pastel-blue-light: #E3F2FD;
            --pastel-blue-medium: #90CAF9;
            --pastel-blue-dark: #64B5F6;
            --pastel-cream: #FFFDF5;
            --text-blue-dark: #2C3E50;
            --accent-peach: #FFCCBC;
            --accent-mint: #B2DFDB;
            --white: #ffffff;
        }

        body {
            font-family: 'Prompt', sans-serif;
            margin: 0; padding: 0;
            color: var(--text-blue-dark);
            background-color: var(--pastel-cream);
            overflow-x: hidden;
        }
        h1, h2, h3, h4 { font-weight: 600; margin: 0; }
        p { line-height: 1.6; font-weight: 300; margin: 0; }
        .container { width: 94%; max-width: 1300px; margin: auto; }

        /* --- Navbar --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.8rem 4%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.03);
            position: sticky; top: 0; z-index: 1000;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { height: 50px; width: auto; }
        .brand-text strong { display: block; font-size: 1.5rem; }
        .brand-text small { color: var(--pastel-blue-medium); font-size: 0.85rem; }

        /* --- ปรับปรุงส่วนปุ่มบน Navbar --- */
        .nav-actions { display: flex; gap: 10px; align-items: center; }
        
        .btn-login, .btn-knowledge { 
            padding: 10px 25px; border-radius: 50px; 
            border: none; font-weight: 600; cursor: pointer; 
            font-family: 'Prompt'; font-size: 0.9rem;
            transition: 0.3s; text-decoration: none; display: inline-block;
        }
        
        .btn-login { background: var(--accent-mint); color: var(--text-blue-dark); }
        .btn-login:hover { background-color: #98d1cc; transform: translateY(-2px); }

        .btn-knowledge { 
            background: var(--pastel-blue-light); 
            color: var(--text-blue-dark);
            border: 1px solid var(--pastel-blue-medium);
        }
        .btn-knowledge:hover { background-color: var(--pastel-blue-medium); color: white; transform: translateY(-2px); }

        /* --- Hero Section --- */
        .hero {
            background: linear-gradient(135deg, #f0f7ff 0%, var(--pastel-cream) 100%);
            padding: 3rem 4% 6rem 4%;
            position: relative;
            min-height: 35vh;
            display: flex; align-items: center;
        }
        .hero-container {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 50px;
            width: 100%; max-width: 1250px; margin: auto; z-index: 2;
        }

        .hero-left h4 { 
            color: var(--pastel-blue-dark); 
            font-size: 1.1rem;
            letter-spacing: 2px; 
            margin-bottom: 10px; 
        }
        .hero-left h1 { 
            font-size: 2.2rem;
            color: var(--text-blue-dark); 
            line-height: 1.3; 
            margin-bottom: 25px; 
        }
        
        .values-section h5 { 
            font-size: 1.2rem;
            color: var(--pastel-blue-dark); 
            margin-bottom: 15px; 
        }
        .moph-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .value-item { 
            background: var(--white); 
            padding: 12px 15px; 
            border-radius: 12px; 
            font-size: 0.9rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            border-left: 5px solid var(--pastel-blue-medium);
        }
        .value-item strong { color: var(--pastel-blue-dark); font-weight: 600; }

        .goals-container {
            background: rgba(255, 255, 255, 0.6); 
            padding: 30px; 
            border-radius: 35px;
            box-shadow: 0 10px 30px rgba(144, 202, 249, 0.1);
        }
        .goals-container h5 { 
            font-size: 1.3rem;
            margin-bottom: 20px; 
            color: var(--text-blue-dark); 
            border-left: 6px solid var(--accent-peach); 
            padding-left: 15px; 
        }
        .goal-list { display: grid; gap: 15px; }
        .goal-card { 
            display: flex; 
            gap: 15px; 
            align-items: flex-start; 
            background: var(--white); 
            padding: 15px; 
            border-radius: 20px; 
        }
        .goal-tag { 
            padding: 6px 12px; 
            border-radius: 10px; 
            font-size: 0.85rem; 
            font-weight: 600; 
            background: var(--pastel-blue-light); 
            color: var(--text-blue-dark); 
            flex-shrink: 0;
        }
        .goal-card p { 
            font-size: 0.95rem;
            color: #455a64; 
            line-height: 1.5; 
        }

        .wave-divider {
            position: absolute; bottom: 0; left: 0; width: 100%; line-height: 0; transform: rotate(180deg);
        }
        .wave-divider svg { display: block; width: calc(100% + 1.3px); height: 80px; fill: var(--pastel-cream); }

        /* --- Services Section --- */
        .services { padding: 4rem 4%; text-align: center; }
        .section-header h2 { 
            font-size: 2rem; 
            color: var(--text-blue-dark); 
            position: relative; 
            display: inline-block; padding-bottom: 15px; margin-bottom: 3rem;
        }
        .section-header h2::after { content: ''; position: absolute; bottom: 0; left: 20%; width: 60%; height: 5px; background: var(--accent-peach); border-radius: 10px; }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px; justify-content: center;
        }

        .service-card {
            background: var(--white); padding: 30px 20px; border-radius: 28px;
            box-shadow: 0 8px 20px rgba(144, 202, 249, 0.06);
            transition: 0.4s; display: flex; flex-direction: column; align-items: center;
            text-decoration: none; border: 1px solid rgba(144, 202, 249, 0.1);
        }
        .service-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(144, 202, 249, 0.15); border-color: var(--pastel-blue-medium); }
        
        .icon-box {
            width: 65px; height: 65px; margin-bottom: 15px;
            background: var(--pastel-blue-light); border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
        }
        .service-card h3 { font-size: 1rem; color: var(--text-blue-dark); line-height: 1.4; font-weight: 600; }

        footer { background: var(--pastel-blue-light); padding: 2.5rem; text-align: center; font-size: 0.95rem; color: #546e7a; }

        @media (max-width: 1100px) {
            .hero-container { grid-template-columns: 1fr; gap: 30px; }
            .hero-left h1 { font-size: 1.8rem; }
            .nav-actions { gap: 5px; }
            .btn-login, .btn-knowledge { padding: 8px 15px; font-size: 0.8rem; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand">
            <div class="brand-text">
                <strong>โรงพยาบาลเชียงแสน</strong>
                <small>CHIANGSAEN HOSPITAL</small>
            </div>
        </div>
        <div class="nav-actions">
            <a href="{{ route('knowledge') }}" class="btn-knowledge">คลังความรู้</a>
           {{--  <a href="#" class="btn-knowledge">คลังความรู้</a> --}}
            <button class="btn-login">เข้าสู่ระบบเจ้าหน้าที่</button>
        </div>
    </nav>

   {{--  <section class="hero">
        <div class="hero-container">
            <div class="hero-left">
                <h4>VISION</h4>
                <h1>โรงพยาบาลเชียงแสนเป็นโรงพยาบาลระดับ F1 ในเขตเศรษฐกิจพิเศษชายแดนที่มีคุณภาพ</h1>
                
                <div class="values-section">
                    <h5>ค่านิยม (MOPH)</h5>
                    <div class="moph-grid">
                        <div class="value-item"><strong>M (Mastery):</strong> เป็นนายตัวเอง</div>
                        <div class="value-item"><strong>O (Originality):</strong> เร่งสร้างสิ่งใหม่</div>
                        <div class="value-item"><strong>P (People centered):</strong> ใส่ใจประชาชน</div>
                        <div class="value-item"><strong>H (Humility):</strong> ถ่อมตน อ่อนน้อม</div>
                    </div>
                </div>
            </div>

            <div class="goals-container">
                <h5>เป้าหมายหลัก (Goals)</h5>
                <div class="goal-list">
                    <div class="goal-card">
                        <div class="goal-tag">Goal 1</div>
                        <p><strong>Safety & Security:</strong> สร้างความมั่นคงทางสุขภาพ ด้วยระบบเฝ้าระวังโรคติดต่อ ข้ามพรมแดนที่รวดเร็วและแม่นยำ</p>
                    </div>
                    <div class="goal-card">
                        <div class="goal-tag" style="background:var(--accent-mint)">Goal 2</div>
                        <p><strong>Service Excellence:</strong> ยกระดับบริการการแพทย์ระดับ F1 ให้มีคุณภาพมาตรฐาน (HA) และบริหารจัดการด้วยเทคโนโลยีดิจิทัล (Smart Hospital)</p>
                    </div>
                    <div class="goal-card">
                        <div class="goal-tag" style="background:var(--accent-peach)">Goal 3</div>
                        <p><strong>Economic Support:</strong> เป็นศูนย์กลางการจัดการสุขภาพแรงงานและประชากรแฝง เพื่อสนับสนุนการเติบโตของเขตเศรษฐกิจพิเศษ</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="wave-divider">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path></svg>
        </div>
    </section> --}}

    <section class="services">
        <div class="container">
            <div class="section-header">
                <h2>ระบบบริการสารสนเทศสำหรับเจ้าหน้าที่</h2>
            </div>
            
            <div class="service-grid">
                <a href="http://192.168.1.2/" target="_blank" class="service-card"><div class="icon-box">🏢</div><h3>SmartOffice</h3></a>
                <a href="http://192.168.1.10:8083/" target="_blank" class="service-card" ><div class="icon-box">📈</div><h3>CSH-DATACENTER</h3></a>
		<a href="http://192.168.1.10:8088/" target="_blank" class="service-card" ><div class="icon-box">📊</div><h3>DATA-Report</h3></a>
                <a href="https://csh.thai-nrls.org/" target="_blank" class="service-card"><div class="icon-box">⚠️</div><h3>รายงานความเสี่ยง</h3></a>
                <a href="http://192.168.1.11/stock_computer/" target="_blank" class="service-card"><div class="icon-box">🔧</div><h3>แจ้งซ่อมคอมพิวเตอร์</h3></a>
                <a href="http://192.168.1.10:8087/" target="_blank" class="service-card"><div class="icon-box">☎️</div><h3>IT-Oncall</h3></a>
                <a href="http://192.168.1.11:8090/" target="_blank" class="service-card"><div class="icon-box">💵</div><h3>สลิปเงินเดือน</h3></a>
                <a href="http://192.168.1.9/vopds" target="_blank" class="service-card"><div class="icon-box">📂</div><h3>OPD-SCAN</h3></a>
                <a href="#" class="service-card"><div class="icon-box">🎯</div><h3>ระบบติดตามข้อสั่งการ</h3></a>
                <a href="https://hdc.moph.go.th/cri" target="_blank" class="service-card"><div class="icon-box">📊</div><h3>HDC</h3></a>
                <a href="http://192.168.1.13:5173/" target="_blank" class="service-card"><div class="icon-box">🧠</div><h3>AI-Generator</h3></a>
                <a href="http://192.168.1.10:8082/" target="_blank" class="service-card"><div class="icon-box">📄</div><h3>สารบรรณการเงิน</h3></a>
                <a href="http://192.168.1.20/product" target="_blank" class="service-card"><div class="icon-box">🚀</div><h3>Nurse-Productivity</h3></a>
                <a href="https://mmis-11193.moph.go.th/" target="_blank" class="service-card"><div class="icon-box">📦</div><h3>MMIS</h3></a>
                <a href="https://homeward.dms.go.th/" target="_blank" class="service-card"><div class="icon-box">🏠</div><h3>HomeWard</h3></a>
                <a href="http://192.168.1.20/kumhosnapapi" target="_blank" class="service-card"><div class="icon-box">🧬</div><h3>API_Nap-LAB</h3></a>
                <a href="http://192.168.1.10:8081/" target="_blank" class="service-card"><div class="icon-box">⚖️</div><h3>ระบบใบคดี</h3></a>
		<a href="https://docs.google.com/spreadsheets/d/183Pye9uc-YIGaRWXNo4uXOwuOxyklrmRlyBKvTP2e3s/edit?usp=drivesdk" target="_blank" class="service-card"><div class="icon-box">🏥</div><h3>BloodAlc</h3></a>
		 <a href="http://192.168.1.10:8089/" target="_blank" class="service-card"><div class="icon-box">💉</div><h3>คลังวัสดุการแพทย์</h3></a>            
	</div>
        </div>
    </section>

    <footer>
        <p>© 2026 กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลเชียงแสน (Chiang Saen Hospital)</p>
    </footer>

</body>
</html>