<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // ต้องมีบรรทัดนี้

class AppServiceProvider extends ServiceProvider
{
    public function register(): void { }

    public function boot(): void
    {
        // บังคับให้ระบบ Pagination ใช้ Bootstrap 5
        Paginator::useBootstrapFive();
    }
}