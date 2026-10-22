<?php

namespace App\Http\Controllers;

use App\Models\AppInstallation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppInstallationController extends Controller
{
    public const CLIENT_APP_VERSION = '2.3.0';

    /**
     * Get the latest client application version.
     */
    public function getClientAppVersion(): string
    {
        return env('CLIENT_APP_VERSION', self::CLIENT_APP_VERSION) ?: self::CLIENT_APP_VERSION;
    }

    /**
     * Get auto-sync interval in minutes (configurable via .env AUTOSYNC_INTERVAL_MINUTES).
     */
    public function getAutoSyncIntervalMinutes(): int
    {
        return (int) (config('app.autosync_interval') ?: env('AUTOSYNC_INTERVAL_MINUTES', 5));
    }

    /**
     * Get client online threshold in minutes (configurable via .env CLIENT_ONLINE_THRESHOLD_MINUTES).
     */
    public function getOnlineThresholdMinutes(): int
    {
        return (int) (config('app.online_threshold') ?: env('CLIENT_ONLINE_THRESHOLD_MINUTES', 15));
    }

    /**
     * Determine reachable base URL for client requests (avoids localhost for remote clients).
     */
    public function getEffectiveAppUrl(?Request $request = null): string
    {
        $request = $request ?: request();
        $schemeAndHost = $request ? $request->getSchemeAndHttpHost() : null;
        $host = $request ? $request->getHost() : null;

        // If request came with a valid non-loopback host (e.g. 192.168.61.180 or domain), use it
        if ($schemeAndHost && $host && !in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
            return rtrim($schemeAndHost, '/');
        }

        // Check if APP_URL in config/env is valid and not localhost
        $configured = rtrim(config('app.url') ?: env('APP_URL', ''), '/');
        if ($configured && !preg_match('#^https?://(localhost|127\.0\.0\.1|::1)#i', $configured)) {
            return $configured;
        }

        // Fallback: Detect local server IP on hospital LAN
        $serverIp = '192.168.61.180';
        $port = $request ? $request->getPort() : 80;
        $scheme = ($request && $request->isSecure()) ? 'https' : 'http';
        $portStr = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';

        return "{$scheme}://{$serverIp}{$portStr}";
    }

    /**
     * Determine reachable asset/endpoint URL for client.
     */
    public function getEffectiveUrl(string $path = '/', ?Request $request = null): string
    {
        $base = $this->getEffectiveAppUrl($request);
        $cleanPath = '/' . ltrim($path, '/');
        return $base . $cleanPath;
    }


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

        // Attempt reverse DNS lookup and NetBIOS lookup for hostname & MAC if not provided
        $hostname = null;
        if ($ip && !in_array($ip, ['127.0.0.1', '::1'])) {
            if (empty($data['hostname'])) {
                $resolved = @gethostbyaddr($ip);
                if ($resolved && $resolved !== $ip) {
                    $hostname = $resolved;
                }
            }
            // If hostname or MAC is still missing, try fast NetBIOS probe on Windows server
            if ((!$hostname || empty($data['mac_address'])) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                try {
                    $escapedIp = escapeshellarg($ip);
                    $nbtOut = shell_exec("nbtstat -A {$escapedIp} 2>&1") ?: '';
                    if (!$hostname && preg_match('/^\s*([a-zA-Z0-9_\-]+)\s+<00>\s+UNIQUE/m', $nbtOut, $mHost)) {
                        $hostname = trim($mHost[1]);
                    }
                    if (empty($data['mac_address']) && preg_match('/MAC\s+Address\s*=\s*([0-9A-Fa-f\-]{17})/i', $nbtOut, $mMac)) {
                        $data['mac_address'] = trim($mMac[1]);
                    }
                } catch (\Throwable $e) {}
            }
        }

        $incomingHost = !empty($data['hostname']) ? $data['hostname'] : $hostname;
        $cleanIncomingHost = $incomingHost ? strtolower(trim(explode('.', $incomingHost)[0])) : null;
        $isLanIp = $ip && !in_array($ip, ['127.0.0.1', '::1']);
        $rawMac = trim($data['mac_address'] ?? '');
        $macAddress = $rawMac ? strtoupper(str_replace([':', ' ', '.'], '-', $rawMac)) : null;

        // 1. Primary Match: Match by unique persistent device_id (Hardware GUID stored in ProgramData)
        $installation = AppInstallation::where('device_id', $deviceId)->first();

        // 2. Hardware MAC Match: If device_id was lost/reinstalled, match by physical network MAC address
        if (!$installation && $macAddress) {
            $installation = AppInstallation::where('mac_address', $macAddress)->first();
        }

        // 3. Hostname Match: If not matched, match by normalized computer hostname (stable across DHCP IP changes)
        if (!$installation && $cleanIncomingHost) {
            $installation = AppInstallation::whereRaw("LOWER(TRIM(SUBSTRING_INDEX(hostname, '.', 1))) = ?", [$cleanIncomingHost])->first();
        }

        // 4. DHCP / LAN IP Match: Only match by IP if existing record does NOT belong to a different active computer
        if (!$installation && $isLanIp) {
            $candidateByIp = AppInstallation::where('ip_address', $ip)->orderByDesc('last_active_at')->first();
            if ($candidateByIp) {
                $candHost = $candidateByIp->hostname ? strtolower(trim(explode('.', $candidateByIp->hostname)[0])) : null;
                $isAnonymousBrowser = str_starts_with($deviceId, 'csh-') && empty($data['cpu']) && empty($data['ram']);
                // If candidate has no hostname, matched incoming hostname, or incoming request is an anonymous browser visit on same LAN IP
                if (!$candHost || ($cleanIncomingHost && $candHost === $cleanIncomingHost) || ($isAnonymousBrowser && !$cleanIncomingHost)) {
                    $installation = $candidateByIp;
                }
            }
        }

        // 5. Preserve permanent GUID device_id if existing record has one and incoming is browser csh-...
        if ($installation) {
            if (str_starts_with($installation->device_id, 'csh-') && !str_starts_with($deviceId, 'csh-')) {
                $installation->device_id = $deviceId;
            }
        }

        // 6. DHCP Migration: If this computer already exists and was assigned a new IP address,
        // clear the new IP from any previous inactive/stale record that used to hold it
        if ($isLanIp) {
            AppInstallation::where('ip_address', $ip)
                ->when($installation, function ($q) use ($installation) {
                    $q->where('id', '!=', $installation->id);
                })
                ->where('device_id', '!=', $deviceId)
                ->when($cleanIncomingHost, function ($q) use ($cleanIncomingHost) {
                    $q->whereRaw("LOWER(TRIM(SUBSTRING_INDEX(hostname, '.', 1))) != ?", [$cleanIncomingHost]);
                })
                ->update(['is_online' => false]);
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
        if ($macAddress) {
            $installation->mac_address = $macAddress;
        }
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

        $isNativeClient = (!empty($data['cpu']) || !empty($data['ram']) || ($data['browser'] ?? '') === 'AutoSync Service');

        if ($isNativeClient) {
            $installation->browser = 'AutoSync Service';
        } elseif (!empty($data['browser']) && empty($installation->browser)) {
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

        if (isset($data['remote_ready']) && (bool) $data['remote_ready']) {
            $installation->remote_ready = true;
        } else {
            // Verify if Port 5900 (VNC) is actually listening on this IP before declaring not ready
            $probeIp = $installation->ip_address ?: $clientIp;
            if ($probeIp && !in_array($probeIp, ['127.0.0.1', '::1'])) {
                $fpVnc = @fsockopen($probeIp, 5900, $errno, $errstr, 0.5);
                if ($fpVnc) {
                    $installation->remote_ready = true;
                    fclose($fpVnc);
                } elseif (isset($data['remote_ready'])) {
                    $installation->remote_ready = false;
                }
            } elseif (isset($data['remote_ready'])) {
                $installation->remote_ready = false;
            }
        }

        if (!empty($data['last_boot_time'])) {
            try {
                $installation->last_boot_time = \Carbon\Carbon::parse($data['last_boot_time']);
            } catch (\Throwable $e) {}
        }
        if (!empty($data['uptime_text'])) {
            $installation->uptime_text = mb_substr($data['uptime_text'], 0, 100);
        }
        if (isset($data['anydesk_installed'])) {
            $installation->anydesk_installed = (bool) $data['anydesk_installed'];
        }
        if (!empty($data['anydesk_id'])) {
            $installation->anydesk_id = mb_substr($data['anydesk_id'], 0, 50);
        }
        if (isset($data['has_risk_software']) || isset($data['risk_software_list'])) {
            $rawRisk = $data['risk_software_list'] ?? '';
            $cleanRisk = \App\Models\RiskSoftwareRule::sanitizeRiskString($rawRisk);
            $installation->has_risk_software = $cleanRisk['has_risk'];
            $installation->risk_software_list = $cleanRisk['list'];
        }

        $serverVersion = $this->getClientAppVersion();

        if ($isNativeClient) {
            if (!empty($data['client_version'])) {
                $installation->client_version = mb_substr($data['client_version'], 0, 50);
            } elseif (!$installation->client_version) {
                $installation->client_version = $serverVersion;
            }
        } else {
            // For browser requests: do NOT overwrite existing client_version
            if (!$installation->client_version) {
                $installation->client_version = '1.0.0';
            }
        }

        $installation->user_agent = $request->userAgent();
        $installation->last_active_at = now();
        $installation->is_online = true;
        $installation->last_probed_at = now();
        $installation->save();

        $this->deduplicateSingleRecord($installation);

        $clientVer = $installation->client_version ?: '1.0.0';
        $isPendingUpdate = (bool) ($installation->pending_update ?? false);
        $isGlobalForce = (bool) Cache::get('cshos_force_all_update', false);

        // Missing metrics indicates client is running an outdated sync script
        $missingMetrics = empty($installation->last_boot_time) || empty($installation->uptime_text);

        $updateAvailable = version_compare($clientVer, $serverVersion, '<') || $isPendingUpdate || $isGlobalForce || $missingMetrics;
        $forceUpdate = $isPendingUpdate || $isGlobalForce || $missingMetrics;

        // Only clear pending_update if native client is on serverVersion or higher AND has metrics
        if ($isNativeClient && $installation->pending_update && version_compare($clientVer, $serverVersion, '>=') && !$missingMetrics) {
            $installation->pending_update = false;
            $installation->save();
        }

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
            'anydesk_installed' => (bool) $installation->anydesk_installed,
            'anydesk_id' => $installation->anydesk_id,
            'has_risk_software' => (bool) $installation->has_risk_software,
            'risk_software_list' => $installation->risk_software_list,
            'client_version' => $installation->client_version,
            'server_version' => $serverVersion,
            'update_available' => $updateAvailable,
            'force_update' => $isPendingUpdate || $isGlobalForce,
            'pending_update' => (bool) $installation->pending_update,
            'update_script_url' => $this->getEffectiveUrl('/api/client/update-script', $request),
            'app_url' => $this->getEffectiveAppUrl($request),
            'app_name' => "CSHOS STAFF",
            'icon_url' => $this->getEffectiveUrl('/app-icon.ico', $request),
            'last_active' => $installation->last_active_at->toDateTimeString(),
        ]);
    }

    /**
     * Dashboard view of installations (Protected by Auth).
     */
    public function index(Request $request)
    {
        // Auto-deduplicate any potential duplicate records before querying
        $this->deduplicateRecords();
        \App\Models\RiskSoftwareRule::resanitizeAllInstallations();

        $query = AppInstallation::query();

        // Search query
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            
            // Check if search term matches staff name or username in CSHOS_DB
            $matchedIps = [];
            try {
                $matchedIps = DB::connection('cshos')
                    ->table('onlineuser as o')
                    ->join('onlineuser_idle as i', 'i.onlineid', '=', 'o.onlineid')
                    ->leftJoin('opduser as u', function($join) {
                        $join->on('u.loginname', '=', DB::raw("IF(o.kskloginname IS NOT NULL AND o.kskloginname != '', o.kskloginname, o.loginname)"));
                    })
                    ->where(function ($sub) use ($search) {
                        $sub->where('u.name', 'like', "%{$search}%")
                            ->orWhere('o.kskloginname', 'like', "%{$search}%")
                            ->orWhere('o.department', 'like', "%{$search}%");
                    })
                    ->pluck('o.ip_address')
                    ->filter()
                    ->unique()
                    ->toArray();
            } catch (\Throwable $e) {}

            $query->where(function ($q) use ($search, $matchedIps) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('hostname', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('os', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%")
                  ->orWhere('cpu', 'like', "%{$search}%")
                  ->orWhere('ram', 'like', "%{$search}%")
                  ->orWhere('disk', 'like', "%{$search}%")
                  ->orWhere('risk_software_list', 'like', "%{$search}%");

                if (!empty($matchedIps)) {
                    $q->orWhereIn('ip_address', $matchedIps);
                }
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        $onlineThreshold = $this->getOnlineThresholdMinutes();

        // Status filter
        if ($request->input('status') === 'online') {
            $query->where('is_online', true)->where('last_active_at', '>=', now()->subMinutes($onlineThreshold));
        } elseif ($request->input('status') === 'offline') {
            $query->where(function ($q) use ($onlineThreshold) {
                $q->where('is_online', false)
                  ->orWhereNull('is_online')
                  ->orWhere('last_active_at', '<', now()->subMinutes($onlineThreshold))
                  ->orWhereNull('last_active_at');
            });
        } elseif ($request->input('status') === 'missing_asset') {
            $query->where(function ($q) {
                $q->whereNull('asset_code')
                  ->orWhere('asset_code', '');
            });
        } elseif ($request->input('status') === 'risk_only') {
            $query->where('has_risk_software', true);
        } elseif ($request->input('status') === 'today') {
            $query->whereDate('last_active_at', today());
        } elseif ($request->input('status') === 'week') {
            $query->where('last_active_at', '>=', now()->subDays(7));
        } elseif ($request->input('status') === 'installed_only') {
            $query->where('install_type', 'installed');
        }

        // Summary statistics
        $serverVersion = $this->getClientAppVersion();
        $outdatedCount = AppInstallation::where(function ($q) use ($serverVersion) {
            $q->whereNull('client_version')
              ->orWhere('client_version', '!=', $serverVersion)
              ->orWhere('pending_update', true);
        })->count();

        $stats = [
            'total_devices'            => AppInstallation::count(),
            'online_count'             => AppInstallation::where('is_online', true)->where('last_active_at', '>=', now()->subMinutes($onlineThreshold))->count(),
            'offline_count'            => AppInstallation::where(function ($q) use ($onlineThreshold) {
                $q->where('is_online', false)
                  ->orWhereNull('is_online')
                  ->orWhere('last_active_at', '<', now()->subMinutes($onlineThreshold))
                  ->orWhereNull('last_active_at');
            })->count(),
            'missing_asset_code_count' => AppInstallation::where(function ($q) {
                $q->whereNull('asset_code')
                  ->orWhere('asset_code', '');
            })->count(),
            'installed_count'          => AppInstallation::where('install_type', 'installed')->count(),
            'active_today'             => AppInstallation::whereDate('last_active_at', today())->count(),
            'active_7days'             => AppInstallation::where('last_active_at', '>=', now()->subDays(7))->count(),
            'unique_ips'               => AppInstallation::distinct('ip_address')->count('ip_address'),
            'risk_count'               => AppInstallation::where('has_risk_software', true)->count(),
            'outdated_count'           => $outdatedCount,
            'pending_count'            => AppInstallation::where('pending_update', true)->count(),
            'server_version'           => $serverVersion,
        ];

        // Unique departments for filter dropdown
        $departments = AppInstallation::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        // Dynamic sorting
        $sortCol = $request->input('sort');
        $sortDir = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Retrieve all records matching the filters
        $allRecords = $query->get();

        // Query CSHOS_DB (onlineuser + opduser) for staff info before sorting
        $allIps = $allRecords->pluck('ip_address')->filter()->unique()->values()->toArray();
        $staffMap = $this->getStaffMapByIps($allIps);

        foreach ($allRecords as $item) {
            $item->computed_online = (bool) ($item->is_online && $item->last_active_at && $item->last_active_at->diffInMinutes(now()) <= $onlineThreshold);
            if (isset($staffMap[$item->ip_address])) {
                $info = $staffMap[$item->ip_address];
                $item->staff_name = $info->staff_name ?: null;
                $item->staff_login = $info->kskloginname ?: null;
                $item->hos_dept = $info->hos_dept ?: null;
                $item->staff_position = $info->staff_position ?: null;
            } else {
                $item->staff_name = null;
                $item->staff_login = null;
                $item->hos_dept = null;
                $item->staff_position = null;
            }
        }

        // Perform in-memory sorting across the full dataset so computed columns (online status, staff, etc.) sort accurately
        $sortedRecords = $allRecords->sortBy(function ($item) use ($sortCol) {
            return match ($sortCol) {
                'id'         => (int) $item->id,
                'status'     => $item->computed_online ? 1 : 0,
                'hostname'   => mb_strtolower((string) $item->hostname, 'UTF-8'),
                'asset_code' => mb_strtolower((string) $item->asset_code, 'UTF-8'),
                'ip'         => sprintf('%03d.%03d.%03d.%03d', ...array_pad(explode('.', (string) $item->ip_address), 4, 0)),
                'vnc'        => (int) $item->remote_ready,
                'anydesk'    => (int) $item->anydesk_installed,
                'risk'       => (int) $item->has_risk_software,
                'uptime'     => (int) $item->uptime_seconds,
                'staff'      => mb_strtolower((string) ($item->staff_name ?? ''), 'UTF-8'),
                'department' => mb_strtolower((string) $item->department, 'UTF-8'),
                'version'    => (string) $item->client_version,
                'notes'      => mb_strtolower((string) $item->notes, 'UTF-8'),
                'active'     => $item->last_active_at ? $item->last_active_at->timestamp : 0,
                default      => $item->last_active_at ? $item->last_active_at->timestamp : 0,
            };
        }, SORT_REGULAR, $sortDir === 'desc')->values();

        // Paginate manually with full query string preservation
        $perPage = 25;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page') ?: 1;
        $currentPageItems = $sortedRecords->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $installations = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $sortedRecords->count(),
            $perPage,
            $currentPage,
            [
                'path'  => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );
        $vnc_password = $this->getVncPassword();

        return view('admin.installations', compact('installations', 'stats', 'departments', 'vnc_password', 'serverVersion'));
    }

    /**
     * Fetch staff name and details from CSHOS_DB (onlineuser joined with opduser by ip_address).
     */
    public function getStaffMapByIps(array $ips): array
    {
        if (empty($ips)) {
            return [];
        }

        try {
            $onlineUsers = DB::connection('cshos')
                ->table('onlineuser as o')
                ->join('onlineuser_idle as i', 'i.onlineid', '=', 'o.onlineid')
                ->leftJoin('opduser as u', function($join) {
                    $join->on('u.loginname', '=', DB::raw("IF(o.kskloginname IS NOT NULL AND o.kskloginname != '', o.kskloginname, o.loginname)"));
                })
                ->whereIn('o.ip_address', $ips)
                ->select(
                    'o.ip_address',
                    'o.computername',
                    'o.kskloginname',
                    'o.ksklogintime',
                    'o.department as hos_dept',
                    'u.name as staff_name',
                    'u.entryposition as staff_position'
                )
                ->orderBy('o.ksklogintime', 'desc')
                ->get();

            $map = [];
            foreach ($onlineUsers as $ou) {
                if (!isset($map[$ou->ip_address])) {
                    $map[$ou->ip_address] = $ou;
                }
            }
            return $map;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CSHOS_DB getStaffMapByIps failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Display client hardware specifications page (Admin & IT).
     */
    public function specs(Request $request)
    {
        $this->deduplicateRecords();

        $query = AppInstallation::query();

        // Search Keyword
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhere('cpu', 'like', "%{$search}%")
                  ->orWhere('ram', 'like', "%{$search}%")
                  ->orWhere('disk', 'like', "%{$search}%")
                  ->orWhere('os_version', 'like', "%{$search}%")
                  ->orWhere('os', 'like', "%{$search}%")
                  ->orWhere('office_version', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by Department
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        // Filter by RAM (Supports multiple selected sizes e.g. ['4', '8'] or string '4,8')
        if ($request->filled('ram')) {
            $ramInput = $request->input('ram');
            $ramList = is_array($ramInput) ? $ramInput : explode(',', (string)$ramInput);
            $ramList = array_filter(array_map('trim', $ramList));

            if (!empty($ramList)) {
                $query->where(function ($q) use ($ramList) {
                    foreach ($ramList as $idx => $val) {
                        $method = ($idx === 0) ? 'where' : 'orWhere';
                        $q->$method(function ($sub) use ($val) {
                            if ($val === '4') {
                                $sub->where('ram', 'like', '4 GB%')->orWhere('ram', 'like', '3.%GB%');
                            } elseif ($val === '8') {
                                $sub->where('ram', 'like', '8 GB%')->orWhere('ram', 'like', '7.%GB%');
                            } elseif ($val === '12') {
                                $sub->where('ram', 'like', '12 GB%')->orWhere('ram', 'like', '11.%GB%');
                            } elseif ($val === '16') {
                                $sub->where('ram', 'like', '16 GB%')->orWhere('ram', 'like', '15.%GB%');
                            } elseif ($val === '32+') {
                                $sub->where('ram', 'like', '32 GB%')->orWhere('ram', 'like', '64 GB%');
                            } elseif ($val === 'low') {
                                $sub->where('ram', 'like', '4 GB%')
                                    ->orWhere('ram', 'like', '2 GB%')
                                    ->orWhere('ram', 'like', '3.%GB%');
                            }
                        });
                    }
                });
            }
        }

        // Filter by RAM Type (DDR5, DDR4, DDR3, etc.)
        if ($request->filled('ram_type')) {
            $rtInput = $request->input('ram_type');
            $rtList = is_array($rtInput) ? $rtInput : explode(',', (string)$rtInput);
            $rtList = array_filter(array_map('trim', $rtList));

            if (!empty($rtList)) {
                $query->where(function ($q) use ($rtList) {
                    foreach ($rtList as $idx => $val) {
                        $method = ($idx === 0) ? 'where' : 'orWhere';
                        $q->$method(function ($sub) use ($val) {
                            if (strtoupper($val) === 'OTHER' || $val === '-') {
                                $sub->where('ram', 'not like', '%DDR%')
                                    ->orWhereNull('ram');
                            } else {
                                $sub->where('ram', 'like', '%' . strtoupper($val) . '%');
                            }
                        });
                    }
                });
            }
        }

        // Filter by Storage Type (SSD vs HDD)
        if ($request->filled('disk_type')) {
            $dt = $request->input('disk_type');
            if ($dt === 'nvme') {
                $query->where('disk', 'like', '%NVMe%');
            } elseif ($dt === 'ssd') {
                $query->where('disk', 'like', '%SSD%');
            } elseif ($dt === 'hdd') {
                $query->where('disk', 'like', '%HDD%')->where('disk', 'not like', '%SSD%');
            }
        }

        // Filter by OS
        if ($request->filled('os')) {
            $osFilter = $request->input('os');
            if ($osFilter === 'win11') {
                $query->where(function ($q) {
                    $q->where('os', 'like', '%Windows 11%')->orWhere('os_version', 'like', '%Windows 11%');
                });
            } elseif ($osFilter === 'win10') {
                $query->where(function ($q) {
                    $q->where('os', 'like', '%Windows 10%')->orWhere('os_version', 'like', '%Windows 10%');
                });
            } elseif ($osFilter === 'other') {
                $query->where('os', 'not like', '%Windows 10%')
                      ->where('os', 'not like', '%Windows 11%');
            }
        }

        // Filter by Office
        if ($request->filled('office')) {
            $off = $request->input('office');
            if ($off === 'installed') {
                $query->whereNotNull('office_version')
                      ->where('office_version', '!=', '')
                      ->where('office_version', 'not like', '%Not Installed%')
                      ->where('office_version', 'not like', '%ไม่ได้ติดตั้ง%');
            } elseif ($off === 'not_installed') {
                $query->where(function ($q) {
                    $q->whereNull('office_version')
                      ->orWhere('office_version', '')
                      ->orWhere('office_version', 'like', '%Not Installed%')
                      ->orWhere('office_version', 'like', '%ไม่ได้ติดตั้ง%');
                });
            } else {
                $query->where('office_version', 'like', "%{$off}%");
            }
        }

        // Filter by Performance Tier (Supports multiple selected tiers e.g. ['fair', 'high'])
        if ($request->filled('perf')) {
            $perfInput = $request->input('perf');
            $perfList = is_array($perfInput) ? $perfInput : explode(',', (string)$perfInput);
            $perfList = array_filter(array_map('trim', $perfList));

            if (!empty($perfList)) {
                $aliasMap = [
                    'excellent' => 'highest',
                    'good' => 'high',
                ];
                $perfList = array_map(function ($p) use ($aliasMap) {
                    return $aliasMap[$p] ?? $p;
                }, $perfList);

                $matchingIds = AppInstallation::all(['id', 'cpu', 'ram', 'disk'])
                    ->filter(function ($item) use ($perfList) {
                        return in_array($item->performance_level['key'] ?? '', $perfList, true);
                    })
                    ->pluck('id');

                $query->whereIn('id', $matchingIds);
            }
        }

        $onlineThreshold = $this->getOnlineThresholdMinutes();

        // Filter by Online status
        if ($request->input('status') === 'online') {
            $query->where('is_online', true)->where('last_active_at', '>=', now()->subMinutes($onlineThreshold));
        } elseif ($request->input('status') === 'offline') {
            $query->where(function ($q) use ($onlineThreshold) {
                $q->where('is_online', false)
                  ->orWhereNull('is_online')
                  ->orWhere('last_active_at', '<', now()->subMinutes($onlineThreshold))
                  ->orWhereNull('last_active_at');
            });
        }

        // Hardware Summary Statistics
        $totalDevices = AppInstallation::count();
        $ssdCount = AppInstallation::where('disk', 'like', '%SSD%')->count();
        $hddOnlyCount = AppInstallation::where('disk', 'like', '%HDD%')->where('disk', 'not like', '%SSD%')->count();
        $win11Count = AppInstallation::where(function($q) {
            $q->where('os', 'like', '%Windows 11%')->orWhere('os_version', 'like', '%Windows 11%');
        })->count();
        $win10Count = AppInstallation::where(function($q) {
            $q->where('os', 'like', '%Windows 10%')->orWhere('os_version', 'like', '%Windows 10%');
        })->count();
        $ram8PlusCount = AppInstallation::where(function($q) {
            $q->where('ram', 'like', '8 GB%')
              ->orWhere('ram', 'like', '16 GB%')
              ->orWhere('ram', 'like', '32 GB%')
              ->orWhere('ram', 'like', '64 GB%');
        })->count();
        $ramLowCount = AppInstallation::where(function($q) {
            $q->where('ram', 'like', '4 GB%')
              ->orWhere('ram', 'like', '2 GB%')
              ->orWhere('ram', 'like', '3.%GB%');
        })->count();
        $officeInstalledCount = AppInstallation::whereNotNull('office_version')
            ->where('office_version', '!=', '')
            ->where('office_version', 'not like', '%Not Installed%')
            ->where('office_version', 'not like', '%ไม่ได้ติดตั้ง%')
            ->count();

        // Performance Tier Summary Distribution
        $allDevicesForPerf = AppInstallation::all(['id', 'cpu', 'ram', 'disk']);
        $perfCounts = [
            'highest' => 0,
            'high'    => 0,
            'fair'    => 0,
            'low'     => 0,
            'lowest'  => 0,
        ];
        foreach ($allDevicesForPerf as $dev) {
            $k = $dev->performance_level['key'] ?? 'lowest';
            if (isset($perfCounts[$k])) {
                $perfCounts[$k]++;
            } else {
                $perfCounts['lowest']++;
            }
        }
        $goodPerfCount = $perfCounts['highest'] + $perfCounts['high']; // เครื่องประสิทธิภาพดี-สูง

        $stats = [
            'total_devices'          => $totalDevices,
            'ssd_count'              => $ssdCount,
            'hdd_only_count'         => $hddOnlyCount,
            'win11_count'            => $win11Count,
            'win10_count'            => $win10Count,
            'ram_8plus_count'        => $ram8PlusCount,
            'ram_low_count'          => $ramLowCount,
            'office_installed_count' => $officeInstalledCount,
            'perf_counts'            => $perfCounts,
            'good_perf_count'        => $goodPerfCount,
        ];

        // Dropdown distinct values
        $departments = AppInstallation::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        // Sorting for all records across all pages
        $sortCol = $request->input('sort', 'last_active');
        $sortDir = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Retrieve all records matching the filters
        $allRecords = $query->get();

        // Perform in-memory sorting across the full dataset so computed columns (speed, cores, ram size, ram type, perf) sort accurately
        $sortedRecords = $allRecords->sortBy(function ($item) use ($sortCol) {
            return match ($sortCol) {
                'id'          => (int) $item->id,
                'hostname'    => mb_strtolower((string) $item->hostname, 'UTF-8'),
                'department'  => mb_strtolower((string) $item->department, 'UTF-8'),
                'perf'        => (int) ($item->performance_level['tier_number'] ?? 0),
                'cpu'         => mb_strtolower((string) $item->short_cpu, 'UTF-8'),
                'cores'       => (function () use ($item) {
                    if (preg_match('/(\d+)\s*C/i', (string) $item->short_cpu_cores, $m)) {
                        return (int) $m[1];
                    }
                    return 0;
                })(),
                'speed'       => (function () use ($item) {
                    if (preg_match('/([\d\.]+)\s*GHz/i', (string) $item->short_cpu_speed, $m)) {
                        return (float) $m[1];
                    }
                    return 0.0;
                })(),
                'ram_size'    => (function () use ($item) {
                    if (preg_match('/(\d+)\s*GB/i', (string) $item->short_ram_size, $m)) {
                        return (int) $m[1];
                    }
                    return 0;
                })(),
                'ram_type'    => match ($item->short_ram_type) {
                    'DDR5'  => 5,
                    'DDR4'  => 4,
                    'DDR3'  => 3,
                    'DDR2'  => 2,
                    default => 0,
                },
                'disk'        => (function () use ($item) {
                    $weight = 0;
                    if (str_contains($item->short_disk, 'NVMe')) {
                        $weight = 3;
                    } elseif (str_contains($item->short_disk, 'SSD')) {
                        $weight = 2;
                    } elseif (str_contains($item->short_disk, 'HDD')) {
                        $weight = 1;
                    }
                    return sprintf('%d_%s', $weight, mb_strtolower((string) $item->short_disk, 'UTF-8'));
                })(),
                'os'          => mb_strtolower((string) $item->short_os, 'UTF-8'),
                'office'      => $item->short_office === 'ไม่มี' ? '' : mb_strtolower((string) $item->short_office, 'UTF-8'),
                'last_active' => $item->last_active_at ? $item->last_active_at->timestamp : 0,
                default       => $item->last_active_at ? $item->last_active_at->timestamp : 0,
            };
        }, SORT_REGULAR, $sortDir === 'desc')->values();

        // Paginate manually with full query string preservation
        $perPage = 25;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page') ?: 1;
        $currentPageItems = $sortedRecords->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $installations = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $sortedRecords->count(),
            $perPage,
            $currentPage,
            [
                'path'  => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        // Staff map
        $ipList = $installations->pluck('ip_address')->filter()->unique()->values()->toArray();
        $staffMap = $this->getStaffMapByIps($ipList);

        foreach ($installations as $item) {
            $item->computed_online = (bool) ($item->is_online && $item->last_active_at && $item->last_active_at->diffInMinutes(now()) <= $onlineThreshold);
            if (isset($staffMap[$item->ip_address])) {
                $info = $staffMap[$item->ip_address];
                $item->staff_name = $info->staff_name ?: null;
                $item->staff_login = $info->kskloginname ?: null;
                $item->hos_dept = $info->hos_dept ?: null;
            } else {
                $item->staff_name = null;
                $item->staff_login = null;
                $item->hos_dept = null;
            }
        }

        $vnc_password = $this->getVncPassword();
        $serverVersion = $this->getClientAppVersion();

        return view('admin.specs', compact(
            'installations',
            'stats',
            'departments',
            'vnc_password',
            'serverVersion'
        ));
    }

    /**
     * Export hardware specs as CSV (Excel compatible with UTF-8 BOM).
     */
    public function exportSpecsCsv(Request $request)
    {
        $filename = 'cshos-hardware-specs-' . date('Y-m-d-His') . '.csv';

        $response = new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'ลำดับ (ID)',
                'แผนก/หน่วยงาน',
                'ชื่อเครื่อง (Hostname)',
                'ประสิทธิภาพ',
                'CPU',
                'Core/Threads',
                'CPU Speed',
                'ความจุ RAM',
                'ประเภท RAM',
                'Disk',
                'OS',
                'Microsoft Office',
                'รหัสครุภัณฑ์ (Asset Code)',
                'ชื่อบัญชีผู้ใช้ (Device User)',
                'เจ้าหน้าที่ HOSxP',
                'IP Address',
                'MAC Address',
                'สถานะการเชื่อมต่อ',
                'CPU เต็ม',
                'RAM เต็ม',
                'Disk เต็ม',
                'OS เต็ม',
                'Office เต็ม',
                'ระยะเวลาเปิดเครื่อง (Uptime)',
                'ใช้งานล่าสุด',
                'หมายเหตุ',
            ]);

            $allIps = AppInstallation::whereNotNull('ip_address')->pluck('ip_address')->filter()->unique()->toArray();
            $staffMap = $this->getStaffMapByIps($allIps);

            $onlineThreshold = $this->getOnlineThresholdMinutes();

            AppInstallation::orderBy('last_active_at', 'desc')->chunk(200, function ($records) use ($handle, $staffMap, $onlineThreshold) {
                foreach ($records as $item) {
                    $staffInfo = $staffMap[$item->ip_address] ?? null;
                    $staffName = $staffInfo ? ($staffInfo->staff_name ?: $staffInfo->kskloginname) : '-';
                    $isOnline = ($item->is_online && $item->last_active_at && $item->last_active_at->diffInMinutes(now()) <= $onlineThreshold);

                    fputcsv($handle, [
                        $item->id,
                        $item->department ?: '-',
                        $item->hostname ?? '-',
                        $item->performance_level['level'] ?? '-',
                        $item->short_cpu,
                        $item->short_cpu_cores,
                        $item->short_cpu_speed,
                        $item->short_ram_size,
                        $item->short_ram_type,
                        $item->short_disk,
                        $item->short_os,
                        $item->short_office,
                        $item->asset_code ?: '-',
                        $item->device_name ?? '-',
                        $staffName,
                        $item->ip_address,
                        $item->mac_address ?: '-',
                        $isOnline ? 'ออนไลน์' : 'ออฟไลน์',
                        $item->cpu ?? '-',
                        $item->ram ?? '-',
                        $item->disk ?? '-',
                        $item->os_version ?? ($item->os ?? '-'),
                        $item->office_version ?: 'ไม่ได้ติดตั้ง',
                        $item->dynamic_uptime ?: ($item->uptime_text ?: '-'),
                        $item->last_active_at ? $item->last_active_at->format('Y-m-d H:i:s') : '-',
                        $item->notes ?: '-',
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Update department, device name, or notes for an installation record.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check() || (!Auth::user()->isAdmin() && !Auth::user()->isIt())) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขข้อมูลส่วนนี้');
        }

        $installation = AppInstallation::findOrFail($id);

        $validated = $request->validate([
            'department'  => 'nullable|string|max:100',
            'device_name' => 'nullable|string|max:100',
            'asset_code'  => 'nullable|string|max:100',
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
     * Clear/delete all offline devices (keep only devices active today).
     */
    public function clearOffline(Request $request)
    {
        $today = \Carbon\Carbon::today();
        $count = AppInstallation::where(function ($query) use ($today) {
            $query->whereNull('last_active_at')
                  ->orWhereDate('last_active_at', '<', $today);
        })->delete();

        return back()->with('success', "ลบเครื่องที่ออฟไลน์ออกจากระบบเรียบร้อยแล้วทั้งหมด {$count} รายการ (คงเหลือเฉพาะเครื่องที่ออนไลน์วันนี้)");
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
     * Trigger immediate auto-update for a specific client machine.
     */
    public function triggerUpdate(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);
        $serverVersion = $this->getClientAppVersion();

        $installation->pending_update = true;
        $installation->update_requested_at = now();
        $installation->save();

        $name = $installation->hostname ?: ($installation->device_name ?: $installation->ip_address);
        $pushed = false;

        // Proactive background trigger via LAN schtasks if IP is available
        if ($installation->ip_address && !in_array($installation->ip_address, ['127.0.0.1', '::1'])) {
            try {
                $ip = escapeshellarg($installation->ip_address);
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $cmd = "start /b schtasks /run /s {$ip} /tn CSHOS-AutoSync-Startup >nul 2>&1";
                    pclose(popen($cmd, "r"));
                    $pushed = true;
                }
            } catch (\Throwable $e) {}
        }

        $msg = "ส่งคำสั่งสั่งอัพเดท CSHOS STAFF (v{$serverVersion}) ไปยังเครื่อง {$name} เรียบร้อยแล้ว ระบบจะทำการอัพเดทอัตโนมัติทันที หรือเมื่อเปิดเครื่อง/ตามรอบเวลา";

        if ($request->wantsJson()) {
            return response()->json([
                'status'         => 'success',
                'message'        => $msg,
                'server_version' => $serverVersion,
                'pending_update' => true,
                'pushed_lan'     => $pushed,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Cancel pending update status for a device.
     */
    public function cancelUpdate(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);
        $installation->pending_update = false;
        $installation->save();

        $name = $installation->hostname ?: ($installation->device_name ?: $installation->ip_address);
        $msg = "ยกเลิกสถานะรออัพเดทของเครื่อง {$name} เรียบร้อยแล้ว";

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Mark device as successfully updated to the target server version.
     */
    public function completeUpdate(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);
        $serverVersion = $this->getClientAppVersion();
        $installation->pending_update = false;
        $installation->client_version = $serverVersion;
        $installation->last_active_at = now();
        $installation->save();

        $name = $installation->hostname ?: ($installation->device_name ?: $installation->ip_address);
        $msg = "บันทึกสถานะเครื่อง {$name} เป็นเวอร์ชัน v{$serverVersion} เรียบร้อยแล้ว";

        if ($request->wantsJson()) {
            return response()->json([
                'status'         => 'success',
                'message'        => $msg,
                'client_version' => $serverVersion,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Clear pending update status across all devices.
     */
    public function clearAllPending(Request $request)
    {
        $count = AppInstallation::where('pending_update', true)->count();
        AppInstallation::where('pending_update', true)->update([
            'pending_update' => false,
        ]);
        Cache::forget('cshos_force_all_update');

        $msg = "ล้างสถานะรออัพเดททั้งหมด ({$count} เครื่อง) เรียบร้อยแล้ว";

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Trigger auto-update broadcast for ALL client machines.
     */
    public function triggerUpdateAll(Request $request)
    {
        $serverVersion = $this->getClientAppVersion();

        // Mark all installations as pending update
        AppInstallation::query()->update([
            'pending_update'      => true,
            'update_requested_at' => now(),
        ]);

        // Set global cache flag for 7 days
        Cache::put('cshos_force_all_update', true, now()->addDays(7));

        // Proactively attempt to wake online machines active today
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $todayIps = AppInstallation::whereDate('last_active_at', today())
                    ->whereNotNull('ip_address')
                    ->pluck('ip_address')
                    ->filter()
                    ->unique();

                foreach ($todayIps as $ip) {
                    if (!in_array($ip, ['127.0.0.1', '::1'])) {
                        $escapedIp = escapeshellarg($ip);
                        $cmd = "start /b schtasks /run /s {$escapedIp} /tn CSHOS-AutoSync-Startup >nul 2>&1";
                        pclose(popen($cmd, "r"));
                    }
                }
            } catch (\Throwable $e) {}
        }

        $count = AppInstallation::count();
        $msg = "ส่งคำสั่งสั่งอัพเดท CSHOS STAFF (v{$serverVersion}) ไปยังเครื่องลูกข่ายทั้งหมด ({$count} เครื่อง) เรียบร้อยแล้ว เครื่องที่เปิดอยู่จะทำการอัพเดท และเครื่องที่ปิดอยู่จะอัพเดททันทีเมื่อเปิดเครื่องหรือตามรอบเวลา";

        if ($request->wantsJson()) {
            return response()->json([
                'status'         => 'success',
                'message'        => $msg,
                'server_version' => $serverVersion,
                'count'          => $count,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Probe & refresh a single device status and details.
     */
    public function refreshDevice(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);
        $ip = $installation->ip_address;
        $isOnline = false;
        $vncReady = false;

        if ($ip && !in_array($ip, ['127.0.0.1', '::1'])) {
            // 1. Probe Port 5900 (VNC) explicitly with 1.0s timeout
            $fpVnc = @fsockopen($ip, 5900, $errno, $errstr, 1.0);
            if ($fpVnc) {
                $isOnline = true;
                $vncReady = true;
                fclose($fpVnc);
            } else {
                // 2. If 5900 is not open, probe standard Windows management ports (445 SMB, 135 RPC)
                foreach ([445, 135] as $port) {
                    $fp = @fsockopen($ip, $port, $errno, $errstr, 0.8);
                    if ($fp) {
                        $isOnline = true;
                        fclose($fp);
                        break;
                    }
                }
            }

            // 3. If not connected via standard ports, try fast ping
            if (!$isOnline) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    exec("ping -n 1 -w 800 " . escapeshellarg($ip), $pingOut, $pingCode);
                    if ($pingCode === 0) {
                        $isOnline = true;
                    }
                }
            }

            $installation->is_online = $isOnline;
            $installation->last_probed_at = now();

            if ($isOnline) {
                $installation->last_active_at = now();
                $installation->remote_ready = (bool) $vncReady;
                // Proactively trigger sync task on the remote machine if Windows host
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    try {
                        $escapedIp = escapeshellarg($ip);
                        $cmd = "start /b schtasks /run /s {$escapedIp} /tn CSHOS-AutoSync-Startup >nul 2>&1";
                        pclose(popen($cmd, "r"));
                    } catch (\Throwable $e) {}
                }
            } else {
                $installation->remote_ready = false;
            }
        }

        $installation->save();

        // Fetch updated staff info from CSHOS_DB if available
        $staffInfo = null;
        if ($ip) {
            $map = $this->getStaffMapByIps([$ip]);
            $staffInfo = $map[$ip] ?? null;
        }

        $name = $installation->hostname ?: ($installation->device_name ?: $installation->ip_address);
        $statusMsg = $isOnline ? "เครื่องออนไลน์" . ($vncReady ? " (พอร์ต VNC 5900 พร้อม)" : "") : "ตรวจสอบแล้ว เครื่องอาจอยู่ในสถานะออฟไลน์หรือยังไม่เปิด";

        if ($request->wantsJson()) {
            return response()->json([
                'status'       => 'success',
                'is_online'    => $isOnline,
                'remote_ready' => (bool) $installation->remote_ready,
                'message'      => "รีเฟรชข้อมูลเครื่อง {$name} เรียบร้อยแล้ว ({$statusMsg})",
                'installation' => $installation,
                'staff_name'   => $staffInfo ? ($staffInfo->staff_name ?: $staffInfo->kskloginname) : null,
                'last_active'  => $installation->last_active_at ? $installation->last_active_at->format('d/m/y H:i') : '-',
            ]);
        }

        return back()->with('success', "รีเฟรชข้อมูลเครื่อง {$name} เรียบร้อยแล้ว ({$statusMsg})");
    }

    /**
     * Trigger immediate hardware & system specification rescan on a remote client.
     */
    public function resyncSpecs(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);
        $name = $installation->hostname ?: ($installation->device_name ?: $installation->ip_address);
        $ip = $installation->ip_address;
        $isOnline = false;
        $pushed = false;

        // Re-sanitize risk software using current active rules immediately
        $cleanRisk = \App\Models\RiskSoftwareRule::sanitizeRiskString($installation->risk_software_list);
        $installation->has_risk_software = $cleanRisk['has_risk'];
        $installation->risk_software_list = $cleanRisk['list'];

        $isLocal = in_array($ip, ['127.0.0.1', '::1']) || $ip === gethostbyname(gethostname()) || $ip === ($request->server('SERVER_ADDR') ?: '');
        if ($isLocal) {
            $isOnline = true;
        } elseif ($ip) {
            $fpVnc = @fsockopen($ip, 5900, $errno, $errstr, 0.8);
            if ($fpVnc) {
                $isOnline = true;
                $installation->remote_ready = true;
                fclose($fpVnc);
            } else {
                foreach ([445, 135] as $port) {
                    $fp = @fsockopen($ip, $port, $errno, $errstr, 0.6);
                    if ($fp) {
                        $isOnline = true;
                        fclose($fp);
                        break;
                    }
                }
            }

            if (!$isOnline) {
                $pingCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
                    ? "ping -n 1 -w 600 " . escapeshellarg($ip)
                    : "ping -c 1 -W 1 " . escapeshellarg($ip);
                @exec($pingCmd, $pingOut, $pingCode);
                if ($pingCode === 0) {
                    $isOnline = true;
                }
            }
        }

        // Flag pending update so client machine executes a full resync cycle upon next contact
        if ($isOnline) {
            $installation->pending_update = true;
            $installation->update_requested_at = now();
        }

        // Proactively trigger the AutoSync task remotely over LAN or locally
        if ($isOnline && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                if ($isLocal) {
                    $cmd = "start /b schtasks /run /tn CSHOS-AutoSync-Startup >nul 2>&1";
                } elseif ($ip) {
                    $escapedIp = escapeshellarg($ip);
                    $cmd = "start /b schtasks /run /s {$escapedIp} /tn CSHOS-AutoSync-Startup >nul 2>&1 & start /b schtasks /run /s {$escapedIp} /tn CSHOS-AutoSync-Hourly >nul 2>&1";
                } else {
                    $cmd = null;
                }
                if ($cmd) {
                    pclose(popen($cmd, "r"));
                    $pushed = true;
                }
            } catch (\Throwable $e) {}
        }

        $installation->is_online = $isOnline;
        $installation->last_probed_at = now();
        if ($isOnline) {
            $installation->last_active_at = now();
        } else {
            $installation->remote_ready = false;
        }
        $installation->save();

        // Refresh staff info from CSHOS_DB
        $staffInfo = null;
        if ($ip) {
            $map = $this->getStaffMapByIps([$ip]);
            $staffInfo = $map[$ip] ?? null;
        }

        if ($isOnline) {
            $msg = "ส่งคำสั่งสั่งตรวจสอบสเปกใหม่ไปยังเครื่อง {$name} ({$ip}) เรียบร้อยแล้ว ระบบบนเครื่องลูกจะรันสแกน CPU, RAM, DISK, OS, Office และส่งข้อมูลกลับมาอัปเดตอัตโนมัติ";
        } else {
            $msg = "ส่งคำสั่งตรวจสอบสเปกเครื่อง {$name} แล้ว (ขณะนี้เครื่องอาจอยู่ในสถานะออฟไลน์หรืออยู่นอกเครือข่าย ระบบจะตรวจจับเมื่อเครื่องเปิดทำงาน)";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'             => 'success',
                'is_online'          => $isOnline,
                'remote_ready'       => (bool) $installation->remote_ready,
                'has_risk_software'  => (bool) $installation->has_risk_software,
                'risk_software_list' => $installation->risk_software_list,
                'pushed_lan'         => $pushed,
                'message'            => $msg,
                'installation'       => $installation,
                'staff_name'         => $staffInfo ? ($staffInfo->staff_name ?: $staffInfo->kskloginname) : null,
                'last_active'        => $installation->last_active_at ? $installation->last_active_at->format('d/m/y H:i') : '-',
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Real-time risk software check and active rules re-evaluation for a single device.
     */
    public function resyncRisk(Request $request, $id)
    {
        $installation = AppInstallation::findOrFail($id);
        $name = $installation->hostname ?: ($installation->device_name ?: $installation->ip_address);
        $ip = $installation->ip_address;

        // 1. Re-sanitize against currently active rules immediately
        $cleanRisk = \App\Models\RiskSoftwareRule::sanitizeRiskString($installation->risk_software_list);
        $installation->has_risk_software = $cleanRisk['has_risk'];
        $installation->risk_software_list = $cleanRisk['list'];

        // 2. Probe connectivity
        $isOnline = false;
        if (in_array($ip, ['127.0.0.1', '::1']) || $ip === gethostbyname(gethostname())) {
            $isOnline = true;
        } elseif ($ip) {
            $fp = @fsockopen($ip, 5900, $errno, $errstr, 0.6);
            if ($fp) {
                $isOnline = true;
                $installation->remote_ready = true;
                fclose($fp);
            } else {
                foreach ([445, 135] as $port) {
                    $p = @fsockopen($ip, $port, $errno, $errstr, 0.5);
                    if ($p) {
                        $isOnline = true;
                        fclose($p);
                        break;
                    }
                }
            }
            if (!$isOnline) {
                $pingCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
                    ? "ping -n 1 -w 600 " . escapeshellarg($ip)
                    : "ping -c 1 -W 1 " . escapeshellarg($ip);
                @exec($pingCmd, $pingOut, $pingCode);
                if ($pingCode === 0) {
                    $isOnline = true;
                }
            }
        }

        $installation->is_online = $isOnline;
        $installation->last_probed_at = now();
        if ($isOnline) {
            $installation->last_active_at = now();
            $installation->pending_update = true;
            $installation->update_requested_at = now();
        }
        $installation->save();

        $statusMsg = $installation->has_risk_software 
            ? "ตรวจพบโปรแกรมเสี่ยงตามกฎปัจจุบัน: {$installation->risk_software_list}"
            : "ปลอดภัย ไม่พบโปรแกรมเสี่ยงตามกฎปัจจุบัน";

        return response()->json([
            'success'            => true,
            'is_online'          => $isOnline,
            'has_risk_software'  => (bool) $installation->has_risk_software,
            'risk_software_list' => $installation->risk_software_list,
            'message'            => "ตรวจสอบโปรแกรมเสี่ยงเครื่อง {$name} เรียบร้อยแล้ว: {$statusMsg}",
            'installation'       => $installation,
        ]);
    }

    /**
     * Trigger bulk specification rescan across all online client machines.
     */
    public function resyncAllSpecs(Request $request)
    {
        $installations = AppInstallation::whereNotNull('ip_address')
            ->whereNotIn('ip_address', ['127.0.0.1', '::1'])
            ->get();

        $triggered = 0;
        $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        $serverVersion = $this->getClientAppVersion();
        foreach ($installations as $inst) {
            $ip = $inst->ip_address;
            if ($inst->client_version !== $serverVersion || empty($inst->uptime_text)) {
                $inst->pending_update = true;
                $inst->save();
            }

            if ($isWin && $ip) {
                try {
                    $escapedIp = escapeshellarg($ip);
                    $cmd = "start /b schtasks /run /s {$escapedIp} /tn CSHOS-AutoSync-Startup >nul 2>&1 & start /b schtasks /run /s {$escapedIp} /tn CSHOS-AutoSync-Hourly >nul 2>&1";
                    pclose(popen($cmd, "r"));
                    $triggered++;
                } catch (\Throwable $e) {}
            }
        }

        $msg = "ส่งคำสั่งตรวจสอบสเปกเครื่องใหม่ไปยังเครื่องลูกข่ายทั้งหมด ({$triggered} เครื่อง) เรียบร้อยแล้ว เครื่องที่เปิดอยู่จะรันตรวจสอบสเปกและส่งผลกลับมาที่หน้า Admin ทันที";

        if ($request->wantsJson()) {
            return response()->json([
                'status'    => 'success',
                'triggered' => $triggered,
                'message'   => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Bulk check live online/offline status for displayed devices.
     */
    public function checkLiveStatus(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['status' => 'success', 'results' => []]);
        }

        $items = AppInstallation::whereIn('id', $ids)->get();
        $results = [];

        foreach ($items as $item) {
            $ip = $item->ip_address;
            $isOnline = false;
            $vncReady = false;

            if ($ip && !in_array($ip, ['127.0.0.1', '::1'])) {
                // 1. Probe port 5900 (VNC) specifically
                $fpVnc = @fsockopen($ip, 5900, $errno, $errstr, 0.6);
                if ($fpVnc) {
                    $isOnline = true;
                    $vncReady = true;
                    fclose($fpVnc);
                } else {
                    // 2. If 5900 not responding, probe 445 (SMB) / 135 (RPC)
                    foreach ([445, 135] as $port) {
                        $fp = @fsockopen($ip, $port, $errno, $errstr, 0.4);
                        if ($fp) {
                            $isOnline = true;
                            fclose($fp);
                            break;
                        }
                    }

                    // 3. Fallback to ping
                    if (!$isOnline && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        exec("ping -n 1 -w 500 " . escapeshellarg($ip), $out, $code);
                        if ($code === 0) {
                            $isOnline = true;
                        }
                    }
                }
            }

            $item->is_online = $isOnline;
            $item->last_probed_at = now();
            if ($isOnline) {
                $item->last_active_at = now();
                if ($vncReady) {
                    $item->remote_ready = true;
                }
            } else {
                $item->remote_ready = false;
            }
            $item->save();

            $results[$item->id] = [
                'is_online'    => $isOnline,
                'remote_ready' => (bool) $item->remote_ready,
                'last_active'  => $item->last_active_at ? $item->last_active_at->diffForHumans() : '-',
            ];
        }

        return response()->json([
            'status'  => 'success',
            'results' => $results,
        ]);
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
                'รหัสครุภัณฑ์ (COM)',
                'ชื่อเจ้าหน้าที่ (CSHOS / Staff Name)',
                'แผนก/หน่วยงาน',
                'ชื่อเครื่อง/เจ้าของ',
                'ระบบปฏิบัติการ (OS)',
                'เวอร์ชั่นระบบปฏิบัติการ (OS Version)',
                'เวอร์ชั่น Microsoft Office',
                'เบราว์เซอร์',
                'CPU (หน่วยประมวลผล)',
                'RAM (หน่วยความจำ)',
                'DISK (พื้นที่จัดเก็บข้อมูล)',
                'เวลาเปิดเครื่องล่าสุด',
                'เปิดต่อเนื่องมาแล้ว',
                'AnyDesk',
                'โปรแกรมเสี่ยงที่ตรวจพบ (Risk Software)',
                'Remote VNC (Port 5900)',
                'Remote (RDP)',
                'ประเภท',
                'จำนวนครั้งที่เปิดใช้งาน',
                'ติดตั้งครั้งแรกเมื่อ',
                'ใช้งานล่าสุดเมื่อ',
                'หมายเหตุ',
            ]);

            // Pre-fetch staff map for all active IPs
            $allIps = AppInstallation::whereNotNull('ip_address')->pluck('ip_address')->filter()->unique()->toArray();
            $staffMap = $this->getStaffMapByIps($allIps);

            // Stream chunked data
            AppInstallation::orderBy('last_active_at', 'desc')->chunk(200, function ($records) use ($handle, $staffMap) {
                foreach ($records as $item) {
                    $staffInfo = $staffMap[$item->ip_address] ?? null;
                    $staffName = $staffInfo ? ($staffInfo->staff_name ?: $staffInfo->kskloginname) : '-';
                    $department = $item->department ?: '-';

                    fputcsv($handle, [
                        $item->id,
                        $item->device_id,
                        $item->ip_address,
                        $item->hostname ?? '-',
                        $item->asset_code ?: '-',
                        $staffName,
                        $department,
                        $item->device_name ?? '-',
                        $item->os ?? '-',
                        $item->os_version ?? ($item->os ?? '-'),
                        $item->office_version ?? 'ไม่ได้ติดตั้ง / ไม่พบข้อมูล',
                        $item->browser ?? '-',
                        $item->cpu ?? '-',
                        $item->ram ?? '-',
                        $item->disk ?? '-',
                        $item->last_boot_time ? $item->last_boot_time->format('Y-m-d H:i:s') : '-',
                        $item->dynamic_uptime ?: ($item->uptime_text ?: '-'),
                        $item->anydesk_installed ? ('ติดตั้งแล้ว' . ($item->anydesk_id ? ' (ID: ' . $item->anydesk_id . ')' : '')) : 'ไม่ได้ติดตั้ง',
                        $item->has_risk_software ? $item->risk_software_list : 'ไม่พบ',
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
     * Pull latest git updates and clear caches on the server host.
     */
    public function serverGitPull()
    {
        try {
            $basePath = base_path();
            @shell_exec('git config --global --add safe.directory ' . escapeshellarg($basePath) . ' 2>&1');
            @shell_exec("git config --global --add safe.directory '*' 2>&1");
            
            // Auto-clean any uncommitted local file modifications so git pull is never blocked
            @shell_exec('git -c safe.directory=' . escapeshellarg($basePath) . " -c safe.directory='*' checkout -- resources/views/admin/installations.blade.php routes/web.php app/Http/Controllers/AppInstallationController.php 2>&1");
            @shell_exec('git -c safe.directory=' . escapeshellarg($basePath) . " -c safe.directory='*' stash 2>&1");

            $output = shell_exec('git -c safe.directory=' . escapeshellarg($basePath) . " -c safe.directory='*' pull origin main 2>&1") ?: 'No output';
            
            // Auto-run pending database migrations
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $mE) {
                \Illuminate\Support\Facades\Log::warning('Auto migration during git pull: ' . $mE->getMessage());
            }

            // Synchronize .env CLIENT_APP_VERSION if present
            $envPath = base_path('.env');
            if (file_exists($envPath) && is_writable($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^CLIENT_APP_VERSION=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^CLIENT_APP_VERSION=.*$/m', 'CLIENT_APP_VERSION=' . self::CLIENT_APP_VERSION, $envContent);
                    @file_put_contents($envPath, $envContent);
                }
            }

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            
            $hasError = str_contains($output, 'fatal:') || str_contains($output, 'error:') || str_contains($output, 'Aborting');
            $newVer = $this->getClientAppVersion();
            
            return response()->json([
                'success' => !$hasError,
                'message' => !$hasError 
                    ? "อัปเดตระบบเซิร์ฟเวอร์เรียบร้อยแล้ว (เวอร์ชันปัจจุบัน: v{$newVer})" 
                    : "พบข้อผิดพลาดในการดึงข้อมูล Git: {$output}",
                'output'  => $output,
                'version' => $newVer,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตเซิร์ฟเวอร์: ' . $e->getMessage(),
            ], 500);
        }
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
            $isLanIp = $clientIp && !in_array($clientIp, ['127.0.0.1', '::1']);
            $record = AppInstallation::where('device_id', $deviceId)->first();
            if (!$record && $request->filled('mac_address')) {
                $cleanMac = strtoupper(str_replace([':', ' ', '.'], '-', trim($request->input('mac_address'))));
                $record = AppInstallation::where('mac_address', $cleanMac)->first();
            }
            if (!$record && $request->filled('hostname')) {
                $cleanHost = strtolower(trim(explode('.', $request->input('hostname'))[0]));
                $record = AppInstallation::whereRaw("LOWER(TRIM(SUBSTRING_INDEX(hostname, '.', 1))) = ?", [$cleanHost])->first();
            }
            if (!$record && $isLanIp) {
                $candidate = AppInstallation::where('ip_address', $clientIp)->first();
                if ($candidate && (!$candidate->hostname || ($request->filled('hostname') && strtolower(trim(explode('.', $request->input('hostname'))[0])) === strtolower(trim(explode('.', $candidate->hostname)[0]))))) {
                    $record = $candidate;
                }
            }
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
            $record->client_version = $this->getClientAppVersion();
            $record->pending_update = false;
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
            $this->deduplicateSingleRecord($record);
        }

        return response()->json([
            'success'          => true,
            'is_local'         => $isLocal,
            'shortcut_created' => $shortcutCreated,
            'message'          => $shortcutCreated 
                ? 'ติดตั้งไอคอน CSHOS STAFF บนหน้าจอ Desktop เรียบร้อยแล้ว!' 
                : 'บันทึกสถานะเรียบร้อยแล้ว',
            'output'           => $output
        ]);
    }

    /**
     * Return current client app version and update metadata for auto-updater.
     */
    public function clientVersion(Request $request)
    {
        return response()->json([
            'status'            => 'success',
            'server_version'    => $this->getClientAppVersion(),
            'app_name'          => "CSHOS STAFF",
            'app_url'           => url('/'),
            'icon_url'          => asset('app-icon.ico'),
            'update_script_url' => url('/api/client/update-script'),
            'installer_url'     => url('/download-shortcut-installer'),
            'vnc_password'      => $this->getVncPassword(),
        ]);
    }

    /**
     * Unregister / completely remove device installation record when client runs uninstaller.
     */
    public function trackUninstall(Request $request)
    {
        $deviceId = $request->input('device_id');
        $hostname = $request->input('hostname');
        $ip = $request->header('X-Forwarded-For') 
            ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) 
            : ($request->input('ip_address') ?: $request->ip());

        $deletedCount = 0;

        if ($deviceId) {
            $deletedCount += AppInstallation::where('device_id', $deviceId)->delete();
        }

        if ($deletedCount === 0 && ($hostname || ($ip && !in_array($ip, ['127.0.0.1', '::1'])))) {
            $query = AppInstallation::query();
            if ($hostname && $ip && !in_array($ip, ['127.0.0.1', '::1'])) {
                $query->where('hostname', $hostname)->orWhere('ip_address', $ip);
            } elseif ($hostname) {
                $query->where('hostname', $hostname);
            } elseif ($ip && !in_array($ip, ['127.0.0.1', '::1'])) {
                $query->where('ip_address', $ip);
            }
            $deletedCount += $query->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => "ลบข้อมูลการติดตั้งออกจากฐานข้อมูลระบบแล้ว ({$deletedCount} เครื่อง)",
            'deleted' => $deletedCount
        ]);
    }

    /**
     * Return automated PowerShell update script to upgrade client machines to latest version.
     */
    public function clientUpdateScript(Request $request)
    {
        $appUrl = $this->getEffectiveAppUrl($request);
        $appName = "CSHOS STAFF";
        $iconUrl = $this->getEffectiveUrl('/app-icon.ico', $request);
        $serverVersion = $this->getClientAppVersion();
        $vncPassword = $this->getVncPassword();
        $vncHash = $this->getVncPasswordHash();
        $syncCode = $this->getClientSyncScriptTemplate($appUrl, $appName);

        $updateTemplate = <<<'POWERSHELL'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

# Enable TLS 1.2/1.1
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
} catch {}

$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) { $sysDir = "$env:LOCALAPPDATA\CSHOS" }
if (-not (Test-Path $sysDir)) {
    try { New-Item -ItemType Directory -Path $sysDir -Force | Out-Null } catch {}
}

# Grant full modify permissions to all users so standard non-admin users can update scripts and version
try {
    & icacls.exe "$sysDir" /grant "*S-1-5-32-545:(OI)(CI)M" /grant "Users:(OI)(CI)M" /grant "Authenticated Users:(OI)(CI)M" /q /c | Out-Null
} catch {}

# Wait 1.5 seconds for any previous cshos_sync.ps1 process to exit and release file locks
Start-Sleep -Milliseconds 1500

$appUrl = '{{APP_URL}}'
$appName = "{{APP_NAME}}"
$iconUrl = '{{ICON_URL}}'
$serverVer = '{{SERVER_VERSION}}'
$vncPass = '{{VNC_PASSWORD}}'
$vncHash = '{{VNC_HASH}}'

# 1. Update Application Icon
$icoPath = Join-Path $sysDir "app-icon.ico"
try {
    Invoke-WebRequest -Uri $iconUrl -OutFile $icoPath -UseBasicParsing -TimeoutSec 10 -ErrorAction SilentlyContinue
} catch {}

# Locate Chrome or Edge
$browserExe = ""
$browserCandidates = @(
    'C:\Program Files\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe",
    'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
    'C:\Program Files\Microsoft\Edge\Application\msedge.exe'
)
foreach ($cand in $browserCandidates) {
    if (Test-Path $cand) { $browserExe = $cand; break }
}

$ws = New-Object -ComObject WScript.Shell

# Remove old/legacy shortcut names so they don't remain as duplicates
$commonDesktop = [Environment]::GetFolderPath('CommonDesktopDirectory')
if (-not $commonDesktop -or -not (Test-Path $commonDesktop)) { $commonDesktop = "C:\Users\Public\Desktop" }
$commonStartup = [Environment]::GetFolderPath('CommonStartup')
if (-not $commonStartup -or -not (Test-Path $commonStartup)) { $commonStartup = "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\Startup" }

$legacyShortcuts = @("CSHOS STAFF INFO.lnk", "CSHOS_STAFF_INFO.lnk", "CSHOS.lnk", "CSHOS DATACENTER.lnk")
foreach ($oldSc in $legacyShortcuts) {
    if (Test-Path (Join-Path $commonDesktop $oldSc)) { try { Remove-Item -Path (Join-Path $commonDesktop $oldSc) -Force -ErrorAction SilentlyContinue } catch {} }
    if (Test-Path (Join-Path $commonStartup $oldSc)) { try { Remove-Item -Path (Join-Path $commonStartup $oldSc) -Force -ErrorAction SilentlyContinue } catch {} }
}

# 2. Update Desktop Shortcut on Public Desktop
if (Test-Path $commonDesktop) {
    try {
        $scDesk = $ws.CreateShortcut((Join-Path $commonDesktop "$appName.lnk"))
        if ($browserExe) {
            $scDesk.TargetPath = $browserExe
            $scDesk.Arguments = "--app=`"$appUrl`""
        } else {
            $scDesk.TargetPath = $appUrl
        }
        $scDesk.WorkingDirectory = $sysDir
        $scDesk.Description = "$appName - โรงพยาบาลเชียงแสน (v$serverVer)"
        if (Test-Path $icoPath) { $scDesk.IconLocation = "$icoPath,0" }
        $scDesk.Save()
    } catch {}
}

# 3. Update / Ensure Auto-Start Shortcut in Windows Startup
$commonStartup = [Environment]::GetFolderPath('CommonStartup')
if (-not $commonStartup -or -not (Test-Path $commonStartup)) { $commonStartup = "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\Startup" }
if (Test-Path $commonStartup) {
    try {
        $scStart = $ws.CreateShortcut((Join-Path $commonStartup "$appName.lnk"))
        if ($browserExe) {
            $scStart.TargetPath = $browserExe
            $scStart.Arguments = "--app=`"$appUrl`""
        } else {
            $scStart.TargetPath = $appUrl
        }
        $scStart.WorkingDirectory = $sysDir
        $scStart.Description = "$appName - เปิดโปรแกรมอัตโนมัติเมื่อเปิดเครื่อง (Auto-Start on Boot)"
        if (Test-Path $icoPath) { $scStart.IconLocation = "$icoPath,0" }
        $scStart.Save()
    } catch {}
}

# 4. Refresh TightVNC Configuration, Ensure Installed, Firewall & Running (Fix WSAECONNREFUSED)
try {
    $tvnSvc = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if (-not $tvnSvc) {
        # Check if tvnserver.exe exists on disk to register service
        $tvnExe = "C:\Program Files\TightVNC\tvnserver.exe"
        if (-not (Test-Path $tvnExe)) { $tvnExe = "C:\Program Files (x86)\TightVNC\tvnserver.exe" }
        if (Test-Path $tvnExe) {
            & "$tvnExe" -install -silent 2>&1 | Out-Null
        } else {
            # Download & install MSI package
            $is64 = [Environment]::Is64BitOperatingSystem
            $msiFile = if ($is64) { "tightvnc-64bit.msi" } else { "tightvnc-32bit.msi" }
            $msiLocal = Join-Path $sysDir $msiFile
            if (-not (Test-Path $msiLocal)) {
                try { Invoke-WebRequest -Uri "$appUrl/downloads/$msiFile" -OutFile $msiLocal -UseBasicParsing -TimeoutSec 25 -ErrorAction SilentlyContinue } catch {}
            }
            if (Test-Path $msiLocal) {
                $msiArgs = "/i `"$msiLocal`" /quiet /norestart ADDLOCAL=Server SERVER_REGISTER_AS_SERVICE=1 SERVER_ADD_FIREWALL_EXCEPTION=1 SET_USEVNCAUTHENTICATION=1 VALUE_OF_USEVNCAUTHENTICATION=1 SET_PASSWORD=1 VALUE_OF_PASSWORD=`"$vncPass`""
                Start-Process msiexec.exe -ArgumentList $msiArgs -Wait -NoNewWindow -ErrorAction SilentlyContinue
            }
        }
    }

    reg add "HKLM\SOFTWARE\TightVNC\Server" /v Password /t REG_BINARY /d $vncHash /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v UseVncAuthentication /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v AcceptRfbConnections /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v AllowLoopback /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v RfbPort /t REG_DWORD /d 5900 /f | Out-Null
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v Password /t REG_BINARY /d $vncHash /f | Out-Null
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v UseVncAuthentication /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v AcceptRfbConnections /t REG_DWORD /d 1 /f | Out-Null

    # Ensure Windows Firewall rule allows port 5900 across all profiles (Domain, Private, Public)
    netsh advfirewall firewall add rule name="TightVNC Server Port 5900" dir=in action=allow protocol=TCP localport=5900 profile=any | Out-Null

    try {
        Set-Service -Name "tvnserver" -StartupType Automatic -ErrorAction SilentlyContinue
        Stop-Service -Name "tvnserver" -Force -ErrorAction SilentlyContinue
        Start-Sleep -Milliseconds 800
        Start-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    } catch {}
    $passFile = Join-Path $sysDir "vnc_password.txt"
    if (Test-Path $passFile) { Remove-Item $passFile -Force -ErrorAction SilentlyContinue }
    $vncLauncherVbs = Join-Path $sysDir "vnc-launcher.vbs"
    $vbsCode = @'
Set args = WScript.Arguments
If args.Count = 0 Then WScript.Quit
rawUrl = Replace(Replace(args(0), "vnc://", "", 1, -1, 1), "/", "")
host = ""
port = "5900"
targetPart = rawUrl
If InStr(targetPart, ":") > 0 Then
    hParts = Split(targetPart, ":")
    host = hParts(0)
    port = hParts(1)
Else
    host = targetPart
End If
Set fso = CreateObject("Scripting.FileSystemObject")
vncExe = "C:\Program Files\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then vncExe = "C:\Program Files (x86)\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then
    MsgBox "TightVNC Viewer is not installed.", 16, "CSHOS VNC"
    WScript.Quit
End If
Set shell = CreateObject("WScript.Shell")
cmd = """" & vncExe & """ -host=" & host & " -port=" & port
shell.Run cmd, 1, False
'@
    Set-Content -Path $vncLauncherVbs -Value $vbsCode -Encoding ASCII -Force
} catch {}

# 5. Update cshos_sync.ps1 to latest sync code
$syncScriptPath = Join-Path $sysDir "cshos_sync.ps1"
try {
    # Ensure any lingering sync process is terminated before overwriting
    Get-CimInstance Win32_Process -Filter "CommandLine LIKE '%cshos_sync%'" -ErrorAction SilentlyContinue | ForEach-Object {
        Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
    }
    Start-Sleep -Milliseconds 500

    $syncCode = @'
{{SYNC_CODE}}
'@
    # Write to temporary file and test parse syntax before replacing working script
    $newSyncPath = Join-Path $sysDir "cshos_sync_new.ps1"
    Set-Content -Path $newSyncPath -Value $syncCode -Encoding UTF8 -Force

    $parseErr = $null
    [System.Management.Automation.Language.Parser]::ParseFile($newSyncPath, [ref]$null, [ref]$parseErr) | Out-Null
    if (-not $parseErr -or $parseErr.Count -eq 0) {
        if (Test-Path $syncScriptPath) {
            Copy-Item -Path $syncScriptPath -Destination "$syncScriptPath.bak" -Force -ErrorAction SilentlyContinue
        }
        Move-Item -Path $newSyncPath -Destination $syncScriptPath -Force
        Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - New sync script verified (0 syntax errors). Replaced successfully." -ErrorAction SilentlyContinue
        $scriptReplaced = $true
    } else {
        Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - CRITICAL: New sync script has $($parseErr.Count) syntax error(s). Aborted replacement to prevent breakages." -ErrorAction SilentlyContinue
        Remove-Item -Path $newSyncPath -Force -ErrorAction SilentlyContinue
        $scriptReplaced = $false
    }
} catch {
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Failed to overwrite sync script: $($_.Exception.Message)" -ErrorAction SilentlyContinue
    $scriptReplaced = $false
}

# 6. Ensure Windows Scheduled Tasks (Startup with 15s delay, Logon for any user, 5-Minute Periodic, Daily & Watchdog)
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Startup" /tr $taskCmd /sc ONSTART /delay 0000:15 /ru "SYSTEM" /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Logon" /tr $taskCmd /sc ONLOGON /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Periodic" /tr $taskCmd /sc MINUTE /mo {{AUTOSYNC_INTERVAL}} /ru "SYSTEM" /f 2>&1 | Out-Null
    $null | & schtasks.exe /delete /tn "CSHOS-AutoSync-Hourly" /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Daily" /tr $taskCmd /sc DAILY /st 08:30 /ri 60 /du 24:00 /ru "SYSTEM" /f 2>&1 | Out-Null

    # Self-Healing Watchdog (Runs every 2 hours: if cshos_sync.ps1 is missing or corrupted, auto-repairs from server)
    $watchdogCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -Command `"try { `$f = '$syncScriptPath'; `$needFix = `$false; if (-not (Test-Path `$f) -or ((Get-Item `$f).Length -lt 500)) { `$needFix = `$true } else { `$e = `$null; [System.Management.Automation.Language.Parser]::ParseFile(`$f, [ref]`$null, [ref]`$e) | Out-Null; if (`$e -and `$e.Count -gt 0) { `$needFix = `$true } }; if (`$needFix) { (Invoke-RestMethod -Uri '$appUrl/api/client/update-script' -UseBasicParsing -TimeoutSec 20) | Invoke-Expression } } catch {}`""
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Watchdog" /tr $watchdogCmd /sc HOURLY /mo 2 /ru "SYSTEM" /f 2>&1 | Out-Null
} catch {}

# Also update Registry Run keys to run on logon
try {
    $regCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    Set-ItemProperty -Path "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" -Name "CSHOS_AutoSync" -Value $regCmd -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKCU:\Software\Microsoft\Windows\CurrentVersion\Run" -Name "CSHOS_AutoSync" -Value $regCmd -Force -ErrorAction SilentlyContinue
} catch {}

# 7. Update version.txt to the new version if script was replaced
if ($scriptReplaced -ne $false) {
    try {
        Set-Content -Path (Join-Path $sysDir "version.txt") -Value $serverVer -Force
    } catch {}
}

# 8. Trigger immediate background sync using the newly updated script
try {
    Start-Process powershell.exe -ArgumentList "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`"" -WindowStyle Hidden
} catch {}

# Refresh Desktop Icons
try { & ie4uinit.exe -show | Out-Null } catch {}

Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - SUCCESS: Auto-update applied version $serverVer" -ErrorAction SilentlyContinue
Write-Output "SUCCESS: Auto-update applied version $serverVer"

# Self-delete temporary updater script
Start-Sleep -Milliseconds 800
try { Remove-Item -Path $MyInvocation.MyCommand.Path -Force -ErrorAction SilentlyContinue } catch {}
POWERSHELL;

        $autoSyncInterval = $this->getAutoSyncIntervalMinutes();

        $script = str_replace(
            ['{{APP_NAME}}', '{{APP_URL}}', '{{ICON_URL}}', '{{VNC_PASSWORD}}', '{{VNC_HASH}}', '{{SERVER_VERSION}}', '{{SYNC_CODE}}', '{{AUTOSYNC_INTERVAL}}'],
            [$appName, $appUrl, $iconUrl, $vncPassword, $vncHash, $serverVersion, $syncCode, $autoSyncInterval],
            $updateTemplate
        );

        return response($script, 200, [
            'Content-Type'  => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Get PowerShell code template for cshos_sync.ps1 (used in installer and auto-update).
     */
    public function getClientSyncScriptTemplate(string $appUrl = '', string $appName = 'CSHOS STAFF'): string
    {
        $template = <<<'SYNC_SCRIPT'

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

    # 4. Ultimate fallback (any IPv4 not loopback, essential for Virtual Machines & Hypervisors)
    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $detectedIps = @(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { 
                $_.InterfaceAlias -notmatch 'Loopback' -and 
                $_.IPAddress -notmatch '^(169\.254\.|127\.)' 
            } | Select-Object -ExpandProperty IPAddress)
        } catch {}
    }

    if ($detectedIps -and $detectedIps.Count -gt 0) {
        break
    }

    Start-Sleep -Seconds 3
    $waited += 3
}

$clientIp = if ($detectedIps -and $detectedIps.Count -gt 0) { $detectedIps[0] } else { "" }

# Detect Physical Network MAC Address for reliable DHCP identification
$macAddress = ""
try {
    # Method 1: Check adapter linked to current default gateway / active IP
    if ($gwRoutes -and $gwRoutes.Count -gt 0) {
        $primaryAd = Get-NetAdapter -InterfaceIndex $gwRoutes[0].InterfaceIndex -ErrorAction SilentlyContinue
        if ($primaryAd -and $primaryAd.MacAddress) {
            $macAddress = ($primaryAd.MacAddress -replace '[:\.\s]', '-').ToUpper().Trim()
        }
    }
    # Method 2: Check any active physical network adapter with MAC
    if (-not $macAddress) {
        $physAd = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object {
            $_.Status -eq 'Up' -and
            $_.InterfaceDescription -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN' -and
            $_.MacAddress
        } | Select-Object -First 1
        if ($physAd -and $physAd.MacAddress) {
            $macAddress = ($physAd.MacAddress -replace '[:\.\s]', '-').ToUpper().Trim()
        }
    }
    # Method 3: Fallback via CIM/WMI
    if (-not $macAddress) {
        $cimAd = Get-CimInstance Win32_NetworkAdapter -Filter "PhysicalAdapter=True" -ErrorAction SilentlyContinue |
                 Where-Object { $_.MACAddress -and $_.NetConnectionStatus -eq 2 } | Select-Object -First 1
        if ($cimAd -and $cimAd.MACAddress) {
            $macAddress = ($cimAd.MACAddress -replace '[:\.\s]', '-').ToUpper().Trim()
        }
    }
    # Method 4: Fallback for Virtual Machines (any Up adapter with MAC)
    if (-not $macAddress) {
        $anyAd = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object { $_.Status -eq 'Up' -and $_.MacAddress } | Select-Object -First 1
        if ($anyAd -and $anyAd.MacAddress) {
            $macAddress = ($anyAd.MacAddress -replace '[:\.\s]', '-').ToUpper().Trim()
        }
    }
} catch {}

$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) { $sysDir = "$env:LOCALAPPDATA\CSHOS" }
try {
    & icacls.exe "$sysDir" /grant "*S-1-5-32-545:(OI)(CI)M" /grant "Users:(OI)(CI)M" /grant "Authenticated Users:(OI)(CI)M" /q /c | Out-Null
} catch {}

$devIdFile = Join-Path $sysDir "device_id.txt"
$devId = ""
if (Test-Path $devIdFile) {
    try { $devId = (Get-Content $devIdFile -Raw).Trim() } catch {}
}
if (-not $devId) {
    $devId = [guid]::NewGuid().ToString()
    try { Set-Content -Path $devIdFile -Value $devId -Force } catch {}
}

$versionFile = Join-Path $sysDir "version.txt"
$localVersion = "1.0.0"
if (Test-Path $versionFile) {
    try { $localVersion = (Get-Content $versionFile -Raw).Trim() } catch {}
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
    $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    if (-not $memSticks -or $memSticks.Count -eq 0) {
        $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    }

    $totalRamBytes = 0
    if ($memSticks -and $memSticks.Count -gt 0) {
        try {
            $sumVal = ($memSticks | Measure-Object -Property Capacity -Sum).Sum
            if ($sumVal -and $sumVal -gt 0) { $totalRamBytes = [double]$sumVal }
        } catch {}
    }

    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $cs = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
            if (-not $cs) { $cs = Get-WmiObject Win32_ComputerSystem -ErrorAction SilentlyContinue }
            if ($cs -and $cs.TotalPhysicalMemory) { $totalRamBytes = [double]$cs.TotalPhysicalMemory }
        } catch {}
    }

    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            Add-Type -AssemblyName Microsoft.VisualBasic -ErrorAction SilentlyContinue
            $ci = New-Object Microsoft.VisualBasic.Devices.ComputerInfo -ErrorAction SilentlyContinue
            if ($ci -and $ci.TotalPhysicalMemory) { $totalRamBytes = [double]$ci.TotalPhysicalMemory }
        } catch {}
    }

    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
            if (-not $os) { $os = Get-WmiObject Win32_OperatingSystem -ErrorAction SilentlyContinue }
            if ($os -and $os.TotalVisibleMemorySize) { $totalRamBytes = [double]$os.TotalVisibleMemorySize * 1KB }
        } catch {}
    }

    $ramGB = if ($totalRamBytes -gt 0) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "ไม่สามารถระบุ RAM" }

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
                else { "" }
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
            if ($details) { $ramInfo = "$ramGB GB $details" } else { $ramInfo = "$ramGB GB" }
        } elseif ($details) {
            $ramInfo = $details
        }
    }
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
    $diskInfo = if ($diskItems) { $diskItems -join ", " } else { "N/A" }
} catch {
    $diskInfo = "N/A"
}

# Remote VNC Status & Self-Healing
$remoteReady = $false
try {
    $tvn = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if ($tvn) {
        if ($tvn.Status -ne 'Running') {
            Set-Service -Name "tvnserver" -StartupType Automatic -ErrorAction SilentlyContinue
            Start-Service -Name "tvnserver" -ErrorAction SilentlyContinue
            Start-Sleep -Milliseconds 500
            $tvn = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
        }
        if ($tvn -and $tvn.Status -eq 'Running') {
            $remoteReady = $true
        }
    } else {
        # Check if TightVNC is installed in Program Files but service not registered
        $tvnExe = "C:\Program Files\TightVNC\tvnserver.exe"
        if (-not (Test-Path $tvnExe)) { $tvnExe = "C:\Program Files (x86)\TightVNC\tvnserver.exe" }
        if (Test-Path $tvnExe) {
            & "$tvnExe" -install -silent 2>&1 | Out-Null
            Set-Service -Name "tvnserver" -StartupType Automatic -ErrorAction SilentlyContinue
            Start-Service -Name "tvnserver" -ErrorAction SilentlyContinue
            $tvn = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
            if ($tvn -and $tvn.Status -eq 'Running') { $remoteReady = $true }
        }
    }

    # Fallback 1: Check if Port 5900 is actively listening locally (TCP Listen)
    if (-not $remoteReady) {
        try {
            $tcp5900 = Get-NetTCPConnection -LocalPort 5900 -State Listen -ErrorAction SilentlyContinue
            if ($tcp5900) { $remoteReady = $true }
        } catch {}
    }

    # Fallback 2: Check if tvnserver process is active in user session / tray mode
    if (-not $remoteReady) {
        try {
            $procTvn = Get-Process -Name "tvnserver" -ErrorAction SilentlyContinue
            if ($procTvn) { $remoteReady = $true }
        } catch {}
    }

    # Ensure Windows Firewall rule allows Port 5900 for all network profiles
    if ($remoteReady) {
        netsh advfirewall firewall add rule name="TightVNC Server Port 5900" dir=in action=allow protocol=TCP localport=5900 profile=any | Out-Null
    }
} catch {}

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

# Detect OS Last Boot Up Time & System Uptime
$lastBootStr = ""
$uptimeFormatted = ""
try {
    $osCim = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
    if ($osCim -and $osCim.LastBootUpTime) {
        $lastBootStr = $osCim.LastBootUpTime.ToString('yyyy-MM-dd HH:mm:ss')
        $upSpan = (Get-Date) - $osCim.LastBootUpTime
        $d = [math]::Floor($upSpan.TotalDays)
        $h = $upSpan.Hours
        $m = $upSpan.Minutes
        $parts = @()
        if ($d -gt 0) { $parts += "$d วัน" }
        if ($h -gt 0) { $parts += "$h ชม." }
        if ($m -gt 0 -or $parts.Count -eq 0) { $parts += "$m นาที" }
        $uptimeFormatted = $parts -join " "
    }
} catch {}

# Detect AnyDesk Installation & AnyDesk ID (Multi-path & Multi-method Detection)
$anydeskFound = $false
$anydeskIdStr = ""
$anydeskExe = ""
try {
    # 1. Running Process Detection
    $adProc = Get-Process -Name "AnyDesk" -ErrorAction SilentlyContinue
    if ($adProc) {
        $anydeskFound = $true
        foreach ($pr in $adProc) {
            if ($pr.Path -and (Test-Path $pr.Path)) {
                $anydeskExe = $pr.Path
                break
            }
        }
    }

    # 2. Windows Service Detection (AnyDesk Service)
    if (-not $anydeskExe) {
        $adSvc = Get-Service -Name "AnyDesk" -ErrorAction SilentlyContinue
        if ($adSvc) {
            $anydeskFound = $true
            try {
                $cimSvc = Get-CimInstance Win32_Service -Filter "Name='AnyDesk'" -ErrorAction SilentlyContinue
                if ($cimSvc -and $cimSvc.PathName) {
                    $sClean = $cimSvc.PathName -replace '^\s*"([^"]+)".*$', '$1' -replace '^\s*([^\s]+).*$', '$1'
                    if (Test-Path $sClean) { $anydeskExe = $sClean }
                }
            } catch {}
        }
    }

    # 3. Registry App Paths (HKLM 64-bit, HKLM 32-bit WOW64, HKCU)
    if (-not $anydeskExe) {
        $regAppPaths = @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe"
        )
        foreach ($rap in $regAppPaths) {
            $apVal = (Get-ItemProperty $rap -ErrorAction SilentlyContinue).'(Default)'
            if ($apVal -and (Test-Path $apVal)) {
                $anydeskExe = $apVal
                $anydeskFound = $true
                break
            }
        }
    }

    # 4. Registry URL Protocol Handler (anydesk:// command)
    if (-not $anydeskExe) {
        $protoKeys = @(
            "HKLM:\SOFTWARE\Classes\anydesk\shell\open\command",
            "HKCU:\SOFTWARE\Classes\anydesk\shell\open\command"
        )
        foreach ($pk in $protoKeys) {
            $cmdVal = (Get-ItemProperty $pk -ErrorAction SilentlyContinue).'(Default)'
            if ($cmdVal) {
                $m = [regex]::Match($cmdVal, '(?i)"?([a-zA-Z]:\\[^"]+?anydesk\.exe)"?')
                if ($m.Success -and (Test-Path $m.Groups[1].Value)) {
                    $anydeskExe = $m.Groups[1].Value
                    $anydeskFound = $true
                    break
                }
            }
        }
    }

    # 5. Registry Uninstall Entries
    if (-not $anydeskFound -or -not $anydeskExe) {
        $adReg = Get-ItemProperty @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
        ) -ErrorAction SilentlyContinue | Where-Object { $_.DisplayName -match 'AnyDesk' }
        if ($adReg) {
            $anydeskFound = $true
            foreach ($u in $adReg) {
                if ($u.DisplayIcon) {
                    $iconExe = $u.DisplayIcon -replace ',.*$', '' -replace '"', ''
                    if (Test-Path $iconExe) { $anydeskExe = $iconExe; break }
                }
                if ($u.InstallLocation) {
                    $cand = Join-Path $u.InstallLocation "AnyDesk.exe"
                    if (Test-Path $cand) { $anydeskExe = $cand; break }
                }
            }
        }
    }

    # 6. Comprehensive File Path Check across All Drives (C:, D:, etc.)
    if (-not $anydeskExe) {
        $drives = @("C:")
        try {
            $allDrives = @(Get-PSDrive -PSProvider FileSystem -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Root)
            if ($allDrives -and $allDrives.Count -gt 0) { $drives = $allDrives }
        } catch {}

        $candRel = @(
            "Program Files (x86)\AnyDesk\AnyDesk.exe",
            "Program Files\AnyDesk\AnyDesk.exe",
            "AnyDesk\AnyDesk.exe",
            "ProgramData\AnyDesk\AnyDesk.exe"
        )
        foreach ($drv in $drives) {
            $cleanDrv = $drv.TrimEnd('\')
            foreach ($rel in $candRel) {
                $fp = "$cleanDrv\$rel"
                if (Test-Path $fp) {
                    $anydeskExe = $fp
                    $anydeskFound = $true
                    break
                }
            }
            if ($anydeskExe) { break }
        }
    }

    # 7. User AppData, Local Programs, and User Profile Searches
    if (-not $anydeskExe) {
        $userAdPaths = @(
            "$env:ProgramData\AnyDesk\AnyDesk.exe",
            "$env:LOCALAPPDATA\Programs\AnyDesk\AnyDesk.exe",
            "$env:LOCALAPPDATA\AnyDesk\AnyDesk.exe",
            "$env:APPDATA\AnyDesk\AnyDesk.exe"
        )
        foreach ($uap in $userAdPaths) {
            if ($uap -and (Test-Path $uap)) {
                $anydeskExe = $uap
                $anydeskFound = $true
                break
            }
        }
    }
    if (-not $anydeskExe) {
        $foundExes = @(Get-ChildItem -Path "C:\Users\*\AppData\Local\Programs\AnyDesk\AnyDesk.exe", "C:\Users\*\AppData\Roaming\AnyDesk\AnyDesk.exe" -ErrorAction SilentlyContinue)
        if ($foundExes -and $foundExes.Count -gt 0) {
            $anydeskExe = $foundExes[0].FullName
            $anydeskFound = $true
        }
    }

    # 8. Extract AnyDesk ID
    # Method A: Query AnyDesk CLI directly (Fastest, 100% accurate regardless of path)
    if ($anydeskExe -and (Test-Path $anydeskExe)) {
        try {
            $rawCli = (cmd /c "`"$anydeskExe`" --get-id" 2>&1 | Out-String).Trim()
            if ($rawCli -match '^\d{9,10}$') {
                $rawId = $rawCli
                if ($rawId.Length -eq 9) {
                    $anydeskIdStr = $rawId.Substring(0,3) + " " + $rawId.Substring(3,3) + " " + $rawId.Substring(6,3)
                } elseif ($rawId.Length -eq 10) {
                    $anydeskIdStr = $rawId.Substring(0,4) + " " + $rawId.Substring(4,3) + " " + $rawId.Substring(7,3)
                } else {
                    $anydeskIdStr = $rawId
                }
                $anydeskFound = $true
            }
        } catch {}
    }

    # Method B: Search and parse system.conf and user.conf across system and all user profiles
    if (-not $anydeskIdStr) {
        $confFiles = New-Object System.Collections.Generic.List[string]
        $knownConfs = @(
            "$env:ProgramData\AnyDesk\system.conf",
            "$env:ALLUSERSPROFILE\AnyDesk\system.conf",
            "C:\ProgramData\AnyDesk\system.conf",
            "C:\ProgramData\AnyDesk\user.conf",
            "C:\Windows\SysWOW64\config\systemprofile\AppData\Roaming\AnyDesk\system.conf",
            "C:\Windows\System32\config\systemprofile\AppData\Roaming\AnyDesk\system.conf",
            "$env:APPDATA\AnyDesk\user.conf",
            "$env:APPDATA\AnyDesk\system.conf"
        )
        foreach ($kc in $knownConfs) {
            if ($kc -and (Test-Path $kc) -and -not $confFiles.Contains($kc)) {
                $confFiles.Add($kc)
            }
        }

        try {
            $userConfs = @(Get-ChildItem -Path "C:\Users\*\AppData\Roaming\AnyDesk" -Filter "*.conf" -ErrorAction SilentlyContinue)
            foreach ($uc in $userConfs) {
                if (-not $confFiles.Contains($uc.FullName)) {
                    $confFiles.Add($uc.FullName)
                }
            }
        } catch {}

        foreach ($cp in $confFiles) {
            $confText = Get-Content -Path $cp -ErrorAction SilentlyContinue | Out-String
            if ($confText -match 'ad\.anynet\.id\s*=\s*(\d+)') {
                $rawId = $matches[1].Trim()
                if ($rawId.Length -eq 9) {
                    $anydeskIdStr = $rawId.Substring(0,3) + " " + $rawId.Substring(3,3) + " " + $rawId.Substring(6,3)
                } elseif ($rawId.Length -eq 10) {
                    $anydeskIdStr = $rawId.Substring(0,4) + " " + $rawId.Substring(4,3) + " " + $rawId.Substring(7,3)
                } else {
                    $anydeskIdStr = $rawId
                }
                $anydeskFound = $true
                break
            }
        }
    }
} catch {}

# Detect Risky Software (Dynamic Server-Controlled Rules with Offline Fallback)
$riskItems = New-Object System.Collections.Generic.List[string]
$serverRulesLoaded = $false

# 1. Fetch Dynamic Active Rules from Server
try {
    $dynRulesUrl = "{{APP_URL}}/api/client/risk-rules"
    $dynRes = Invoke-RestMethod -Uri $dynRulesUrl -UseBasicParsing -TimeoutSec 8 -ErrorAction Stop
    if ($dynRes -and $dynRes.success -and ($dynRes.rules -ne $null)) {
        $serverRulesLoaded = $true

        # Collect installed software from Registry (HKLM, WOW64, HKCU, and HKEY_USERS for all user profiles)
        $unKeys = @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "Registry::HKEY_USERS\*\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
        )
        $installedApps = @(Get-ItemProperty $unKeys -ErrorAction SilentlyContinue | Where-Object { $_.DisplayName })
        
        $appPathKeys = @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\*",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\App Paths\*",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\*"
        )
        $appPaths = @(Get-ItemProperty $appPathKeys -ErrorAction SilentlyContinue | Where-Object { $_.PSChildName })

        foreach ($r in $dynRes.rules) {
            $rName = "$($r.name)".Trim()
            if (-not $rName -or $riskItems.Contains($rName)) { continue }
            $isFound = $false

            # Check Processes (strip .exe so Get-Process matches accurately)
            if ($r.processes -and $r.processes.Count -gt 0) {
                foreach ($p in $r.processes) {
                    $cleanP = ($p -replace '\.exe$', '').Trim()
                    if ($cleanP -and (Get-Process -Name $cleanP -ErrorAction SilentlyContinue)) {
                        $isFound = $true
                        break
                    }
                }
            }

            # Check Services
            if (-not $isFound -and $r.services -and $r.services.Count -gt 0) {
                foreach ($s in $r.services) {
                    if (Get-Service -Name $s -ErrorAction SilentlyContinue) {
                        $isFound = $true
                        break
                    }
                }
            }

            # Check Registry (both DisplayName and App Paths)
            if (-not $isFound -and $r.registry -and $r.registry.Count -gt 0) {
                foreach ($regPattern in $r.registry) {
                    $esc = [regex]::Escape($regPattern)
                    if ($installedApps | Where-Object { $_.DisplayName -match $esc }) {
                        $isFound = $true
                        break
                    }
                    if ($appPaths | Where-Object { $_.PSChildName -match $esc -or $_.'(Default)' -match $esc }) {
                        $isFound = $true
                        break
                    }
                }
            }

            # Check File / Folder Paths (supports wildcards)
            if (-not $isFound -and $r.paths -and $r.paths.Count -gt 0) {
                foreach ($pth in $r.paths) {
                    if ($pth -and (Test-Path $pth)) {
                        $isFound = $true
                        break
                    }
                }
            }

            if ($isFound) {
                $riskItems.Add($rName)
            }
        }
    }
} catch {
    $serverRulesLoaded = $false
}

# 2. Offline Fallback ONLY (runs only when server is unreachable)
if (-not $serverRulesLoaded) {
    try {
        if ((Get-Process -Name "TeamViewer", "TeamViewer_Service", "tv_w32", "tv_x64" -ErrorAction SilentlyContinue) -or (Get-Service -Name "TeamViewer" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("TeamViewer")
        }
        if ((Get-Process -Name "remoting_host", "remoting_desktop" -ErrorAction SilentlyContinue) -or (Get-Service -Name "chromoting" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("Chrome Remote Desktop")
        }
        if ((Get-Process -Name "UltraViewer", "UltraViewer_Desktop", "UltraViewer_Service" -ErrorAction SilentlyContinue) -or (Get-Service -Name "UltraViewerService" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("UltraViewer")
        }
        if ((Get-Process -Name "rustdesk" -ErrorAction SilentlyContinue) -or (Get-Service -Name "rustdesk" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("RustDesk")
        }
        if (Get-Process -Name "mstsc" -ErrorAction SilentlyContinue) {
            $riskItems.Add("Windows App (RDP)")
        }
    } catch {}
}

$hasRisk = ($riskItems.Count -gt 0)
$riskStr = if ($hasRisk) { $riskItems -join ", " } else { "" }

$reportObj = @{
    device_id          = $devId
    hostname           = $compName
    device_name        = $userName
    ip_address         = $clientIp
    mac_address        = $macAddress
    os                 = $osInfo
    os_version         = $osVerStr
    office_version     = $officeFinal
    cpu                = $cpuInfo
    ram                = $ramInfo
    disk               = $diskInfo
    remote_ready       = $remoteReady
    last_boot_time     = $lastBootStr
    uptime_text        = $uptimeFormatted
    anydesk_installed  = $anydeskFound
    anydesk_id         = $anydeskIdStr
    has_risk_software  = $hasRisk
    risk_software_list = $riskStr
    install_type       = 'installed'
    browser            = 'AutoSync Service'
    client_version     = $localVersion
}
$jsonPayload = $reportObj | ConvertTo-Json

$maxHttpRetries = 6
$res = $null

for ($attempt = 1; $attempt -le $maxHttpRetries; $attempt++) {
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
        $res = Invoke-RestMethod -Uri "{{APP_URL}}/api/track-install" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 15 -ErrorAction Stop
        if ($res) { break }
    } catch {
        if ($attempt -lt $maxHttpRetries) {
            Start-Sleep -Seconds 5
        } else {
            $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - HTTP request failed after $attempt attempts: $($_.Exception.Message)"
            Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
        }
    }
}

if ($res) {
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - OK: Host=$compName, IP=$clientIp, RAM=$ramInfo, DISK=$diskInfo, Ver=$localVersion"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue

    # Auto-Update: Check if server has released a newer version or commanded an update
    $serverVer = if ($res.server_version) { "$($res.server_version)".Trim() } else { "" }
    $isUpdateNeeded = $false
    if ($serverVer -and ($serverVer -ne $localVersion)) { $isUpdateNeeded = $true }
    if ($res.force_update -or $res.update_available) { $isUpdateNeeded = $true }

    if ($isUpdateNeeded) {
        $logUpd = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Server update available: $serverVer (Current: $localVersion, Force=$($res.force_update)). Starting detached auto-update..."
        Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logUpd -ErrorAction SilentlyContinue
        try {
            $updUrl = if ($res.update_script_url) { $res.update_script_url } else { "{{APP_URL}}/api/client/update-script" }
            $updCode = (Invoke-RestMethod -Uri $updUrl -UseBasicParsing -TimeoutSec 20 -ErrorAction Stop)
            if ($updCode -and $updCode.Length -gt 50) {
                $tmpUpd = Join-Path $env:TEMP "cshos_upd_$([guid]::NewGuid().ToString('N')).ps1"
                Set-Content -Path $tmpUpd -Value $updCode -Encoding UTF8 -Force
                Start-Process powershell.exe -ArgumentList "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$tmpUpd`"" -WindowStyle Hidden
                Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Handed off to detached updater ($tmpUpd). Exiting sync process." -ErrorAction SilentlyContinue
                exit 0
            }
        } catch {
            Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Auto-Update download failed: $($_.Exception.Message)" -ErrorAction SilentlyContinue
        }
    }

    # Ensure Auto-Start shortcut in Startup folder is intact
    $commonStartup = [Environment]::GetFolderPath('CommonStartup')
    if (-not $commonStartup -or -not (Test-Path $commonStartup)) { $commonStartup = "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\Startup" }
    $startSc = Join-Path $commonStartup "{{APP_NAME}}.lnk"
    if (-not (Test-Path $startSc)) {
        try {
            $ws = New-Object -ComObject WScript.Shell
            $scStart = $ws.CreateShortcut($startSc)
            $browserCandidates = @('C:\Program Files\Google\Chrome\Application\chrome.exe','C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',"$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe",'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe','C:\Program Files\Microsoft\Edge\Application\msedge.exe')
            $bExe = ""
            foreach ($c in $browserCandidates) { if (Test-Path $c) { $bExe = $c; break } }
            if ($bExe) {
                $scStart.TargetPath = $bExe
                $scStart.Arguments = "--app=`"{{APP_URL}}`""
            } else {
                $scStart.TargetPath = '{{APP_URL}}'
            }
            $scStart.WorkingDirectory = $sysDir
            $scStart.Description = "{{APP_NAME}} - Auto-Start"
            $ico = Join-Path $sysDir "app-icon.ico"
            if (Test-Path $ico) { $scStart.IconLocation = "$ico,0" }
            $scStart.Save()
        } catch {}
    }
} catch {
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Err: $($_.Exception.Message)"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
}
SYNC_SCRIPT;

        if ($appUrl) {
            $template = str_replace(
                ['{{APP_URL}}', '{{APP_NAME}}'],
                [$appUrl, $appName],
                $template
            );
        }

        return $template;
    }

    /**
     * Download .bat 1-click shortcut installer for Windows client machines in LAN.
     */
    public function downloadShortcutInstaller(Request $request)
    {
        $appUrl = $this->getEffectiveAppUrl($request);
        $appName = "CSHOS STAFF";
        $iconUrl = $this->getEffectiveUrl('/app-icon.ico', $request);
        
        $serverVersion = $this->getClientAppVersion();
        $deviceId = $request->query('device_id');
        if ($deviceId) {
            $clientIp = $request->header('X-Forwarded-For') 
                ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) 
                : $request->ip();
                
            $isLanIp = $clientIp && !in_array($clientIp, ['127.0.0.1', '::1']);
            $record = AppInstallation::where('device_id', $deviceId)->first();
            if (!$record && $request->filled('mac_address')) {
                $cleanMac = strtoupper(str_replace([':', ' ', '.'], '-', trim($request->query('mac_address'))));
                $record = AppInstallation::where('mac_address', $cleanMac)->first();
            }
            if (!$record && $isLanIp) {
                $candidate = AppInstallation::where('ip_address', $clientIp)->first();
                if ($candidate && !$candidate->hostname) {
                    $record = $candidate;
                }
            }
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
            $record->client_version = $serverVersion;
            $record->pending_update = false;
            $record->launch_count = ($record->launch_count ?? 0) + 1;
            if ($request->filled('os')) $record->os = $request->query('os');
            if ($request->filled('browser')) $record->browser = $request->query('browser');
            $record->save();
            $this->deduplicateSingleRecord($record);
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
$commonStartup = [Environment]::GetFolderPath('CommonStartup')
if (-not $commonStartup -or -not (Test-Path $commonStartup)) { $commonStartup = "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\Startup" }
$userStartup = [Environment]::GetFolderPath('Startup')

$previousFound = $false
if ((Test-Path "$sysDir\cshos_sync.ps1") -or (Test-Path "$userSysDir\cshos_sync.ps1") -or 
    (Test-Path "$commonDesktop\$shortcutName.lnk") -or ($userDesktop -and (Test-Path "$userDesktop\$shortcutName.lnk")) -or
    (Test-Path "$commonStartup\$shortcutName.lnk") -or ($userStartup -and (Test-Path "$userStartup\$shortcutName.lnk"))) {
    $previousFound = $true
}
if (-not $previousFound) {
    try {
        $null | & schtasks.exe /query /tn "CSHOS-AutoSync-Startup" 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) { $previousFound = $true }
    } catch {}
}

if ($previousFound) {
    Write-Host "       -> Found previous CSHOS version. Removing old components..." -ForegroundColor Cyan

    # 1. Terminate running background sync processes
    try {
        Get-CimInstance Win32_Process -Filter "CommandLine LIKE '%cshos_sync%'" -ErrorAction SilentlyContinue | ForEach-Object {
            Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
        }
        Get-WmiObject Win32_Process -Filter "CommandLine LIKE '%cshos_sync%'" -ErrorAction SilentlyContinue | ForEach-Object {
            Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
        }
    } catch {}

    # 2. Delete old scheduled tasks (safe non-blocking)
    try {
        $null | & schtasks.exe /end /tn "CSHOS-AutoSync-Startup" 2>&1 | Out-Null
        $null | & schtasks.exe /end /tn "CSHOS-AutoSync-Logon" 2>&1 | Out-Null
        $null | & schtasks.exe /end /tn "CSHOS-AutoSync-Daily" 2>&1 | Out-Null
        $null | & schtasks.exe /delete /tn "CSHOS-AutoSync-Startup" /f 2>&1 | Out-Null
        $null | & schtasks.exe /delete /tn "CSHOS-AutoSync-Logon" /f 2>&1 | Out-Null
        $null | & schtasks.exe /delete /tn "CSHOS-AutoSync-Daily" /f 2>&1 | Out-Null
        $null | & schtasks.exe /delete /tn "CSHOS-Startup-Sync" /f 2>&1 | Out-Null
        $null | & schtasks.exe /delete /tn "CSHOS_AutoSync" /f 2>&1 | Out-Null
    } catch {}

    # 3. Delete old registry run keys
    try {
        $null | & reg.exe delete "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /f 2>&1 | Out-Null
        $null | & reg.exe delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /f 2>&1 | Out-Null
    } catch {}

    # 4. Remove all previous desktop and startup shortcuts from all user profiles
    try {
        $legacyNames = @("$shortcutName.lnk", "CSHOS STAFF INFO.lnk", "CSH DIGITAL HEALTH.lnk", "CSHOS.lnk", "CSHOS DATACENTER.lnk", "CSHOS.url")
        foreach ($name in $legacyNames) {
            Remove-Item -Path "$commonDesktop\$name" -Force -ErrorAction SilentlyContinue
            if ($userDesktop) { Remove-Item -Path "$userDesktop\$name" -Force -ErrorAction SilentlyContinue }
            Remove-Item -Path "$env:USERPROFILE\OneDrive\Desktop\$name" -Force -ErrorAction SilentlyContinue
            Remove-Item -Path "C:\Users\Public\Desktop\$name" -Force -ErrorAction SilentlyContinue
            if ($commonStartup) { Remove-Item -Path "$commonStartup\$name" -Force -ErrorAction SilentlyContinue }
            if ($userStartup) { Remove-Item -Path "$userStartup\$name" -Force -ErrorAction SilentlyContinue }
        }
        Get-ChildItem -Path "C:\Users" -Directory -ErrorAction SilentlyContinue | ForEach-Object {
            foreach ($name in $legacyNames) {
                Remove-Item -Path "$($_.FullName)\Desktop\$name" -Force -ErrorAction SilentlyContinue
                Remove-Item -Path "$($_.FullName)\OneDrive\Desktop\$name" -Force -ErrorAction SilentlyContinue
                Remove-Item -Path "$($_.FullName)\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup\$name" -Force -ErrorAction SilentlyContinue
            }
        }
    } catch {}

    # 5. Clean old script files, temporary files, old logs (preserve existing device_id.txt and version.txt)
    try {
        $savedId = ""
        if (Test-Path "$sysDir\device_id.txt") { $savedId = (Get-Content "$sysDir\device_id.txt" -Raw).Trim() }
        elseif (Test-Path "$userSysDir\device_id.txt") { $savedId = (Get-Content "$userSysDir\device_id.txt" -Raw).Trim() }

        Get-ChildItem -Path $sysDir -Exclude @("device_id.txt", "version.txt") -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
        Get-ChildItem -Path $userSysDir -Exclude @("device_id.txt", "version.txt") -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue

        if ($savedId -and (Test-Path $sysDir)) { Set-Content -Path "$sysDir\device_id.txt" -Value $savedId -Force }
    } catch {}

    Write-Host "       -> Previous version uninstalled cleanly! Installing latest version..." -ForegroundColor Green
} else {
    Write-Host "       -> First-time installation detected. Installing latest version..." -ForegroundColor Green
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

# Grant full modify permissions to all users so standard non-admin users can update scripts and version
try {
    & icacls.exe "$sysDir" /grant "*S-1-5-32-545:(OI)(CI)M" /grant "Users:(OI)(CI)M" /grant "Authenticated Users:(OI)(CI)M" /q /c | Out-Null
} catch {}

# --- Record Installed Client Version ---
try {
    Set-Content -Path (Join-Path $sysDir "version.txt") -Value "{{CLIENT_VERSION}}" -Force
} catch {}

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

Write-Host "       -> Desktop shortcut installed successfully (1 icon on Desktop)." -ForegroundColor Green

# --- Setup Auto-Start Shortcut (Open program automatically when computer turns on / logs in) ---
$targetStartup = if (Test-Path $commonStartup) { $commonStartup } elseif ($userStartup -and (Test-Path $userStartup)) { $userStartup } else { "" }
if ($targetStartup) {
    try {
        $startupScPath = Join-Path $targetStartup "$shortcutName.lnk"
        $scStart = $ws.CreateShortcut($startupScPath)
        if ($browserExe) {
            $scStart.TargetPath = $browserExe
            $scStart.Arguments = "--app=`"$appUrl`""
        } else {
            $scStart.TargetPath = $appUrl
        }
        $scStart.WorkingDirectory = $sysDir
        $scStart.Description = "$shortcutName - เปิดโปรแกรมอัตโนมัติเมื่อเปิดเครื่อง (Auto-Start on Boot)"
        if ($iconDownloaded -or ((Test-Path $icoPath) -and ((Get-Item $icoPath).Length -gt 100))) {
            $scStart.IconLocation = "$icoPath,0"
        }
        $scStart.Save()
        Write-Host "       -> Auto-Start configured: Program will launch automatically on boot." -ForegroundColor Green
    } catch {}
}

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
    # 1. Query physical memory sticks (try CIM then WMI)
    $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    if (-not $memSticks -or $memSticks.Count -eq 0) {
        $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    }

    # 2. Total RAM bytes determination (4-layer cascade)
    $totalRamBytes = 0

    # Layer 1: Sum capacity of physical sticks (hardware level, e.g. 16GB instead of APU-shared 13.9GB)
    if ($memSticks -and $memSticks.Count -gt 0) {
        try {
            $sumVal = ($memSticks | Measure-Object -Property Capacity -Sum).Sum
            if ($sumVal -and $sumVal -gt 0) { $totalRamBytes = [double]$sumVal }
        } catch {}
    }

    # Layer 2: Win32_ComputerSystem TotalPhysicalMemory (CIM & WMI)
    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $cs = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
            if (-not $cs) { $cs = Get-WmiObject Win32_ComputerSystem -ErrorAction SilentlyContinue }
            if ($cs -and $cs.TotalPhysicalMemory) { $totalRamBytes = [double]$cs.TotalPhysicalMemory }
        } catch {}
    }

    # Layer 3: Kernel Win32 API via ComputerInfo (Direct OS call, never fails even if WMI/CIM broken)
    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            Add-Type -AssemblyName Microsoft.VisualBasic -ErrorAction SilentlyContinue
            $ci = New-Object Microsoft.VisualBasic.Devices.ComputerInfo -ErrorAction SilentlyContinue
            if ($ci -and $ci.TotalPhysicalMemory) { $totalRamBytes = [double]$ci.TotalPhysicalMemory }
        } catch {}
    }

    # Layer 4: Win32_OperatingSystem VisibleMemory (CIM & WMI)
    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
            if (-not $os) { $os = Get-WmiObject Win32_OperatingSystem -ErrorAction SilentlyContinue }
            if ($os -and $os.TotalVisibleMemorySize) { $totalRamBytes = [double]$os.TotalVisibleMemorySize * 1KB }
        } catch {}
    }

    $ramGB = if ($totalRamBytes -gt 0) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "ไม่สามารถระบุ RAM" }

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
                else { "" }
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
            if ($details) { $ramInfo = "$ramGB GB $details" } else { $ramInfo = "$ramGB GB" }
        } elseif ($details) {
            $ramInfo = $details
        }
    }
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

