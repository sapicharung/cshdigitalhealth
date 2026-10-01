<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Knowledge; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KnowledgeController extends Controller
{
    // หน้าหลัก: แบ่งหน้าละ 20 รายการ
    public function index()
    {
        $recentArticles = Knowledge::latest()->paginate(20);
        return view('knowledge', [
            'recentArticles' => $recentArticles,
            'title' => 'การจัดการความรู้ โรงพยาบาลเชียงแสน'
        ]);
    }

    /**
     * หน้าคู่มือระบบโรงพยาบาล (HOSxP)
     * แก้ไข Syntax Error และปัญหา Method hospitalSystem หายไป
     */
    public function hospitalSystem()
    {
        $articles = Knowledge::where('category', 'hosxp')->latest()->paginate(20);
        $title = "คู่มือ/เอกสารทั่วไป";
        
        return view('knowledge', [
            'recentArticles' => $articles,
            'title' => $title
        ]);
    }

    // แสดงข้อมูลแยกตามหมวดหมู่ (CPG, Research, BCP)
    public function showCategory($category)
    {
        $articles = Knowledge::where('category', $category)->latest()->paginate(20);
        $categoryNames = [
            'cpg' => 'CPG (แนวปฏิบัติ)',
            'research' => 'งานวิจัย & CQI',
            'bcp' => 'แผนปฏิบัติการฯ'
        ];
        $title = $categoryNames[$category] ?? 'หมวดหมู่ความรู้';

        return view('knowledge', [
            'recentArticles' => $articles,
            'title' => $title
        ]);
    }

    public function create() { return view('upload'); }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required',
            'document' => 'required|file|max:51200', // รองรับ 50MB
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            
            // ดักจับ Error 1 (ขนาดไฟล์เกินที่ PHP ยอมรับ)
            if ($file->getError() === 1) {
                return back()->withInput()->withErrors(['document' => 'ไฟล์ใหญ่เกินกว่าที่ Server กำหนด']);
            }

            $fileName = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('knowledge_files', $fileName, 'public');

            Knowledge::create([
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'file_path' => $path,
            ]);

            return redirect()->route('knowledge')->with('success', 'บันทึกสำเร็จ');
        }
        return back()->with('error', 'อัปโหลดล้มเหลว');
    }
}