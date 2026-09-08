<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
    ) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        // TODO: implement
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        // TODO: implement
    }

    public function show(int $product): ProductResource
    {
        // TODO: implement
    }

    public function update(UpdateProductRequest $request, int $product): ProductResource
    {
        // TODO: implement
    }

    public function destroy(int $product): JsonResponse
    {
        // TODO: implement
    }
}
