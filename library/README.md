# 🏛️ The SkipList Library

This is the core PHP library, built to implement a Sorted Linked List using a **Skip List**.
It's built for speed and reliability, following modern PHP standards (PSR-4, strict_types).

*   **O(log n) Performance:** Average time for insert, delete, and search.
*   **Type-Safe:** Enforces int or string values (not both).
*   **Duplicates:** This implementation allows duplicates.
*   **Unit Tested:** Validated with a full PHPUnit test suite.

## 1. Get The Code

This package is built for Composer.

```bash
composer require atoder/sorted-linked-list
```

## 2. How to Use It

```php
// Make sure you load the autoloader
require 'vendor/autoload.php';

use Atoder\SortedLinkedList\SkipList;

// Create a new list
$list = new SkipList();

// Insert values
$list->insert(20);
$list->insert(5);
$list->insert(10);
$list->insert(10); // Duplicates are good.

// Search
$list->search(10); // -> true
$list->search(99); // -> false

// Delete
$list->delete(10); // -> true (deletes the first '10')

// Count
echo count($list); // -> 3

// Get as array
$array = $list->toArray(); // -> [5, 10, 20]
```

## 3. The API (The Methods)

### new SkipList

```php
new SkipList(int $maxLevel = 32, float $p = 0.5)
```

Creates a new list.

### insert

```php
insert(int|string $value): void
```

Inserts a value, keeping the list sorted.

### delete

```php
delete(int|string $value): bool
```

Deletes the _first_ occurrence of a value. Returns true on success, false if the value isn't found.

### search

```php
search(int|string $value): bool
```

Checks if a value exists.

### count

```php
count(): int
```

Implements `\Countable`. Lets you use the `count()` function.

### toArray

```php
toArray(): array
```

Returns a simple, sorted array of all values. O(n) operation.

### getAllowedType

```php
getAllowedType(): ?string
```

Returns the locked data type ('integer' or 'string') or null if the list is empty.

### getMaxHeight

```php
getMaxHeight(): int
```

Returns the 0-indexed height of the tallest "tower" in the list.

### getStructure

```php
getStructure(): array
```

For the visualizer. Returns a structured array of node objects:
```php
[['value' => 10, 'height' => 2], ...]
```

## 4. Validate The Work

This library is validated with PHPUnit.

```bash
# From the /library directory:
composer install
./vendor/bin/phpunit
```
