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
}
