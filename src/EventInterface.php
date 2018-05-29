<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface EventInterface
{
    /**
     * Provide access to the event arguments, if any.
     *
     * Implementations may have this return null if no event arguments are
     * needed, or if immutable event arguments are unnecessary.
     */
    public function getArguments() : ?EventArgumentsInterface;

    /**
     * Stop event propagation.
     *
     * Once called, when handling returns to the dispatcher, the dispatcher MUST
     * stop calling any remaining listeners and return handling back to the
     * target object.
     */
    public function stopPropagation() : void;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the dispatcher to determine if the
     * previous listener halted propagation.
     */
    public function isStopped() : bool;
}
