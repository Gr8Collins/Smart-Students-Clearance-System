<?php
// create_tables.php

// Database configuration
$host = 'localhost';
$username = 'root';
$password = ''; // Your MySQL password (default is empty for XAMPP/WAMP)
$database = 'clearance_system';

echo "<h2>Smart Clearance System - Database Setup</h2>";

// Connect to MySQL server
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("<span style='color: red;'>✗ Connection failed: " . $conn->connect_error . "</span>");
}

echo "<span style='color: green;'>✓ Connected to MySQL server successfully</span><br><br>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<span style='color: green;'>✓ Database '$database' created or already exists</span><br><br>";
} else {
    die("<span style='color: red;'>✗ Error creating database: " . $conn->error . "</span>");
}

// Select the database
if (!$conn->select_db($database)) {
    die("<span style='color: red;'>✗ Error selecting database: '$database'</span><br>");
}

echo "<span style='color: green;'>✓ Selected database: '$database'</span><br><br>";

// ===================== CREATE TABLES =====================

$tables = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('student','library','bursary','department','faculty','admin','registry') NOT NULL,
        matric_no VARCHAR(30) UNIQUE,
        phone VARCHAR(20),
        department VARCHAR(100),
        faculty VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        INDEX idx_role (role),
        INDEX idx_matric (matric_no)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Academic records table
    "CREATE TABLE IF NOT EXISTS academic_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matric_no VARCHAR(30) NOT NULL,
        academic_year VARCHAR(20) NOT NULL,
        semester VARCHAR(20) NOT NULL,
        cgpa DECIMAL(3,2),
        courses_passed INT,
        courses_failed INT,
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_matric (matric_no),
        FOREIGN KEY (matric_no) REFERENCES users(matric_no) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Registry table for final certificate processing
    "CREATE TABLE IF NOT EXISTS registry (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matric_no VARCHAR(30) NOT NULL,
        student_name VARCHAR(100) NOT NULL,
        department VARCHAR(100) NOT NULL,
        faculty VARCHAR(100) NOT NULL,
        year_of_graduation YEAR NOT NULL,
        certificate_type ENUM('bachelor','master','phd','diploma','certificate') DEFAULT 'bachelor',
        clearance_status ENUM('pending','processing','approved','rejected','completed') DEFAULT 'pending',
        all_units_cleared BOOLEAN DEFAULT FALSE,
        certificate_issued BOOLEAN DEFAULT FALSE,
        certificate_number VARCHAR(50) UNIQUE,
        certificate_issue_date DATE,
        registry_officer_id INT,
        registry_officer_name VARCHAR(100),
        comments TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_matric (matric_no),
        INDEX idx_clearance_status (clearance_status),
        INDEX idx_certificate_number (certificate_number),
        FOREIGN KEY (matric_no) REFERENCES users(matric_no) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Clearance units table
    "CREATE TABLE IF NOT EXISTS clearance_units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        unit_code VARCHAR(20) UNIQUE NOT NULL,
        unit_name VARCHAR(100) NOT NULL,
        description TEXT,
        approval_role VARCHAR(50) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        requires_registry_approval BOOLEAN DEFAULT FALSE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Clearance requests table
    "CREATE TABLE IF NOT EXISTS clearance_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_code VARCHAR(20) UNIQUE NOT NULL,
        matric_no VARCHAR(30) NOT NULL,
        student_name VARCHAR(100) NOT NULL,
        student_department VARCHAR(100),
        student_faculty VARCHAR(100),
        unit_id INT NOT NULL,
        unit_code VARCHAR(20),
        unit_name VARCHAR(100) NOT NULL,
        status ENUM('pending','approved','rejected','on_hold','referred_to_registry') DEFAULT 'pending',
        comments TEXT,
        approved_by INT NULL,
        approver_name VARCHAR(100),
        approved_at TIMESTAMP NULL,
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        registry_reviewed BOOLEAN DEFAULT FALSE,
        registry_comments TEXT,
        INDEX idx_matric_status (matric_no, status),
        INDEX idx_status (status),
        INDEX idx_unit (unit_id),
        FOREIGN KEY (matric_no) REFERENCES users(matric_no) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES clearance_units(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Login attempts table
    "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        success BOOLEAN DEFAULT FALSE,
        INDEX idx_username_time (username, attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Clearance history table
    "CREATE TABLE IF NOT EXISTS clearance_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        action ENUM('requested','approved','rejected','commented','updated','referred_to_registry','registry_approved','certificate_issued') NOT NULL,
        performed_by INT NOT NULL,
        performed_by_name VARCHAR(100),
        performed_role VARCHAR(50) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_request (request_id),
        FOREIGN KEY (request_id) REFERENCES clearance_requests(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Notifications table
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info','success','warning','error','registry') DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_read (user_id, is_read),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Certificates table
    "CREATE TABLE IF NOT EXISTS certificates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        certificate_number VARCHAR(50) UNIQUE NOT NULL,
        matric_no VARCHAR(30) NOT NULL,
        student_name VARCHAR(100) NOT NULL,
        certificate_type VARCHAR(50) NOT NULL,
        issue_date DATE NOT NULL,
        expiry_date DATE,
        issued_by INT NOT NULL,
        issued_by_name VARCHAR(100) NOT NULL,
        download_count INT DEFAULT 0,
        file_path VARCHAR(255),
        status ENUM('active','revoked','expired') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_matric (matric_no),
        INDEX idx_cert_number (certificate_number),
        FOREIGN KEY (matric_no) REFERENCES users(matric_no) ON DELETE CASCADE,
        FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

echo "<h3>Creating Tables...</h3>";

foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<span style='color: green;'>✓ Table created successfully</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Error creating table: " . $conn->error . "</span><br>";
    }
}

echo "<br><hr>";

// ===================== INSERT DEFAULT DATA =====================

echo "<h3>Inserting Default Data...</h3>";

// First, check if clearance_units is empty
$result = $conn->query("SELECT COUNT(*) as count FROM clearance_units");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert clearance units with registry requirement
    $units = [
        "('LIB', 'Library Department', 'library', 'Check for borrowed books and outstanding fines', 0)",
        "('BUR', 'Bursary Department', 'bursary', 'Check for outstanding school fees and payments', 0)",
        "('DEP', 'Departmental Head', 'department', 'Academic and departmental clearance', 0)",
        "('FAC', 'Faculty Officer', 'faculty', 'Faculty-level clearance and approval', 0)",
        "('HOD', 'Head of Department', 'department', 'Final departmental approval', 0)",
        "('REG', 'Registry Department', 'registry', 'Final registration and certificate processing', 1)",
        "('EXM', 'Examination Unit', 'admin', 'Examination clearance and results verification', 0)",
        "('HOS', 'Hostel Affairs', 'admin', 'Hostel dues and property clearance', 0)",
        "('MED', 'Medical Center', 'admin', 'Medical and health clearance', 0)"
    ];
    
    $sql = "INSERT INTO clearance_units (unit_code, unit_name, approval_role, description, requires_registry_approval) VALUES " . implode(', ', $units);
    
    if ($conn->query($sql) === TRUE) {
        echo "<span style='color: green;'>✓ 9 clearance units inserted</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Error inserting units: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span style='color: blue;'>⚠ Clearance units already exist, skipping...</span><br>";
}

echo "<hr>";

// Check if users table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // ===================== USER PASSWORDS =====================
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h4 style='color: #d35400;'>🔐 DEFAULT PASSWORDS (Please change after login):</h4>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: 'admin', password: 'Admin@123'</li>";
    echo "<li><strong>Registry Staff:</strong> username: 'registry.staff', password: 'Registry@123'</li>";
    echo "<li><strong>Library Staff:</strong> username: 'lib.staff', password: 'Library@123'</li>";
    echo "<li><strong>Bursary Staff:</strong> username: 'bursary.staff', password: 'Bursary@123'</li>";
    echo "<li><strong>Department Staff:</strong> username: 'dept.staff', password: 'Department@123'</li>";
    echo "<li><strong>Faculty Staff:</strong> username: 'faculty.staff', password: 'Faculty@123'</li>";
    echo "<li><strong>Student 1:</strong> username: 'student1', password: 'Student@123'</li>";
    echo "<li><strong>Student 2:</strong> username: 'student2', password: 'Student@123'</li>";
    echo "</ul>";
    echo "</div>";
    
    // Hash passwords
    $admin_pass = password_hash('Admin@123', PASSWORD_DEFAULT);
    $registry_pass = password_hash('Registry@123', PASSWORD_DEFAULT);
    $lib_pass = password_hash('Library@123', PASSWORD_DEFAULT);
    $bursary_pass = password_hash('Bursary@123', PASSWORD_DEFAULT);
    $dept_pass = password_hash('Department@123', PASSWORD_DEFAULT);
    $faculty_pass = password_hash('Faculty@123', PASSWORD_DEFAULT);
    $student_pass = password_hash('Student@123', PASSWORD_DEFAULT);
    
    // Insert users
    $users_sql = "INSERT INTO users (username, email, password, full_name, role, phone, is_active) VALUES
        ('admin', 'admin@university.edu', '$admin_pass', 'System Administrator', 'admin', '+1234567890', 1),
        ('registry.staff', 'registry@university.edu', '$registry_pass', 'Registry Officer', 'registry', '+1234567891', 1),
        ('lib.staff', 'library@university.edu', '$lib_pass', 'Library Staff', 'library', '+1234567892', 1),
        ('bursary.staff', 'bursary@university.edu', '$bursary_pass', 'Bursary Staff', 'bursary', '+1234567893', 1),
        ('dept.staff', 'department@university.edu', '$dept_pass', 'Department Officer', 'department', '+1234567894', 1),
        ('faculty.staff', 'faculty@university.edu', '$faculty_pass', 'Faculty Officer', 'faculty', '+1234567895', 1),
        ('student1', 'student1@university.edu', '$student_pass', 'John Doe', 'student', '+1234567896', 1),
        ('student2', 'student2@university.edu', '$student_pass', 'Jane Smith', 'student', '+1234567897', 1)";
    
    if ($conn->query($users_sql) === TRUE) {
        echo "<span style='color: green;'>✓ 8 users inserted successfully</span><br>";
        
        // Update users with details
        $update_sql = "UPDATE users SET 
            matric_no = CASE 
                WHEN username = 'student1' THEN 'CS/2019/001'
                WHEN username = 'student2' THEN 'CS/2019/002'
                ELSE matric_no 
            END,
            department = CASE 
                WHEN username = 'student1' THEN 'Computer Science'
                WHEN username = 'student2' THEN 'Computer Science'
                WHEN username = 'lib.staff' THEN 'Library'
                WHEN username = 'bursary.staff' THEN 'Bursary'
                WHEN username = 'dept.staff' THEN 'Computer Science'
                WHEN username = 'faculty.staff' THEN 'Faculty of Science'
                WHEN username = 'registry.staff' THEN 'Registry Department'
                WHEN username = 'admin' THEN 'Administration'
                ELSE department 
            END,
            faculty = CASE
                WHEN username = 'student1' THEN 'Faculty of Science'
                WHEN username = 'student2' THEN 'Faculty of Science'
                WHEN username = 'faculty.staff' THEN 'Faculty of Science'
                ELSE 'General'
            END";
        
        if ($conn->query($update_sql) === TRUE) {
            echo "<span style='color: green;'>✓ User details updated</span><br>";
            
            // Insert sample academic records
            $academic_sql = "INSERT INTO academic_records (matric_no, academic_year, semester, cgpa, courses_passed, courses_failed, remarks) VALUES
                ('CS/2019/001', '2023/2024', 'First', 3.75, 8, 0, 'Excellent performance'),
                ('CS/2019/001', '2023/2024', 'Second', 3.82, 7, 1, 'Good performance, one course to retake'),
                ('CS/2019/002', '2023/2024', 'First', 3.45, 7, 1, 'Good performance'),
                ('CS/2019/002', '2023/2024', 'Second', 3.68, 8, 0, 'Improved performance')";
            
            if ($conn->query($academic_sql) === TRUE) {
                echo "<span style='color: green;'>✓ Sample academic records inserted</span><br>";
            }
            
            // Insert sample registry records
            $registry_sql = "INSERT INTO registry (matric_no, student_name, department, faculty, year_of_graduation, certificate_type, clearance_status) VALUES
                ('CS/2019/001', 'John Doe', 'Computer Science', 'Faculty of Science', 2024, 'bachelor', 'pending'),
                ('CS/2019/002', 'Jane Smith', 'Computer Science', 'Faculty of Science', 2024, 'bachelor', 'pending')";
            
            if ($conn->query($registry_sql) === TRUE) {
                echo "<span style='color: green;'>✓ Sample registry records inserted</span><br>";
            }
            
            // Insert sample clearance requests
            $clearance_sql = "INSERT INTO clearance_requests (request_code, matric_no, student_name, student_department, student_faculty, unit_id, unit_code, unit_name, status) VALUES
                ('CLR-001', 'CS/2019/001', 'Micheal John', 'Computer Science', 'Faculty of Science', 1, 'LIB', 'Library Department', 'approved'),
                ('CLR-002', 'CS/2019/002', 'Lucky Dick', 'Computer Science', 'Faculty of Science', 2, 'BUR', 'Bursary Department', 'pending'),
                ('CLR-003', 'CS/2019/003', 'Jane Smith', 'Computer Science', 'Faculty of Science', 1, 'LIB', 'Library Department', 'approved')";
            
            if ($conn->query($clearance_sql) === TRUE) {
                echo "<span style='color: green;'>✓ Sample clearance requests inserted</span><br>";
            }
        }
        
    } else {
        echo "<span style='color: red;'>✗ Error inserting users: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span style='color: blue;'>⚠ Users already exist, skipping...</span><br>";
}

echo "<hr>";

// ===================== CREATE TRIGGERS =====================

echo "<h3>Creating Triggers and Procedures...</h3>";

// Create trigger to update registry when all units are cleared
$trigger_sql = "
CREATE TRIGGER IF NOT EXISTS update_registry_status 
AFTER UPDATE ON clearance_requests
FOR EACH ROW
BEGIN
    DECLARE total_units INT;
    DECLARE cleared_units INT;
    DECLARE student_matric VARCHAR(30);
    
    -- Get student matric number
    SET student_matric = NEW.matric_no;
    
    -- Count total active units
    SELECT COUNT(*) INTO total_units 
    FROM clearance_units 
    WHERE is_active = TRUE;
    
    -- Count approved clearance units for this student
    SELECT COUNT(*) INTO cleared_units 
    FROM clearance_requests cr
    JOIN clearance_units cu ON cr.unit_id = cu.id
    WHERE cr.matric_no = student_matric 
    AND cr.status = 'approved'
    AND cu.is_active = TRUE;
    
    -- Update registry if all units are cleared
    IF cleared_units >= total_units THEN
        UPDATE registry 
        SET all_units_cleared = TRUE,
            clearance_status = 'processing',
            updated_at = CURRENT_TIMESTAMP
        WHERE matric_no = student_matric;
    END IF;
    
    -- If registry approval is required and unit is approved, mark as referred to registry
    IF NEW.status = 'approved' THEN
        SELECT requires_registry_approval INTO @requires_registry 
        FROM clearance_units 
        WHERE id = NEW.unit_id;
        
        IF @requires_registry = 1 THEN
            UPDATE clearance_requests 
            SET status = 'referred_to_registry'
            WHERE id = NEW.id;
        END IF;
    END IF;
END";

if ($conn->multi_query($trigger_sql)) {
    echo "<span style='color: green;'>✓ Trigger created successfully</span><br>";
    // Clear multi_query results
    while ($conn->more_results() && $conn->next_result()) {;}
} else {
    echo "<span style='color: orange;'>⚠ Could not create trigger (might already exist): " . $conn->error . "</span><br>";
}

echo "<hr>";

// ===================== VERIFICATION =====================

echo "<h3>Verification...</h3>";

$tables_to_check = ['users', 'academic_records', 'registry', 'clearance_units', 'clearance_requests', 'login_attempts', 'clearance_history', 'notifications', 'certificates'];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        echo "<span style='color: green;'>✓ Table '$table' exists with $count records</span><br>";
    } else {
        echo "<span style='color: orange;'>⚠ Table '$table' not found</span><br>";
    }
}

