<?php

namespace App\DataTransferObjects;

readonly class LoginCredentials
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
