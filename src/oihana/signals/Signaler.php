<?php

namespace oihana\signals;

/**
 * Contract for objects that communicate through signal/slot patterns.
 *
 * This interface defines the core API for event-driven communication systems
 * where objects can emit signals and connect receivers to handle those signals.
 * It provides a decoupled, flexible way to implement the observer pattern with
 * priority-based execution and lifecycle management.
 *
 * Implementing classes should provide:
 * - Registration and removal of receivers (observers/listeners)
 * - Signal emission to notify all connected receivers
 * - Priority-based execution ordering
 * - Auto-disconnect capability for one-time listeners
 * - Thread-safe receiver management (if applicable)
 *
 * Use cases:
 * - Event systems (user interactions, system events)
 * - Plugin architectures (extensibility points)
 * - State change notifications (model updates)
 * - Command pattern implementations
 * - Message buses and pub/sub systems
 *
 * @example Basic implementation usage
 * ```php
 * // Any class implementing Signaler can be used polymorphically
 * function setupEventHandlers( Signaler $signal ): void
 * {
 *     $signal->connect( fn($data) => logEvent($data), priority: 10 );
 *     $signal->connect( fn($data) => sendNotification($data) );
 *
 *     if ( $signal->connected() ) {
 *         $signal->emit( 'Event data' );
 *     }
 * }
 *
 * $mySignal = new Signal();
 * setupEventHandlers( $mySignal );
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\signals
 *
 * @see Signal The primary implementation of this interface
 * @see Receiver Interface for objects that receive signals
 */
interface Signaler
{
    /**
     * The number of currently connected receivers.
     *
     * This read-only property provides a quick way to check how many receivers
     * are currently listening to this signal. Useful for debugging, monitoring,
     * and conditional logic based on listener count.
     *
     * @var int Always >= 0. Returns 0 when no receivers are connected.
     *
     * @example
     * ```php
     * function emitIfListeners( Signaler $signal, mixed $data ): void
     * {
     *     if ( $signal->length > 0 ) {
     *         echo "Notifying {$signal->length} listener(s)..." . PHP_EOL;
     *         $signal->emit( $data );
     *     } else {
     *         echo "No listeners attached, skipping emit." . PHP_EOL;
     *     }
     * }
     * ```
     */
    public int $length {
        get;
    }

    /**
     * Connects a receiver to this signal.
     *
     * Registers a callable or Receiver object to be notified when this signal
     * is emitted. Receivers are executed in priority order (highest first).
     * Duplicate connections are prevented - attempting to connect the same
     * receiver twice returns false.
     *
     * @param callable|Receiver $receiver The receiver to connect. Can be:
     *        - A closure or arrow function: `fn($x) => process($x)`
     *        - A function name: `'myFunction'`
     *        - An array callable: `[$object, 'method']`
     *        - A Receiver object: Must implement Receiver interface
     *
     * @param int $priority Execution priority. Higher values execute first.
     *        Default: 0 (normal priority)
     *        - Use positive values (1-100+) for high-priority handlers
     *        - Use negative values (-1 to -100) for low-priority handlers
     *
     * @param bool $autoDisconnect If true, receiver disconnects after first emit.
     *        Default: false (persistent connection)
     *        - true: One-time listener (useful for initialization)
     *        - false: Permanent listener (standard behavior)
     *
     * @return bool True if successfully connected, false if:
     *         - Receiver is already connected (prevents duplicates)
     *         - Receiver is not callable and not a Receiver object
     *
     * @example
     * ```php
     * $signal = new Signal();
     *
     * // Connect various receiver types
     * $signal->connect( fn() => echo 'Lambda' );                    // Closure
     * $signal->connect( [$logger, 'log'], priority: 100 );         // High priority method
     * $signal->connect( $receiver, autoDisconnect: true );         // One-time
     *
     * // Duplicate connection prevented
     * $handler = fn() => echo 'test';
     * $signal->connect( $handler );  // true (first connection)
     * $signal->connect( $handler );  // false (already connected)
     * ```
     */
    public function connect( mixed $receiver , int $priority = 0 , bool $autoDisconnect = false ):bool ;

    /**
     * Checks if any receivers are connected.
     *
     * Provides a boolean check for whether this signal has active listeners.
     * More semantic than checking `$length > 0` and useful for conditional
     * signal emission or validation logic.
     *
     * @return bool True if at least one receiver is connected, false if none.
     *
     * @example
     * ```php
     * function notifyUsers( Signaler $userSignal, string $message ): void
     * {
     *     if ( !$userSignal->connected() ) {
     *         throw new RuntimeException( 'No notification handlers registered' );
     *     }
     *
     *     $userSignal->emit( $message );
     * }
     *
     * // Conditional emit
     * if ( $signal->connected() ) {
     *     $signal->emit( $data );
     * } else {
     *     // Fallback behavior when no listeners
     *     logWarning( 'No listeners for event' );
     * }
     * ```
     */
    public function connected() :bool ;

