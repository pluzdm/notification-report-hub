<?php

namespace App\Modules\Reports\Application\UseCases;

use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DownloadReportUseCase
{
    public function __construct(
        private ReportRepositoryInterface $reports,
    ) {}

    public function execute(int $id): StreamedResponse
    {
        $status = Cache::tags(['reports', "report:{$id}"])->get("report:{$id}:status");

        $report = null;

        if (!$status) {
            $report = $this->reports->findById($id);

            if (!$report) {
                abort(Response::HTTP_NOT_FOUND, 'Report not found');
            }

            $status = $report['status'] ?? null;
        }

        if ($status !== ReportStatusEnum::Done->value) {
            abort(Response::HTTP_CONFLICT, 'Report is not ready yet');
        }

        $path = Cache::tags(['reports', "report:{$id}"])->get("report:{$id}:result_path");

        if (!$path) {
            $report ??= $this->reports->findById($id);

            if (! $report) {
                abort(Response::HTTP_NOT_FOUND, 'Report not found');
            }

            $path = $report['result_path'] ?? null;
        }

        if (!$path) {
            abort(Response::HTTP_CONFLICT, 'Report has no result file');
        }

        if (!str_starts_with($path, 'reports/')) {
            abort(Response::HTTP_CONFLICT, 'Invalid report path');
        }

        if (!Storage::disk('local')->exists($path)) {
            abort(Response::HTTP_NOT_FOUND, 'Report file not found');
        }

        $downloadName = "report-{$id}.json";

        return Storage::disk('local')->download(
            $path,
            $downloadName,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
