<?php declare(strict_types=1);

namespace Atoder\SortedLinkedList;

/**
 * Represents a single node within the SkipList.
 *
 * Each node contains a value and an array of forward pointers
 * for each level.
 * This single object represents the entire "tower" for a given value.
 *
 * @package Atoder\SortedLinkedList
 * @author  Alexander T
 * @license MIT
 */
final class SkipListNode
{
    /**
     * @var int|string|float The data payload of the node.
     * (Note: `float` is allowed internally for the -INF header).
     */
    public int|string|float $value;

    /**
     * The "tower" of forward pointers.
     * The array index represents the level (0 being the base).
     * The value is the next node at that level or null.
     *
     * @var array<int, ?SkipListNode>
     */
    public array $forward = [];

    /**
     * Constructs a new SkipListNode.
     *
     * @param int|string|float $value The data for this node.
     * @param int $level The height (number of levels) this node will have (0-indexed).
     * @return void
     */
    public function __construct(int|string|float $value, int $level)
    {
        $this->value = $value;
        // Initialize the forward pointers array with nulls up to the node's level.
        // A level 0 node needs 1 pointer ($forward[0]).
        $this->forward = array_fill(0, $level + 1, null);
    }
}
