<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

/**
 * Defines a notifying dispatcher.
 */
interface NotifyDispatcherInterface
{
    /**
     * Notify listeners of an event.
     *
     * This method MAY act asynchronously.  Callers SHOULD NOT
     * assume that any action has been taken when this method
     * returns.
     *
     * @param EventInterface $event
     *   The event to notify listeners of.
     */
    public function notify(EventInterface $event) : void;
}
