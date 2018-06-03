<?php
declare(strict_types=1);

namespace Crell\EventDispatcher\Test;

use Crell\EventDispatcher\BasicDispatcher;
use Crell\EventDispatcher\CompiledListenerCollector;
use Crell\EventDispatcher\ListenerCompiler;
use PHPUnit\Framework\TestCase;
use Psr\Event\Dispatcher\ListenerSetInterface;

function listenerA(CollectingEvent $event) : void
{
    $event->add('A');
}

function listenerB(CollectingEvent $event) : void
{
    $event->add('B');
}

/**
 * @throws \Exception
 */
function noListen(EventOne $event) : void
{
    throw new \Exception('This should not be called');
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
        $class = 'CompiledSet';
        $namespace = 'Test\\Space';

        $collector = new CompiledListenerCollector();
        $compiler = new ListenerCompiler();

        $container = new MockContainer();
        $container->addService('D', new ListenService());

        $collector->addListener('\\Crell\\EventDispatcher\\Test\\listenerA');
        $collector->addListener('\\Crell\\EventDispatcher\\Test\\listenerB');
        $collector->addListener('\\Crell\\EventDispatcher\\Test\\noListen');
        $collector->addListener([Listen::class, 'listen']);
        $collector->addListenerService('D', 'listen', CollectingEvent::class);

        // Write the generated compiler out ot a temp file.
        $filename = tempnam(sys_get_temp_dir(), 'compiled');
        $out = fopen($filename, 'w');
        $compiler->compile($collector, $out, $class, $namespace);
        fclose($out);

        // Now include it.  If there's a parse error PHP will throw a ParseError and PHPUnit will catch it for us.
        include($filename);

        /** @var ListenerSetInterface $set */
        $compiledClassName = "$namespace\\$class";
        $set = new $compiledClassName($container);

        $d = new BasicDispatcher($set);

        $event = new CollectingEvent();
        $d->dispatch($event);

        $result = $event->result();
        $this->assertContains('A', $result);
        $this->assertContains('B', $result);
        $this->assertContains('C', $result);
        $this->assertContains('D', $result);

        $this->assertTrue(true);
    }
}
