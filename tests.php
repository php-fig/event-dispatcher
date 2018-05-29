<?php
declare(strict_types=1);

namespace Tester;

use Psr\Event\Dispatcher\EventTrait;
use Psr\Event\Dispatcher\IntegratedDispatcher;
use Psr\Event\Dispatcher\EventInterface;

require_once 'vendor/autoload.php';

interface FancyEventInterface {}

class EventOne implements EventInterface {
    use EventTrait;
}

class EventTwo extends EventOne implements FancyEventInterface {}

class EventThree implements EventInterface, FancyEventInterface {
    use EventTrait;
}

function run_tests()
{
    $d = new IntegratedDispatcher();

    // This should fire twice.
    $d->addListener(function (EventOne $e) {
        println('A', get_class($e));
        return $e;
    });
    // This should fire once.
    $d->addListener(function (EventTwo $e) {
        println('B', get_class($e));
        return $e;
    });
    // This should fire once.
    $d->addListener(function (EventThree $e) {
        println('C', get_class($e));
        return $e;
    });
    // This should fire twice.
    $d->addListener(function (FancyEventInterface $e) {
        println('D', get_class($e));
        return $e;
    });
    // This should never fire, nor error.
    $d->addListener(function (EventNone $e) {
        println('E', get_class($e));
        return $e;
    });
    // This should never fire, nor error.
    $d->addListener(function (EventNone $e) {
        println('F', get_class($e));
        return $e;
    }, EventNone::class);

    $d->dispatch(new EventOne());
    $d->dispatch(new EventTwo());
    $d->dispatch(new EventThree());
}

function println(...$s)
{
    print implode(': ', $s) . PHP_EOL;
}

run_tests();
