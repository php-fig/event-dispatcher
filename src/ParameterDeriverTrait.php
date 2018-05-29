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
            $reflect = new \ReflectionFunction($listener);
            $params = $reflect->getParameters();

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
