<?php

use App\Modules\Reports\Application\Exceptions\ReportNotFoundException;
use App\Modules\Reports\Application\Exceptions\ReportNotReadyException;
use App\Modules\Reports\Application\Exceptions\ReportResultMissingException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ReportNotFoundException $e, $request) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        });

        $exceptions->render(function (ReportNotReadyException $e, $request) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        });

        $exceptions->render(function (ReportResultMissingException $e, $request) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        });
    })->create();
