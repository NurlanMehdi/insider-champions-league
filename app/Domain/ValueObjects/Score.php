<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class Score
{
    private int $home;
    private int $away;

    public function __construct(int $home, int $away)
    {
        $this->home = $home;
        $this->away = $away;
        $this->validate();
    }

    public static function create(int $home, int $away): self
    {
        return new self($home, $away);
    }

    public static function notPlayed(): self
    {
        return new self(0, 0);
    }

    public function getHome(): int
    {
        return $this->home;
    }

    public function getAway(): int
    {
        return $this->away;
    }

    public function toString(): string
    {
        return "{$this->home}-{$this->away}";
    }

    public function getResult(): string
    {
        if ($this->home > $this->away) {
            return 'home_win';
        }
        
        if ($this->away > $this->home) {
            return 'away_win';
        }
        
        return 'draw';
    }

    public function equals(Score $other): bool
    {
        return $this->home === $other->getHome() && $this->away === $other->getAway();
    }

    private function validate(): void
    {
        if ($this->home < 0 || $this->away < 0) {
            throw new InvalidArgumentException('Scores cannot be negative');
        }
        
        if ($this->home > 50 || $this->away > 50) {
            throw new InvalidArgumentException('Scores cannot exceed 50');
        }
    }
} 