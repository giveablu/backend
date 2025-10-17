<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\SocialVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            if (! $user->search_id) {
                $user->forceFill([
                    'search_id' => self::formatSearchId($user->id),
                ]);

                $originalTimestamps = $user->timestamps;
                $user->timestamps = false;
                $user->save();
                $user->timestamps = $originalTimestamps;
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    'phone',
	'role',
	'status',
        'photo',
        'search_id',
        'joined_date',
        'email_verified_at',
        'phone_verified_at',
    'last_login_at',
        'device_token',
        'gender',
        'profile_description',
        'city',
        'region',
        'country',
        'reset_otp',
        'reset_otp_expires_at',
        'reset_token',
        'reset_token_expires_at',
        'social_verification_status',
        'social_verified_at',
        'social_verification_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'reset_otp',
        'reset_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'joined_date' => 'datetime',
    'last_login_at' => 'datetime',
        'password' => 'hashed',
        'reset_otp_expires_at' => 'datetime',
        'reset_token_expires_at' => 'datetime',
        'social_verified_at' => 'datetime',
        'social_verification_status' => SocialVerificationStatus::class,
    ];

    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }

    public function bankDetail(): HasOne
    {
        return $this->hasOne(BankDetail::class);
    }

    public function withdraws(): HasMany
    {
        return $this->hasMany(Withdraw::class);
    }

    public function donations(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'donations', 'user_id', 'post_id')
            ->withTimestamps()
            ->withPivot([
                'gross_amount',
                'processing_fee',
                'platform_fee',
                'net_amount',
                'currency',
                'processor_payload',
                'activity',
            ]);
    }

    public function deleteds(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'delete_post', 'user_id', 'post_id')->withTimestamps();
    }

    public function donorPreference(): HasOne
    {
        return $this->hasOne(DonorPreference::class);
    }

    public function social(): HasOne
    {
        return $this->hasOne(UserSocial::class)->where('is_primary', true);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(UserSocial::class);
    }

    public function socialVerificationEvents(): HasMany
    {
        return $this->hasMany(SocialVerificationEvent::class);
    }

    protected static function formatSearchId(int $id): string
    {
        return sprintf('BLU-%06d', $id);
    }
}
