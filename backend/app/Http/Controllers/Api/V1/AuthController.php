<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\LoginCredentials;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function login(LoginRequest $request): AuthTokenResource
    {
        $session = $this->auth->login(new LoginCredentials(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        ));

        return new AuthTokenResource($session);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return response()->json(null, 204);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($this->auth->me($request->user()));
    }
}
