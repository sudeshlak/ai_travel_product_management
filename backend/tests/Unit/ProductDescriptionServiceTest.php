<?php

namespace Tests\Unit;

use App\Contracts\Integrations\ProductDescriptionPolisherInterface;
use App\DataTransferObjects\ProductDescriptionPolishInput;
use App\Exceptions\IntegrationException;
use App\Services\ProductDescriptionService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductDescriptionServiceTest extends TestCase
{
    private ProductDescriptionPolisherInterface&MockInterface $polisher;

    private ProductDescriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->polisher = Mockery::mock(ProductDescriptionPolisherInterface::class);
        $this->service = new ProductDescriptionService($this->polisher);
    }

    public function test_generate_returns_polished_description(): void
    {
        $input = new ProductDescriptionPolishInput(
            description: 'nice tour in paris with guides',
            productName: 'Paris Tour',
            category: 'Tours',
        );

        $this->polisher
            ->shouldReceive('polish')
            ->once()
            ->with(Mockery::on(fn (ProductDescriptionPolishInput $value) => $value === $input))
            ->andReturn('A guided Paris tour with expert local hosts.');

        $this->assertSame(
            'A guided Paris tour with expert local hosts.',
            $this->service->generate($input),
        );
    }

    public function test_generate_truncates_overlong_output(): void
    {
        $input = new ProductDescriptionPolishInput(
            description: 'nice tour in paris with guides',
        );

        $long = str_repeat('a', ProductDescriptionService::MAX_DESCRIPTION_LENGTH + 50);

        $this->polisher
            ->shouldReceive('polish')
            ->once()
            ->andReturn($long);

        $result = $this->service->generate($input);

        $this->assertSame(ProductDescriptionService::MAX_DESCRIPTION_LENGTH, mb_strlen($result));
        $this->assertSame(mb_substr($long, 0, ProductDescriptionService::MAX_DESCRIPTION_LENGTH), $result);
    }

    public function test_generate_propagates_integration_exception(): void
    {
        $input = new ProductDescriptionPolishInput(
            description: 'nice tour in paris with guides',
        );

        $this->polisher
            ->shouldReceive('polish')
            ->once()
            ->andThrow(new IntegrationException('boom'));

        $this->expectException(IntegrationException::class);

        $this->service->generate($input);
    }
}
