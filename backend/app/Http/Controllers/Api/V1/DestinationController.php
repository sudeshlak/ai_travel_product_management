<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NamedResource;
use App\Services\DestinationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DestinationController extends Controller
{
    public function __construct(
        private readonly DestinationService $destinations,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return NamedResource::collection($this->destinations->list());
    }
}
