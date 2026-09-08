<?php

declare(strict_types=1);

namespace IvanFuhr\Result;

use Illuminate\Support\ServiceProvider;

final class ResultServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Result is a value object created via factories / helpers — nothing to bind.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        //
    }
}
