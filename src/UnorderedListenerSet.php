<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


class UnorderedListenerSet implements ListenerSetInterface, BasicRegistrationInterface
{
    use ParameterDeriverTrait;

    /**
     * @var callable[]
     */
    protected $listeners;

    public function getListenersFor(EventInterface $event) : iterable
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

}
