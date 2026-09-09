<?php

namespace App\Contracts\Repositories;

use App\Models\Destination;
use Illuminate\Support\Collection;

interface DestinationRepositoryInterface
{
    /**
     * @return Collection<int, Destination>
     */
    public function allOrderedByName(): Collection;
}
