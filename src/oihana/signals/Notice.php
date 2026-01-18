<?php

namespace oihana\signals;

use oihana\core\options\PrepareOption;
use ReflectionException;
use JsonSerializable;

use oihana\reflect\traits\ReflectionTrait;

/**
 * Represents a notification or message emitted by a Signal.
 *
 * This class encapsulates the details of an event, including:
 * - The type of the event (`$type`).
 * - The target object that triggered the event (`$target`).
 * - Additional contextual information related to the event (`$context`).
 *
 * It is typically used as the payload when emitting signals:
 *
 * ```php
 * $notice = new Notice
 * (
 *     type: 'afterDelete',
 *     target: $documentModel,
 *     context:
 *     [
 *         'deletedDocuments' => $documents,
 *         'options'          => $deleteOptions
 *     ]
 * );
 * $afterDeleteSignal->emit($notice);
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\signals
 */
class Notice implements JsonSerializable
{
    /**
     * Creates a new Notice.
     *
     * @param string      $type    The type of the notice.
     * @param object|null $target  The target of the notice.
     * @param array       $context The context of the notice.
     */
    public function __construct
    (
        string  $type ,
        ?object $target  = null ,
        array   $context = []
    )
    {
        $this->context = $context ;
        $this->target  = $target  ;
        $this->type    = $type    ;
    }

    use ReflectionTrait
    {
        toArray as traitToArray ; // renommer la méthode du trait
    }

    /**
     * The context of the notice
     * @var array
     */
    public array $context ;

    /**
     * The target of the notice.
     * @var mixed
     */
    public mixed $target ;

    /**
     * The type of the notice.
     * @var mixed
     */
    public mixed $type ;

    /**
     * Serializes the current object into a JSON array.
     *
     * @return array JSON-LD representation of the object.
     *
     * @throws ReflectionException If reflection fails when accessing properties.
     */
    public function jsonSerialize() : array
    {
        return $this->toArray() ;
    }

    /**
     * Generates an associative array from the public properties of a given class or object.
     *
     * @param null|object|string $class The object instance or fully-qualified class name.
     * @param array|null $options Optional configuration.
     *
     * @return array The resulting associative array of properties.
     *
     * @throws ReflectionException
     */
    public function toArray( null|object|string $class = null , ?array $options = [] ): array
    {
        $class ??= $this ;
        $options = [PrepareOption::REDUCE => true, ...($options ?? [])];

        return $this->traitToArray( $class , [ PrepareOption::REDUCE => true , ...$options ] ) ;
    }
}