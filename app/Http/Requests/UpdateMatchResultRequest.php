<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0', 'max:20'],
            'away_score' => ['required', 'integer', 'min:0', 'max:20'],
        ];
    }

    public function getHomeScore(): int
    {
        return $this->integer('home_score');
    }

    public function getAwayScore(): int
    {
        return $this->integer('away_score');
    }
} 