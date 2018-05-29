<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface AttachableListenerAggregateInterface extends ListenerAggregateInterface
{
    /**
     * Attach a listener for a given event type.
     *
     * The event type should be a specific EventInterface implementation
     * or extension. When an emitter emits a specific EventInterface instance,
     * it will trigger any listener that has specified that type or its subtype.
     */
    public function listen(string $eventType, callable $listener) : void;
}
