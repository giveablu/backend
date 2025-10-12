<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_country',
        'preferred_region',
        'preferred_city',
        'preferred_hardship_ids',
    ];

    protected $casts = [
        'preferred_hardship_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
