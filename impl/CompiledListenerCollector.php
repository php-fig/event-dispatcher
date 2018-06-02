<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;

class CompiledListenerCollector implements \IteratorAggregate
{
    use ParameterDeriverTrait;

    /**
     * @var \SplPriorityQueue
     */
    protected $listeners;

    public function __construct()
    {
        $this->listeners = new \SplPriorityQueue();
    }

    public function addListener(callable $listener, $priority = 0, string $type = null): void
    {
        if (!$this->isValidListenerType($listener)) {
            throw new \InvalidArgumentException("That listener type is invalid");
        }

        $this->listeners->insert([
            'type'=> $type ?? $this->getParameterType($listener),
            'listener' => $listener
        ], $priority);
    }

    public function addListenerService(string $serviceName, string $methodName, string $type, $priority = 0): void
    {
        // Encode a service call as a colon-delimited string.
        $this->listeners->insert([
            'type'=> $type,
            'listener' => "$serviceName:$methodName"
        ], $priority);
    }

    public function getIterator()
    {
        yield from $this->listeners;
    }

    protected function isValidListenerType(callable $listener) : bool
    {
        // We can't serialize a closure.
        if ($listener instanceof \Closure) {
            return false;
        }
        // String means it's a function name, and that's safe.  Or it's a service call, and that's also safe.
        if (is_string($listener)) {
            return true;
        }
        // This is how we recognize a static method call.
        if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
            return true;
        }
        // Anything else isn't safe to serialize, so reject it.
        return false;
    }

}
