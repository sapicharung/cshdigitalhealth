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

        // If client reported its own detected LAN IP address, prioritize it when request is local or proxy
        $clientReportedIp = trim($data['ip_address'] ?? '');
        if ($clientReportedIp && filter_var($clientReportedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Check if client reported IP belongs to a virtual host-only adapter (e.g. VMware 192.168.233.x, VirtualBox 192.168.56.x)
            $isVirtualReported = (bool) preg_match('/^(127\.|169\.254\.|192\.168\.(233|56)\.)/', $clientReportedIp);
            if (in_array($ip, ['127.0.0.1', '::1', '172.17.0.1', '172.18.0.1']) || empty($ip)) {
                if (!$isVirtualReported || empty($ip)) {
                    $ip = $clientReportedIp;
                }
            } elseif ($isVirtualReported) {
                // Retain genuine TCP connection IP, reject virtual adapter IP
            }
        }

        // Attempt reverse DNS lookup for hostname (LAN computer name) only if not provided
        $hostname = null;
        if (empty($data['hostname']) && $ip && !in_array($ip, ['127.0.0.1', '::1'])) {
            $resolved = @gethostbyaddr($ip);
            if ($resolved && $resolved !== $ip) {
                $hostname = $resolved;
            }
        }

        $installation = AppInstallation::where('device_id', $deviceId)->first();

        // If not found by device_id, match by hostname to update existing computer record
        if (!$installation && !empty($data['hostname'])) {
            $installation = AppInstallation::where('hostname', $data['hostname'])->first();
            if ($installation) {
                $installation->device_id = $deviceId;
            }
        }

        // If still not found, match by IP address (if not localhost)
        if (!$installation && $ip && !in_array($ip, ['127.0.0.1', '::1'])) {
            $installation = AppInstallation::where('ip_address', $ip)->first();
            if ($installation) {
                $installation->device_id = $deviceId;
            }
        }

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
        if (!empty($data['hostname'])) {
            $installation->hostname = $data['hostname'];
        } elseif ($hostname && !$installation->hostname) {
            $installation->hostname = $hostname;
        }

        if (!empty($data['device_name'])) {
            $installation->device_name = $data['device_name'];
        }

        if (!empty($data['os'])) {
            $installation->os = $data['os'];
        }

        if (!empty($data['os_version'])) {
            $installation->os_version = $data['os_version'];
        }

        if (!empty($data['office_version'])) {
            $installation->office_version = $data['office_version'];
        }

        if (!empty($data['browser'])) {
            $installation->browser = $data['browser'];
        }

        if (!empty($data['cpu'])) {
            $installation->cpu = $data['cpu'];
        }

        if (!empty($data['ram'])) {
            $installation->ram = mb_substr($data['ram'], 0, 255);
        }

        if (!empty($data['disk'])) {
            $installation->disk = mb_substr($data['disk'], 0, 255);
        }

        if (isset($data['remote_ready'])) {
            $installation->remote_ready = (bool) $data['remote_ready'];
        }

        $installation->user_agent = $request->userAgent();
        $installation->last_active_at = now();
        $installation->save();

        return response()->json([
            'status' => 'success',
            'message' => 'อัปเดตข้อมูลเครื่องคอมพิวเตอร์ปลายทางเรียบร้อยแล้ว',
            'id' => $installation->id,
            'device_id' => $installation->device_id,
            'ip' => $installation->ip_address,
            'hostname' => $installation->hostname,
            'device_name' => $installation->device_name,
            'cpu' => $installation->cpu,
            'ram' => $installation->ram,
            'disk' => $installation->disk,
            'os_version' => $installation->os_version,
            'office_version' => $installation->office_version,
            'remote_ready' => $installation->remote_ready,
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
                  ->orWhere('browser', 'like', "%{$search}%")
                  ->orWhere('cpu', 'like', "%{$search}%")
                  ->orWhere('ram', 'like', "%{$search}%")
                  ->orWhere('disk', 'like', "%{$search}%");
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
        $vnc_password = $this->getVncPassword();

        return view('admin.installations', compact('installations', 'stats', 'departments', 'vnc_password'));
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
                'เวอร์ชั่นระบบปฏิบัติการ (OS Version)',
                'เวอร์ชั่น Microsoft Office',
                'เบราว์เซอร์',
                'CPU (หน่วยประมวลผล)',
                'RAM (หน่วยความจำ)',
                'DISK (พื้นที่จัดเก็บข้อมูล)',
                'Remote VNC (Port 5900)',
                'Remote (RDP)',
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
                        $item->os_version ?? ($item->os ?? '-'),
                        $item->office_version ?? 'ไม่ได้ติดตั้ง / ไม่พบข้อมูล',
                        $item->browser ?? '-',
                        $item->cpu ?? '-',
                        $item->ram ?? '-',
                        $item->disk ?? '-',
                        $item->remote_ready ? 'พร้อม VNC (Port 5900)' : 'ไม่ระบุ',
                        $item->remote_ready ? 'พร้อม RDP' : 'ไม่ระบุ',
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
            if ($request->filled('hostname')) $record->hostname = $request->input('hostname');
            if ($request->filled('cpu')) $record->cpu = $request->input('cpu');
            if ($request->filled('ram')) $record->ram = $request->input('ram');
            if ($request->filled('disk')) $record->disk = $request->input('disk');

            if ($isLocal && (!$record->cpu || !$record->hostname)) {
                $record->hostname = gethostname();
                try {
                    $specCmd = 'powershell -NoProfile -Command "$cpuObj=Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1; $clk=if($cpuObj.MaxClockSpeed){[math]::Round(($cpuObj.MaxClockSpeed/1000),2)}else{0}; $cores=if($cpuObj.NumberOfCores){$cpuObj.NumberOfCores}else{0}; $thr=if($cpuObj.NumberOfLogicalProcessors){$cpuObj.NumberOfLogicalProcessors}else{0}; $cpu=$cpuObj.Name.Trim(); if($clk -gt 0 -and $cpu -notmatch \'@\'){$cpu=\"$cpu @ $clk GHz\"}; if($cores -gt 0){$cpu=\"$cpu ($cores Cores, $thr Threads)\"}; $ram=[math]::Round((Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory/1GB,1); $disk=Get-CimInstance Win32_LogicalDisk -Filter \"DeviceID=\'C:\'\" -ErrorAction SilentlyContinue; $free=[math]::Round($disk.FreeSpace/1GB,1); $tot=[math]::Round($disk.Size/1GB,1); Write-Output \"$cpu|||$ram GB|||C: $free/$tot GB\""';
                    $specOut = trim(shell_exec($specCmd) ?: '');
                    if (str_contains($specOut, '|||')) {
                        $parts = explode('|||', $specOut);
                        if (!empty($parts[0])) $record->cpu = trim($parts[0]);
                        if (!empty($parts[1])) $record->ram = trim($parts[1]);
                        if (!empty($parts[2])) $record->disk = trim($parts[2]);
                    }
                } catch (\Throwable $e) {}
            }
            $record->remote_ready = true;
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

        $vncPassword = $this->getVncPassword();
        $vncHash = $this->getVncPasswordHash();

        $psTemplate = <<<'POWERSHELL'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

# Enable TLS 1.2/1.1 for modern web requests on all Windows versions
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
} catch {}

$ws = New-Object -ComObject WScript.Shell
$shortcutName = "{{APP_NAME}}"
$appUrl = '{{APP_URL}}'

Write-Host ""
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host "      CSHOS DIGITAL HEALTH - CLIENT INSTALLER & SETUP" -ForegroundColor White
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host " [Step 1/6] Checking for previous version & cleaning up old components..." -ForegroundColor Yellow

# --- Previous Version Cleanup (Upgrade Mode) ---
$sysDir = "$env:ProgramData\CSHOS"
$userSysDir = "$env:LOCALAPPDATA\CSHOS"
$commonDesktop = [Environment]::GetFolderPath('CommonDesktopDirectory')
if (-not $commonDesktop -or -not (Test-Path $commonDesktop)) { $commonDesktop = "C:\Users\Public\Desktop" }
$userDesktop = [Environment]::GetFolderPath('Desktop')

$previousFound = $false
if ((Test-Path "$sysDir\cshos_sync.ps1") -or (Test-Path "$userSysDir\cshos_sync.ps1") -or 
    (Test-Path "$commonDesktop\$shortcutName.lnk") -or ($userDesktop -and (Test-Path "$userDesktop\$shortcutName.lnk"))) {
    $previousFound = $true
}
if (-not $previousFound) {
    try {
        $existingTask = schtasks /query /tn "CSHOS-AutoSync-Startup" 2>&1
        if ($LASTEXITCODE -eq 0) { $previousFound = $true }
    } catch {}
}

if ($previousFound) {
    Write-Host "       -> พบโปรแกรมหรือระบบ CSHOS เวอร์ชั่นเดิม กำลังลบและทำความสะอาด..." -ForegroundColor Cyan

    # 1. Terminate running background sync processes
    try {
        Get-WmiObject Win32_Process -Filter "CommandLine LIKE '%cshos_sync%'" -ErrorAction SilentlyContinue | ForEach-Object {
            Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
        }
    } catch {}

    # 2. Delete old scheduled tasks
    try {
        schtasks /delete /tn "CSHOS-AutoSync-Startup" /f >$null 2>&1
        schtasks /delete /tn "CSHOS-AutoSync-Logon" /f >$null 2>&1
        schtasks /delete /tn "CSHOS-Startup-Sync" /f >$null 2>&1
        schtasks /delete /tn "CSHOS_AutoSync" /f >$null 2>&1
    } catch {}

    # 3. Delete old registry run keys
    try {
        reg delete "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /f >$null 2>&1
        reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /f >$null 2>&1
    } catch {}

    # 4. Remove all previous desktop shortcuts from all user profiles and desktops
    try {
        $legacyNames = @("$shortcutName.lnk", "CSH DIGITAL HEALTH.lnk", "CSHOS.lnk", "CSHOS DATACENTER.lnk", "CSHOS.url")
        foreach ($name in $legacyNames) {
            Remove-Item -Path "$commonDesktop\$name" -Force -ErrorAction SilentlyContinue
            if ($userDesktop) { Remove-Item -Path "$userDesktop\$name" -Force -ErrorAction SilentlyContinue }
            Remove-Item -Path "$env:USERPROFILE\OneDrive\Desktop\$name" -Force -ErrorAction SilentlyContinue
            Remove-Item -Path "C:\Users\Public\Desktop\$name" -Force -ErrorAction SilentlyContinue
        }
        Get-ChildItem -Path "C:\Users" -Directory -ErrorAction SilentlyContinue | ForEach-Object {
            foreach ($name in $legacyNames) {
                Remove-Item -Path "$($_.FullName)\Desktop\$name" -Force -ErrorAction SilentlyContinue
                Remove-Item -Path "$($_.FullName)\OneDrive\Desktop\$name" -Force -ErrorAction SilentlyContinue
            }
        }
    } catch {}

    # 5. Clean old script files, temporary files, old logs (preserve existing device_id if present)
    try {
        $savedId = ""
        if (Test-Path "$sysDir\device_id.txt") { $savedId = (Get-Content "$sysDir\device_id.txt" -Raw).Trim() }
        elseif (Test-Path "$userSysDir\device_id.txt") { $savedId = (Get-Content "$userSysDir\device_id.txt" -Raw).Trim() }

        Get-ChildItem -Path $sysDir -Exclude "device_id.txt" -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
        Get-ChildItem -Path $userSysDir -Exclude "device_id.txt" -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue

        if ($savedId -and (Test-Path $sysDir)) { Set-Content -Path "$sysDir\device_id.txt" -Value $savedId -Force }
    } catch {}

    Write-Host "       -> ลบเวอร์ชั่นเดิมออกเรียบร้อยแล้ว! กำลังติดตั้งเวอร์ชั่นล่าสุดแทน..." -ForegroundColor Green
} else {
    Write-Host "       -> ตรวจไม่พบเวอร์ชั่นเดิม กำลังติดตั้งโปรแกรมเวอร์ชั่นล่าสุด..." -ForegroundColor Green
}

Write-Host " [Step 2/6] Setting up Desktop shortcuts..." -ForegroundColor Yellow

# Locate Google Chrome or Microsoft Edge
$browserExe = ""
$browserCandidates = @(
    'C:\Program Files\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe",
    'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
    'C:\Program Files\Microsoft\Edge\Application\msedge.exe'
)
foreach ($cand in $browserCandidates) {
    if (Test-Path $cand) {
        $browserExe = $cand
        break
    }
}

# --- Shared System Directory for CSHOS assets (All local users have read access) ---
$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) {
    try { New-Item -ItemType Directory -Path $sysDir -Force | Out-Null } catch {}
}
if (-not (Test-Path $sysDir)) {
    $sysDir = "$env:LOCALAPPDATA\CSHOS"
    if (-not (Test-Path $sysDir)) { New-Item -ItemType Directory -Path $sysDir -Force | Out-Null }
}

# --- Download App Icon (Fast timeout max 5s) ---
$icoPath = Join-Path $sysDir "app-icon.ico"
$iconDownloaded = $false
try {
    Invoke-WebRequest -Uri '{{ICON_URL}}' -OutFile $icoPath -UseBasicParsing -TimeoutSec 5 -ErrorAction SilentlyContinue
    if ((Test-Path $icoPath) -and ((Get-Item $icoPath).Length -gt 100)) {
        $iconDownloaded = $true
    }
} catch {}

# --- Setup Desktop Shortcut (Install exactly 1 icon on Public Desktop for all users) ---
# In Windows, Desktop aggregates Public Desktop and Current User Desktop.
# Installing to Public Desktop ensures all users see 1 icon, while removing duplicates.
$commonDesktop = [Environment]::GetFolderPath('CommonDesktopDirectory')
if (-not $commonDesktop -or -not (Test-Path $commonDesktop)) {
    $commonDesktop = "C:\Users\Public\Desktop"
}
$userDesktop = [Environment]::GetFolderPath('Desktop')

$targetDesktop = ""
if (Test-Path $commonDesktop) {
    $targetDesktop = $commonDesktop
    # Clean up duplicate shortcuts from personal user folders so Windows shows only 1 icon
    try {
        if ($userDesktop -and (Test-Path (Join-Path $userDesktop "$shortcutName.lnk"))) {
            Remove-Item -Path (Join-Path $userDesktop "$shortcutName.lnk") -Force -ErrorAction SilentlyContinue
        }
        if (Test-Path "$env:USERPROFILE\OneDrive\Desktop\$shortcutName.lnk") {
            Remove-Item -Path "$env:USERPROFILE\OneDrive\Desktop\$shortcutName.lnk" -Force -ErrorAction SilentlyContinue
        }
        Get-ChildItem -Path "C:\Users" -Directory -ErrorAction SilentlyContinue | Where-Object { 
            $_.Name -notmatch '^(Public|Default|Default User|All Users)$' 
        } | ForEach-Object {
            $uDesk = Join-Path $_.FullName "Desktop\$shortcutName.lnk"
            $uOneDesk = Join-Path $_.FullName "OneDrive\Desktop\$shortcutName.lnk"
            if (Test-Path $uDesk) { Remove-Item -Path $uDesk -Force -ErrorAction SilentlyContinue }
            if (Test-Path $uOneDesk) { Remove-Item -Path $uOneDesk -Force -ErrorAction SilentlyContinue }
        }
    } catch {}
} elseif ($userDesktop -and (Test-Path $userDesktop)) {
    $targetDesktop = $userDesktop
}

$installedCount = 0
if ($targetDesktop) {
    try {
        $shortcutPath = Join-Path $targetDesktop "$shortcutName.lnk"
        $sc = $ws.CreateShortcut($shortcutPath)
        if ($browserExe) {
            $sc.TargetPath = $browserExe
            $sc.Arguments = "--app=`"$appUrl`""
        } else {
            $sc.TargetPath = $appUrl
        }
        $sc.WorkingDirectory = $sysDir
        $sc.Description = "$shortcutName - โรงพยาบาลเชียงแสน"
        if ($iconDownloaded -or ((Test-Path $icoPath) -and ((Get-Item $icoPath).Length -gt 100))) {
            $sc.IconLocation = "$icoPath,0"
        }
        $sc.Save()
        $installedCount = 1
    } catch {}
}

# Refresh Windows Explorer desktop icon cache
try { & ie4uinit.exe -show | Out-Null } catch {}
Write-Host "       -> Desktop shortcut installed successfully (1 icon on Desktop)." -ForegroundColor Green

# --- Collect Computer Specs ---
Write-Host " [Step 3/6] Detecting machine specifications (CPU Speed, Cores, RAM, Disk, OS, Office)..." -ForegroundColor Yellow
$compName = $env:COMPUTERNAME
$userName = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
if (-not $userName) { $userName = $env:USERNAME }

$osInfo = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption
if (-not $osInfo) { $osInfo = [System.Environment]::OSVersion.VersionString }

$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $cpuObj) {
    $cpuObj = Get-WmiObject Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
}
if ($cpuObj -and $cpuObj.Name) {
    $rawName = ($cpuObj.Name -replace '\s+', ' ').Trim()
    $clock = 0
    if ($cpuObj.MaxClockSpeed) { $clock = [int]$cpuObj.MaxClockSpeed }
    elseif ($cpuObj.CurrentClockSpeed) { $clock = [int]$cpuObj.CurrentClockSpeed }
    
    $cores = if ($cpuObj.NumberOfCores) { [int]$cpuObj.NumberOfCores } else { 0 }
    $threads = if ($cpuObj.NumberOfLogicalProcessors) { [int]$cpuObj.NumberOfLogicalProcessors } else { 0 }

    $speedStr = ""
    if ($clock -gt 0 -and $rawName -notmatch '@\s*[0-9\.]+\s*GHz') {
        $ghz = [math]::Round(($clock / 1000), 2)
        $speedStr = "@ $ghz GHz"
    }

    $coreStr = ""
    if ($cores -gt 0 -and $threads -gt 0) {
        $coreStr = "($cores Cores, $threads Threads)"
    } elseif ($cores -gt 0) {
        $coreStr = "($cores Cores)"
    }

    $cpuParts = @($rawName)
    if ($speedStr) { $cpuParts += $speedStr }
    if ($coreStr) { $cpuParts += $coreStr }
    $cpuInfo = $cpuParts -join " "
} else {
    $cpuInfo = "Unknown CPU"
}

# RAM Specs & Bus Speed
try {
    $totalRamBytes = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory
    if (-not $totalRamBytes) {
        $totalRamBytes = ((Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).TotalVisibleMemorySize) * 1KB
    }
    $ramGB = if ($totalRamBytes) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "Unknown RAM" }

    try {
        $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        if (-not $memSticks -or $memSticks.Count -eq 0) {
            $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        }

        if ($memSticks -and $memSticks.Count -gt 0) {
            $first = $memSticks[0]
            $clock = 0
            if ($first.ConfiguredClockSpeed) { $clock = [int]$first.ConfiguredClockSpeed }
            elseif ($first.Speed) { $clock = [int]$first.Speed }

            $smb = if ($first.SMBIOSMemoryType) { [int]$first.SMBIOSMemoryType } else { 0 }
            $memType = switch ($smb) {
                20 { "DDR" }
                21 { "DDR2" }
                24 { "DDR3" }
                26 { "DDR4" }
                34 { "DDR5" }
                default {
                    if ($clock -ge 4800) { "DDR5" }
                    elseif ($clock -ge 2133) { "DDR4" }
                    elseif ($clock -ge 800) { "DDR3" }
                    else { "RAM" }
                }
            }
            $busStr = if ($clock -gt 0) { "Bus $clock MHz" } else { "" }
            $part = if ($first.PartNumber) { ($first.PartNumber -as [string]).Trim() } else { "" }
            $brand = if ($first.Manufacturer) { ($first.Manufacturer -as [string]).Trim() } else { "" }
            if ($brand -match '^(Undefined|None|0x[0-9A-Fa-f]+)$') { $brand = "" }
            
            $modelInfo = if ($brand -and $part) { "$brand $part" } elseif ($brand) { "$brand" } elseif ($part) { "$part" } else { "" }
            $stickCount = $memSticks.Count
            $descList = New-Object System.Collections.Generic.List[string]
            if ($memType) { $descList.Add($memType) }
            if ($busStr) { $descList.Add($busStr) }
            if ($stickCount -gt 1) {
                $detailStr = if ($modelInfo) { " - $modelInfo" } else { "" }
                $descList.Add("($stickCount ช่อง$detailStr)")
            } elseif ($modelInfo) {
                $descList.Add("($modelInfo)")
            }
            $details = $descList -join " "
            if ($ramGB -gt 0) {
                $ramInfo = "$ramGB GB $details".Trim()
            } elseif ($details) {
                $ramInfo = $details
            }
        }
    } catch {}
} catch {
    $ramInfo = "Unknown RAM"
}

# Harddisk Type (SSD, NVMe M.2, SATA, HDD) & Capacity
try {
    $physDict = @{}
    try {
        Get-PhysicalDisk -ErrorAction SilentlyContinue | ForEach-Object {
            $key = $_.FriendlyName
            $mt = if ($_.MediaType) { $_.MediaType } else { "" }
            $bt = if ($_.BusType) { $_.BusType } else { "" }
            $physDict[$key] = @{ Media = $mt; Bus = $bt }
        }
    } catch {}

    $diskMap = @{}
    try {
        Get-Disk -ErrorAction SilentlyContinue | ForEach-Object {
            $d = $_
            $bt = if ($d.BusType) { $d.BusType } else { "" }
            $mt = if ($physDict.ContainsKey($d.FriendlyName)) { $physDict[$d.FriendlyName].Media } else { "" }
            if (-not $bt -and $physDict.ContainsKey($d.FriendlyName)) { $bt = $physDict[$d.FriendlyName].Bus }
            
            $typeLabel = if ($bt -match 'NVMe') { "SSD NVMe M.2" }
                         elseif ($mt -eq "SSD" -and $bt) { "SSD $bt" }
                         elseif ($mt -eq "SSD") { "SSD" }
                         elseif ($mt -eq "HDD" -and $bt) { "HDD $bt" }
                         elseif ($mt -eq "HDD") { "HDD" }
                         elseif ($bt) { "$bt" }
                         else { "" }
                         
            Get-Partition -DiskNumber $d.Number -ErrorAction SilentlyContinue | Where-Object { $_.DriveLetter } | ForEach-Object {
                $ltr = "$($_.DriveLetter):"
                $diskMap[$ltr] = $typeLabel
            }
        }
    } catch {}

    $diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue | ForEach-Object {
        $dId = $_.DeviceID
        $free = [math]::Round($_.FreeSpace / 1GB, 1)
        $total = [math]::Round($_.Size / 1GB, 1)
        $type = if ($diskMap.ContainsKey($dId) -and $diskMap[$dId]) { " (" + $diskMap[$dId] + ")" } else { "" }
        "$dId$type ว่าง $free/$total GB"
    }
    $diskInfo = if ($diskItems) { $diskItems -join ", " } else { "N/A" }
} catch {
    $diskInfo = "N/A"
}

# --- Detect Target Machine IP Address (IPv4) ---
$detectedIps = @()

# 1. Prefer active physical interface with Default Gateway route (0.0.0.0/0)
try {
    $gwRoutes = @(Get-NetRoute -DestinationPrefix '0.0.0.0/0' -AddressFamily IPv4 -ErrorAction SilentlyContinue | Sort-Object RouteMetric)
    foreach ($route in $gwRoutes) {
        $adapter = Get-NetAdapter -InterfaceIndex $route.InterfaceIndex -ErrorAction SilentlyContinue
        if ($adapter) {
            $desc = "$($adapter.InterfaceDescription) $($adapter.Name)"
            if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier') {
                continue
            }
            $ipObj = Get-NetIPAddress -InterfaceIndex $route.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                     Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -First 1
            if ($ipObj -and $ipObj.IPAddress) {
                $detectedIps += $ipObj.IPAddress
            }
        }
    }
} catch {}

# 2. Physical adapters that are 'Up' without gateway filter if method 1 found nothing
if (-not $detectedIps -or $detectedIps.Count -eq 0) {
    try {
        $upAdapters = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object { 
            $_.Status -eq 'Up' -and 
            $_.InterfaceDescription -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier' -and
            $_.Name -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth'
        }
        foreach ($ad in $upAdapters) {
            $ips = @(Get-NetIPAddress -InterfaceIndex $ad.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                     Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -ExpandProperty IPAddress)
            $detectedIps += $ips
        }
    } catch {}
}

# 3. Fallback via Win32_NetworkAdapterConfiguration (excluding virtual)
if (-not $detectedIps -or $detectedIps.Count -eq 0) {
    try {
        $netConfigs = Get-CimInstance Win32_NetworkAdapterConfiguration -Filter "IPEnabled=TRUE" -ErrorAction SilentlyContinue
        foreach ($nc in $netConfigs) {
            $desc = "$($nc.Description)"
            if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN') {
                continue
            }
            foreach ($ip in $nc.IPAddress) {
                if ($ip -match '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$' -and $ip -ne '127.0.0.1' -and $ip -notmatch '^169\.254\.') {
                    $detectedIps += $ip
                }
            }
        }
    } catch {}
}

# 4. Ultimate fallback (any IPv4 not loopback)
if (-not $detectedIps -or $detectedIps.Count -eq 0) {
    try {
        $detectedIps = @(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { 
            $_.InterfaceAlias -notmatch 'Loopback' -and 
            $_.IPAddress -notmatch '^(169\.254\.|127\.)' 
        } | Select-Object -ExpandProperty IPAddress)
    } catch {}
}

$clientIp = if ($detectedIps -and $detectedIps.Count -gt 0) { $detectedIps[0] } else { "" }
$allIpsStr = if ($detectedIps -and $detectedIps.Count -gt 0) { ($detectedIps | Select-Object -Unique) -join ", " } else { "No IP (DHCP)" }

# Detect OS Version
$osCaption = ($osInfo -replace 'Microsoft\s*', '').Trim()
$osVerStr = $osCaption
try {
    $regNT = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion" -ErrorAction SilentlyContinue
    $dispVer = if ($regNT.DisplayVersion) { $regNT.DisplayVersion } elseif ($regNT.ReleaseId) { $regNT.ReleaseId } else { "" }
    $build = $regNT.CurrentBuild
    $arch = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).OSArchitecture
    if ($dispVer) { $osVerStr += " $dispVer" }
    if ($arch -or $build) {
        $p = @()
        if ($arch) { $p += $arch }
        if ($build) { $p += "Build $build" }
        $osVerStr += " (" + ($p -join ", ") + ")"
    }
} catch {}

# Detect Microsoft Office Version
$officeFinal = "Not Installed"
try {
    $c2r = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Office\ClickToRun\Configuration" -ErrorAction SilentlyContinue
    if ($c2r -and $c2r.ProductReleaseIds) {
        $idStr = "$($c2r.ProductReleaseIds)"
        $verReport = $c2r.VersionToReport
        $platform = $c2r.Platform
        $friendly = if ($idStr -match '2024') { "Microsoft Office 2024" }
                    elseif ($idStr -match '2021') { "Microsoft Office 2021" }
                    elseif ($idStr -match '2019') { "Microsoft Office 2019" }
                    elseif ($idStr -match 'O365|M365|365') { "Microsoft 365 Apps" }
                    elseif ($idStr -match '2016') { "Microsoft Office 2016" }
                    elseif ($idStr -match '2013') { "Microsoft Office 2013" }
                    elseif ($idStr -match '2010') { "Microsoft Office 2010" }
                    else { "Microsoft Office" }
        $edition = if ($idStr -match 'ProPlus') { " Professional Plus" }
                   elseif ($idStr -match 'Standard') { " Standard" }
                   elseif ($idStr -match 'HomeBusiness') { " Home & Business" }
                   elseif ($idStr -match 'HomeStudent') { " Home & Student" }
                   else { "" }
        $officeFinal = "$friendly$edition"
        if ($verReport) { $officeFinal += " ($verReport" + (if ($platform) { " $platform" } else { "" }) + ")" }
    }
    if ($officeFinal -eq "Not Installed") {
        $keys = @("HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*", "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*")
        $installed = Get-ItemProperty $keys -ErrorAction SilentlyContinue | Where-Object { 
            $_.DisplayName -match '^Microsoft Office (Professional|Standard|Home|Personal|365|Enterprise)' -or
            $_.DisplayName -match '^Microsoft 365'
        } | Select-Object -First 1
        if ($installed) {
            $officeFinal = $installed.DisplayName + (if ($installed.DisplayVersion) { " ($($installed.DisplayVersion))" } else { "" })
        }
    }
    if ($officeFinal -eq "Not Installed") {
        $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\excel.exe" -ErrorAction SilentlyContinue).'(Default)'
        if (-not $appPath) { $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\winword.exe" -ErrorAction SilentlyContinue).'(Default)' }
        if ($appPath -and (Test-Path $appPath)) {
            $vi = (Get-Item $appPath -ErrorAction SilentlyContinue).VersionInfo
            $m = $vi.ProductMajorPart
            $known = switch ($m) { 16 { "Microsoft Office 2016/2019/365" } 15 { "Microsoft Office 2013" } 14 { "Microsoft Office 2010" } 12 { "Microsoft Office 2007" } default { "Microsoft Office" } }
            $officeFinal = "$known" + (if ($vi.ProductVersion) { " ($($vi.ProductVersion))" } else { "" })
        }
    }
} catch {}

Write-Host "       -> Host: $compName ($clientIp) | $ramInfo RAM | $officeFinal" -ForegroundColor Green

# --- Install & Configure VNC Server (TightVNC - Screen Share, No Logout) ---
Write-Host " [Step 4/6] Configuring Remote Desktop & TightVNC service..." -ForegroundColor Yellow
$vncInstalled = $false
try {
    $tvnSvc = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if ($tvnSvc) {
        $vncInstalled = $true
    }
} catch {}

if (-not $vncInstalled) {
    try {
        $is64 = [Environment]::Is64BitOperatingSystem
        $msiFile = if ($is64) { "tightvnc-64bit.msi" } else { "tightvnc-32bit.msi" }
        $msiUrl = "{{APP_URL}}/downloads/" + $msiFile
        $msiLocal = Join-Path $sysDir $msiFile

        if (-not (Test-Path $msiLocal)) {
            Write-Host "       -> Downloading TightVNC Server package ($msiFile)..." -ForegroundColor Cyan
            try {
                Invoke-WebRequest -Uri $msiUrl -OutFile $msiLocal -UseBasicParsing -TimeoutSec 15 -ErrorAction Stop
            } catch {
                Write-Host "       -> Notice: TightVNC download skipped (Network timeout or offline)." -ForegroundColor Gray
            }
        }

        if (Test-Path $msiLocal) {
            Write-Host "       -> Installing TightVNC Server service..." -ForegroundColor Cyan
            $vncPass = "{{VNC_PASSWORD}}"
            $msiArgs = "/i `"$msiLocal`" /quiet /norestart ADDLOCAL=Server SERVER_REGISTER_AS_SERVICE=1 SERVER_ADD_FIREWALL_EXCEPTION=1 SET_USEVNCAUTHENTICATION=1 VALUE_OF_USEVNCAUTHENTICATION=1 SET_PASSWORD=1 VALUE_OF_PASSWORD=`"$vncPass`""
            $p = Start-Process msiexec.exe -ArgumentList $msiArgs -NoNewWindow -PassThru -ErrorAction SilentlyContinue
            if ($p) {
                $p.WaitForExit(20000)
            }
        }
    } catch {}
}

# Set TightVNC Password directly into Registry
try {
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v Password /t REG_BINARY /d {{VNC_HASH}} /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v UseVncAuthentication /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v AcceptRfbConnections /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v AllowLoopback /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v RfbPort /t REG_DWORD /d 5900 /f | Out-Null
} catch {}

try {
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v Password /t REG_BINARY /d {{VNC_HASH}} /f | Out-Null
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v UseVncAuthentication /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v AcceptRfbConnections /t REG_DWORD /d 1 /f | Out-Null
} catch {}

# Ensure tvnserver service is running and restarted to load new password
try {
    Set-Service -Name "tvnserver" -StartupType Automatic -ErrorAction SilentlyContinue
    Stop-Service -Name "tvnserver" -Force -ErrorAction SilentlyContinue
    Start-Sleep -Milliseconds 500
    Start-Service -Name "tvnserver" -ErrorAction SilentlyContinue
} catch {}

# Ensure Windows Firewall allows Port 5900
try {
    netsh advfirewall firewall add rule name="TightVNC Server Port 5900" dir=in action=allow protocol=TCP localport=5900 | Out-Null
} catch {}

# --- Also Enable Internal Windows Remote Desktop & Remote Assistance as backup ---
try {
    Set-ItemProperty -Path 'HKLM:\System\CurrentControlSet\Control\Terminal Server' -Name "fDenyTSConnections" -Value 0 -ErrorAction SilentlyContinue
    Enable-NetFirewallRule -DisplayGroup "Remote Desktop" -ErrorAction SilentlyContinue
    Set-Service -Name TermService -StartupType Automatic -ErrorAction SilentlyContinue
    Start-Service -Name TermService -ErrorAction SilentlyContinue
    Set-ItemProperty -Path 'HKLM:\System\CurrentControlSet\Control\Remote Assistance' -Name "fAllowToGetHelp" -Value 1 -ErrorAction SilentlyContinue
    Set-ItemProperty -Path 'HKLM:\System\CurrentControlSet\Control\Remote Assistance' -Name "fAllowFullControl" -Value 1 -ErrorAction SilentlyContinue
} catch {}
Write-Host "       -> Remote VNC service configured and ready." -ForegroundColor Green

# --- Persistent Device UUID ---
$devIdFile = Join-Path $sysDir "device_id.txt"
$devId = ""
if (Test-Path $devIdFile) {
    try { $devId = (Get-Content $devIdFile -Raw).Trim() } catch {}
}
if (-not $devId) {
    $devId = [guid]::NewGuid().ToString()
}
try { Set-Content -Path $devIdFile -Value $devId -Force } catch {}

# --- Setup Background Auto-Sync Script for System Restart / Boot / Logon ---
Write-Host " [Step 5/6] Setting up persistent background auto-sync system..." -ForegroundColor Yellow
$syncScriptPath = Join-Path $sysDir "cshos_sync.ps1"
$syncCode = @'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

$maxWait = 30
$waited = 0
$detectedIps = @()

while ($waited -lt $maxWait) {
    # 1. Prefer active physical interface with Default Gateway route (0.0.0.0/0)
    try {
        $gwRoutes = @(Get-NetRoute -DestinationPrefix '0.0.0.0/0' -AddressFamily IPv4 -ErrorAction SilentlyContinue | Sort-Object RouteMetric)
        foreach ($route in $gwRoutes) {
            $adapter = Get-NetAdapter -InterfaceIndex $route.InterfaceIndex -ErrorAction SilentlyContinue
            if ($adapter) {
                $desc = "$($adapter.InterfaceDescription) $($adapter.Name)"
                if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier') {
                    continue
                }
                $ipObj = Get-NetIPAddress -InterfaceIndex $route.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                         Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -First 1
                if ($ipObj -and $ipObj.IPAddress) {
                    $detectedIps += $ipObj.IPAddress
                }
            }
        }
    } catch {}

    # 2. Physical adapters that are 'Up' without gateway filter
    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $upAdapters = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object { 
                $_.Status -eq 'Up' -and 
                $_.InterfaceDescription -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier' -and
                $_.Name -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth'
            }
            foreach ($ad in $upAdapters) {
                $ips = @(Get-NetIPAddress -InterfaceIndex $ad.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                         Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -ExpandProperty IPAddress)
                $detectedIps += $ips
            }
        } catch {}
    }

    # 3. Fallback via Win32_NetworkAdapterConfiguration (excluding virtual)
    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $netConfigs = Get-CimInstance Win32_NetworkAdapterConfiguration -Filter "IPEnabled=TRUE" -ErrorAction SilentlyContinue
            foreach ($nc in $netConfigs) {
                $desc = "$($nc.Description)"
                if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN') {
                    continue
                }
                foreach ($ip in $nc.IPAddress) {
                    if ($ip -match '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$' -and $ip -ne '127.0.0.1' -and $ip -notmatch '^169\.254\.') {
                        $detectedIps += $ip
                    }
                }
            }
        } catch {}
    }

    if ($detectedIps -and $detectedIps.Count -gt 0) {
        break
    }

    Start-Sleep -Seconds 3
    $waited += 3
}

