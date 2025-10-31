<?php declare(strict_types=1);

namespace Atoder\SortedLinkedList;

use Countable;
use TypeError;

/**
 * Implements a SortedLinkedList using the Skip List data structure.
 *
 * This provides O(log n) average performance for search, insert, and delete operations.
 *
 * The list enforces a single data type (either 'integer' or 'string')
 * after the first item is inserted.
 *
 * Important: Duplicates are allowed.
 *
 * @package Atoder\SortedLinkedList
 * @author  Alexander T
 * @license MIT
 * @implements \Countable
 */
final class SkipList implements Countable
{
    /**
     * The maximum possible height (number of levels/towers) the list can have.
     * Acts as a safety cap for node level generation.
     * @var int
     */
    private int $maxLevel;

    /**
     * The probability (0.0 to 1.0) used for promoting a node to
     * a higher level during insertion.
     * @var float
     */
    private float $p;

    /**
     * The *current* highest level (index) that is in use by any
     * node in the list. Starts at 0.
     * @var int
     */
    private int $level;

    /**
     * The sentinel header node. It acts as the starting point
     * for all operations. Its value is set to -INF (negative infinity).
     * This ensures it is always the smallest item, which simplifies
     * search and insert logic by removing edge cases at the
     * beginning of the list.
     *
     * @var SkipListNode
     */
    private SkipListNode $header;

    /**
     * Stores the data type ('integer' or 'string') that the list is
     * locked to. It is set to `null` until the first insertion.
     * @var ?string
     */
    private ?string $allowedType = null;

    /**
     * The total count of data nodes currently in the list.
     * @var int
     */
    private int $size = 0;

    /**
     * Initializes a new, empty SkipList.
     *
     * @param int $maxLevel The maximum allowed "towers" aka height for any node (default 32).
     * @param float $p The promotion probability (default 0.5 for 50%).
     * @return void
     */
    public function __construct(int $maxLevel = 32, float $p = 0.5)
    {
        $this->maxLevel = $maxLevel;
        $this->p = $p;
        $this->level = 0;
        $this->size = 0;

        // The header's value is -INF (a float) to ensure it's always
        // smaller than any user-supplied int or string. This acts as
        // an anchor and simplifies all operations.
        $this->header = new SkipListNode(-INF, $this->maxLevel);
    }

    /**
     * Inserts a value into the list, maintaining sorted order.
     * Duplicates are allowed by default.
     * Average time complexity is O(log n).
     *
     * @param int|string $value The value to insert.
     * @return void
     * @throws \TypeError If the value's type is invalid.
     */
    public function insert(int|string $value): void
    {
        $this->checkType($value);

        // $update is an array tracking the "path" to the insertion point.
        // $update[$i] will store the node that precedes the
        // insertion spot at level $i.
        $update = array_fill(0, $this->maxLevel + 1, null);
        $current = $this->header;

        // Find the insertion path, starting from the highest level down.
        for ($i = $this->level; $i >= 0; $i--) {
            // Move right at level $i as long as the next node's value is < $value.
            while ($current->forward[$i] !== null && $current->forward[$i]->value < $value) {
                $current = $current->forward[$i];
            }
            // Record the predecessor at this level.
            $update[$i] = $current;
        }

        $newLevel = $this->randomLevel();

        // If the new node's level is higher than the list's current level,
        // we must update the list's level and set the header as the
        // predecessor for these new, higher levels.
        if ($newLevel > $this->level) {
            for ($i = $this->level + 1; $i <= $newLevel; $i++) {
                $update[$i] = $this->header;
            }
            $this->level = $newLevel;
        }

        $newNode = new SkipListNode($value, $newLevel);

        // Splice the new node into the list by rewiring pointers.
        for ($i = 0; $i <= $newLevel; $i++) {
            // New node points to what the predecessor *was* pointing to.
            $newNode->forward[$i] = $update[$i]->forward[$i];
            // Predecessor now points to the new node.
            $update[$i]->forward[$i] = $newNode;
        }

        $this->size++;
    }

    /**
     * Deletes the first occurrence of a value from the list.
     * Average time complexity is O(log n).
     *
     * @param int|string $value The value to delete.
     * @return bool True if a node was successfully deleted, false if the
     * value was not found.
     */
    public function delete(int|string $value): bool
    {
        // Skip check if type doesn't match; it can't be in the list.
        if ($this->allowedType !== null && gettype($value) !== $this->allowedType) {
            return false;
        }

        // $update stores the path to the node *before* the target.
        $update = array_fill(0, $this->maxLevel + 1, null);
        $current = $this->header;

        // Find the path, identical to insert().
        for ($i = $this->level; $i >= 0; $i--) {
            while ($current->forward[$i] !== null && $current->forward[$i]->value < $value) {
                $current = $current->forward[$i];
            }
            $update[$i] = $current;
        }

        // $nodeToDelete is the candidate node at the base level.
        $nodeToDelete = $current->forward[0];

        // Check if the node exists and its value matches.
        if ($nodeToDelete !== null && $nodeToDelete->value === $value) {

            // Splice the node out at every level it exists on.
            for ($i = 0; $i < count($nodeToDelete->forward); $i++) {
                // Check if the predecessor at this level points to our target.
                if ($update[$i]->forward[$i] === $nodeToDelete) {
                    // Bypass it: predecessor now points to target's next node.
                    $update[$i]->forward[$i] = $nodeToDelete->forward[$i];
                }
            }

            // After deletion, check if the top level(s) are now empty.
            // If so, lower the list's current level.
            while ($this->level > 0 && $this->header->forward[$this->level] === null) {
                $this->level--;
            }

            $this->size--;

            // If the list is now empty, reset the type lock.
            if ($this->size === 0) {
                $this->allowedType = null;
            }
            return true;
        }

        return false;
    }

