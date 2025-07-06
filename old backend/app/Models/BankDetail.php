<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_name',
        'account_id_type',
        'paypal_account_id',
        'currency'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
