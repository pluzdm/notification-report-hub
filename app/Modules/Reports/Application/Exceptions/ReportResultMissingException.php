<?php

namespace App\Modules\Reports\Application\Exceptions;

use RuntimeException;

final class ReportResultMissingException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Report {$id} result is missing");
    }
}
