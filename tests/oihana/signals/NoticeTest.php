<?php

namespace tests\oihana\signals;

use oihana\signals\Notice;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

class NoticeTest extends TestCase
{
    public function testCanInstantiateNotice()
    {
        $payload = new stdClass() ;

        $notice = new Notice
        (
            type    : 'afterDelete',
            target  : $payload,
            context : ['foo' => 'bar']
        );

        $this->assertSame('afterDelete', $notice->type);
        $this->assertInstanceOf(stdClass::class, $notice->target);
        $this->assertSame(['foo' => 'bar'], $notice->context);
    }

    public function testDefaultValues()
    {
        $notice = new Notice('beforeInsert');

        $this->assertSame('beforeInsert', $notice->type);
        $this->assertNull($notice->target);
        $this->assertSame([], $notice->context);
    }

    public function testContextCanBeEmptyArray()
    {
        $notice = new Notice(
            type: 'update',
            target: new stdClass(),
            context: []
        );

        $this->assertSame([], $notice->context);
    }

    public function testTargetCanBeNull()
    {
        $notice = new Notice(
            type: 'delete',
            target: null,
            context: ['deleted' => 1]
        );

        $this->assertNull($notice->target);
        $this->assertSame(['deleted' => 1], $notice->context);
    }

    /**
     * @throws ReflectionException
     */
    public function testNoticeToArray()
    {
        $payload = new stdClass() ;
        $notice  = new Notice
        (
            type    : 'afterDelete',
            target  : $payload,
            context : ['foo' => 'bar']
        );

        $this->assertSame
        ([
            'context' => ['foo' => 'bar'] ,
            'target'  => $payload ,
            'type'    => 'afterDelete' ,
        ]
        , $notice->toArray() );
    }

    public function testNoticeJsonEncode()
    {
        $payload = new stdClass() ;
        $payload->name = 'foo' ;

        $notice  = new Notice
        (
            type    : 'afterDelete',
            target  : $payload,
            context : ['foo' => 'bar']
        );

        $this->assertSame
        (
            expected : '{"context":{"foo":"bar"},"target":{"name":"foo"},"type":"afterDelete"}' ,
            actual   : json_encode( $notice )
        );
    }
}