    /**
     * Disconnects a receiver or all receivers from this signal.
     *
     * Removes receivers from the signal's notification list. Can disconnect
     * a specific receiver or clear all receivers at once. Safe to call
     * multiple times - returns false if receiver is not found.
     *
     * @param callable|Receiver|null $receiver The receiver to disconnect:
     *        - If null: Disconnects ALL receivers (complete reset)
     *        - If callable/Receiver: Disconnects only that specific receiver
     *
     * @return bool True if successfully disconnected, false if:
     *         - Specific receiver was not found (already disconnected)
     *         - No receivers exist when trying to disconnect all
     *
     * @example
     * ```php
     * $signal = new Signal();
     * $handler1 = fn() => echo 'A';
     * $handler2 = fn() => echo 'B';
     *
     * $signal->connect( $handler1 );
     * $signal->connect( $handler2 );
     *
     * // Disconnect specific receiver
     * $signal->disconnect( $handler1 );     // true (found and removed)
     * $signal->disconnect( $handler1 );     // false (not found)
     *
     * // Disconnect all receivers
     * $signal->disconnect();                // true (cleared all)
     * $signal->disconnect();                // false (already empty)
     *
     * // Use case: Cleanup in destructor
     * class EventEmitter
     * {
     *     private Signaler $signal;
     *
     *     public function __destruct()
     *     {
     *         $this->signal->disconnect(); // Clean up all listeners
     *     }
     * }
     * ```
     */
    public function disconnect( mixed $receiver = null ) :bool ;

    /**
     * Emits a signal, notifying all connected receivers with provided values.
     *
     * Invokes all connected receivers in priority order (highest priority first),
     * passing the specified values to each receiver. Receivers marked with
     * autoDisconnect are automatically removed after execution.
     *
     * Execution characteristics:
     * - Receivers execute in priority order (higher values first)
     * - All receivers get the same parameter values
     * - Auto-disconnect receivers are removed after their call
     * - Empty receiver list is handled gracefully (no-op)
     * - Exceptions in receivers should be handled by implementation
     *
     * @param mixed ...$values Zero or more values to pass to each receiver.
     *        All receivers receive all values in the same order.
     *
     * @return void No return value. Fire-and-forget notification pattern.
     *
     * @example
     * ```php
     * $signal = new Signal();
     *
     * // Connect receivers
     * $signal->connect( fn($user, $action) =>
     *     logActivity($user, $action),
     *     priority: 10
     * );
     *
     * $signal->connect( fn($user, $action) =>
     *     sendNotification($user, $action)
     * );
     *
     * // Emit with multiple parameters
     * $signal->emit( 'Alice', 'logged_in' );
     * // Both receivers called with: ('Alice', 'logged_in')
     *
     * // Emit with no parameters
     * $signal->connect( fn() => echo 'Tick!' . PHP_EOL );
     * $signal->emit();
     * // Receivers with no required params are called
     *
     * // Safe to emit with no receivers
     * $emptySignal = new Signal();
     * $emptySignal->emit( 'data' ); // No-op, no error
     * ```
     *
     * @example Advanced: Error handling pattern
     * ```php
     * class SafeSignal implements Signaler
     * {
     *     public function emit( mixed ...$values ): void
     *     {
     *         foreach( $this->receivers as $entry ) {
     *             try {
     *                 call_user_func_array( $entry->receiver, $values );
     *             } catch ( \Throwable $e ) {
     *                 $this->handleReceiverError( $entry, $e );
     *             }
     *         }
     *     }
     * }
     * ```
     */
    public function emit( mixed ...$values ) :void ;

    /**
     * Checks if a specific receiver is currently connected.
     *
     * Determines whether a given receiver is in this signal's active listener
     * list. Useful for preventing duplicate connections, conditional logic,
     * and debugging signal state.
     *
     * @param callable|Receiver $receiver The receiver to search for.
     *        Must be the exact same reference as when connected.
     *
     * @return bool True if the receiver is connected, false otherwise.
     *
     * @example
     * ```php
     * $signal = new Signal();
     * $handler = fn() => echo 'test';
     *
     * // Check before connecting
     * if ( !$signal->hasReceiver( $handler ) ) {
     *     $signal->connect( $handler );
     * }
     *
     * // Verify connection
     * assert( $signal->hasReceiver( $handler ) === true );
     *
     * // Check after disconnecting
     * $signal->disconnect( $handler );
     * assert( $signal->hasReceiver( $handler ) === false );
     *
     * // Use case: Conditional connection
     * function ensureHandler( Signaler $signal, callable $handler ): void
     * {
     *     if ( !$signal->hasReceiver( $handler ) ) {
     *         echo "Handler not found, connecting..." . PHP_EOL;
     *         $signal->connect( $handler );
     *     } else {
     *         echo "Handler already connected." . PHP_EOL;
     *     }
     * }
     * ```
     *
     * @note Reference equality: The receiver must be the exact same instance.
     *       Two different closures with identical code are considered different.
     *
     * @example Reference equality behavior
     * ```php
     * $signal = new Signal();
     *
     * $signal->connect( fn() => echo 'A' );
     * $signal->hasReceiver( fn() => echo 'A' ); // false (different instance)
     *
     * $handler = fn() => echo 'A';
     * $signal->connect( $handler );
     * $signal->hasReceiver( $handler ); // true (same reference)
     * ```
     */
    function hasReceiver( mixed $receiver ) :bool ;
}