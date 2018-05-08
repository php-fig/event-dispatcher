<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


interface DeferredDispatcherInterface
{

    public function dispatchDeferred(EventInterface $event) : void;

}
