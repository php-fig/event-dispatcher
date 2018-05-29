<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


interface EventInterface
{
    public function stopPropagation(bool $stop = true) : void;

    public function stopped() : bool;
}
