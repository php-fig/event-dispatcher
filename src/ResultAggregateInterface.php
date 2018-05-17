<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

use Iterator;

interface ResultAggregateInterface extends Iterator
{
    /**
     * Retrieve the event returned by the first listener.
     */
    public function first() : EventInterface;

    /**
     * Retrieve the event returned by the last listener.
     */
    public function last() : EventInterface;
}
