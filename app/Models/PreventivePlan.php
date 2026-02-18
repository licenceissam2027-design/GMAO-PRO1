<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PreventivePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'sector', 'asset_type', 'asset_reference', 'maintenance_domain', 'failure_mode',
        'industrial_machine_id', 'technical_asset_id', 'logistic_asset_id',
        'frequency', 'interval_value', 'trigger_mode', 'meter_threshold',
        'estimated_duration_minutes', 'skill_level', 'requires_shutdown',
        'next_due_date', 'last_done_date', 'responsible_id', 'is_active',
        'checklist', 'procedure_steps', 'safety_notes', 'spare_parts_list',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_shutdown' => 'boolean',
            'meter_threshold' => 'decimal:2',
            'next_due_date' => 'date',
            'last_done_date' => 'date',
        ];
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function industrialMachine(): BelongsTo
    {
        return $this->belongsTo(IndustrialMachine::class, 'industrial_machine_id');
    }

    public function technicalAsset(): BelongsTo
    {
        return $this->belongsTo(TechnicalAsset::class, 'technical_asset_id');
    }

    public function logisticAsset(): BelongsTo
    {
        return $this->belongsTo(LogisticAsset::class, 'logistic_asset_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'preventive_plan_id');
    }
}

