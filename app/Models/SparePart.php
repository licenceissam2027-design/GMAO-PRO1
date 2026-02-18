<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'sector', 'category', 'current_stock', 'minimum_stock', 'unit_price', 'supplier', 'shelf_location',
    ];
}

