<?php
require_once __DIR__ . '/basemodel.php';

class Course extends BaseModel {
    protected $table = 'courses';
    
    // Define valid statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ARCHIVED = 'archived';
    
    // Define valid semesters
    const SEMESTER_FIRST = 'First';
    const SEMESTER_SECOND = 'Second';
    const SEMESTER_SUMMER = 'Summer';

    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function create($data) {
        try {
            // Validate required fields
            $this->validateCourseData($data);
            
            // Check for duplicate course code
            if ($this->courseCodeExists($data['course_code'])) {
                throw new Exception("Course code already exists: " . $data['course_code']);
            }
            
            $courseData = [
                'course_code' => strtoupper(trim($data['course_code'])),
                'course_name' => trim($data['course_name']),
                'credits' => (int) $data['credits'],
                'semester' => $data['semester'],
                'year' => (int) $data['year'],
                'description' => isset($data['description']) ? trim($data['description']) : null,
                'status' => $data['status'] ?? self::STATUS_ACTIVE
            ];
            
            // Validate status
            if (!$this->isValidStatus($courseData['status'])) {
                throw new Exception("Invalid status: " . $courseData['status']);
            }
            
            // Validate semester
            if (!$this->isValidSemester($courseData['semester'])) {
                throw new Exception("Invalid semester: " . $courseData['semester']);
            }
            
            return parent::create($courseData);
        } catch (Exception $e) {
            error_log("Error creating course: " . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $data) {
        try {
            if (!$this->exists($id)) {
                throw new Exception("Course not found with ID: " . $id);
            }
            
            // Get current course data
            $currentCourse = $this->getById($id);
            if (!$currentCourse) {
                throw new Exception("Failed to retrieve current course data");
            }
            
            // Validate required fields if they're being updated
            $this->validateCourseData($data, false);
            
            // Check for duplicate course code (excluding current record)
            if (isset($data['course_code']) && 
                strtoupper(trim($data['course_code'])) !== strtoupper($currentCourse['course_code'])) {
                if ($this->courseCodeExists($data['course_code'], $id)) {
                    throw new Exception("Course code already exists: " . $data['course_code']);
                }
            }
            
            $courseData = [];
            
            // Only update fields that are provided
            if (isset($data['course_code'])) {
                $courseData['course_code'] = strtoupper(trim($data['course_code']));
            }
            if (isset($data['course_name'])) {
                $courseData['course_name'] = trim($data['course_name']);
            }
            if (isset($data['credits'])) {
                $courseData['credits'] = (int) $data['credits'];
            }
            if (isset($data['semester'])) {
                $courseData['semester'] = $data['semester'];
            }
            if (isset($data['year'])) {
                $courseData['year'] = (int) $data['year'];
            }
            if (isset($data['description'])) {
                $courseData['description'] = trim($data['description']) ?: null;
            }
            if (isset($data['status'])) {
                $courseData['status'] = $data['status'];
            }
            
            // Validate status if provided
            if (isset($courseData['status']) && !$this->isValidStatus($courseData['status'])) {
                throw new Exception("Invalid status: " . $courseData['status']);
            }
            
            // Validate semester if provided
            if (isset($courseData['semester']) && !$this->isValidSemester($courseData['semester'])) {
                throw new Exception("Invalid semester: " . $courseData['semester']);
            }
            
            return parent::update($id, $courseData);
        } catch (Exception $e) {
            error_log("Error updating course: " . $e->getMessage());
            throw $e;
        }
    }

    public function countActive() {
        try {
            return $this->count(['status' => self::STATUS_ACTIVE]);
        } catch (Exception $e) {
            error_log("Error counting active courses: " . $e->getMessage());
            return 0;
        }
    }

    public function getBySemester($semester, $year) {
        try {
            if (!$this->isValidSemester($semester)) {
                throw new Exception("Invalid semester: " . $semester);
            }
            
            if (!is_numeric($year) || $year < 2000 || $year > 2050) {
                throw new Exception("Invalid year: " . $year);
            }
            
            $query = "SELECT * FROM courses 
                     WHERE semester = :semester AND year = :year 
                     ORDER BY course_code";
            
            return $this->fetchAll($query, [
                'semester' => $semester,
                'year' => (int) $year
            ]);
        } catch (Exception $e) {
            error_log("Error getting courses by semester: " . $e->getMessage());
            return [];
        }
    }

    public function search($term) {
        try {
            if (empty(trim($term))) {
                return $this->getAll();
            }
            
            $query = "SELECT * FROM courses 
                     WHERE course_code LIKE :term 
                     OR course_name LIKE :term 
                     OR description LIKE :term
                     ORDER BY course_code";
            
            $searchTerm = '%' . trim($term) . '%';
            return $this->fetchAll($query, ['term' => $searchTerm]);
        } catch (Exception $e) {
            error_log("Error searching courses: " . $e->getMessage());
            return [];
        }
    }

    public function getByStatus($status = self::STATUS_ACTIVE) {
        try {
            if (!$this->isValidStatus($status)) {
                throw new Exception("Invalid status: " . $status);
            }
            
            $query = "SELECT * FROM courses WHERE status = :status ORDER BY course_code";
            return $this->fetchAll($query, ['status' => $status]);
        } catch (Exception $e) {
            error_log("Error getting courses by status: " . $e->getMessage());
            return [];
        }
    }

    public function getByYear($year) {
        try {
            if (!is_numeric($year) || $year < 2000 || $year > 2050) {
                throw new Exception("Invalid year: " . $year);
            }
            
            $query = "SELECT * FROM courses WHERE year = :year ORDER BY semester, course_code";
            return $this->fetchAll($query, ['year' => (int) $year]);
        } catch (Exception $e) {
            error_log("Error getting courses by year: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalCredits($semester = null, $year = null) {
        try {
            $query = "SELECT COALESCE(SUM(credits), 0) as total_credits FROM courses WHERE status = :status";
            $params = ['status' => self::STATUS_ACTIVE];

            if ($semester && $year) {
                if (!$this->isValidSemester($semester)) {
                    throw new Exception("Invalid semester: " . $semester);
                }
                if (!is_numeric($year)) {
                    throw new Exception("Invalid year: " . $year);
                }
                
                $query .= " AND semester = :semester AND year = :year";
                $params['semester'] = $semester;
                $params['year'] = (int) $year;
            }

            $result = $this->fetchOne($query, $params);
            return $result ? (int) $result['total_credits'] : 0;
        } catch (Exception $e) {
            error_log("Error getting total credits: " . $e->getMessage());
            return 0;
        }
    }

    public function getCourseWithEnrollments($courseId) {
        try {
            if (!is_numeric($courseId) || $courseId <= 0) {
                throw new Exception("Invalid course ID: " . $courseId);
            }
            
            $query = "SELECT c.*, COALESCE(COUNT(sc.student_id), 0) as enrollment_count
                     FROM courses c
                     LEFT JOIN student_courses sc ON c.id = sc.course_id
                     WHERE c.id = :course_id
                     GROUP BY c.id";
            
            return $this->fetchOne($query, ['course_id' => (int) $courseId]);
        } catch (Exception $e) {
            error_log("Error getting course with enrollments: " . $e->getMessage());
            return false;
        }
    }

    public function getPopularCourses($limit = 10) {
        try {
            if (!is_numeric($limit) || $limit <= 0) {
                $limit = 10;
            }
            
            $query = "SELECT c.*, COALESCE(COUNT(sc.student_id), 0) as enrollment_count
                     FROM courses c
                     LEFT JOIN student_courses sc ON c.id = sc.course_id
                     WHERE c.status = :status
                     GROUP BY c.id
                     ORDER BY enrollment_count DESC, c.course_name
                     LIMIT :limit";
            
            return $this->fetchAll($query, [
                'status' => self::STATUS_ACTIVE,
                'limit' => (int) $limit
            ]);
        } catch (Exception $e) {
            error_log("Error getting popular courses: " . $e->getMessage());
            return [];
        }
    }

    public function getCoursesWithLowEnrollment($threshold = 5) {
        try {
            $query = "SELECT c.*, COALESCE(COUNT(sc.student_id), 0) as enrollment_count
                     FROM courses c
                     LEFT JOIN student_courses sc ON c.id = sc.course_id
                     WHERE c.status = :status
                     GROUP BY c.id
                     HAVING enrollment_count < :threshold
                     ORDER BY enrollment_count ASC, c.course_name";
            
            return $this->fetchAll($query, [
                'status' => self::STATUS_ACTIVE,
                'threshold' => (int) $threshold
            ]);
        } catch (Exception $e) {
            error_log("Error getting courses with low enrollment: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableYears() {
        try {
            $query = "SELECT DISTINCT year FROM courses ORDER BY year DESC";
            $results = $this->fetchAll($query);
            
            return array_column($results, 'year');
        } catch (Exception $e) {
            error_log("Error getting available years: " . $e->getMessage());
            return [];
        }
    }

    public function courseCodeExists($courseCode, $excludeId = null) {
        try {
            $query = "SELECT 1 FROM courses WHERE course_code = :course_code";
            $params = ['course_code' => strtoupper(trim($courseCode))];
            
            if ($excludeId) {
                $query .= " AND id != :exclude_id";
                $params['exclude_id'] = (int) $excludeId;
            }
            
            $result = $this->fetchOne($query, $params);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Error checking course code existence: " . $e->getMessage());
            return false;
        }
    }

    private function validateCourseData($data, $isCreate = true) {
        $errors = [];
        
        if ($isCreate || isset($data['course_code'])) {
            if (empty($data['course_code'])) {
                $errors[] = "Course code is required";
            } elseif (!preg_match('/^[A-Z0-9]{3,10}$/', strtoupper(trim($data['course_code'])))) {
                $errors[] = "Course code must be 3-10 alphanumeric characters";
            }
        }
        
        if ($isCreate || isset($data['course_name'])) {
            if (empty($data['course_name'])) {
                $errors[] = "Course name is required";
            } elseif (strlen(trim($data['course_name'])) < 3) {
                $errors[] = "Course name must be at least 3 characters long";
            }
        }
        
        if ($isCreate || isset($data['credits'])) {
            if (!isset($data['credits']) || !is_numeric($data['credits'])) {
                $errors[] = "Credits must be a number";
            } elseif ($data['credits'] < 1 || $data['credits'] > 10) {
                $errors[] = "Credits must be between 1 and 10";
            }
        }
        
        if ($isCreate || isset($data['semester'])) {
            if (empty($data['semester'])) {
                $errors[] = "Semester is required";
            }
        }
        
        if ($isCreate || isset($data['year'])) {
            if (!isset($data['year']) || !is_numeric($data['year'])) {
                $errors[] = "Year must be a number";
            } elseif ($data['year'] < 2000 || $data['year'] > 2050) {
                $errors[] = "Year must be between 2000 and 2050";
            }
        }
        
        if (!empty($errors)) {
            throw new Exception("Validation errors: " . implode(", ", $errors));
        }
    }

    private function isValidStatus($status) {
        return in_array($status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_ARCHIVED]);
    }

    private function isValidSemester($semester) {
        return in_array($semester, [self::SEMESTER_FIRST, self::SEMESTER_SECOND, self::SEMESTER_SUMMER]);
    }
}
?>