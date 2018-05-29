<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

trait EventTrait
{
    /**
     * @var ?EventArgumentsInterface
     */
    private $arguments;

    /**
     * @var bool
     */
    private $isStopped = false;

    public function getArguments() : ?EventArgumentsInterface
    {
        return $this->arguments;
    }

    /**
     * Stop event propagation.
     *
     * Once called, when handling returns to the dispatcher, the dispatcher MUST
     * stop calling any remaining listeners and return handling back to the
     * target object.
     *
     * MUST return a NEW instance that will cause isStopped to return boolean
     * true.
     */
    public function stopPropagation() : void
    {
        $this->isStopped = true;
    }

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the dispatcher to determine if the
     * previous listener halted propagation.
     */
    public function isStopped() : bool
    {
        return $this->isStopped;
    }
}