    /**
     * Searches for a value in the list.
     * Average time complexity is O(log n).
     *
     * @param int|string $value The value to search for.
     * @return bool True if the value is found, false otherwise.
     */
    public function search(int|string $value): bool
    {
        // Skip check if type doesn't match.
        if ($this->allowedType !== null && gettype($value) !== $this->allowedType) {
            return false;
        }

        $current = $this->header;

        // Find path, top-down.
        for ($i = $this->level; $i >= 0; $i--) {
            while ($current->forward[$i] !== null && $current->forward[$i]->value < $value) {
                $current = $current->forward[$i];
            }
        }

        // $current is now the node before the potential match.
        // Move one step right to check the node itself.
        $current = $current->forward[0];

        // Check if this node exists and its value matches.
        if ($current !== null && $current->value === $value) {
            return true;
        }

        return false;
    }

    /**
     * Gets the total number of items in the list.
     * Implements the \Countable interface.
     *
     * @return int The number of items.
     */
    public function count(): int
    {
        return $this->size;
    }

    /**
     * Gets the current highest level (0-indexed) in the list.
     *
     * @return int The current max level.
     */
    public function getMaxHeight(): int
    {
        return $this->level;
    }

    /**
     * Gets the (optional) data type the list is locked to.
     *
     * @return ?string 'integer', 'string', or null if empty.
     */
    public function getAllowedType(): ?string
    {
        return $this->allowedType;
    }

    /**
     * Returns the entire list as a simple, sorted array.
     * Used primarily for testing and visualization.
     *
     * @return array<int, int|string>
     */
    public function toArray(): array
    {
        $result = [];
        // Start at Level 0
        $current = $this->header->forward[0];
        while ($current !== null) {
            $result[] = $current->value;
            $current = $current->forward[0];
        }
        return $result;
    }

    /**
     * Gets a structured array of all nodes and their heights.
     * Intended for API/visualization use.
     *
     * @return array<int, array{value: int|string, height: int}>
     */
    public function getStructure(): array
    {
        $nodes = [];
        $current = $this->header->forward[0];

        while ($current !== null) {
            // height is 0-indexed height
            $nodes[] = [
                'value' => $current->value,
                'height' => count($current->forward) - 1
            ];
            $current = $current->forward[0];
        }
        return $nodes;
    }

    /**
     * Generates a random level (height) for a new node.
     * The level is determined by successive "coin flips" based on the
     * list's probability `$p`, capped by `$maxLevel`.
     *
     * @return int The randomly determined level (from 0 to $maxLevel).
     */
    private function randomLevel(): int
    {
        $lvl = 0;

        /* This 'while' loop is the "coin flip" that determines the node's height.
         * It continues as long as BOTH of the following conditions are true:
         *
         * 1. (mt_rand() / mt_getrandmax()) < $this->p
         * This is the actual coin flip (e.g., 50% chance if p=0.5).
         *
         * 2. $lvl < $this->maxLevel
         * This is the safety cap to prevent absurdly tall nodes/towers.
         **/
        while ((mt_rand() / mt_getrandmax()) < $this->p && $lvl < $this->maxLevel) {
            $lvl++;
        }
        return $lvl;
    }

    /**
     * Validates a value's type against the list's allowed type.
     * On first call, it "locks" the list to the type of that value.
     * On subsequent calls, it ensures new values match the locked type.
     *
     * @param mixed $value The value to check.
     * @return void
     * @throws \TypeError If the value is not an 'int' or 'string', or if
     * it mismatches the list's locked-in type.
     */
    private function checkType(mixed $value): void
    {
        if ($this->allowedType === null) {
            // First insert. Set the type lock.
            if (is_int($value)) {
                $this->allowedType = 'integer';
            } elseif (is_string($value)) {
                $this->allowedType = 'string';
            } else {
                // The task only specified int or string.
                throw new TypeError("List only supports 'int' or 'string' values.");
            }
        }

        $valueType = gettype($value);
        if ($valueType !== $this->allowedType) {
            throw new TypeError(
                "This list is locked to type '{$this->allowedType}'. " .
                "Cannot insert type '{$valueType}'."
            );
        }
    }
}

