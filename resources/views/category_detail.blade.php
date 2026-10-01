<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - CSH Knowledge</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background: #FFFDF5; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        h1 { color: #2C3E50; border-left: 5px solid #64B5F6; padding-left: 15px; font-size: 1.5rem; }
        .file-list { margin-top: 20px; }
        .file-item { display: flex; justify-content: space-between; padding: 15px; border-bottom: 1px solid #eee; text-decoration: none; color: #2C3E50; }
        .file-item:hover { background: #E3F2FD; border-radius: 10px; }
        .btn-open { background: #90CAF9; color: white; padding: 5px 15px; border-radius: 5px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('knowledge') }}" style="text-decoration: none; color: #64B5F6;">← กลับหน้าคลังความรู้</a>
        <h1>{{ $title }}</h1>
        <div class="file-list">
            @forelse($articles as $file)
                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="file-item">
                    <span>{{ $file->title }}</span>
                    <span class="btn-open">เปิดดูไฟล์</span>
                </a>
            @empty
                <p style="text-align: center; color: #999;">ยังไม่มีข้อมูลในหมวดหมู่นี้</p>
            @endforelse
        </div>
    </div>
</body>
</html>