<?php

namespace Tests\Unit\Domain\Aggregates;

use Tests\TestCase;
use App\Domain\Aggregates\Team;
use App\Domain\ValueObjects\TeamStrength;
use App\Domain\ValueObjects\TeamStatistics;
use App\Domain\Events\TeamStatisticsUpdatedEvent;
use InvalidArgumentException;

class TeamTest extends TestCase
{
    public function test_can_create_team_with_valid_data(): void
    {
        $strength = TeamStrength::fromValue(85);
        $team = Team::create(1, 'Manchester City', $strength, 'mancity.png');

        $this->assertEquals(1, $team->getId());
        $this->assertEquals('Manchester City', $team->getName());
        $this->assertEquals(85, $team->getStrength()->getValue());
        $this->assertEquals('mancity.png', $team->getLogo());
        $this->assertInstanceOf(TeamStatistics::class, $team->getStatistics());
    }

    public function test_can_create_team_without_logo(): void
    {
        $strength = TeamStrength::fromValue(80);
        $team = Team::create(2, 'Arsenal', $strength);

        $this->assertEquals(2, $team->getId());
        $this->assertEquals('Arsenal', $team->getName());
        $this->assertNull($team->getLogo());
    }

    public function test_cannot_create_team_with_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team name cannot be empty');

        $strength = TeamStrength::fromValue(75);
        Team::create(1, '', $strength);
    }

    public function test_cannot_create_team_with_invalid_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team ID must be positive');

        $strength = TeamStrength::fromValue(75);
        Team::create(0, 'Chelsea', $strength);
    }

    public function test_team_statistics_are_initially_empty(): void
    {
        $strength = TeamStrength::fromValue(88);
        $team = Team::create(1, 'Liverpool', $strength);

        $stats = $team->getStatistics();
        
        $this->assertEquals(0, $stats->getPoints());
        $this->assertEquals(0, $stats->getPlayed());
        $this->assertEquals(0, $stats->getWins());
        $this->assertEquals(0, $stats->getDraws());
        $this->assertEquals(0, $stats->getLosses());
        $this->assertEquals(0, $stats->getGoalsFor());
        $this->assertEquals(0, $stats->getGoalsAgainst());
        $this->assertEquals(0, $stats->getGoalDifference());
    }

    public function test_can_update_team_statistics(): void
    {
        $strength = TeamStrength::fromValue(90);
        $team = Team::create(1, 'Manchester City', $strength);

        $newStats = TeamStatistics::create(9, 3, 3, 0, 0, 8, 2);
        $team->updateStatistics($newStats);

        $stats = $team->getStatistics();
        
        $this->assertEquals(9, $stats->getPoints());
        $this->assertEquals(3, $stats->getPlayed());
        $this->assertEquals(3, $stats->getWins());
        $this->assertEquals(0, $stats->getDraws());
        $this->assertEquals(0, $stats->getLosses());
        $this->assertEquals(8, $stats->getGoalsFor());
        $this->assertEquals(2, $stats->getGoalsAgainst());
        $this->assertEquals(6, $stats->getGoalDifference());
    }

    public function test_updating_statistics_records_domain_event(): void
    {
        $strength = TeamStrength::fromValue(85);
        $team = Team::create(1, 'Chelsea', $strength);

        $newStats = TeamStatistics::create(6, 2, 2, 0, 0, 5, 1);
        $team->updateStatistics($newStats);

        $events = $team->getRecordedEvents();
        
        $this->assertCount(1, $events);
        $this->assertInstanceOf(TeamStatisticsUpdatedEvent::class, $events[0]);
    }

    public function test_team_equality_by_id(): void
    {
        $strength1 = TeamStrength::fromValue(80);
        $strength2 = TeamStrength::fromValue(75);
        
        $team1 = Team::create(1, 'Arsenal', $strength1);
        $team2 = Team::create(1, 'Different Name', $strength2);
        $team3 = Team::create(2, 'Arsenal', $strength1);

        $this->assertTrue($team1->equals($team2)); // Same ID
        $this->assertFalse($team1->equals($team3)); // Different ID
    }

    public function test_team_can_be_converted_to_string(): void
    {
        $strength = TeamStrength::fromValue(82);
        $team = Team::create(1, 'Tottenham', $strength);

        $this->assertEquals('Tottenham', (string) $team);
    }
} 