$clientIp = if ($detectedIps -and $detectedIps.Count -gt 0) { $detectedIps[0] } else { "" }

$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) { $sysDir = "$env:LOCALAPPDATA\CSHOS" }

$devIdFile = Join-Path $sysDir "device_id.txt"
$devId = ""
if (Test-Path $devIdFile) {
    try { $devId = (Get-Content $devIdFile -Raw).Trim() } catch {}
}
if (-not $devId) {
    $devId = [guid]::NewGuid().ToString()
    try { Set-Content -Path $devIdFile -Value $devId -Force } catch {}
}

$compName = $env:COMPUTERNAME
$userName = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
if (-not $userName) { $userName = $env:USERNAME }
if (-not $userName) { $userName = "System" }

$osInfo = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption
if (-not $osInfo) { $osInfo = [System.Environment]::OSVersion.VersionString }

$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $cpuObj) {
    $cpuObj = Get-WmiObject Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
}
if ($cpuObj -and $cpuObj.Name) {
    $rawName = ($cpuObj.Name -replace '\s+', ' ').Trim()
    $clock = 0
    if ($cpuObj.MaxClockSpeed) { $clock = [int]$cpuObj.MaxClockSpeed }
    elseif ($cpuObj.CurrentClockSpeed) { $clock = [int]$cpuObj.CurrentClockSpeed }
    
    $cores = if ($cpuObj.NumberOfCores) { [int]$cpuObj.NumberOfCores } else { 0 }
    $threads = if ($cpuObj.NumberOfLogicalProcessors) { [int]$cpuObj.NumberOfLogicalProcessors } else { 0 }

    $speedStr = ""
    if ($clock -gt 0 -and $rawName -notmatch '@\s*[0-9\.]+\s*GHz') {
        $ghz = [math]::Round(($clock / 1000), 2)
        $speedStr = "@ $ghz GHz"
    }

    $coreStr = ""
    if ($cores -gt 0 -and $threads -gt 0) {
        $coreStr = "($cores Cores, $threads Threads)"
    } elseif ($cores -gt 0) {
        $coreStr = "($cores Cores)"
    }

    $cpuParts = @($rawName)
    if ($speedStr) { $cpuParts += $speedStr }
    if ($coreStr) { $cpuParts += $coreStr }
    $cpuInfo = $cpuParts -join " "
} else {
    $cpuInfo = "ไม่สามารถระบุ CPU"
}

