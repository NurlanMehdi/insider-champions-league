<?php

namespace App\Application\Handlers\CommandHandlers;

use App\Application\Commands\SimulateWeekCommand;
use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Domain\Services\Interfaces\MatchSimulatorInterface;

final class SimulateWeekCommandHandler
{
    private MatchRepositoryInterface $matchRepository;
    private MatchSimulatorInterface $matchSimulator;

    public function __construct(
        MatchRepositoryInterface $matchRepository,
        MatchSimulatorInterface $matchSimulator
    ) {
        $this->matchRepository = $matchRepository;
        $this->matchSimulator = $matchSimulator;
    }

    public function handle(SimulateWeekCommand $command): void
    {
        $matches = $this->matchRepository->findByWeek($command->getWeek());
        
        foreach ($matches as $footballMatch) {
            if (!$footballMatch->isPlayed()) {
                $this->matchSimulator->simulateAndPlay($footballMatch);
                $this->matchRepository->save($footballMatch);
            }
        }
    }
} 