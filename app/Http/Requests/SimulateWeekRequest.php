<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SimulateWeekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week' => ['sometimes', 'integer', 'min:1', 'max:38'],
        ];
    }

    public function getWeek(): int
    {
        return $this->input('week', 1);
    }
} 