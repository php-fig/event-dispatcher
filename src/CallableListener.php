<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

final class CallableListener implements ListenerInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function listen(EventInterface $event) : EventInterface
    {
        return ($this->callback)($event);
    }
}
