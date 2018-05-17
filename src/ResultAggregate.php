<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

final class ResultAggregate implements ResultAggregateInterface
{
    /**
     * @var EventInterface[]
     */
    private $results = [];

    /**
     * Push a result into the aggregate.
     *
     * @param EventInterface $result
     */
    public function push(EventInterface $result) : void
    {
        $this->results[] = $result;
    }

    /**
     * Retrieve the first result.
     */
    public function first() : EventInterface
    {
        $this->rewind();
        return $this->current();
    }

    /**
     * Retrieve the last result.
     */
    public function last() : EventInterface
    {
        return end($this->results);
    }

    /**
     * @return EventInterface Not type-hinted, due to extending Iterator.
     */
    public function current()
    {
        current($this->results);
    }

    /**
     * @return null|false|string|int
     */
    public function key()
    {
        key($this->results);
    }

    public function next() : void
    {
        next($this->results);
    }

    public function rewind() : void
    {
        reset($this->results);
    }

    public function valid() : bool
    {
        $key = $this->key();
        return null !== $key && false !== $key;
    }
}
