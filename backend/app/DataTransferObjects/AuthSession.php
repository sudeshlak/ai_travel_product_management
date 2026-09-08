<?php

namespace App\DataTransferObjects;

use App\Models\User;

readonly class AuthSession
{
    public function __construct(
        public string $token,
        public User $user,
    ) {}
}
