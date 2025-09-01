<?php
require_once __DIR__ . '/../models/course.php';
require_once __DIR__ . '/../includes/auth.php';

class CoursesController {
    private $courseModel;
    private $auth;

    public function __construct() {
        $this->courseModel = new Course();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }

    public function index() {
        try {
            $currentYear = date('Y');
            $currentSemester = $this->getCurrentSemester();
            
            $courses = $this->courseModel->getBySemester($currentSemester, $currentYear);
            require_once __DIR__ . '/../views/courses/index.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCourseData($_POST);
                
                $data = [
                    'course_code' => $_POST['course_code'],
                    'course_name' => $_POST['course_name'],
                    'credits' => $_POST['credits'],
                    'semester' => $_POST['semester'],
                    'year' => $_POST['year'],
                    'description' => $_POST['description'] ?? null,
                    'status' => 'active'
                ];
                
                $id = $this->courseModel->create($data);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Course created successfully'];
                header('Location: /courses/view.php?id=' . $id);
                exit();
            }
            
            $currentYear = date('Y');
            $currentSemester = $this->getCurrentSemester();
            
            require_once __DIR__ . '/../views/courses/create.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function getCurrentSemester() {
        $month = date('n');
        if ($month >= 1 && $month <= 5) {
            return 'Spring';
        } elseif ($month >= 8 && $month <= 12) {
            return 'Fall';
        } else {
            return 'Summer';
        }
    }

    private function validateCourseData($data) {
        $required = ['course_code', 'course_name', 'credits', 'semester', 'year'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required", 400);
            }
        }
        
        if (!is_numeric($data['credits']) || $data['credits'] <= 0) {
            throw new Exception("Credits must be a positive number", 400);
        }
        
        if (!is_numeric($data['year']) || $data['year'] < 2000 || $data['year'] > 2100) {
            throw new Exception("Invalid year", 400);
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
        
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/courses');
        exit();
    }
}
?>