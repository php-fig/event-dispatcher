<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

/**
 * Event that can stop propagation to undispatched listeners.
 *
 * A Stoppable Dispatcher implementation MUST check to determine if an Event
 * is marked as stopped after each listener is called.  If it is then it should
 * return immediately without calling any further Listeners.
 */
interface StoppableEventInterface extends EventInterface
{
    /**
     * Stop event propagation.
     *
     * Once called, when handling returns to the dispatcher, the dispatcher MUST
     * stop calling any remaining listeners and return handling back to the
     * target object.
     *
     * @param bool $stop
     *   True (default) to flag the event as stopped. False to cancel the stoppage.
     *
     * @return self
     */
    public function stopPropagation($stop = true) : self;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the dispatcher to determine if the
     * previous listener halted propagation.
     */
    public function isStopped() : bool;
}
