<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;


use Psr\Container\ContainerInterface;
use Psr\Event\Dispatcher\EventInterface;

class ServiceListenerSet extends OrderedListenerSet
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    public function addListenerService(string $serviceName, string $methodName, string $type, $priority = 0): void
    {
        // Fun fact: We cannot auto-detect the listener target type from a container without instantiating it, which
        // defeats the purpose of a service registration. Therefore this method requires an explicit event type. Also,
        // the wrapping listener must listen to just EventInterface.  The explicit $type means it will still get only
        // the right event type, and the real listener can still type itself properly.
        $container = $this->container;
        $listener = function(EventInterface $event) use ($serviceName, $methodName, $container) {
            $container->get($serviceName)->$methodName($event);
        };

        $this->addListener($listener, $priority, $type);
    }

}
