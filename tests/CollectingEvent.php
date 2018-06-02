<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;


use Crell\EventDispatcher\BasicEvent;
use Crell\EventDispatcher\EventTrait;

class CollectingEvent extends BasicEvent
{
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
