<?php

namespace App\Providers;

use App\Interfaces\AdminRepositoryInterface;
use App\Interfaces\ContactUsRepositoryInterface;
use App\Interfaces\SliderRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\BrandRepositoryInterface;

use App\Repositories\AdminRepository;
use App\Repositories\ContactUsRepository;
use App\Repositories\SliderRepository;
use App\Repositories\UserRepository;
use App\Repositories\BrandRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ContactUsRepositoryInterface::class, ContactUsRepository::class);
        $this->app->bind(SliderRepositoryInterface::class, SliderRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
