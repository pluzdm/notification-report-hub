<?php

use App\Modules\Reports\Http\Controllers\ReportController;


Route::prefix('reports')->group(function () {
    Route::post('', [ReportController::class, 'store']);
    Route::get('{id}', [ReportController::class, 'show']);
    Route::get('{id}/download', [ReportController::class, 'download']);
});
