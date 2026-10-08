<?php

namespace App\Http\Controllers;

use App\Models\AppInstallation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppInstallationController extends Controller
{
    /**
     * Track client app installation or launch beacon (Public/API endpoint).
     */
    public function track(Request $request)
    {
        $data = $request->all();
        if (empty($data['device_id'])) {
            $raw = json_decode($request->getContent(), true);
            if (is_array($raw)) {
                $data = array_merge($data, $raw);
            }
        }

        $deviceId = trim($data['device_id'] ?? '');
        if (!$deviceId) {
            return response()->json(['error' => 'Device ID is required'], 422);
        }

        // Determine IP address
        $ip = $request->header('X-Forwarded-For');
        if ($ip) {
            $ipList = explode(',', $ip);
            $ip = trim($ipList[0]);
        } else {
            $ip = $request->ip();
        }

        // Attempt reverse DNS lookup for hostname (LAN computer name)
        $hostname = null;
        if ($ip && !in_array($ip, ['127.0.0.1', '::1'])) {
            $resolved = @gethostbyaddr($ip);
            if ($resolved && $resolved !== $ip) {
                $hostname = $resolved;
            }
        }

        $installation = AppInstallation::where('device_id', $deviceId)->first();

        if (!$installation) {
            $installation = new AppInstallation();
            $installation->device_id = $deviceId;
            $installation->first_installed_at = now();
            $installation->launch_count = 1;
            $installation->install_type = $data['install_type'] ?? 'installed';
        } else {
            $type = $data['install_type'] ?? null;
            if ($type === 'installed') {
                $installation->install_type = 'installed';
            }
            $installation->launch_count = ($installation->launch_count ?? 0) + 1;
        }

        $installation->ip_address = $ip;
        if ($hostname) {
            $installation->hostname = $hostname;
        }

        if (!empty($data['device_name']) && !$installation->device_name) {
            $installation->device_name = $data['device_name'];
        }

        if (!empty($data['os'])) {
            $installation->os = $data['os'];
        }

        if (!empty($data['browser'])) {
            $installation->browser = $data['browser'];
        }

        $installation->user_agent = $request->userAgent();
        $installation->last_active_at = now();
        $installation->save();

        return response()->json([
            'status' => 'success',
            'id' => $installation->id,
            'device_id' => $installation->device_id,
            'ip' => $installation->ip_address,
            'hostname' => $installation->hostname,
            'last_active' => $installation->last_active_at->toDateTimeString(),
        ]);
    }

    /**
     * Dashboard view of installations (Protected by Auth).
     */
    public function index(Request $request)
    {
        $query = AppInstallation::query();

        // Search query
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('hostname', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('os', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        // Status filter
        if ($request->input('status') === 'today') {
            $query->whereDate('last_active_at', today());
        } elseif ($request->input('status') === 'week') {
            $query->where('last_active_at', '>=', now()->subDays(7));
        } elseif ($request->input('status') === 'installed_only') {
            $query->where('install_type', 'installed');
        }

        // Summary statistics
        $stats = [
            'total_devices'   => AppInstallation::count(),
            'installed_count' => AppInstallation::where('install_type', 'installed')->count(),
            'active_today'    => AppInstallation::whereDate('last_active_at', today())->count(),
            'active_7days'    => AppInstallation::where('last_active_at', '>=', now()->subDays(7))->count(),
            'unique_ips'      => AppInstallation::distinct('ip_address')->count('ip_address'),
        ];

        // Unique departments for filter dropdown
        $departments = AppInstallation::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        // Paginated results sorted by last_active_at desc
        $installations = $query->orderBy('last_active_at', 'desc')->paginate(25)->withQueryString();

        return view('admin.installations', compact('installations', 'stats', 'departments'));
    }

    /**
     * Update department, device name, or notes for an installation record.
     */
    public function update(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);

        $validated = $request->validate([
            'department'  => 'nullable|string|max:100',
            'device_name' => 'nullable|string|max:100',
            'notes'       => 'nullable|string|max:500',
        ]);

        $installation->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
                'data'    => $installation
            ]);
        }

        return back()->with('success', 'บันทึกข้อมูลเครื่องเรียบร้อยแล้ว');
    }

    /**
     * Reset installation status for a device (allows re-installing).
     */
    public function reset(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);
        $name = $installation->hostname ?: ($installation->device_name ?: $installation->ip_address);

        $installation->install_type = 'browser';
        $installation->first_installed_at = null;
        $installation->save();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "รีเซ็ตสถานะการติดตั้งของ {$name} เรียบร้อยแล้ว (สามารถติดตั้งใหม่ได้ทันที)",
                'data'    => $installation
            ]);
        }

        return back()->with('success', "รีเซ็ตสถานะการติดตั้งของเครื่อง {$name} เรียบร้อยแล้ว (สถานะเปลี่ยนเป็น 'รอติดตั้งใหม่')");
    }

    /**
     * Reset all installed devices back to browser / uninstalled status.
     */
    public function resetAll(Request $request)
    {
        $count = AppInstallation::where('install_type', 'installed')->count();
        AppInstallation::where('install_type', 'installed')->update([
            'install_type' => 'browser',
            'first_installed_at' => null,
        ]);

        return back()->with('success', "รีเซ็ตสถานะการติดตั้งของทั้งหมด {$count} เครื่องในระบบเรียบร้อยแล้ว");
    }

    /**
     * Delete an installation record.
     */
    public function destroy($id)
    {
        $installation = AppInstallation::findOrFail($id);
        $installation->delete();

        return back()->with('success', 'ลบข้อมูลอุปกรณ์เรียบร้อยแล้ว');
    }

    /**
     * Export all records to CSV with UTF-8 BOM for Thai Excel compatibility.
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'cshos_installations_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel Thai support
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'ID',
                'Device UUID',
                'IP Address (LAN)',
                'Hostname (ชื่อคอมพิวเตอร์)',
                'แผนก/ฝ่าย',
                'ชื่อเครื่อง/เจ้าของ',
                'ระบบปฏิบัติการ (OS)',
                'เบราว์เซอร์',
                'ประเภท',
                'จำนวนครั้งที่เปิดใช้งาน',
                'ติดตั้งครั้งแรกเมื่อ',
                'ใช้งานล่าสุดเมื่อ',
                'หมายเหตุ',
            ]);

            // Stream chunked data
            AppInstallation::orderBy('last_active_at', 'desc')->chunk(200, function ($records) use ($handle) {
                foreach ($records as $item) {
                    fputcsv($handle, [
                        $item->id,
                        $item->device_id,
                        $item->ip_address,
                        $item->hostname ?? '-',
                        $item->department ?? '-',
                        $item->device_name ?? '-',
                        $item->os ?? '-',
                        $item->browser ?? '-',
                        $item->install_type === 'installed' ? 'ติดตั้งลงเครื่อง' : 'เปิดผ่านเบราว์เซอร์',
                        $item->launch_count,
                        $item->first_installed_at ? $item->first_installed_at->format('Y-m-d H:i:s') : '-',
                        $item->last_active_at ? $item->last_active_at->format('Y-m-d H:i:s') : '-',
                        $item->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Automatically install desktop shortcut on the host computer.
     */
    public function installDesktopShortcut(Request $request)
    {
        $clientIp = $request->header('X-Forwarded-For') 
            ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) 
            : $request->ip();

        $appUrl = url('/');
        $serverIp = gethostbyname(gethostname());

        // Check if the client is on the local computer or server host
        $isLocal = in_array($clientIp, ['127.0.0.1', '::1', 'localhost']) 
            || $clientIp === $serverIp 
            || $clientIp === '192.168.61.63';

        $output = '';
        $shortcutCreated = false;

        if ($isLocal) {
            $scriptPath = base_path('create_shortcut.ps1');
            if (file_exists($scriptPath)) {
                $cmd = "powershell -ExecutionPolicy Bypass -File \"{$scriptPath}\" -AppUrl \"{$appUrl}\" 2>&1";
                $output = shell_exec($cmd);
                $shortcutCreated = str_contains($output ?: '', 'SUCCESS');
            }
        }

        // Track in database
        $deviceId = $request->input('device_id');
        if ($deviceId) {
            $record = AppInstallation::where('device_id', $deviceId)->first();
            if (!$record) {
                $record = new AppInstallation();
                $record->device_id = $deviceId;
            }
            $record->ip_address = $clientIp;
            $record->install_type = 'installed';
            if (!$record->first_installed_at) {
                $record->first_installed_at = now();
            }
            $record->last_active_at = now();
            $record->launch_count = ($record->launch_count ?? 0) + 1;
            if ($request->filled('os')) $record->os = $request->input('os');
            if ($request->filled('browser')) $record->browser = $request->input('browser');
            $record->save();
        }

        return response()->json([
            'success'          => true,
            'is_local'         => $isLocal,
            'shortcut_created' => $shortcutCreated,
            'message'          => $shortcutCreated 
                ? 'ติดตั้งไอคอน CSHOS DATACENTER บนหน้าจอ Desktop เรียบร้อยแล้ว!' 
                : 'บันทึกสถานะเรียบร้อยแล้ว',
            'output'           => $output
        ]);
    }

    /**
     * Download .bat 1-click shortcut installer for Windows client machines in LAN.
     */
    public function downloadShortcutInstaller(Request $request)
    {
        $appUrl = url('/');
        $appName = "CSHOS DATACENTER";
        $iconUrl = asset('app-icon.ico');
        
        $deviceId = $request->query('device_id');
        if ($deviceId) {
            $clientIp = $request->header('X-Forwarded-For') 
                ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) 
                : $request->ip();
                
            $record = AppInstallation::where('device_id', $deviceId)->first();
            if (!$record) {
                $record = new AppInstallation();
                $record->device_id = $deviceId;
            }
            $record->ip_address = $clientIp;
            $record->install_type = 'installed';
            if (!$record->first_installed_at) {
                $record->first_installed_at = now();
            }
            $record->last_active_at = now();
            $record->launch_count = ($record->launch_count ?? 0) + 1;
            if ($request->filled('os')) $record->os = $request->query('os');
            if ($request->filled('browser')) $record->browser = $request->query('browser');
            $record->save();
        }

        $psScript = <<<POWERSHELL
