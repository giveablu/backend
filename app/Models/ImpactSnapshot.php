<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpactSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_iso',
        'category',
        'min_usd',
        'max_usd',
        'headline',
        'description',
        'icon',
        'local_currency',
        'local_amount',
        'source',
        'metadata',
        'observed_at',
    ];

    protected $casts = [
        'min_usd' => 'decimal:2',
        'max_usd' => 'decimal:2',
        'local_amount' => 'decimal:2',
        'metadata' => 'array',
        'observed_at' => 'datetime',
    ];
}
