<?php

namespace App\Providers;

use App\Values\ServerCodes;
use Discord\Discord;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Discord::class, fn() => new Discord([
            'token' => config('services.discord.authToken'),
        ]));
        $this->app->singleton(ServerCodes::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
