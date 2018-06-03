<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;

use Crell\EventDispatcher\BasicDispatcher;
use Crell\EventDispatcher\BasicEvent;
use Crell\EventDispatcher\EventSubscriberInterface;
use Crell\EventDispatcher\EventTrait;
use Crell\EventDispatcher\IntegratedDispatcher;
use Crell\EventDispatcher\OrderedListenerSet;
use Crell\EventDispatcher\RelativeListenerSet;
use Crell\EventDispatcher\ServiceListenerSet;
use Crell\EventDispatcher\SubscriberListenerSet;
use PHPUnit\Framework\TestCase;
use Psr\Event\Dispatcher\EventInterface;

class MockSubscriber implements EventSubscriberInterface
{
    public function a(CollectingEvent $event) : void
    {
        $event->add('A');
    }
    public function b(CollectingEvent $event) : void
    {
        $event->add('B');
    }
    public function c(CollectingEvent $event) : void
    {
        $event->add('C');
    }
    public function d(CollectingEvent $event) : void
    {
        $event->add('D');
    }
    public function e(CollectingEvent $event) : void
    {
        $event->add('E');
    }

    public function f(NoEvent $event) : void
    {
        $event->add('F');
    }

    public static function getSubscribers(): iterable
    {
        return [
            ['method' => 'a', 'type' => CollectingEvent::class, 'priority' => 10],  // Specify everything.
            ['method' => 'b', 'priority' => 9], // Both type and prioirty can be omitted.
            'd',  // Just list the method, the rest is default/autodetected. The most common case.
            ['method' => 'c', 'type' => CollectingEvent::class], // Both type and prioirty can be omitted.
            ['e' => -5], // You can short-case the method/priority, but not the type. Use the full version for that.
            'f', // This one shouldn't fire.
        ];
    }
}

class SubscriberTest extends TestCase
{

    function testSubscriberSet()
    {
        $container = new MockContainer();

        $subscriber = new MockSubscriber();

        $container->addService('subscriber', $subscriber);

        $set = new SubscriberListenerSet($container);
        $set->addSubscriber(MockSubscriber::class, 'subscriber');
        $d = new BasicDispatcher($set);

        $event = new CollectingEvent();
        $d->dispatch($event);

        $this->assertEquals('ABCDE', implode($event->result()));
    }
}
