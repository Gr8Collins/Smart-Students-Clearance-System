<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has registry/admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'registry')) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: registry_dashboard.php");
    exit();
}

$request_id = intval($_GET['id']);

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// UPDATED QUERY - Includes all necessary fields
$query = "
    SELECT cr.*, 
           u.full_name as student_name, 
           u.email, 
           u.phone,
           u.department as student_department,
           u.faculty as student_faculty
    FROM clearance_requests cr
    JOIN users u ON cr.matric_no = u.matric_no
    WHERE cr.id = ?
";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $request_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Clearance request not found!");
}

$request = $result->fetch_assoc();

// Try to get unit info if available
$unit_info = [];
$unit_query = "SELECT unit_name, unit_code FROM clearance_units WHERE id = ?";
if ($stmt2 = $conn->prepare($unit_query)) {
    $stmt2->bind_param("i", $request['unit_id']);
    $stmt2->execute();
    $unit_result = $stmt2->get_result();
    if ($unit_result->num_rows > 0) {
        $unit_info = $unit_result->fetch_assoc();
    }
}

// Try to get approver info if available
$approver_name = null;
if (!empty($request['approved_by'])) {
    $approver_query = "SELECT full_name FROM users WHERE id = ?";
    if ($stmt3 = $conn->prepare($approver_query)) {
        $stmt3->bind_param("i", $request['approved_by']);
        $stmt3->execute();
        $approver_result = $stmt3->get_result();
        if ($approver_result->num_rows > 0) {
            $approver = $approver_result->fetch_assoc();
            $approver_name = $approver['full_name'];
        }
    }
}

// Try to get clearance history
$history = [];
if ($stmt4 = $conn->prepare("SELECT * FROM clearance_history WHERE request_id = ? ORDER BY created_at DESC")) {
    $stmt4->bind_param("i", $request_id);
    $stmt4->execute();
    $history_result = $stmt4->get_result();
    while ($row = $history_result->fetch_assoc()) {
        $history[] = $row;
    }
}

// Try to get academic records if available
$academic_records = [];
if ($stmt5 = $conn->prepare("SELECT * FROM academic_records WHERE matric_no = ? ORDER BY academic_year DESC, semester DESC")) {
    $stmt5->bind_param("s", $request['matric_no']);
    $stmt5->execute();
    $academic_result = $stmt5->get_result();
    while ($row = $academic_result->fetch_assoc()) {
        $academic_records[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Details - Smart Clearance System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7e22ce 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .content {
            padding: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #4f46e5;
        }

        .info-card h3 {
            color: #4a5568;
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #1e293b;
            font-weight: 500;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #d1fae5; color: #059669; }
        .status-rejected { background: #fee2e2; color: #dc2626; }

        .section-title {
            color: #1e293b;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .history-table th {
            background: #f1f5f9;
            padding: 12px;
            text-align: left;
            color: #475569;
            font-weight: 600;
        }

        .history-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .history-table tr:hover {
            background: #f8fafc;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-print {
            background: #3b82f6;
            color: white;
        }

        .btn-print:hover {
            background: #2563eb;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .comments-box {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #3b82f6;
        }

        .comments-box h4 {
            color: #475569;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .back-btn, .action-buttons {
                display: none;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-file-alt"></i>
                Clearance Request Details
            </h1>
            <a href="registry_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="content">
            <!-- Request Information -->
            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-user-graduate"></i> Student Information</h3>
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['student_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Matric Number:</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['matric_no']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Department:</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['student_department']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Faculty:</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['student_faculty']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['phone'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-clipboard-check"></i> Clearance Details</h3>
                    <div class="info-row">
                        <span class="info-label">Request Code:</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['request_code']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Clearance Unit:</span>
                        <span class="info-value">
                            <?php 
                            if (!empty($unit_info)) {
                                echo htmlspecialchars($unit_info['unit_name']) . ' (' . htmlspecialchars($unit_info['unit_code']) . ')';
                            } else {
                                echo htmlspecialchars($request['unit_name']) . ' (' . htmlspecialchars($request['unit_code'] ?? 'N/A') . ')';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $request['status']; ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Request Date:</span>
                        <span class="info-value"><?php echo date('F d, Y H:i:s', strtotime($request['requested_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value"><?php echo date('F d, Y H:i:s', strtotime($request['updated_at'])); ?></span>
                    </div>
                </div>

                <?php if ($request['status'] != 'pending'): ?>
                <div class="info-card">
                    <h3><i class="fas fa-user-check"></i> Approval Details</h3>
                    <div class="info-row">
                        <span class="info-label">Approved/Rejected By:</span>
                        <span class="info-value"><?php echo htmlspecialchars($approver_name ?? $request['approver_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Approval Date:</span>
                        <span class="info-value"><?php echo !empty($request['approved_at']) ? date('F d, Y H:i:s', strtotime($request['approved_at'])) : 'N/A'; ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Comments Section -->
            <?php if (!empty($request['comments'])): ?>
            <div class="comments-box">
                <h4><i class="fas fa-comment"></i> Officer's Comments</h4>
                <p><?php echo nl2br(htmlspecialchars($request['comments'])); ?></p>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Details
                </button>
                <a href="registry_dashboard.php" class="btn" style="background: #64748b; color: white;">
                    <i class="fas fa-arrow-left"></i> Return to Dashboard
                </a>
            </div>

            <!-- Clearance History -->
            <?php if (!empty($history)): ?>
            <div style="margin-top: 40px;">
                <h3 class="section-title"><i class="fas fa-history"></i> Clearance History</h3>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th>Role</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $history_item): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($history_item['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $history_item['action']; ?>">
                                        <?php echo ucfirst($history_item['action']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($history_item['performed_by_name'] ?? $history_item['performed_by']); ?></td>
                                <td><?php echo ucfirst($history_item['performed_role']); ?></td>
                                <td><?php echo htmlspecialchars($history_item['notes']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Academic Records -->
            <?php if (!empty($academic_records)): ?>
            <div style="margin-top: 40px;">
                <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Academic Records</h3>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>CGPA</th>
                            <th>Courses Passed</th>
                            <th>Courses Failed</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($academic_records as $academic): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($academic['academic_year']); ?></td>
                                <td><?php echo htmlspecialchars($academic['semester']); ?></td>
                                <td><?php echo htmlspecialchars($academic['cgpa'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($academic['courses_passed'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($academic['courses_failed'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($academic['remarks'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="margin-top: 40px;">
                <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Academic Records</h3>
                <p style="color: #64748b; text-align: center; padding: 20px;">
                    <i class="fas fa-info-circle"></i> No academic records available for this student.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Print functionality
        function printClearance() {
            window.print();
        }
    </script>
</body>
</html>