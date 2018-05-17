<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface ListenerInterface
{
    public function listen(EventInterface $event) : EventInterface;
}
