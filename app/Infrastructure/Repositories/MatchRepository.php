<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Aggregates\FootballMatch;
use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Infrastructure\Mappers\FootballMatchMapper;
use App\Models\FootballMatch as MatchModel;

final class MatchRepository implements MatchRepositoryInterface
{
    private FootballMatchMapper $mapper;
    
    public function __construct(FootballMatchMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function findById(int $id): FootballMatch
    {
        $matchModel = MatchModel::with(['homeTeam', 'awayTeam'])->findOrFail($id);
        return $this->mapper->toDomainEntity($matchModel);
    }

    public function findByWeek(int $week): array
    {
        $matches = MatchModel::with(['homeTeam', 'awayTeam'])
            ->where('week', $week)
            ->get();

        return $matches->map(function ($matchModel) {
            return $this->mapper->toDomainEntity($matchModel);
        })->toArray();
    }

    public function findAll(): array
    {
        $matches = MatchModel::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->get();

        return $matches->map(function ($matchModel) {
            return $this->mapper->toDomainEntity($matchModel);
        })->toArray();
    }

    public function save(FootballMatch $footballMatch): void
    {
        $matchModel = $this->findOrCreateModel($footballMatch->getId());

        $this->mapToModel($footballMatch, $matchModel);
        $matchModel->save();
    }

    public function delete(FootballMatch $footballMatch): void
    {
        MatchModel::destroy($footballMatch->getId());
    }

    public function deleteAll(): void
    {
        MatchModel::query()->delete();
    }

    private function findOrCreateModel(int $id): MatchModel
    {
        return MatchModel::find($id) ?: new MatchModel();
    }

    private function mapToModel(FootballMatch $footballMatch, MatchModel $matchModel): void
    {
        $matchModel->id = $footballMatch->getId();
        $matchModel->home_team_id = $footballMatch->getHomeTeamId();
        $matchModel->away_team_id = $footballMatch->getAwayTeamId();
        $matchModel->week = $footballMatch->getWeek();

        if ($footballMatch->isPlayed() && $footballMatch->getScore()) {
            $score = $footballMatch->getScore();
            $matchModel->home_score = $score->getHome();
            $matchModel->away_score = $score->getAway();
            $matchModel->is_played = true;
            $matchModel->played_at = $footballMatch->getPlayedAt()?->format('Y-m-d H:i:s');
        } else {
            $matchModel->home_score = null;
            $matchModel->away_score = null;
            $matchModel->is_played = false;
            $matchModel->played_at = null;
        }
    }
} 