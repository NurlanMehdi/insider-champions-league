<?php

namespace App\Application\Services;

use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Domain\Specifications\PremierLeagueRulesSpecification;

final class WeekCalculationService
{
    private MatchRepositoryInterface $matchRepository;
    private PremierLeagueRulesSpecification $rules;

    public function __construct(
        MatchRepositoryInterface $matchRepository,
        PremierLeagueRulesSpecification $rules
    ) {
        $this->matchRepository = $matchRepository;
        $this->rules = $rules;
    }

    public function getCurrentWeek(): int
    {
        $playedMatchesCount = $this->countPlayedMatches();
        $matchesPerWeek = $this->rules->getMatchesPerWeek();
        
        if ($playedMatchesCount === 0) {
            return 1;
        }
        
        $currentWeek = (int) floor($playedMatchesCount / $matchesPerWeek) + 1;
        
        return min($currentWeek, $this->rules->getTotalWeeks());
    }

    public function getTotalWeeks(): int
    {
        return $this->rules->getTotalWeeks();
    }

    public function isSeasonComplete(): bool
    {
        return $this->getCurrentWeek() > $this->rules->getTotalWeeks();
    }

    private function countPlayedMatches(): int
    {
        $allMatches = $this->matchRepository->findAll();
        $playedCount = 0;
        
        foreach ($allMatches as $match) {
            if ($match->isPlayed()) {
                $playedCount++;
            }
        }
        
        return $playedCount;
    }
} 