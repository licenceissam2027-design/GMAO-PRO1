<?php

namespace App\Support;

final class GmaoOptions
{
    public const SECTORS = ['production', 'utilities', 'quality', 'it', 'logistics', 'hse', 'administration'];
    public const ASSET_TYPES = ['industrial', 'technical', 'logistic', 'other'];
    public const ISSUE_CATEGORIES = ['breakdown', 'quality', 'safety', 'software', 'electrical', 'mechanical', 'other'];
    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];
    public const MAINTENANCE_STATUSES = ['pending', 'in_progress', 'completed', 'stopped'];
    public const PLAN_FREQUENCIES = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
    public const TASK_TYPES = ['corrective', 'preventive', 'predictive'];
    public const PROJECT_STATUSES = ['planned', 'in_progress', 'completed', 'delayed'];
    public const PROJECT_PHASE_STATUSES = ['planned', 'in_progress', 'completed', 'blocked'];
    public const PROJECT_PHASE_MODES = ['sequential', 'parallel'];
    public const MAINTENANCE_DOMAINS = [
        'electrical', 'mechanical', 'hydraulic', 'pneumatic', 'plc', 'logistic', 'technical', 'climatisation',
        'it_support', 'telephony', 'safety', 'other',
    ];

    public const FAILURE_MODES = [
        'electrical' => ['power_loss', 'short_circuit', 'breaker_trip', 'sensor_fault', 'motor_fault'],
        'mechanical' => ['bearing_failure', 'misalignment', 'belt_damage', 'overheating', 'excessive_vibration'],
        'hydraulic' => ['oil_leak', 'pressure_drop', 'pump_failure', 'valve_blocked', 'seal_damage'],
        'pneumatic' => ['air_leak', 'compressor_fault', 'pressure_instability', 'actuator_failure', 'filter_blocked'],
        'plc' => ['io_fault', 'program_error', 'communication_loss', 'module_fault', 'hmi_fault'],
        'logistic' => ['forklift_failure', 'loading_issue', 'dock_blocked', 'tracking_error', 'inspection_overdue'],
        'technical' => ['instrument_fault', 'calibration_drift', 'network_device_fault', 'ups_alarm', 'printer_fault'],
        'climatisation' => ['no_cooling', 'gas_leak', 'compressor_stop', 'fan_fault', 'thermostat_fault'],
        'it_support' => ['pc_failure', 'server_down', 'software_crash', 'network_outage', 'backup_failure'],
        'telephony' => ['line_down', 'pbx_fault', 'voip_quality', 'phone_device_fault', 'configuration_error'],
        'safety' => ['guard_open', 'alarm_fault', 'fire_system_fault', 'interlock_fault', 'unsafe_condition'],
        'other' => ['other'],
    ];

    public static function allFailureModes(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::FAILURE_MODES))));
    }

    public static function domainLabels(string $locale): array
    {
        $labels = [];
        foreach (self::MAINTENANCE_DOMAINS as $domain) {
            $translated = __('gmao.enum.domain.' . $domain, [], $locale);
            $labels[$domain] = ($translated === 'gmao.enum.domain.' . $domain)
                ? ucwords(str_replace('_', ' ', $domain))
                : $translated;
        }

        return $labels;
    }

    public static function failureLabel(string $mode, string $locale): string
    {
        $translated = __('gmao.enum.failure.' . $mode, [], $locale);
        if ($translated === 'gmao.enum.failure.' . $mode) {
            return ucwords(str_replace('_', ' ', $mode));
        }

        return $translated;
    }
}
