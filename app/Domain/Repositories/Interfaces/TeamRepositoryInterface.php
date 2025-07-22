<?php

namespace App\Domain\Repositories\Interfaces;

use App\Domain\Aggregates\Team;

interface TeamRepositoryInterface
{
    public function findById(int $id): Team;
    
    public function findAll(): array;
    
    public function save(Team $team): void;
    
    public function delete(Team $team): void;
} 