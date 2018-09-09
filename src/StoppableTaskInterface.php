<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;

/**
 * Event that can stop propagation to undispatched listeners.
 *
 * A Stoppable Dispatcher implementation MUST check to determine if an Event
 * is marked as stopped after each listener is called.  If it is then it should
 * return immediately without calling any further Listeners.
 */
interface StoppableTaskInterface extends TaskInterface
{
    /**
     * Stop event propagation.
     *
     * Once called, when handling returns to the dispatcher, the dispatcher MUST
     * stop calling any remaining listeners and return handling back to the
     * target object.
     *
     * @return self
     */
    public function stopPropagation() : self;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the dispatcher to determine if the
     * previous listener halted propagation.
     *
     * If stopPropagation() has previously been called then this method MUST
     * return true. If not, it may return true or false as appropriate.
     */
    public function isPropagationStopped() : bool;
}
