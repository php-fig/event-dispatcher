<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface ListenerAggregateInterface
{
    /**
     * @return ListenerInterface[]
     */
    public function getListenersForEvent(EventInterface $event) : array;
}