# RAM Specs & Bus Speed
try {
    $totalRamBytes = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory
    if (-not $totalRamBytes) {
        $totalRamBytes = ((Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).TotalVisibleMemorySize) * 1KB
    }
    $ramGB = if ($totalRamBytes) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "ไม่สามารถระบุ RAM" }

    try {
        $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        if (-not $memSticks -or $memSticks.Count -eq 0) {
            $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        }

        if ($memSticks -and $memSticks.Count -gt 0) {
            $first = $memSticks[0]
            $clock = 0
            if ($first.ConfiguredClockSpeed) { $clock = [int]$first.ConfiguredClockSpeed }
            elseif ($first.Speed) { $clock = [int]$first.Speed }

            $smb = if ($first.SMBIOSMemoryType) { [int]$first.SMBIOSMemoryType } else { 0 }
            $memType = switch ($smb) {
                20 { "DDR" }
                21 { "DDR2" }
                24 { "DDR3" }
                26 { "DDR4" }
                34 { "DDR5" }
                default {
                    if ($clock -ge 4800) { "DDR5" }
                    elseif ($clock -ge 2133) { "DDR4" }
                    elseif ($clock -ge 800) { "DDR3" }
                    else { "RAM" }
                }
            }
            $busStr = if ($clock -gt 0) { "Bus $clock MHz" } else { "" }
            $part = if ($first.PartNumber) { ($first.PartNumber -as [string]).Trim() } else { "" }
            $brand = if ($first.Manufacturer) { ($first.Manufacturer -as [string]).Trim() } else { "" }
            if ($brand -match '^(Undefined|None|0x[0-9A-Fa-f]+)$') { $brand = "" }
            
            $modelInfo = if ($brand -and $part) { "$brand $part" } elseif ($brand) { "$brand" } elseif ($part) { "$part" } else { "" }
            $stickCount = $memSticks.Count
            $descList = New-Object System.Collections.Generic.List[string]
            if ($memType) { $descList.Add($memType) }
            if ($busStr) { $descList.Add($busStr) }
            if ($stickCount -gt 1) {
                $detailStr = if ($modelInfo) { " - $modelInfo" } else { "" }
                $descList.Add("($stickCount ช่อง$detailStr)")
            } elseif ($modelInfo) {
                $descList.Add("($modelInfo)")
            }
            $details = $descList -join " "
            if ($ramGB -gt 0) {
                $ramInfo = "$ramGB GB $details".Trim()
            } elseif ($details) {
                $ramInfo = $details
            }
        }
    } catch {}
} catch {
    $ramInfo = "ไม่สามารถระบุ RAM"
}

