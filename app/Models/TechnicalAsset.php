<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class TechnicalAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'sector', 'category', 'brand', 'model', 'serial_number', 'location', 'status',
    ];

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'technical_asset_id');
    }

    public function preventivePlans(): HasMany
    {
        return $this->hasMany(PreventivePlan::class, 'technical_asset_id');
    }
}

