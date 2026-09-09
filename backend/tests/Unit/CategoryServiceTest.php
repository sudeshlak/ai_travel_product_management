<?php

namespace Tests\Unit;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    private CategoryRepositoryInterface&MockInterface $categories;

    private CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Mockery::mock(CategoryRepositoryInterface::class);
        $this->service = new CategoryService($this->categories);
    }

    public function test_list_forwards_to_repository(): void
    {
        $items = Collection::make([new Category(['name' => 'Adventure'])]);

        $this->categories
            ->shouldReceive('allOrderedByName')
            ->once()
            ->andReturn($items);

        $this->assertSame($items, $this->service->list());
    }
}
