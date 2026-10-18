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
    ];

    protected $appends = [
        'dynamic_uptime',
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
}
