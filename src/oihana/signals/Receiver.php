<?php

namespace oihana\signals;

use Exception;

/**
 * Contract for objects that receive and handle signal emissions.
 *
 * This interface defines the standard method signature for objects that act as
 * signal receivers (observers/listeners) in an event-driven system. Any class
 * implementing this interface can be connected to a Signaler object and will
 * automatically receive notifications when signals are emitted.
 *
 * The Receiver pattern provides several advantages over raw callables:
 * - Type-safe receiver registration (enforced by type hints)
 * - Object-oriented encapsulation of handler logic
 * - Easier testing and mocking of event handlers
 * - Clear separation of concerns in complex event systems
 * - Self-documenting code (receiver intent is explicit)
 *
 * Design patterns supported:
 * - Observer pattern: Receivers as observers of signal subjects
 * - Command pattern: Receivers as command executors
 * - Strategy pattern: Different receivers as interchangeable strategies
 * - Chain of Responsibility: Multiple receivers processing the same event
 *
 * @example Basic implementation
 * ```php
 * use oihana\signals\Receiver;
 * use oihana\signals\Signal;
 *
 * class Logger implements Receiver
 * {
 *     public function receive( mixed ...$values ): void
 *     {
 *         $timestamp = date('Y-m-d H:i:s');
 *         $message = implode(', ', $values);
 *         echo "[{$timestamp}] {$message}" . PHP_EOL;
 *     }
 * }
 *
 * $signal = new Signal();
 * $signal->connect( new Logger() );
 * $signal->emit( 'User logged in', 'user_id: 123' );
 * // Output: [2025-01-15 10:30:45] User logged in, user_id: 123
 * ```
 *
 * @example Multiple receivers with different responsibilities
 * ```php
 * class EmailNotifier implements Receiver
 * {
 *     public function receive( mixed ...$values ): void
 *     {
 *         [$event, $user] = $values;
 *         $this->sendEmail( $user, "Event: {$event}" );
 *     }
 * }
 *
 * class MetricsCollector implements Receiver
 * {
 *     public function receive( mixed ...$values ): void
 *     {
 *         [$event] = $values;
 *         $this->incrementCounter( "events.{$event}" );
 *     }
 * }
 *
 * class AuditLogger implements Receiver
 * {
 *     public function receive( mixed ...$values ): void
 *     {
 *         $this->database->insert( 'audit_log', $values );
 *     }
 * }
 *
 * $signal = new Signal();
 * $signal->connect( new EmailNotifier(), priority: 10 );
 * $signal->connect( new MetricsCollector(), priority: 5 );
 * $signal->connect( new AuditLogger() );
 *
 * $signal->emit( 'user_registered', 'alice@example.com' );
 * // All three receivers handle the event in priority order
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\signals
 *
 * @see Signaler The interface for objects that emit signals
 * @see Signal   The primary implementation that manages receivers
 */
