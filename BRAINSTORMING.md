# Brainstorming

## Basic interfaces

### Event

An object that is passed as a message to listeners. It also communicates to the
dispatcher the result and status of listener propagation.

```php
<?php
interface EventInterface
{
    /**
     * Provide access to the event arguments, if any.
     *
     * Implementations may have this return null if no event arguments are
     * needed, or if immutable event arguments are unnecessary.
     */
    public function getArguments() : ?EventArgumentsInterface;

    /**
     * Stop event propagation.
     *
     * Once called, when handling returns to the dispatcher, the dispatcher MUST
     * stop calling any remaining listeners and return handling back to the
     * target object.
     *
     * MUST return a NEW instance that will cause isStopped to return boolean
     * true.
     */
    public function stopPropagation() : self;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the dispatcher to determine if the
     * previous listener halted propagation.
     */
    public function isStopped() : bool;
}
```

#### Event arguments

In some cases, it may be useful to provide event arguments; these would reflect
the state that caused the event to be emitted. To ensure that all listeners know
this specific state, they should be immutable.

The following interface is a _collaborator_ to the `EventInterface`, and
provides an immutable container from which to fetch event arguments. It IS NOT
required that implementations provide this, nor that events compose an instance.

```php
/**
 * Provide access to event arguments, but prevent changes to them.
 *
 * Implementations of this interface MUST NOT allow changes to the
 * arguments encapsulated within (with the exception that any argument
 * provided by reference, including objects, can potentially change).
 */
interface EventArgumentsInterface
{
    public function getArguments() : array;

    /**
     * @param mixed $default Default value to return if no matching argument
     *     discovered.
     * @return mixed
     */
    public function getArgument(string $name, $default = null);
}
```

### Listener

Listeners are any PHP callable. They will _always_ be passed an `EventInterface`
instance as the first argument; their return value will be ignored. If they wish
to communicate information to the process emitting the event, they may do so via
methods on the specific event instance designed for that purpose.

### Listener aggregates

These allow aggregating listeners to provide to an emitter; the aggregate
exposes a method for retrieving listeners by event type. An emitter
implementation could also implement this interface or decorate or consume an
instance.

```php
interface ListenerAggregateInterface
{
    public function getListenersForEvent(EventInterface $event) : iterable;
}
```

The return type is `iterable`, allowing for arrays, iterators, or generators.

Typically, you'll want to _attach_ listeners to the aggregate. The simplest
mechanism would be as follows:

```php
interface AttachableListenerAggregateInterface extends ListenerAggregateInterface
{
    /**
     * Attach a listener for a given event type.
     *
     * The event type should be a specific EventInterface implementation
     * or extension. When an emitter emits a specific EventInterface instance,
     * it will trigger any listener that has specified that type or its subtype.
     */
    public function listen(string $eventType, callable $listener) : void;
}
```

Another possibility is to use reflection on the initial argument to the listener
to determine the event type; the following interface demonstrates such a
signature:

```php
interface ReflectableListenerAggregateInterface extends ListenerAggregateInterface
{
    /**
     * Attach a listener
     *
     * If no $eventType is provided, reflects the first argument of the $listener
     * to determine the type it accepts.
     *
     * When an emitter emits a specific EventInterface instance, it will
     * trigger any listener that has specified that type or its subtype.
     */
    public function listen(callable $listener, string $eventType = null) : void;
}
```

The main reason for providing an aggregate is to provide a mechanism for
_prioritization_ of listeners. One possibility for that might be:

```php
interface PrioritizedListenerAggregateInterface extends ListenerAggregateInterface
{
    public function listen(string $eventType, callable $listener, int $priority = 1) : void;
}
```

Alternatives might allow appending/prepending based on another listener:

```php
interface PositionableListenerAggregateInterface extends AttachableListenerAggregateInterface
{
    public function listenAfter(string $listenerTypeToAppend, string $eventType, callable $newListener) : void;
    public function listenBefore(string $listenerTypeToPrepend, string $eventType, callable $newListener) : void;
}
```

Of these interfaces, the only one REQUIRED by the specification would be
`ListenerAggregateInterface`; all others could be defined in other
specifications, within implementations, etc.

### Emitter

Emits events. Generally, it will either compose a `ListenerAggregateInterface`,
or a nested array of event/listeners pairs. When it emits an event, it SHOULD
emit any listener attached to that specific type, or any subtype of it.

