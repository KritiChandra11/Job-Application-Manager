<?php
require_once '../config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Create calendar_events table if it doesn't exist
try {
    // First check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Users table does not exist. Please initialize the database first.']);
        exit;
    }
    
    // Then create calendar_events table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS calendar_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            company VARCHAR(100) NOT NULL,
            start DATETIME NOT NULL,
            end DATETIME NOT NULL,
            type VARCHAR(20) NOT NULL DEFAULT 'other',
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            description TEXT NULL,
            reminder INT DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Check if the table was created
    $stmt = $pdo->query("SHOW TABLES LIKE 'calendar_events'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Failed to create calendar_events table');
    }
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch(Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Extract the event ID from the path or query string if present
$event_id = null;
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
} else if (preg_match('/\/api\/calendar_events\.php\?id=(\d+)/', $request_uri, $matches) || 
    preg_match('/\/api\/calendar_events\.php\/(\d+)/', $path, $matches)) {
    $event_id = $matches[1];
}

// Parse JSON input for POST and PUT requests
$json_data = null;
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        $json_data = json_decode($input, true);
        // Check for JSON parse errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
            exit;
        }
    } else {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Empty request body']);
        exit;
    }
}

// Handle the request based on the method and path
header('Content-Type: application/json');

if ($method === 'GET' && $event_id === null) {
    // Get all calendar events for the current user
    $stmt = $pdo->prepare("
        SELECT id, title, company, start, end, type, status, description, reminder
        FROM calendar_events 
        WHERE user_id = ? 
        ORDER BY start ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
    
    // Format events for FullCalendar
    $formattedEvents = [];
    foreach ($events as $event) {
        // Determine color based on status first, then type
        $color = '';
        // Determine color based on status
        switch ($event['status']) {
            case 'Interview':
                $color = '#007bff'; // blue
                break;
            case 'Offer':
                $color = '#28a745'; // green
                break;
            case 'Rejected':
                $color = '#dc3545'; // red
                break;
            case 'pending':
            default:
                // If status is pending or not set, determine color based on event type
                switch ($event['type']) {
                    case 'interview':
                        $color = '#007bff'; // blue
                        break;
                    case 'deadline':
                        $color = '#dc3545'; // red
                        break;
                    case 'followup':
                        $color = '#17a2b8'; // cyan
                        break;
                    default:
                        $color = '#6c757d'; // gray
                }
        }
        
        $formattedEvents[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $event['start'],
            'end' => $event['end'],
            'color' => $color,
            'extendedProps' => [
                'company' => $event['company'],
                'type' => $event['type'],
                'status' => $event['status'],
                'description' => $event['description'],
                'reminder' => $event['reminder']
            ]
        ];
    }
    
    echo json_encode($formattedEvents);
} elseif ($method === 'GET' && $event_id !== null) {
    // Get a specific calendar event
    $stmt = $pdo->prepare("
        SELECT id, title, company, start, end, type, status, description, reminder
        FROM calendar_events 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    $event = $stmt->fetch();
    
    if ($event) {
        echo json_encode($event);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Event not found']);
    }
} elseif ($method === 'POST') {
    // Create a new calendar event
    try {
        // Debug: Log the incoming data
        error_log("Received data: " . file_get_contents('php://input'));
        
        // Validate required fields
        if (empty($json_data['title'])) {
            throw new Exception('Missing required field: title');
        }
        if (empty($json_data['company'])) {
            throw new Exception('Missing required field: company');
        }
        if (empty($json_data['start'])) {
            throw new Exception('Missing required field: start');
        }
        if (empty($json_data['end'])) {
            throw new Exception('Missing required field: end');
        }
        
        $user_id = $_SESSION['user_id'];
        $title = $json_data['title'];
        $company = $json_data['company'];
        $start = $json_data['start'];
        $end = $json_data['end'];
        $type = $json_data['type'] ?? 'other';
        $status = $json_data['status'] ?? 'pending';
        $description = $json_data['description'] ?? '';
        $reminder = $json_data['reminder'] ?? 0;
        $created_at = date('Y-m-d H:i:s');
        
        // Debug: Log the processed data
        error_log("Processing event: Title=$title, Company=$company, Start=$start, End=$end, Type=$type, Status=$status");
        
        $stmt = $pdo->prepare("
            INSERT INTO calendar_events (user_id, title, company, start, end, type, status, description, reminder, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$user_id, $title, $company, $start, $end, $type, $status, $description, $reminder, $created_at, $created_at]);
        
        if ($result) {
            $new_event_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE id = ?");
            $stmt->execute([$new_event_id]);
            $new_event = $stmt->fetch();
            
            header('HTTP/1.1 201 Created');
            echo json_encode($new_event);
        } else {
            throw new Exception('Failed to create event: Database error');
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        error_log("PDO Error in calendar_events.php: " . $e->getMessage());
    } catch (Exception $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $e->getMessage()]);
        error_log("Error in calendar_events.php: " . $e->getMessage());
    }
} elseif ($method === 'PUT' && $event_id !== null) {
    // Update an existing calendar event
    try {
        // Debug: Log the incoming data
        error_log("Updating event ID $event_id with data: " . file_get_contents('php://input'));
        
        $updates = [];
        $params = [];
        
        if (isset($json_data['title']) && !empty($json_data['title'])) {
            $updates[] = "title = ?";
            $params[] = $json_data['title'];
        } else if (isset($json_data['title']) && empty($json_data['title'])) {
            throw new Exception('Title cannot be empty');
        }
        
        if (isset($json_data['company']) && !empty($json_data['company'])) {
            $updates[] = "company = ?";
            $params[] = $json_data['company'];
        } else if (isset($json_data['company']) && empty($json_data['company'])) {
            throw new Exception('Company cannot be empty');
        }
        
        if (isset($json_data['start']) && !empty($json_data['start'])) {
            $updates[] = "start = ?";
            $params[] = $json_data['start'];
        } else if (isset($json_data['start']) && empty($json_data['start'])) {
            throw new Exception('Start date cannot be empty');
        }
        
        if (isset($json_data['end']) && !empty($json_data['end'])) {
            $updates[] = "end = ?";
            $params[] = $json_data['end'];
        } else if (isset($json_data['end']) && empty($json_data['end'])) {
            throw new Exception('End date cannot be empty');
        }
        
        if (isset($json_data['type'])) {
            $updates[] = "type = ?";
            $params[] = $json_data['type'];
        }
        
        if (isset($json_data['status'])) {
            $updates[] = "status = ?";
            $params[] = $json_data['status'];
        }
        
        if (isset($json_data['description'])) {
            $updates[] = "description = ?";
            $params[] = $json_data['description'];
        }
        
        if (isset($json_data['reminder'])) {
            $updates[] = "reminder = ?";
            $params[] = $json_data['reminder'];
        }
        
        if (count($updates) > 0) {
            $updates[] = "updated_at = ?";
            $params[] = date('Y-m-d H:i:s');
            
            $params[] = $event_id;
            $params[] = $_SESSION['user_id'];
            
            $sql = "UPDATE calendar_events SET " . implode(", ", $updates) . " WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                $stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE id = ? AND user_id = ?");
                $stmt->execute([$event_id, $_SESSION['user_id']]);
                $updated_event = $stmt->fetch();
                
                echo json_encode($updated_event);
            } else {
                throw new Exception('Failed to update event: Database error');
            }
        } else {
            throw new Exception('No fields to update');
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        error_log("PDO Error in calendar_events.php update: " . $e->getMessage());
    } catch (Exception $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $e->getMessage()]);
        error_log("Error in calendar_events.php update: " . $e->getMessage());
    }
} elseif ($method === 'DELETE' && $event_id !== null) {
    // Delete a calendar event
    $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$event_id, $_SESSION['user_id']]);
    
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