# Harddisk Type (SSD, NVMe M.2, SATA, HDD) & Capacity
try {
    $physDict = @{}
    try {
        Get-PhysicalDisk -ErrorAction SilentlyContinue | ForEach-Object {
            $key = $_.FriendlyName
            $mt = if ($_.MediaType) { $_.MediaType } else { "" }
            $bt = if ($_.BusType) { $_.BusType } else { "" }
            $physDict[$key] = @{ Media = $mt; Bus = $bt }
        }
    } catch {}

    $diskMap = @{}
    try {
        Get-Disk -ErrorAction SilentlyContinue | ForEach-Object {
            $d = $_
            $bt = if ($d.BusType) { $d.BusType } else { "" }
            $mt = if ($physDict.ContainsKey($d.FriendlyName)) { $physDict[$d.FriendlyName].Media } else { "" }
            if (-not $bt -and $physDict.ContainsKey($d.FriendlyName)) { $bt = $physDict[$d.FriendlyName].Bus }
            
            $typeLabel = if ($bt -match 'NVMe') { "SSD NVMe M.2" }
                         elseif ($mt -eq "SSD" -and $bt) { "SSD $bt" }
                         elseif ($mt -eq "SSD") { "SSD" }
                         elseif ($mt -eq "HDD" -and $bt) { "HDD $bt" }
                         elseif ($mt -eq "HDD") { "HDD" }
                         elseif ($bt) { "$bt" }
                         else { "" }
                         
            Get-Partition -DiskNumber $d.Number -ErrorAction SilentlyContinue | Where-Object { $_.DriveLetter } | ForEach-Object {
                $ltr = "$($_.DriveLetter):"
                $diskMap[$ltr] = $typeLabel
            }
        }
    } catch {}

    $diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue | ForEach-Object {
        $dId = $_.DeviceID
        $free = [math]::Round($_.FreeSpace / 1GB, 1)
        $total = [math]::Round($_.Size / 1GB, 1)
        $type = if ($diskMap.ContainsKey($dId) -and $diskMap[$dId]) { " (" + $diskMap[$dId] + ")" } else { "" }
        "$dId$type ว่าง $free/$total GB"
    }
    $diskInfo = if ($diskItems) { $diskItems -join ", " } else { "ไม่สามารถระบุ DISK" }
} catch {
    $diskInfo = "ไม่สามารถระบุ DISK"
}

$remoteReady = 0
try {
    $tvnSvc = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if ($tvnSvc -and $tvnSvc.Status -eq 'Running') {
        $remoteReady = 1
    }
} catch {}

$osCaption = ($osInfo -replace 'Microsoft\s*', '').Trim()
$osVerStr = $osCaption
try {
    $regNT = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion" -ErrorAction SilentlyContinue
    $dispVer = if ($regNT.DisplayVersion) { $regNT.DisplayVersion } elseif ($regNT.ReleaseId) { $regNT.ReleaseId } else { "" }
    $build = $regNT.CurrentBuild
    $arch = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).OSArchitecture
    if ($dispVer) { $osVerStr += " $dispVer" }
    if ($arch -or $build) {
        $p = @()
        if ($arch) { $p += $arch }
        if ($build) { $p += "Build $build" }
        $osVerStr += " (" + ($p -join ", ") + ")"
    }
} catch {}

$officeFinal = "Not Installed"
try {
    $c2r = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Office\ClickToRun\Configuration" -ErrorAction SilentlyContinue
    if ($c2r -and $c2r.ProductReleaseIds) {
        $idStr = "$($c2r.ProductReleaseIds)"
        $verReport = $c2r.VersionToReport
        $platform = $c2r.Platform
        $friendly = if ($idStr -match '2024') { "Microsoft Office 2024" }
                    elseif ($idStr -match '2021') { "Microsoft Office 2021" }
                    elseif ($idStr -match '2019') { "Microsoft Office 2019" }
                    elseif ($idStr -match 'O365|M365|365') { "Microsoft 365 Apps" }
                    elseif ($idStr -match '2016') { "Microsoft Office 2016" }
                    elseif ($idStr -match '2013') { "Microsoft Office 2013" }
                    elseif ($idStr -match '2010') { "Microsoft Office 2010" }
                    else { "Microsoft Office" }
        $edition = if ($idStr -match 'ProPlus') { " Professional Plus" }
                   elseif ($idStr -match 'Standard') { " Standard" }
                   elseif ($idStr -match 'HomeBusiness') { " Home & Business" }
                   elseif ($idStr -match 'HomeStudent') { " Home & Student" }
                   else { "" }
        $officeFinal = "$friendly$edition"
        if ($verReport) { $officeFinal += " ($verReport" + (if ($platform) { " $platform" } else { "" }) + ")" }
    }
    if ($officeFinal -eq "Not Installed") {
        $keys = @("HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*", "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*")
        $installed = Get-ItemProperty $keys -ErrorAction SilentlyContinue | Where-Object { 
            $_.DisplayName -match '^Microsoft Office (Professional|Standard|Home|Personal|365|Enterprise)' -or
            $_.DisplayName -match '^Microsoft 365'
        } | Select-Object -First 1
        if ($installed) {
            $officeFinal = $installed.DisplayName + (if ($installed.DisplayVersion) { " ($($installed.DisplayVersion))" } else { "" })
        }
    }
    if ($officeFinal -eq "Not Installed") {
        $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\excel.exe" -ErrorAction SilentlyContinue).'(Default)'
        if (-not $appPath) { $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\winword.exe" -ErrorAction SilentlyContinue).'(Default)' }
        if ($appPath -and (Test-Path $appPath)) {
            $vi = (Get-Item $appPath -ErrorAction SilentlyContinue).VersionInfo
            $m = $vi.ProductMajorPart
            $known = switch ($m) { 16 { "Microsoft Office 2016/2019/365" } 15 { "Microsoft Office 2013" } 14 { "Microsoft Office 2010" } 12 { "Microsoft Office 2007" } default { "Microsoft Office" } }
            $officeFinal = "$known" + (if ($vi.ProductVersion) { " ($($vi.ProductVersion))" } else { "" })
        }
    }
} catch {}

$reportObj = @{
    device_id      = $devId
    hostname       = $compName
    device_name    = $userName
    ip_address     = $clientIp
    os             = $osInfo
    os_version     = $osVerStr
    office_version = $officeFinal
    cpu            = $cpuInfo
    ram            = $ramInfo
    disk           = $diskInfo
    remote_ready   = $remoteReady
    install_type   = 'installed'
    browser        = 'AutoSync Service'
}
$jsonPayload = $reportObj | ConvertTo-Json

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
    $res = Invoke-RestMethod -Uri "{{APP_URL}}/api/track-install" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 10 -ErrorAction Stop
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - OK: Host=$compName, IP=$clientIp, RAM=$ramInfo, DISK=$diskInfo"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
} catch {
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Err: $($_.Exception.Message)"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
}
'@

Write-Host " [Step 4/5] Setting up Auto-Sync background task (Task Scheduler & Registry)..." -ForegroundColor Yellow
try {
    Set-Content -Path $syncScriptPath -Value $syncCode -Encoding UTF8 -Force
} catch {}

# 1. Register Task Scheduler: Startup (ONSTART with 30s delay)
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    schtasks /create /tn "CSHOS-AutoSync-Startup" /tr $taskCmd /sc ONSTART /delay 0000:30 /ru SYSTEM /f | Out-Null
} catch {}

# 2. Register Task Scheduler: Logon (ONLOGON with 15s delay)
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    schtasks /create /tn "CSHOS-AutoSync-Logon" /tr $taskCmd /sc ONLOGON /delay 0000:15 /rl HIGHEST /f | Out-Null
} catch {}

# 3. Register Windows Registry Run Key
try {
    $regCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /t REG_SZ /d $regCmd /f | Out-Null
    reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /t REG_SZ /d $regCmd /f | Out-Null
} catch {}

# Trigger initial Auto-Sync immediately in background to verify setup
try {
    Start-Process powershell.exe -ArgumentList "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`"" -WindowStyle Hidden
} catch {}

# 4. Register vnc:// URL protocol for TightVNC Viewer (CSHOS Remote Launcher)
try {
    $passFile = Join-Path $sysDir "vnc_password.txt"
    Set-Content -Path $passFile -Value "{{VNC_PASSWORD}}" -Encoding ASCII -Force
    $vncLauncherVbs = Join-Path $sysDir "vnc-launcher.vbs"
    $vbsCode = @'
Set args = WScript.Arguments
If args.Count = 0 Then WScript.Quit
rawUrl = Replace(Replace(args(0), "vnc://", "", 1, -1, 1), "/", "")
host = ""
port = "5900"
pass = "{{VNC_PASSWORD}}"
Set fso = CreateObject("Scripting.FileSystemObject")
If fso.FileExists("C:\ProgramData\CSHOS\vnc_password.txt") Then
    On Error Resume Next
    Set pf = fso.OpenTextFile("C:\ProgramData\CSHOS\vnc_password.txt", 1)
    pass = Trim(pf.ReadAll)
    pf.Close
    On Error Goto 0
End If
targetPart = rawUrl
If InStr(targetPart, ":") > 0 Then
    hParts = Split(targetPart, ":")
    host = hParts(0)
    port = hParts(1)
Else
    host = targetPart
End If
vncExe = "C:\Program Files\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then vncExe = "C:\Program Files (x86)\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then
    MsgBox "TightVNC Viewer is not installed.", 16, "CSHOS VNC"
    WScript.Quit
End If
Set shell = CreateObject("WScript.Shell")
If pass <> "" Then
    cmd = """" & vncExe & """ -host=" & host & " -port=" & port & " -password=" & pass
Else
    cmd = """" & vncExe & """ -host=" & host & " -port=" & port
End If
shell.Run cmd, 1, False
'@
    Set-Content -Path $vncLauncherVbs -Value $vbsCode -Encoding ASCII -Force
    reg add "HKCU\Software\Classes\vnc" /ve /t REG_SZ /d "URL:VNC Protocol" /f | Out-Null
    reg add "HKCU\Software\Classes\vnc" /v "URL Protocol" /t REG_SZ /d "" /f | Out-Null
    reg add "HKCU\Software\Classes\vnc\shell\open\command" /ve /t REG_SZ /d "wscript.exe `"$vncLauncherVbs`" `"%1`"" /f | Out-Null
} catch {}
Write-Host "       -> Auto-Sync background task configured successfully." -ForegroundColor Green


# --- Report Hardware Specs & Remote Status to CSHOS Server ---
Write-Host " [Step 6/6] Synchronizing machine status with CSHOS Server..." -ForegroundColor Yellow
$reportObj = @{
    device_id      = $devId
    hostname       = $compName
    device_name    = $userName
    ip_address     = $clientIp
    os             = $osInfo
    os_version     = $osVerStr
    office_version = $officeFinal
    cpu            = $cpuInfo
    ram            = $ramInfo
    disk           = $diskInfo
    remote_ready   = 1
    install_type   = 'installed'
    browser        = 'Desktop App'
}
$jsonPayload = $reportObj | ConvertTo-Json

$serverSyncStatus = "Waiting for connection..."
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
    $res = Invoke-RestMethod -Uri "{{APP_URL}}/api/track-install" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 8 -ErrorAction Stop
    if ($res -and $res.status -eq 'success') {
        $serverSyncStatus = "Successfully updated client info to CSHOS Server!"
        if ($res.ip) { $allIpsStr = $res.ip }
    } else {
        $serverSyncStatus = "Connected to CSHOS Server"
    }
} catch {
    $serverSyncStatus = "Saved locally on machine (CSHOS Server offline/unreachable)"
}
Write-Host "       -> $serverSyncStatus" -ForegroundColor Green

