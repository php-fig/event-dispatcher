<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


use Psr\Container\ContainerInterface;

abstract class CompiledListenerSetBase implements ListenerSetInterface
{
    protected $container;

    protected $listeners = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->init();
    }

    public function getListenersFor(EventInterface $event): iterable
    {
        foreach ($this->listeners as $listener) {
            if ($event instanceof $listener['type']) {
                yield $listener['listener'];
            }
        }
    }

    /**
     * Initializes all listeners.
     *
     * This will get called automatically by the constructor. Base classes should implement it to define all registered
     * listeners.
     */
    abstract protected function init() : void;
}
