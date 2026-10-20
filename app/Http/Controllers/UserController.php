<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('id', 'asc')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'it'    => User::where('role', 'it')->count(),
        ];

        return view('admin.users', compact('users', 'stats'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100', 'unique:users,name'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4'],
            'role'     => ['required', 'string', Rule::in(['admin', 'it'])],
        ], [
            'name.required'     => 'กรุณากรอกชื่อผู้ใช้งาน',
            'name.unique'       => 'ชื่อผู้ใช้งานนี้มีอยู่ในระบบแล้ว',
            'email.required'    => 'กรุณากรอกอีเมล',
            'email.email'       => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique'      => 'อีเมลนี้ถูกใช้งานแล้ว',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min'      => 'รหัสผ่านต้องมีความยาวอย่างน้อย 4 ตัวอักษร',
            'role.required'     => 'กรุณาเลือกระดับสิทธิ์ (Role)',
            'role.in'           => 'ระดับสิทธิ์ต้องเป็น admin หรือ it เท่านั้น',
        ]);

        $user = User::create([
            'name'     => trim($validated['name']),
            'email'    => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "เพิ่มผู้ใช้งาน '{$user->name}' สำเร็จ",
                'user'    => $user,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "เพิ่มผู้ใช้งาน '{$user->name}' เรียบร้อยแล้ว");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100', Rule::unique('users', 'name')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:4'],
            'role'     => ['required', 'string', Rule::in(['admin', 'it'])],
        ], [
            'name.required' => 'กรุณากรอกชื่อผู้ใช้งาน',
            'name.unique'   => 'ชื่อผู้ใช้งานนี้มีอยู่ในระบบแล้ว',
            'email.required'=> 'กรุณากรอกอีเมล',
            'email.email'   => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique'  => 'อีเมลนี้ถูกใช้งานแล้ว',
            'password.min'  => 'รหัสผ่านต้องมีความยาวอย่างน้อย 4 ตัวอักษร',
            'role.required' => 'กรุณาเลือกระดับสิทธิ์ (Role)',
            'role.in'       => 'ระดับสิทธิ์ต้องเป็น admin หรือ it เท่านั้น',
        ]);

        // ป้องกันไม่ให้ปลด role ตัวเอง หากเหลือ admin เพียงคนเดียว
        if ($user->id === Auth::id() && $user->role === 'admin' && $validated['role'] !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ไม่สามารถเปลี่ยนสิทธิ์ของตนเองเป็น IT ได้ เนื่องจากมี Admin เหลือเพียงคนเดียวในระบบ',
                    ], 422);
                }
                return back()->with('error', 'ไม่สามารถเปลี่ยนสิทธิ์ของตนเองเป็น IT ได้ เนื่องจากมี Admin เหลือเพียงคนเดียวในระบบ');
            }
        }

        $data = [
            'name'  => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role'  => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "อัปเดตข้อมูลผู้ใช้งาน '{$user->name}' สำเร็จ",
                'user'    => $user,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "อัปเดตข้อมูลผู้ใช้งาน '{$user->name}' สำเร็จ");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // ป้องกันการลบบัญชีตัวเองที่กำลังล็อกอินอยู่
        if ($user->id === Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบบัญชีที่ท่านกำลังใช้งานเข้าสู่ระบบอยู่ได้',
                ], 422);
            }
            return back()->with('error', 'ไม่สามารถลบบัญชีที่ท่านกำลังใช้งานเข้าสู่ระบบอยู่ได้');
        }

        // ป้องกันการลบ Admin คนสุดท้าย
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ไม่สามารถลบผู้ดูแลระบบคนนี้ได้ เนื่องจากต้องมี Admin อย่างน้อย 1 คนในระบบ',
                    ], 422);
                }
                return back()->with('error', 'ไม่สามารถลบผู้ดูแลระบบคนนี้ได้ เนื่องจากต้องมี Admin อย่างน้อย 1 คนในระบบ');
            }
        }

        $name = $user->name;
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "ลบผู้ใช้งาน '{$name}' สำเร็จ",
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "ลบผู้ใช้งาน '{$name}' สำเร็จ");
    }
}