echo "<hr>";

// ===================== COMPLETION =====================

echo "<h2 style='color: green;'>✅ SETUP COMPLETED!</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border-left: 5px solid #2ecc71;'>";
echo "<h3>🎉 Database is Ready!</h3>";
echo "<p>The system now includes full registry functionality for:</p>";
echo "<ul>";
echo "<li><strong>Final Certificate Processing</strong></li>";
echo "<li><strong>Registry Approval Workflow</strong></li>";
echo "<li><strong>Certificate Generation & Tracking</strong></li>";
echo "<li><strong>Complete Clearance History</strong></li>";
echo "<li><strong>Academic Records Management</strong></li>";
echo "</ul>";
echo "<p>You can now:</p>";
echo "<ol>";
echo "<li><strong>Access the system:</strong> <a href='index.php' target='_blank'>Go to Homepage</a></li>";
echo "<li><strong>Login as Registry Staff:</strong> Username: 'registry.staff', Password: 'Registry@123'</li>";
echo "<li><strong>Login as Admin:</strong> Username: 'admin', Password: 'Admin@123'</li>";
echo "<li><strong>Login as Student:</strong> Username: 'student1', Password: 'Student@123'</li>";
echo "</ol>";
echo "<p><strong>Important Security Note:</strong></p>";
echo "<ul>";
echo "<li>Change default passwords immediately after first login</li>";
echo "<li>Delete this setup file: <code>create_tables.php</code></li>";
echo "<li>For production, set up proper MySQL user permissions</li>";
echo "</ul>";
echo "</div>";

// Close connection
$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        line-height: 1.6;
        background: #f5f5f5;
    }
    h2, h3, h4 {
        color: #2c3e50;
    }
    hr {
        border: 1px solid #ddd;
        margin: 20px 0;
    }
    code {
        background: #2c3e50;
        color: white;
        padding: 2px 5px;
        border-radius: 3px;
    }
</style>