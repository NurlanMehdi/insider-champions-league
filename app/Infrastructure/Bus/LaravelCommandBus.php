<?php

namespace App\Infrastructure\Bus;

use App\Application\Bus\CommandBusInterface;
use Illuminate\Contracts\Container\Container;

final class LaravelCommandBus implements CommandBusInterface
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatch(object $command): mixed
    {
        $handlerClass = $this->resolveHandlerClass($command);
        $handler = $this->container->make($handlerClass);

        return $handler->handle($command);
    }

    private function resolveHandlerClass(object $command): string
    {
        $commandClass = get_class($command);
        $commandName = class_basename($commandClass);
        
        return str_replace(
            ['App\Application\Commands', $commandName],
            ['App\Application\Handlers\CommandHandlers', $commandName . 'Handler'],
            $commandClass
        );
    }
} 