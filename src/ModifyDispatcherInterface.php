<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

/**
 * Defines a dispatcher for modifiable events.
 */
interface ModifyDispatcherInterface
{
    /**
     * Provide all listeners with an event to modify.
     *
     * @param EventInterface $event
     *  The event to modify.
     *
     * @return EventInterface
     *  The event that was passed, now modified by callers.
     */
    public function modify(EventInterface $event) : EventInterface;
}
