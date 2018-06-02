<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;


use Psr\Event\Dispatcher\EventInterface;

trait EventTrait
{
    protected $stop = false;

    public function stopPropagation(bool $stop = true) : EventInterface
    {
        $this->stop = $stop;
    }

    public function stopped() : bool
    {
        return $this->stop;
    }

}
