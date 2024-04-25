<?php

namespace App\Providers;

use App\Services\Cart;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Facades\CartFacade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
        $this->app->singleton('cart', function () {
            return new Cart();
        });
        
        $loader = AliasLoader::getInstance();
        $loader->alias('Cart' , CartFacade::class);
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();
    }
}
