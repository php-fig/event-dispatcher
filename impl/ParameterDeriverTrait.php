<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;


trait ParameterDeriverTrait
{
    protected function getParameterType($callable) : string
    {
        // We can't type hint $callable as it could be an array, and arrays are not callable. Sometimes. Bah, PHP.

        // This try-catch is only here to keep OCD linters happy about uncaught reflection exceptions.
        try {
            // If the handler has no type on its parameter it is invalid.
            if (is_string($callable) || $callable instanceof \Closure) {
                $reflect = new \ReflectionFunction($callable);
                $params = $reflect->getParameters();
            }
            else if (is_array($callable)) {
                if (is_object($callable[0])) {
                    $reflect = new \ReflectionObject($callable[0]);
                    $params = $reflect->getMethod($callable[1])->getParameters();
                }
                else if (class_exists($callable[0])) {
                    $reflect = new \ReflectionClass($callable[0]);
                    $params = $reflect->getMethod($callable[1])->getParameters();
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
