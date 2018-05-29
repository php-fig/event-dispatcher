<?php
declare(strict_types=1);

namespace Tester;

use Psr\Event\Dispatcher\BasicDispatcher;
use Psr\Event\Dispatcher\BasicEvent;
use Psr\Event\Dispatcher\EventTrait;
use Psr\Event\Dispatcher\IntegratedDispatcher;
use Psr\Event\Dispatcher\EventInterface;
use Psr\Event\Dispatcher\OrderedListenerSet;
use Psr\Event\Dispatcher\RelativeListenerSet;

require_once 'vendor/autoload.php';

interface FancyEventInterface {}

class EventOne extends BasicEvent {}

class EventTwo extends EventOne implements FancyEventInterface {}

class EventThree implements EventInterface, FancyEventInterface {
    use EventTrait;
}

function test_unordered_listener_set()
{
    $d = new IntegratedDispatcher();

    // This should fire twice.
    $d->addListener(function (EventOne $e) {
        println('A', get_class($e));
    });
    // This should fire once.
    $d->addListener(function (EventTwo $e) {
        println('B', get_class($e));
    });
    // This should fire once.
    $d->addListener(function (EventThree $e) {
        println('C', get_class($e));
    });
    // This should fire twice.
    $d->addListener(function (FancyEventInterface $e) {
        println('D', get_class($e));
    });
    // This should never fire, nor error.
    $d->addListener(function (EventNone $e) {
        println('E', get_class($e));
    });
    // This should never fire, nor error.
    $d->addListener(function (EventNone $e) {
        println('F', get_class($e));
    }, EventNone::class);

    $d->dispatch(new EventOne());
    $d->dispatch(new EventTwo());
    $d->dispatch(new EventThree());
}

function test_ordered_listener_set()
{
    $set = new OrderedListenerSet();
    $d = new BasicDispatcher($set);

    $set->addListener(function (EventOne $e) {
        println('E', get_class($e));
    }, 80);
    $set->addListener(function (EventOne $e) {
        println('R', get_class($e));
    }, 90);
    $set->addListener(function (EventOne $e) {
        println('L', get_class($e));
    }, 70);
    $set->addListener(function (EventOne $e) {
        println('C', get_class($e));
    }, 100);
    $set->addListener(function (EventOne $e) {
        println('L', get_class($e));
    }, 70);

    $d->dispatch(new EventOne());
}

function test_relative_listener_set()
{
    $set = new RelativeListenerSet();
    $d = new BasicDispatcher($set);

    $e = $set->addListener(function (EventOne $e) {
        println('E', get_class($e));
    });
    $r = $set->addListenerBefore(function (EventOne $e) {
        println('R', get_class($e));
    }, $e);
    $l1 = $set->addListenerAfter(function (EventOne $e) {
        println('L', get_class($e));
    }, $e);
    $c = $set->addListenerBefore(function (EventOne $e) {
        println('C', get_class($e));
    }, $r);
    $l2 = $set->addListenerAfter(function (EventOne $e) {
        println('L', get_class($e));
    }, $l1);

    $d->dispatch(new EventOne());
}

function println(...$s)
{
    print implode(': ', $s) . PHP_EOL;
}

test_unordered_listener_set();
println('---------');
test_ordered_listener_set();
println('---------');
test_relative_listener_set();
