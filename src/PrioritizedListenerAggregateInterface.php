<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface PrioritizedListenerAggregateInterface extends ListenerAggregateInterface
{
    public function listen(string $eventType, ListenerInterface $listener, int $priority = 1) : void;
}
