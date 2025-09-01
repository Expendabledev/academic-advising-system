<?php
require_once __DIR__ . '/BaseModel.php';

class Report extends BaseModel {
    protected $table = 'reports';

    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function generateStudentProgressReport($supervisorId) {
        try {
            $query = "SELECT
                        s.id, s.student_id, s.full_name, s.program, s.academic_year, s.gpa,
                        COUNT(sc.id) AS total_courses,
                        SUM(CASE WHEN sc.completion_status = 'completed' THEN 1 ELSE 0 END) AS completed_courses,
                        SUM(CASE WHEN sc.completion_status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_courses,
                        SUM(CASE WHEN sc.completion_status = 'not_started' THEN 1 ELSE 0 END) AS not_started_courses,
                        AVG(CASE 
                            WHEN sc.grade IN ('A', 'A+') THEN 4.0
                            WHEN sc.grade = 'A-' THEN 3.7
                            WHEN sc.grade = 'B+' THEN 3.3
                            WHEN sc.grade = 'B' THEN 3.0
                            WHEN sc.grade = 'B-' THEN 2.7
                            WHEN sc.grade = 'C+' THEN 2.3
                            WHEN sc.grade = 'C' THEN 2.0
                            WHEN sc.grade = 'C-' THEN 1.7
                            WHEN sc.grade = 'D+' THEN 1.3
                            WHEN sc.grade = 'D' THEN 1.0
                            WHEN sc.grade = 'F' THEN 0.0
                            ELSE NULL 
                        END) AS course_gpa
                     FROM students s
                     LEFT JOIN student_courses sc ON s.id = sc.student_id
                     WHERE s.supervisor_id = :supervisor_id
                     GROUP BY s.id, s.student_id, s.full_name, s.program, s.academic_year, s.gpa
                     ORDER BY s.full_name";

            return $this->fetchAll($query, ['supervisor_id' => $supervisorId]);
        } catch (Exception $e) {
            error_log("Error generating student progress report: " . $e->getMessage());
            return [];
        }
    }

    public function generateMeetingReport($supervisorId, $startDate = null, $endDate = null) {
        try {
            $query = "SELECT
                        m.id, m.meeting_date, m.meeting_time, m.duration, m.type, m.status,
                        s.student_id, s.full_name as student_name, s.program,
                        m.purpose, m.notes
                     FROM meetings m
                     JOIN students s ON m.student_id = s.id
                     WHERE m.supervisor_id = :supervisor_id";

            $params = ['supervisor_id' => $supervisorId];

            if ($startDate && $endDate) {
                $query .= " AND m.meeting_date BETWEEN :start_date AND :end_date";
                $params['start_date'] = $startDate;
                $params['end_date'] = $endDate;
            } elseif ($startDate) {
                $query .= " AND m.meeting_date >= :start_date";
                $params['start_date'] = $startDate;
            }

            $query .= " ORDER BY m.meeting_date DESC, m.meeting_time DESC";

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error generating meeting report: " . $e->getMessage());
            return [];
        }
    }

    public function generateEnrollmentReport($supervisorId = null) {
        try {
            $query = "SELECT
                        c.course_code, c.course_name, c.credits, c.semester, c.year,
                        COUNT(sc.student_id) as enrollment_count,
                        SUM(CASE WHEN sc.completion_status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                        SUM(CASE WHEN sc.completion_status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
                        AVG(CASE 
                            WHEN sc.grade IN ('A', 'A+') THEN 4.0
                            WHEN sc.grade = 'A-' THEN 3.7
                            WHEN sc.grade = 'B+' THEN 3.3
                            WHEN sc.grade = 'B' THEN 3.0
                            WHEN sc.grade = 'B-' THEN 2.7
                            WHEN sc.grade = 'C+' THEN 2.3
                            WHEN sc.grade = 'C' THEN 2.0
                            WHEN sc.grade = 'C-' THEN 1.7
                            WHEN sc.grade = 'D+' THEN 1.3
                            WHEN sc.grade = 'D' THEN 1.0
                            WHEN sc.grade = 'F' THEN 0.0
                            ELSE NULL 
                        END) AS average_grade
                     FROM courses c
                     LEFT JOIN student_courses sc ON c.id = sc.course_id";

            $params = [];

            if ($supervisorId) {
                $query .= " LEFT JOIN students s ON sc.student_id = s.id
                           WHERE s.supervisor_id = :supervisor_id";
                $params['supervisor_id'] = $supervisorId;
            }

            $query .= " GROUP BY c.id, c.course_code, c.course_name, c.credits, c.semester, c.year
                       ORDER BY c.year DESC, c.semester, c.course_code";

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error generating enrollment report: " . $e->getMessage());
            return [];
        }
    }

    public function saveReport($supervisorId, $type, $filePath, $timePeriod = null) {
        try {
            $reportData = [
                'supervisor_id' => $supervisorId,
                'report_type' => $type,
                'file_path' => $filePath,
                'time_period' => $timePeriod ?? date('Y-m')
            ];

            return parent::create($reportData);
        } catch (Exception $e) {
            error_log("Error saving report: " . $e->getMessage());
            return false;
        }
    }

    public function getReportsBySupervisor($supervisorId, $limit = null) {
        try {
            $query = "SELECT * FROM reports 
                     WHERE supervisor_id = :supervisor_id 
                     ORDER BY generated_at DESC";

            $params = ['supervisor_id' => $supervisorId];

            if ($limit) {
                $query .= " LIMIT :limit";
                $params['limit'] = $limit;
            }

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error getting reports by supervisor: " . $e->getMessage());
            return [];
        }
    }

    public function getReportsByType($type, $supervisorId = null) {
        try {
            $query = "SELECT * FROM reports WHERE report_type = :report_type";
            $params = ['report_type' => $type];

            if ($supervisorId) {
                $query .= " AND supervisor_id = :supervisor_id";
                $params['supervisor_id'] = $supervisorId;
            }

            $query .= " ORDER BY generated_at DESC";

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error getting reports by type: " . $e->getMessage());
            return [];
        }
    }

    public function deleteOldReports($daysOld = 90) {
        try {
            $query = "DELETE FROM reports 
                     WHERE generated_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

            $stmt = $this->executeQuery($query, ['days' => $daysOld]);
            return $stmt ? $stmt->rowCount() : 0;
        } catch (Exception $e) {
            error_log("Error deleting old reports: " . $e->getMessage());
            return 0;
        }
    }

    public function getReportStatistics($supervisorId) {
        try {
            $query = "SELECT 
                        report_type,
                        COUNT(*) as report_count,
                        MAX(generated_at) as last_generated,
                        MIN(generated_at) as first_generated
                     FROM reports 
                     WHERE supervisor_id = :supervisor_id
                     GROUP BY report_type
                     ORDER BY report_count DESC";

            return $this->fetchAll($query, ['supervisor_id' => $supervisorId]);
        } catch (Exception $e) {
            error_log("Error getting report statistics: " . $e->getMessage());
            return [];
        }
    }

    public function generateGPADistributionReport($supervisorId = null) {
        try {
            $query = "SELECT 
                        CASE 
                            WHEN s.gpa >= 3.5 THEN 'High (3.5-4.0)'
                            WHEN s.gpa >= 3.0 THEN 'Good (3.0-3.49)'
                            WHEN s.gpa >= 2.5 THEN 'Average (2.5-2.99)'
                            WHEN s.gpa >= 2.0 THEN 'Below Average (2.0-2.49)'
                            WHEN s.gpa IS NOT NULL THEN 'Poor (Below 2.0)'
                            ELSE 'No GPA'
                        END as gpa_range,
                        COUNT(*) as student_count,
                        AVG(s.gpa) as average_gpa
                     FROM students s";

            $params = [];

            if ($supervisorId) {
                $query .= " WHERE s.supervisor_id = :supervisor_id";
                $params['supervisor_id'] = $supervisorId;
            }

            $query .= " GROUP BY gpa_range
                       ORDER BY average_gpa DESC";

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error generating GPA distribution report: " . $e->getMessage());
            return [];
        }
    }

    public function generateAcademicYearReport($supervisorId = null) {
        try {
            $query = "SELECT 
                        s.academic_year,
                        COUNT(*) as student_count,
                        AVG(s.gpa) as average_gpa,
                        COUNT(CASE WHEN s.status = 'active' THEN 1 END) as active_students,
                        COUNT(CASE WHEN s.status = 'graduated' THEN 1 END) as graduated_students
                     FROM students s";

            $params = [];

            if ($supervisorId) {
                $query .= " WHERE s.supervisor_id = :supervisor_id";
                $params['supervisor_id'] = $supervisorId;
            }

            $query .= " GROUP BY s.academic_year
                       ORDER BY 
                           CASE s.academic_year
                               WHEN '1st Year' THEN 1
                               WHEN '2nd Year' THEN 2
                               WHEN '3rd Year' THEN 3
                               WHEN '4th Year' THEN 4
                               WHEN 'Graduate' THEN 5
                               ELSE 6
                           END";

            return $this->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error generating academic year report: " . $e->getMessage());
            return [];
        }
    }
}
?>