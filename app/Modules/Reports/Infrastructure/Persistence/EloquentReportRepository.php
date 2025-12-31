<?php

namespace App\Modules\Reports\Infrastructure\Persistence;

use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use App\Modules\Reports\Infrastructure\Models\Report;

final class EloquentReportRepository implements ReportRepositoryInterface
{
    public function create(array $data): int
    {
        return Report::query()->create($data)->id;
    }

    public function findById(int $id): ?array
    {
        $model = Report::query()->find($id);
        return $model?->toArray();
    }

    public function findIdByRequestId(string $requestId): ?int
    {
        return Report::query()
            ->where('request_id', $requestId)
            ->value('id');
    }

    public function updateStatus(int $id, ReportStatusEnum $status, ?string $errorMessage = null): void
    {
        Report::query()->whereKey($id)->update([
            'status' => $status->value,
            'error_message' => $errorMessage,
        ]);
    }

    public function updateProgress(int $id, int $progress): void
    {
        $progress = max(0, min(100, $progress));

        Report::query()->whereKey($id)->update([
            'progress' => $progress,
        ]);
    }

    public function setResultPath(int $id, string $path): void
    {
        Report::query()->whereKey($id)->update([
            'result_path' => $path,
        ]);
    }
}
