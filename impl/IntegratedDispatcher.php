<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;


use Psr\Event\Dispatcher\BasicRegistrationInterface;
use Psr\Event\Dispatcher\DispatcherInterface;
use Psr\Event\Dispatcher\EventInterface;
use Psr\Event\Dispatcher\ListenerSetInterface;

class IntegratedDispatcher implements DispatcherInterface, BasicRegistrationInterface, ListenerSetInterface
{

    protected $listeners;

    public function __construct(ListenerSetInterface $listeners = null)
    {
        $this->listeners = $listeners ?? new UnorderedListenerSet();
    }

    public function dispatch(EventInterface $event): EventInterface
    {
        foreach ($this->getListenersFor($event) as $listener) {
            $listener($event);
            if ($event->stopped()) {
                break;
            }
        }

        return $event;
    }

    public function addListener(callable $listener, string $type = null): void
    {
        $this->listeners->addListener($listener, $type);
    }

    public function getListenersFor(EventInterface $event) : iterable
    {
        yield from $this->listeners->getListenersFor($event);
    }
}
