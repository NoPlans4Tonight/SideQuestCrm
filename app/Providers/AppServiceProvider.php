<?php

namespace App\Providers;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Services\CustomerServiceInterface;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);

        // Service bindings
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
