<?php

namespace App\Application\Queries;

final class GetWeeklyResultsQuery
{
    private int $week;

    public function __construct(int $week)
    {
        $this->week = $week;
    }

    public function getWeek(): int
    {
        return $this->week;
    }
} 