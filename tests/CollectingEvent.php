<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;


use Psr\Event\Dispatcher\EventInterface;
use Psr\Event\Dispatcher\EventTrait;

class CollectingEvent implements EventInterface
{
    use EventTrait;

    protected $out = [];

    public function add(string $val) : void
    {
        $this->out[] = $val;
    }

    public function result() : array
    {
        return $this->out;
    }

}
