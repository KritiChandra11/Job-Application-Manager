<?php
require_once 'config.php';

class Event {
    // Get all events for a user
    public static function getAllForUser($user_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY date ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    // Get single event by ID and user ID
    public static function getById($id, $user_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch();
    }
    
    // Count all events for a user
    public static function countByUserId($user_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
    
    // Count events by status for a user
    public static function countByUserIdAndStatus($user_id, $status) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE user_id = ? AND status = ?");
        $stmt->execute([$user_id, $status]);
        return $stmt->fetchColumn();
    }
    
    // Count combined interviews from events and calendar_events tables
    public static function countCombinedInterviews($user_id) {
        global $pdo;
        // Count interviews from events table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE user_id = ? AND status = 'Interview'");
        $stmt->execute([$user_id]);
        $event_interviews = $stmt->fetchColumn();
        
        // Count interviews from calendar_events table - consider both type='interview' and status='Interview'
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM calendar_events 
            WHERE user_id = ? AND (type = 'interview' OR status = 'Interview')
        ");
        $stmt->execute([$user_id]);
        $calendar_interviews = $stmt->fetchColumn();
        
        return $event_interviews + $calendar_interviews;
    }
    
    // Get events by status for a user
    public static function getByUserIdAndStatus($user_id, $status, $limit = null) {
        global $pdo;
        $sql = "SELECT * FROM events WHERE user_id = ? AND status = ? ORDER BY date ASC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
        }
        $stmt = $pdo->prepare($sql);
        
        if ($limit !== null) {
            $stmt->execute([$user_id, $status, $limit]);
        } else {
            $stmt->execute([$user_id, $status]);
        }
        
        return $stmt->fetchAll();
    }
    
    // Create a new event
    public static function create($user_id, $title, $company, $description, $date, $status = 'Pending') {
        global $pdo;
        $created_at = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("INSERT INTO events (user_id, title, company, description, date, status, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $title, $company, $description, $date, $status, $created_at, $created_at]);
    }
    
    // Update an existing event
    public static function update($id, $user_id, $data) {
        global $pdo;
        $event = self::getById($id, $user_id);
        
        if (!$event) {
            return false;
        }
        
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'company', 'description', 'date', 'status'])) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = date('Y-m-d H:i:s'); // updated_at
        $values[] = $id;
        $values[] = $user_id;
        
        $stmt = $pdo->prepare("UPDATE events SET " . implode(', ', $fields) . ", updated_at = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute($values);
    }
    
    // Delete an event
    public static function delete($id, $user_id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    }
}
?>
