<?php

namespace oihana\signals\notices;

use oihana\signals\Notice;

/**
 * Represents a payload notice emitted by a Signal.
 *
 * This class encapsulates the details of an event, including:
 * - The type of the event (`$type`).
 * - The payload (`$data`).
 * - The target object that triggered the event (`$target`).
 * - Additional contextual information related to the event (`$context`).
 *
 * It is typically used as the payload when emitting signals:
 *
 * ```php
 * $signal->emit( new Payload
 * (
 *    type    : 'delete' ,
 *    data    : [ 'id' => '15151' , 'name' : 'John' ] ,
 *    target  : $signal
 * ));
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\signals
 */
class Payload extends Notice
{
    /**
     * Creates a new Payload.
     *
     * @param string      $type    The type of the notice.
     * @param string      $data    The payload entry.
     * @param object|null $target  The target of the notice.
     * @param array       $context The context of the notice.
     */
    public function __construct
    (
        string  $type ,
        mixed   $data    = null ,
        ?object $target  = null ,
        array   $context = []
    )
    {
        parent::__construct( $type , $target , $context ) ;
        $this->data = $data ;
    }

    /**
     * The message of the notice
     * @var string
     */
    public mixed $data ;
}