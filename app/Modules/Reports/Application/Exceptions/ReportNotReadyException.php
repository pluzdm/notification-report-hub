<?php

namespace App\Modules\Reports\Application\Exceptions;

use App\Modules\Reports\Domain\Enums\ReportStatusEnum;
use RuntimeException;

final class ReportNotReadyException extends RuntimeException
{
    public function __construct(int $id, ReportStatusEnum $status)
    {
        parent::__construct("Report {$id} is not ready. Current status: {$status->value}");
    }
}
