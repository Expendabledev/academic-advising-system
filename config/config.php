<?php
// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Africa/Lagos');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

// Application constants
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('BASE_URL', 'http://localhost/swepgroup17');

// Environment
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');
define('IS_PROD', ENVIRONMENT === 'production');
?>