<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $inicio = microtime(true);
        app()->terminating(function () use ($inicio) {
            $totalMs = round((microtime(true) - $inicio) * 1000, 2);
            if ($totalMs > 800) {
                \Illuminate\Support\Facades\Log::error("TIEMPO PHP ({$totalMs} ms) en: " . request()->fullUrl());
            }
        });

        DB::listen(function ($query) {
            if ($query->time > 500) {
                $ruta = app()->runningInConsole() ? 'console' : request()->fullUrl();
                Log::error("SQL LENTO ({$query->time} ms) en [{$ruta}]: " . $query->sql);
            }
        });
    }
}