\$ws = New-Object -ComObject WScript.Shell
\$desktop = [Environment]::GetFolderPath('Desktop')
if (-not (Test-Path \$desktop)) {
    if (Test-Path "\$env:USERPROFILE\OneDrive\Desktop") {
        \$desktop = "\$env:USERPROFILE\OneDrive\Desktop"
    }
}
\$shortcutPath = Join-Path \$desktop "{$appName}.lnk"
\$sc = \$ws.CreateShortcut(\$shortcutPath)
\$appUrl = '{$appUrl}'

\$chrome = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
if (-not (Test-Path \$chrome)) {
    \$chrome = 'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
}
\$edge = 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
if (-not (Test-Path \$edge)) {
    \$edge = 'C:\Program Files\Microsoft\Edge\Application\msedge.exe'
}

if (Test-Path \$chrome) {
    \$sc.TargetPath = \$chrome
    \$sc.Arguments = "--app=\$appUrl"
} elseif (Test-Path \$edge) {
    \$sc.TargetPath = \$edge
    \$sc.Arguments = "--app=\$appUrl"
} else {
    \$sc.TargetPath = \$appUrl
}

\$appDir = "\$env:LOCALAPPDATA\CSHOS"
if (-not (Test-Path \$appDir)) {
    New-Item -ItemType Directory -Path \$appDir -Force | Out-Null
}
\$icoPath = "\$appDir\app-icon.ico"
if (-not (Test-Path \$icoPath)) {
    try {
        Invoke-WebRequest -Uri '{$iconUrl}' -OutFile \$icoPath -UseBasicParsing -TimeoutSec 4 -ErrorAction SilentlyContinue
    } catch {}
}
if (Test-Path \$icoPath) {
    \$sc.IconLocation = "\$icoPath,0"
}

