<?php

namespace App\Modules\Reports\Application\DTO;

final readonly class DownloadReportDTO
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $downloadName,
        public string $contentType,
    ) {}
}
