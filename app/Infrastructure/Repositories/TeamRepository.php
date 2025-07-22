<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Aggregates\Team;
use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\ValueObjects\TeamStrength;
use App\Models\Team as TeamModel;

final class TeamRepository implements TeamRepositoryInterface
{
    private TeamStatisticsRepository $statisticsRepository;

    public function __construct(TeamStatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function findById(int $id): Team
    {
        $teamModel = TeamModel::findOrFail($id);
        return $this->toDomainEntity($teamModel);
    }

    public function findAll(): array
    {
        $teams = TeamModel::all();
        
        return $teams->map(function ($teamModel) {
            return $this->toDomainEntity($teamModel);
        })->toArray();
    }

    public function save(Team $team): void
    {
        $teamModel = $this->findOrCreateModel($team->getId());

        $teamModel->id = $team->getId();
        $teamModel->name = $team->getName();
        $teamModel->strength = $team->getStrength()->getValue();
        $teamModel->logo = $team->getLogo();
        
        $teamModel->save();
    }

    public function delete(Team $team): void
    {
        TeamModel::destroy($team->getId());
    }

    private function toDomainEntity(TeamModel $teamModel): Team
    {
        $team = Team::create(
            $teamModel->id,
            $teamModel->name,
            TeamStrength::fromValue($teamModel->strength),
            $teamModel->logo
        );

        $statistics = $this->statisticsRepository->calculateStatistics($teamModel);
        $team->updateStatistics($statistics);
        
        return $team;
    }

    private function findOrCreateModel(int $id): TeamModel
    {
        return TeamModel::find($id) ?: new TeamModel();
    }
} 