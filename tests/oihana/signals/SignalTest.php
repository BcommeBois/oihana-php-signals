<?php

namespace tests\oihana\signals;

use oihana\signals\Signal;
use oihana\signals\Receiver;
use PHPUnit\Framework\TestCase;

final class SignalTest extends TestCase
{
    public function testConnectCallable(): void
    {
        $signal = new Signal();
        $handler = fn($x) => $x;

        $this->assertTrue($signal->connect($handler));
        $this->assertTrue($signal->hasReceiver($handler));
        $this->assertEquals(1, $signal->length);

        // Duplicate connection returns false
        $this->assertFalse($signal->connect($handler));
        $this->assertEquals(1, $signal->length);
    }

    public function testConnectReceiverObject(): void
    {
        $signal = new Signal();
        $receiver = new class implements Receiver
        {
            public array $called = [] ;
            public function receive( mixed ...$values ) :void
            {
                $this->called[] = $values ;
            }
        };

        $this->assertTrue($signal->connect($receiver));
        $this->assertTrue($signal->hasReceiver($receiver));
        $this->assertEquals(1, $signal->length) ;
    }

    public function testDisconnectCallable(): void
    {
        $signal = new Signal();
        $handler = fn() => 'test';
        $signal->connect($handler);

        $this->assertTrue($signal->disconnect($handler));
        $this->assertFalse($signal->hasReceiver($handler));
        $this->assertEquals(0, $signal->length);

        // Disconnect non-existing receiver returns false
        $this->assertFalse($signal->disconnect($handler));
    }

    public function testDisconnectReceiverObject(): void
    {
        $signal = new Signal();
        $receiver = new class implements Receiver {
            public function receive(mixed ...$values): void {}
        };
        $signal->connect($receiver);

        $this->assertTrue($signal->disconnect($receiver));
        $this->assertFalse($signal->hasReceiver($receiver));
        $this->assertEquals(0, $signal->length);
    }

    public function testDisconnectAll(): void
    {
        $signal = new Signal();
        $handler1 = fn() => 'A';
        $handler2 = fn() => 'B';

        $signal->connect($handler1);
        $signal->connect($handler2);

        $this->assertTrue($signal->disconnect());
        $this->assertEquals(0, $signal->length);

        // Already empty
        $this->assertFalse($signal->disconnect());
    }

    public function testEmitCallable(): void
    {
        $signal = new Signal();
        $called = [];

        $handler = function($x, $y) use (&$called) {
            $called[] = [$x, $y];
        };
        $signal->connect($handler);

        $signal->emit(1, 2);
        $this->assertEquals([[1, 2]], $called);

        $signal->emit(3, 4);
        $this->assertEquals([[1, 2], [3, 4]], $called);
    }

    public function testEmitReceiverObject(): void
    {
        $signal = new Signal();
        $receiver = new class implements Receiver
        {
            public array $called = [];
            public function receive(mixed ...$values): void {
                $this->called[] = $values;
            }
        };

        $signal->connect($receiver);
        $signal->emit('foo', 'bar');

        $this->assertEquals([['foo', 'bar']], $receiver->called);
    }

    public function testAutoDisconnect(): void
    {
        $signal = new Signal();
        $called = [];

        $handler = function($x) use (&$called) {
            $called[] = $x;
        };
        $signal->connect($handler, autoDisconnect: true);

        $signal->emit(1);
        $this->assertEquals([1], $called);
        $this->assertEquals(0, $signal->length);

        // Should not be called again
        $signal->emit(2);
        $this->assertEquals([1], $called);
    }

    public function testPriorityOrder(): void
    {
        $signal = new Signal();

        $results = [];

        $slot1 = function() use ( &$results )
        {
            $results[] = 'low' ;
        };

        $slot2 = function() use ( &$results )
        {
            $results[] = 'high' ;
        };

        $slot3 = function() use ( &$results )
        {
            $results[] = 'highest' ;
        };

        $signal->connect( $slot1 , priority: 50  ) ;
        $signal->connect( $slot2 , priority: 1  ) ;
        $signal->connect( $slot3 , priority: 100 ) ;

        $signal->emit();

        $this->assertSame(
            ['highest', 'low', 'high'],
            $results,
            'Receivers should be called in priority order from high to low'
        );
    }

    public function testToArray(): void
    {
        $signal = new Signal();
        $h1 = fn() => 'A';
        $h2 = fn() => 'B';

        $signal->connect($h1, priority: 10);
        $signal->connect($h2, priority: 5);

        $arr = $signal->toArray();
        $this->assertEquals([$h1, $h2], $arr);
    }

    public function testConnectedProperty(): void
    {
        $signal = new Signal();
        $this->assertFalse($signal->connected());

        $signal->connect(fn() => null);
        $this->assertTrue($signal->connected());
    }
}