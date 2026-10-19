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
     * Preset profiles for well-known software to guarantee accurate detection.
     */
    public static array $presets = [
        'anyviewer' => [
            'name' => 'AnyViewer',
            'category' => 'remote',
            'severity' => 'high',
            'process_names' => 'AnyViewer, AnyViewerCore',
            'service_names' => 'AnyViewer Service',
            'registry_patterns' => 'AnyViewer',
            'file_paths' => "C:\\Program Files\\AnyViewer*\nC:\\Program Files (x86)\\AnyViewer*",
            'description' => 'โปรแกรมรีโมทควบคุมคอมพิวเตอร์ระยะไกล AnyViewer',
        ],
        'todesk' => [
            'name' => 'ToDesk',
            'category' => 'remote',
            'severity' => 'high',
            'process_names' => 'ToDesk, ToDesk_Service, ToDesk_Lite',
            'service_names' => 'ToDesk_Service',
            'registry_patterns' => 'ToDesk',
            'file_paths' => "C:\\Program Files\\ToDesk*\nC:\\Program Files (x86)\\ToDesk*",
            'description' => 'โปรแกรมรีโมทระยะไกล ToDesk',
        ],
        'sunlogin' => [
            'name' => 'Sunlogin',
            'category' => 'remote',
            'severity' => 'high',
            'process_names' => 'SunloginClient, SunloginService',
            'service_names' => 'SunloginService',
            'registry_patterns' => 'Sunlogin',
            'file_paths' => "C:\\Program Files\\Oray\\Sunlogin*\nC:\\Program Files (x86)\\Oray\\Sunlogin*",
            'description' => 'โปรแกรมรีโมทระยะไกล Sunlogin (Oray)',
        ],
        'parsec' => [
            'name' => 'Parsec',
            'category' => 'remote',
            'severity' => 'high',
            'process_names' => 'parsecd, parsec',
            'service_names' => '',
            'registry_patterns' => 'Parsec',
            'file_paths' => "C:\\Program Files\\Parsec*",
            'description' => 'โปรแกรมสตรีมหน้าจอและรีโมทเกม Parsec',
        ],
        'splashtop' => [
            'name' => 'Splashtop',
            'category' => 'remote',
            'severity' => 'high',
            'process_names' => 'SRFeature, StrStart, SplashtopStreamer',
            'service_names' => 'SplashtopRemoteService',
            'registry_patterns' => 'Splashtop',
            'file_paths' => "C:\\Program Files\\Splashtop*\nC:\\Program Files (x86)\\Splashtop*",
            'description' => 'โปรแกรมรีโมทเดสก์ท็อป Splashtop Streamer',
        ],
        'radmin' => [
            'name' => 'Radmin',
            'category' => 'remote',
            'severity' => 'high',
            'process_names' => 'rserver3, famserver',
            'service_names' => 'Radmin Server',
            'registry_patterns' => 'Radmin',
            'file_paths' => "C:\\Program Files\\Radmin*\nC:\\Program Files (x86)\\Radmin*",
            'description' => 'โปรแกรมรีโมทคอมพิวเตอร์ Famatech Radmin',
        ],
        'vnc' => [
            'name' => 'RealVNC / UltraVNC',
            'category' => 'remote',
            'severity' => 'medium',
            'process_names' => 'winvnc, uvnc_service',
            'service_names' => 'uvnc_service',
            'registry_patterns' => 'RealVNC, UltraVNC',
            'file_paths' => "C:\\Program Files\\RealVNC*\nC:\\Program Files\\UltraVNC*",
            'description' => 'โปรแกรม VNC Server อื่นๆ ภายนอก',
        ],
        'navicat' => [
            'name' => 'Navicat',
            'category' => 'other',
            'severity' => 'high',
            'process_names' => 'navicat, navicat15, navicat16, navicat_premium',
            'service_names' => '',
            'registry_patterns' => 'Navicat, PremiumSoft',
            'file_paths' => "C:\\Program Files\\PremiumSoft\\Navicat*\nC:\\Program Files (x86)\\PremiumSoft\\Navicat*\nC:\\Program Files\\Navicat*\nC:\\Program Files (x86)\\Navicat*",
            'description' => 'โปรแกรมจัดการฐานข้อมูล Navicat (Premium / MySQL / PostgreSQL)',
        ],
        'dbeaver' => [
            'name' => 'DBeaver',
            'category' => 'other',
            'severity' => 'high',
            'process_names' => 'dbeaver, dbeaver-cli',
            'service_names' => '',
            'registry_patterns' => 'DBeaver',
            'file_paths' => "C:\\Program Files\\DBeaver*",
            'description' => 'โปรแกรมจัดการฐานข้อมูล DBeaver Universal Database Tool',
        ],
        'wireshark' => [
            'name' => 'Wireshark',
            'category' => 'other',
            'severity' => 'critical',
            'process_names' => 'Wireshark, tshark, dumpcap',
            'service_names' => 'npcap',
            'registry_patterns' => 'Wireshark, Npcap',
            'file_paths' => "C:\\Program Files\\Wireshark*",
            'description' => 'เครื่องมือดักจับแพ็กเก็ตข้อมูลในเครือข่าย Wireshark Packet Analyzer',
        ],
        'bittorrent' => [
            'name' => 'BitTorrent / uTorrent',
            'category' => 'other',
            'severity' => 'high',
            'process_names' => 'uTorrent, BitTorrent, qbittorrent',
            'service_names' => '',
            'registry_patterns' => 'uTorrent, BitTorrent, qBittorrent',
            'file_paths' => "C:\\Program Files\\qBittorrent*\nC:\\Program Files (x86)\\uTorrent*",
            'description' => 'โปรแกรมดาวน์โหลดไฟล์ Peer-to-Peer BitTorrent',
        ],
        'ccleaner' => [
            'name' => 'CCleaner',
            'category' => 'other',
            'severity' => 'medium',
            'process_names' => 'CCleaner, CCleaner64',
            'service_names' => 'CCleaner Crash Reporting',
            'registry_patterns' => 'CCleaner',
            'file_paths' => "C:\\Program Files\\CCleaner*",
            'description' => 'โปรแกรมล้างไฟล์ขยะและ Registry CCleaner',
        ],
        'cheatengine' => [
            'name' => 'Cheat Engine',
            'category' => 'other',
            'severity' => 'critical',
            'process_names' => 'cheatengine-x86_64, cheatengine-i386, Cheat Engine',
            'service_names' => '',
            'registry_patterns' => 'Cheat Engine',
            'file_paths' => "C:\\Program Files\\Cheat Engine*",
            'description' => 'โปรแกรมแก้ไขหน่วยความจำและดัดแปลงระบบ Cheat Engine',
        ],
        'apowermirror' => [
            'name' => 'ApowerMirror',
            'category' => 'other',
            'severity' => 'medium',
            'process_names' => 'ApowerMirror, Apowersoft',
            'service_names' => '',
            'registry_patterns' => 'ApowerMirror',
            'file_paths' => "C:\\Program Files\\Apowersoft\\ApowerMirror*\nC:\\Program Files (x86)\\Apowersoft\\ApowerMirror*",
            'description' => 'โปรแกรมสะท้อนหน้าจอมือถือ ApowerMirror',
        ],
        'scrcpy' => [
            'name' => 'Scrcpy',
            'category' => 'other',
            'severity' => 'medium',
            'process_names' => 'scrcpy, adb',
            'service_names' => '',
            'registry_patterns' => 'scrcpy',
            'file_paths' => "C:\\scrcpy*\nD:\\scrcpy*",
            'description' => 'โปรแกรมควบคุมหน้าจอมือถือ Android บน PC (Scrcpy)',
        ],
    ];

    /**
     * Auto-fill detection parameters (processes, registry, paths, description) if missing or partial.
     */
    public static function autofillRuleAttributes(array $data): array
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            return $data;
        }

        // Match against predefined presets if applicable
        $lookupKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        foreach (self::$presets as $key => $preset) {
            if ($lookupKey === $key || str_contains($lookupKey, $key) || str_contains($key, $lookupKey)) {
                if (empty($data['category']) || $data['category'] === 'other') {
                    $data['category'] = $preset['category'];
                }
                if (empty($data['severity'])) {
                    $data['severity'] = $preset['severity'];
                }
                if (empty($data['process_names'])) {
                    $data['process_names'] = $preset['process_names'];
                }
                if (empty($data['service_names']) && !empty($preset['service_names'])) {
                    $data['service_names'] = $preset['service_names'];
                }
                if (empty($data['registry_patterns'])) {
                    $data['registry_patterns'] = $preset['registry_patterns'];
                }
                if (empty($data['file_paths'])) {
                    $data['file_paths'] = $preset['file_paths'];
                }
                if (empty($data['description'])) {
                    $data['description'] = $preset['description'];
                }
                return $data;
            }
        }

        // Generic intelligent auto-generation for custom or unlisted tools
        $cleanWord = preg_replace('/[^\w\-]/u', '', $name);
        $cleanWithSpaces = preg_replace('/[^\w\s\-]/u', '', $name);

        if (empty($data['process_names'])) {
            $procs = array_unique(array_filter([
                $name,
                $cleanWord,
                strtolower($cleanWord),
            ]));
            $data['process_names'] = implode(', ', $procs);
        }

        if (empty($data['registry_patterns'])) {
            $data['registry_patterns'] = $name;
        }

        if (empty($data['file_paths'])) {
            $data['file_paths'] = "C:\\Program Files\\{$cleanWithSpaces}*\nC:\\Program Files (x86)\\{$cleanWithSpaces}*\nC:\\ProgramData\\{$cleanWithSpaces}*";
        }

        if (empty($data['description'])) {
            $catLabel = match($data['category'] ?? 'other') {
                'remote' => 'โปรแกรมรีโมทควบคุมระยะไกล',
                'emulator' => 'โปรแกรมจำลองแอนดรอยด์ (Emulator)',
                default => 'โปรแกรมเสี่ยงด้านความปลอดภัย'
            };
            $data['description'] = "ตรวจจับ{$catLabel} {$name}";
        }

        return $data;
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

        // Auto-fill processes, registry patterns, and file paths if omitted
        $validated = self::autofillRuleAttributes($validated);

        $rule = RiskSoftwareRule::create($validated);
        RiskSoftwareRule::resanitizeAllInstallations();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "เพิ่มโปรแกรมเสี่ยง '{$rule->name}' เรียบร้อยแล้ว (ระบบเติมเงื่อนไขให้ครบถ้วนอัตโนมัติ)",
                'rule' => $rule,
            ]);
        }

        return redirect()->route('admin.risk-rules.index')->with('success', "เพิ่มโปรแกรมเสี่ยง '{$rule->name}' เรียบร้อยแล้ว (ระบบเติมเงื่อนไขให้ครบถ้วนอัตโนมัติ)");
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

        // Auto-fill processes, registry patterns, and file paths if omitted
        $validated = self::autofillRuleAttributes($validated);

        $rule->update($validated);
        RiskSoftwareRule::resanitizeAllInstallations();

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
        RiskSoftwareRule::resanitizeAllInstallations();

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
        RiskSoftwareRule::resanitizeAllInstallations();

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

