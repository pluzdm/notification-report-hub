<?php

namespace App\Modules\Reports\Domain\Enums;

enum ReportStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';
}
