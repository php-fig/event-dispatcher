<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;

use Crell\EventDispatcher\CompiledListenerCollector;
use Crell\EventDispatcher\ListenerCompiler;
use PHPUnit\Framework\TestCase;

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