```php
<?php
interface EmitterInterface
{
    /**
     * Emit the given event to all attached listeners.
     */
    public function emit(EventInterface $event) : void;
}
```

## Utilities

Some utilties might be interesting to provide. These include:

- a trait defining common functionality of all event types.
- a default implementation of `EventArgumentsInterface`.
- a decorator for _lazy_ listeners (where the listener body pulls a named
  service from a composed PSR-11 container). This would have a corresponding
  utility function for more simple DX.

### Event trait

```php
trait EventTrait
{
    /**
     * @var ?EventArgumentsInterface
     */
    private $arguments;

    /**
     * @var bool
     */
    private $isStopped = false;

    public function getArguments() : ?EventArgumentsInterface
    {
        return $this->arguments;
    }

    /**
     * Stop event propagation.
     *
     * Once called, when handling returns to the dispatcher, the dispatcher MUST
     * stop calling any remaining listeners and return handling back to the
     * target object.
     *
     * MUST return a NEW instance that will cause isStopped to return boolean
     * true.
     */
    public function stopPropagation() : void
    {
        $event->isStopped = true;
    }

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the dispatcher to determine if the
     * previous listener halted propagation.
     */
    public function isStopped() : bool
    {
        return $this->isStopped;
    }
}
```

### Event arguments

```php
use InvalidArgumentException;

/**
 * Default implementation for immutable event arguments.
 */
class EventArguments implements EventArgumentsInterface
{
    /**
     * @var array
     */
    private $arguments;

    public function __construct(array $arguments)
    {
        if (empty($arguments)
            || array_keys($arguments) === range(0, count($arguments) - 1)
        ) {
            throw new InvalidArgumentException(sprintf(
                '%s only accepts associative arrays to its constructor',
                __CLASS__
            ));
        }

        $this->arguments = $arguments;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }

    /**
     * @param mixed $default Default value to return if no matching argument
     *     discovered.
     * @return mixed
     */
    public function getArgument(string $name, $default = null)
    {
        if (! array_key_exists($name, $this->arguments)) {
            return $default;
        }
        return $this->arguments[$name];
    }
}
```

### Lazy listeners

Lazy listeners allow creation and attachment of a listener without the necessity
of having the expense of loading the listener if it is never triggered. It
leverages a PSR-11 container.

This implementation provides the ability to specify a specific method, allowing
a generic callable listener; if no method is provided, the implementation
determines if the instance is callable; if so, it uses the `__invoke()` method.

The implementation detailed below includes some implementation-specific
exceptions; as such, it is likely best suited to a util or implementation
package.

```php
use Psr\Container\ContainerInterface;

final class LazyListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ?string
     */
    private $method = null;

    /**
     * @var string
     */
    private $service;

    public function __construct(ContainerInterface $container, string $service, string $method = null)
    {
        $this->container = $container;
        $this->service = $service;
        $this->method = $method;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(EventInterface $event) : void
    {
        $listener = $this->getListener(
            $this->container->get($this->service)
        );

        $listener($event);
    }

    /**
     * @var mixed $service Service retrieved from container.
     */
    private function getListener($service) : callable
    {
        // Not an object, and not callable: invalid
        if (! is_object($service) && ! is_callable($service)) {
            throw Exception\InvalidListenerException::forNonCallableService($service);
        }

        // Not an object, but callable: return verbatim
        if (! is_object($service) && is_callable($service)) {
            return $service;
        }

        // Object, no method present, and not callable: invalid
        if (! $this->method && ! is_callable($service)) {
            throw Exception\InvalidListenerException::forNonCallableInstance($service);
        }

        // Object, no method present, not a listener, but callable: return verbatim
        if (! $this->method && is_callable($service)) {
            return $service;
        }

        $callback = [$service, $this->method];

        // Object, method present, but method is not callable: invalid
        if (! is_callable($callback)) {
            throw Exception\InvalidListenerException::forNonCallableInstanceMethod($service, $method);
        }

        // Object with method as callback
        return $callback;
    }
}
```

The corresponding utility function:

```php
function lazyListener(
    ContainerInterface $container,
    string $service,
    string $method = null
) : LazyListener {
    return new LazyListener($container, $service, $method);
}
```

> The utility function would be defined in the utility namespace, or the
> implementation namespace.

## Usage

### PubSub composition

In this scenario, we'll have an emitter named `PubSub` that:

- implements `EmitterInterface`
- implements `AttachableListenerAggregateInterface` (and thus `ListenerAggregateInterface`)

