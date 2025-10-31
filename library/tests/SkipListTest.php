<?php declare(strict_types=1);

namespace Atoder\SortedLinkedList\Tests;

use PHPUnit\Framework\TestCase;
use Atoder\SortedLinkedList\SkipList;
use TypeError;

/**
 * Unit tests for the SkipList class.
 *
 * @package Atoder\SortedLinkedList\Tests
 * @author  Alexander T
 * @license MIT
 */
final class SkipListTest extends TestCase
{
    private SkipList $list;

    /**
     * Set up a new and empty SkipList before each test.
     */
    protected function setUp(): void
    {
        $this->list = new SkipList();
    }

    public function testListIsInitiallyEmpty(): void
    {
        $this->assertSame(0, $this->list->count());
        $this->assertSame(0, count($this->list)); // Test \Countable interface
        $this->assertSame([], $this->list->toArray());
        $this->assertNull($this->list->getAllowedType());
    }

    public function testInsertsAndSortsIntegers(): void
    {
        $this->list->insert(20);
        $this->list->insert(5);
        $this->list->insert(10);

        $this->assertSame(3, $this->list->count());
        $this->assertSame([5, 10, 20], $this->list->toArray());
    }

    public function testInsertsAndSortsStrings(): void
    {
        $this->list->insert('Hustle');
        $this->list->insert('Discipline');
        $this->list->insert('Execute');

        $this->assertSame(3, $this->list->count());
        // Asserts alphabetical order
        $this->assertSame(['Discipline', 'Execute', 'Hustle'], $this->list->toArray());
    }

    public function testTypeLockingIntegers(): void
    {
        $this->list->insert(10);
        $this->assertSame('integer', $this->list->getAllowedType());

        // This should throw a TypeError.
        $this->expectException(TypeError::class);
        $this->list->insert('Hustle');
    }

    public function testTypeLockingStrings(): void
    {
        $this->list->insert('Hustle');
        $this->assertSame('string', $this->list->getAllowedType());

        // This should throw a TypeError.
        $this->expectException(TypeError::class);
        $this->list->insert(10);
    }

    public function testAllowsDuplicates(): void
    {
        $this->list->insert(10);
        $this->list->insert(5);
        $this->list->insert(10);

        $this->assertSame(3, $this->list->count());
        $this->assertSame([5, 10, 10], $this->list->toArray());
    }

    public function testSearchFindsExistingValue(): void
    {
        $this->list->insert(20);
        $this->list->insert(5);
        $this->list->insert(10);

        $this->assertTrue($this->list->search(10));
        $this->assertTrue($this->list->search(5));
        $this->assertTrue($this->list->search(20));
    }

    public function testSearchFailsForNonExistingValue(): void
    {
        $this->list->insert(20);
        $this->list->insert(5);
        $this->list->insert(10);

        $this->assertFalse($this->list->search(99));
    }

    public function testSearchFailsForWrongType(): void
    {
        $this->list->insert(10);
        $this->assertFalse($this->list->search('Hustle'));
    }

    public function testDeleteRemovesValue(): void
    {
        $this->list->insert(20);
        $this->list->insert(5);
        $this->list->insert(10);

        $this->assertTrue($this->list->delete(10));
        $this->assertSame(2, $this->list->count());
        $this->assertFalse($this->list->search(10));
        $this->assertSame([5, 20], $this->list->toArray());
    }

    public function testDeleteHandlesDuplicates(): void
    {
        $this->list->insert(10);
        $this->list->insert(5);
        $this->list->insert(10);
        $this->assertSame([5, 10, 10], $this->list->toArray());

        // Delete the first '10'
        $this->assertTrue($this->list->delete(10));
        $this->assertSame(2, $this->list->count());
        $this->assertTrue($this->list->search(10)); // The second '10' is still there
        $this->assertSame([5, 10], $this->list->toArray());

        // Delete the second '10'
        $this->assertTrue($this->list->delete(10));
        $this->assertSame(1, $this->list->count());
        $this->assertFalse($this->list->search(10));
        $this->assertSame([5], $this->list->toArray());
    }

    public function testDeleteFailsForNonExistingValue(): void
    {
        $this->list->insert(20);
        $this->assertFalse($this->list->delete(99));
        $this->assertSame(1, $this->list->count());
    }

    public function testDeleteFailsForWrongType(): void
    {
        $this->list->insert(10);
        $this->assertFalse($this->list->delete('Hustle'));
        $this->assertSame(1, $this->list->count());
    }

    public function testTypeLockIsResetWhenListIsEmpty(): void
    {
        $this->list->insert(10);
        $this->assertSame('integer', $this->list->getAllowedType());

        $this->list->delete(10);
        $this->assertSame(0, $this->list->count());
        $this->assertNull($this->list->getAllowedType());

        // Now we should be able to insert a string
        $this->list->insert('Hustle');
        $this->assertSame(1, $this->list->count());
        $this->assertSame('string', $this->list->getAllowedType());
    }

    public function testLargeInsertionOrder(): void
    {
        $items = range(1, 100);
        shuffle($items); // Insert in random order

        foreach ($items as $item) {
            $this->list->insert($item);
        }

        $this->assertSame(100, $this->list->count());
        sort($items); // The original, sorted
        $this->assertSame($items, $this->list->toArray());
    }
}

