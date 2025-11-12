<?php

namespace tests\oihana\signals;

use oihana\signals\notices\Message;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MessageTest extends TestCase
{
    public function testCanInstantiateMessage()
    {
        $notice = new Message(
            type: 'info',
            text: 'hello world' ,
            target: new stdClass(),
            context: ['foo' => 'bar']
        );

        $this->assertSame('info', $notice->type);
        $this->assertInstanceOf(stdClass::class, $notice->target);
        $this->assertSame('hello world', $notice->text);
        $this->assertSame(['foo' => 'bar'], $notice->context);
    }

    public function testDefaultValues()
    {
        $notice = new Message('info' );

        $this->assertSame('info', $notice->type);
        $this->assertEmpty($notice->text);
        $this->assertNull($notice->target);
        $this->assertSame([], $notice->context);
    }
}