\$sc.Description = "{$appName} - โรงพยาบาลเชียงแสน"
\$sc.Save()

Write-Host ""
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host "   CSHOS DATACENTER - โรงพยาบาลเชียงแสน" -ForegroundColor White
Write-Host "   สร้างไอคอนแอปบนหน้าจอ Desktop สำเร็จ!" -ForegroundColor Green
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host ""

\$ws.Popup("ติดตั้งไอคอน {$appName} บนหน้าจอ Desktop เรียบร้อยแล้ว!", 0, "{$appName} - โรงพยาบาลเชียงแสน", 64)
POWERSHELL;

        $encodedCommand = base64_encode(mb_convert_encoding($psScript, 'UTF-16LE', 'UTF-8'));

        $batContent = "@echo off\r\n"
            . "title {$appName} - Installer\r\n"
            . "echo ====================================================\r\n"
            . "echo    {$appName} - Chiang Saen Hospital\r\n"
            . "echo    Installing Desktop Shortcut...\r\n"
            . "echo ====================================================\r\n"
            . "\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -EncodedCommand {$encodedCommand}\r\n"
            . "\r\n"
            . "timeout /t 2 >nul\r\n";

        return response($batContent, 200, [
            'Content-Type'        => 'application/x-bat',
            'Content-Disposition' => 'attachment; filename="Install-CSHOS-Desktop.bat"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Download .url Internet Shortcut file.
     */
    public function downloadUrlShortcut(Request $request)
    {
        $appUrl = url('/');
        $appName = "CSHOS DATACENTER";
        $faviconUrl = asset('favicon.ico');
        
        $urlContent = "[InternetShortcut]\r\n"
            . "URL={$appUrl}\r\n"
            . "IconIndex=0\r\n"
            . "IconFile={$faviconUrl}\r\n";

        return response($urlContent, 200, [
            'Content-Type'        => 'application/internet-shortcut',
            'Content-Disposition' => 'attachment; filename="' . $appName . '.url"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
