<?php
// config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'clearance_system');

// Security settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('ENABLE_LOGIN_LIMIT', false); // Set to true for production

// Site settings
define('SITE_NAME', 'Smart Clearance System');
define('SITE_URL', 'http://localhost/clearance_system');

// Set timezone
date_default_timezone_set('Africa/Lagos');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>