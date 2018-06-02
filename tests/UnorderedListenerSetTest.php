<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;

use PHPUnit\Framework\TestCase;
use Psr\Event\Dispatcher\BasicEvent;
use Psr\Event\Dispatcher\EventInterface;
use Psr\Event\Dispatcher\EventTrait;
use Psr\Event\Dispatcher\IntegratedDispatcher;

interface FancyEventInterface {}

class EventOne extends BasicEvent {}

class EventTwo extends EventOne implements FancyEventInterface {}

class EventThree implements EventInterface, FancyEventInterface {
    use EventTrait;
}


class UnorderedListenerSetTest extends TestCase
{
    function test_unordered_listener_set()
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
}
