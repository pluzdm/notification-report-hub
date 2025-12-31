<?php

namespace App\Modules\Reports\Application\Jobs;

use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
        //
    }

    /**
     * Execute the job.
     * @throws Throwable
     */
    public function handle(ReportRepositoryInterface $reports): void
    {
        $lock = Cache::lock("reports:lock:{$this->reportId}", 600);

        if (!$lock->get()) {
            return;
        }

        try {
            $reports->updateStatus($this->reportId, ReportStatusEnum::Processing);

            Cache::tags(['reports', "report:{$this->reportId}"])->put(
                "report:{$this->reportId}:status",
                ReportStatusEnum::Processing->value,
                3600
            );

            foreach ([10, 35, 60, 85, 100] as $p) {
                $reports->updateProgress($this->reportId, $p);

                Cache::tags(['reports', "report:{$this->reportId}"])->put(
                    "report:{$this->reportId}:progress",
                    $p,
                    3600
                );

                usleep(150_000);
            }

            $payload = [
                'report_id' => $this->reportId,
                'generated_at' => now()->toIso8601String(),
                'data' => [
                    'message' => 'Demo report payload',
                ],
            ];
            $path = "reports/{$this->reportId}.json";

            Storage::disk('local')->put(
                $path,
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $reports->setResultPath($this->reportId, $path);

            Cache::tags(['reports', "report:{$this->reportId}"])->put(
                "report:{$this->reportId}:result_path",
                $path,
                3600
            );

            $reports->updateStatus($this->reportId, ReportStatusEnum::Done);

            Cache::tags(['reports', "report:{$this->reportId}"])->put(
                "report:{$this->reportId}:status",
                ReportStatusEnum::Done->value,
                3600
            );
        } catch (Throwable $e) {
            $reports->updateStatus(
                $this->reportId,
                ReportStatusEnum::Failed,
                mb_substr($e->getMessage(), 0, 2000)
            );

            Cache::tags(['reports', "report:{$this->reportId}"])->put(
                "report:{$this->reportId}:status",
                ReportStatusEnum::Failed->value,
                3600
            );

            throw $e;
        } finally {
            optional($lock)->release();
        }
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("report:{$this->reportId}"))->expireAfter(600),
            new RateLimited('reports-heavy'),
        ];
    }
}
