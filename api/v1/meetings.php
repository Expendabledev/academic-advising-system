<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/meeting.php';
require_once __DIR__ . '/../../includes/auth.php';

$auth = new Auth();
$auth->authenticate();

$meetingModel = new Meeting();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single meeting
                $meeting = $meetingModel->getById($_GET['id']);
                if (!$meeting || $meeting['supervisor_id'] != $_SESSION['user_id']) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Meeting not found']);
                    exit();
                }
                echo json_encode($meeting);
            } else {
                // List meetings
                $meetings = $meetingModel->getUpcoming($_SESSION['user_id']);
                echo json_encode($meetings);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $required = ['student_id', 'meeting_date', 'meeting_time', 'duration', 'type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Field $field is required"]);
                    exit();
                }
            }
            
            // Check availability
            if (!$meetingModel->checkAvailability(
                $_SESSION['user_id'],
                $data['meeting_date'],
                $data['meeting_time'],
                $data['duration']
            )) {
                http_response_code(400);
                echo json_encode(['error' => 'Time slot not available']);
                exit();
            }
            
            // Create meeting
            $data['supervisor_id'] = $_SESSION['user_id'];
            $id = $meetingModel->create($data);
            
            http_response_code(201);
            echo json_encode([
                'id' => $id,
                'message' => 'Meeting scheduled successfully'
            ]);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Meeting ID required']);
                exit();
            }
            
            $meeting = $meetingModel->getById($_GET['id']);
            if (!$meeting || $meeting['supervisor_id'] != $_SESSION['user_id']) {
                http_response_code(404);
                echo json_encode(['error' => 'Meeting not found']);
                exit();
            }
            
            $meetingModel->delete($_GET['id']);
            echo json_encode(['message' => 'Meeting cancelled']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>