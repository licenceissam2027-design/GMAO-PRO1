<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id', 'preventive_plan_id', 'technician_id', 'sector',
        'title', 'type', 'status', 'scheduled_for', 'generated_for_date', 'reminder_sent_at',
        'completed_at', 'estimated_hours', 'actual_hours', 'notes',
        'did_lubrication', 'did_measurement', 'did_inspection', 'did_replacement', 'did_cleaning',
        'anomaly_detected', 'measurement_reading', 'inspection_location', 'execution_note', 'anomaly_note', 'round_completed_at',
        'execution_checks',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'generated_for_date' => 'date',
            'reminder_sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'round_completed_at' => 'datetime',
            'did_lubrication' => 'boolean',
            'did_measurement' => 'boolean',
            'did_inspection' => 'boolean',
            'did_replacement' => 'boolean',
            'did_cleaning' => 'boolean',
            'anomaly_detected' => 'boolean',
            'execution_checks' => 'array',
        ];
    }

    public function preventivePlan(): BelongsTo
    {
        return $this->belongsTo(PreventivePlan::class);
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}

