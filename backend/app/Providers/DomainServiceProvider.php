<?php

namespace App\Providers;

use App\Contracts\Integrations\ProductGeneratorInterface;
use App\Contracts\Integrations\SearchQueryInterpreterInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\DestinationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Infrastructure\OpenAi\OpenAiProductGenerator;
use App\Infrastructure\OpenAi\OpenAiSearchQueryInterpreter;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentDestinationRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(DestinationRepositoryInterface::class, EloquentDestinationRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(SearchQueryInterpreterInterface::class, OpenAiSearchQueryInterpreter::class);
        $this->app->bind(ProductGeneratorInterface::class, OpenAiProductGenerator::class);
    }

    public function boot(): void
    {
        //
    }
}
