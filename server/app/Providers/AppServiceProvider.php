<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\DashboardAdminRepositoryInterface;
use App\Repositories\Contracts\DashboardClienteRepositoryInterface;
use App\Repositories\Contracts\RelatorioClienteRepositoryInterface;
use App\Repositories\Local\DashboardAdminLocalRepository;
use App\Repositories\Local\DashboardClienteLocalRepository;
use App\Repositories\Local\RelatorioClienteLocalRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DashboardAdminRepositoryInterface::class, DashboardAdminLocalRepository::class);
        $this->app->bind(DashboardClienteRepositoryInterface::class, DashboardClienteLocalRepository::class);
        $this->app->bind(RelatorioClienteRepositoryInterface::class, RelatorioClienteLocalRepository::class);
    }

    public function boot(): void {}
}
