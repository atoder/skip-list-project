# 🚀 Skip List Project: Sorted Linked List Implementation

A complete, full-stack application built around a high-performance PHP Skip List data structure with React visualization.

## 📋 Project Overview

The core deliverable is the **SkipList implementation**, which provides **O(log n) average time complexity** for insertion, deletion, and search operations. This offers massive performance improvements over standard linked lists' O(n) complexity.

## 🏗️ Project Architecture (Monorepo)

The project uses a monorepo structure with three main components:

| Directory | Purpose | Core Technology |
|-----------|---------|-----------------|
| `/library` | The core SkipList algorithm (reusable, tested) | PHP 8.1+, PHPUnit |
| `/application/api` | Backend API (consumes library, manages state) | LAMP Stack, Composer |
| `/application/frontend` | React visualization interface | React, TypeScript, Tailwind CSS |

### Monorepo Link Strategy

The `application/api` folder is linked to the `library` folder using Composer's path repository, creating a symlink for seamless development across both packages.

## 🎯 Key Features

- **High-Performance Skip List**: O(log n) average case operations
- **Type Safety**: Full TypeScript + strict PHP type checking
- **Visualization**: Animated React interface with Framer Motion
- **Session Persistence**: PHP sessions maintain state across requests
- **Cross-Origin Development**: Proper CORS configuration for local development
- **Dark/Light Mode**: Toggleable theme with system preference detection

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 16+
- npm

### 1. Clone and Setup Library
```bash
git clone <repository-url>
cd sorted-linked-list/library
composer install

