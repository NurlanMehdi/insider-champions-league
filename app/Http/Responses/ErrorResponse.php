<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ErrorResponse
{
    private string $message;
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 500)
    {
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    public function toJsonResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'error' => $this->message,
        ], $this->statusCode);
    }
} 