Objects will attach to this instance, which will then be dropped into any class
that needs to emit events.

Delegator factories on the `PubSub` class will attach events:

```php
public function __invoke(ContainerInterface $container, $name, callable $factory)
{
    $pubsub = $factory();
    $pubsub->listen(SomeEventType::class, lazyListener($container, ListenerName::class, 'onSomeEvent'));
    $pubsub->listen(SomeOtherEventType::class, lazyListener($container, AnotherListenerName::class, 'onSomeOtherEvent'));
    return $pubsub;
}
```

We'll have a class compose this instance:

```php
public function __invoke(ContainerInterface $container)
{
    return new ClassThatEmitsEvents(
        $container->get(PubSub::class)
    );
}
```

Somewhere inside, it does the following:

```php
public function doSomething()
{
    // some work is done...
    $event = (new SomeOtherEventType())->withData($dataForEvent);
    $this->emitter->emit($event);
    if ($event->getSomeEventSpecificValue() instanceof SomethingOfInterest) {
        return $event->getSomeEventSpecificValue();
    }
    // do more work...
}
```

A listener handles the event:

```php
class AnotherListenerName
{
    public function onSomeOtherEvent(SomeOtherEventType $event) : SomeOtherEventType
    {
        // do some work:
        $value = $event->getData()['foo'] ?? 'default';

        // return a result with the event:
        return $event->setSomeEventSpecificValue($result);
    }
}
```

In this way, we can have a single dispatcher used across the entire application,
aggregating all listeners, and capable of emitting any event. Results may be
tested.

### Stopping propagation

What if we come across a condition that indicates we need to stop propagation?
For instance, what if we discover there is corrupted or invalid data?

```php
public function listen(EventInterface $event) : EventInterface
{
    // do some work:
    $value = $event->getData()['foo'] ?? false;

    if (false === $value) {
        $event->stopPropagation();
        $event->setEventSpecificValue(new InvalidDataResult());
    }

    // otherwise, continue processing
}
```

### What if I want prioritized listeners?

For this scenario, we'll have a very simple emitter implementation:

```php
class Emitter implements EmitterInterface
{
    private $listeners;

    public function __construct(ListenerAggregateInterface $listeners)
    {
        $this->listeners = $listeners;
    }

    public function emit(EventInterface $event) : void
    {
        foreach ($listeners->getListenersForEvent($event) as $listener) {
            $listener($event);
            if ($event->isStopped()) {
                break;
            }
        }
    }
}
```

The factory for the emitter might then depend on a specific implementation:

```php
public function __invoke(ContainerInterface $container)
{
    return new Emitter($container->get(PrioritizedListenerAggregate::class));
}
```

That class might look like the following:

```php
use Zend\Stdlib\PriorityQueue;

class PrioritizedListenerAggregate implements PrioritizedListenerAggregateInterface
{
    /**
     * @var PriorityQueue[]
     */
    private $listeners;

    public function listen(string $eventType, ListenerInterface $listener, int $priority = 1) : void
    {
        if (! isset($this->listeners[$eventType])) {
            $this->listeners[$eventType] = new PriorityQueue();
        }
        $this->listeners[$eventType]->insert($listener, $priority);
    }

    public function getListenersForEvent(EventInterface $event) : array
    {
        $type = get_class($event);
        if (! isset($this->listeners[$event])) {
            return [];
        }

        $this->listeners[$event];
    }
}
```

You would then attach listeners to the aggregate instead of the emitter, likely
via a delegator factory:

```php
public function __invoke(ContainerInterface $container, $name, callable $factory)
{
    $listeners = $factory();
    $listeners>listen(SomeEventType::class, lazyListener($container, ListenerName::class, 'onSomeEvent'), 100);
    $listeners>listen(SomeOtherEventType::class, lazyListener($container, AnotherListenerName::class, 'onSomeOtherEvent'), -100);
    return $listeners;
}
```

(Note the third argument to `listen()` in each of the above; priority queues
sort in order of highest to lowest values. Negative values will trigger last.)

Classes that need to emit events would compose the emitter, which would have the
prioritized queue composed from which it would pull listeners.

### What about async?

Like node, listeners can make their internals asynchronous if desired. As an
example:

```php
function (EventInterface $event) : EventInterface
{
    $promise = somethingReturningAPromise();
    return $event->withResult($promise);
}
```

Once executed, this immediately returns execution to the emitter, composing the
promise as a result; a later listener could work on the promise itself, or the
code emitting the event could.
