<?php

namespace App\Services;

use App\Contracts\Repositories\DestinationRepositoryInterface;
use App\Models\Destination;
use Illuminate\Support\Collection;

class DestinationService
{
    public function __construct(
        private readonly DestinationRepositoryInterface $destinations,
    ) {}

    /**
     * @return Collection<int, Destination>
     */
    public function list(): Collection
    {
        return $this->destinations->allOrderedByName();
    }
}
