<?php
declare(strict_types=1);

namespace Psr\Event\Dispatcher;


class ListenerCompiler
{
    /**
     * @param CompiledListenerCollector $listeners
     *   The set of listeners to compile.
     * @param resource $stream
     *   A writeable stream to which to write the compiled class.
     * @param string $class
     *   The un-namespaced class name to compile to.
     * @param string $namespace
     *   the namespace for the compiled class.
     */
    public function compile(CompiledListenerCollector $listeners, $stream, string $class = 'CompiledListenerSet', string $namespace = '\\Crell\\Compiled') : void
    {
        fwrite($stream, $this->createPreamble($class, $namespace));

        // The ordered queue will read this in order for us, so the result gets built already in order.
        foreach ($listeners as $listener) {
            // A function callable.
            if (is_string($listener['listener']) && strpos($listener['listener'], ':') === false) {
                fwrite($stream, $this->createFunctionEntry($listener['listener'], $listener['type']));
            }
            // This means it's a static method call on a class.
            elseif (is_array($listener)) {
                fwrite($stream, $this->createStaticMethodEntry($listener['listener'], $listener['type']));
            }
        }

        fwrite($stream, $this->createClosing());
    }

    protected function createPreamble(string $class, string $namespace) : string
    {
        return <<<END
<?php
declare(strict_types=1);

namespace $namespace;

use Psr\EventDispatcher\CompiledListenerSetBase;

class $class extends CompiledListenerSetBase
{

    protected function init() : void
    {    

END;

    }

    protected function createClosing() : string
    {
        return <<<END
    }
}
END;
    }

    protected function createFunctionEntry(string $listener, string $type)
    {
        $listener = str_replace('\\', '\\\\', $listener);
        $type = str_replace('\\', '\\\\', $type);

        return <<<END
        \$this->listeners[] = [
          'type' => '$type',
          'listener' => '$listener',
        ];

END;
    }

    protected function createStaticMethodEntry(array $listener, string $type)
    {
        $listener = str_replace('\\', '\\\\', $listener);
        $type = str_replace('\\', '\\\\', $type);

        return <<<END
        \$this->listeners[] = [
          'type' => '$type',
          'listener' => ['$listener[0]', '$listener[1]'],
        ];

END;
    }

}


