<?php

use App\Modules\Reports\Http\Controllers\ReportController;

Route::post('/reports', [ReportController::class, 'store']);
Route::get('/reports/{id}', [ReportController::class, 'show']);
