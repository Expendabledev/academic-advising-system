<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../model/student.php';
require_once __DIR__ . '/../model/course.php';  
require_once __DIR__ . '/../model/meeting.php';

class DashboardController {
    private $auth;
    private $db;
    // private $studentModel;
    // private $courseModel;
    // private $meetingModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->auth = new Auth();
        
        // Temporarily comment out until models exist
        // $this->studentModel = new Student($this->db);
        // $this->courseModel = new Course($this->db);
        // $this->meetingModel = new Meeting($this->db);
    }

    public function index() {
        $this->auth->requireAuth();

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'];

        // Mock data for now - replace with actual model calls later
        $stats = [
            'total_students' => $this->getTotalStudents($userId),
            'active_courses' => $this->getActiveCourses(),
            'upcoming_meetings' => $this->getUpcomingMeetings($userId),
            'success_rate' => $this->getSuccessRate($userId)
        ];

        $recentStudents = $this->getRecentStudents($userId, 5);
        $upcomingMeetings = $this->getUpcomingMeetingsList($userId, 5);
        $recentActivities = $this->getRecentActivities($userId);

        // Check if dashboard view exists
        $dashboardView = __DIR__ . '/../views/dashboard.php';
        
        // DEBUG: Check if views directory and file exist
        echo "<!-- DEBUG INFO -->\n";
        echo "<!-- Looking for dashboard view at: " . $dashboardView . " -->\n";
        echo "<!-- Views directory exists: " . (is_dir(__DIR__ . '/../views/') ? 'YES' : 'NO') . " -->\n";
        echo "<!-- Dashboard file exists: " . (file_exists($dashboardView) ? 'YES' : 'NO') . " -->\n";
        
        if (is_dir(__DIR__ . '/../views/')) {
            $viewFiles = scandir(__DIR__ . '/../views/');
            echo "<!-- Views directory contents: " . implode(', ', array_diff($viewFiles, ['.', '..'])) . " -->\n";
        }
        echo "<!-- END DEBUG -->\n";
        
        if (file_exists($dashboardView)) {
            include $dashboardView;
        } else {
            // Show instructions for creating the view file
            echo "<div style='background: #fff3cd; color: #856404; padding: 1rem; margin: 1rem; border: 1px solid #ffeaa7; border-radius: 8px;'>";
            echo "<h3>📁 Dashboard View Missing</h3>";
            echo "<p><strong>Please create:</strong> <code>views/dashboard.php</code></p>";
            echo "<p><strong>Full path:</strong> <code>" . $dashboardView . "</code></p>";
            echo "<p>Once you create this file with your custom dashboard, it will be displayed instead of this simple version.</p>";
            echo "</div>";
            
            // Create a simple dashboard view if it doesn't exist
            $this->renderSimpleDashboard($stats, $userRole);
        }
    }

    // Temporary methods using direct database queries
    private function getTotalStudents($userId) {
        try {
            if ($_SESSION['role'] === 'supervisor') {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM students s 
                    JOIN supervisors sup ON s.supervisor_id = sup.id 
                    WHERE sup.user_id = ?
                ");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM students");
                $stmt->execute();
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total students: " . $e->getMessage());
            return 0;
        }
    }

    private function getActiveCourses() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM courses WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting active courses: " . $e->getMessage());
            return 0;
        }
    }

    private function getUpcomingMeetings($userId) {
        try {
            if ($_SESSION['role'] === 'supervisor') {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM meetings m 
                    JOIN supervisors s ON m.supervisor_id = s.id 
                    WHERE s.user_id = ? AND m.meeting_date >= CURDATE() AND m.status = 'scheduled'
                ");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM meetings 
                    WHERE meeting_date >= CURDATE() AND status = 'scheduled'
                ");
                $stmt->execute();
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting upcoming meetings: " . $e->getMessage());
            return 0;
        }
    }

    private function getSuccessRate($userId) {
        // Mock data for now
        return 85.5;
    }

    private function getRecentStudents($userId, $limit) {
        try {
            // Validate and sanitize limit parameter
            $limit = (int)$limit;
            if ($limit <= 0) $limit = 5;
            
            if ($_SESSION['role'] === 'supervisor') {
                $stmt = $this->db->prepare("
                    SELECT s.*, u.username 
                    FROM students s 
                    JOIN users u ON s.user_id = u.id 
                    JOIN supervisors sup ON s.supervisor_id = sup.id 
                    WHERE sup.user_id = ? 
                    ORDER BY s.created_at DESC 
                    LIMIT $limit
                ");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT s.*, u.username 
                    FROM students s 
                    JOIN users u ON s.user_id = u.id 
                    ORDER BY s.created_at DESC 
                    LIMIT $limit
                ");
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent students: " . $e->getMessage());
            return [];
        }
    }

    private function getUpcomingMeetingsList($userId, $limit) {
        try {
            // Validate and sanitize limit parameter
            $limit = (int)$limit;
            if ($limit <= 0) $limit = 5;
            
            if ($_SESSION['role'] === 'supervisor') {
                $stmt = $this->db->prepare("
                    SELECT m.*, s.full_name as student_name 
                    FROM meetings m 
                    JOIN students s ON m.student_id = s.id 
                    JOIN supervisors sup ON m.supervisor_id = sup.id 
                    WHERE sup.user_id = ? AND m.meeting_date >= CURDATE() AND m.status = 'scheduled' 
                    ORDER BY m.meeting_date, m.meeting_time 
                    LIMIT $limit
                ");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT m.*, s.full_name as student_name 
                    FROM meetings m 
                    JOIN students s ON m.student_id = s.id 
                    WHERE m.meeting_date >= CURDATE() AND m.status = 'scheduled' 
                    ORDER BY m.meeting_date, m.meeting_time 
                    LIMIT $limit
                ");
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting upcoming meetings: " . $e->getMessage());
            return [];
        }
    }

    private function getRecentActivities($userId) {
        // Mock data for now
        return [
            ['action' => 'Student enrolled', 'time' => '2 hours ago'],
            ['action' => 'Meeting scheduled', 'time' => '1 day ago'],
            ['action' => 'Report generated', 'time' => '2 days ago']
        ];
    }

    private function renderSimpleDashboard($stats, $userRole) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashboard - Academic Advising System</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
                .header { background: #333; color: white; padding: 1rem; margin: -20px -20px 20px -20px; }
                .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
                .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .stat-number { font-size: 2em; font-weight: bold; color: #333; }
                .stat-label { color: #666; margin-top: 5px; }
                .logout { float: right; color: white; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Dashboard</h1>
                <a href="<?= BASE_URL ?>/logout" class="logout">Logout</a>
                <div style="clear: both;"></div>
            </div>

            <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (<?= ucfirst($userRole) ?>)</h2>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_students'] ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['active_courses'] ?></div>
                    <div class="stat-label">Active Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['upcoming_meetings'] ?></div>
                    <div class="stat-label">Upcoming Meetings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['success_rate'], 1) ?>%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>

            <p><em>Dashboard is working! You can now create your views/dashboard.php file for a custom design.</em></p>
        </body>
        </html>
        <?php
    }
}
?>