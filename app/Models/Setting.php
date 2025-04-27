<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable  = [
        'default_amount',
        'app_version',
        'new_version',
        'app_feature',
    ];
}