Write-Host ""
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host "      CSHOS DIGITAL HEALTH - CHIANG SAEN HOSPITAL" -ForegroundColor White
Write-Host "      Desktop App Installed & Client Specs Updated Successfully!" -ForegroundColor Green
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host " [1] Desktop Shortcut:" -ForegroundColor Yellow
Write-Host "     * Location                    : " -NoNewline -ForegroundColor Gray
Write-Host "Public Desktop (All Users - $installedCount positions)" -ForegroundColor Green
Write-Host "     * App Icon File               : " -NoNewline -ForegroundColor Gray
if ($iconDownloaded -or (Test-Path $icoPath)) {
    Write-Host "Installed Successfully ($icoPath)" -ForegroundColor Green
} else {
    Write-Host "Default System Icon" -ForegroundColor Yellow
}
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [2] Client Machine Specifications (Hardware & System Specs):" -ForegroundColor Yellow
Write-Host "     * Computer Name               : " -NoNewline -ForegroundColor Gray
Write-Host "$compName" -ForegroundColor White
Write-Host "     * Current User (Username)     : " -NoNewline -ForegroundColor Gray
Write-Host "$userName" -ForegroundColor White
Write-Host "     * IP Address                  : " -NoNewline -ForegroundColor Gray
Write-Host "$allIpsStr" -ForegroundColor Green
Write-Host "     * Processor (CPU)             : " -NoNewline -ForegroundColor Gray
Write-Host "$cpuInfo" -ForegroundColor White
Write-Host "     * Memory (RAM)                : " -NoNewline -ForegroundColor Gray
Write-Host "$ramInfo" -ForegroundColor White
Write-Host "     * Hard Disk (DISK)            : " -NoNewline -ForegroundColor Gray
Write-Host "$diskInfo" -ForegroundColor White
Write-Host "     * Operating System (OS)       : " -NoNewline -ForegroundColor Gray
Write-Host "$osVerStr" -ForegroundColor White
Write-Host "     * Microsoft Office            : " -NoNewline -ForegroundColor Gray
Write-Host "$officeFinal" -ForegroundColor White
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [3] Internal Remote Support (TightVNC - Screen Sharing, No Logout):" -ForegroundColor Yellow
Write-Host "     * Service Status              : " -NoNewline -ForegroundColor Gray
Write-Host "Ready to Connect (TightVNC Port 5900)" -ForegroundColor Green
Write-Host "     * VNC Password                : " -NoNewline -ForegroundColor Gray
Write-Host "{{VNC_PASSWORD}}" -ForegroundColor Yellow
Write-Host "     * Mode                        : Screen Sharing (IT assistance without session logout)" -ForegroundColor DarkCyan
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [4] Auto-Sync System on Boot / Restart (Auto-Sync on Reboot):" -ForegroundColor Yellow
Write-Host "     * Service Status              : " -NoNewline -ForegroundColor Gray
Write-Host "Enabled (Windows Task Scheduler & Registry)" -ForegroundColor Green
Write-Host "     * Feature                     : " -NoNewline -ForegroundColor Gray
Write-Host "Auto-updates IP and Specs to CSHOS Server on every system boot/logon" -ForegroundColor White
Write-Host "     * Convenience                 : " -NoNewline -ForegroundColor Gray
Write-Host "No need to reinstall or rerun installer again" -ForegroundColor Green
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [5] Server Connection & Sync Status:" -ForegroundColor Yellow
Write-Host "     * $serverSyncStatus" -ForegroundColor Green
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host ""

$popupText = "ติดตั้งไอคอนแอปและอัปเดตข้อมูลเครื่องปลายทางเรียบร้อยแล้ว!`n`n" +
    "================ ตำแหน่งไอคอนทางลัด ===============`n" +
    "• ติดตั้งไว้ที่ : หน้าจอเดสก์ท็อป (Public Desktop สำหรับทุกผู้ใช้)`n`n" +
    "================ ข้อมูลเครื่องคอมพิวเตอร์ ================`n" +
    "• ชื่อเครื่อง (Computer Name) : $compName`n" +
    "• ผู้ใช้งาน (Username)       : $userName`n" +
    "• หมายเลข IP Address        : $allIpsStr`n" +
    "• สเปก CPU                 : $cpuInfo`n" +
    "• ขนาด RAM                 : $ramInfo`n" +
    "• พื้นที่ DISK              : $diskInfo`n" +
    "• ระบบปฏิบัติการ OS        : $osVerStr`n" +
    "• โปรแกรม Microsoft Office  : $officeFinal`n`n" +
    "================ ระบบ Remote VNC ภายใน ================`n" +
    "• สถานะ : พร้อมเชื่อมต่อ (พอร์ต 5900)`n" +
    "• รหัสผ่าน VNC : {{VNC_PASSWORD}} (หน้าจอไม่ดับ)`n`n" +
    "================ ระบบ Auto-Sync อัตโนมัติ ================`n" +
    "• สถานะ : เปิดใช้งานแล้ว (Windows Task Scheduler)`n" +
    "• ทำงาน : อัปเดต IP และสเปกอัตโนมัติทุกครั้งที่เปิด/รีสตาร์ตเครื่อง`n" +
    "• สะดวก : ไม่จำเป็นต้องติดตั้งหรือรันแอปใหม่อีกต่อไป`n`n" +
    "• สถานะระบบ : $serverSyncStatus"

try {
    $ws.Popup($popupText, 10, "{{APP_NAME}} - ข้อมูลเครื่องปลายทาง", 64)
} catch {}
POWERSHELL;

        $psScript = str_replace(
            ['{{APP_NAME}}', '{{APP_URL}}', '{{ICON_URL}}', '{{VNC_PASSWORD}}', '{{VNC_HASH}}'],
            [$appName, $appUrl, $iconUrl, $vncPassword, $vncHash],
            $psTemplate
        );

        // Encode payload using CertUtil compatible Base64 block (64 chars per line)
        // With UTF-8 BOM so PowerShell correctly interprets Thai characters
        $psScriptWithBom = "\xEF\xBB\xBF" . $psScript;
        $b64Payload = chunk_split(base64_encode($psScriptWithBom), 64, "\r\n");

        $batContent = "@echo off\r\n"
            . "chcp 65001 >nul\r\n"
            . "cd /d \"%~dp0\"\r\n"
            . "title {$appName} - Desktop Installer and Auto-Sync Setup\r\n"
            . "echo ========================================================================\r\n"
            . "echo    {$appName} - Chiang Saen Hospital\r\n"
            . "echo    Installing desktop shortcut, detecting specs, configuring remote...\r\n"
            . "echo ========================================================================\r\n"
            . "echo.\r\n"
            . "\r\n"
            . "REM Check Administrator privileges - elevate if needed\r\n"
            . "net session >nul 2>&1\r\n"
            . "if %errorlevel% equ 0 goto :IS_ADMIN\r\n"
            . "\r\n"
            . "echo [INFO] Requesting Administrator privileges (UAC)...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -Command \"\$f = [System.IO.Path]::GetFullPath('%~f0'); Start-Process cmd.exe -ArgumentList @('/k', \$f) -Verb RunAs\"\r\n"
            . "exit /b\r\n"
            . "\r\n"
            . ":IS_ADMIN\r\n"
            . "echo [1/3] Extracting installer components...\r\n"
            . "set \"CSHOS_TEMP_PS=%TEMP%\\cshos_installer_%RANDOM%.ps1\"\r\n"
            . "certutil -decode \"%~f0\" \"%CSHOS_TEMP_PS%\" >nul 2>&1\r\n"
            . "\r\n"
            . "if exist \"%CSHOS_TEMP_PS%\" goto :HAVE_PAYLOAD\r\n"
            . "\r\n"
            . "echo [INFO] Using fallback extractor...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -Command \"\$f = [System.IO.Path]::GetFullPath('%~f0'); \$l = Get-Content -LiteralPath \$f; \$s = \$false; \$b = ''; foreach (\$x in \$l) { if (\$x -match '^-+END') { break }; if (\$s) { \$b += \$x.Trim() }; if (\$x -match '^-+BEGIN') { \$s = \$true } }; if (\$b) { [IO.File]::WriteAllBytes('%CSHOS_TEMP_PS%', [Convert]::FromBase64String(\$b)) }\"\r\n"
            . "\r\n"
            . ":HAVE_PAYLOAD\r\n"
            . "if not exist \"%CSHOS_TEMP_PS%\" (\r\n"
            . "    echo [ERROR] Failed to extract temporary installer.\r\n"
            . "    echo Please make sure the downloaded file is complete.\r\n"
            . "    echo.\r\n"
            . "    pause\r\n"
            . "    exit /b 1\r\n"
            . ")\r\n"
            . "\r\n"
            . "echo [2/3] Running installation and configuring CSHOS...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -File \"%CSHOS_TEMP_PS%\"\r\n"
            . "set \"PS_EXIT=%ERRORLEVEL%\"\r\n"
            . "del /f /q \"%CSHOS_TEMP_PS%\" >nul 2>&1\r\n"
            . "\r\n"
            . "echo.\r\n"
            . "echo ------------------------------------------------------------------------\r\n"
            . "echo [3/3] Setup completed successfully! Press any key to exit...\r\n"
            . "pause >nul\r\n"
            . "exit\r\n"
            . "\r\n"
            . "-----BEGIN CERTIFICATE-----\r\n"
            . $b64Payload
            . "-----END CERTIFICATE-----\r\n";

        return response($batContent, 200, [
            'Content-Type'        => 'application/x-bat',
            'Content-Disposition' => 'attachment; filename="Install-CSHOS-Desktop.bat"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Download standalone lightweight Auto-Sync enabler for client machines (.bat).
     * Enables automatic spec & IP synchronization on boot/restart without reinstalling the app.
     */
    public function downloadAutoSyncInstaller(Request $request)
    {
        $appName = config('app.name', 'CSHOS DATACENTER');
        $appUrl = url('/');

        $psTemplate = <<<'POWERSHELL'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8
$ws = New-Object -ComObject WScript.Shell

# --- Persistent System Folder ---
$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) {
    try { New-Item -ItemType Directory -Path $sysDir -Force | Out-Null } catch {}
}
if (-not (Test-Path $sysDir)) {
    $sysDir = "$env:LOCALAPPDATA\CSHOS"
    if (-not (Test-Path $sysDir)) { New-Item -ItemType Directory -Path $sysDir -Force | Out-Null }
}

$devIdFile = Join-Path $sysDir "device_id.txt"
$devId = ""
if (Test-Path $devIdFile) {
    try { $devId = (Get-Content $devIdFile -Raw).Trim() } catch {}
}
if (-not $devId -and (Test-Path "$env:LOCALAPPDATA\CSHOS\device_id.txt")) {
    try { $devId = (Get-Content "$env:LOCALAPPDATA\CSHOS\device_id.txt" -Raw).Trim() } catch {}
}
if (-not $devId) {
    $devId = [guid]::NewGuid().ToString()
}
try { Set-Content -Path $devIdFile -Value $devId -Force } catch {}

# --- Setup Background Auto-Sync Script ---
$syncScriptPath = Join-Path $sysDir "cshos_sync.ps1"
$syncCode = @'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

$maxWait = 30
$waited = 0
$detectedIps = @()

while ($waited -lt $maxWait) {
    # 1. Prefer active physical interface with Default Gateway route (0.0.0.0/0)
    try {
        $gwRoutes = @(Get-NetRoute -DestinationPrefix '0.0.0.0/0' -AddressFamily IPv4 -ErrorAction SilentlyContinue | Sort-Object RouteMetric)
        foreach ($route in $gwRoutes) {
            $adapter = Get-NetAdapter -InterfaceIndex $route.InterfaceIndex -ErrorAction SilentlyContinue
            if ($adapter) {
                $desc = "$($adapter.InterfaceDescription) $($adapter.Name)"
                if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier') {
                    continue
                }
                $ipObj = Get-NetIPAddress -InterfaceIndex $route.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                         Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -First 1
                if ($ipObj -and $ipObj.IPAddress) {
                    $detectedIps += $ipObj.IPAddress
                }
            }
        }
    } catch {}

    # 2. Physical adapters that are 'Up' without gateway filter
    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $upAdapters = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object { 
                $_.Status -eq 'Up' -and 
                $_.InterfaceDescription -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier' -and
                $_.Name -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth'
            }
            foreach ($ad in $upAdapters) {
                $ips = @(Get-NetIPAddress -InterfaceIndex $ad.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                         Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -ExpandProperty IPAddress)
                $detectedIps += $ips
            }
        } catch {}
    }

    # 3. Fallback via Win32_NetworkAdapterConfiguration (excluding virtual)
    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $netConfigs = Get-CimInstance Win32_NetworkAdapterConfiguration -Filter "IPEnabled=TRUE" -ErrorAction SilentlyContinue
            foreach ($nc in $netConfigs) {
                $desc = "$($nc.Description)"
                if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN') {
                    continue
                }
                foreach ($ip in $nc.IPAddress) {
                    if ($ip -match '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$' -and $ip -ne '127.0.0.1' -and $ip -notmatch '^169\.254\.') {
                        $detectedIps += $ip
                    }
                }
            }
        } catch {}
    }

    if ($detectedIps -and $detectedIps.Count -gt 0) {
        break
    }

    Start-Sleep -Seconds 3
    $waited += 3
}

$clientIp = if ($detectedIps -and $detectedIps.Count -gt 0) { $detectedIps[0] } else { "" }

$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) { $sysDir = "$env:LOCALAPPDATA\CSHOS" }

$devIdFile = Join-Path $sysDir "device_id.txt"
$devId = ""
if (Test-Path $devIdFile) {
    try { $devId = (Get-Content $devIdFile -Raw).Trim() } catch {}
}
if (-not $devId) {
    $devId = [guid]::NewGuid().ToString()
    try { Set-Content -Path $devIdFile -Value $devId -Force } catch {}
}

$compName = $env:COMPUTERNAME
$userName = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
if (-not $userName) { $userName = $env:USERNAME }
if (-not $userName) { $userName = "System" }

