<?php

namespace App\Modules\Reports\Application\UseCases;

use App\Modules\Reports\Application\DTO\ReportResultDTO;
use App\Modules\Reports\Application\Exceptions\ReportNotFoundException;
use App\Modules\Reports\Application\Exceptions\ReportNotReadyException;
use App\Modules\Reports\Application\Exceptions\ReportResultMissingException;
use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use Illuminate\Support\Facades\Storage;

final readonly class GetReportResultUseCase
{
    public function __construct(
        private ReportRepositoryInterface $reports,
    )
    {
    }

    public function execute(int $id): ReportResultDTO
    {
        $report = $this->reports->findById($id);

        if (!$report) {
            throw new ReportNotFoundException($id);
        }

        $status = ReportStatusEnum::from($report['status']);
        if ($status !== ReportStatusEnum::Done) {
            throw new ReportNotReadyException($id, $status);
        }

        $path = $report['result_path'] ?? null;
        if (!$path) {
            throw new ReportResultMissingException($id);
        }

        $disk = Storage::disk('local');
        if (!$disk->exists($path)) {
            throw new ReportResultMissingException($id);
        }

        $content = $disk->get($path);

        return new ReportResultDTO(
            reportId: $id,
            path: $path,
            content: $content,
            mime: 'application/json',
        );
    }
}
