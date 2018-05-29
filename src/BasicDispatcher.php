<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


class BasicDispatcher implements DispatcherInterface
{
    /**
     * @var ListenerSetInterface
     */
    protected $listeners;

    public function __construct(ListenerSetInterface $listeners)
    {
        $this->listeners = $listeners;
    }

    public function dispatch(EventInterface $event): EventInterface
    {
        foreach ($this->listeners->getListenersFor($event) as $listener) {
            $listener($event);
            if ($event->stopped()) {
                break;
            }
        }
        return $event;
    }
}
