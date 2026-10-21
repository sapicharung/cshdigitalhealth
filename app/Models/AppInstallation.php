<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppInstallation extends Model
{
    protected $fillable = [
        'device_id',
        'ip_address',
        'mac_address',
        'hostname',
        'asset_code',
        'department',
        'device_name',
        'os',
        'os_version',
        'office_version',
        'browser',
        'cpu',
        'ram',
        'disk',
        'remote_ready',
        'is_online',
        'last_boot_time',
        'uptime_text',
        'anydesk_installed',
        'anydesk_id',
        'has_risk_software',
        'risk_software_list',
        'last_probed_at',
        'user_agent',
        'install_type',
        'client_version',
        'pending_update',
        'update_requested_at',
        'launch_count',
        'first_installed_at',
        'last_active_at',
        'notes',
    ];

    protected $casts = [
        'first_installed_at' => 'datetime',
        'last_active_at' => 'datetime',
        'last_probed_at' => 'datetime',
        'last_boot_time' => 'datetime',
        'launch_count' => 'integer',
        'remote_ready' => 'boolean',
        'is_online' => 'boolean',
        'pending_update' => 'boolean',
        'anydesk_installed' => 'boolean',
        'has_risk_software' => 'boolean',
    ];

    protected $appends = [
        'dynamic_uptime',
        'short_cpu',
        'short_ram',
        'short_disk',
        'short_os',
        'short_office',
    ];

    public function getDynamicUptimeAttribute(): ?string
    {
        if (!$this->last_boot_time) {
            return $this->uptime_text;
        }

        $now = now();
        if ($this->last_boot_time->isFuture()) {
            return $this->uptime_text;
        }

        $diff = $this->last_boot_time->diff($now);
        $parts = [];
        if ($diff->d > 0) $parts[] = $diff->d . ' วัน';
        if ($diff->h > 0) $parts[] = $diff->h . ' ชม.';
        if ($diff->i > 0) $parts[] = $diff->i . ' นาที';

        return !empty($parts) ? implode(' ', $parts) : 'เพิ่งเปิดเครื่อง';
    }

    public function getShortCpuAttribute(): string
    {
        $cpu = $this->cpu;
        if (!$cpu || $cpu === '-') return '-';
        $c = str_ireplace(['(R)', '(TM)'], '', $cpu);
        $c = preg_replace('/\s*\(\d+\s*Cores?,\s*\d+\s*Threads?\)/i', '', $c);
        $c = preg_replace('/with Radeon Graphics/i', '', $c);
        $c = preg_replace('/24-Core Processor/i', '', $c);
        $c = preg_replace('/Processor/i', '', $c);
        $c = preg_replace('/\bCPU\b/i', '', $c);
        $c = preg_replace('/@\s*[\d\.]+\s*GHz/i', '', $c);
        $c = preg_replace('/\d+(st|nd|rd|th)\s+Gen\s+/i', '', $c);
        $c = trim(preg_replace('/\s+/', ' ', $c));
        $c = preg_replace('/^Intel\s+Core\s+/i', 'Core ', $c);
        $c = preg_replace('/^AMD\s+Ryzen\s+/i', 'Ryzen ', $c);
        $c = preg_replace('/^Intel\s+/i', '', $c);
        $c = preg_replace('/^AMD\s+/i', '', $c);
        return trim($c) ?: $cpu;
    }

    public function getShortRamAttribute(): string
    {
        $ram = $this->ram;
        if (!$ram || $ram === '-') return '-';
        if (preg_match('/^(\d+(?:\.\d+)?\s*GB)(?:\s+(DDR\d?))?/i', trim($ram), $m)) {
            $size = $m[1];
            $ddr = !empty($m[2]) ? strtoupper($m[2]) : '';
            return trim("{$size} {$ddr}");
        }
        return $ram;
    }

    public function getShortDiskAttribute(): string
    {
        $disk = $this->disk;
        if (!$disk || $disk === '-') return '-';
        $isNvme = stripos($disk, 'NVMe') !== false;
        $isSsd = stripos($disk, 'SSD') !== false;
        $isHdd = stripos($disk, 'HDD') !== false;
        
        $cSize = '';
        if (preg_match('/C:[^\/]*\/\s*([0-9\.]+)\s*GB/i', $disk, $m)) {
            $gb = round((float) $m[1]);
            if ($gb >= 900) {
                $cSize = round($gb / 1000, 1) . ' TB';
            } else {
                $cSize = $gb . ' GB';
            }
        }
        
        if ($isNvme) {
            $type = 'SSD NVMe';
        } elseif ($isSsd && $isHdd) {
            $type = 'SSD + HDD';
        } elseif ($isSsd) {
            $type = 'SSD';
        } elseif ($isHdd) {
            $type = 'HDD';
        } else {
            $type = 'Disk';
        }
        
        return $cSize ? "{$type} {$cSize}" : $type;
    }

    public function getShortOsAttribute(): string
    {
        $os = $this->os_version ?: $this->os;
        if (!$os || $os === '-') return '-';
        $o = str_ireplace('Microsoft ', '', $os);
        if (preg_match('/(Windows\s+1[01]\s+(?:Pro|Home|Enterprise|Education|Pro\s+N)?)/i', $o, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(Windows\s+Server\s+\d+)/i', $o, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(Windows\s+[0-9\.]+)/i', $o, $m)) {
            return trim($m[1]);
        }
        $o = preg_replace('/\s*\([^)]*\)/', '', $o);
        $o = preg_replace('/\s*\d{2}H\d/i', '', $o);
        return trim($o);
    }

    public function getShortOfficeAttribute(): string
    {
        $office = $this->office_version;
        if (!$office || stripos($office, 'Not Installed') !== false || stripos($office, 'ไม่ได้ติดตั้ง') !== false) {
            return 'ไม่มี';
        }
        if (preg_match('/(?:Microsoft\s+Office|Office)\s*(\d{4}|365)/i', $office, $m)) {
            return "Office {$m[1]}";
        }
        if (stripos($office, 'Office') !== false) {
            return 'Office';
        }
        return $office;
    }
}