Write-Host "       -> Host: $compName ($clientIp) | $ramInfo | $officeFinal" -ForegroundColor Green

# --- Install & Configure VNC Server (TightVNC - Screen Share, No Logout) ---
Write-Host " [Step 4/6] Configuring TightVNC Remote service..." -ForegroundColor Yellow
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
{{SYNC_CODE}}
'@

try {
    Set-Content -Path $syncScriptPath -Value $syncCode -Encoding UTF8 -Force
} catch {}

# 1. Register Task Scheduler: Startup & Logon for any user + 5-Minute Periodic, Daily & Watchdog
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Startup" /tr $taskCmd /sc ONSTART /delay 0000:15 /ru "SYSTEM" /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Logon" /tr $taskCmd /sc ONLOGON /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Periodic" /tr $taskCmd /sc MINUTE /mo {{AUTOSYNC_INTERVAL}} /ru "SYSTEM" /f 2>&1 | Out-Null
    $null | & schtasks.exe /delete /tn "CSHOS-AutoSync-Hourly" /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Daily" /tr $taskCmd /sc DAILY /st 08:30 /ri 60 /du 24:00 /ru "SYSTEM" /f 2>&1 | Out-Null

    # Self-Healing Watchdog (Checks every 2 hours; if cshos_sync.ps1 is missing or has syntax errors, auto-repairs from server)
    $watchdogCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -Command `"try { `$f = '$syncScriptPath'; `$needFix = `$false; if (-not (Test-Path `$f) -or ((Get-Item `$f).Length -lt 500)) { `$needFix = `$true } else { `$e = `$null; [System.Management.Automation.Language.Parser]::ParseFile(`$f, [ref]`$null, [ref]`$e) | Out-Null; if (`$e -and `$e.Count -gt 0) { `$needFix = `$true } }; if (`$needFix) { (Invoke-RestMethod -Uri '$appUrl/api/client/update-script' -UseBasicParsing -TimeoutSec 20) | Invoke-Expression } } catch {}`""
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Watchdog" /tr $watchdogCmd /sc HOURLY /mo 2 /ru "SYSTEM" /f 2>&1 | Out-Null
} catch {}

# Write initial version.txt
try {
    Set-Content -Path (Join-Path $sysDir "version.txt") -Value "{{CLIENT_VERSION}}" -Force
} catch {}

# 2. Register Windows Registry Run Key (Runs reliably on user logon across all Windows versions)
try {
    $regCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    Set-ItemProperty -Path "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" -Name "CSHOS_AutoSync" -Value $regCmd -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKCU:\Software\Microsoft\Windows\CurrentVersion\Run" -Name "CSHOS_AutoSync" -Value $regCmd -Force -ErrorAction SilentlyContinue
} catch {}

# Trigger initial Auto-Sync immediately in background to verify setup
try {
    Start-Process powershell.exe -ArgumentList "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`"" -WindowStyle Hidden
} catch {}

