# Brainstorming

## Basic interfaces

### Event

An object that is passed as a message to listeners. It also communicates to the
dispatcher the result and status of listener propagation.

```php
interface EventInterface
{
    /**
     * Retrieve any data pertaining to the event. This will be data provided by
     * the object that triggers the event and/or listeners called by the event
     * dispatcher.
     */
    public function getData() : array;

    /**
     * Evolve the event to include a new set of data.
     *
     * MUST return a NEW instance that returns the $data via getData();
     */
    public function withData(array $data) : self;

    /**
     * Evolve the event such that getData will include a new key with the datum provided.
     *
     * MUST return a NEW instance that includes $key in the data returned via
     * getData(), with the value $datum.
     *
     * @param mixed $datum
     */
    public function with(string $key, $datum) : self;

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

### Listener

An object listening to one or more events and doing something based on what it
receives. It may evolve the event, including creating a new event with a result
or stopping propagation. The emitter will inspect the event returned by the
listener.

```php
interface ListenerInterface
{
    public function listen(EventInterface $event) : EventInterface;
}
```

### Listener aggregates

These allow aggregating listeners to provide to an emitter; the aggregate
exposes a method for retrieving listeners by event type. An emitter
implementation could also implement this interface or decorate or consume an
instance.

```php
interface ListenerAggregateInterface
{
    /**
     * @return ListenerInterface[]
     */
    public function getListenersForEvent(EventInterface $event) : array;
}
```

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
    public function listen(string $eventType, ListenerInterface $listener) : void;
}
```

The main reason for providing an aggregate is to provide a mechanism for
_prioritization_ of listeners. One possibility for that might be:

```php
interface PrioritizedListenerAggregateInterface extends ListenerAggregateInterface
{
    public function listen(string $eventType, ListenerInterface $listener, int $priority = 1) : void;
}
```

Alternatives might allow appending/prepending based on another listener:

```php
interface PositionableListenerAggregateInterface extends AttachableListenerAggregateInterface
{
    public function listenAfter(string $listenerTypeToAppend, string $eventType, ListenerInterface $newListener) : void;
    public function listenBefore(string $listenerTypeToPrepend, string $eventType, ListenerInterface $newListener) : void;
}
```

Of these interfaces, the only one REQUIRED by the specification would be
`ListenerAggregateInterface`; all others could be defined in other
specifications, within implementations, etc.

### Results

An object aggregating the results of all listeners; in each case, it is the
return value of the listener just called, which will be an event instance.
The aggregate is itself an iterator, allowing iteration of all results, but
also provides convenience methods for obtaining the first or last results.

```php
use Iterator;

interface ResultAggregateInterface extends Iterator
{
    /**
     * Retrieve the event returned by the first listener.
     */
    public function first() : EventInterface;

    /**
     * Retrieve the event returned by the last listener.
     */
    public function last() : EventInterface;
}
```

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
     *
     * @throws EventTypeMismatchExceptionInterface
     */
    public function emit(EventInterface $event) : ResultAggregateInterface;
}

