# 🚀 Skip List Project: Sorted Linked List Implementation

A complete, full-stack application built around a high-performance PHP Skip
List data structure with React, TypeScript, Tailwind CSS, and Framer Motion
visualization.

## 🚀 Live Demo

The full-stack application is live, deployed, and validated.

**https://skip-list-project.vercel.app/**

## 📋 Project Overview

The core deliverable is a high-performance `SortedLinkedList` implementation,
which uses a Skip List data structure. A standard linked list suffers from
slow, linear O(n) time for searches. This implementation solves that problem.

It provides **O(log n) average time complexity** for insertion, deletion, and
search. This logarithmic performance is a massive improvement and makes the
data structure suitable for large, scalable applications.

The library also enforces a strict type-lock validation. It allows either `int`
or `string` values, but not both. This is a key architectural decision to
ensure data integrity and prevent the unpredictable behavior of mixed-type
comparisons.

## 🏗️ Project Architecture (Monorepo)

The project uses a monorepo structure with three main components, allowing the entire stack to be managed from a single repository.

| Directory | Purpose | Core Technology |
|-----------|---------|-----------------|
| `/library` | The core SkipList algorithm (reusable, tested) | PHP 8.1+, PHPUnit |
| `/application/api` | Backend API (consumes library, manages state) | PHP, Composer, Redis |
| `/application/frontend` | React visualization interface | React, TypeScript, Tailwind CSS, Framer Motion |

### Monorepo Link Strategy

The `application/api` folder is linked to the `library` folder using Composer's
path repository, creating a symlink for seamless development across both
packages. This strategy is key, as it allows for atomic commits. Changes to the
core library logic and the API endpoint that consumes it can be committed and
validated as a single unit of work.

## 🎯 Key Features

- **High-Performance Skip List**: O(log n) average time for all core
operations, providing performance on par with a balanced binary tree.

- **Type Safety**: Full-stack type checking. The library uses PHP's
strict_types and the frontend uses TypeScript to create a robust data contract,
eliminating an entire class of bugs.

- **Visualization**: A fluid, animated React interface built with Framer Motion
provides immediate, smooth visual feedback for all data operations.

- **Redis Session Persistence**: Solves the critical ephemeral filesystem
problem. By using a free Redis instance on Render, the PHP session state
persists between requests, a pro-grade solution for a read-only container
environment.

- **Cross-Origin Deployment**: A professional CORS configuration allows the
Vercel frontend to securely communicate with the Render backend, complete with
cookie-based credential passing for sessions.

- **Dark/Light Mode**: The UI includes a toggleable theme that also respects
the user's `prefers-color-scheme` OS setting on first load.

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 16+
- npm

### 1. Run the API (Backend)

The backend is a PHP application that consumes the library and uses Redis for
session state on production.

```bash
cd application/api
composer install  # This will use atoder/sorted-linked-list from the local path
cp .env.example .env  # Create your local config file

# This reads the .env file and enables the /tmp/ session fix
php -S localhost:8000 -t public
```

Your API is now live at `http://localhost:8000`. Good job.

### 2. Run the Frontend

The frontend is a modern React/TypeScript application.

```bash
cd application/frontend
npm install  # This installs React, Tailwind, Framer Motion, etc.
cp .env.example .env.development  # Create your local config for the API URL
npm start
```

Your app is now live at `http://localhost:3000`. Good job.

## 🚀 Deployment

### Backend (Render)
1. Deploy the `application/api` directory as a PHP Web Service
2. Add a free Redis Key/Value store in Render dashboard
3. Set environment variable: `REDIS_URL=your-redis-connection-url`

### Frontend (Vercel)
1. Connect your GitHub repository to Vercel
2. Deploy the `application/frontend` directory
3. No additional configuration needed

## 🔌 API Endpoints

- `POST /?action=insert` - Insert value into skip list
- `POST /?action=delete` - Delete value from skip list
- `POST /?action=reset` - Reset skip list to empty state
- `GET /?action=getall` - Retrieve complete skip list structure

All endpoints return JSON responses with the current state of the skip list.
