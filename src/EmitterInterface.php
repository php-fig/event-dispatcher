<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface EmitterInterface
{
    /**
     * Emit the given event to all attached listeners.
     *
     * @throws EventTypeMismatchExceptionInterface
     */
    public function emit(EventInterface $event) : ResultAggregateInterface;
}
