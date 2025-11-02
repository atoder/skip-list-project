<?php declare(strict_types=1);

/**
 * This file is the single API endpoint for the Skip List visualizer.
 *
 * It's the "controller" that consumes the `atoder/sorted-linked-list` library.
 * It loads configuration from a .env file, handles all incoming requests,
 * manages the SkipList object in the PHP session (state persistence),
 * and returns JSON responses for the React frontend.
 */

// API namespace (from our composer.json)
namespace Atoder\Api;

// 1. Load the Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Load Environment Variables (.env)
// This  loads config from a .env file.
// __DIR__ . '/../' points to the 'application/api/' folder.
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load(); // All variables are now in $_ENV
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // This is a fallback for production environments that set env vars directly
    // No .env file found but we continue anyway.
}

// 3. Import the classes we need
use Atoder\SortedLinkedList\SkipList;
use TypeError;

// 4. Configure and Start PHP Session
// We use the container's always writable /tmp directory.
// This is a solution for both local dev and production.
$sessionPath = '/tmp/skip-list-session';
if (!is_dir($sessionPath)) {
    // Use @ to suppress warnings if the dir already exists (race condition)
    @mkdir($sessionPath, 0777, true);
}
// We always set the save path. This is a non-conditional build.
ini_set('session.save_path', realpath($sessionPath));

// session_start() tells PHP to load or create a session file
// for the user, which gives us the $_SESSION array.
session_start();

// 5. Initialize the List
if (!isset($_SESSION['skip_list'])) {
    $_SESSION['skip_list'] = new SkipList();
}

$list = $_SESSION['skip_list'];

// 6. API Router
$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? null;
$value = $data['value'] ?? null;
$response = [];

try {
    switch ($action) {
        /**
         * POST /index.php?action=insert
         * Inserts a value into the list.
         */
        case 'insert':
            if ($value === null) {
                throw new \Exception('Value cannot be null.');
            }
            $valueToInsert = is_numeric($value) ? (int)$value : (string)$value;
            $list->insert($valueToInsert);
            $response['message'] = "Inserted: {$valueToInsert}. Good job.";
            break;

        /**
         * POST /index.php?action=delete
         * Deletes a value from the list.
         */
        case 'delete':
            if ($value === null) {
                throw new \Exception('Value cannot be null.');
            }
            $valueToDelete = is_numeric($value) ? (int)$value : (string)$value;

            if ($list->delete($valueToDelete)) {
                $response['message'] = "Deleted: {$valueToDelete}.";
            } else {
                $response['message'] = "Value {$valueToDelete} not found.";
            }
            break;

        /**
         * POST /index.php?action=reset
         * Resets the list to an empty state.
         */
        case 'reset':
            $list = new SkipList();
            $response['message'] = 'List reset. Back to work.';
            break;

        /**
         * GET /index.php?action=getall
         * Returns the full structure of the list for visualization.
         */
        case 'getall':
            $response['structure'] = [
                'total_height' => $list->getMaxHeight(),
                'node_count'   => $list->count(),
                'type'         => $list->getAllowedType(),
                'nodes'        => $list->getStructure(),
            ];
            break;

        default:
            throw new \Exception('Invalid action.');
    }

    // 7. Save State
    $_SESSION['skip_list'] = $list;
    $response['success'] = true;

} catch (TypeError $e) {
    // Catch the TypeErrors from our library (int vs string)
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} catch (\Exception $e) {
    // Catch all other errors (e.g., "Invalid action")
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// 8. Send JSON Response
//
// The "Guest List" is now loaded from your .env file.
$allowed_origins_str = $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000';
$allowed_origins = explode(',', $allowed_origins_str);
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Trim whitespace from all allowed origins
$allowed_origins = array_map('trim', $allowed_origins);

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Block any origin not on the list by default, but fallback for safety.
    header("Access-Control-Allow-Origin: " . $allowed_origins[0]);
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Convert the PHP $response array into a JSON string and send it.
echo json_encode($response, JSON_PRETTY_PRINT);
