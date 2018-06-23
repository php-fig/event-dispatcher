<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;

use Psr\Event\Dispatcher\EventInterface;

class BasicEvent implements EventInterface
{
    use EventTrait;
}
