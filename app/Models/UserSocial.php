<?php

namespace App\Models;

use App\Enums\SocialProvider;
use App\Models\SocialVerificationEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSocial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'username',
        'profile_url',
        'avatar_url',
        'account_created_at',
        'followers_count',
        'raw_payload',
        'last_synced_at',
        'is_primary',
    ];

    protected $casts = [
        'provider' => SocialProvider::class,
        'account_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_payload' => 'array',
        'is_primary' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verificationEvents(): HasMany
    {
        return $this->hasMany(SocialVerificationEvent::class);
    }

    public function scopeProvider(Builder $query, SocialProvider $provider): Builder
    {
        return $query->where('provider', $provider->value);
    }

    public function markAsPrimary(): void
    {
        $this->is_primary = true;
        $this->save();
    }
}
