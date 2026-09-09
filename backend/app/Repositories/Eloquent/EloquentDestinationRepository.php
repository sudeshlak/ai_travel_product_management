<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\DestinationRepositoryInterface;
use App\Models\Destination;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    public function findByName(string $name): ?Destination
    {
        return Destination::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();
    }
}
