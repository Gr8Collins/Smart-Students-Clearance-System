<?php
// create_database_only.php

// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'clearance_system';

echo "Creating database '$database'...<br>";

// Connect to MySQL server
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL server successfully<br>";

// Create database SQL
$sql = "CREATE DATABASE IF NOT EXISTS `$database` 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_unicode_ci";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "Database '$database' created successfully!";
} else {
    echo "Error creating database: " . $conn->error;
}

// Close connection
$conn->close();
?>