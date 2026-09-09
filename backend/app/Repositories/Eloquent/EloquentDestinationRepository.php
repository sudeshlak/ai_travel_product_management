<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\DestinationRepositoryInterface;
use App\Models\Destination;
use Illuminate\Support\Collection;

class EloquentDestinationRepository implements DestinationRepositoryInterface
{
    /**
     * @return Collection<int, Destination>
     */
    public function allOrderedByName(): Collection
    {
        return Destination::query()
            ->orderBy('name')
            ->get();
    }
}
