<?php

namespace App\Http\Controllers;

use App\Models\RiskSoftwareRule;
use Illuminate\Http\Request;

class RiskSoftwareRuleController extends Controller
{
    /**
     * Display a listing of risk software rules.
     */
    public function index(Request $request)
    {
        $query = RiskSoftwareRule::query();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('process_names', 'like', "%{$search}%")
                  ->orWhere('service_names', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $rules = $query->orderBy('category')->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'rules' => $rules,
            ]);
        }

        return view('admin.risk_rules', compact('rules'));
    }

    /**
     * Store a newly created risk software rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:risk_software_rules,name',
            'category' => 'required|string|in:remote,emulator,other',
            'process_names' => 'nullable|string',
            'service_names' => 'nullable|string',
            'registry_patterns' => 'nullable|string',
            'file_paths' => 'nullable|string',
            'severity' => 'required|string|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $rule = RiskSoftwareRule::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "เพิ่มโปรแกรมเสี่ยง '{$rule->name}' เรียบร้อยแล้ว",
                'rule' => $rule,
            ]);
        }

        return redirect()->route('admin.risk-rules.index')->with('success', "เพิ่มโปรแกรมเสี่ยง '{$rule->name}' เรียบร้อยแล้ว");
    }

    /**
     * Update the specified risk software rule.
     */
    public function update(Request $request, $id)
    {
        $rule = RiskSoftwareRule::findOrFail($id);

        $validated = $request->validate([
            'name' => "required|string|max:255|unique:risk_software_rules,name,{$id}",
            'category' => 'required|string|in:remote,emulator,other',
            'process_names' => 'nullable|string',
            'service_names' => 'nullable|string',
            'registry_patterns' => 'nullable|string',
            'file_paths' => 'nullable|string',
            'severity' => 'required|string|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $rule->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "แก้ไขข้อมูลโปรแกรมเสี่ยง '{$rule->name}' เรียบร้อยแล้ว",
                'rule' => $rule,
            ]);
        }

        return redirect()->route('admin.risk-rules.index')->with('success', "แก้ไขข้อมูลโปรแกรมเสี่ยง '{$rule->name}' เรียบร้อยแล้ว");
    }

    /**
     * Toggle active status of the specified rule.
     */
    public function toggleActive($id)
    {
        $rule = RiskSoftwareRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return response()->json([
            'success' => true,
            'is_active' => $rule->is_active,
            'message' => "เปลี่ยนสถานะ '{$rule->name}' เป็น " . ($rule->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน') . " แล้ว",
        ]);
    }

    /**
     * Remove the specified risk software rule.
     */
    public function destroy(Request $request, $id)
    {
        $rule = RiskSoftwareRule::findOrFail($id);
        $name = $rule->name;
        $rule->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "ลบโปรแกรมเสี่ยง '{$name}' เรียบร้อยแล้ว",
            ]);
        }

        return redirect()->route('admin.risk-rules.index')->with('success', "ลบโปรแกรมเสี่ยง '{$name}' เรียบร้อยแล้ว");
    }

    /**
     * API for client machines to fetch active detection rules as JSON.
     */
    public function getActiveRulesJson()
    {
        $rules = RiskSoftwareRule::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'count' => $rules->count(),
            'rules' => $rules->map(function ($r) {
                return [
                    'name' => $r->name,
                    'category' => $r->category,
                    'severity' => $r->severity,
                    'processes' => $r->process_list,
                    'services' => $r->service_list,
                    'registry' => $r->registry_list,
                    'paths' => $r->path_list,
                ];
            }),
        ]);
    }
}
