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

    /**
     * Get array of all currently active rule names.
     */
    public static function getActiveRuleNames(): array
    {
        return static::where('is_active', true)
            ->pluck('name')
            ->map(fn($n) => trim($n))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Filter a risk software string so it only contains items from currently active rules.
     *
     * @param string|null $riskString
     * @param array|null $activeNames
     * @return array ['has_risk' => bool, 'list' => string|null]
     */
    public static function sanitizeRiskString(?string $riskString, ?array $activeNames = null): array
    {
        if (empty($riskString)) {
            return ['has_risk' => false, 'list' => null];
        }

        if ($activeNames === null) {
            $activeNames = static::getActiveRuleNames();
        }

        $items = array_filter(array_map('trim', explode(',', $riskString)));
        $filtered = [];

        foreach ($items as $item) {
            if ($item === '') continue;
            $itemLower = mb_strtolower($item);

            $isAllowed = false;
            foreach ($activeNames as $actName) {
                $actLower = mb_strtolower(trim($actName));
                if (empty($actLower)) continue;

                // Match exact or contains (e.g. 'BlueStacks' vs 'BlueStacks X', 'Windows App (RDP)' vs 'Windows App')
                if ($itemLower === $actLower || 
                    str_contains($itemLower, $actLower) || 
                    str_contains($actLower, $itemLower)) {
                    $isAllowed = true;
                    break;
                }
            }

            if ($isAllowed) {
                $filtered[] = $item;
            }
        }

        $filtered = array_values(array_unique($filtered));
        $hasRisk = !empty($filtered);

        return [
            'has_risk' => $hasRisk,
            'list' => $hasRisk ? implode(', ', $filtered) : null,
        ];
    }

    /**
     * Re-sanitize risk_software_list and has_risk_software across all AppInstallation records.
     * Ensures any deactivated or deleted rules are immediately pruned from the database.
     */
    public static function resanitizeAllInstallations(): void
    {
        try {
            $activeNames = static::getActiveRuleNames();
            $records = AppInstallation::where('has_risk_software', true)
                ->orWhereNotNull('risk_software_list')
                ->get();

            foreach ($records as $rec) {
                $clean = static::sanitizeRiskString($rec->risk_software_list, $activeNames);
                if ((bool)$rec->has_risk_software !== (bool)$clean['has_risk'] || $rec->risk_software_list !== $clean['list']) {
                    $rec->has_risk_software = $clean['has_risk'];
                    $rec->risk_software_list = $clean['list'];
                    $rec->save();
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('resanitizeAllInstallations error: ' . $e->getMessage());
        }
    }
}

