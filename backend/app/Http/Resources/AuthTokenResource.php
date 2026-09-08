<?php

namespace App\Http\Resources;

use App\DataTransferObjects\AuthSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AuthSession
 */
class AuthTokenResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AuthSession $session */
        $session = $this->resource;

        return [
            'token' => $session->token,
            'user' => (new UserResource($session->user))->resolve(),
        ];
    }
}
