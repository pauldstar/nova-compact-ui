<?php

namespace Pauldstar\NovaCompactUi;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->namespace('Pauldstar\NovaCompactUi\Http\Controllers')
            ->prefix('pauldstar/nova-compact-reactive-ui')
            ->group(__DIR__ . '/../routes/api.php');
    }
}
