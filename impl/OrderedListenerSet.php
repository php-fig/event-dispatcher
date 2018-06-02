<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;


use Psr\Event\Dispatcher\EventInterface;
use Psr\Event\Dispatcher\ListenerSetInterface;

class OrderedListenerSet implements ListenerSetInterface
{
    use ParameterDeriverTrait;

    /**
     * @var \SplPriorityQueue
     */
    protected $listeners;

    public function __construct()
    {
        $this->listeners = new \SplPriorityQueue();
    }

    public function getListenersFor(EventInterface $event): iterable
    {
        foreach ($this->listeners as $listener) {
            if ($event instanceof $listener['type']) {
                yield $listener['listener'];
            }
        }
    }

    public function addListener(callable $listener, $priority = 0, string $type = null): void
    {
        $this->listeners->insert([
            'type'=> $type ?? $this->getParameterType($listener),
            'listener' => $listener
        ], $priority);
    }

}
