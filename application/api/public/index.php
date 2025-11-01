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

// 2. Import the classes we need
use Atoder\SortedLinkedList\SkipList;
use TypeError;

// 3. Configure and Start PHP Session
if (getenv('APP_ENV') === 'development') {
    $sessionPath = __DIR__ . '/../sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    ini_set('session.save_path', $sessionPath);
}

session_start();

// 4. Initialize the List
if (!isset($_SESSION['skip_list'])) {
    $_SESSION['skip_list'] = new SkipList();
}

$list = $_SESSION['skip_list'];

// 5. API Router
$data = json_decode(file_get_contents('php://input'), true) ?? [];

$action = $_GET['action'] ?? null;
$value = $data['value'] ?? null;
$response = [];

try {
    switch ($action) {
        case 'insert':
            if ($value === null) {
                throw new \Exception('Value cannot be null.');
            }
            $valueToInsert = is_numeric($value) ? (int)$value : (string)$value;
            $list->insert($valueToInsert);
            $response['message'] = "Inserted: {$valueToInsert}. Good job.";
            break;

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

        case 'reset':
            $list = new SkipList();
            $response['message'] = 'List reset. Back to work.';
            break;

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

    $_SESSION['skip_list'] = $list;
    $response['success'] = true;

} catch (TypeError $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} catch (\Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// 7. Send JSON Response - MINIMAL FIX HERE
header('Content-Type: application/json');

// FIXED CORS headers (only these lines changed)
$allowed_origins = ['http://localhost:3000', 'http://127.0.0.1:3000'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost:3000");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

echo json_encode($response, JSON_PRETTY_PRINT);

