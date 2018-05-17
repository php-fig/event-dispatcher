<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;

trait EventDataTrait
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $isStopped = false;

    /**
     * @var null|mixed
     */
    private $result;

    /**
     * Retrieve any data pertaining to the event. This will be data provided by
     * the object that triggers the event and/or listeners called by the event
     * dispatcher.
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Evolve the event to include a new set of data.
     *
     * MUST return a NEW instance that returns the $data via getData();
     */
    public function withData(array $data) : self
    {
        $event = clone $this;
        $event->data = $data;
        return $event;
    }

    /**
     * Evolve the event such that getData will include a new key with the datum provided.
     *
     * MUST return a NEW instance that includes $key in the data returned via
     * getData(), with the value $datum.
     *
     * @param mixed $datum
     */
    public function with(string $key, $datum) : self
    {
        $data = $this->getData();
        $data[$key] = $datum;
        return $this->withData($data);
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
    public function stopPropagation() : self
    {
        $event = clone $this;
        $event->isStopped = true;
        return $event;
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
