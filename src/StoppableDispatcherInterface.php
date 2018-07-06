<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

/**
 * Defines a dispatcher for stoppable events.
 */
interface StoppableDispatcherInterface
{
    /**
     * Provides all listeners an event to respond to.
     *
     * @param StoppableEventInterface $event
     *   The event to pass to listeners.
     * @return StoppableEventInterface
     *   The event that was passed, now modified.
     */
    public function intercept(StoppableEventInterface $event) : StoppableEventInterface;
}
