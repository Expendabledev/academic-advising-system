<?php
// controllers/studentscontroller.php
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../includes/auth.php';

class StudentsController {
    private $studentModel;
    private $auth;
    
    public function __construct() {
        $this->studentModel = new Student();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }
    
    public function index() {
        try {
            if ($this->auth->isSupervisor()) {
                $students = $this->studentModel->getBySupervisor($this->auth->getUserId());
            } else {
                $students = $this->studentModel->getAll();
            }
            
            return $students;
        } catch (Exception $e) {
            $this->handleError($e);
            return [];
        }
    }
    
    public function create($data = null) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || $data) {
                $postData = $data ?? $_POST;
                $this->validateStudentData($postData);
                
                $studentData = [
                    'user_id' => $this->auth->getUserId(),
                    'student_id' => $postData['student_id'],
                    'full_name' => $postData['full_name'],
                    'email' => $postData['email'],
                    'program' => $postData['program'],
                    'academic_year' => $postData['academic_year'],
                    'gpa' => !empty($postData['gpa']) ? floatval($postData['gpa']) : null,
                    'supervisor_id' => $this->auth->getUserId(),
                    'status' => $postData['status'] ?? 'active'
                ];
                
                $id = $this->studentModel->create($studentData);
                
                if ($data) {
                    return ['success' => true, 'id' => $id, 'message' => 'Student created successfully'];
                } else {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Student created successfully'];
                    header('Location: /dashboard.php?tab=students&success=created');
                    exit();
                }
            }
        } catch (Exception $e) {
            if ($data) {
                return ['success' => false, 'message' => $e->getMessage()];
            } else {
                $this->handleError($e);
            }
        }
    }
    
    public function view($id) {
        try {
            $student = $this->studentModel->getById($id);
            
            if (!$student) {
                throw new Exception('Student not found', 404);
            }
            
            // Check if supervisor can view this student
            if ($this->auth->isSupervisor() && $student['supervisor_id'] != $this->auth->getUserId()) {
                throw new Exception('Access denied', 403);
            }
            
            return $student;
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }
    
    public function update($id, $data = null) {
        try {
            $student = $this->studentModel->getById($id);
            
            if (!$student) {
                throw new Exception('Student not found', 404);
            }
            
            // Check if supervisor can update this student
            if ($this->auth->isSupervisor() && $student['supervisor_id'] != $this->auth->getUserId()) {
                throw new Exception('Access denied', 403);
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || $data) {
                $postData = $data ?? $_POST;
                $this->validateStudentData($postData, false);
                
                $updateData = [
                    'student_id' => $postData['student_id'],
                    'full_name' => $postData['full_name'],
                    'email' => $postData['email'],
                    'program' => $postData['program'],
                    'academic_year' => $postData['academic_year'],
                    'gpa' => !empty($postData['gpa']) ? floatval($postData['gpa']) : null,
                    'status' => $postData['status'] ?? 'active',
                    'id' => $id
                ];
                
                $rowsAffected = $this->studentModel->update($id, $updateData);
                
                if ($data) {
                    return ['success' => true, 'message' => 'Student updated successfully', 'rows_affected' => $rowsAffected];
                } else {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Student updated successfully'];
                    header('Location: /dashboard.php?tab=students&success=updated');
                    exit();
                }
            }
            
            return $student;
        } catch (Exception $e) {
            if ($data) {
                return ['success' => false, 'message' => $e->getMessage()];
            } else {
                $this->handleError($e);
            }
        }
    }
    
    public function delete($id) {
        try {
            $student = $this->studentModel->getById($id);
            
            if (!$student) {
                throw new Exception('Student not found', 404);
            }
            
            // Check if supervisor can delete this student
            if ($this->auth->isSupervisor() && $student['supervisor_id'] != $this->auth->getUserId()) {
                throw new Exception('Access denied', 403);
            }
            
            $rowsAffected = $this->studentModel->delete($id);
            
            return ['success' => true, 'message' => 'Student deleted successfully', 'rows_affected' => $rowsAffected];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function search($term, $returnJson = false) {
        try {
            $supervisorId = $this->auth->isSupervisor() ? $this->auth->getUserId() : null;
            $students = $this->studentModel->search($term, $supervisorId);
            
            if ($returnJson) {
                return ['success' => true, 'students' => $students];
            }
            
            return $students;
        } catch (Exception $e) {
            if ($returnJson) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
            
            $this->handleError($e);
            return [];
        }
    }
    
    public function getStatistics() {
        try {
            $supervisorId = $this->auth->isSupervisor() ? $this->auth->getUserId() : null;
            return $this->studentModel->getStudentStats($supervisorId);
        } catch (Exception $e) {
            error_log("Error getting student statistics: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'success_rate' => 0,
                'avg_gpa' => 0
            ];
        }
    }
    
    public function getRecentStudents($limit = 5) {
        try {
            $supervisorId = $this->auth->isSupervisor() ? $this->auth->getUserId() : null;
            return $this->studentModel->getRecentStudents($limit, $supervisorId);
        } catch (Exception $e) {
            error_log("Error getting recent students: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByStatus($status) {
        try {
            $supervisorId = $this->auth->isSupervisor() ? $this->auth->getUserId() : null;
            return $this->studentModel->getByStatus($status, $supervisorId);
        } catch (Exception $e) {
            error_log("Error getting students by status: " . $e->getMessage());
            return [];
        }
    }
    
    public function handleAjaxRequest() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }
            
            if (!isset($_POST['action'])) {
                throw new Exception('Action not specified', 400);
            }
            
            header('Content-Type: application/json');
            
            switch ($_POST['action']) {
                case 'add_student':
                    $result = $this->create($_POST);
                    echo json_encode($result);
                    break;
                    
                case 'update_student':
                    $studentId = $_POST['student_id'] ?? null;
                    if (!$studentId) {
                        throw new Exception('Student ID is required');
                    }
                    $result = $this->update($studentId, $_POST);
                    echo json_encode($result);
                    break;
                    
                case 'delete_student':
                    $studentId = $_POST['student_id'] ?? null;
                    if (!$studentId) {
                        throw new Exception('Student ID is required');
                    }
                    $result = $this->delete($studentId);
                    echo json_encode($result);
                    break;
                    
                case 'search_students':
                    $searchTerm = $_POST['search_term'] ?? '';
                    $result = $this->search($searchTerm, true);
                    echo json_encode($result);
                    break;
                    
                case 'get_student':
                    $studentId = $_POST['student_id'] ?? null;
                    if (!$studentId) {
                        throw new Exception('Student ID is required');
                    }
                    $student = $this->view($studentId);
                    echo json_encode(['success' => true, 'student' => $student]);
                    break;
                    
                case 'get_statistics':
                    $stats = $this->getStatistics();
                    echo json_encode(['success' => true, 'statistics' => $stats]);
                    break;
                    
                case 'get_recent_students':
                    $limit = $_POST['limit'] ?? 5;
                    $students = $this->getRecentStudents($limit);
                    echo json_encode(['success' => true, 'students' => $students]);
                    break;
                    
                default:
                    throw new Exception('Unknown action', 400);
            }
            
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit();
    }
    
    private function validateStudentData($data, $isCreating = true) {
        if ($isCreating) {
            $required = ['student_id', 'full_name', 'email', 'program', 'academic_year'];
        } else {
            $required = ['student_id', 'full_name', 'email', 'program', 'academic_year'];
        }
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required", 400);
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format", 400);
        }
        
        if (isset($data['gpa']) && !empty($data['gpa'])) {
            $gpa = floatval($data['gpa']);
            if ($gpa < 0 || $gpa > 4.0) {
                throw new Exception("GPA must be between 0.0 and 4.0", 400);
            }
        }
        
        // Validate academic year
        $validYears = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Graduate'];
        if (!in_array($data['academic_year'], $validYears)) {
            throw new Exception("Invalid academic year", 400);
        }
        
        // Validate status if provided
        if (isset($data['status'])) {
            $validStatuses = ['active', 'inactive', 'completed', 'suspended', 'graduated'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new Exception("Invalid status", 400);
            }
        }
    }
    
    private function handleError(Exception $e) {
        error_log("StudentsController Error: " . $e->getMessage());
        
        if (isset($_SESSION)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
        }
        
        $code = $e->getCode();
        
        if ($code === 404) {
            header('HTTP/1.0 404 Not Found');
            if (file_exists(__DIR__ . '/../views/errors/404.php')) {
                require_once __DIR__ . '/../views/errors/404.php';
            } else {
                echo "404 - Student Not Found";
            }
            exit();
        } elseif ($code === 403) {
            header('HTTP/1.0 403 Forbidden');
            if (file_exists(__DIR__ . '/../views/errors/403.php')) {
                require_once __DIR__ . '/../views/errors/403.php';
            } else {
                echo "403 - Access Denied";
            }
            exit();
        }
        
        // For other errors, redirect back
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/dashboard.php';
        header('Location: ' . $referrer);
        exit();
    }
    
    public function export($format = 'csv') {
        try {
            $supervisorId = $this->auth->isSupervisor() ? $this->auth->getUserId() : null;
            $students = $supervisorId ? $this->studentModel->getBySupervisor($supervisorId) : $this->studentModel->getAll();
            
            switch (strtolower($format)) {
                case 'csv':
                    $this->exportCSV($students);
                    break;
                case 'json':
                    $this->exportJSON($students);
                    break;
                default:
                    throw new Exception('Unsupported export format');
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    private function exportCSV($students) {
        $filename = 'students_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, ['Student ID', 'Full Name', 'Email', 'Program', 'Academic Year', 'GPA', 'Status', 'Created Date']);
        
        foreach ($students as $student) {
            fputcsv($output, [
                $student['student_id'],
                $student['full_name'],
                $student['email'],
                $student['program'],
                $student['academic_year'],
                $student['gpa'] ?? 'N/A',
                $student['status'],
                date('Y-m-d', strtotime($student['created_at']))
            ]);
        }
        
        fclose($output);
        exit();
    }
    
    private function exportJSON($students) {
        $filename = 'students_export_' . date('Y-m-d_H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($students, JSON_PRETTY_PRINT);
        exit();
    }
}