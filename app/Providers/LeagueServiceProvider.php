<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Application\Services\Interfaces\LeagueApplicationServiceInterface;
use App\Application\Services\LeagueApplicationService;
use App\Application\Services\LeagueStandingsService;
use App\Application\Services\MatchSimulationService;
use App\Application\Services\MatchManagementService;
use App\Application\Services\LeagueSetupService;
use App\Application\Services\WeekCalculationService;

use App\Application\Bus\CommandBusInterface;
use App\Infrastructure\Bus\LaravelCommandBus;

use App\Domain\Repositories\Interfaces\TeamRepositoryInterface;
use App\Domain\Repositories\Interfaces\MatchRepositoryInterface;
use App\Domain\Services\Interfaces\MatchSimulatorInterface;
use App\Domain\Services\Interfaces\FixtureGeneratorInterface;

use App\Domain\Services\Strategies\MatchSimulationStrategyInterface;
use App\Domain\Services\Strategies\RealisticMatchSimulationStrategy;

use App\Domain\Events\DomainEventDispatcherInterface;
use App\Infrastructure\Events\LaravelDomainEventDispatcher;

use App\Infrastructure\Repositories\TeamRepository;
use App\Infrastructure\Repositories\MatchRepository;
use App\Infrastructure\Repositories\TeamStatisticsRepository;
use App\Infrastructure\Mappers\FootballMatchMapper;

use App\Domain\Services\MatchSimulator;
use App\Domain\Services\FixtureGenerator;
use App\Domain\Services\LeaguePredictor;

use App\Domain\Factories\TeamAggregateFactory;
use App\Domain\Specifications\PremierLeagueRulesSpecification;

final class LeagueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->bindCQRSPatterns();
        $this->bindDomainEvents();
        $this->bindRepositories();
        $this->bindDomainServices();
        $this->bindApplicationServices();
        $this->bindInfrastructureServices();
        $this->bindFactoriesAndSpecifications();
        $this->bindStrategies();
    }

    public function boot(): void
    {
    }

    private function bindCQRSPatterns(): void
    {
        $this->app->bind(CommandBusInterface::class, LaravelCommandBus::class);
    }

    private function bindDomainEvents(): void
    {
        $this->app->bind(DomainEventDispatcherInterface::class, LaravelDomainEventDispatcher::class);
    }

    private function bindRepositories(): void
    {
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(MatchRepositoryInterface::class, MatchRepository::class);
    }

    private function bindDomainServices(): void
    {
        $this->app->bind(MatchSimulatorInterface::class, MatchSimulator::class);
        $this->app->bind(FixtureGeneratorInterface::class, FixtureGenerator::class);
        
        $this->app->singleton(LeaguePredictor::class, function ($app) {
            return new LeaguePredictor(
                $app->make(PremierLeagueRulesSpecification::class)
            );
        });
    }

    private function bindApplicationServices(): void
    {
        $this->app->singleton(LeagueStandingsService::class);
        $this->app->singleton(MatchManagementService::class);
        $this->app->singleton(LeagueSetupService::class);
        
        $this->app->singleton(MatchSimulationService::class, function ($app) {
            return new MatchSimulationService(
                $app->make(MatchRepositoryInterface::class),
                $app->make(TeamRepositoryInterface::class),
                $app->make(MatchSimulatorInterface::class),
                $app->make(MatchSimulationStrategyInterface::class)
            );
        });
        
        $this->app->singleton(WeekCalculationService::class, function ($app) {
            return new WeekCalculationService(
                $app->make(MatchRepositoryInterface::class),
                $app->make(PremierLeagueRulesSpecification::class)
            );
        });
        
        $this->app->bind(LeagueApplicationServiceInterface::class, LeagueApplicationService::class);
    }

    private function bindInfrastructureServices(): void
    {
        $this->app->singleton(TeamStatisticsRepository::class);
        $this->app->singleton(FootballMatchMapper::class);
    }

    private function bindFactoriesAndSpecifications(): void
    {
        $this->app->singleton(PremierLeagueRulesSpecification::class);
        $this->app->singleton(TeamAggregateFactory::class);
    }

    private function bindStrategies(): void
    {
        $this->app->bind(MatchSimulationStrategyInterface::class, RealisticMatchSimulationStrategy::class);
    }
}
