<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


trait EventTrait
{
    protected $stop = false;

    public function stopPropagation(bool $stop = true) : void
    {
        $this->stop = $stop;
    }

    public function stopped() : bool
    {
        return $this->stop;
    }

}
