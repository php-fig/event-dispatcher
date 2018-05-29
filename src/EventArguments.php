<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

use InvalidArgumentException;

/**
 * Default implementation for immutable event arguments.
 */
class EventArguments implements EventArgumentsInterface
{
    /**
     * @var array
     */
    private $arguments;

    public function __construct(array $arguments)
    {
        if (empty($arguments)
            || array_keys($arguments) === range(0, count($arguments) - 1)
        ) {
            throw new InvalidArgumentException(sprintf(
                '%s only accepts associative arrays to its constructor',
                __CLASS__
            ));
        }

        $this->arguments = $arguments;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }

    /**
     * @param mixed $default Default value to return if no matching argument
     *     discovered.
     * @return mixed
     */
    public function getArgument(string $name, $default = null)
    {
        if (! array_key_exists($name, $this->arguments)) {
            return $default;
        }
        return $this->arguments[$name];
    }
}
