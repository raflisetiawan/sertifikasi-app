<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function success(mixed $data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $error
     * @return JsonResponse
     */
    protected function error(string $message = 'Error', int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, mixed $error = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error,
        ], $statusCode);
    }
}
