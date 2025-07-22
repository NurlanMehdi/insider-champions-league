<?php

namespace App\Application\Commands;

final class SimulateWeekCommand
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