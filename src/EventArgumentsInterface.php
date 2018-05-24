<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

/**
 * Provide access to event arguments, but prevent changes to them.
 *
 * Implementations of this interface MUST NOT allow changes to the
 * arguments encapsulated within (with the exception that any argument
 * provided by reference, including objects, can potentially change).
 */
interface EventArgumentsInterface
{
    public function getArguments() : array;

    /**
     * @param mixed $default Default value to return if no matching argument
     *     discovered.
     * @return mixed
     */
    public function getArgument(string $name, $default = null);
}