$osInfo = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption
if (-not $osInfo) { $osInfo = [System.Environment]::OSVersion.VersionString }

$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $cpuObj) {
    $cpuObj = Get-WmiObject Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
}
if ($cpuObj -and $cpuObj.Name) {
    $rawName = ($cpuObj.Name -replace '\s+', ' ').Trim()
    $clock = 0
    if ($cpuObj.MaxClockSpeed) { $clock = [int]$cpuObj.MaxClockSpeed }
    elseif ($cpuObj.CurrentClockSpeed) { $clock = [int]$cpuObj.CurrentClockSpeed }
    
    $cores = if ($cpuObj.NumberOfCores) { [int]$cpuObj.NumberOfCores } else { 0 }
    $threads = if ($cpuObj.NumberOfLogicalProcessors) { [int]$cpuObj.NumberOfLogicalProcessors } else { 0 }

    $speedStr = ""
    if ($clock -gt 0 -and $rawName -notmatch '@\s*[0-9\.]+\s*GHz') {
        $ghz = [math]::Round(($clock / 1000), 2)
        $speedStr = "@ $ghz GHz"
    }

    $coreStr = ""
    if ($cores -gt 0 -and $threads -gt 0) {
        $coreStr = "($cores Cores, $threads Threads)"
    } elseif ($cores -gt 0) {
        $coreStr = "($cores Cores)"
    }

    $cpuParts = @($rawName)
    if ($speedStr) { $cpuParts += $speedStr }
    if ($coreStr) { $cpuParts += $coreStr }
    $cpuInfo = $cpuParts -join " "
} else {
    $cpuInfo = "ไม่สามารถระบุ CPU"
}

# RAM Specs & Bus Speed
try {
    $totalRamBytes = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory
    if (-not $totalRamBytes) {
        $totalRamBytes = ((Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).TotalVisibleMemorySize) * 1KB
    }
    $ramGB = if ($totalRamBytes) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "ไม่สามารถระบุ RAM" }

    try {
        $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        if (-not $memSticks -or $memSticks.Count -eq 0) {
            $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        }

        if ($memSticks -and $memSticks.Count -gt 0) {
            $first = $memSticks[0]
            $clock = 0
            if ($first.ConfiguredClockSpeed) { $clock = [int]$first.ConfiguredClockSpeed }
            elseif ($first.Speed) { $clock = [int]$first.Speed }

            $smb = if ($first.SMBIOSMemoryType) { [int]$first.SMBIOSMemoryType } else { 0 }
            $memType = switch ($smb) {
                20 { "DDR" }
                21 { "DDR2" }
                24 { "DDR3" }
                26 { "DDR4" }
                34 { "DDR5" }
                default {
                    if ($clock -ge 4800) { "DDR5" }
                    elseif ($clock -ge 2133) { "DDR4" }
                    elseif ($clock -ge 800) { "DDR3" }
                    else { "RAM" }
                }
            }
            $busStr = if ($clock -gt 0) { "Bus $clock MHz" } else { "" }
            $part = if ($first.PartNumber) { ($first.PartNumber -as [string]).Trim() } else { "" }
            $brand = if ($first.Manufacturer) { ($first.Manufacturer -as [string]).Trim() } else { "" }
            if ($brand -match '^(Undefined|None|0x[0-9A-Fa-f]+)$') { $brand = "" }
            
            $modelInfo = if ($brand -and $part) { "$brand $part" } elseif ($brand) { "$brand" } elseif ($part) { "$part" } else { "" }
            $stickCount = $memSticks.Count
            $descList = New-Object System.Collections.Generic.List[string]
            if ($memType) { $descList.Add($memType) }
            if ($busStr) { $descList.Add($busStr) }
            if ($stickCount -gt 1) {
                $detailStr = if ($modelInfo) { " - $modelInfo" } else { "" }
                $descList.Add("($stickCount ช่อง$detailStr)")
            } elseif ($modelInfo) {
                $descList.Add("($modelInfo)")
            }
            $details = $descList -join " "
            if ($ramGB -gt 0) {
                $ramInfo = "$ramGB GB $details".Trim()
            } elseif ($details) {
                $ramInfo = $details
            }
        }
    } catch {}
} catch {
    $ramInfo = "ไม่สามารถระบุ RAM"
}

# Harddisk Type (SSD, NVMe M.2, SATA, HDD) & Capacity
try {
    $physDict = @{}
    try {
        Get-PhysicalDisk -ErrorAction SilentlyContinue | ForEach-Object {
            $key = $_.FriendlyName
            $mt = if ($_.MediaType) { $_.MediaType } else { "" }
            $bt = if ($_.BusType) { $_.BusType } else { "" }
            $physDict[$key] = @{ Media = $mt; Bus = $bt }
        }
    } catch {}

    $diskMap = @{}
    try {
        Get-Disk -ErrorAction SilentlyContinue | ForEach-Object {
            $d = $_
            $bt = if ($d.BusType) { $d.BusType } else { "" }
            $mt = if ($physDict.ContainsKey($d.FriendlyName)) { $physDict[$d.FriendlyName].Media } else { "" }
            if (-not $bt -and $physDict.ContainsKey($d.FriendlyName)) { $bt = $physDict[$d.FriendlyName].Bus }
            
            $typeLabel = if ($bt -match 'NVMe') { "SSD NVMe M.2" }
                         elseif ($mt -eq "SSD" -and $bt) { "SSD $bt" }
                         elseif ($mt -eq "SSD") { "SSD" }
                         elseif ($mt -eq "HDD" -and $bt) { "HDD $bt" }
                         elseif ($mt -eq "HDD") { "HDD" }
                         elseif ($bt) { "$bt" }
                         else { "" }
                         
            Get-Partition -DiskNumber $d.Number -ErrorAction SilentlyContinue | Where-Object { $_.DriveLetter } | ForEach-Object {
                $ltr = "$($_.DriveLetter):"
                $diskMap[$ltr] = $typeLabel
            }
        }
    } catch {}

    $diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue | ForEach-Object {
        $dId = $_.DeviceID
        $free = [math]::Round($_.FreeSpace / 1GB, 1)
        $total = [math]::Round($_.Size / 1GB, 1)
        $type = if ($diskMap.ContainsKey($dId) -and $diskMap[$dId]) { " (" + $diskMap[$dId] + ")" } else { "" }
        "$dId$type ว่าง $free/$total GB"
    }
    $diskInfo = if ($diskItems) { $diskItems -join ", " } else { "ไม่สามารถระบุ DISK" }
} catch {
    $diskInfo = "ไม่สามารถระบุ DISK"
}

$remoteReady = 0
try {
    $tvnSvc = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if ($tvnSvc -and $tvnSvc.Status -eq 'Running') {
        $remoteReady = 1
    }
} catch {}

$osCaption = ($osInfo -replace 'Microsoft\s*', '').Trim()
$osVerStr = $osCaption
try {
    $regNT = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion" -ErrorAction SilentlyContinue
    $dispVer = if ($regNT.DisplayVersion) { $regNT.DisplayVersion } elseif ($regNT.ReleaseId) { $regNT.ReleaseId } else { "" }
    $build = $regNT.CurrentBuild
    $arch = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).OSArchitecture
    if ($dispVer) { $osVerStr += " $dispVer" }
    if ($arch -or $build) {
        $p = @()
        if ($arch) { $p += $arch }
        if ($build) { $p += "Build $build" }
        $osVerStr += " (" + ($p -join ", ") + ")"
    }
} catch {}

$officeFinal = "Not Installed"
try {
    $c2r = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Office\ClickToRun\Configuration" -ErrorAction SilentlyContinue
    if ($c2r -and $c2r.ProductReleaseIds) {
        $idStr = "$($c2r.ProductReleaseIds)"
        $verReport = $c2r.VersionToReport
        $platform = $c2r.Platform
        $friendly = if ($idStr -match '2024') { "Microsoft Office 2024" }
                    elseif ($idStr -match '2021') { "Microsoft Office 2021" }
                    elseif ($idStr -match '2019') { "Microsoft Office 2019" }
                    elseif ($idStr -match 'O365|M365|365') { "Microsoft 365 Apps" }
                    elseif ($idStr -match '2016') { "Microsoft Office 2016" }
                    elseif ($idStr -match '2013') { "Microsoft Office 2013" }
                    elseif ($idStr -match '2010') { "Microsoft Office 2010" }
                    else { "Microsoft Office" }
        $edition = if ($idStr -match 'ProPlus') { " Professional Plus" }
                   elseif ($idStr -match 'Standard') { " Standard" }
                   elseif ($idStr -match 'HomeBusiness') { " Home & Business" }
                   elseif ($idStr -match 'HomeStudent') { " Home & Student" }
                   else { "" }
        $officeFinal = "$friendly$edition"
        if ($verReport) { $officeFinal += " ($verReport" + (if ($platform) { " $platform" } else { "" }) + ")" }
    }
    if ($officeFinal -eq "Not Installed") {
        $keys = @("HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*", "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*")
        $installed = Get-ItemProperty $keys -ErrorAction SilentlyContinue | Where-Object { 
            $_.DisplayName -match '^Microsoft Office (Professional|Standard|Home|Personal|365|Enterprise)' -or
            $_.DisplayName -match '^Microsoft 365'
        } | Select-Object -First 1
        if ($installed) {
            $officeFinal = $installed.DisplayName + (if ($installed.DisplayVersion) { " ($($installed.DisplayVersion))" } else { "" })
        }
    }
    if ($officeFinal -eq "Not Installed") {
        $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\excel.exe" -ErrorAction SilentlyContinue).'(Default)'
        if (-not $appPath) { $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\winword.exe" -ErrorAction SilentlyContinue).'(Default)' }
        if ($appPath -and (Test-Path $appPath)) {
            $vi = (Get-Item $appPath -ErrorAction SilentlyContinue).VersionInfo
            $m = $vi.ProductMajorPart
            $known = switch ($m) { 16 { "Microsoft Office 2016/2019/365" } 15 { "Microsoft Office 2013" } 14 { "Microsoft Office 2010" } 12 { "Microsoft Office 2007" } default { "Microsoft Office" } }
            $officeFinal = "$known" + (if ($vi.ProductVersion) { " ($($vi.ProductVersion))" } else { "" })
        }
    }
} catch {}

$reportObj = @{
    device_id      = $devId
    hostname       = $compName
    device_name    = $userName
    ip_address     = $clientIp
    os             = $osInfo
    os_version     = $osVerStr
    office_version = $officeFinal
    cpu            = $cpuInfo
    ram            = $ramInfo
    disk           = $diskInfo
    remote_ready   = $remoteReady
    install_type   = 'installed'
    browser        = 'AutoSync Service'
}
$jsonPayload = $reportObj | ConvertTo-Json

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
    $res = Invoke-RestMethod -Uri "{{APP_URL}}/api/track-install" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 10 -ErrorAction Stop
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - OK: Host=$compName, IP=$clientIp, RAM=$ramInfo, DISK=$diskInfo"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
} catch {
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Err: $($_.Exception.Message)"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
}
'@

try {
    Set-Content -Path $syncScriptPath -Value $syncCode -Encoding UTF8 -Force
} catch {}

# 1. Register Task Scheduler: Startup (ONSTART with 30s delay)
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    schtasks /create /tn "CSHOS-AutoSync-Startup" /tr $taskCmd /sc ONSTART /delay 0000:30 /ru SYSTEM /f | Out-Null
} catch {}

# 2. Register Task Scheduler: Logon (ONLOGON with 15s delay)
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    schtasks /create /tn "CSHOS-AutoSync-Logon" /tr $taskCmd /sc ONLOGON /delay 0000:15 /rl HIGHEST /f | Out-Null
} catch {}

# 3. Register Windows Registry Run Key
try {
    $regCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /t REG_SZ /d $regCmd /f | Out-Null
} catch {}

# Execute immediate sync test
try {
    & $syncScriptPath
} catch {}

# Read detected info for display
$compName = $env:COMPUTERNAME
$userName = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
if (-not $userName) { $userName = $env:USERNAME }
$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $cpuObj) {
    $cpuObj = Get-WmiObject Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
}
if ($cpuObj -and $cpuObj.Name) {
    $rawName = ($cpuObj.Name -replace '\s+', ' ').Trim()
    $clock = 0
    if ($cpuObj.MaxClockSpeed) { $clock = [int]$cpuObj.MaxClockSpeed }
    elseif ($cpuObj.CurrentClockSpeed) { $clock = [int]$cpuObj.CurrentClockSpeed }
    
    $cores = if ($cpuObj.NumberOfCores) { [int]$cpuObj.NumberOfCores } else { 0 }
    $threads = if ($cpuObj.NumberOfLogicalProcessors) { [int]$cpuObj.NumberOfLogicalProcessors } else { 0 }

    $speedStr = ""
    if ($clock -gt 0 -and $rawName -notmatch '@\s*[0-9\.]+\s*GHz') {
        $ghz = [math]::Round(($clock / 1000), 2)
        $speedStr = "@ $ghz GHz"
    }

    $coreStr = ""
    if ($cores -gt 0 -and $threads -gt 0) {
        $coreStr = "($cores Cores, $threads Threads)"
    } elseif ($cores -gt 0) {
        $coreStr = "($cores Cores)"
    }

    $cpuParts = @($rawName)
    if ($speedStr) { $cpuParts += $speedStr }
    if ($coreStr) { $cpuParts += $coreStr }
    $cpuInfo = $cpuParts -join " "
} else {
    $cpuInfo = "-"
}

