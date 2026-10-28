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
        'hosxp_installed',
        'hosxp_version',
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
        'hosxp_installed' => 'boolean',
        'has_risk_software' => 'boolean',
    ];

    protected $appends = [
        'dynamic_uptime',
        'uptime_seconds',
        'computed_online',
        'short_cpu',
        'short_cpu_cores',
        'cpu_cores_detail',
        'short_cpu_speed',
        'short_ram',
        'short_ram_size',
        'short_ram_type',
        'short_disk',
        'short_os',
        'short_office',
        'performance_level',
    ];

    public function getComputedOnlineAttribute(): bool
    {
        if (array_key_exists('computed_online', $this->attributes)) {
            return (bool) $this->attributes['computed_online'];
        }
        $threshold = (int) (config('app.online_threshold_minutes') ?: env('APP_ONLINE_THRESHOLD_MINUTES', 15));
        return (bool) (
            $this->is_online &&
            $this->last_active_at &&
            $this->last_active_at->diffInMinutes(now()) <= $threshold
        );
    }

    public function getUptimeSecondsAttribute(): int
    {
        if (!$this->last_boot_time || $this->last_boot_time->isFuture()) {
            return 0;
        }

        if ($this->computed_online) {
            return max(0, now()->timestamp - $this->last_boot_time->timestamp);
        }

        if ($this->last_active_at && $this->last_active_at->gt($this->last_boot_time)) {
            return max(0, $this->last_active_at->timestamp - $this->last_boot_time->timestamp);
        }

        return 0;
    }

    public function getDynamicUptimeAttribute(): ?string
    {
        if (!$this->last_boot_time) {
            return $this->uptime_text;
        }

        if ($this->last_boot_time->isFuture()) {
            return $this->uptime_text;
        }

        // หากเครื่องออฟไลน์ ไม่นับเวลา uptime ลอยไปข้างหน้าเรื่อยๆ
        // ให้ยึดตาม uptime_text ล่าสุดที่ตัวเครื่องส่งมา หรือคำนวณจนถึงเวลา active ล่าสุด
        if (!$this->computed_online) {
            if (!empty($this->uptime_text)) {
                return $this->uptime_text;
            }
            if ($this->last_active_at && $this->last_active_at->gt($this->last_boot_time)) {
                $totalSeconds = max(0, $this->last_active_at->timestamp - $this->last_boot_time->timestamp);
            } else {
                return $this->uptime_text;
            }
        } else {
            $totalSeconds = max(0, now()->timestamp - $this->last_boot_time->timestamp);
        }

        // คำนวณวัน, ชม., นาที จากวินาทีทั้งหมดโดยตรง เพื่อป้องกันปัญหา DateInterval->d ที่แสดงเฉพาะเศษวันที่ของเดือน
        $days = intdiv($totalSeconds, 86400);
        $hours = intdiv($totalSeconds % 86400, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);

        $parts = [];
        if ($days > 0) $parts[] = $days . ' วัน';
        if ($hours > 0) $parts[] = $hours . ' ชม.';
        if ($minutes > 0) $parts[] = $minutes . ' นาที';

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

    public function getShortCpuCoresAttribute(): string
    {
        $cpu = $this->cpu ?? '';
        if (preg_match('/(\d+)\s*Cores?,\s*(\d+)\s*Threads?/i', $cpu, $m)) {
            return "{$m[1]}C / {$m[2]}T";
        }
        if (preg_match('/(\d+)\s*Cores?/i', $cpu, $m)) {
            return "{$m[1]}C";
        }
        return '-';
    }

    public function getShortCpuSpeedAttribute(): string
    {
        $cpu = $this->cpu ?? '';
        if (preg_match('/@\s*([0-9\.]+\s*GHz)/i', $cpu, $m)) {
            return preg_replace('/\s*GHz/i', ' GHz', trim($m[1]));
        }
        if (preg_match('/([0-9\.]+\s*GHz)/i', $cpu, $m)) {
            return preg_replace('/\s*GHz/i', ' GHz', trim($m[1]));
        }
        return '-';
    }

    public function getCpuCoresDetailAttribute(): string
    {
        $cpu = $this->cpu ?? '';
        if (preg_match('/(\d+)\s*Cores?,\s*(\d+)\s*Threads?/i', $cpu, $m)) {
            return "{$m[1]} Cores, {$m[2]} Threads";
        }
        return $cpu ?: '-';
    }

    public function getShortRamSizeAttribute(): string
    {
        $ram = $this->ram ?? '';
        if (preg_match('/(\d+(?:\.\d+)?)\s*GB/i', $ram, $m)) {
            return round((float)$m[1]) . ' GB';
        }
        return '-';
    }

    public function getShortRamTypeAttribute(): string
    {
        $ram = $this->ram ?? '';
        if (preg_match('/(DDR[2-5]|LPDDR[45])/i', $ram, $m)) {
            return strtoupper($m[1]);
        }
        return '-';
    }

    public function getShortOfficeAttribute(): string
    {
        $office = $this->office_version;
        if (!$office || stripos($office, 'Not Installed') !== false || stripos($office, 'ไม่ได้ติดตั้ง') !== false) {
            return 'ไม่มี';
        }
        if (stripos($office, '365') !== false) {
            return 'Microsoft 365 Apps';
        }
        if (preg_match('/(?:Microsoft\s+Office|Office)\s*(\d{4})/i', $office, $m)) {
            return "Office {$m[1]}";
        }
        if (stripos($office, 'Office') !== false) {
            return 'Office';
        }
        return $office;
    }

    public function getPerformanceLevelAttribute(): array
    {
        $cpu = $this->cpu ?? '';
        $ram = $this->ram ?? '';
        $disk = $this->disk ?? '';

        // --- 1. CPU (จำนวนคอร์ / ระดับการประมวลผล) ---
        // 5. สูงมาก: 16 Cores ขึ้นไป (Core i9, Ryzen 9)
        // 4. สูง: 8 - 14 Cores (Core i7, Ryzen 7)
        // 3. พอใช้: 6 Cores (Core i5, Ryzen 5)
        // 2. ต่ำ: 4 Cores (Core i3, Ryzen 3)
        // 1. ต่ำมาก: 2 Cores หรือเก่ากว่า (Pentium, Core 2)
        $cores = 0;
        if (preg_match('/\((\d+)\s*Cores?/i', $cpu, $m)) {
            $cores = (int)$m[1];
        } elseif (preg_match('/(\d+)\s*-?Cores?/i', $cpu, $m)) {
            $cores = (int)$m[1];
        }

        $cpuLabel = '';
        if ($cores >= 16 || preg_match('/(i9|Ryzen\s*9)/i', $cpu)) {
            $cpuScore = 5;
            $cpuLabel = '16C+ (Core i9/Ryzen 9)';
        } elseif ($cores >= 8 || preg_match('/(Ryzen\s*7)/i', $cpu)) {
            $cpuScore = 4;
            $cpuLabel = '8-14C (Core i7/Ryzen 7)';
        } elseif ($cores >= 6 || preg_match('/(Ryzen\s*5)/i', $cpu) || (preg_match('/i7/i', $cpu) && $cores >= 6)) {
            $cpuScore = 3;
            $cpuLabel = '6C (Core i5/Ryzen 5)';
        } elseif ($cores >= 4 || preg_match('/(i5|i3|Ryzen\s*3)/i', $cpu)) {
            $cpuScore = 2;
            $cpuLabel = '4C (Core i3/Ryzen 3)';
        } else {
            $cpuScore = 1;
            $cpuLabel = '2C หรือเก่ากว่า';
        }

        // --- 2. RAM (ความจุ / เทคโนโลยี) ---
        // 5. สูงมาก: 64GB+ (DDR5)
        // 4. สูง: 32GB (DDR5 / DDR4 บัสสูง)
        // 3. พอใช้: 16GB (DDR4)
        // 2. ต่ำ: 8GB (DDR4)
        // 1. ต่ำมาก: 4GB - 8GB (DDR3 หรือเก่ากว่า)
        $ramGB = 0;
        if (preg_match('/(\d+(?:\.\d+)?)\s*GB/i', $ram, $m)) {
            $ramGB = (float)$m[1];
        }
        $isDdr5 = stripos($ram, 'DDR5') !== false;
        $isDdr4 = stripos($ram, 'DDR4') !== false;
        $isDdr3 = stripos($ram, 'DDR3') !== false || stripos($ram, 'DDR2') !== false;

        $ramLabel = '';
        if ($ramGB >= 64) {
            $ramScore = 5;
            $ramLabel = '64GB+ (DDR5)';
        } elseif ($ramGB >= 32) {
            $ramScore = 4;
            $ramLabel = '32GB';
        } elseif ($ramGB >= 16) {
            $ramScore = $isDdr3 ? 2 : 3;
            $ramLabel = '16GB (DDR4)';
        } elseif ($ramGB >= 8) {
            if ($isDdr5) {
                $ramScore = 3;
                $ramLabel = '8GB (DDR5)';
            } elseif ($isDdr3) {
                $ramScore = 1;
                $ramLabel = '8GB (DDR3)';
            } else {
                $ramScore = 2;
                $ramLabel = '8GB (DDR4)';
            }
        } else {
            $ramScore = 1;
            $ramLabel = '4GB';
        }

        // --- 3. Disk (ประเภท / เทคโนโลยีความเร็ว) ---
        // 5. สูงมาก: M.2 NVMe PCIe Gen 5 (ความเร็ว 10,000+ MB/s)
        // 4. สูง: M.2 NVMe PCIe Gen 4 (ความเร็ว 5,000 - 7,500 MB/s)
        // 3. พอใช้: M.2 NVMe PCIe Gen 3 (ความเร็ว 2,000 - 3,500 MB/s)
        // 2. ต่ำ: SATA III SSD (2.5 นิ้ว / M.2 SATA) (ความเร็ว ~500 MB/s)
        // 1. ต่ำมาก: HDD (จานหมุนแม่เหล็ก) (ความเร็ว ~100 - 150 MB/s)
        $isNvme = stripos($disk, 'NVMe') !== false;
        $isSsd = stripos($disk, 'SSD') !== false || $isNvme;
        $isHdd = stripos($disk, 'HDD') !== false && !$isNvme && stripos($disk, 'SSD') === false;

        $diskLabel = '';
        if (stripos($disk, 'Gen 5') !== false || stripos($disk, 'Gen5') !== false) {
            $diskScore = 5;
            $diskLabel = 'NVMe Gen 5';
        } elseif (stripos($disk, 'Gen 4') !== false || stripos($disk, 'Gen4') !== false) {
            $diskScore = 4;
            $diskLabel = 'NVMe Gen 4';
        } elseif ($isNvme) {
            if (preg_match('/(1[2-4]\d{3}|5825U)/i', $cpu)) {
                $diskScore = 4;
                $diskLabel = 'M.2 NVMe Gen 4';
            } else {
                $diskScore = 3;
                $diskLabel = 'M.2 NVMe Gen 3';
            }
        } elseif ($isSsd) {
            $diskScore = 2;
            $diskLabel = 'SATA SSD';
        } else {
            $diskScore = 1;
            $diskLabel = 'HDD จานหมุน';
        }

        // Average score
        $avgScore = ($cpuScore + $ramScore + $diskScore) / 3.0;
        $tier = (int)round($avgScore);
        if ($tier < 1) $tier = 1;
        if ($tier > 5) $tier = 5;

        $tierMap = [
            5 => ['key' => 'highest', 'level' => 'สูงมาก', 'class' => 'perf-highest', 'badge' => 'perf-highest', 'icon' => 'rocket_launch', 'color' => '#059669'],
            4 => ['key' => 'high',    'level' => 'สูง',     'class' => 'perf-high',    'badge' => 'perf-high',    'icon' => 'verified',      'color' => '#0284C7'],
            3 => ['key' => 'fair',    'level' => 'พอใช้',   'class' => 'perf-fair',    'badge' => 'perf-fair',    'icon' => 'speed',         'color' => '#0D9488'],
            2 => ['key' => 'low',     'level' => 'ต่ำ',     'class' => 'perf-low',     'badge' => 'perf-low',     'icon' => 'warning',       'color' => '#D97706'],
            1 => ['key' => 'lowest',  'level' => 'ต่ำมาก',  'class' => 'perf-lowest',  'badge' => 'perf-lowest',  'icon' => 'error',         'color' => '#DC2626'],
        ];

        $res = $tierMap[$tier];
        $res['tier_number'] = $tier;
        $res['desc'] = "ระดับ {$tier}. {$res['level']} | CPU: {$cpuLabel}, RAM: {$ramLabel}, Disk: {$diskLabel}";
        $res['scores'] = [
            'cpu' => $cpuScore,
            'ram' => $ramScore,
            'disk' => $diskScore,
            'avg' => round($avgScore, 2),
        ];

        return $res;
    }
}
