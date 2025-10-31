# 🚀 Skip List Project

This project is a high-performance PHP library designed to implement a `SortedLinkedList`.
It's built using a Skip List data structure to achieve `O(log n)` (logarithmic) performance, which is a massive improvement over a standard linked list's `O(n)`.

The repository is a "monorepo" containing both the core library and a full-stack demo application to visualize it.

* `/library` - The Core Library (PHP 8.1, PHPUnit)
* `/application` - The Demo App (PHP API, React, TypeScript)

## 1. 🏛️ The Library (`/library`)

This is the core, reusable library, built for speed and reliability.

### Key Features

* **Performance:** `O(log n)` average time for search, insert, and delete.
* **Type-Lock:** Enforces `int` or `string` values only (not both). It will throw a `\TypeError` if types are mixed.
* **Duplicates:** This implementation allows duplicates by default.
* **Pro-Grade:** Built on PSR-4, fully namespaced, and validated with PHPUnit.

See the `library/README.md` for its full API and developer details.

## 2. 💻 The Demo App (`/application`)

This is a full-stack application that consumes the `library` to provide a live and interactive visualizer.

### API (PHP)

A simple backend that uses Composer to load the local library and persists the list in a PHP session.

To run the API:

1. `cd application/api`
2. `composer install` (This will use `atoder/sorted-linked-list` from the local path)
3. Run the local PHP server with the development environment variable:

```bash
# This sets the 'APP_ENV' to 'development' for the local session fix
APP_ENV='development' php -S localhost:8000 -t public
```

