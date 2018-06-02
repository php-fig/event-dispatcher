<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


trait ParameterDeriverTrait
{
    protected function getParameterType(callable $listener) : string
    {
        // This try-catch is only here to keep OCD linters happy about uncaught reflection exceptions.
        try {
            // If the handler has no type on its parameter it is invalid.
            if (is_string($listener) || $listener instanceof \Closure) {
                $reflect = new \ReflectionFunction($listener);
                $params = $reflect->getParameters();
            }
            else if (is_array($listener)) {
                if (is_object($listener[0])) {
                    $reflect = new \ReflectionObject($listener[0]);
                    $params = $reflect->getMethod($listener[1])->getParameters();
                }
                else if (class_exists($listener[0])) {
                    $reflect = new \ReflectionClass($listener[0]);
                    $params = $reflect->getMethod($listener[1])->getParameters();
                }
            }
            $rType =$params[0]->getType();
            if ($rType == null) {
                throw new \InvalidArgumentException('Listeners must declare an object type they can accept.');
            }
            $type = $rType->getName();
        }
        catch (\ReflectionException $e) {
            throw new \RuntimeException('Type error registering listener.', 0, $e);
        }

        return $type;
    }
}
