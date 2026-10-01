<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การใช้งานระบบโรงพยาบาล - CSH Digital Health</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --pastel-blue-light: #E3F2FD;
            --pastel-blue-medium: #90CAF9;
            --pastel-blue-dark: #64B5F6;
            --text-blue-dark: #2C3E50;
            --white: #ffffff;
            --bg-gray: #F8FAFC;
        }

        body { font-family: 'Prompt', sans-serif; margin: 0; background-color: var(--bg-gray); color: var(--text-blue-dark); }

        /* --- Navbar --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.8rem 4%; background: var(--white); box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 1000;
        }
        .btn-back { padding: 8px 20px; border-radius: 50px; background: var(--pastel-blue-light); text-decoration: none; color: var(--text-blue-dark); font-weight: 600; font-size: 0.85rem; }

        /* --- Header --- */
        .header-banner { background: linear-gradient(135deg, var(--pastel-blue-dark), var(--pastel-blue-medium)); color: white; padding: 3rem 4%; text-align: center; }
        .header-banner h1 { margin: 0; font-size: 2.2rem; }

        /* --- Content Grid --- */
        .container { max-width: 1200px; margin: 3rem auto; padding: 0 20px; }
        .system-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }

        .system-card { background: var(--white); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.03); border: 1px solid #edf2f7; }
        .card-header { background: var(--pastel-blue-light); padding: 15px 20px; font-weight: 600; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--pastel-blue-medium); }
        
        .manual-list { list-style: none; padding: 0; margin: 0; }
        .manual-item { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; }
        .manual-item:last-child { border: none; }
        .manual-item:hover { background: #fdfdfd; }
        .manual-name { font-size: 0.95rem; color: var(--text-blue-dark); }
        
        .btn-download { padding: 5px 12px; border-radius: 6px; background: #e2e8f0; color: #475569; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: 0.3s; }
        .btn-download:hover { background: var(--pastel-blue-dark); color: white; }

        footer { text-align: center; padding: 3rem; color: #94a3b8; font-size: 0.85rem; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand"><strong>โรงพยาบาลเชียงแสน</strong> | <small>HOSxP Manuals</small></div>
        <a href="/knowledge" class="btn-back">← กลับหน้าคลังความรู้</a>
    </nav>

    <header class="header-banner">
        <h1>คู่มือการใช้งานระบบโรงพยาบาล (HOSxP)</h1>
        <p>สนับสนุนการทำงานอย่างมีประสิทธิภาพตามมาตรฐาน HA</p>
    </header>

    <div class="container">
        <div class="system-grid">
            
            <div class="system-card">
                <div class="card-header">📂 งานเวชระเบียน / คัดกรอง</div>
                <div class="manual-list">
                    <div class="manual-item"><span class="manual-name">การลงทะเบียนผู้ป่วยใหม่</span> <a href="#" class="btn-download">PDF</a></div>
                    <div class="manual-item"><span class="manual-name">การส่งต่อผู้ป่วย (Refer Out)</span> <a href="#" class="btn-download">PDF</a></div>
                </div>
            </div>

            <div class="system-card">
                <div class="card-header">🩺 งานห้องตรวจผู้ป่วยนอก (OPD)</div>
                <div class="manual-list">
                    <div class="manual-item"><span class="manual-name">การบันทึกวินิจฉัยและสั่งยา</span> <a href="#" class="btn-download">PDF</a></div>
                    <div class="manual-item"><span class="manual-name">การขอนัดหมายผู้ป่วยล่วงหน้า</span> <a href="#" class="btn-download">PDF</a></div>
                </div>
            </div>

            <div class="system-card">
                <div class="card-header">🌟 คลินิกพิเศษ</div>
                <div class="manual-list">
                    <div class="manual-item"><span class="manual-name">คู่มือระบบคลินิกเบาหวาน/ความดัน</span> <a href="#" class="btn-download">PDF</a></div>
                    <div class="manual-item"><span class="manual-name">คู่มือระบบคลินิกฝากครรภ์ (ANC)</span> <a href="#" class="btn-download">PDF</a></div>
                </div>
            </div>

            <div class="system-card">
                <div class="card-header">🏥 ระบบผู้ป่วยใน (IPD)</div>
                <div class="manual-list">
                    <div class="manual-item"><span class="manual-name">การรับ Admit และจำหน่ายผู้ป่วย</span> <a href="#" class="btn-download">PDF</a></div>
                    <div class="manual-item"><span class="manual-name">การบันทึกการพยาบาล (Nurse Note)</span> <a href="#" class="btn-download">PDF</a></div>
                </div>
            </div>

             <div class="system-card">
                <div class="card-header">💊 งานเภสัชกรรม (Pharmacy)</div>
                <div class="manual-list">
                    <div class="manual-item"><span class="manual-name">การตรวจสอบยาและจ่ายยา</span> <a href="#" class="btn-download">PDF</a></div>
                    <div class="manual-item"><span class="manual-name">ระบบคลังยาและเวชภัณฑ์</span> <a href="#" class="btn-download">PDF</a></div>
                </div>
            </div>

             <div class="system-card">
                <div class="card-header">💰 งานการเงินและสิทธิ</div>
                <div class="manual-list">
                    <div class="manual-item"><span class="manual-name">การตรวจสอบสิทธิ์และการออกใบเสร็จ</span> <a href="#" class="btn-download">PDF</a></div>
                </div>
            </div>

        </div>
    </div>

    <footer>
        © 2026 กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลเชียงแสน | Smart Hospital Level F1
    </footer>

</body>
</html>