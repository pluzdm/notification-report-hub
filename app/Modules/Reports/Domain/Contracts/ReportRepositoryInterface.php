<?php

namespace App\Modules\Reports\Domain\Contracts;

use App\Modules\Reports\Domain\Enums\ReportStatusEnum;

interface ReportRepositoryInterface
{
    public function create(array $data): int;

    public function findById(int $id): ?array;

    public function findIdByRequestId(string $requestId): ?int;

    public function updateStatus(int $id, ReportStatusEnum $status, ?string $errorMessage = null): void;

    public function updateProgress(int $id, int $progress): void;

    public function setResultPath(int $id, string $path): void;
}
