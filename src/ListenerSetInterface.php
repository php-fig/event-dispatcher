<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

interface ListenerSetInterface
{
    public function getListenersFor(EventInterface $event): \Generator;
}
