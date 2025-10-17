<?php

namespace App\Enums;

enum SocialProvider: string
{
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case X = 'x';
    case Google = 'google';

    public static function fromService(string $service): self
    {
        return match (strtolower($service)) {
            'facebook' => self::Facebook,
            'instagram' => self::Instagram,
            'x', 'twitter' => self::X,
            'google' => self::Google,
            default => throw new \InvalidArgumentException("Unsupported social provider [{$service}]."),
        };
    }
}
