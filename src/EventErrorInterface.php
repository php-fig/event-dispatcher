<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;

/**
 * An object that contains information about an error triggered by Event handling.
 */
interface EventErrorInterface
{
    /**
     * Returns the event that triggered this error condition.
     *
     * @return EventInterface
     */
    public function getEvent() : EventInterface;

    /**
     * Returns the throwable (Exception or Error) that triggered this error condition.
     *
     * @return \Throwable
     */
    public function getThrowable() : \Throwable;

    /**
     * Returns the callable from which the exception or error was generated.
     *
     * @return callable
     */
    public function getListener() : callable;
}