interface Receiver
{
    /**
     * Handles signal emissions by processing the provided values.
     *
     * This method is automatically invoked by the Signal system when a connected
     * signal is emitted. Implementations should process the provided values
     * according to their specific logic (logging, notifications, data processing, etc.).
     *
     * Method characteristics:
     * - Called automatically when signal emits (no manual invocation)
     * - Receives all values passed to the signal's emit() method
     * - Values arrive in the same order they were emitted
     * - No return value expected (fire-and-forget pattern)
     * - Should handle missing or unexpected values gracefully
     * - Exceptions should be caught internally or allowed to propagate
     *
     * Implementation guidelines:
     * - Keep processing fast to avoid blocking other receivers
     * - Validate input values before using them
     * - Handle edge cases (empty values, wrong types)
     * - Consider using variadic parameters destructuring for clarity
     * - Document expected value format in implementation
     *
     * @param mixed ...$values Zero or more values emitted by the signal.
     *        The number and types of values depend on the signal source.
     *        - Empty for signals that emit no data
     *        - Single value for simple notifications
     *        - Multiple values for complex events
     *
     * @return void No return value. Receivers perform side effects only.
     *
     * @example Simple receiver with no parameters
     * ```php
     * class HeartbeatMonitor implements Receiver
     * {
     *     public function receive( mixed ...$values ): void
     *     {
     *         // Called periodically, no data needed
     *         echo "♥ Heartbeat at " . time() . PHP_EOL;
     *     }
     * }
     *
     * $heartbeat = new Signal();
     * $heartbeat->connect( new HeartbeatMonitor() );
     * $heartbeat->emit(); // No parameters
     * ```
     *
     * @example Receiver with structured data
     * ```php
     * class OrderProcessor implements Receiver
     * {
     *     public function receive( mixed ...$values ): void
     *     {
     *         // Destructure expected values
     *         [$orderId, $amount, $customer] = $values;
     *
     *         echo "Processing order #{$orderId}" . PHP_EOL;
     *         echo "Amount: \${$amount}" . PHP_EOL;
     *         echo "Customer: {$customer}" . PHP_EOL;
     *
     *         $this->processPayment( $orderId, $amount );
     *     }
     * }
     *
     * $orderSignal = new Signal();
     * $orderSignal->connect( new OrderProcessor() );
     * $orderSignal->emit( 12345, 99.99, 'Alice' );
     * ```
     *
     * @example Robust receiver with validation
     * ```php
     * class ValidatingReceiver implements Receiver
     * {
     *     public function receive( mixed ...$values ): void
     *     {
     *         // Handle variable number of values
     *         if ( empty( $values ) ) {
     *             $this->logWarning( 'Received empty signal' );
     *             return;
     *         }
     *
     *         // Validate expected format
     *         if ( count( $values ) < 2 ) {
     *             $this->logError( 'Insufficient data received' );
     *             return;
     *         }
     *
     *         [$event, $data] = $values;
     *
     *         // Type checking
     *         if ( !is_string( $event ) ) {
     *             $this->logError( 'Invalid event type' );
     *             return;
     *         }
     *
     *         // Process valid data
     *         $this->handleEvent( $event, $data );
     *     }
     * }
     * ```
     *
     * @example Receiver with error handling
     * ```php
     * class ResilientReceiver implements Receiver
     * {
     *     public function receive( mixed ...$values ): void
     *     {
     *         try {
     *             $this->processValues( $values );
     *         } catch ( \Exception $e ) {
     *             // Log error but don't crash the signal chain
     *             $this->errorHandler->log( $e );
     *
     *             // Optional: Rethrow critical errors
     *             if ( $e instanceof CriticalException ) {
     *                 throw $e;
     *             }
     *         }
     *     }
     *
     *     private function processValues( array $values ): void
     *     {
     *         // Actual processing logic that might throw
     *     }
     * }
     * ```
     *
     * @example Stateful receiver tracking multiple emissions
     * ```php
     * class EventCounter implements Receiver
     * {
     *     private int $count = 0;
     *     private array $history = [];
     *
     *     public function receive( mixed ...$values ): void
     *     {
     *         $this->count++;
     *         $this->history[] = [
     *             'timestamp' => microtime(true),
     *             'values' => $values
     *         ];
     *
     *         echo "Event #{$this->count} received" . PHP_EOL;
     *
     *         // Limit history size
     *         if ( count( $this->history ) > 100 ) {
     *             array_shift( $this->history );
     *         }
     *     }
     *
     *     public function getCount(): int
     *     {
     *         return $this->count;
     *     }
     *
     *     public function getHistory(): array
     *     {
     *         return $this->history;
     *     }
     * }
     * ```
     *
     * @throws Exception Implementations may throw exceptions if they encounter
     *                   critical errors. It's the Signal's responsibility to
     *                   decide whether to catch or propagate these exceptions.
     */
    public function receive( mixed ...$values ):void ;
}