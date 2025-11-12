<?php

namespace oihana\signals;

use InvalidArgumentException;
use WeakReference;

/**
 * Internal container for signal receiver metadata.
 *
 * This class wraps a receiver callable along with its execution priority
 * and auto-disconnect behavior. It is used internally by the Signal class
 * to manage and order receivers.
 *
 * Supports three types of receivers:
 * 1. Simple callables (closures, functions, arrow functions, static methods)
 * 2. Objects implementing `Receiver` (auto-disconnected if needed)
 * 3. Object methods as array callables `[object, 'method']` with WeakReference
 *
 * WeakReferences are used for object receivers to allow garbage collection
 * without preventing objects from being destroyed.
 *
 * SignalEntry instances are created automatically when connecting receivers
 * to a Signal and should not be instantiated directly by user code.
 *
 * @internal This class is part of the internal signal implementation.
 *
 * @example Internal usage by Signal class
 * ```php
 * // This happens internally in Signal::connect()
 * $entry = new SignalEntry(
 *     receiver: fn() => echo 'test',
 *     priority: 10,
 *     auto: false
 * );
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\signals
 */
class SignalEntry
{
    /**
     * Creates a new SignalEntry instance.
     *
     * Wraps a receiver callable with associated metadata for priority-based execution,
     * auto-disconnect, and safe invocation via WeakReference.
     *
     * @param mixed $receiver The receiver to invoke when the signal is emitted.
     * Can be:
     * - any callable (closure, arrow function, static method)
     * - an object implementing `Receiver`
     * - an array callable `[object, 'method']`
     *
     * @param int $priority The execution priority (higher values execute first).
     * Default: 0
     *
     * @param bool $auto Auto-disconnect flag. If true, the receiver is automatically
     * disconnected after its first execution. Default: false
     *
     * @throws InvalidArgumentException If the receiver is not callable or does not
     * implement Receiver correctly.
     *
     * @example
     * ```php
     * // Normal persistent receiver
     * $entry1 = new SignalEntry( fn() => echo 'normal' );
     *
     * // High-priority receiver
     * $entry2 = new SignalEntry
     * (
     *    receiver : fn() => echo 'urgent',
     *    priority : 100
     * );
     *
     * // One-time receiver
     * $entry3 = new SignalEntry
     * (
     *     receiver : fn() => echo 'once' ,
     *     auto     : true
     * );
     * ```
     */
    public function __construct( mixed $receiver , int $priority = 0 , bool $auto = false )
    {
        $this->auto     = $auto ;
        $this->priority = $priority ;
        if ( is_array( $receiver ) && is_object( $receiver[0] ) && is_string( $receiver[1] ) )
        {
            if ( !method_exists( $receiver[0] , $receiver[1] ) )
            {
                throw new InvalidArgumentException
                (
                    "Method $receiver[1] does not exist on object " . get_class( $receiver[0] )
                );
            }
            $this->receiver = WeakReference::create( $receiver[0] ) ;
            $this->method   = $receiver[1] ;
        }
        else if ( $receiver instanceof Receiver )
        {
            $this->receiver = WeakReference::create( $receiver ) ;
            $this->method   = Signal::RECEIVE ;
        }
        else
        {
            // Callable (closure, static method, function name, etc.)
            $this->receiver = $receiver ;
        }
    }

    /**
     * Auto-disconnect flag.
     *
     * When true, this receiver will be automatically removed from the signal
     * after its first execution. Useful for one-time event listeners.
     *
     * @var bool
     *
     * @example
     * ```php
     * $entry = new SignalEntry( fn() => echo 'init', auto: true );
     * if ( $entry->auto ) {
     *     echo 'This receiver will disconnect after first use';
     * }
     * ```
     */
    public bool $auto = false ;

    /**
     * The optional method name of an object receiver.
     *
     * Only set if the receiver is an object or object method callable.
     *
     * @var string|null
     */
    public ?string $method = null ;

    /**
     * Execution priority of this receiver.
     *
     * Determines the order in which receivers are called when a signal is emitted.
     * Higher values are executed first.
     *
     * @var int
     *
     * @example
     * ```php
     * $critical = new SignalEntry( fn() => validate(), priority: 100 );
     * $logger   = new SignalEntry( fn() => log(), priority: 10 );
     * $normal   = new SignalEntry( fn() => process() );
     * $cleanup  = new SignalEntry( fn() => clean(), priority: -10 );
     *
     * // Execution order: validate() -> log() -> process() -> clean()
     * ```
     */
    public int $priority = 0 ;

    /**
     * The receiver callable.
     *
     * Can be any valid PHP callable:
     * - Closure: `function() { ... }`
     * - Arrow function: `fn() => ...`
     * - Array callable: `[object, 'method']` or `[ClassName::class, 'staticMethod']`
     * - Function name: `'functionName'`
     * - Invokable objects: `new InvokableClass()`
     *
     * Stored as mixed to allow WeakReference for object receivers.
     *
     * @var mixed
     *
     * @example
     * ```php
     * $entry1 = new SignalEntry( function($data) { echo $data; } );
     * $entry2 = new SignalEntry( fn($x) => $x * 2 );
     * $handler = new MyHandler();
     * $entry3 = new SignalEntry( [$handler, 'handle'] );
     * call_user_func_array( $entry1->receiver, ['Hello'] );
     * ```
     */
    public mixed $receiver ;

    /**
     * Get the actual callable to execute.
     *
     * If the receiver is a WeakReference and the object has been garbage collected,
     * returns null.
     *
     * @return callable|null
     */
    public function getCallable(): ?callable
    {
        if ( $this->receiver instanceof WeakReference )
        {
            $object = $this->receiver->get() ;
            if ( $object === null )
            {
                return null ; // object was garbage collected
            }
            return [ $object , $this->method ] ;
        }
        return $this->receiver ;
    }
}