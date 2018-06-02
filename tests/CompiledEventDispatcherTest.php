<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;

use PHPUnit\Framework\TestCase;
use Psr\Event\Dispatcher\BasicDispatcher;
use Psr\Event\Dispatcher\BasicEvent;
use Psr\Event\Dispatcher\CompiledListenerCollector;
use Psr\Event\Dispatcher\EventInterface;
use Psr\Event\Dispatcher\EventTrait;
use Psr\Event\Dispatcher\IntegratedDispatcher;
use Psr\Event\Dispatcher\ListenerCompiler;
use Psr\Event\Dispatcher\OrderedListenerSet;
use Psr\Event\Dispatcher\RelativeListenerSet;
use Psr\Event\Dispatcher\ServiceListenerSet;


function listenerA(CollectingEvent $event) : void
{
    $event->add('A');
}

function listenerB(CollectingEvent $event) : void
{
    $event->add('B');
}

class Listen
{
    public static function listen(CollectingEvent $event)
    {
        $event->add('C');
    }
}

class ListenService
{
    public static function listen(CollectingEvent $event)
    {
        $event->add('D');
    }
}

class CompiledEventDispatcherTest extends TestCase
{
    function testFunctionCompile()
    {
        $set = new CompiledListenerCollector();
        $compiler = new ListenerCompiler();

        $container = new MockContainer();
        $container->addService('D', new ListenService());

        $set->addListener('\\Crell\\EventDispatcher\\Test\\listenerA');
        $set->addListener('\\Crell\\EventDispatcher\\Test\\listenerB');
        $set->addListener([Listen::class, 'listen']);
        $set->addListenerService('D', 'listen', CollectingEvent::class);

        $out = fopen('php://temp', 'w');
        $compiler->compile($set, $out);

        fseek($out, 0);
        $output = stream_get_contents($out);

        print $output;

        $this->assertTrue(true);
    }
}
