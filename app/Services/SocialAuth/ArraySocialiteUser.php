<?php

namespace App\Services\SocialAuth;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class ArraySocialiteUser implements SocialiteUser
{
    public array $user;

    public function __construct(
        private string $id,
        private ?string $email = null,
        private ?string $name = null,
        private ?string $nickname = null,
        private ?string $avatar = null,
        array $raw = []
    ) {
        $this->user = $raw;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
}
