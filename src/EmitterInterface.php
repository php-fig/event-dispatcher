<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface EmitterInterface
{
    /**
     * Emit the given event to all attached listeners for that event.
     */
    public function emit(EventInterface $event) : void;
}
