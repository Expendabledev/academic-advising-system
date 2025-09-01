<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// Get requested page from URL rewrite or GET parameter
$page = $_GET['page'] ?? $_GET['url'] ?? 'dashboard';

// Handle auth routes that bypass authentication check
if ($page === 'login' || strpos($_SERVER['REQUEST_URI'], '/login') !== false) {
    include __DIR__ . '/views/auth/login.php';
    exit();
}

if ($page === 'logout' || strpos($_SERVER['REQUEST_URI'], '/logout') !== false) {
    include __DIR__ . '/includes/logout.php';
    exit();
}

// Redirect to login if not authenticated
if (!$auth->isAuthenticated()) {
    header('Location: ' . BASE_URL . '/views/auth/login.php');
    exit();
}

// Route to appropriate controller
try {
    switch ($page) {
        case 'dashboard':
            require_once __DIR__ . '/controllers/dashboardcontroller.php';
            $controller = new DashboardController();
            $controller->index();
            break;
           
        case 'students':
            require_once __DIR__ . '/controllers/studentscontroller.php';
            $controller = new StudentsController();
            $controller->index();
            break;
           
        case 'courses':
            require_once __DIR__ . '/controllers/coursescontroller.php';
            $controller = new CoursesController();
            $controller->index();
            break;
           
        case 'meetings':
            require_once __DIR__ . '/controllers/meetingscontroller.php';
            $controller = new MeetingsController();
            $controller->index();
            break;
           
        case 'reports':
            require_once __DIR__ . '/controllers/reportscontroller.php';
            $controller = new ReportsController();
            $controller->index();
            break;
           
        default:
            http_response_code(404);
            if (file_exists(__DIR__ . '/views/errors/404.php')) {
                include __DIR__ . '/views/errors/404.php';
            } else {
                echo "404 - Page not found";
            }
            exit();
    }
} catch (Exception $e) {
    // Log the error
    error_log("Error in index.php: " . $e->getMessage());
    
    // Set error message for user
    $_SESSION['error'] = 'An error occurred while loading the page: ' . $e->getMessage();
    
    // Show error page
    if (file_exists(__DIR__ . '/views/errors/500.php')) {
        include __DIR__ . '/views/errors/500.php';
    } else {
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>