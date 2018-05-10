<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


interface RegistrationInterface
{

    public function addListener(callable $listener, string $type = null) : string;

}
