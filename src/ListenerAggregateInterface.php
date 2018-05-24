<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface ListenerAggregateInterface
{
    /**
     * @return iterable Can be an array, iterator, or generator; each item
     *     returned MUST be callable.
     */
    public function getListenersForEvent(EventInterface $event) : iterable;
}
