<?php

namespace Psr\Event\Dispatcher;

use Psr\Container\ContainerInterface;

function lazyListener(
    ContainerInterface $container,
    string $service,
    string $method = null
) : LazyListener {
    return new LazyListener($container, $service, $method);
}
