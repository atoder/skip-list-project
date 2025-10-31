<?php declare(strict_types=1);

/**
 * This file is the single API endpoint for the Skip List visualizer.
 *
 * It's the "controller" that consumes the `atoder/sorted-linked-list` library.
 * It handles all incoming requests, manages the SkipList object in the
 * PHP session (state persistence), and returns JSON responses for the
 * React frontend.
 */

// API namespace (from our composer.json)
namespace Atoder\Api;

// 1. Load the Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Import (alias) the classes we need
// This lets us write `new SkipList()` instead of `new \Atoder\SortedLinkedList\SkipList()`
use Atoder\SortedLinkedList\SkipList;
use TypeError;

// 3. Start PHP Session
// session_start() tells PHP to load or create a session file
// for the user, which gives us the $_SESSION array to store data
// between API calls.
session_start();

// 4. Initialize the List
// Check if our list object is already in the session.
if (!isset($_SESSION['skip_list'])) {
    // If not, create a new one. This only happens on the first-ever request.
    $_SESSION['skip_list'] = new SkipList();
}

// Load the list from the session into a local variable for easy access.
$list = $_SESSION['skip_list'];

// 5. API Router
// This reads the request and decides which operation to perform.

// Get the POST body (raw JSON) from React.
$data = json_decode(file_get_contents('php://input'), true) ?? [];

// Get the 'action' from the URL query string (e.g., ?action=insert).
$action = $_GET['action'] ?? null;
$value = $data['value'] ?? null;

// PHP array we'll send back as JSON.
$response = [];

try {
    switch ($action) {
        /**
         * POST /index.php?action=insert
         * Expects JSON: { "value": "..." }
         * Inserts a value into the list.
         */
        case 'insert':
            if ($value === null) {
                throw new \Exception('Value cannot be null.');
            }
            // Auto-detect if it's a number or string
            $valueToInsert = is_numeric($value) ? (int)$value : (string)$value;
            $list->insert($valueToInsert);
            $response['message'] = "Inserted: {$valueToInsert}. Good job.";
            break;

        /**
         * POST /index.php?action=delete
         * Expects JSON: { "value": "..." }
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
            // To reset, we just create a new, empty list and replace the one in session.
            $list = new SkipList();
            $response['message'] = 'List reset. Back to work.';
            break;

        /**
         * GET /index.php?action=getall
         * Returns the full structure of the list for visualization.
         */
        case 'getall':
            // This is the main endpoint for the React app.
            // It calls the API-specific methods from the library.
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

    // 6. Save State
    // Put the (potentially modified) list object back into the session
    // to save it for the next request.
    $_SESSION['skip_list'] = $list;
    $response['success'] = true;

} catch (TypeError $e) {
    // Catch the TypeErrors from our library (e.g., "int vs string")
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} catch (\Exception $e) {
    // Catch all other errors (e.g., "Invalid action")
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// 7. Send JSON Response
// This is the data contract with the frontend.

// Set the header to tell the browser this is JSON, not HTML.
header('Content-Type: application/json');

// This CORS header is critical.
// It allows the React app (on localhost:3000)
// to make requests to your PHP API (on localhost:8000).
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Convert the PHP $response array into a JSON string and send it.
echo json_encode($response, JSON_PRETTY_PRINT);

