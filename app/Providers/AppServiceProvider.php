<?php

namespace App\Providers;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Services\CustomerServiceInterface;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Support\ServiceProvider;
use App\Models\Service;
use App\Observers\ServiceObserver;
use App\Models\Job;
use App\Observers\JobObserver;

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
        $this->app->bind(\App\Contracts\Repositories\ServiceRepositoryInterface::class, \App\Repositories\ServiceRepository::class);
        $this->app->bind(\App\Contracts\Services\ServiceServiceInterface::class, \App\Services\ServiceService::class);
        $this->app->bind(\App\Contracts\Repositories\JobRepositoryInterface::class, \App\Repositories\JobRepository::class);
        $this->app->bind(\App\Contracts\Services\JobServiceInterface::class, \App\Services\JobService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Service::observe(ServiceObserver::class);
        Job::observe(JobObserver::class);
    }
}
