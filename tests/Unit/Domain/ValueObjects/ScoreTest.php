<?php

namespace Tests\Unit\Domain\ValueObjects;

use Tests\TestCase;
use App\Domain\ValueObjects\Score;
use InvalidArgumentException;

class ScoreTest extends TestCase
{
    public function test_can_create_score_with_valid_values(): void
    {
        $score = Score::create(2, 1);

        $this->assertEquals(2, $score->getHome());
        $this->assertEquals(1, $score->getAway());
    }

    public function test_can_create_score_with_zero_values(): void
    {
        $score = Score::create(0, 0);

        $this->assertEquals(0, $score->getHome());
        $this->assertEquals(0, $score->getAway());
    }

    public function test_cannot_create_score_with_negative_home_score(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scores cannot be negative');

        Score::create(-1, 2);
    }

    public function test_cannot_create_score_with_negative_away_score(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scores cannot be negative');

        Score::create(2, -1);
    }

    public function test_cannot_create_score_with_unrealistic_home_score(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scores cannot exceed 50');

        Score::create(51, 1);
    }

    public function test_cannot_create_score_with_unrealistic_away_score(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scores cannot exceed 50');

        Score::create(1, 51);
    }

    public function test_can_create_score_with_maximum_valid_values(): void
    {
        $score = Score::create(50, 50);

        $this->assertEquals(50, $score->getHome());
        $this->assertEquals(50, $score->getAway());
    }

    public function test_score_equality(): void
    {
        $score1 = Score::create(3, 1);
        $score2 = Score::create(3, 1);
        $score3 = Score::create(2, 1);

        $this->assertTrue($score1->equals($score2));
        $this->assertFalse($score1->equals($score3));
    }

    public function test_score_result_determination(): void
    {
        $homeWin = Score::create(3, 1);
        $this->assertEquals('home_win', $homeWin->getResult());

        $awayWin = Score::create(1, 3);
        $this->assertEquals('away_win', $awayWin->getResult());

        $draw = Score::create(2, 2);
        $this->assertEquals('draw', $draw->getResult());
    }

    public function test_score_string_representation(): void
    {
        $score = Score::create(2, 1);
        
        $this->assertEquals('2-1', $score->toString());
    }

    public function test_score_immutability(): void
    {
        $score = Score::create(2, 1);
        
        // Scores should be immutable - any change should create new instance
        $newScore = Score::create(3, 1);
        
        $this->assertNotSame($score, $newScore);
        $this->assertEquals(2, $score->getHome()); // Original unchanged
        $this->assertEquals(3, $newScore->getHome());
    }

    public function test_can_create_not_played_score(): void
    {
        $score = Score::notPlayed();
        
        $this->assertEquals(0, $score->getHome());
        $this->assertEquals(0, $score->getAway());
        $this->assertEquals('draw', $score->getResult());
    }

    /**
     * @dataProvider scoreProvider
     */
    public function test_various_score_scenarios(int $home, int $away, string $expectedResult): void
    {
        $score = Score::create($home, $away);
        
        $this->assertEquals($expectedResult, $score->getResult());
    }

    public static function scoreProvider(): array
    {
        return [
            [0, 0, 'draw'],
            [1, 0, 'home_win'],
            [0, 1, 'away_win'],
            [5, 2, 'home_win'],
            [2, 5, 'away_win'],
            [3, 3, 'draw'],
            [10, 10, 'draw'],
        ];
    }
} 