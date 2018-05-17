<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

use Psr\Container\ContainerInterface;

final class LazyListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ?string
     */
    private $method = null;

    /**
     * @var string
     */
    private $service;

    public function __construct(ContainerInterface $container, string $service, string $method = null)
    {
        $this->container = $container;
        $this->service = $service;
        $this->method = $method;
    }

    /**
     * {@inheritDoc}
     */
    public function listen(EventInterface $event) : EventInterface
    {
        $listener = $this->getCallableListener(
            $this->container->get($this->service)
        );

        return $listener($event);
    }

    /**
     * @var mixed $service Service retrieved from container.
     */
    private function getCallableListener($service) : callable
    {
        // Not an object, and not callable: invalid
        if (! is_object($service) && ! is_callable($service)) {
            throw Exception\InvalidListenerException::forNonCallableService($service);
        }

        // Not an object, but callable: return verbatim
        if (! is_object($service) && is_callable($service)) {
            return $service;
        }

        // Object, no method present, and implements ListenerInterface: return
        // its listen() method
        if (! $this->method && $service instanceof ListenerInterface) {
            return [$listener, 'listen'];
        }

        // Object, no method present, not a listener, and not callable: invalid
        if (! $this->method && ! is_callable($service)) {
            throw Exception\InvalidListenerException::forNonCallableInstance($service);
        }

        // Object, no method present, not a listener, but callable: return verbatim
        if (! $this->method && is_callable($service)) {
            return $service;
        }

        $callback = [$service, $this->method];

        // Object, method present, but method is not callable: invalid
        if (! is_callable($callback)) {
            throw Exception\InvalidListenerException::forNonCallableInstanceMethod($service, $method);
        }

        // Object with method as callback
        return $callback;
    }
}
