<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


interface DispatcherInterface
{

    public function dispatch(EventInterface $event) : EventInterface;

}
