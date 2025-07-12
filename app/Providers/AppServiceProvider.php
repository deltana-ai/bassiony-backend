<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);

        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
        VerifyEmail::createUrlUsing(function ($notifiable) {
           $prefix = $this->getGuardPrefix($notifiable);

           return URL::temporarySignedRoute(
               "{$prefix}.verification.verify",
               Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
               [
                   'id' => $notifiable->getKey(),
                   'hash' => sha1($notifiable->getEmailForVerification()),
               ]
           );
        });
    }


    /**
     * Determine route prefix based on notifiable class.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function getGuardPrefix($notifiable): string
    {
        return match (get_class($notifiable)) {
            \App\Models\CompanyManager::class => 'company',
            \App\Models\Owner::class => 'owner',
            \App\Models\Client::class => 'client',
            \App\Models\Pharmacist::class => 'pharmacy',

            default => 'pharmacy',
        };
    }
}
