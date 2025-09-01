<?php
// models/student.php
require_once __DIR__ . '/basemodel.php';

class Student extends BaseModel {
    protected $table = 'students';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function create($data) {
        $data = $this->sanitizeInput($data);
        $this->validateStudentData($data);
        
        $query = "INSERT INTO students (
            user_id, student_id, full_name, email,
            program, academic_year, gpa, supervisor_id, status, created_at
        ) VALUES (
            :user_id, :student_id, :full_name, :email,
            :program, :academic_year, :gpa, :supervisor_id, :status, NOW()
        )";
        
        $params = [
            'user_id' => $data['user_id'],
            'student_id' => $data['student_id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'program' => $data['program'],
            'academic_year' => $data['academic_year'],
            'gpa' => $data['gpa'] ?? null,
            'supervisor_id' => $data['supervisor_id'],
            'status' => $data['status'] ?? 'active'
        ];
        
        $stmt = $this->executeQuery($query, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $data = $this->sanitizeInput($data);
        $this->validateStudentData($data, false); // false = not creating, so some fields optional
        
        $query = "UPDATE students SET
            student_id = :student_id,
            full_name = :full_name,
            email = :email,
            program = :program,
            academic_year = :academic_year,
            gpa = :gpa,
            status = :status,
            updated_at = NOW()
            WHERE id = :id";
        
        $params = [
            'id' => $id,
            'student_id' => $data['student_id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'program' => $data['program'],
            'academic_year' => $data['academic_year'],
            'gpa' => $data['gpa'] ?? null,
            'status' => $data['status'] ?? 'active'
        ];
        
        return $this->executeQuery($query, $params)->rowCount();
    }
    
    public function getBySupervisor($supervisorId) {
        $query = "SELECT * FROM students WHERE supervisor_id = :supervisor_id ORDER BY created_at DESC";
        $stmt = $this->executeQuery($query, ['supervisor_id' => $supervisorId]);
        return $stmt->fetchAll();
    }
    
    public function search($term, $supervisorId = null) {
        $query = "SELECT * FROM students
                 WHERE (full_name LIKE :term OR student_id LIKE :term OR email LIKE :term OR program LIKE :term)";
        
        $params = ['term' => "%$term%"];
        
        if ($supervisorId) {
            $query .= " AND supervisor_id = :supervisor_id";
            $params['supervisor_id'] = $supervisorId;
        }
        
        $query .= " ORDER BY full_name ASC";
        
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetchAll();
    }
    
    public function getByStatus($status, $supervisorId = null) {
        $query = "SELECT * FROM students WHERE status = :status";
        $params = ['status' => $status];
        
        if ($supervisorId) {
            $query .= " AND supervisor_id = :supervisor_id";
            $params['supervisor_id'] = $supervisorId;
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetchAll();
    }
    
    public function getStudentStats($supervisorId = null) {
        $stats = [];
        
        // Total students
        $conditions = $supervisorId ? ['supervisor_id' => $supervisorId] : [];
        $stats['total'] = $this->count($conditions);
        
        // Active students
        $activeConditions = ['status' => 'active'];
        if ($supervisorId) $activeConditions['supervisor_id'] = $supervisorId;
        $stats['active'] = $this->count($activeConditions);
        
        // Completed students
        $completedConditions = ['status' => 'completed'];
        if ($supervisorId) $completedConditions['supervisor_id'] = $supervisorId;
        $stats['completed'] = $this->count($completedConditions);
        
        // Success rate
        $stats['success_rate'] = $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0;
        
        // Average GPA
        $gpaQuery = "SELECT AVG(gpa) as avg_gpa FROM students WHERE gpa IS NOT NULL";
        $gpaParams = [];
        
        if ($supervisorId) {
            $gpaQuery .= " AND supervisor_id = :supervisor_id";
            $gpaParams['supervisor_id'] = $supervisorId;
        }
        
        $avgGpaResult = $this->executeQuery($gpaQuery, $gpaParams)->fetch();
        $stats['avg_gpa'] = $avgGpaResult ? round($avgGpaResult['avg_gpa'], 2) : 0;
        
        return $stats;
    }
    
    public function getRecentStudents($limit = 5, $supervisorId = null) {
        $query = "SELECT * FROM students";
        $params = [];
        
        if ($supervisorId) {
            $query .= " WHERE supervisor_id = :supervisor_id";
            $params['supervisor_id'] = $supervisorId;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        if ($supervisorId) {
            $stmt->bindParam(':supervisor_id', $supervisorId, PDO::PARAM_INT);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function checkStudentIdExists($studentId, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM students WHERE student_id = :student_id";
        $params = ['student_id' => $studentId];
        
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->executeQuery($query, $params)->fetchColumn() > 0;
    }
    
    public function checkEmailExists($email, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM students WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->executeQuery($query, $params)->fetchColumn() > 0;
    }
    
    private function validateStudentData($data, $isCreating = true) {
        if ($isCreating) {
            $required = ['user_id', 'student_id', 'full_name', 'email', 'program', 'academic_year', 'supervisor_id'];
            $this->validateRequired($data, $required);
        } else {
            $required = ['student_id', 'full_name', 'email', 'program', 'academic_year'];
            $this->validateRequired($data, $required);
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format", 400);
        }
        
        // Validate GPA if provided
        if (isset($data['gpa']) && $data['gpa'] !== null && $data['gpa'] !== '') {
            $gpa = floatval($data['gpa']);
            if ($gpa < 0 || $gpa > 4.0) {
                throw new Exception("GPA must be between 0.0 and 4.0", 400);
            }
        }
        
        // Validate student_id format (you can customize this)
        if (!preg_match('/^[A-Z]{2}\d{4}\d{3}$/', $data['student_id'])) {
            // Format: CS2025001 (2 letters, 4 digits for year, 3 digits for sequence)
            if (!preg_match('/^[A-Z]{2,}\d+$/', $data['student_id'])) {
                throw new Exception("Invalid student ID format", 400);
            }
        }
        
        // Check for duplicate student_id
        $excludeId = $isCreating ? null : ($data['id'] ?? null);
        if ($this->checkStudentIdExists($data['student_id'], $excludeId)) {
            throw new Exception("Student ID already exists", 400);
        }
        
        // Check for duplicate email
        if ($this->checkEmailExists($data['email'], $excludeId)) {
            throw new Exception("Email already exists", 400);
        }
        
        // Validate academic year
        $validYears = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Graduate'];
        if (!in_array($data['academic_year'], $validYears)) {
            throw new Exception("Invalid academic year", 400);
        }
        
        // Validate status
        if (isset($data['status'])) {
            $validStatuses = ['active', 'inactive', 'completed', 'suspended', 'graduated'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new Exception("Invalid status", 400);
            }
        }
    }
}
?>