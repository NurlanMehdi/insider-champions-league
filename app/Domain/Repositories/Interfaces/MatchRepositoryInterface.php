<?php

namespace App\Domain\Repositories\Interfaces;

use App\Domain\Aggregates\FootballMatch;

interface MatchRepositoryInterface
{
    public function findById(int $id): FootballMatch;
    
    public function findByWeek(int $week): array;
    
    public function findAll(): array;
    
    public function save(FootballMatch $footballMatch): void;
    
    public function delete(FootballMatch $footballMatch): void;
    
    public function deleteAll(): void;
} 