<?php
require_once 'db.php';
require_once 'functions.php';

class Auth {
    
    public static function login($username, $password) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Temporarily comment out login attempts check for testing
        // if (self::checkLoginAttempts($username) >= MAX_LOGIN_ATTEMPTS) {
        //     return ['success' => false, 'message' => 'Too many login attempts. Try again later.'];
        // }
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (!password_verify($password, $user['password'])) {
                // self::logFailedAttempt($username); // Temporarily disabled
                return ['success' => false, 'message' => 'Invalid username or password.'];
            }
            
            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Account is deactivated.'];
            }
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['matric_no'] = $user['matric_no'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // self::clearFailedAttempts($username); // Temporarily disabled
            
            return [
                'success' => true,
                'role' => $user['role'],
                'message' => 'Login successful!'
            ];
        }
        
        // self::logFailedAttempt($username); // Temporarily disabled
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    
    public static function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        return true;
    }
    
    public static function isLoggedIn() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_TIMEOUT)) {
            self::logout();
            return false;
        }
        
        // Update login time on activity
        $_SESSION['login_time'] = time();
        return true;
    }
    
    public static function checkRole($allowedRoles) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if (!isset($_SESSION['role'])) {
            return false;
        }
        
        if (is_array($allowedRoles)) {
            return in_array($_SESSION['role'], $allowedRoles);
        }
        
        return $_SESSION['role'] === $allowedRoles;
    }
    
    public static function requireRole($allowedRoles) {
        if (!self::checkRole($allowedRoles)) {
            header('HTTP/1.0 403 Forbidden');
            die('Access denied. You do not have permission to access this page.');
        }
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
    }
    
    private static function checkLoginAttempts($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Check if table exists
        $table_exists = $conn->query("SHOW TABLES LIKE 'login_attempts'");
        if (!$table_exists || $table_exists->num_rows == 0) {
            return 0; // Table doesn't exist, return 0 attempts
        }
        
        $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        if (!$stmt) {
            return 0; // If prepare fails, return 0
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['attempts'] ?? 0;
        }
        
        return 0;
    }
    
    private static function logFailedAttempt($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Check if table exists
        $table_exists = $conn->query("SHOW TABLES LIKE 'login_attempts'");
        if (!$table_exists || $table_exists->num_rows == 0) {
            return; // Table doesn't exist, skip logging
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $username, $ip);
            $stmt->execute();
        }
    }
    
    private static function clearFailedAttempts($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Check if table exists
        $table_exists = $conn->query("SHOW TABLES LIKE 'login_attempts'");
        if (!$table_exists || $table_exists->num_rows == 0) {
            return; // Table doesn't exist, skip clearing
        }
        
        $stmt = $conn->prepare("DELETE FROM login_attempts WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
        }
    }
    
    public static function createUser($data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Validate required fields
        $required = ['username', 'email', 'password', 'full_name', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }
        
        // Check if username/email exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$checkStmt) {
            return ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
        
        $checkStmt->bind_param("ss", $data['username'], $data['email']);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Prepare SQL
        $sql = "INSERT INTO users (username, email, password, full_name, role";
        $params = "sssss";
        $values = [$data['username'], $data['email'], $hashedPassword, $data['full_name'], $data['role']];
        
        // Add optional fields
        if (!empty($data['matric_no'])) {
            $sql .= ", matric_no";
            $params .= "s";
            $values[] = $data['matric_no'];
        }
        
        if (!empty($data['department'])) {
            $sql .= ", department";
            $params .= "s";
            $values[] = $data['department'];
        }
        
        $sql .= ") VALUES (" . str_repeat("?,", count($values) - 1) . "?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error];
        }
        
        $stmt->bind_param($params, ...$values);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User created successfully', 'user_id' => $conn->insert_id];
        }
        
        return ['success' => false, 'message' => 'Failed to create user: ' . $conn->error];
    }
}
?>