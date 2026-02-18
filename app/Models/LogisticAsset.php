<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class LogisticAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'sector', 'type', 'status', 'location', 'next_inspection_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'next_inspection_date' => 'date',
        ];
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'logistic_asset_id');
    }

    public function preventivePlans(): HasMany
    {
        return $this->hasMany(PreventivePlan::class, 'logistic_asset_id');
    }
}

