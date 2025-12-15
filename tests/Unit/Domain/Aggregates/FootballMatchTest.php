<?php

namespace Tests\Unit\Domain\Aggregates;

use Tests\TestCase;
use App\Domain\Aggregates\FootballMatch;
use App\Domain\ValueObjects\Score;
use App\Domain\Events\MatchPlayedEvent;
use InvalidArgumentException;
use DateTimeImmutable;

class FootballMatchTest extends TestCase
{
    public function test_can_create_match_with_valid_data(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);

        $this->assertEquals(1, $match->getId());
        $this->assertEquals(10, $match->getHomeTeamId());
        $this->assertEquals(15, $match->getAwayTeamId());
        $this->assertEquals(1, $match->getWeek());
        $this->assertFalse($match->isPlayed());
        $this->assertNull($match->getScore());
        $this->assertNull($match->getPlayedAt());
    }

    public function test_cannot_create_match_with_invalid_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Match ID must be positive');

        FootballMatch::create(0, 1, 2, 1);
    }

    public function test_cannot_create_match_with_same_teams(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Home and away teams must be different');

        FootballMatch::create(1, 5, 5, 1);
    }

    public function test_cannot_create_match_with_invalid_week(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Week must be positive');

        FootballMatch::create(1, 1, 2, 0);
    }

    public function test_can_play_match_with_score(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);
        $score = Score::create(2, 1);

        $match->play($score);

        $this->assertTrue($match->isPlayed());
        $this->assertEquals($score, $match->getScore());
        $this->assertInstanceOf(DateTimeImmutable::class, $match->getPlayedAt());
        $this->assertEquals(2, $match->getScore()->getHome());
        $this->assertEquals(1, $match->getScore()->getAway());
    }

    public function test_playing_match_records_domain_event(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);
        $score = Score::create(3, 0);

        $match->play($score);

        $events = $match->getRecordedEvents();
        
        $this->assertCount(1, $events);
        $this->assertInstanceOf(MatchPlayedEvent::class, $events[0]);
        
        $event = $events[0];
        $this->assertEquals(1, $event->getAggregateId());
        $this->assertEquals(10, $event->getHomeTeamId());
        $this->assertEquals(15, $event->getAwayTeamId());
        $this->assertEquals(1, $event->getWeek());
        $this->assertEquals($score, $event->getScore());
    }

    public function test_cannot_play_already_played_match(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);
        $score1 = Score::create(2, 1);
        $score2 = Score::create(3, 0);

        $match->play($score1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Match has already been played');

        $match->play($score2);
    }

    public function test_match_result_determination(): void
    {
        $homeWin = FootballMatch::create(1, 10, 15, 1);
        $homeWin->play(Score::create(3, 1));
        $this->assertEquals('H', $homeWin->getResult());

        $awayWin = FootballMatch::create(2, 10, 15, 1);
        $awayWin->play(Score::create(1, 2));
        $this->assertEquals('A', $awayWin->getResult());

        $draw = FootballMatch::create(3, 10, 15, 1);
        $draw->play(Score::create(1, 1));
        $this->assertEquals('D', $draw->getResult());
    }

    public function test_match_equality_by_id(): void
    {
        $match1 = FootballMatch::create(1, 10, 15, 1);
        $match2 = FootballMatch::create(1, 20, 25, 2);
        $match3 = FootballMatch::create(2, 10, 15, 1);

        $this->assertTrue($match1->equals($match2)); // Same ID
        $this->assertFalse($match1->equals($match3)); // Different ID
    }

    public function test_can_get_match_summary(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);
        $score = Score::create(2, 1);
        $match->play($score);

        $summary = $match->getSummary();
        
        $this->assertIsArray($summary);
        $this->assertEquals(1, $summary['id']);
        $this->assertEquals(10, $summary['home_team_id']);
        $this->assertEquals(15, $summary['away_team_id']);
        $this->assertEquals(1, $summary['week']);
        $this->assertEquals(2, $summary['home_score']);
        $this->assertEquals(1, $summary['away_score']);
        $this->assertTrue($summary['is_played']);
        $this->assertEquals('H', $summary['result']);
    }

    public function test_unplayed_match_summary(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);
        
        $summary = $match->getSummary();
        
        $this->assertIsArray($summary);
        $this->assertEquals(1, $summary['id']);
        $this->assertEquals(10, $summary['home_team_id']);
        $this->assertEquals(15, $summary['away_team_id']);
        $this->assertEquals(1, $summary['week']);
        $this->assertNull($summary['home_score']);
        $this->assertNull($summary['away_score']);
        $this->assertFalse($summary['is_played']);
        $this->assertNull($summary['result']);
        $this->assertNull($summary['played_at']);
    }

    public function test_can_update_match_result(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);
        $originalScore = Score::create(2, 1);
        $newScore = Score::create(3, 0);

        $match->play($originalScore);
        $this->assertEquals(2, $match->getScore()->getHome());
        $this->assertEquals(1, $match->getScore()->getAway());

        $match->updateScore($newScore);
        $this->assertEquals(3, $match->getScore()->getHome());
        $this->assertEquals(0, $match->getScore()->getAway());
    }

    public function test_cannot_update_score_of_unplayed_match(): void
    {
        $match = FootballMatch::create(1, 10, 15, 1);
        $score = Score::create(2, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot update score of unplayed match');

        $match->updateScore($score);
    }
} 