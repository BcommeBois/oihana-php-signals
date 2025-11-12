<?php

namespace oihana\signals\notices;

use oihana\signals\Notice;

/**
 * Represents a text message emitted by a Signal.
 *
 * This class encapsulates the details of an event, including:
 * - The type of the event (`$type`).
 * - The text of message (`$text`).
 * - The target object that triggered the event (`$target`).
 * - Additional contextual information related to the event (`$context`).
 *
 * It is typically used as the payload when emitting signals:
 *
 * ```php
 * $signal->emit( new Message
 * (
 *    type    : 'info' ,
 *    text    : 'Hello World' ,
 *    target  : $signal
 * ));
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\signals
 */
class Message extends Notice
{
    /**
     * Creates a new Message.
     *
     * @param string      $type    The type of the notice.
     * @param string      $text    The text of the message.
     * @param object|null $target  The target of the notice.
     * @param array       $context The context of the notice.
     */
    public function __construct
    (
        string  $type ,
        string  $text    = '' ,
        ?object $target  = null ,
        array   $context = []
    )
    {
        parent::__construct( $type , $target , $context ) ;
        $this->text = $text ;
    }

    /**
     * The message of the notice
     * @var string
     */
    public string $text ;
}