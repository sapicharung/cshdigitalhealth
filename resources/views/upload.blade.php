<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดความรู้ - CSH Digital Health</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --pastel-blue-light: #E3F2FD;
            --pastel-blue-medium: #90CAF9;
            --text-blue-dark: #2C3E50;
            --accent-mint: #B2DFDB;
            --white: #ffffff;
            --soft-gray: #F1F5F9;
        }

        body {
            font-family: 'Prompt', sans-serif;
            margin: 0; padding: 0;
            background-color: #FFFDF5;
            color: var(--text-blue-dark);
        }

        /* --- Navbar --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.8rem 4%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .brand-text strong { font-size: 1.2rem; display: block; }
        .brand-text small { color: var(--pastel-blue-medium); font-size: 0.8rem; }

        .btn-back { 
            padding: 8px 18px; border-radius: 50px; background: var(--soft-gray); 
            text-decoration: none; color: var(--text-blue-dark); font-weight: 600; font-size: 0.8rem;
        }

        /* --- Upload Container --- */
        .upload-container {
            max-width: 700px; margin: 3rem auto; padding: 40px;
            background: var(--white); border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            border: 1px solid rgba(144, 202, 249, 0.2);
        }

        .upload-header { text-align: center; margin-bottom: 2.5rem; }
        .upload-header h2 { margin: 0; font-size: 1.8rem; color: var(--text-blue-dark); font-weight: 600; }
        .upload-header p { color: #64748B; font-size: 0.95rem; margin-top: 8px; }

        /* --- Form Styling --- */
        .form-group { margin-bottom: 1.8rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 10px; font-size: 0.95rem; color: #475569; }
        
        .form-control {
            width: 100%; padding: 14px 18px; border-radius: 15px;
            border: 2px solid #E2E8F0; font-family: 'Prompt'; font-size: 0.95rem;
            box-sizing: border-box; transition: 0.3s;
        }
        .form-control:focus { outline: none; border-color: var(--pastel-blue-medium); background-color: #f8fbff; box-shadow: 0 0 0 4px rgba(144, 202, 249, 0.1); }

        .file-upload-box {
            border: 2px dashed var(--pastel-blue-medium);
            padding: 40px 20px; text-align: center; border-radius: 20px;
            background: #F8FAFC; cursor: pointer; transition: 0.3s;
        }
        .file-upload-box:hover { background: var(--pastel-blue-light); border-color: var(--pastel-blue-dark); }

        .btn-submit {
            width: 100%; padding: 16px; border-radius: 50px;
            background: var(--accent-mint); color: var(--text-blue-dark);
            border: none; font-weight: 600; font-size: 1.1rem; cursor: pointer;
            transition: 0.3s; margin-top: 1rem;
        }
        .btn-submit:hover { background: #99dcd4; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }

        footer { text-align: center; padding: 2rem; color: #94A3B8; font-size: 0.85rem; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand-text">
            <strong>โรงพยาบาลเชียงแสน</strong>
            <small>Knowledge Upload Portal</small>
        </div>
        <a href="{{ route('knowledge') }}" class="btn-back">← กลับคลังความรู้</a>
    </nav>

    <div class="upload-container">
        <div class="upload-header">
            <h2>📤 ส่งข้อมูลเข้าคลังความรู้</h2>
            <p>แบ่งปันข้อมูลเพื่อ Smart Hospital และบริการที่เป็นเลิศ</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 px-4 py-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('knowledge.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label>หัวข้อความรู้ / ชื่อบทความ</label>
                <input type="text" class="form-control" name="title" value="{{ old('title') }}" placeholder="เช่น คู่มือการใช้งาน HOSxP XE งานเวชระเบียน" required>
            </div>

            <div class="form-group">
                <label>หมวดหมู่ความรู้</label>
                <select class="form-control" name="category" required>
                    <option value="" disabled selected>--- เลือกหมวดหมู่ตามยุทธศาสตร์ ---</option>
                    <option value="cpg" {{ old('category') == 'cpg' ? 'selected' : '' }}>CPG (แนวปฏิบัติต่างๆ)</option>
                    <option value="research" {{ old('category') == 'research' ? 'selected' : '' }}>งานวิจัย, R2R, นวัตกรรม</option>
                    <option value="bcp" {{ old('category') == 'bcp' ? 'selected' : '' }}>Business Continuity Plan (BCP)</option>
                    <option value="hosxp" {{ old('category') == 'hosxp' ? 'selected' : '' }}>คู่มือการใช้งานระบบโรงพยาบาล</option>
                </select>
            </div>

            <div class="form-group">
                <label>รายละเอียดโดยย่อ</label>
                <textarea class="form-control" name="description" rows="3" placeholder="ระบุรายละเอียดสั้นๆ เพื่อให้ง่ายต่อการค้นหา...">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label>ไฟล์เอกสาร (PDF, JPG, PNG) - ไม่เกิน 50MB</label>
                <div class="file-upload-box" onclick="document.getElementById('fileInput').click()">
                    <span id="uploadHint">📁 คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่</span>
                    <input type="file" id="fileInput" name="document" style="display: none;" required onchange="updateFileName(this)">
                    <p id="fileNameDisplay" style="margin-top: 15px; color: var(--pastel-blue-dark); font-weight: 600; font-size: 1.1rem;"></p>
                </div>
            </div>

            <button type="submit" class="btn-submit">ยืนยันการอัปโหลดข้อมูล</button>
        </form>
    </div>

    <footer>
        © 2026 กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลเชียงแสน | Smart Hospital & Health Security
    </footer>

    <script>
        function updateFileName(input) {
            if (input.files && input.files.length > 0) {
                const fileName = input.files[0].name;
                document.getElementById('fileNameDisplay').innerHTML = "ไฟล์ที่เลือก: " + fileName;
                document.getElementById('uploadHint').style.display = "none";
            }
        }
    </script>

</body>
</html>