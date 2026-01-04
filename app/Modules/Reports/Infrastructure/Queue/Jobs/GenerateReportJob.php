<?php

namespace App\Modules\Reports\Infrastructure\Queue\Jobs;

use App\Modules\Reports\Application\UseCases\GenerateReportUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $reportId,
    )
    {
    }

    /**
     * Execute the job.
     * @throws Throwable
     */
    public function handle(GenerateReportUseCase $useCase): void
    {
        $useCase->execute($this->reportId);
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("report:{$this->reportId}"))->expireAfter(600),
            new RateLimited('reports-heavy'),
        ];
    }

}
