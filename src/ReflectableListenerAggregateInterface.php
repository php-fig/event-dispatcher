<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface ReflectableListenerAggregateInterface extends ListenerAggregateInterface
{
    /**
     * Attach a listener
     *
     * If no $eventType is provided, reflects the first argument of the $listener
     * to determine the type it accepts.
     *
     * When an emitter emits a specific EventInterface instance, it will
     * trigger any listener that has specified that type or its subtype.
     */
    public function listen(callable $listener, string $eventType = null) : void;
}
