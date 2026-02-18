<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ReportFile extends Model
{
    use HasFactory;

    public const CONTEXT_TYPES = [
        'project' => Project::class,
        'maintenance_request' => MaintenanceRequest::class,
        'maintenance_task' => MaintenanceTask::class,
        'preventive_plan' => PreventivePlan::class,
        'industrial_machine' => IndustrialMachine::class,
        'technical_asset' => TechnicalAsset::class,
        'logistic_asset' => LogisticAsset::class,
        'spare_part' => SparePart::class,
        'expert_mission' => ExpertMission::class,
    ];

    protected $fillable = [
        'title', 'type', 'format', 'report_date', 'context_type', 'context_id', 'context_label', 'sector', 'file_path', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function contextClass(?string $contextType): ?string
    {
        if (empty($contextType)) {
            return null;
        }

        return self::CONTEXT_TYPES[$contextType] ?? null;
    }
}

