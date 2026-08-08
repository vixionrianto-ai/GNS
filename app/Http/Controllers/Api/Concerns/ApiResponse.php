<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $extra = []
    ): JsonResponse {

        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra), $status);
    }
    protected function successPaginated(
        mixed $data,
        LengthAwarePaginator $pagination,
        string $message = 'OK'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'current_page' => $pagination->currentPage(),
                'last_page'    => $pagination->lastPage(),
                'per_page'     => $pagination->perPage(),
                'total'        => $pagination->total(),
                'from'         => $pagination->firstItem(),
                'to'           => $pagination->lastItem(),
            ],
        ]);
    }
    protected function error(
        string $message,
        int $status = 422,
        mixed $errors = null
    ): JsonResponse {

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
    protected function created(
        mixed $data,
        string $message = 'Berhasil dibuat.'
    ): JsonResponse {

        return $this->success(
            $data,
            $message,
            201
        );
    }
    protected function deleted(
        string $message = 'Data berhasil dihapus.'
    ): JsonResponse {

        return $this->success(
            null,
            $message,
            200
        );
    }
    protected function notFound(
        string $message = 'Data tidak ditemukan.'
    ): JsonResponse {

        return $this->error(
            $message,
            404
        );
    }
    protected function validationError(
        mixed $errors
    ): JsonResponse {

        return $this->error(
            'Validasi gagal.',
            422,
            $errors
        );
    }
}