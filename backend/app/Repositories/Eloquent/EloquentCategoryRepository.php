<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    public function findByName(string $name): ?Category
    {
        return Category::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();
    }
}
