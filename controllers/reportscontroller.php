<?php
require_once __DIR__ . '/../models/report.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../includes/auth.php';

class ReportsController {
    private $reportModel;
    private $studentModel;
    private $auth;

    public function __construct() {
        $this->reportModel = new Report();
        $this->studentModel = new Student();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }

    public function index() {
        try {
            $reports = $this->reportModel->getReportsBySupervisor($this->auth->getUserId());
            require_once __DIR__ . '/../views/reports/index.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function generate() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $type = $_POST['report_type'];
                $format = $_POST['format'];
               
                switch ($type) {
                    case 'student_progress':
                        $data = $this->reportModel->generateStudentProgressReport($this->auth->getUserId());
                        $fileName = $this->generateStudentProgressReport($data, $format);
                        break;
                       
                    default:
                        throw new Exception("Invalid report type", 400);
                }
               
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Report generated successfully'];
                header('Location: /reports/download.php?file=' . urlencode($fileName));
                exit();
            }
           
            require_once __DIR__ . '/../views/reports/generate.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function generateStudentProgressReport($data, $format) {
        $fileName = 'student_progress_' . date('Ymd_His') . '.' . $format;
        $filePath = __DIR__ . '/../../uploads/reports/' . $fileName;
       
        switch ($format) {
            case 'csv':
                $this->generateCSV($data, $filePath);
                break;
               
            case 'pdf':
                // Requires a PDF library like TCPDF or Dompdf
                $this->generatePDF($data, $filePath);
                break;
               
            default:
                throw new Exception("Unsupported format", 400);
        }
       
        // Save report metadata to database
        $this->reportModel->saveReport(
            $this->auth->getUserId(),
            'student_progress',
            'uploads/reports/' . $fileName
        );
       
        return $fileName;
    }

    private function generateCSV($data, $filePath) {
        $fp = fopen($filePath, 'w');
       
        // Write header
        fputcsv($fp, [
            'Student ID', 'Name', 'Program', 'Year', 'GPA',
            'Total Courses', 'Completed Courses', 'In Progress Courses'
        ]);
       
        // Write data
        foreach ($data as $row) {
            fputcsv($fp, [
                $row['student_id'],
                $row['full_name'],
                $row['program'],
                $row['academic_year'],
                $row['gpa'],
                $row['total_courses'],
                $row['completed_courses'],
                $row['in_progress_courses']
            ]);
        }
       
        fclose($fp);
    }

    private function generatePDF($data, $filePath) {
        // This is a placeholder - you'll need to implement PDF generation
        // using a library like TCPDF, Dompdf, or mPDF
        throw new Exception("PDF generation not implemented yet", 501);
    }

    private function handleError(Exception $e) {
        error_log($e->getMessage());
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
       
        if ($e->getCode() === 404) {
            header('HTTP/1.0 404 Not Found');
            require_once __DIR__ . '/../views/errors/404.php';
            exit();
        }
       
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/reports');
        exit();
    }
}
?>