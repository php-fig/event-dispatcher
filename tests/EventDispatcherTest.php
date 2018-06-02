<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;

use PHPUnit\Framework\TestCase;
use Psr\Event\Dispatcher\BasicDispatcher;
use Psr\Event\Dispatcher\BasicEvent;
use Psr\Event\Dispatcher\EventInterface;
use Psr\Event\Dispatcher\EventTrait;
use Psr\Event\Dispatcher\IntegratedDispatcher;
use Psr\Event\Dispatcher\OrderedListenerSet;
use Psr\Event\Dispatcher\RelativeListenerSet;
use Psr\Event\Dispatcher\ServiceListenerSet;

interface FancyEventInterface {}

class EventOne extends BasicEvent {}

class EventTwo extends EventOne implements FancyEventInterface {}

class EventThree implements EventInterface, FancyEventInterface {
    use EventTrait;
}


class EventDispatcherTest extends TestCase
{
    function testUnorderedListnerSet()
    {
        $d = new IntegratedDispatcher();

        $counter = new class {
            protected $counts = [];
            public function inc(string $key) {
                $this->counts[$key] = isset($this->counts[$key]) ? $this->counts[$key] + 1 : 1;;
            }

            public function countOf(string $key) {
                return isset($this->counts[$key]) ? $this->counts[$key] : 0;
            }
        };

        // This should fire twice.
        $d->addListener(function (EventOne $e) use ($counter) {
            $counter->inc('A');
        });
        // This should fire once.
        $d->addListener(function (EventTwo $e) use ($counter) {
            $counter->inc('B');
        });
        // This should fire once.
        $d->addListener(function (EventThree $e) use ($counter) {
            $counter->inc('C');
        });
        // This should fire twice.
        $d->addListener(function (FancyEventInterface $e) use ($counter) {
            $counter->inc('D');
        });
        // This should never fire, nor error.
        $d->addListener(function (EventNone $e) use ($counter) {
            $counter->inc('E');
        });
        // This should never fire, nor error.
        $d->addListener(function (EventNone $e) use ($counter) {
            $counter->inc('F');
        }, EventNone::class);

        $d->dispatch(new EventOne());
        $d->dispatch(new EventTwo());
        $d->dispatch(new EventThree());

        $this->assertEquals(2, $counter->countOf('A'));
        $this->assertEquals(1, $counter->countOf('B'));
        $this->assertEquals(1, $counter->countOf('C'));
        $this->assertEquals(2, $counter->countOf('D'));
        $this->assertEquals(0, $counter->countOf('E'));
        $this->assertEquals(0, $counter->countOf('F'));
    }

    function testOrderedListnerSet()
    {
        $set = new OrderedListenerSet();
        $d = new BasicDispatcher($set);

        $out = [];

        $set->addListener(function (EventOne $e) use (&$out) {
            $out[] = 'E';
        }, 80);
        $set->addListener(function (EventOne $e) use (&$out) {
            $out[] = 'R';
        }, 90);
        $set->addListener(function (EventOne $e) use (&$out) {
            $out[] = 'L';
        }, 70);
        $set->addListener(function (EventOne $e) use (&$out) {
            $out[] = 'C';
        }, 100);
        $set->addListener(function (EventOne $e) use (&$out) {
            $out[] = 'L';
        }, 70);

        $d->dispatch(new EventOne());

        $this->assertEquals('CRELL', implode($out));
    }

    function testRelativeListenerSet()
    {
        $set = new RelativeListenerSet();
        $d = new BasicDispatcher($set);

        $out = [];

        $e = $set->addListener(function (EventOne $e) use (&$out) {
            $out[] = 'E';
        });
        $r = $set->addListenerBefore(function (EventOne $e) use (&$out) {
            $out[] = 'R';
        }, $e);
        $l1 = $set->addListenerAfter(function (EventOne $e) use (&$out) {
            $out[] = 'L';
        }, $e);
        $c = $set->addListenerBefore(function (EventOne $e) use (&$out) {
            $out[] = 'C';
        }, $r);
        $l2 = $set->addListenerAfter(function (EventOne $e) use (&$out) {
            $out[] = 'L';
        }, $l1);

        $d->dispatch(new EventOne());

        $this->assertEquals('CRELL', implode($out));
    }

    function testServiceLisenerSet()
    {
        $container = new MockContainer();

        $container->addService('A', new class {
            public function listen(CollectingEvent $event) {
                $event->add('A');
            }
        });
        $container->addService('B', new class {
            public function listen(CollectingEvent $event) {
                $event->add('B');
            }
        });
        $container->addService('C', new class {
            public function listen(CollectingEvent $event) {
                $event->add('C');
            }
        });
        $container->addService('R', new class {
            public function listen(CollectingEvent $event) {
                $event->add('R');
            }
        });
        $container->addService('E', new class {
            public function listen(CollectingEvent $event) {
                $event->add('E');
            }
        });
        $container->addService('L', new class {
            public function hear(CollectingEvent $event) {
                $event->add('L');
            }
        });

        $set = new ServiceListenerSet($container);
        $d = new BasicDispatcher($set);

        $set->addListenerService('L', 'hear', CollectingEvent::class, 70);
        $set->addListenerService('E', 'listen', CollectingEvent::class, 80);
        $set->addListenerService('C', 'listen', CollectingEvent::class, 100);
        $set->addListenerService('L', 'hear', CollectingEvent::class); // Defaults to 0
        $set->addListenerService('R', 'listen', CollectingEvent::class, 90);

        $event = new CollectingEvent();
        $d->dispatch($event);

        $this->assertEquals('CRELL', implode($event->result()));
    }
}
