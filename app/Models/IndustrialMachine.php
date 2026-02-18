<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class IndustrialMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'sector', 'manufacturer', 'model', 'serial_number', 'location', 'status', 'criticality',
        'last_maintenance_at', 'next_maintenance_at',
    ];

    protected function casts(): array
    {
        return [
            'last_maintenance_at' => 'datetime',
            'next_maintenance_at' => 'datetime',
        ];
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'industrial_machine_id');
    }

    public function preventivePlans(): HasMany
    {
        return $this->hasMany(PreventivePlan::class, 'industrial_machine_id');
    }
}

