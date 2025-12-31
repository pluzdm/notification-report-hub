<?php

namespace App\Modules\Reports\Infrastructure\Providers;

use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Infrastructure\Persistence\EloquentReportRepository;
use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ReportRepositoryInterface::class, EloquentReportRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
