<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code', 'requester_id', 'assigned_to', 'sector', 'asset_type', 'asset_reference', 'industrial_machine_id',
        'technical_asset_id', 'logistic_asset_id',
        'issue_category', 'maintenance_domain', 'failure_mode', 'severity', 'status', 'is_recurrent', 'recurrence_count',
        'location', 'description', 'occurrence_at', 'downtime_minutes', 'requested_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_recurrent' => 'boolean',
            'requested_at' => 'datetime',
            'occurrence_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function machine(): BelongsTo
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

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'maintenance_request_id');
    }
}

