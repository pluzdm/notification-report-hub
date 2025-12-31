<?php

namespace App\Modules\Reports\Application\DTO;

final readonly class CreateReportDTO
{
    public function __construct(
        public string $requestId,
        public string $type,
        public array $params,
    ) {}
}
