<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class SuccessResponse
{
    private string $message;
    private array $data;

    public function __construct(string $message, array $data = [])
    {
        $this->message = $message;
        $this->data = $data;
    }

    public function toJsonResponse(): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $this->message,
        ];

        if (!empty($this->data)) {
            $response['data'] = $this->data;
        }

        return response()->json($response);
    }
} 