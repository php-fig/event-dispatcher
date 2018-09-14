<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;

/**
 * A Task whose processing may be interrupted when the task is complete.
 *
 * A Processor implementation MUST check to determine if a Task
 * is marked as stopped after each listener is called.  If it is then it should
 * return immediately without calling any further Listeners.
 */
interface StoppableTaskInterface extends TaskInterface
{
    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the Processor to determine if the
     * previous listener halted propagation.
     *
     * @return bool
     *   True if the Task is complete and no further listeners should be called.
     *   False to continue calling listeners.
     */
    public function isPropagationStopped() : bool;
}
