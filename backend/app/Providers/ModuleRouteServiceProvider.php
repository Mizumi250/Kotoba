<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ModuleRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $routeFile = base_path('routes/modules/kotoba.php');
        if (file_exists($routeFile)) {
            Route::middleware(['api'])
                ->group($routeFile);
        }
    }
}
