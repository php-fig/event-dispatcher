<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


class BasicDispatcher implements DispatcherInterface, RegistrationInterface
{

    /**
     * @var array
     */
    protected $listeners = [];

    public function dispatch(EventInterface $event): EventInterface
    {
        $listeners = $this->listeners[get_class($event)] ?? [];

        /** @var callable $listener */
        foreach ($listeners as $listener) {
            $event = $listener($event);
            // @todo Should this be a separate type of dispatcher, or must all dispatchers handle this?
            // @todo This turns the event dispatcher into a fallthrough pipeline, too. Is that OK?
            if ($event instanceof InterruptableEventInterface && $event->stopped()) {
                break;
            }
        }

        return $event;
    }

    public function addListener(callable $listener, string $type = null): void
    {
        // @todo This assumes type-based registration. We should benchmark this code to see if it's fast enough.
        if ($type == null) {
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
        }

        $this->listeners[$type][] = $listener;
    }


}
