<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DataTransferObjects\AuthSession;
use App\DataTransferObjects\LoginCredentials;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function login(LoginCredentials $credentials): AuthSession
    {
        $user = $this->users->findByEmail($credentials->email);

        if ($user === null || ! Hash::check($credentials->password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        $token = $user->createToken('api')->plainTextToken;

        return new AuthSession($token, $user);
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function me(User $user): User
    {
        return $user;
    }
}
