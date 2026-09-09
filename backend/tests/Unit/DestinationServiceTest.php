<?php

namespace Tests\Unit;

use App\Contracts\Repositories\DestinationRepositoryInterface;
use App\Models\Destination;
use App\Services\DestinationService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DestinationServiceTest extends TestCase
{
    private DestinationRepositoryInterface&MockInterface $destinations;

    private DestinationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->destinations = Mockery::mock(DestinationRepositoryInterface::class);
        $this->service = new DestinationService($this->destinations);
    }

    public function test_list_forwards_to_repository(): void
    {
        $items = Collection::make([new Destination(['name' => 'Colombo'])]);

        $this->destinations
            ->shouldReceive('allOrderedByName')
            ->once()
            ->andReturn($items);

        $this->assertSame($items, $this->service->list());
    }
}