Write-Host "       -> Background Auto-Sync service successfully registered." -ForegroundColor Green

# 3. Register vnc:// URL protocol for TightVNC Viewer (CSHOS Remote Launcher)
try {
    $passFile = Join-Path $sysDir "vnc_password.txt"
    if (Test-Path $passFile) { Remove-Item $passFile -Force -ErrorAction SilentlyContinue }
    $vncLauncherVbs = Join-Path $sysDir "vnc-launcher.vbs"
    $vbsCode = @'
Set args = WScript.Arguments
If args.Count = 0 Then WScript.Quit
rawUrl = Replace(Replace(args(0), "vnc://", "", 1, -1, 1), "/", "")
host = ""
port = "5900"
targetPart = rawUrl
If InStr(targetPart, ":") > 0 Then
    hParts = Split(targetPart, ":")
    host = hParts(0)
    port = hParts(1)
Else
    host = targetPart
End If
Set fso = CreateObject("Scripting.FileSystemObject")
vncExe = "C:\Program Files\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then vncExe = "C:\Program Files (x86)\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then
    MsgBox "TightVNC Viewer is not installed.", 16, "CSHOS VNC"
    WScript.Quit
End If
Set shell = CreateObject("WScript.Shell")
cmd = """" & vncExe & """ -host=" & host & " -port=" & port
shell.Run cmd, 1, False
'@
    Set-Content -Path $vncLauncherVbs -Value $vbsCode -Encoding ASCII -Force

    # Native PowerShell registry configuration (Both HKCU and HKLM, never blocks on stdin)
    $null = New-Item -Path "HKCU:\Software\Classes\vnc\shell\open\command" -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKCU:\Software\Classes\vnc" -Name "(Default)" -Value "URL:VNC Protocol" -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKCU:\Software\Classes\vnc" -Name "URL Protocol" -Value "" -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKCU:\Software\Classes\vnc\shell\open\command" -Name "(Default)" -Value "wscript.exe `"$vncLauncherVbs`" `"%1`"" -Force -ErrorAction SilentlyContinue

    $null = New-Item -Path "HKLM:\SOFTWARE\Classes\vnc\shell\open\command" -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKLM:\SOFTWARE\Classes\vnc" -Name "(Default)" -Value "URL:VNC Protocol" -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKLM:\SOFTWARE\Classes\vnc" -Name "URL Protocol" -Value "" -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKLM:\SOFTWARE\Classes\vnc\shell\open\command" -Name "(Default)" -Value "wscript.exe `"$vncLauncherVbs`" `"%1`"" -Force -ErrorAction SilentlyContinue
} catch {}
Write-Host "       -> Remote vnc:// protocol launcher registered." -ForegroundColor Green


