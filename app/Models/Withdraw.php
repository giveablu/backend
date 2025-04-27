<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'batch_id',
        'batch_status',
        'paypal_id',
        'status'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
