<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


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
            $event = $listener($event);
            // @todo Should this be a separate type of dispatcher, or must all dispatchers handle this?
            // @todo This turns the event dispatcher into a fallthrough pipeline, too. Is that OK?
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
