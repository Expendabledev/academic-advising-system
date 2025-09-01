<?php
require_once __DIR__ . '/../models/meeting.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../includes/auth.php';

class MeetingsController {
    private $meetingModel;
    private $studentModel;
    private $auth;

    public function __construct() {
        $this->meetingModel = new Meeting();
        $this->studentModel = new Student();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }

    public function index() {
        try {
            $upcomingMeetings = $this->meetingModel->getUpcoming($this->auth->getUserId());
            require_once __DIR__ . '/../views/meetings/index.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function create() {
        try {
            $students = $this->studentModel->getBySupervisor($this->auth->getUserId());
           
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateMeetingData($_POST);
               
                if (!$this->meetingModel->checkAvailability(
                    $this->auth->getUserId(),
                    $_POST['meeting_date'],
                    $_POST['meeting_time'],
                    $_POST['duration']
                )) {
                    throw new Exception("The selected time slot is not available", 400);
                }
               
                $data = [
                    'student_id' => $_POST['student_id'],
                    'supervisor_id' => $this->auth->getUserId(),
                    'meeting_date' => $_POST['meeting_date'],
                    'meeting_time' => $_POST['meeting_time'],
                    'duration' => $_POST['duration'],
                    'type' => $_POST['type'],
                    'purpose' => $_POST['purpose']
                ];
               
                $id = $this->meetingModel->create($data);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Meeting scheduled successfully'];
                header('Location: /meetings/view.php?id=' . $id);
                exit();
            }
           
            require_once __DIR__ . '/../views/meetings/create.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function validateMeetingData($data) {
        $required = ['student_id', 'meeting_date', 'meeting_time', 'duration', 'type', 'purpose'];
       
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required", 400);
            }
        }
       
        if (strtotime($data['meeting_date']) < strtotime('today')) {
            throw new Exception("Meeting date cannot be in the past", 400);
        }
       
        if (!is_numeric($data['duration']) || $data['duration'] <= 0) {
            throw new Exception("Duration must be a positive number", 400);
        }
    }

    private function handleError(Exception $e) {
        error_log($e->getMessage());
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
       
        if ($e->getCode() === 404) {
            header('HTTP/1.0 404 Not Found');
            require_once __DIR__ . '/../views/errors/404.php';
            exit();
        }
       
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/meetings');
        exit();
    }
}
?>