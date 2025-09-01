<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

class Auth {
    private $db;
    private $maxInactiveSessionTime = 1800; // 30 minutes in seconds
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_secure' => IS_PROD, // Only send cookies over HTTPS in production
                'cookie_httponly' => true, // Prevent JavaScript access to session cookie
                'cookie_samesite' => 'Strict' // Prevent CSRF attacks
            ]);
        }
        
        $this->db = (new Database())->connect();
    }

    public function login($username, $password) {
        // Validate input
        if (empty($username) || empty($password)) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            return true;
        }
        
        // Add delay to prevent timing attacks (even if small)
        usleep(random_int(100000, 300000));
        return false;
    }

    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }

    public function isAuthenticated() {
        if (!isset($_SESSION['user_id'], 
                  $_SESSION['last_activity'], 
                  $_SESSION['ip_address'], 
                  $_SESSION['user_agent'])) {
            return false;
        }

        // Check for session timeout
        if ((time() - $_SESSION['last_activity']) > $this->maxInactiveSessionTime) {
            $this->logout();
            return false;
        }

        // Check if IP or user agent changed (possible session hijacking)
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] || 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logout();
            return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        return true;
    }

    public function isSupervisor() {
        return $this->isAuthenticated() && $_SESSION['role'] === 'supervisor';
    }

    public function logout() {
        // Clear session data
        $_SESSION = [];

        // Invalidate session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"], 
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();
    }

    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    public function requireRole($requiredRole) {
        $this->requireAuth();
        
        if ($_SESSION['role'] !== $requiredRole) {
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied');
        }
    }
}