# RAM Specs & Bus Speed
try {
    $totalRamBytes = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory
    if (-not $totalRamBytes) {
        $totalRamBytes = ((Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).TotalVisibleMemorySize) * 1KB
    }
    $ramGB = if ($totalRamBytes) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "-" }

    try {
        $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        if (-not $memSticks -or $memSticks.Count -eq 0) {
            $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
        }

        if ($memSticks -and $memSticks.Count -gt 0) {
            $first = $memSticks[0]
            $clock = 0
            if ($first.ConfiguredClockSpeed) { $clock = [int]$first.ConfiguredClockSpeed }
            elseif ($first.Speed) { $clock = [int]$first.Speed }

            $smb = if ($first.SMBIOSMemoryType) { [int]$first.SMBIOSMemoryType } else { 0 }
            $memType = switch ($smb) {
                20 { "DDR" }
                21 { "DDR2" }
                24 { "DDR3" }
                26 { "DDR4" }
                34 { "DDR5" }
                default {
                    if ($clock -ge 4800) { "DDR5" }
                    elseif ($clock -ge 2133) { "DDR4" }
                    elseif ($clock -ge 800) { "DDR3" }
                    else { "RAM" }
                }
            }
            $busStr = if ($clock -gt 0) { "Bus $clock MHz" } else { "" }
            $part = if ($first.PartNumber) { ($first.PartNumber -as [string]).Trim() } else { "" }
            $brand = if ($first.Manufacturer) { ($first.Manufacturer -as [string]).Trim() } else { "" }
            if ($brand -match '^(Undefined|None|0x[0-9A-Fa-f]+)$') { $brand = "" }
            
            $modelInfo = if ($brand -and $part) { "$brand $part" } elseif ($brand) { "$brand" } elseif ($part) { "$part" } else { "" }
            $stickCount = $memSticks.Count
            $descList = New-Object System.Collections.Generic.List[string]
            if ($memType) { $descList.Add($memType) }
            if ($busStr) { $descList.Add($busStr) }
            if ($stickCount -gt 1) {
                $detailStr = if ($modelInfo) { " - $modelInfo" } else { "" }
                $descList.Add("($stickCount ช่อง$detailStr)")
            } elseif ($modelInfo) {
                $descList.Add("($modelInfo)")
            }
            $details = $descList -join " "
            if ($ramGB -gt 0) {
                $ramInfo = "$ramGB GB $details".Trim()
            } elseif ($details) {
                $ramInfo = $details
            }
        }
    } catch {}
} catch {
    $ramInfo = "-"
}

# Harddisk Type (SSD, NVMe M.2, SATA, HDD) & Capacity
try {
    $physDict = @{}
    try {
        Get-PhysicalDisk -ErrorAction SilentlyContinue | ForEach-Object {
            $key = $_.FriendlyName
            $mt = if ($_.MediaType) { $_.MediaType } else { "" }
            $bt = if ($_.BusType) { $_.BusType } else { "" }
            $physDict[$key] = @{ Media = $mt; Bus = $bt }
        }
    } catch {}

    $diskMap = @{}
    try {
        Get-Disk -ErrorAction SilentlyContinue | ForEach-Object {
            $d = $_
            $bt = if ($d.BusType) { $d.BusType } else { "" }
            $mt = if ($physDict.ContainsKey($d.FriendlyName)) { $physDict[$d.FriendlyName].Media } else { "" }
            if (-not $bt -and $physDict.ContainsKey($d.FriendlyName)) { $bt = $physDict[$d.FriendlyName].Bus }
            
            $typeLabel = if ($bt -match 'NVMe') { "SSD NVMe M.2" }
                         elseif ($mt -eq "SSD" -and $bt) { "SSD $bt" }
                         elseif ($mt -eq "SSD") { "SSD" }
                         elseif ($mt -eq "HDD" -and $bt) { "HDD $bt" }
                         elseif ($mt -eq "HDD") { "HDD" }
                         elseif ($bt) { "$bt" }
                         else { "" }
                         
            Get-Partition -DiskNumber $d.Number -ErrorAction SilentlyContinue | Where-Object { $_.DriveLetter } | ForEach-Object {
                $ltr = "$($_.DriveLetter):"
                $diskMap[$ltr] = $typeLabel
            }
        }
    } catch {}

    $diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue | ForEach-Object {
        $dId = $_.DeviceID
        $free = [math]::Round($_.FreeSpace / 1GB, 1)
        $total = [math]::Round($_.Size / 1GB, 1)
        $type = if ($diskMap.ContainsKey($dId) -and $diskMap[$dId]) { " (" + $diskMap[$dId] + ")" } else { "" }
        "$dId$type ว่าง $free/$total GB"
    }
    $diskInfo = if ($diskItems) { $diskItems -join ", " } else { "-" }
} catch {
    $diskInfo = "-"
}

# --- Detect Target Machine IP Address (IPv4) ---
$detectedIps = @()

# 1. Prefer active physical interface with Default Gateway route (0.0.0.0/0)
try {
    $gwRoutes = @(Get-NetRoute -DestinationPrefix '0.0.0.0/0' -AddressFamily IPv4 -ErrorAction SilentlyContinue | Sort-Object RouteMetric)
    foreach ($route in $gwRoutes) {
        $adapter = Get-NetAdapter -InterfaceIndex $route.InterfaceIndex -ErrorAction SilentlyContinue
        if ($adapter) {
            $desc = "$($adapter.InterfaceDescription) $($adapter.Name)"
            if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier') {
                continue
            }
            $ipObj = Get-NetIPAddress -InterfaceIndex $route.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                     Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -First 1
            if ($ipObj -and $ipObj.IPAddress) {
                $detectedIps += $ipObj.IPAddress
            }
        }
    }
} catch {}

# 2. Physical adapters that are 'Up' without gateway filter
if (-not $detectedIps -or $detectedIps.Count -eq 0) {
    try {
        $upAdapters = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object { 
            $_.Status -eq 'Up' -and 
            $_.InterfaceDescription -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier' -and
            $_.Name -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth'
        }
        foreach ($ad in $upAdapters) {
            $ips = @(Get-NetIPAddress -InterfaceIndex $ad.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                     Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -ExpandProperty IPAddress)
            $detectedIps += $ips
        }
    } catch {}
}

# 3. Fallback via Win32_NetworkAdapterConfiguration (excluding virtual)
if (-not $detectedIps -or $detectedIps.Count -eq 0) {
    try {
        $netConfigs = Get-CimInstance Win32_NetworkAdapterConfiguration -Filter "IPEnabled=TRUE" -ErrorAction SilentlyContinue
        foreach ($nc in $netConfigs) {
            $desc = "$($nc.Description)"
            if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN') {
                continue
            }
            foreach ($ip in $nc.IPAddress) {
                if ($ip -match '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$' -and $ip -ne '127.0.0.1' -and $ip -notmatch '^169\.254\.') {
                    $detectedIps += $ip
                }
            }
        }
    } catch {}
}

# 4. Ultimate fallback (any IPv4 not loopback)
if (-not $detectedIps -or $detectedIps.Count -eq 0) {
    try {
        $detectedIps = @(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { 
            $_.InterfaceAlias -notmatch 'Loopback' -and 
            $_.IPAddress -notmatch '^(169\.254\.|127\.)' 
        } | Select-Object -ExpandProperty IPAddress)
    } catch {}
}

$allIpsStr = if ($detectedIps) { ($detectedIps | Select-Object -Unique) -join ", " } else { "No IP (DHCP)" }

$osInfo = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption
if (-not $osInfo) { $osInfo = [System.Environment]::OSVersion.VersionString }
$osCaption = ($osInfo -replace 'Microsoft\s*', '').Trim()
$osVerStr = $osCaption
try {
    $regNT = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion" -ErrorAction SilentlyContinue
    $dispVer = if ($regNT.DisplayVersion) { $regNT.DisplayVersion } elseif ($regNT.ReleaseId) { $regNT.ReleaseId } else { "" }
    $build = $regNT.CurrentBuild
    $arch = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).OSArchitecture
    if ($dispVer) { $osVerStr += " $dispVer" }
    if ($arch -or $build) {
        $p = @()
        if ($arch) { $p += $arch }
        if ($build) { $p += "Build $build" }
        $osVerStr += " (" + ($p -join ", ") + ")"
    }
} catch {}

