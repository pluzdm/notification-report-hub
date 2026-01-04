<?php

namespace App\Modules\Reports\Application\UseCases;

use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class GenerateReportUseCase
{
    public function __construct(
        private ReportRepositoryInterface $reports,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(int $reportId): void
    {
        $lock = Cache::lock("reports:lock:{$reportId}", 600);

        if (!$lock->get()) {
            return;
        }

        try {
            $this->reports->updateStatus($reportId, ReportStatusEnum::Processing);

            Cache::tags(['reports', "report:{$reportId}"])->put(
                "report:{$reportId}:status",
                ReportStatusEnum::Processing->value,
                3600
            );

            foreach ([10, 35, 60, 85, 100] as $p) {
                $this->reports->updateProgress($reportId, $p);

                Cache::tags(['reports', "report:{$reportId}"])->put(
                    "report:{$reportId}:progress",
                    $p,
                    3600
                );

                usleep(150_000);
            }

            $payload = [
                'report_id' => $reportId,
                'generated_at' => now()->toIso8601String(),
                'data' => [
                    'message' => 'Demo report payload',
                ],
            ];

            $path = "reports/{$reportId}.json";

            Storage::disk('local')->put(
                $path,
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $this->reports->setResultPath($reportId, $path);

            Cache::tags(['reports', "report:{$reportId}"])->put(
                "report:{$reportId}:result_path",
                $path,
                3600
            );

            $this->reports->updateStatus($reportId, ReportStatusEnum::Done);

            Cache::tags(['reports', "report:{$reportId}"])->put(
                "report:{$reportId}:status",
                ReportStatusEnum::Done->value,
                3600
            );
        } catch (Throwable $e) {
            $this->reports->updateStatus(
                $reportId,
                ReportStatusEnum::Failed,
                mb_substr($e->getMessage(), 0, 2000)
            );

            Cache::tags(['reports', "report:{$reportId}"])->put(
                "report:{$reportId}:status",
                ReportStatusEnum::Failed->value,
                3600
            );

            throw $e;
        } finally {
            optional($lock)->release();
        }
    }
}
