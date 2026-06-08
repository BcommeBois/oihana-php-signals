<?php

namespace tests\oihana\signals;

use InvalidArgumentException;

use oihana\signals\SignalEntry;
use PHPUnit\Framework\TestCase;

final class SignalEntryTest extends TestCase
{
    public function testArrayCallableWithExistingMethodStoresWeakReference(): void
    {
        $obj = new class
        {
            public function handle(): void {}
        };

        $entry = new SignalEntry([ $obj , 'handle' ]);

        $this->assertSame('handle', $entry->method);
        $this->assertSame([ $obj , 'handle' ], $entry->getCallable());
    }

    public function testArrayCallableWithMissingMethodThrows(): void
    {
        $obj = new class {};

        $this->expectException(InvalidArgumentException::class);
        new SignalEntry([ $obj , 'doesNotExist' ]);
    }

    public function testGetCallableReturnsNullAfterObjectGarbageCollected(): void
    {
        $obj = new class
        {
            public function handle(): void {}
        };

        $entry = new SignalEntry([ $obj , 'handle' ]);

        // Drop the only strong reference, then force collection: the stored
        // WeakReference now resolves to null and getCallable() reports it.
        $obj = null;
        gc_collect_cycles();

        $this->assertNull($entry->getCallable());
    }
}
