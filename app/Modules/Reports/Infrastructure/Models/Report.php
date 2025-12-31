<?php

namespace App\Modules\Reports\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'request_id',
        'type',
        'params',
        'status',
        'progress',
        'result_path',
        'error_message',
    ];

    protected $casts = [
        'params' => 'array',
        'progress' => 'integer',
    ];
}
