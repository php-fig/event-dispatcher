<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;


interface EventSubscriberInterface
{
    public static function getSubscribers() : iterable;
}
