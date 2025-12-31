<?php

namespace App\Modules\Reports\Application\DTO;

final readonly class ReportResultDTO
{
    public function __construct(
        public int $reportId,
        public string $path,
        public string $content,
        public string $mime = 'application/json',
    ) {}
}
