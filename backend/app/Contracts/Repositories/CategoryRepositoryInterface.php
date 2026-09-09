<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    /**
     * @return Collection<int, Category>
     */
    public function allOrderedByName(): Collection;
}
