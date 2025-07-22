<?php

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\MatchSimulationService;
use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\Services\Interfaces\MatchSimulatorInterface;
use App\Domain\Services\Strategies\MatchSimulationStrategyInterface;
use App\Domain\Aggregates\FootballMatch;
use App\Domain\Aggregates\Team;
use App\Domain\ValueObjects\TeamStrength;
use App\Domain\ValueObjects\Score;
use Mockery;

class MatchSimulationServiceTest extends TestCase
{
    private $mockMatchRepository;
    private $mockTeamRepository;
    private $mockMatchSimulator;
    private $mockSimulationStrategy;
    private MatchSimulationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockMatchRepository = Mockery::mock(MatchRepositoryInterface::class);
        $this->mockTeamRepository = Mockery::mock(TeamRepositoryInterface::class);
        $this->mockMatchSimulator = Mockery::mock(MatchSimulatorInterface::class);
        $this->mockSimulationStrategy = Mockery::mock(MatchSimulationStrategyInterface::class);
        
        $this->service = new MatchSimulationService(
            $this->mockMatchRepository,
            $this->mockTeamRepository,
            $this->mockMatchSimulator,
            $this->mockSimulationStrategy
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_simulate_week_processes_unplayed_matches_only(): void
    {
        $week = 5;
        $playedMatch = FootballMatch::create(1, 10, 11, $week);
        $playedMatch->play(Score::create(2, 1));
        
        $unplayedMatch = FootballMatch::create(2, 12, 13, $week);
        
        $matches = [$playedMatch, $unplayedMatch];
        
        $this->mockMatchRepository
            ->shouldReceive('findByWeek')
            ->with($week)
            ->once()
            ->andReturn($matches);
        
        $this->mockMatchSimulator
            ->shouldReceive('simulateAndPlay')
            ->with($unplayedMatch)
            ->once();
        
        $this->mockMatchRepository
            ->shouldReceive('save')
            ->with($unplayedMatch)
            ->once();
        
        $this->service->simulateWeek($week);
    }

    public function test_simulate_all_remaining_matches_fast_uses_caching(): void
    {
        $unplayedMatch = FootballMatch::create(1, 10, 11, 1);
        $allMatches = [$unplayedMatch];
        
        $homeTeam = Team::create(10, 'Home Team', TeamStrength::fromValue(80));
        $awayTeam = Team::create(11, 'Away Team', TeamStrength::fromValue(75));
        
        $score = Score::create(2, 1);
        
        $this->mockMatchRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($allMatches);
        
        $this->mockTeamRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([$homeTeam, $awayTeam]);
        
        $this->mockSimulationStrategy
            ->shouldReceive('simulate')
            ->with($homeTeam->getStrength(), $awayTeam->getStrength())
            ->once()
            ->andReturn($score);
        
        $this->service->simulateAllRemainingMatchesFast();
    }

    public function test_simulate_all_remaining_matches_handles_empty_matches(): void
    {
        $this->mockMatchRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([]);
        
        $this->mockTeamRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([]);
        
        // Should not call simulation strategy when no matches
        $this->mockSimulationStrategy->shouldNotReceive('simulate');
        
        $this->service->simulateAllRemainingMatchesFast();
    }

    public function test_simulate_all_remaining_matches_skips_played_matches(): void
    {
        $playedMatch = FootballMatch::create(1, 10, 11, 1);
        $playedMatch->play(Score::create(2, 1));
        
        $unplayedMatch = FootballMatch::create(2, 12, 13, 1);
        
        $allMatches = [$playedMatch, $unplayedMatch];
        
        $homeTeam = Team::create(12, 'Home Team', TeamStrength::fromValue(80));
        $awayTeam = Team::create(13, 'Away Team', TeamStrength::fromValue(75));
        
        $score = Score::create(1, 0);
        
        $this->mockMatchRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($allMatches);
        
        $this->mockTeamRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([$homeTeam, $awayTeam]);
        
        // Should only simulate unplayed match
        $this->mockSimulationStrategy
            ->shouldReceive('simulate')
            ->once()
            ->andReturn($score);
        
        $this->service->simulateAllRemainingMatchesFast();
    }

    public function test_simulate_week_with_no_matches_does_nothing(): void
    {
        $week = 10;
        
        $this->mockMatchRepository
            ->shouldReceive('findByWeek')
            ->with($week)
            ->once()
            ->andReturn([]);
        
        $this->mockMatchSimulator->shouldNotReceive('simulateAndPlay');
        $this->mockMatchRepository->shouldNotReceive('save');
        
        $this->service->simulateWeek($week);
    }

    public function test_team_caching_prevents_multiple_repository_calls(): void
    {
        $match1 = FootballMatch::create(1, 10, 11, 1);
        $match2 = FootballMatch::create(2, 10, 12, 1); // Same home team
        
        $allMatches = [$match1, $match2];
        
        $team10 = Team::create(10, 'Team 10', TeamStrength::fromValue(80));
        $team11 = Team::create(11, 'Team 11', TeamStrength::fromValue(75));
        $team12 = Team::create(12, 'Team 12', TeamStrength::fromValue(70));
        
        $this->mockMatchRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($allMatches);
        
        $this->mockTeamRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([$team10, $team11, $team12]);
        
        $this->mockSimulationStrategy
            ->shouldReceive('simulate')
            ->twice()
            ->andReturn(Score::create(1, 0));
        
        $this->service->simulateAllRemainingMatchesFast();
    }

    public function test_performance_with_large_dataset(): void
    {
        // Simulate full Premier League season
        $matches = [];
        for ($i = 1; $i <= 380; $i++) {
            $homeTeam = ($i % 20) + 1;
            $awayTeam = (($i + 10) % 20) + 1;
            if ($homeTeam === $awayTeam) $awayTeam = ($awayTeam % 20) + 1;
            
            $matches[] = FootballMatch::create($i, $homeTeam, $awayTeam, (int)ceil($i / 10));
        }
        
        $teams = [];
        for ($i = 1; $i <= 20; $i++) {
            $teams[] = Team::create($i, "Team {$i}", TeamStrength::fromValue(70 + $i));
        }
        
        $this->mockMatchRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($matches);
        
        $this->mockTeamRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($teams);
        
        $this->mockSimulationStrategy
            ->shouldReceive('simulate')
            ->times(380)
            ->andReturn(Score::create(1, 0));
        
        $startTime = microtime(true);
        $this->service->simulateAllRemainingMatchesFast();
        $endTime = microtime(true);
        
        // Should complete in under 1 second (it's actually much faster)
        $this->assertLessThan(1.0, $endTime - $startTime, 'Simulation should be very fast');
    }

    public function test_batch_processing_with_sleep(): void
    {
        $matches = [];
        for ($i = 1; $i <= 120; $i++) { // More than batch size
            $matches[] = FootballMatch::create($i, 1, 2, 1);
        }
        
        $team1 = Team::create(1, 'Team 1', TeamStrength::fromValue(80));
        $team2 = Team::create(2, 'Team 2', TeamStrength::fromValue(75));
        
        $this->mockMatchRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($matches);
        
        $this->mockTeamRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([$team1, $team2]);
        
        $this->mockMatchRepository
            ->shouldReceive('save')
            ->times(120);
        
        $this->mockMatchSimulator
            ->shouldReceive('simulateAndPlay')
            ->times(120);
        
        $this->service->simulateAllRemainingMatches();
    }
} 