<?php
declare(strict_types=1);

namespace Crell\EventDispatcher;


class SubscriberListenerSet extends ServiceListenerSet
{

    public function addSubscriber(string $class, string $serviceName) : void
    {
        // @todo Exception on classes that don't implement EventSubscriberInterface

        /** @var array $subscribers */
        $subscribers = $class::getSubscribers();

        /** @var array $subscriber */
        foreach ($subscribers as $subscriber) {
            if (is_string($subscriber)) {
                $subscriber = [
                    'method' => $subscriber,
                    'type' => $this->getParameterType([$class, $subscriber]),
                    'priority' => 0,
                ];
            }
            elseif (is_array($subscriber) && count($subscriber) == 1) {
                $subscriber = [
                    'method' => key($subscriber),
                    'type' => $this->getParameterType([$class, key($subscriber)]),
                    'priority' => current($subscriber),
                ];
            }
            elseif (is_array($subscriber) && isset($subscriber['method'])) {
                $subscriber['priority'] = $subscriber['priority'] ?? 0;
                $subscriber['type'] = $subscriber['type'] ?? $this->getParameterType([$class, $subscriber['method']]);
            }
            else {
                throw new \InvalidArgumentException('Subscriber entry must be a method name or definition array.');
            }

            // @todo Error checking to confirm that the method does actually exist on the class.

            $this->addListenerService($serviceName, $subscriber['method'], $subscriber['type'], $subscriber['priority']);
        }
    }
}
