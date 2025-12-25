<?php
// fix_login_attempts.php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'clearance_system';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username_time (username, attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "✓ 'login_attempts' table created successfully!<br>";
    echo "You can now login properly.<br>";
    echo "<a href='login.php'>Go to Login</a>";
} else {
    echo "✗ Error creating table: " . $conn->error;
}

$conn->close();
?>