<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppInstallation extends Model
{
    protected $fillable = [
        'device_id',
        'ip_address',
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
        'launch_count' => 'integer',
        'remote_ready' => 'boolean',
        'is_online' => 'boolean',
        'pending_update' => 'boolean',
    ];
}
