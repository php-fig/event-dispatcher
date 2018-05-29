<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface PositionableListenerAggregateInterface extends AttachableListenerAggregateInterface
{
    public function listenAfter(string $listenerTypeToAppend, string $eventType, callable $newListener) : void;
    public function listenBefore(string $listenerTypeToPrepend, string $eventType, callable $newListener) : void;
}
