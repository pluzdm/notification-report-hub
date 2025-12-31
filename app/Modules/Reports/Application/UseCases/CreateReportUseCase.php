<?php

namespace App\Modules\Reports\Application\UseCases;

use App\Modules\Reports\Application\DTO\CreateReportDTO;
use App\Modules\Reports\Application\Jobs\GenerateReportJob;
use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use Illuminate\Support\Facades\Cache;

final readonly class CreateReportUseCase
{
    public function __construct(
        private ReportRepositoryInterface $reports,
    ) {}

    public function execute(CreateReportDTO $dto): int
    {
        $existingId = $this->reports->findIdByRequestId($dto->requestId);
        if ($existingId) {
            return $existingId;
        }

        $lock = Cache::lock("reports:create:{$dto->requestId}", 10);

        if (! $lock->get()) {
            return $this->reports->findIdByRequestId($dto->requestId) ?? 0;
        }

        try {
            $existingId = $this->reports->findIdByRequestId($dto->requestId);
            if ($existingId) {
                return $existingId;
            }

            $id = $this->reports->create([
                'request_id' => $dto->requestId,
                'type' => $dto->type,
                'params' => $dto->params,
                'status' => ReportStatusEnum::Pending->value,
                'progress' => 0,
            ]);

            Cache::tags(['reports', "report:{$id}"])->put(
                "report:{$id}:status",
                ReportStatusEnum::Pending->value,
                3600
            );

            dispatch((new GenerateReportJob($id))->onQueue('reports'));

            return $id;
        } finally {
            $lock->release();
        }
    }
}
