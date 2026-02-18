<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_name', 'company', 'specialty', 'mission_title', 'start_date', 'end_date', 'status', 'daily_rate', 'notes',
    ];
}