interface EventTypeMismatchExceptionInterface
{
}
```

One note: it's possible for a listener to return an event of a different type.
As such, the emitter needs to verify that the event returned is of the same type
or subtype before continuing dispatch of listeners. If it is not, it should
raise an `EventTypeMismatchExceptionInterface`, ideally reporting the original
event type, and the type of the latest event returned.

## Utilities

Some utilties might be interesting to provide. These include:

- a trait defining common functionality of all event types.
- a standard result aggregate implementation.
- a decorator for callable listeners, allowing attaching arbitrary callables as
  listeners, or listeners that typehint against more specific event
  implementations. This would have a corresponding utility function for more
  simple DX.
- a decorator for _lazy_ listeners (where the listener body pulls a named
  service from a composed PSR-11 container). This would have a corresponding
  utility function for more simple DX.

### Event trait

```php
trait EventDataTrait
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $isStopped = false;

    /**
     * @var null|mixed
     */
    private $result;

    /**
     * Retrieve any data pertaining to the event. This will be data provided by
     * the object that triggers the event and/or listeners called by the event
     * dispatcher.
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Evolve the event to include a new set of data.
     *
     * MUST return a NEW instance that returns the $data via getData();
     */
    public function withData(array $data) : self
    {
        $event = clone $this;
        $event->data = $data;
        return $event;
    }

    /**
     * Evolve the event such that getData will include a new key with the datum provided.
     *
     * MUST return a NEW instance that includes $key in the data returned via
     * getData(), with the value $datum.
     *
     * @param mixed $datum
     */
    public function with(string $key, $datum) : self
    {
        $data = $this->getData();
        $data[$key] = $datum;
        return $this->withData($data);
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
    public function stopPropagation() : self
    {
        $event = clone $this;
        $event->isStopped = true;
        return $event;
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

### Result aggregate

Remember, `ResultAggregateInterface` extends `Iterator`, requiring additional
methods. This implementation also provides a `push()` method as a simple
mechanism for an emitter to add results to the aggregate.

```php
final class ResultAggregate implements ResultAggregateInterface
{
    /**
     * @var EventInterface[]
     */
    private $results = [];

    /**
     * Push a result into the aggregate.
     *
     * @param EventInterface $result
     */
    public function push(EventInterface $result) : void
    {
        $this->results[] = $result;
    }

    /**
     * Retrieve the first result.
     */
    public function first() : EventInterface
    {
        $this->rewind();
        return $this->current();
    }

    /**
     * Retrieve the last result.
     */
    public function last() : EventInterface
    {
        return end($this->results);
    }

    /**
     * @return EventInterface Not type-hinted, due to extending Iterator.
     */
    public function current()
    {
        current($this->results);
    }

    /**
     * @return null|false|string|int
     */
    public function key()
    {
        key($this->results);
    }

    public function next() : void
    {
        next($this->results);
    }

    public function rewind() : void
    {
        reset($this->results);
    }

    public function valid() : bool
    {
        $key = $this->key();
        return null !== $key && false !== $key;
    }
}
```

### Callable listener decorator

This class could be used to decorate anonymous functions, or to establish an
existing instance method as an event listener. This latter is particularly
powerful, as it allows a listener to typehint against a more specific event
type, and thus delegate to PHP's type system.

```php
final class CallableListener implements ListenerInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function listen(EventInterface $event) : EventInterface
    {
        return ($this->callback)($event);
    }
}
```

The corresponding utility function:

```php
function listener(callable $callback) : CallableListener
{
    return new CallableListener($callback);
}
```

> The utility function would be defined in the utility namespace, or the
> implementation namespace.

### Lazy listeners

Lazy listeners allow creation and attachment of a listener without the necessity
of having the expense of loading the listener if it is never triggered. It
leverages a PSR-11 container.

This implementation provides the ability to specify a specific method, allowing
a generic callable listener; if no method is provided, the implementation:

- determines if the instance is a `ListenerInterface`; if so, it uses the
  `listen()` method.
- determines if the instance is callable; if so, it uses the `__invoke()`
  method.

The implementation detailed below includes some implementation-specific
exceptions; as such, it is likely best suited to a util or implementation
package.

```php
use Psr\Container\ContainerInterface;

final class LazyListener implements ListenerInterface
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
    public function listen(EventInterface $event) : EventInterface
    {
        $listener = $this->getCallableListener(
            $this->container->get($this->service)
        );

        return $listener($event);
    }

    /**
     * @var mixed $service Service retrieved from container.
     */
    private function getCallableListener($service) : callable
    {
        // Not an object, and not callable: invalid
        if (! is_object($service) && ! is_callable($service)) {
            throw Exception\InvalidListenerException::forNonCallableService($service);
        }

        // Not an object, but callable: return verbatim
        if (! is_object($service) && is_callable($service)) {
            return $service;
        }

        // Object, no method present, and implements ListenerInterface: return
        // its listen() method
        if (! $this->method && $service instanceof ListenerInterface) {
            return [$listener, 'listen'];
        }

        // Object, no method present, not a listener, and not callable: invalid
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
    $results = $this->emitter->emit($event);
    if ($results->last()->getSomeEventSpecificValue() instanceof SomethingOfInterest) {
        return $results->last()->getSomeEventSpecificValue();
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
        return $event->with('result', $result);
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
        return $event
            ->stopPropagation()
            ->with('result', new InvalidDataResult());
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

    public function emit(EventInterface $event) : ResultAggregateInterface
    {
        $results = new ResultAggregate();
        foreach ($listeners->getListenersForEvent($event) as $listener) {
            $event = $listener->listen($event);
            $results->push($event);
            if ($event->isStopped()) {
                break;
            }
        }
        return $results;
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

        $this->listeners[$event]->toArray();
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
