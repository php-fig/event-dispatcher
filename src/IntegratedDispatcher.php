<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


class IntegratedDispatcher implements DispatcherInterface, BasicRegistrationInterface, ListenerSetInterface
{

    /**
     * @var array
     */
    protected $listeners = [];

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

    public function getListenersFor(EventInterface $event) : \Generator
    {
        foreach ($this->listeners as $type => $listeners) {
            if ($event instanceof $type) {
                yield from $listeners;
            }
        }
    }

    public function addListener(callable $listener, string $type = null): void
    {
        // @todo This assumes type-based registration. We should benchmark this code to see if it's fast enough.
        $type = $type ?? $this->getParameterType($listener);

        $this->listeners[$type][] = $listener;
    }

    protected function getParameterType(callable $listener) : string
    {
        // This try-catch is only here to keep OCD linters happy about uncaught reflection exceptions.
        try {
            // If the handler has no type on its parameter it is invalid.
            $reflect = new \ReflectionFunction($listener);
            $params = $reflect->getParameters();

            $rType =$params[0]->getType();
            if ($rType == null) {
                throw new \InvalidArgumentException('Listeners must declare an object type they can accept.');
            }
            $type = $rType->getName();
        }
        catch (\ReflectionException $e) {
            throw new \RuntimeException('Type error registering listener.', 0, $e);
        }

        return $type;
    }


}
