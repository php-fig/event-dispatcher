<?php

namespace Psr\Event\Dispatcher;

function listener(callable $callback) : CallableListener
{
    return new CallableListener($callback);
}
