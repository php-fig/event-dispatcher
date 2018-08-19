<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;

/**
 * Defines a notifier for message events.
 */
interface MessageNotifierInterface
{
    /**
     * Notify listeners of a message event.
     *
     * This method MAY act asynchronously.  Callers SHOULD NOT
     * assume that any action has been taken when this method
     * returns.
     *
     * @param MessageInterface $event
     *   The event to notify listeners of.
     */
    public function notify(MessageInterface $event) : void;
}
