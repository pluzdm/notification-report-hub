<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Application\DTO\CreateReportDTO;
use App\Modules\Reports\Application\Exceptions\ReportNotFoundException;
use App\Modules\Reports\Application\Exceptions\ReportNotReadyException;
use App\Modules\Reports\Application\Exceptions\ReportResultMissingException;
use App\Modules\Reports\Application\UseCases\CreateReportUseCase;
use App\Modules\Reports\Application\UseCases\DownloadReportUseCase;
use App\Modules\Reports\Application\UseCases\GetReportResultUseCase;
use App\Modules\Reports\Application\UseCases\GetReportStatusUseCase;
use App\Modules\Reports\Http\Requests\CreateReportRequest;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function store(CreateReportRequest $request, CreateReportUseCase $useCase)
    {
        $validated = $request->validated();

        $dto = new CreateReportDTO(
            requestId: $validated['request_id'] ?? (string) Str::uuid(),
            type: $validated['type'],
            params: $validated['params'] ?? [],
        );

        $id = $useCase->execute($dto);

        return response()->json([
            'id' => $id,
            'request_id' => $dto->requestId,
        ], 202);
    }

    public function show(int $id, GetReportStatusUseCase $useCase)
    {
        $data = $useCase->execute($id);

        if (!$data) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($data);
    }

    public function download(int $id, DownloadReportUseCase $useCase)
    {
        return $useCase->execute($id);
    }

    public function result(int $id, GetReportResultUseCase $useCase)
    {
        try {
            $dto = $useCase->execute($id);

            return response($dto->content, 200, [
                'Content-Type' => $dto->mime,
            ]);
        } catch (ReportNotFoundException) {
            return response()->json(['message' => 'Not found'], 404);
        } catch (ReportNotReadyException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (ReportResultMissingException) {
            return response()->json(['message' => 'Result missing'], 500);
        }
    }
}
