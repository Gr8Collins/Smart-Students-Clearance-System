<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has registry/admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'registry')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_clearance'])) {
        $request_id = $_POST['request_id'];
        $comments = $_POST['comments'] ?? '';
        
        $stmt = $conn->prepare("UPDATE clearance_requests SET status = 'approved', 
                               approved_by = ?, approved_at = NOW(), comments = ?
                               WHERE id = ?");
        $stmt->bind_param("isi", $user_id, $comments, $request_id);
        
        if ($stmt->execute()) {
            // Add to history
            $history_stmt = $conn->prepare("INSERT INTO clearance_history 
                                          (request_id, action, performed_by, performed_role, notes)
                                          VALUES (?, 'approved', ?, ?, ?)");
            $notes = "Registry clearance approved by $full_name. Comments: $comments";
            $history_stmt->bind_param("iiss", $request_id, $user_id, $user_role, $notes);
            $history_stmt->execute();
            
            // Create notification for student
            $request_info = $conn->query("SELECT matric_no FROM clearance_requests WHERE id = $request_id")->fetch_assoc();
            $student = $conn->query("SELECT id FROM users WHERE matric_no = '{$request_info['matric_no']}'")->fetch_assoc();
            
            if ($student) {
                $conn->query("INSERT INTO notifications (user_id, title, message, type) VALUES 
                            ({$student['id']}, 'Registry Clearance Approved', 
                            'Your registry clearance has been approved. You can now proceed with final registration.', 'success')");
            }
            
            $_SESSION['success'] = "Clearance approved successfully!";
        }
    }
    
    if (isset($_POST['reject_clearance'])) {
        $request_id = $_POST['request_id'];
        $comments = $_POST['comments'] ?? '';
        
        $stmt = $conn->prepare("UPDATE clearance_requests SET status = 'rejected', 
                               approved_by = ?, approved_at = NOW(), comments = ?
                               WHERE id = ?");
        $stmt->bind_param("isi", $user_id, $comments, $request_id);
        
        if ($stmt->execute()) {
            $history_stmt = $conn->prepare("INSERT INTO clearance_history 
                                          (request_id, action, performed_by, performed_role, notes)
                                          VALUES (?, 'rejected', ?, ?, ?)");
            $notes = "Registry clearance rejected by $full_name. Reason: $comments";
            $history_stmt->bind_param("iiss", $request_id, $user_id, $user_role, $notes);
            $history_stmt->execute();
            
            $_SESSION['error'] = "Clearance rejected!";
        }
    }
    
    if (isset($_POST['generate_report'])) {
        // Handle report generation
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = $_POST['status'];
        
        // Redirect to report page with parameters
        header("Location: registry_reports.php?start_date=$start_date&end_date=$end_date&status=$status");
        exit();
    }
}

// Get statistics
$total_requests = $conn->query("SELECT COUNT(*) as total FROM clearance_requests WHERE unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG')")->fetch_assoc()['total'];
$pending_requests = $conn->query("SELECT COUNT(*) as pending FROM clearance_requests WHERE unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG') AND status = 'pending'")->fetch_assoc()['pending'];
$approved_requests = $conn->query("SELECT COUNT(*) as approved FROM clearance_requests WHERE unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG') AND status = 'approved'")->fetch_assoc()['approved'];
$rejected_requests = $conn->query("SELECT COUNT(*) as rejected FROM clearance_requests WHERE unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG') AND status = 'rejected'")->fetch_assoc()['rejected'];

// Get pending clearance requests for registry
$pending_clearances = $conn->query("
    SELECT cr.*, u.full_name as student_name, u.department as student_department 
    FROM clearance_requests cr
    JOIN users u ON cr.matric_no = u.matric_no
    WHERE cr.unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG')
    AND cr.status = 'pending'
    ORDER BY cr.requested_at DESC
");

// Get recent clearance history
$recent_clearances = $conn->query("
    SELECT cr.*, u.full_name as student_name
    FROM clearance_requests cr
    JOIN users u ON cr.matric_no = u.matric_no
    WHERE cr.unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG')
    ORDER BY cr.updated_at DESC
    LIMIT 10
");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registry Dashboard - Smart Clearance System</title>
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

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7e22ce 100%);
            color: white;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 i {
            font-size: 32px;
        }

        .user-info {
            text-align: right;
        }

        .user-info .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 5px;
        }

        .main-content {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 800px;
        }

        .sidebar {
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            padding: 30px 0;
        }

        .nav-item {
            padding: 15px 30px;
            color: #4a5568;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: #edf2f7;
            color: #4f46e5;
            border-left-color: #4f46e5;
        }

        .nav-item i {
            font-size: 20px;
        }

        .content {
            padding: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #1e293b;
        }

        .stat-card.pending { border-left: 5px solid #f59e0b; }
        .stat-card.approved { border-left: 5px solid #10b981; }
        .stat-card.rejected { border-left: 5px solid #ef4444; }
        .stat-card.total { border-left: 5px solid #4f46e5; }

        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .section-title h2 {
            color: #1e293b;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #d1fae5; color: #059669; }
        .status-rejected { background: #fee2e2; color: #dc2626; }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }

        .btn-approve {
            background: #10b981;
            color: white;
        }

        .btn-reject {
            background: #ef4444;
            color: white;
        }

        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .btn-approve:hover { background: #059669; }
        .btn-reject:hover { background: #dc2626; }
        .btn-view:hover { background: #2563eb; }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            color: #1e293b;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #64748b;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .report-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .user-info {
                text-align: center;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>
                <i class="fas fa-university"></i>
                Registry Dashboard
            </h1>
            <div class="user-info">
                <div class="welcome">Welcome, <?php echo htmlspecialchars($full_name); ?></div>
                <div class="role-badge">Registry Officer</div>
                <a href="logout.php" style="color: white; margin-top: 10px; display: inline-block;">Logout</a>
            </div>
        </div>

        <div class="main-content">
            <div class="sidebar">
                <a href="#" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="#" class="nav-item" onclick="showPendingClearances()">
                    <i class="fas fa-clock"></i>
                    Pending Clearances
                    <span class="pending-count"><?php echo $pending_requests; ?></span>
                </a>
                <a href="#" class="nav-item" onclick="showReports()">
                    <i class="fas fa-chart-bar"></i>
                    Reports & Analytics
                </a>
                <a href="#" class="nav-item" onclick="showFinalApprovals()">
                    <i class="fas fa-graduation-cap"></i>
                    Final Approvals
                </a>
                <a href="#" class="nav-item" onclick="showStudentSearch()">
                    <i class="fas fa-search"></i>
                    Student Search
                </a>
                <a href="#" class="nav-item" onclick="showSettings()">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </div>

            <div class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics Section -->
                <div class="stats-grid">
                    <div class="stat-card total">
                        <h3>Total Requests</h3>
                        <div class="stat-value"><?php echo $total_requests; ?></div>
                        <p>All time registry clearance requests</p>
                    </div>
                    <div class="stat-card pending">
                        <h3>Pending</h3>
                        <div class="stat-value"><?php echo $pending_requests; ?></div>
                        <p>Awaiting registry approval</p>
                    </div>
                    <div class="stat-card approved">
                        <h3>Approved</h3>
                        <div class="stat-value"><?php echo $approved_requests; ?></div>
                        <p>Successfully cleared by registry</p>
                    </div>
                    <div class="stat-card rejected">
                        <h3>Rejected</h3>
                        <div class="stat-value"><?php echo $rejected_requests; ?></div>
                        <p>Rejected clearance requests</p>
                    </div>
                </div>

                <!-- Pending Clearances Section -->
                <div class="section">
                    <div class="section-title">
                        <h2><i class="fas fa-clock"></i> Pending Clearance Requests</h2>
                    </div>
                    <div class="table-container">
                        <?php if ($pending_clearances->num_rows > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Request Code</th>
                                        <th>Student Name</th>
                                        <th>Matric No</th>
                                        <th>Department</th>
                                        <th>Requested Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $pending_clearances->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['request_code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['matric_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['student_department']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($row['requested_at'])); ?></td>
                                            <td><span class="status-badge status-pending">Pending</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-approve" onclick="openApproveModal(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button class="btn btn-sm btn-reject" onclick="openRejectModal(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                                <button class="btn btn-sm btn-view" onclick="window.open('clearance_details.php?id=<?php echo $row['id']; ?>', '_blank')">
    <i class="fas fa-eye"></i> View
</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fas fa-check-circle" style="font-size: 48px; color: #10b981; margin-bottom: 20px;"></i>
                                <h3>No Pending Requests</h3>
                                <p>All registry clearance requests have been processed.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Clearances Section -->
                <div class="section">
                    <div class="section-title">
                        <h2><i class="fas fa-history"></i> Recent Clearance Activity</h2>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Request Code</th>
                                    <th>Student Name</th>
                                    <th>Matric No</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent_clearances->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['request_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['matric_no']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($row['updated_at'])); ?></td>
                                        <td><?php echo $row['approved_by'] ? 'Registry Officer' : 'Pending'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reports Section -->
                <div class="section" id="reports-section" style="display: none;">
                    <div class="section-title">
                        <h2><i class="fas fa-chart-bar"></i> Generate Reports</h2>
                    </div>
                    <form method="POST" action="" class="report-form">
                        <div class="form-group">
                            <label for="start_date"><i class="fas fa-calendar-start"></i> Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date"><i class="fas fa-calendar-end"></i> End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="status"><i class="fas fa-filter"></i> Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="generate_report" class="btn btn-approve" style="margin-top: 25px;">
                                <i class="fas fa-download"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal" id="approveModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-check-circle"></i> Approve Clearance</h3>
                <button class="close-modal" onclick="closeModal('approveModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="request_id" id="approve_request_id">
                <div class="form-group">
                    <label for="approve_comments">Comments (Optional)</label>
                    <textarea id="approve_comments" name="comments" class="form-control" rows="4" 
                              placeholder="Add any comments or notes..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeModal('approveModal')" 
                            style="background: #64748b; color: white;">Cancel</button>
                    <button type="submit" name="approve_clearance" class="btn btn-approve">
                        <i class="fas fa-check"></i> Confirm Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal" id="rejectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-times-circle"></i> Reject Clearance</h3>
                <button class="close-modal" onclick="closeModal('rejectModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="request_id" id="reject_request_id">
                <div class="form-group">
                    <label for="reject_comments">Reason for Rejection *</label>
                    <textarea id="reject_comments" name="comments" class="form-control" rows="4" 
                              placeholder="Please provide reason for rejection..." required></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeModal('rejectModal')" 
                            style="background: #64748b; color: white;">Cancel</button>
                    <button type="submit" name="reject_clearance" class="btn btn-reject">
                        <i class="fas fa-times"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function openApproveModal(requestId) {
            document.getElementById('approve_request_id').value = requestId;
            document.getElementById('approveModal').style.display = 'flex';
        }

        function openRejectModal(requestId) {
            document.getElementById('reject_request_id').value = requestId;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Navigation Functions
        function showPendingClearances() {
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
            });
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelectorAll('.section')[0].style.display = 'block';
            document.querySelectorAll('.nav-item')[1].classList.add('active');
        }

        function showReports() {
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
            });
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            document.getElementById('reports-section').style.display = 'block';
            document.querySelectorAll('.nav-item')[2].classList.add('active');
        }

        function showFinalApprovals() {
            alert('Final Approvals section will show completed clearances ready for certificate issuance.');
        }

        function showStudentSearch() {
            alert('Student Search feature - Search students by matric number or name.');
        }

        function showSettings() {
            alert('Registry settings and preferences.');
        }

        function viewDetails(requestId) {
            window.open(`clearance_details.php?id=${requestId}`, '_blank');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>