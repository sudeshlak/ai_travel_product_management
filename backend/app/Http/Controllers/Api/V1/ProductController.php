<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\ProductSearchCriteria;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
    ) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        $paginator = $this->products->list(new ProductSearchCriteria(
            status: ProductStatus::Active,
            onlyValid: false,
            userId: $request->user()->id,
            page: $request->integer('page', 1),
            perPage: $request->integer('per_page', 15),
        ));

        return ProductResource::collection($paginator);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }

    public function show(int $product): ProductResource
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }

    public function update(UpdateProductRequest $request, int $product): ProductResource
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $this->products->delete($product, $request->user()->id);

        return response()->json(null, 204);
    }
}
