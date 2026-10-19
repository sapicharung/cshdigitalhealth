<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskSoftwareRule extends Model
{
    protected $table = 'risk_software_rules';

    protected $fillable = [
        'name',
        'category',
        'process_names',
        'service_names',
        'registry_patterns',
        'file_paths',
        'severity',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get array of process names
     */
    public function getProcessListAttribute(): array
    {
        if (empty($this->process_names)) return [];
        return array_values(array_filter(array_map('trim', explode(',', $this->process_names))));
    }

    /**
     * Get array of service names
     */
    public function getServiceListAttribute(): array
    {
        if (empty($this->service_names)) return [];
        return array_values(array_filter(array_map('trim', explode(',', $this->service_names))));
    }

    /**
     * Get array of registry patterns
     */
    public function getRegistryListAttribute(): array
    {
        if (empty($this->registry_patterns)) return [];
        return array_values(array_filter(array_map('trim', explode(',', $this->registry_patterns))));
    }

    /**
     * Get array of file/folder paths
     */
    public function getPathListAttribute(): array
    {
        if (empty($this->file_paths)) return [];
        // Support both newline and comma
        $normalized = str_replace(["\r\n", "\r"], "\n", $this->file_paths);
        $lines = explode("\n", $normalized);
        $paths = [];
        foreach ($lines as $line) {
            foreach (explode(',', $line) as $p) {
                $p = trim($p);
                if ($p !== '') $paths[] = $p;
            }
        }
        return array_values(array_unique($paths));
    }
}
