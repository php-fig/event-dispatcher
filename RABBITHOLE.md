# Down the event rabbit hole

## Executive summary

I have been spending a lot of time on airplanes lately so decided to see just how far down the rabbit hole I could go before I ran into the Cheshire Cat.  So far I've managed to avoid any catapillers, which is a very good sign.

This branch contains experimental implementations of the interfaces we've been discussing abstractly.  Nothing here is set in stone.  My goal was to see where we would run into problems and see what other insight we could gain.  Here's what I've concluded so far:

* I've yet to run into a place where type-based registration is problematic.
* That said, service-based listeners must have their type specified explicilty; the reflection detection, despite being fun to say, doesn't work without an for-reals callable (obviously).  I don't see that as a serious drawback.
* The service-based listener use case obviously needs a container, and PSR-11 takes care of everything we need.  Which... suggests PSR-11 may have been a good thing after all. :-)
* The compiled use-case is handled best not by a compiled dispatcher but a compiled ListenerSet.
* In fact, with listener sets (aggregators, whatever) factored out to a separate interface a nearly universal dispatcher is stupid-easy to write.  The only use case for an alternate implementation seems to be for instrumentation (eg, logging every event directly for debugging).  I consider this a good thing.
* However, there seems little value to a unified registration interface.  Every use case (unordered, priority ordered, before/after ordered, services, etc.) seems to require a different method signature.  That suggests we should do one of the following:
** Do not provide a registration interface at all, and explicitly kick that downstream to implementers.  This has the benefit of flexibility but the downside of having no standardized way for trivial cases to register themselves.
** Provide multiple "common case" registration interfaces (probably unordered and priority ordered) and then explicitly say that they're optional and it's totally legit to define your own instead.
* The word "listener" is far too easy to misspell as "listner".
* The code in pretty much all implementations is scarily simple.  That is a good sign.

## The interfaces

```php
interface EventInterface
{
    public function stopPropagation(bool $stop = true) : self;

    public function stopped() : bool;
}
```

A trivially simple interface with propagation control.  There's also a trait provided that implements them both in the only logical way, which is a natural fit for a util library.

```php
interface ListenerSetInterface
{
    public function getListenersFor(EventInterface $event): iterable;
}
```

This is a read-only collection of listeners.  It's basically the same as Matthew's "aggregate", as that turned out to be a really elegant solution but I find "aggregate" to be too pretentious a word even for me :-).  The only thing you can do is get a list of listeners that are applicable to an event object.  It returns an iterable so that implementers can use an array or generator or whatever at their leisure.

```php
interface DispatcherInterface
{
    public function dispatch(EventInterface $event) : EventInterface;
}
```

The dispatcher interface.  Give it an event, it runs it through the appropriate listeners and then gives the event back.  I have it returning the event to make chaining easier.  Eg:

```php
$event = new Whatever();
$results = $dispatcher->dispatch($event)->getResults();
```

I am not wedded to that but it seems like a nice convenience for the "give me your stuff" and "modify my thingie" use cases.

```php
interface BasicRegistrationInterface
{
    public function addListener(callable $listener, string $type = null) : void;
}
```

This is the most basic registration interface possible.  No ordering, just adding a listener to the collection.  The type is optional, on the assumptoin that it can and will be auto-detected if possible.  (If not possible and not specified, that's an error.)

I didn't bother creating more registration interfaces for now.  I'm not sure what direction we want to take here.  See options above.  Of note, there are use cases to implement ListenerSetInterface alone but I the registration interfaces only have one use case I've found to ever implement without ListenerSetInterface: For a compiler/builder object that will nver get called but gets mutated into a compiled listener.  I am not sure what that says about whether we should have registration interfaces or not.

## Implementations

I've a couple of implementations included in the impl directory, namespaced separately.  Some of them could eventually evolve into a util package, others I'll probably use for a stand-alone implementation of whatever the spec ends up being.  I won't cover every class, just the notable ones.  There are unit tests showing all of them at work.

I won't embed the code for them here but just link to them, for space reasons.  However, note that nearly all of them are super short.  Only one is over 100 lines.  Most are under 50 lines, including whitespace. Hawt.

[UnorderedListenerSet](impl/UnorderedListenerSet.php) - This is the naive implementation of BasicRegistrationInterface.  Trivially simple aside from the reflection logic, which is split off to a trait for easier sharing.

[OrderedListenerSet](impl/OrderdListenerSet.php) - An alternate version that allows for a numeric priority.  Also super simple as the ordering logic is all punted to SplPriorityQueue.

[RelativeListenerSet](impl/RelativeListenerSet.php) - Another alternate version that allows for before/after ordering.  The internal code is kinda gross.  I'm sure someone with more experience could come up with a more performant internal implementation but it shows how it could be made to work.  An alternate approach would be to allow the registrant to specify an identifier rather than letting it be auto-generated, so that modules could specify the before/after based on a known static value.  I didn't bother writing that version.

[IntegratedDispatcher](impl/integratedDispatcher.php) - This is a bit weird at the moment as I have it accepting an arbitrary listner, but itself only reimplements BasicRegistrationInterface.  I should probably inline that again.  The idea here is that this is the canonical "one big object" approach, where the same object is both registration and dispatching (the model used by Symfony now, and I presume others).

[BasicDispatcher](impl/BasicDispatcher.php) - I honestly have a hard time not calling this the universal dispatcher.  All of the interesting logic is in registration and ordering and stuff, hidden in the ListnerSet logic.  Once you have a listener set, this class can dispatch basically anything.  Again, trivially simple.

[ServiceListenerSet](impl/ServiceListenerSet.php) - Another set with a custom registration signature that takes services as specified in a provided PSR-11 container.  The type is required as it cannot be auto-detected from a service definition but that's a minor inconvenience (and something already required with all existing implementations today as far as I'm aware).  The only runtime overhead added is one closure call and a $container->get() call.  There's very likely more error handling to do there but this is just proof-of-concept level code.

[ListenerCompiler](impl/ListenerCompiler.php) - This is the only implementation that has any significant size to it, and even then it's still all fairly straightforward.  It's split into 2 classes for easier maintainability and could probably be refined more.  Whether or not there are additional interfaces here to extract I don't know.  It only allows for static method calls, service calls, and functions.  Calls on objects and closures are explicitly disallowed as you cannot call those statically.  However, aside from the logic to enforce that it's all fairly mundane.  Even the code generation is mundane and it needs to generate only a single method body; the rest is up in a base class.  I used the priority-ordered implementation here but there's no reason one couldn't implement a relative-ordered version instead/in addition.

It's not, of course, the last word in compiled containers.  I could totally see a Symfony version that keeps the compiler pass concept to allow all sorts of pluggable manipulation.  I didn't want to bother with that here, but suffice to say compiled containers are very much supported.


