<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class TeamStrength
{
    private const MIN_STRENGTH = 1;
    private const MAX_STRENGTH = 100;
    
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
        $this->validate();
    }

    public static function fromValue(int $value): self
    {
        return new self($value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function addHomeAdvantage(int $advantage = 5): self
    {
        $newValue = min(self::MAX_STRENGTH, $this->value + $advantage);
        return new self($newValue);
    }

    public function compareTo(TeamStrength $other): int
    {
        return $this->value <=> $other->getValue();
    }

    public function getDifference(TeamStrength $other): int
    {
        return $this->value - $other->getValue();
    }

    private function validate(): void
    {
        if ($this->value < self::MIN_STRENGTH || $this->value > self::MAX_STRENGTH) {
            throw new InvalidArgumentException(
                sprintf('Team strength must be between %d and %d', self::MIN_STRENGTH, self::MAX_STRENGTH)
            );
        }
    }
} 