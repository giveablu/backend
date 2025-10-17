<?php

namespace App\Enums;

enum SocialVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case NeedsReview = 'needs_review';
    case InsufficientData = 'insufficient_data';

    public function isFinal(): bool
    {
        return match ($this) {
            self::Verified, self::NeedsReview => true,
            self::Pending, self::InsufficientData => false,
        };
    }
}