# --- Report Hardware Specs & Remote Status to CSHOS Server ---
Write-Host " [Step 6/6] Synchronizing machine status with CSHOS Server..." -ForegroundColor Yellow

# Detect OS Last Boot Up Time & System Uptime
$lastBootStr = ""
$uptimeFormatted = ""
try {
    $osCim = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
    if ($osCim -and $osCim.LastBootUpTime) {
        $lastBootStr = $osCim.LastBootUpTime.ToString('yyyy-MM-dd HH:mm:ss')
        $upSpan = (Get-Date) - $osCim.LastBootUpTime
        $d = [math]::Floor($upSpan.TotalDays)
        $h = $upSpan.Hours
        $m = $upSpan.Minutes
        $parts = @()
        if ($d -gt 0) { $parts += "$d วัน" }
        if ($h -gt 0) { $parts += "$h ชม." }
        if ($m -gt 0 -or $parts.Count -eq 0) { $parts += "$m นาที" }
        $uptimeFormatted = $parts -join " "
    }
} catch {}

# Detect AnyDesk Installation & AnyDesk ID (Multi-path & Multi-method Detection)
$anydeskFound = $false
$anydeskIdStr = ""
$anydeskExe = ""
try {
    # 1. Running Process Detection
    $adProc = Get-Process -Name "AnyDesk" -ErrorAction SilentlyContinue
    if ($adProc) {
        $anydeskFound = $true
        foreach ($pr in $adProc) {
            if ($pr.Path -and (Test-Path $pr.Path)) {
                $anydeskExe = $pr.Path
                break
            }
        }
    }

    # 2. Windows Service Detection (AnyDesk Service)
    if (-not $anydeskExe) {
        $adSvc = Get-Service -Name "AnyDesk" -ErrorAction SilentlyContinue
        if ($adSvc) {
            $anydeskFound = $true
            try {
                $cimSvc = Get-CimInstance Win32_Service -Filter "Name='AnyDesk'" -ErrorAction SilentlyContinue
                if ($cimSvc -and $cimSvc.PathName) {
                    $sClean = $cimSvc.PathName -replace '^\s*"([^"]+)".*$', '$1' -replace '^\s*([^\s]+).*$', '$1'
                    if (Test-Path $sClean) { $anydeskExe = $sClean }
                }
            } catch {}
        }
    }

    # 3. Registry App Paths (HKLM 64-bit, HKLM 32-bit WOW64, HKCU)
    if (-not $anydeskExe) {
        $regAppPaths = @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe"
        )
        foreach ($rap in $regAppPaths) {
            $apVal = (Get-ItemProperty $rap -ErrorAction SilentlyContinue).'(Default)'
            if ($apVal -and (Test-Path $apVal)) {
                $anydeskExe = $apVal
                $anydeskFound = $true
                break
            }
        }
    }

    # 4. Registry URL Protocol Handler (anydesk:// command)
    if (-not $anydeskExe) {
        $protoKeys = @(
            "HKLM:\SOFTWARE\Classes\anydesk\shell\open\command",
            "HKCU:\SOFTWARE\Classes\anydesk\shell\open\command"
        )
        foreach ($pk in $protoKeys) {
            $cmdVal = (Get-ItemProperty $pk -ErrorAction SilentlyContinue).'(Default)'
            if ($cmdVal) {
                $m = [regex]::Match($cmdVal, '(?i)"?([a-zA-Z]:\\[^"]+?anydesk\.exe)"?')
                if ($m.Success -and (Test-Path $m.Groups[1].Value)) {
                    $anydeskExe = $m.Groups[1].Value
                    $anydeskFound = $true
                    break
                }
            }
        }
    }

    # 5. Registry Uninstall Entries
    if (-not $anydeskFound -or -not $anydeskExe) {
        $adReg = Get-ItemProperty @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
        ) -ErrorAction SilentlyContinue | Where-Object { $_.DisplayName -match 'AnyDesk' }
        if ($adReg) {
            $anydeskFound = $true
            foreach ($u in $adReg) {
                if ($u.DisplayIcon) {
                    $iconExe = $u.DisplayIcon -replace ',.*$', '' -replace '"', ''
                    if (Test-Path $iconExe) { $anydeskExe = $iconExe; break }
                }
                if ($u.InstallLocation) {
                    $cand = Join-Path $u.InstallLocation "AnyDesk.exe"
                    if (Test-Path $cand) { $anydeskExe = $cand; break }
                }
            }
        }
    }

    # 6. Comprehensive File Path Check across All Drives (C:, D:, etc.)
    if (-not $anydeskExe) {
        $drives = @("C:")
        try {
            $allDrives = @(Get-PSDrive -PSProvider FileSystem -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Root)
            if ($allDrives -and $allDrives.Count -gt 0) { $drives = $allDrives }
        } catch {}

        $candRel = @(
            "Program Files (x86)\AnyDesk\AnyDesk.exe",
            "Program Files\AnyDesk\AnyDesk.exe",
            "AnyDesk\AnyDesk.exe",
            "ProgramData\AnyDesk\AnyDesk.exe"
        )
        foreach ($drv in $drives) {
            $cleanDrv = $drv.TrimEnd('\')
            foreach ($rel in $candRel) {
                $fp = "$cleanDrv\$rel"
                if (Test-Path $fp) {
                    $anydeskExe = $fp
                    $anydeskFound = $true
                    break
                }
            }
            if ($anydeskExe) { break }
        }
    }

    # 7. User AppData, Local Programs, and User Profile Searches
    if (-not $anydeskExe) {
        $userAdPaths = @(
            "$env:ProgramData\AnyDesk\AnyDesk.exe",
            "$env:LOCALAPPDATA\Programs\AnyDesk\AnyDesk.exe",
            "$env:LOCALAPPDATA\AnyDesk\AnyDesk.exe",
            "$env:APPDATA\AnyDesk\AnyDesk.exe"
        )
        foreach ($uap in $userAdPaths) {
            if ($uap -and (Test-Path $uap)) {
                $anydeskExe = $uap
                $anydeskFound = $true
                break
            }
        }
    }
    if (-not $anydeskExe) {
        $foundExes = @(Get-ChildItem -Path "C:\Users\*\AppData\Local\Programs\AnyDesk\AnyDesk.exe", "C:\Users\*\AppData\Roaming\AnyDesk\AnyDesk.exe" -ErrorAction SilentlyContinue)
        if ($foundExes -and $foundExes.Count -gt 0) {
            $anydeskExe = $foundExes[0].FullName
            $anydeskFound = $true
        }
    }

    # 8. Extract AnyDesk ID
    # Method A: Query AnyDesk CLI directly (Fastest, 100% accurate regardless of path)
    if ($anydeskExe -and (Test-Path $anydeskExe)) {
        try {
            $rawCli = (cmd /c "`"$anydeskExe`" --get-id" 2>&1 | Out-String).Trim()
            if ($rawCli -match '^\d{9,10}$') {
                $rawId = $rawCli
                if ($rawId.Length -eq 9) {
                    $anydeskIdStr = $rawId.Substring(0,3) + " " + $rawId.Substring(3,3) + " " + $rawId.Substring(6,3)
                } elseif ($rawId.Length -eq 10) {
                    $anydeskIdStr = $rawId.Substring(0,4) + " " + $rawId.Substring(4,3) + " " + $rawId.Substring(7,3)
                } else {
                    $anydeskIdStr = $rawId
                }
                $anydeskFound = $true
            }
        } catch {}
    }

    # Method B: Search and parse system.conf and user.conf across system and all user profiles
    if (-not $anydeskIdStr) {
        $confFiles = New-Object System.Collections.Generic.List[string]
        $knownConfs = @(
            "$env:ProgramData\AnyDesk\system.conf",
            "$env:ALLUSERSPROFILE\AnyDesk\system.conf",
            "C:\ProgramData\AnyDesk\system.conf",
            "C:\ProgramData\AnyDesk\user.conf",
            "C:\Windows\SysWOW64\config\systemprofile\AppData\Roaming\AnyDesk\system.conf",
            "C:\Windows\System32\config\systemprofile\AppData\Roaming\AnyDesk\system.conf",
            "$env:APPDATA\AnyDesk\user.conf",
            "$env:APPDATA\AnyDesk\system.conf"
        )
        foreach ($kc in $knownConfs) {
            if ($kc -and (Test-Path $kc) -and -not $confFiles.Contains($kc)) {
                $confFiles.Add($kc)
            }
        }

        try {
            $userConfs = @(Get-ChildItem -Path "C:\Users\*\AppData\Roaming\AnyDesk" -Filter "*.conf" -ErrorAction SilentlyContinue)
            foreach ($uc in $userConfs) {
                if (-not $confFiles.Contains($uc.FullName)) {
                    $confFiles.Add($uc.FullName)
                }
            }
        } catch {}

        foreach ($cp in $confFiles) {
            $confText = Get-Content -Path $cp -ErrorAction SilentlyContinue | Out-String
            if ($confText -match 'ad\.anynet\.id\s*=\s*(\d+)') {
                $rawId = $matches[1].Trim()
                if ($rawId.Length -eq 9) {
                    $anydeskIdStr = $rawId.Substring(0,3) + " " + $rawId.Substring(3,3) + " " + $rawId.Substring(6,3)
                } elseif ($rawId.Length -eq 10) {
                    $anydeskIdStr = $rawId.Substring(0,4) + " " + $rawId.Substring(4,3) + " " + $rawId.Substring(7,3)
                } else {
                    $anydeskIdStr = $rawId
                }
                $anydeskFound = $true
                break
            }
        }
    }
} catch {}

# Detect Risky Software (Dynamic Server-Controlled Rules with Offline Fallback)
$riskItems = New-Object System.Collections.Generic.List[string]
$serverRulesLoaded = $false

# 1. Fetch Dynamic Active Rules from Server
try {
    $dynRulesUrl = "{{APP_URL}}/api/client/risk-rules"
    $dynRes = Invoke-RestMethod -Uri $dynRulesUrl -UseBasicParsing -TimeoutSec 8 -ErrorAction Stop
    if ($dynRes -and $dynRes.success -and ($dynRes.rules -ne $null)) {
        $serverRulesLoaded = $true
        foreach ($r in $dynRes.rules) {
            $rName = "$($r.name)".Trim()
            if (-not $rName -or $riskItems.Contains($rName)) { continue }
            $isFound = $false

            # Check Processes
            if ($r.processes -and $r.processes.Count -gt 0) {
                foreach ($p in $r.processes) {
                    if (Get-Process -Name $p -ErrorAction SilentlyContinue) {
                        $isFound = $true
                        break
                    }
                }
            }

            # Check Services
            if (-not $isFound -and $r.services -and $r.services.Count -gt 0) {
                foreach ($s in $r.services) {
                    if (Get-Service -Name $s -ErrorAction SilentlyContinue) {
                        $isFound = $true
                        break
                    }
                }
            }

            # Check Registry
            if (-not $isFound -and $r.registry -and $r.registry.Count -gt 0) {
                $unKeys = @(
                    "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
                    "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
                    "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
                )
                $installedApps = @(Get-ItemProperty $unKeys -ErrorAction SilentlyContinue | Where-Object { $_.DisplayName })
                foreach ($regPattern in $r.registry) {
                    if ($installedApps | Where-Object { $_.DisplayName -match [regex]::Escape($regPattern) }) {
                        $isFound = $true
                        break
                    }
                }
            }

            # Check File / Folder Paths
            if (-not $isFound -and $r.paths -and $r.paths.Count -gt 0) {
                foreach ($pth in $r.paths) {
                    if ($pth -and (Test-Path $pth)) {
                        $isFound = $true
                        break
                    }
                }
            }

            if ($isFound) {
                $riskItems.Add($rName)
            }
        }
    }
} catch {
    $serverRulesLoaded = $false
}

# 2. Offline Fallback ONLY (runs only when server is unreachable)
if (-not $serverRulesLoaded) {
    try {
        if ((Get-Process -Name "TeamViewer", "TeamViewer_Service", "tv_w32", "tv_x64" -ErrorAction SilentlyContinue) -or (Get-Service -Name "TeamViewer" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("TeamViewer")
        }
        if ((Get-Process -Name "remoting_host", "remoting_desktop" -ErrorAction SilentlyContinue) -or (Get-Service -Name "chromoting" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("Chrome Remote Desktop")
        }
        if ((Get-Process -Name "UltraViewer", "UltraViewer_Desktop", "UltraViewer_Service" -ErrorAction SilentlyContinue) -or (Get-Service -Name "UltraViewerService" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("UltraViewer")
        }
        if ((Get-Process -Name "rustdesk" -ErrorAction SilentlyContinue) -or (Get-Service -Name "rustdesk" -ErrorAction SilentlyContinue)) {
            $riskItems.Add("RustDesk")
        }
        if (Get-Process -Name "mstsc" -ErrorAction SilentlyContinue) {
            $riskItems.Add("Windows App (RDP)")
        }
    } catch {}
}

$hasRisk = ($riskItems.Count -gt 0)
$riskStr = if ($hasRisk) { $riskItems -join ", " } else { "" }

$reportObj = @{
    device_id          = $devId
    hostname           = $compName
    device_name        = $userName
    ip_address         = $clientIp
    os                 = $osInfo
    os_version         = $osVerStr
    office_version     = $officeFinal
    cpu                = $cpuInfo
    ram                = $ramInfo
    disk               = $diskInfo
    remote_ready       = 1
    last_boot_time     = $lastBootStr
    uptime_text        = $uptimeFormatted
    anydesk_installed  = $anydeskFound
    anydesk_id         = $anydeskIdStr
    has_risk_software  = $hasRisk
    risk_software_list = $riskStr
    install_type       = 'installed'
    browser            = 'Desktop App'
    client_version     = '{{CLIENT_VERSION}}'
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
Write-Host " [4] Auto-Start on Boot & Auto-Update System:" -ForegroundColor Yellow
Write-Host "     * Auto-Start on Boot          : " -NoNewline -ForegroundColor Gray
Write-Host "Enabled (Opens program automatically on Windows startup)" -ForegroundColor Green
Write-Host "     * Client Version & Auto-Update: " -NoNewline -ForegroundColor Gray
Write-Host "v{{CLIENT_VERSION}} (Auto-updates silently when server is updated)" -ForegroundColor Green
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [5] Auto-Sync System on Boot / Restart (Auto-Sync on Reboot):" -ForegroundColor Yellow
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
    "================ เปิดโปรแกรมอัตโนมัติ (Auto-Start) ===============`n" +
    "• สถานะ : เปิดใช้งานแล้ว (Windows Startup)`n" +
    "• ทำงาน : เปิดโปรแกรม CSHOS STAFF ทันทีทุกครั้งที่เปิดเครื่องคอมพิวเตอร์`n`n" +
    "================ ระบบ Auto-Update อัปเดตอัตโนมัติ ===============`n" +
    "• สถานะ : เปิดใช้งานแล้ว (เวอร์ชัน {{CLIENT_VERSION}})`n" +
    "• ทำงาน : ตรวจสอบและอัปเดตโปรแกรมเป็นเวอร์ชันล่าสุดจาก Server อัตโนมัติ`n`n" +
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

        $clientVersion = $this->getClientAppVersion();
        $autoSyncInterval = $this->getAutoSyncIntervalMinutes();
        $syncCode = $this->getClientSyncScriptTemplate($appUrl, $appName);

        $psScript = str_replace(
            ['{{APP_NAME}}', '{{APP_URL}}', '{{ICON_URL}}', '{{VNC_PASSWORD}}', '{{VNC_HASH}}', '{{CLIENT_VERSION}}', '{{SYNC_CODE}}', '{{AUTOSYNC_INTERVAL}}'],
            [$appName, $appUrl, $iconUrl, $vncPassword, $vncHash, $clientVersion, $syncCode, $autoSyncInterval],
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

        $fileName = "Install-CSHOS_v{$serverVersion}.bat";

        return response($batContent, 200, [
            'Content-Type'        => 'application/x-bat',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Download standalone lightweight Auto-Sync enabler for client machines (.bat).
     * Enables automatic spec & IP synchronization on boot/restart without reinstalling the app.
     */
    public function downloadAutoSyncInstaller(Request $request)
    {
        $appName = "CSHOS STAFF";
        $appUrl = $this->getEffectiveAppUrl($request);

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

# Grant full modify permissions to all users so standard non-admin users can update scripts and version
try {
    & icacls.exe "$sysDir" /grant "*S-1-5-32-545:(OI)(CI)M" /grant "Users:(OI)(CI)M" /grant "Authenticated Users:(OI)(CI)M" /q /c | Out-Null
} catch {}

# --- Record Installed Client Version ---
try {
    Set-Content -Path (Join-Path $sysDir "version.txt") -Value "{{CLIENT_VERSION}}" -Force
} catch {}

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
{{SYNC_CODE}}
'@

try {
    Set-Content -Path $syncScriptPath -Value $syncCode -Encoding UTF8 -Force
} catch {}

# 1. Register Task Scheduler: Startup & Logon for any user + 5-Minute Periodic, Daily & Watchdog
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Startup" /tr $taskCmd /sc ONSTART /delay 0000:15 /ru "SYSTEM" /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Logon" /tr $taskCmd /sc ONLOGON /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Periodic" /tr $taskCmd /sc MINUTE /mo {{AUTOSYNC_INTERVAL}} /ru "SYSTEM" /f 2>&1 | Out-Null
    $null | & schtasks.exe /delete /tn "CSHOS-AutoSync-Hourly" /f 2>&1 | Out-Null
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Daily" /tr $taskCmd /sc DAILY /st 08:30 /ri 60 /du 24:00 /ru "SYSTEM" /f 2>&1 | Out-Null

    # Self-Healing Watchdog (Checks every 2 hours; if cshos_sync.ps1 is missing or has syntax errors, auto-repairs from server)
    $watchdogCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -Command `"try { `$f = '$syncScriptPath'; `$needFix = `$false; if (-not (Test-Path `$f) -or ((Get-Item `$f).Length -lt 500)) { `$needFix = `$true } else { `$e = `$null; [System.Management.Automation.Language.Parser]::ParseFile(`$f, [ref]`$null, [ref]`$e) | Out-Null; if (`$e -and `$e.Count -gt 0) { `$needFix = `$true } }; if (`$needFix) { (Invoke-RestMethod -Uri '$appUrl/api/client/update-script' -UseBasicParsing -TimeoutSec 20) | Invoke-Expression } } catch {}`""
    $null | & schtasks.exe /create /tn "CSHOS-AutoSync-Watchdog" /tr $watchdogCmd /sc HOURLY /mo 2 /ru "SYSTEM" /f 2>&1 | Out-Null
} catch {}

# 2. Register Windows Registry Run Key
try {
    $regCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    Set-ItemProperty -Path "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" -Name "CSHOS_AutoSync" -Value $regCmd -Force -ErrorAction SilentlyContinue
    Set-ItemProperty -Path "HKCU:\Software\Microsoft\Windows\CurrentVersion\Run" -Name "CSHOS_AutoSync" -Value $regCmd -Force -ErrorAction SilentlyContinue
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
    # 1. Query physical memory sticks (try CIM then WMI)
    $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    if (-not $memSticks -or $memSticks.Count -eq 0) {
        $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    }

    # 2. Total RAM bytes determination (4-layer cascade)
    $totalRamBytes = 0

    # Layer 1: Sum capacity of physical sticks (hardware level, e.g. 16GB instead of APU-shared 13.9GB)
    if ($memSticks -and $memSticks.Count -gt 0) {
        try {
            $sumVal = ($memSticks | Measure-Object -Property Capacity -Sum).Sum
            if ($sumVal -and $sumVal -gt 0) { $totalRamBytes = [double]$sumVal }
        } catch {}
    }

    # Layer 2: Win32_ComputerSystem TotalPhysicalMemory (CIM & WMI)
    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $cs = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
            if (-not $cs) { $cs = Get-WmiObject Win32_ComputerSystem -ErrorAction SilentlyContinue }
            if ($cs -and $cs.TotalPhysicalMemory) { $totalRamBytes = [double]$cs.TotalPhysicalMemory }
        } catch {}
    }

    # Layer 3: Kernel Win32 API via ComputerInfo (Direct OS call, never fails even if WMI/CIM broken)
    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            Add-Type -AssemblyName Microsoft.VisualBasic -ErrorAction SilentlyContinue
            $ci = New-Object Microsoft.VisualBasic.Devices.ComputerInfo -ErrorAction SilentlyContinue
            if ($ci -and $ci.TotalPhysicalMemory) { $totalRamBytes = [double]$ci.TotalPhysicalMemory }
        } catch {}
    }

    # Layer 4: Win32_OperatingSystem VisibleMemory (CIM & WMI)
    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
            if (-not $os) { $os = Get-WmiObject Win32_OperatingSystem -ErrorAction SilentlyContinue }
            if ($os -and $os.TotalVisibleMemorySize) { $totalRamBytes = [double]$os.TotalVisibleMemorySize * 1KB }
        } catch {}
    }

    $ramGB = if ($totalRamBytes -gt 0) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "-" }

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
                else { "" }
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
            if ($details) { $ramInfo = "$ramGB GB $details" } else { $ramInfo = "$ramGB GB" }
        } elseif ($details) {
            $ramInfo = $details
        }
    }
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

        $clientVersion = $this->getClientAppVersion();
        $autoSyncInterval = $this->getAutoSyncIntervalMinutes();
        $syncCode = $this->getClientSyncScriptTemplate($appUrl, $appName);
        $psScript = str_replace(
            ['{{APP_NAME}}', '{{APP_URL}}', '{{CLIENT_VERSION}}', '{{SYNC_CODE}}', '{{AUTOSYNC_INTERVAL}}'],
            [$appName, $appUrl, $clientVersion, $syncCode, $autoSyncInterval],
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

        $vncContent = "[connection]\r\n"
            . "host={$target}\r\n"
            . "port=5900\r\n"
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
        $appName = "CSHOS STAFF";
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
     * Download Uninstall-CSHOS.bat to completely remove all installation data, shortcuts, Auto-Sync jobs, TightVNC service, and server registration.
     */
    public function downloadUninstaller(Request $request)
    {
        $appUrl = url('/');
        $appName = "CSHOS STAFF";
        $fileName = "Uninstall-CSHOS.bat";

        $psTemplate = <<<'POWERSHELL'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
} catch {}

$ws = New-Object -ComObject WScript.Shell
$shortcutName = "{{APP_NAME}}"
$appUrl = '{{APP_URL}}'

Write-Host ""
Write-Host "========================================================================" -ForegroundColor Red
Write-Host "      CSHOS STAFF - COMPLETE UNINSTALLER & SYSTEM CLEANUP" -ForegroundColor White
Write-Host "========================================================================" -ForegroundColor Red
Write-Host ""

# [Step 1/7] Notifying CSHOS Server to remove device registration from database
Write-Host " [Step 1/7] Notifying CSHOS Server to remove device registration..." -ForegroundColor Yellow
$sysDir = "$env:ProgramData\CSHOS"
$userSysDir = "$env:LOCALAPPDATA\CSHOS"

$devId = ""
if (Test-Path "$sysDir\device_id.txt") {
    try { $devId = (Get-Content "$sysDir\device_id.txt" -Raw).Trim() } catch {}
}
if (-not $devId -and (Test-Path "$userSysDir\device_id.txt")) {
    try { $devId = (Get-Content "$userSysDir\device_id.txt" -Raw).Trim() } catch {}
}

$compName = $env:COMPUTERNAME
$clientIp = ""
try {
    $gwRoutes = @(Get-NetRoute -DestinationPrefix '0.0.0.0/0' -AddressFamily IPv4 -ErrorAction SilentlyContinue | Sort-Object RouteMetric)
    foreach ($route in $gwRoutes) {
        $ipObj = Get-NetIPAddress -InterfaceIndex $route.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                 Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -First 1
        if ($ipObj -and $ipObj.IPAddress) { $clientIp = $ipObj.IPAddress; break }
    }
} catch {}

$serverUnregistered = $false
try {
    $uninstallReport = @{
        device_id  = $devId
        hostname   = $compName
        ip_address = $clientIp
    }
    $jsonPayload = $uninstallReport | ConvertTo-Json
    $res = Invoke-RestMethod -Uri "$appUrl/api/track-uninstall" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 10 -ErrorAction Stop
    if ($res -and $res.status -eq 'success') {
        $serverUnregistered = $true
        Write-Host "       -> Successfully removed device record from CSHOS Server database." -ForegroundColor Green
    }
} catch {
    Write-Host "       -> Server unregister notification skipped (offline or unreachable)." -ForegroundColor Gray
}

# [Step 2/7] Terminating running background sync processes & remote tools
Write-Host " [Step 2/7] Terminating running background sync processes..." -ForegroundColor Yellow
try {
    Get-CimInstance Win32_Process -Filter "CommandLine LIKE '%cshos_sync%' OR CommandLine LIKE '%cshos_upd%' OR CommandLine LIKE '%vnc-launcher%'" -ErrorAction SilentlyContinue | ForEach-Object {
        Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
    }
    Get-WmiObject Win32_Process -Filter "CommandLine LIKE '%cshos_sync%' OR CommandLine LIKE '%cshos_upd%' OR CommandLine LIKE '%vnc-launcher%'" -ErrorAction SilentlyContinue | ForEach-Object {
        Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
    }
} catch {}
Write-Host "       -> Background sync processes terminated." -ForegroundColor Green

# [Step 3/7] Removing Auto-Sync tasks in Windows Task Scheduler
Write-Host " [Step 3/7] Removing Auto-Sync tasks in Windows Task Scheduler..." -ForegroundColor Yellow
$tasks = @(
    "CSHOS-AutoSync-Startup",
    "CSHOS-AutoSync-Logon",
    "CSHOS-AutoSync-Hourly",
    "CSHOS-AutoSync-Daily",
    "CSHOS-Sync"
)
foreach ($t in $tasks) {
    try {
        $null | & schtasks.exe /delete /tn $t /f 2>&1 | Out-Null
    } catch {}
}
try {
    Get-ScheduledTask -TaskName "CSHOS*" -ErrorAction SilentlyContinue | ForEach-Object {
        Unregister-ScheduledTask -TaskName $_.TaskName -Confirm:$false -ErrorAction SilentlyContinue
    }
} catch {}
Write-Host "       -> All CSHOS scheduled tasks removed." -ForegroundColor Green

# [Step 4/7] Cleaning Windows Registry (Run keys & URL Protocol)
Write-Host " [Step 4/7] Cleaning Windows Registry (Run keys & URL Protocol)..." -ForegroundColor Yellow
$runKeys = @(
    "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run",
    "HKCU:\Software\Microsoft\Windows\CurrentVersion\Run",
    "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Run"
)
$valNames = @("CSHOS_AutoSync", "CSHOS", "CSHOS_Sync", "CSHOS STAFF INFO", "CSHOS STAFF")
foreach ($k in $runKeys) {
    foreach ($v in $valNames) {
        try { Remove-ItemProperty -Path $k -Name $v -Force -ErrorAction SilentlyContinue } catch {}
    }
}

try {
    Remove-Item -Path "HKCU:\Software\Classes\vnc" -Recurse -Force -ErrorAction SilentlyContinue
    Remove-Item -Path "HKLM:\SOFTWARE\Classes\vnc" -Recurse -Force -ErrorAction SilentlyContinue
} catch {}
Write-Host "       -> Registry entries cleaned." -ForegroundColor Green

# [Step 5/7] Cleaning TightVNC service & firewall rules
Write-Host " [Step 5/7] Cleaning TightVNC service & firewall rules..." -ForegroundColor Yellow
try {
    Stop-Service -Name "tvnserver" -Force -ErrorAction SilentlyContinue
    Set-Service -Name "tvnserver" -StartupType Disabled -ErrorAction SilentlyContinue
    $null | & sc.exe delete tvnserver 2>&1 | Out-Null
} catch {}

try {
    $null | & netsh.exe advfirewall firewall delete rule name="TightVNC Server Port 5900" 2>&1 | Out-Null
} catch {}

try {
    Remove-Item -Path "HKLM:\SOFTWARE\TightVNC" -Recurse -Force -ErrorAction SilentlyContinue
    Remove-Item -Path "HKLM:\SOFTWARE\WOW6432Node\TightVNC" -Recurse -Force -ErrorAction SilentlyContinue
} catch {}
Write-Host "       -> TightVNC service and firewall rules removed." -ForegroundColor Green

# [Step 6/7] Removing shortcuts across all user profiles & Desktop
Write-Host " [Step 6/7] Removing shortcuts across all user profiles & Desktop..." -ForegroundColor Yellow
$legacyPatterns = @("CSHOS*.lnk", "CSHOS*.url", "CSH DIGITAL HEALTH*.lnk", "CSH DIGITAL HEALTH*.url")

$foldersToCheck = @(
    [Environment]::GetFolderPath('CommonDesktopDirectory'),
    "C:\Users\Public\Desktop",
    [Environment]::GetFolderPath('Desktop'),
    "$env:USERPROFILE\Desktop",
    "$env:USERPROFILE\OneDrive\Desktop",
    [Environment]::GetFolderPath('CommonStartup'),
    "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\Startup",
    [Environment]::GetFolderPath('Startup'),
    "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup",
    "C:\ProgramData\Microsoft\Windows\Start Menu\Programs",
    "$env:APPDATA\Microsoft\Windows\Start Menu\Programs"
)

foreach ($f in $foldersToCheck) {
    if ($f -and (Test-Path $f)) {
        foreach ($pat in $legacyPatterns) {
            Get-ChildItem -Path $f -Filter $pat -File -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue
        }
    }
}

try {
    Get-ChildItem -Path "C:\Users" -Directory -ErrorAction SilentlyContinue | ForEach-Object {
        $uPath = $_.FullName
        $uFolders = @(
            "$uPath\Desktop",
            "$uPath\OneDrive\Desktop",
            "$uPath\AppData\Roaming\Microsoft\Windows\Start Menu\Programs",
            "$uPath\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup"
        )
        foreach ($uf in $uFolders) {
            if (Test-Path $uf) {
                foreach ($pat in $legacyPatterns) {
                    Get-ChildItem -Path $uf -Filter $pat -File -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue
                }
            }
        }
    }
} catch {}
Write-Host "       -> All shortcuts removed from Desktop and Start Menu." -ForegroundColor Green

# [Step 7/7] Removing all CSHOS directories, cache, and system files
Write-Host " [Step 7/7] Removing all CSHOS directories, cache, and system files..." -ForegroundColor Yellow
$dirsToRemove = @(
    "C:\ProgramData\CSHOS",
    "$env:LOCALAPPDATA\CSHOS",
    "$env:APPDATA\CSHOS"
)
foreach ($d in $dirsToRemove) {
    if (Test-Path $d) {
        try { Remove-Item -Path $d -Recurse -Force -ErrorAction SilentlyContinue } catch {}
    }
}

try {
    Get-ChildItem -Path "C:\Users" -Directory -ErrorAction SilentlyContinue | ForEach-Object {
        $uL = Join-Path $_.FullName "AppData\Local\CSHOS"
        $uR = Join-Path $_.FullName "AppData\Roaming\CSHOS"
        if (Test-Path $uL) { Remove-Item -Path $uL -Recurse -Force -ErrorAction SilentlyContinue }
        if (Test-Path $uR) { Remove-Item -Path $uR -Recurse -Force -ErrorAction SilentlyContinue }
    }
} catch {}

try {
    Get-ChildItem -Path "$env:TEMP" -Filter "cshos_*" -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
} catch {}

try { & ie4uinit.exe -show | Out-Null } catch {}

Write-Host "       -> All CSHOS system directories and cache completely deleted." -ForegroundColor Green
Write-Host ""
Write-Host "========================================================================" -ForegroundColor Green
Write-Host "   UNINSTALL COMPLETED: All CSHOS components have been completely removed!" -ForegroundColor White
Write-Host "========================================================================" -ForegroundColor Green
Write-Host ""

$statusServerText = if ($serverUnregistered) { "✓ ลบข้อมูลเครื่องออกจากระบบ Server เรียบร้อยแล้ว" } else { "- ไม่สามารถเชื่อมต่อ Server เพื่อลบข้อมูลเครื่องอัตโนมัติได้" }
$popupMsg = "ถอนการติดตั้งและลบข้อมูลระบบ CSHOS STAFF ทั้งหมดเรียบร้อยแล้ว!`n`n" +
    "• ไอคอนทางลัดทั้งหมด : ลบออกจากหน้าจอ Desktop และ Startup แล้ว`n" +
    "• เซอร์วิส Auto-Sync : ลบงานตั้งเวลาใน Task Scheduler ทั้งหมดแล้ว`n" +
    "• ระบบ Remote VNC    : ปิดและถอนการติดตั้งเซอร์วิสแล้ว`n" +
    "• ไฟล์และโฟลเดอร์ระบบ : ลบ C:\ProgramData\CSHOS และแคชทั้งหมดแล้ว`n" +
    "• ฐานข้อมูล Server     : $statusServerText`n`n" +
    "เครื่องคอมพิวเตอร์นี้กลับสู่สถานะก่อนติดตั้งอย่างสมบูรณ์แบบ"

try {
    $ws.Popup($popupMsg, 8, "{{APP_NAME}} - ถอนการติดตั้งสำเร็จ", 64)
} catch {}
POWERSHELL;

        $psScript = str_replace(
            ['{{APP_NAME}}', '{{APP_URL}}'],
            [$appName, $appUrl],
            $psTemplate
        );

        $psScriptWithBom = "\xEF\xBB\xBF" . $psScript;
        $b64Payload = chunk_split(base64_encode($psScriptWithBom), 64, "\r\n");

        $batContent = "@echo off\r\n"
            . "chcp 65001 >nul\r\n"
            . "cd /d \"%~dp0\"\r\n"
            . "title {$appName} - Complete Uninstall and System Cleanup\r\n"
            . "echo ========================================================================\r\n"
            . "echo    {$appName} - Chiang Saen Hospital\r\n"
            . "echo    Uninstalling and completely removing CSHOS system components...\r\n"
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
            . "echo [1/3] Extracting uninstaller components...\r\n"
            . "set \"CSHOS_TEMP_PS=%TEMP%\\cshos_uninstaller_%RANDOM%.ps1\"\r\n"
            . "certutil -decode \"%~f0\" \"%CSHOS_TEMP_PS%\" >nul 2>&1\r\n"
            . "\r\n"
            . "if exist \"%CSHOS_TEMP_PS%\" goto :HAVE_PAYLOAD\r\n"
            . "\r\n"
            . "echo [INFO] Using fallback extractor...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -Command \"\$f = [System.IO.Path]::GetFullPath('%~f0'); \$l = Get-Content -LiteralPath \$f; \$s = \$false; \$b = ''; foreach (\$x in \$l) { if (\$x -match '^-+END') { break }; if (\$s) { \$b += \$x.Trim() }; if (\$x -match '^-+BEGIN') { \$s = \$true } }; if (\$b) { [IO.File]::WriteAllBytes('%CSHOS_TEMP_PS%', [Convert]::FromBase64String(\$b)) }\"\r\n"
            . "\r\n"
            . ":HAVE_PAYLOAD\r\n"
            . "if not exist \"%CSHOS_TEMP_PS%\" (\r\n"
            . "    echo [ERROR] Failed to extract temporary uninstaller.\r\n"
            . "    echo Please make sure the downloaded file is complete.\r\n"
            . "    echo.\r\n"
            . "    pause\r\n"
            . "    exit /b 1\r\n"
            . ")\r\n"
            . "\r\n"
            . "echo [2/3] Running complete system cleanup...\r\n"
            . "powershell.exe -NoProfile -ExecutionPolicy Bypass -File \"%CSHOS_TEMP_PS%\"\r\n"
            . "set \"PS_EXIT=%ERRORLEVEL%\"\r\n"
            . "del /f /q \"%CSHOS_TEMP_PS%\" >nul 2>&1\r\n"
            . "\r\n"
            . "echo.\r\n"
            . "echo ------------------------------------------------------------------------\r\n"
            . "echo [3/3] Complete cleanup finished! Press any key to exit...\r\n"
            . "pause >nul\r\n"
            . "exit\r\n"
            . "\r\n"
            . "-----BEGIN CERTIFICATE-----\r\n"
            . $b64Payload
            . "-----END CERTIFICATE-----\r\n";

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
            // Method 1: PHP OpenSSL (Fastest & Native, des-ede3-cbc with repeated key == DES CBC)
            try {
                $rawKey = hex2bin('e84ad660c4721ae0');
                $keyTriple = $rawKey . $rawKey . $rawKey;
                $data = str_pad($p8, 8, chr(0));
                $iv = str_repeat(chr(0), 8);
                $cipher = @openssl_encrypt($data, 'des-ede3-cbc', $keyTriple, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
                if ($cipher && strlen($cipher) === 8) {
                    return strtolower(bin2hex($cipher));
                }
            } catch (\Throwable $e) {}

            // Method 2: PowerShell .NET DES
            try {
                $pSafe = addslashes($p8);
                $psCmd = 'powershell.exe -NoProfile -Command "[byte[]]$k=0xe8,0x4a,0xd6,0x60,0xc4,0x72,0x1a,0xe0; [byte[]]$p=[System.Text.Encoding]::ASCII.GetBytes(\'' . $pSafe . '\'.PadRight(8,[char]0).Substring(0,8)); $des=[System.Security.Cryptography.DES]::Create(); $des.Mode=[System.Security.Cryptography.CipherMode]::ECB; $des.Padding=[System.Security.Cryptography.PaddingMode]::None; $des.Key=$k; $enc=$des.CreateEncryptor(); $res=$enc.TransformFinalBlock($p,0,8); [System.BitConverter]::ToString($res).Replace(\'-\',\'\').ToLower()"';
                $out = trim(shell_exec($psCmd) ?: '');
                if (preg_match('/^[a-f0-9]{16}$/i', $out)) {
                    return strtolower($out);
                }
            } catch (\Throwable $e) {}

            return '42eee47285988d91';
        });
    }

    /**
     * Deduplicate records across the entire installations table.
     */
    public function deduplicateRecords(): void
    {
        try {
            // Group by normalized hostname (exclude empty)
            $hostnames = AppInstallation::whereNotNull('hostname')
                ->where('hostname', '!=', '')
                ->selectRaw("LOWER(TRIM(SUBSTRING_INDEX(hostname, '.', 1))) as clean_host")
                ->groupBy('clean_host')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('clean_host');

            foreach ($hostnames as $cleanHost) {
                $group = AppInstallation::whereRaw("LOWER(TRIM(SUBSTRING_INDEX(hostname, '.', 1))) = ?", [$cleanHost])
                    ->orderByDesc('last_active_at')
                    ->get();
                if ($group->count() > 1) {
                    $master = $group->first();
                    foreach ($group as $candidate) {
                        if (!str_starts_with($candidate->device_id, 'csh-') && str_starts_with($master->device_id, 'csh-')) {
                            $master = $candidate;
                        }
                    }
                    $this->deduplicateSingleRecord($master);
                }
            }

            // Group by MAC address (physical hardware)
            $macs = AppInstallation::whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->select('mac_address')
                ->groupBy('mac_address')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('mac_address');

            foreach ($macs as $mac) {
                $group = AppInstallation::where('mac_address', $mac)->orderByDesc('last_active_at')->get();
                if ($group->count() > 1) {
                    $master = $group->first();
                    $this->deduplicateSingleRecord($master);
                }
            }

            // Group by LAN IP address (merge duplicate entries sharing the same LAN IP)
            $duplicateIps = AppInstallation::whereNotNull('ip_address')
                ->where('ip_address', '!=', '')
                ->whereNotIn('ip_address', ['127.0.0.1', '::1'])
                ->select('ip_address')
                ->groupBy('ip_address')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('ip_address');

            foreach ($duplicateIps as $dupIp) {
                $group = AppInstallation::where('ip_address', $dupIp)->orderByDesc('last_active_at')->get();
                if ($group->count() <= 1) continue;

                // Priority for master: prefer genuine hardware record with hostname/specs over anonymous browser csh-...
                $master = $group->first(function ($r) {
                    return !str_starts_with($r->device_id, 'csh-') && !empty($r->hostname);
                }) ?: ($group->first(function ($r) {
                    return !empty($r->hostname) || !empty($r->cpu);
                }) ?: $group->first());

                $this->deduplicateSingleRecord($master);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Deduplication failed: ' . $e->getMessage());
        }
    }

    /**
     * Merge any duplicate records matching master's hostname or LAN IP into master.
     */
    public function deduplicateSingleRecord(AppInstallation $master): void
    {
        if (!$master->id) {
            return;
        }

        try {
            $isLanIp = $master->ip_address && !in_array($master->ip_address, ['127.0.0.1', '::1', '']);
            $cleanHost = $master->hostname ? strtolower(trim(explode('.', $master->hostname)[0])) : null;
            $mac = $master->mac_address ? strtoupper(trim($master->mac_address)) : null;
            $devId = $master->device_id ? trim($master->device_id) : null;

            // Only search if master has permanent hardware identity
            if (!$cleanHost && !$mac && !$devId) {
                return;
            }

            $duplicates = AppInstallation::where('id', '!=', $master->id)
                ->where(function ($q) use ($cleanHost, $mac, $devId, $isLanIp, $master) {
                    $hasCondition = false;
                    if ($devId) {
                        $q->where('device_id', $devId);
                        $hasCondition = true;
                    }
                    if ($mac) {
                        if ($hasCondition) {
                            $q->orWhere('mac_address', $mac);
                        } else {
                            $q->where('mac_address', $mac);
                            $hasCondition = true;
                        }
                    }
                    if ($cleanHost) {
                        if ($hasCondition) {
                            $q->orWhereRaw("LOWER(TRIM(SUBSTRING_INDEX(hostname, '.', 1))) = ?", [$cleanHost]);
                        } else {
                            $q->whereRaw("LOWER(TRIM(SUBSTRING_INDEX(hostname, '.', 1))) = ?", [$cleanHost]);
                            $hasCondition = true;
                        }
                    }
                    // Also match anonymous browser-only entries (empty hostname, empty mac, or csh- browser device_id) on master's LAN IP
                    if ($isLanIp) {
                        $q->orWhere(function ($sub) use ($master) {
                            $sub->where('ip_address', $master->ip_address)
                                ->where(function ($w) {
                                    $w->whereNull('hostname')
                                      ->orWhere('hostname', '')
                                      ->orWhere('device_id', 'like', 'csh-%');
                                });
                        });
                    }
                })->get();

            if ($duplicates->isEmpty()) {
                return;
            }

            $mergedLaunches = (int) ($master->launch_count ?? 1);
            $needsSave = false;

            foreach ($duplicates as $dup) {
                $mergedLaunches += (int) ($dup->launch_count ?? 0);

                // Preserve earliest install date
                if ($dup->first_installed_at && (!$master->first_installed_at || $dup->first_installed_at < $master->first_installed_at)) {
                    $master->first_installed_at = $dup->first_installed_at;
                    $needsSave = true;
                }

                // Preserve latest active date
                if ($dup->last_active_at && (!$master->last_active_at || $dup->last_active_at > $master->last_active_at)) {
                    $master->last_active_at = $dup->last_active_at;
                    $needsSave = true;
                }

                // Fill in hardware/OS specs if master is missing them
                $fieldsToBackfill = ['mac_address', 'cpu', 'ram', 'disk', 'os_version', 'office_version', 'os', 'department', 'notes', 'device_name', 'hostname'];
                foreach ($fieldsToBackfill as $field) {
                    if (empty($master->$field) && !empty($dup->$field)) {
                        $master->$field = $dup->$field;
                        $needsSave = true;
                    }
                }

                // If master had browser csh- ID and dup had desktop GUID, adopt GUID
                if (str_starts_with($master->device_id, 'csh-') && !str_starts_with($dup->device_id, 'csh-')) {
                    $master->device_id = $dup->device_id;
                    $needsSave = true;
                }

                if ($dup->remote_ready && !$master->remote_ready) {
                    $master->remote_ready = true;
                    $needsSave = true;
                }

                if ($dup->install_type === 'installed' && $master->install_type !== 'installed') {
                    $master->install_type = 'installed';
                    $needsSave = true;
                }

                $dup->delete();
            }

            if ($master->launch_count !== $mergedLaunches) {
                $master->launch_count = $mergedLaunches;
                $needsSave = true;
            }

            if ($needsSave) {
                $master->save();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Single deduplication failed: ' . $e->getMessage());
        }
    }
}
