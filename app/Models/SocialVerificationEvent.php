<?php

namespace App\Models;

use App\Enums\SocialVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialVerificationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_social_id',
        'status',
        'reason',
        'metadata',
    ];

    protected $casts = [
        'status' => SocialVerificationStatus::class,
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function social(): BelongsTo
    {
        return $this->belongsTo(UserSocial::class, 'user_social_id');
    }
}
