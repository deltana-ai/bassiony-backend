<?php

namespace App\Providers;

use App\Interfaces\AdminRepositoryInterface;
use App\Interfaces\ContactUsRepositoryInterface;
use App\Interfaces\SliderRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\{BrandRepositoryInterface,CategoryRepositoryInterface, ProductRepositoryInterface,FavoriteRepositoryInterface, OrderRepositoryInterface, PharmacistRepositoryInterface, PillReminderRepositoryInterface, RateRepositoryInterface};
use App\Interfaces\{PharmacyRateRepositoryInterface};
use App\Repositories\PharmacyRateRepository;
use App\Repositories\AdminRepository;
use App\Repositories\ContactUsRepository;
use App\Repositories\SliderRepository;
use App\Repositories\UserRepository;
use App\Repositories\{BrandRepository, CategoryRepository, ProductRepository,FavoriteRepository, OrderRepository, PharmacistRepository, PillReminderRepository, RateRepository};

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
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(RateRepositoryInterface::class, RateRepository::class);
        $this->app->bind(PillReminderRepositoryInterface::class, PillReminderRepository::class);
        $this->app->bind(PharmacyRateRepositoryInterface::class, PharmacyRateRepository::class);
        $this->app->bind(PharmacistRepositoryInterface::class, PharmacistRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
