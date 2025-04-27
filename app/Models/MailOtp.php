<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'old_mail',
        'old_code',
        'new_mail',
        'new_code',
        'expire_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