$officeFinal = "Not Installed"
try {
    $c2r = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Office\ClickToRun\Configuration" -ErrorAction SilentlyContinue
    if ($c2r -and $c2r.ProductReleaseIds) {
        $idStr = "$($c2r.ProductReleaseIds)"
        $verReport = $c2r.VersionToReport
        $platform = $c2r.Platform
        $friendly = if ($idStr -match '2024') { "Microsoft Office 2024" }
                    elseif ($idStr -match '2021') { "Microsoft Office 2021" }
                    elseif ($idStr -match '2019') { "Microsoft Office 2019" }
                    elseif ($idStr -match 'O365|M365|365') { "Microsoft 365 Apps" }
                    elseif ($idStr -match '2016') { "Microsoft Office 2016" }
                    elseif ($idStr -match '2013') { "Microsoft Office 2013" }
                    elseif ($idStr -match '2010') { "Microsoft Office 2010" }
                    else { "Microsoft Office" }
        $edition = if ($idStr -match 'ProPlus') { " Professional Plus" }
                   elseif ($idStr -match 'Standard') { " Standard" }
                   elseif ($idStr -match 'HomeBusiness') { " Home & Business" }
                   elseif ($idStr -match 'HomeStudent') { " Home & Student" }
                   else { "" }
        $officeFinal = "$friendly$edition"
        if ($verReport) { $officeFinal += " ($verReport" + (if ($platform) { " $platform" } else { "" }) + ")" }
    }
    if ($officeFinal -eq "Not Installed") {
        $keys = @("HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*", "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*")
        $installed = Get-ItemProperty $keys -ErrorAction SilentlyContinue | Where-Object { 
            $_.DisplayName -match '^Microsoft Office (Professional|Standard|Home|Personal|365|Enterprise)' -or
            $_.DisplayName -match '^Microsoft 365'
        } | Select-Object -First 1
        if ($installed) {
            $officeFinal = $installed.DisplayName + (if ($installed.DisplayVersion) { " ($($installed.DisplayVersion))" } else { "" })
        }
    }
    if ($officeFinal -eq "Not Installed") {
        $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\excel.exe" -ErrorAction SilentlyContinue).'(Default)'
        if (-not $appPath) { $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\winword.exe" -ErrorAction SilentlyContinue).'(Default)' }
        if ($appPath -and (Test-Path $appPath)) {
            $vi = (Get-Item $appPath -ErrorAction SilentlyContinue).VersionInfo
            $m = $vi.ProductMajorPart
            $known = switch ($m) { 16 { "Microsoft Office 2016/2019/365" } 15 { "Microsoft Office 2013" } 14 { "Microsoft Office 2010" } 12 { "Microsoft Office 2007" } default { "Microsoft Office" } }
            $officeFinal = "$known" + (if ($vi.ProductVersion) { " ($($vi.ProductVersion))" } else { "" })
        }
    }
} catch {}

Write-Host ""
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host "      CSHOS DIGITAL HEALTH - CHIANG SAEN HOSPITAL" -ForegroundColor White
Write-Host "      Auto-Sync System Configured & Specs Updated Successfully!" -ForegroundColor Green
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host " [1] Client Machine Specifications:" -ForegroundColor Yellow
Write-Host "     * Computer Name               : " -NoNewline -ForegroundColor Gray
Write-Host "$compName" -ForegroundColor White
Write-Host "     * Current User (Username)     : " -NoNewline -ForegroundColor Gray
Write-Host "$userName" -ForegroundColor White
Write-Host "     * IP Address                  : " -NoNewline -ForegroundColor Gray
Write-Host "$allIpsStr" -ForegroundColor Green
Write-Host "     * Processor (CPU)             : " -NoNewline -ForegroundColor Gray
Write-Host "$cpuInfo" -ForegroundColor White
Write-Host "     * Memory (RAM)                : " -NoNewline -ForegroundColor Gray
Write-Host "$ramInfo" -ForegroundColor White
Write-Host "     * Hard Disk (DISK)            : " -NoNewline -ForegroundColor Gray
Write-Host "$diskInfo" -ForegroundColor White
Write-Host "     * Operating System (OS)       : " -NoNewline -ForegroundColor Gray
Write-Host "$osVerStr" -ForegroundColor White
Write-Host "     * Microsoft Office            : " -NoNewline -ForegroundColor Gray
Write-Host "$officeFinal" -ForegroundColor White
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [2] Auto-Sync System Status (Reboot / Startup Sync):" -ForegroundColor Yellow
Write-Host "     * Status                      : " -NoNewline -ForegroundColor Gray
Write-Host "Enabled (Windows Task Scheduler & Registry)" -ForegroundColor Green
Write-Host "     * Feature                     : " -NoNewline -ForegroundColor Gray
Write-Host "Auto-updates IP & Specs to CSHOS Server on every system boot/logon" -ForegroundColor White
Write-Host "     * Convenience                 : " -NoNewline -ForegroundColor Gray
Write-Host "No need to reinstall or rerun installer again" -ForegroundColor Green
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host ""

$popupText = "เปิดระบบ Auto-Sync สำหรับเครื่องนี้สำเร็จแล้ว!`n`n" +
    "================ ข้อมูลเครื่องปลายทางที่อัปเดต ================`n" +
    "• ชื่อเครื่อง (Computer Name) : $compName`n" +
    "• ผู้ใช้งาน (Username)       : $userName`n" +
    "• หมายเลข IP Address        : $allIpsStr`n" +
    "• สเปก CPU                 : $cpuInfo`n" +
    "• ขนาด RAM                 : $ramInfo`n" +
    "• พื้นที่ DISK              : $diskInfo`n" +
    "• ระบบปฏิบัติการ OS        : $osVerStr`n" +
    "• โปรแกรม Microsoft Office  : $officeFinal`n`n" +
    "================ คุณสมบัติ Auto-Sync ================`n" +
    "• ต่อไปนี้ ทุกครั้งที่มีการเปิดเครื่องหรือรีสตาร์ตเครื่อง`n" +
    "  ข้อมูล IP และสเปกจะถูกส่งไปอัปเดตที่หน้า Admin อัตโนมัติ`n" +
    "• ไม่จำเป็นต้องติดตั้งหรือรันแอปใหม่อีกต่อไป"

try {
    $ws.Popup($popupText, 10, "{{APP_NAME}} - เปิดระบบ Auto-Sync สำเร็จ", 64)
} catch {}
POWERSHELL;

        $psScript = str_replace(
            ['{{APP_NAME}}', '{{APP_URL}}'],
            [$appName, $appUrl],
            $psTemplate
        );

        // Encode payload using CertUtil compatible Base64 block (64 chars per line)
        // With UTF-8 BOM so PowerShell correctly interprets Thai characters
        $psScriptWithBom = "\xEF\xBB\xBF" . $psScript;
        $b64Payload = chunk_split(base64_encode($psScriptWithBom), 64, "\r\n");

        $batContent = "@echo off\r\n"
            . "chcp 65001 >nul\r\n"
            . "cd /d \"%~dp0\"\r\n"
            . "title {$appName} - Enable Auto-Sync on Boot\r\n"
            . "echo ========================================================================\r\n"
            . "echo    {$appName} - Chiang Saen Hospital\r\n"
            . "echo    Configuring Auto-Sync system to report IP and specs on boot...\r\n"
            . "echo ========================================================================\r\n"
            . "echo.\r\n"
            . "\r\n"
            . "REM Check Administrator privileges - elevate if needed\r\n"
            . "net session >nul 2>&1\r\n"
            . "if %errorlevel% equ 0 goto :IS_ADMIN\r\n"
            . "\r\n"
            . "echo [INFO] Requesting Administrator privileges (UAC)...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -Command \"\$f = [System.IO.Path]::GetFullPath('%~f0'); Start-Process cmd.exe -ArgumentList @('/k', \$f) -Verb RunAs\"\r\n"
            . "exit /b\r\n"
            . "\r\n"
            . ":IS_ADMIN\r\n"
            . "echo [1/3] Extracting installer components...\r\n"
            . "set \"CSHOS_TEMP_PS=%TEMP%\\cshos_sync_inst_%RANDOM%.ps1\"\r\n"
            . "certutil -decode \"%~f0\" \"%CSHOS_TEMP_PS%\" >nul 2>&1\r\n"
            . "\r\n"
            . "if exist \"%CSHOS_TEMP_PS%\" goto :HAVE_PAYLOAD\r\n"
            . "\r\n"
            . "echo [INFO] Using fallback extractor...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -Command \"\$f = [System.IO.Path]::GetFullPath('%~f0'); \$l = Get-Content -LiteralPath \$f; \$s = \$false; \$b = ''; foreach (\$x in \$l) { if (\$x -match '^-+END') { break }; if (\$s) { \$b += \$x.Trim() }; if (\$x -match '^-+BEGIN') { \$s = \$true } }; if (\$b) { [IO.File]::WriteAllBytes('%CSHOS_TEMP_PS%', [Convert]::FromBase64String(\$b)) }\"\r\n"
            . "\r\n"
            . ":HAVE_PAYLOAD\r\n"
            . "if not exist \"%CSHOS_TEMP_PS%\" (\r\n"
            . "    echo [ERROR] Failed to extract temporary installer.\r\n"
            . "    echo Please make sure the downloaded file is complete.\r\n"
            . "    echo.\r\n"
            . "    pause\r\n"
            . "    exit /b 1\r\n"
            . ")\r\n"
            . "\r\n"
            . "echo [2/3] Configuring Auto-Sync service...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -File \"%CSHOS_TEMP_PS%\"\r\n"
            . "set \"PS_EXIT=%ERRORLEVEL%\"\r\n"
            . "del /f /q \"%CSHOS_TEMP_PS%\" >nul 2>&1\r\n"
            . "\r\n"
            . "echo.\r\n"
            . "echo ------------------------------------------------------------------------\r\n"
            . "echo [3/3] Auto-Sync configuration completed successfully! Press any key to exit...\r\n"
            . "pause >nul\r\n"
            . "exit\r\n"
            . "\r\n"
            . "-----BEGIN CERTIFICATE-----\r\n"
            . $b64Payload
            . "-----END CERTIFICATE-----\r\n";

        return response($batContent, 200, [
            'Content-Type'        => 'application/x-bat',
            'Content-Disposition' => 'attachment; filename="Enable-CSHOS-AutoSync.bat"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Download VNC configuration file (.vnc) for client computer.
     */
    public function downloadVnc($id)
    {
        $installation = AppInstallation::findOrFail($id);
        $target = $installation->ip_address ?: $installation->hostname;
        if (!$target) {
            return back()->with('error', 'ไม่พบ IP Address หรือ Hostname ของเครื่องนี้สำหรับเชื่อมต่อ VNC');
        }

        $cleanName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $installation->hostname ?: $installation->ip_address);
        $fileName = "remote-{$cleanName}.vnc";

        $vncHash = $this->getVncPasswordHash();
        $vncContent = "[connection]\r\n"
            . "host={$target}\r\n"
            . "port=5900\r\n"
            . "password={$vncHash}\r\n"
            . "\r\n"
            . "[options]\r\n"
            . "use_encoding_0=1\r\n"
            . "use_encoding_1=1\r\n"
            . "use_encoding_2=1\r\n"
            . "use_encoding_7=1\r\n"
            . "viewonly=0\r\n"
            . "fullscreen=0\r\n"
            . "fitwindow=1\r\n"
            . "shared=1\r\n"
            . "belldeiconify=0\r\n";

        return response($vncContent, 200, [
            'Content-Type'        => 'application/x-vnc',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Download Windows Remote Desktop (.rdp) configuration file for client computer.
     */
    public function downloadRdp($id)
    {
        $installation = AppInstallation::findOrFail($id);
        $target = $installation->ip_address ?: $installation->hostname;
        if (!$target) {
            return back()->with('error', 'ไม่พบ IP Address หรือ Hostname ของเครื่องนี้สำหรับเชื่อมต่อ Remote');
        }

        $cleanName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $installation->hostname ?: $installation->ip_address);
        $fileName = "remote-{$cleanName}.rdp";

        $rdpContent = "full address:s:{$target}\r\n"
            . "prompt for credentials:i:1\r\n"
            . "administrative session:i:1\r\n"
            . "screen mode id:i:2\r\n"
            . "use multimon:i:0\r\n"
            . "desktopwidth:i:1920\r\n"
            . "desktopheight:i:1080\r\n"
            . "session bpp:i:32\r\n"
            . "compression:i:1\r\n"
            . "keyboardhook:i:2\r\n"
            . "audiomode:i:0\r\n"
            . "redirectclipboard:i:1\r\n"
            . "redirectprinters:i:1\r\n"
            . "redirectcomports:i:0\r\n"
            . "redirectsmartcards:i:1\r\n"
            . "redirectposdevices:i:0\r\n"
            . "drivestoredirect:s:*\r\n"
            . "displayconnectionbar:i:1\r\n"
            . "autoreconnection enabled:i:1\r\n"
            . "authentication level:i:2\r\n"
            . "enableworkspacereconnect:i:0\r\n"
            . "gatewayusagemethod:i:0\r\n"
            . "promptcredentialonce:i:0\r\n";

        return response($rdpContent, 200, [
            'Content-Type'        => 'application/x-rdp',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
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

    /**
     * Download Uninstall-CSHOS.bat to completely remove shortcuts, Auto-Sync jobs, and system data.
     */
    public function downloadUninstaller(Request $request)
    {
        $appName = "CSHOS DATACENTER";
        $fileName = "Uninstall-CSHOS.bat";

        $batContent = "@echo off\r\n"
            . "chcp 65001 >nul\r\n"
            . "cd /d \"%~dp0\"\r\n"
            . "title {$appName} - Uninstall and Cleanup\r\n"
            . "echo ========================================================================\r\n"
            . "echo    {$appName} - Chiang Saen Hospital\r\n"
            . "echo    Uninstalling and removing CSHOS system components...\r\n"
            . "echo ========================================================================\r\n"
            . "echo.\r\n"
            . "\r\n"
            . "REM Check Administrator privileges - elevate if needed\r\n"
            . "net session >nul 2>&1\r\n"
            . "if %errorlevel% equ 0 goto :IS_ADMIN\r\n"
            . "\r\n"
            . "echo [INFO] Requesting Administrator privileges (UAC)...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -Command \"\$f = [System.IO.Path]::GetFullPath('%~f0'); Start-Process cmd.exe -ArgumentList @('/k', \$f) -Verb RunAs\"\r\n"
            . "exit /b\r\n"
            . "\r\n"
            . ":IS_ADMIN\r\n"
            . "echo [1/5] Removing Auto-Sync tasks in Windows Task Scheduler...\r\n"
            . "schtasks /delete /tn \"CSHOS-AutoSync-Startup\" /f >nul 2>&1\r\n"
            . "schtasks /delete /tn \"CSHOS-AutoSync-Logon\" /f >nul 2>&1\r\n"
            . "\r\n"
            . "echo [2/5] Removing startup entries in Windows Registry (Run Keys)...\r\n"
            . "reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Run\" /v \"CSHOS_AutoSync\" /f >nul 2>&1\r\n"
            . "reg delete \"HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Run\" /v \"CSHOS_AutoSync\" /f >nul 2>&1\r\n"
            . "\r\n"
            . "echo [3/5] Removing VNC Protocol Launcher registration...\r\n"
            . "reg delete \"HKCU\\Software\\Classes\\vnc\" /f >nul 2>&1\r\n"
            . "\r\n"
            . "echo [4/5] Removing Desktop shortcuts...\r\n"
            . "del /f /q \"%PUBLIC%\\Desktop\\{$appName}.lnk\" >nul 2>&1\r\n"
            . "del /f /q \"%USERPROFILE%\\Desktop\\{$appName}.lnk\" >nul 2>&1\r\n"
            . "del /f /q \"%ALLUSERSPROFILE%\\Desktop\\{$appName}.lnk\" >nul 2>&1\r\n"
            . "powershell.exe -NoProfile -Command \"Get-ChildItem 'C:\\Users\\*\\Desktop\\{$appName}.lnk' -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue\"\r\n"
            . "\r\n"
            . "echo [5/5] Removing CSHOS system directories and cache...\r\n"
            . "rmdir /s /q \"C:\\ProgramData\\CSHOS\" >nul 2>&1\r\n"
            . "rmdir /s /q \"%LOCALAPPDATA%\\CSHOS\" >nul 2>&1\r\n"
            . "ie4uinit.exe -show >nul 2>&1\r\n"
            . "\r\n"
            . "echo ========================================================================\r\n"
            . "echo    CSHOS components and shortcuts removed successfully!\r\n"
            . "echo ========================================================================\r\n"
            . "echo.\r\n"
            . "powershell.exe -NoProfile -Command \"(New-Object -ComObject WScript.Shell).Popup('ถอนการติดตั้งไอคอนและระบบ Auto-Sync ของ {$appName} เรียบร้อยแล้ว!', 5, '{$appName}', 64) | Out-Null\"\r\n"
            . "timeout /t 3 >nul\r\n";

        return response($batContent, 200, [
            'Content-Type'        => 'application/x-bat',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Get configured VNC password from .env / config (full password).
     */
    public function getVncPassword(): string
    {
        $pass = env('VNC_PASSWORD') ?: config('services.vnc.password', '4Sh0sp@remote');
        return trim((string) $pass) ?: '4Sh0sp@remote';
    }

    /**
     * Get computed DES hash for VNC password (cached).
     */
    public function getVncPasswordHash(): string
    {
        $pass = $this->getVncPassword();
        $p8 = substr($pass, 0, 8);
        return \Illuminate\Support\Facades\Cache::remember('vnc_hash_' . md5($p8), 86400 * 30, function () use ($p8) {
            $pSafe = addslashes($p8);
            $psCmd = 'powershell.exe -NoProfile -Command "$des=[System.Security.Cryptography.DES]::Create(); $des.Mode=[System.Security.Cryptography.CipherMode]::CBC; $des.Padding=[System.Security.Cryptography.PaddingMode]::None; $des.Key=[byte[]]@(0xe8,0x4a,0xd6,0x60,0xc4,0x72,0x1a,0xe0); $des.IV=[byte[]]::new(8); $pBytes=[System.Text.Encoding]::ASCII.GetBytes(\'' . $pSafe . '\'.PadRight(8, [char]0).Substring(0,8)); $enc=$des.CreateEncryptor(); $res=$enc.TransformFinalBlock($pBytes,0,8); [System.BitConverter]::ToString($res).Replace(\'-\',\'\').ToLower()"';
            $out = trim(shell_exec($psCmd) ?: '');
            if (preg_match('/^[a-f0-9]{16}$/i', $out)) {
                return strtolower($out);
            }
            return '51bb787873401462';
        });
    }
}
