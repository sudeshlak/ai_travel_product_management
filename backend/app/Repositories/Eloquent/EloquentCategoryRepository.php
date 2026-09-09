<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Support\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @return Collection<int, Category>
     */
    public function allOrderedByName(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }
}
