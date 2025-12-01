<?php
require_once 'config.php';
require_once 'models/Event.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Extract the event ID from the path if present
$event_id = null;
if (preg_match('/\/api\/events\/(\d+)/', $path, $matches)) {
    $event_id = $matches[1];
}

// Parse JSON input for POST and PUT requests
$json_data = null;
if ($method === 'POST' || $method === 'PUT') {
    $json_data = json_decode(file_get_contents('php://input'), true);
}

// Handle the request based on the method and path
header('Content-Type: application/json');

if ($method === 'GET' && $event_id === null) {
    // Get all events for the current user
    $events = Event::getAllForUser($_SESSION['user_id']);
    echo json_encode($events);
} elseif ($method === 'GET' && $event_id !== null) {
    // Get a specific event
    $event = Event::getById($event_id, $_SESSION['user_id']);
    
    if ($event) {
        echo json_encode($event);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Event not found']);
    }
} elseif ($method === 'POST') {
    // Create a new event
    try {
        // Validate required fields
        if (empty($json_data['title']) || empty($json_data['company']) || empty($json_data['date'])) {
            throw new Exception('Missing required fields');
        }
        
        $user_id = $_SESSION['user_id'];
        $title = $json_data['title'];
        $company = $json_data['company'];
        $description = $json_data['description'] ?? '';
        $date = $json_data['date'];
        $status = $json_data['status'] ?? 'Pending';
        
        if (Event::create($user_id, $title, $company, $description, $date, $status)) {
            $new_event_id = $pdo->lastInsertId();
            $new_event = Event::getById($new_event_id, $user_id);
            
            header('HTTP/1.1 201 Created');
            echo json_encode($new_event);
        } else {
            throw new Exception('Failed to create event');
        }
    } catch (Exception $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($method === 'PUT' && $event_id !== null) {
    // Update an event
    try {
        $result = Event::update($event_id, $_SESSION['user_id'], $json_data);
        
        if ($result) {
            $updated_event = Event::getById($event_id, $_SESSION['user_id']);
            echo json_encode($updated_event);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['error' => 'Event not found or update failed']);
        }
    } catch (Exception $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($method === 'DELETE' && $event_id !== null) {
    // Delete an event
    $result = Event::delete($event_id, $_SESSION['user_id']);
    
    if ($result) {
        echo json_encode(['message' => 'Event deleted successfully']);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Event not found or delete failed']);
    }
} else {
    // Method not allowed or invalid path
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
}
?>
