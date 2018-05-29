<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


class RelativeListenerSet implements ListenerSetInterface
{
    use ParameterDeriverTrait;

    /**
     * @var array
     */
    protected $listeners = [];
    
    public function getListenersFor(EventInterface $event) : iterable
    {
        foreach ($this->listeners as $type => $listeners) {
            if ($event instanceof $type) {
                yield from $listeners;
            }
        }
    }

    public function addListener(callable $listener, string $type = null) : string
    {
        $type = $type ?? $this->getParameterType($listener);

        $id = uniqid();

        $this->listeners[$type][$id] = $listener;

        return $id;
    }

    public function addListenerBefore(callable $listener, string $id, $type = null) : string
    {
        $type = $type ?? $this->getParameterType($listener);

        if (!array_key_exists($id, $this->listeners[$type])) {
            throw new \RuntimeException('No listener to compare to');
        }

        // @todo There is very likely a better way to do this. I hope, anyway...
        $keys = array_keys($this->listeners[$type]);
        $index = array_search($id, $keys);
        $before = array_slice($this->listeners[$type], 0, $index);
        $after = array_slice($this->listeners[$type], $index);

        $newId = uniqid();

        $this->listeners[$type] = array_merge($before, [$newId => $listener], $after);

        return $newId;
    }

    public function addListenerAfter(callable $listener, string $id, $type = null) : string
    {
        $type = $type ?? $this->getParameterType($listener);

        if (!array_key_exists($id, $this->listeners[$type])) {
            throw new \RuntimeException('No listener to compare to');
        }

        // @todo There is very likely a better way to do this. I hope, anyway...
        $keys = array_keys($this->listeners[$type]);
        $index = array_search($id, $keys) + 1;
        $before = array_slice($this->listeners[$type], 0, $index);
        $after = array_slice($this->listeners[$type], $index);

        $newId = uniqid();

        $this->listeners[$type] = array_merge($before, [$newId => $listener], $after);

        return $newId;
    }
}
