<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpactExample extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_iso',
        'category',
        'min_usd',
        'max_usd',
        'icon',
        'headline',
        'description',
        'metadata',
    ];

    protected $casts = [
        'min_usd' => 'decimal:2',
        'max_usd' => 'decimal:2',
        'metadata' => 'array',
    ];
}
