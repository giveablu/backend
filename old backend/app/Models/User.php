<?php

namespace App\Models;

use App\Models\MailOtp;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use App\Observers\NewUserRegister;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'phone_verified_at',
        'email_verified_at',
        'device_token',
        'photo',
        'gender',
        'reset_otp',
        'reset_otp_expires_at',
        'reset_token',
        'reset_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_otp',
        'reset_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'reset_otp_expires_at' => 'datetime',
        'reset_token_expires_at' => 'datetime',
    ];

    public function social()
    {
        return $this->hasOne(UserSocial::class);
    }

    public function post()
    {
        return $this->hasOne(Post::class);
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class);
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }

    public function otp()
    {
        return $this->hasMany(Otp::class);
    }

    public function donations(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'donations', 'user_id', 'post_id');
    }

    public function deleteds(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'delete_post', 'user_id', 'post_id');
    }

    public function mailOtp()
    {
        return $this->hasOne(MailOtp::class);
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::lower($value)
        );
    }

    // public static function booted()
    // {
    //     static::observe(NewUserRegister::class);
    // }
}