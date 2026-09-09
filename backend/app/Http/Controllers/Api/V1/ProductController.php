<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\ProductData;
use App\DataTransferObjects\ProductSearchCriteria;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\SearchProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductSearchService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly ProductSearchService $search,
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

    public function search(SearchProductRequest $request): AnonymousResourceCollection
    {
        $paginator = $this->search->search(
            query: (string) $request->input('query', ''),
            page: $request->integer('page', 1),
            perPage: $request->integer('per_page', 15),
        );

        return ProductResource::collection($paginator);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create(
            $this->productDataFromRequest($request),
            $request->user()->id,
        );

        return (new ProductResource($product->load(['category', 'destinations'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $product): ProductResource
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }

    public function update(UpdateProductRequest $request, int $product): ProductResource
    {
        $updated = $this->products->update(
            $product,
            $this->productDataFromRequest($request),
            $request->user()->id,
        );

        return new ProductResource($updated->load(['category', 'destinations']));
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $this->products->delete($product, $request->user()->id);

        return response()->json(null, 204);
    }

    private function productDataFromRequest(StoreProductRequest|UpdateProductRequest $request): ProductData
    {
        /** @var list<int> $destinationIds */
        $destinationIds = array_map(
            static fn (mixed $id): int => (int) $id,
            $request->validated('destination_ids'),
        );

        return new ProductData(
            productName: $request->string('product_name')->toString(),
            categoryId: $request->integer('category_id'),
            description: $request->string('description')->toString(),
            price: (float) $request->input('price'),
            inventoryCount: $request->integer('inventory_count'),
            validFrom: Carbon::parse($request->input('valid_from'))->startOfDay(),
            validUntil: Carbon::parse($request->input('valid_until'))->startOfDay(),
            status: ProductStatus::from($request->string('status')->toString()),
            destinationIds: $destinationIds,
        );
    }
}
