<?php

namespace App\Modules\Reports\Application\UseCases;

use App\Modules\Reports\Application\DTO\DownloadReportDTO;
use App\Modules\Reports\Application\Exceptions\ReportNotFoundException;
use App\Modules\Reports\Application\Exceptions\ReportNotReadyException;
use App\Modules\Reports\Application\Exceptions\ReportResultMissingException;
use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class DownloadReportUseCase
{
    private const DISK = 'local';
    private const REPORTS_PREFIX = 'reports/';

    public function __construct(
        private ReportRepositoryInterface $reports,
    ) {}

    public function execute(int $id): DownloadReportDTO
    {
        $status = Cache::tags(['reports'])->get("report:{$id}:status");
        if (!$status) {
            $report = $this->getReportOrFail($id);
            $status = $report['status'] ?? null;
        }

        if ($status !== ReportStatusEnum::Done->value) {
            throw new ReportNotReadyException($id, $status);
        }

        $path = Cache::tags(['reports'])->get("report:{$id}:result_path")
            ?? ($this->getReportOrFail($id)['result_path'] ?? null);
        if (!$path) {
            throw new ReportResultMissingException($id);
        }

        if (!Str::startsWith($path, 'reports/')) {
            throw new ReportResultMissingException($id);
        }

        if (Str::contains($path, '..')) {
            throw new ReportResultMissingException($id);
        }

        if (!Storage::disk('local')->exists($path)) {
            throw new ReportNotFoundException($id);
        }

        return new DownloadReportDTO(
            disk: 'local',
            path: $path,
            downloadName: "report-{$id}.json",
            contentType: 'application/json; charset=UTF-8',
        );
    }

    private function getReportOrFail(int $id): array
    {
        $report = $this->reports->findById($id);

        if (!$report) {
            throw new ReportNotFoundException($id);
        }

        return $report;
    }
}
