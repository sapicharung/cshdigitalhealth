<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คลังความรู้ - CSH Digital Health</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-body: #FFFDF5;
            --blue-accent: #007bff;
            --text-dark: #2C3E50;
        }
        body { font-family: 'Prompt', sans-serif; background-color: var(--bg-body); color: var(--text-dark); margin: 0; }
        
        .navbar-custom {
            background: white; padding: 1rem 5%; box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            display: flex; justify-content: space-between; align-items: center;
        }

        /* Container หลักที่ควบคุมความกว้างให้เท่ากันทั้ง Card และ Table */
        .page-container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }

        .hero-section { background-color: #EBF5FF; padding: 3rem 0; text-align: center; margin-bottom: 3rem; }
        .hero-section h1 { font-size: 2.2rem; font-weight: 600; margin-bottom: 10px; }

        /* หมวดหมู่แถวละ 4 */
        .category-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
            margin-bottom: 4rem;
        }
        .category-card {
            background: white; padding: 40px 20px; border-radius: 25px; text-decoration: none; color: inherit;
            display: flex; flex-direction: column; align-items: center; text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03); transition: 0.3s;
        }
        .category-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .cat-icon { font-size: 3.5rem; margin-bottom: 20px; }
        .category-card h3 { font-size: 1.25rem; margin-bottom: 10px; font-weight: 600; }
        .category-card p { font-size: 0.85rem; color: #666; line-height: 1.6; }

        /* หัวข้อส่วนที่มีเส้นสีฟ้าด้านหน้า */
        .section-header { border-left: 5px solid var(--blue-accent); padding-left: 15px; margin-bottom: 2rem; font-size: 1.6rem; font-weight: 600; }

        /* ส่วนตารางที่ความกว้างเท่ากับ Card */
        .table-container { background: white; border-radius: 25px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057; }
        
        .btn-view { border-radius: 50px; border: 1.5px solid #dee2e6; color: #333; text-decoration: none; padding: 6px 15px; font-size: 0.85rem; font-weight: 600; }
        .btn-view:hover { background: #f1f1f1; }

        @media (max-width: 1200px) { .category-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .category-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <nav class="navbar-custom">
        <a href="{{ route('knowledge') }}" style="text-decoration:none; color:inherit;">
            <strong>โรงพยาบาลเชียงแสน</strong><br><small>CHIANGSAEN HOSPITAL</small>
        </a>
        <div>
            <a href="{{ route('knowledge.upload') }}" class="btn btn-info rounded-pill px-4 text-white" style="background-color: #99dcd4; border:none;">📤 อัปโหลดความรู้</a>
            <a href="/" class="btn btn-outline-primary rounded-pill px-4">กลับหน้าหลัก</a>
        </div>
    </nav>

    <div class="hero-section">
        <h1>{{ $title ?? 'การจัดการความรู้ โรงพยาบาลเชียงแสน' }}</h1>
        <p class="text-muted">KM-เพื่อขับเคลื่อนความปลอดภัยในโรงพยาบาล รวมคู่มือการใช้งานและความรู้สำคัญขององค์กร</p>
    </div>

    <div class="page-container">
        
        <h2 class="section-header">หมวดหมู่ความรู้</h2>
        <div class="category-grid">
            <a href="{{ route('knowledge.category', 'cpg') }}" class="category-card">
                <div class="cat-icon">📜</div>
                <h3>CPG (แนวปฏิบัติ)</h3>
                <p>แนวทางการดูแลรักษาผู้ป่วยที่เหมาะสม ปลอดภัย และมีมาตรฐานเดียวกัน</p>
            </a>
            <a href="{{ route('knowledge.category', 'research') }}" class="category-card">
                <div class="cat-icon">🔬</div>
                <h3>งานวิจัย & CQI</h3>
                <p>นวัตกรรมและ R2R เพื่อยกระดับองค์กร</p>
            </a>
            <a href="{{ route('knowledge.category', 'bcp') }}" class="category-card">
                <div class="cat-icon">🛡️</div>
                <h3>แผนปฏิบัติการฯ</h3>
                <p>แผนยุทธศาสตร์เตรียมพร้อมรับมือสถานการณ์ฉุกเฉินและภัยพิบัติ</p>
            </a>
            <a href="{{ route('knowledge.hosxp') }}" class="category-card">
                <div class="cat-icon">🖥️</div>
                <h3>คู่มือ/เอกสารทั่วไป</h3>
                <p>คู่มือการใช้งาน, แบบฟอร์มต่างๆ, เอกสารทั่วไป</p>
            </a>
        </div>

        <h2 class="section-header">บทความล่าสุด</h2>
        <div class="table-container mb-5">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th width="15%">วันที่</th>
                        <th width="50%">ชื่อหัวข้อ</th>
                        <th width="15%">หมวดหมู่</th>
                        <th width="20%">เปิดไฟล์</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentArticles as $article)
                    <tr>
                        <td class="text-muted">{{ $article->created_at->format('d/m/Y') }}</td>
                        <td class="fw-bold">{{ $article->title }}</td>
                        <td><span class="badge rounded-pill bg-light text-primary border">{{ strtoupper($article->category) }}</span></td>
                        <td><a href="{{ asset('storage/' . $article->file_path) }}" target="_blank" class="btn-view">👁️ เปิดดู PDF</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-5">ยังไม่มีข้อมูลในระบบ</td></tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $recentArticles->links() }}
            </div>
        </div>
    </div>

</body>
</html>