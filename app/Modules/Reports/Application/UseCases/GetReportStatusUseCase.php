<?php

namespace App\Modules\Reports\Application\UseCases;

use App\Modules\Reports\Domain\Contracts\ReportRepositoryInterface;
use Illuminate\Support\Facades\Cache;

final readonly class GetReportStatusUseCase
{
    public function __construct(
        private ReportRepositoryInterface $reports,
    ) {}

    public function execute(int $id): ?array
    {
        $cached = Cache::tags(['reports', "report:{$id}"])->get("report:{$id}:payload");

        if (is_array($cached)) {
            return $cached;
        }

        $data = $this->reports->findById($id);
        if (!$data) {
            return null;
        }

        Cache::tags(['reports', "report:{$id}"])->put("report:{$id}:payload", $data, 60);
        return $data;
    }
}
