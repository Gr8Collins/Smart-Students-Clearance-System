<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

Auth::requireRole(['student']);
$user_id = $_SESSION['user_id'];
$matric_no = $_SESSION['matric_no'];

// Handle clearance request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_clearance'])) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get active clearance units
    $units = get_clearance_units();
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        foreach ($units as $unit) {
            // Check if request already exists
            $checkStmt = $conn->prepare("
                SELECT id FROM clearance_requests 
                WHERE matric_no = ? AND unit_id = ? AND status IN ('pending', 'approved')
            ");
            $checkStmt->bind_param("si", $matric_no, $unit['id']);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows === 0) {
                // Create new request
                $request_code = generate_request_code($matric_no);
                
                $stmt = $conn->prepare("
                    INSERT INTO clearance_requests 
                    (request_code, matric_no, student_name, student_department, unit_id, unit_name) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param(
                    "ssssis",
                    $request_code,
                    $matric_no,
                    $_SESSION['full_name'],
                    $_SESSION['department'],
                    $unit['id'],
                    $unit['unit_name']
                );
                $stmt->execute();
                
                // Log the action
                log_clearance_action($conn->insert_id, 'requested', 'Student requested clearance');
                
                // Add notification for staff
                add_notification(
                    0, // Will be set per staff member when they login
                    'New Clearance Request',
                    "Student {$matric_no} has requested {$unit['unit_name']} clearance",
                    'info'
                );
            }
        }
        
        $db->commit();
        $success = "Clearance request submitted successfully!";
    } catch (Exception $e) {
        $db->rollback();
        $error = "Failed to submit clearance request: " . $e->getMessage();
    }
}

// Get clearance status
$clearance_status = get_user_clearance_status($matric_no);
$all_approved = true;
$pending_count = 0;

foreach ($clearance_status as $status) {
    if ($status['status'] !== 'approved') {
        $all_approved = false;
    }
    if ($status['status'] === 'pending') {
        $pending_count++;
    }
}

// Get notifications
$notifications = get_user_notifications($user_id, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Smart Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            position: fixed;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .badge {
            font-size: 0.8em;
            padding: 5px 10px;
        }
        .clearance-item {
            border-left: 4px solid;
            margin-bottom: 10px;
        }
        .clearance-item.pending { border-color: #ffc107; }
        .clearance-item.approved { border-color: #28a745; }
        .clearance-item.rejected { border-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-graduation-cap"></i> Student Portal</h4>
                        <h6 class="text-light"><?php echo $_SESSION['full_name']; ?></h6>
                        <small><?php echo $matric_no; ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#requestModal">
                                <i class="fas fa-paper-plane"></i> Request Clearance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#status">
                                <i class="fas fa-tasks"></i> Clearance Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-bell"></i> Notifications
                                <?php if (count($notifications) > 0): ?>
                                    <span class="badge bg-danger float-end"><?php echo count($notifications); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Student Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
                                <i class="fas fa-paper-plane"></i> Request Clearance
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Units</h5>
                                <h2><?php echo count($clearance_status); ?></h2>
                                <p class="card-text">Clearance Units</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Approved</h5>
                                <h2><?php echo count(array_filter($clearance_status, function($s) { return $s['status'] === 'approved'; })); ?></h2>
                                <p class="card-text">Units Cleared</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h2><?php echo $pending_count; ?></h2>
                                <p class="card-text">Awaiting Approval</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card <?php echo $all_approved ? 'bg-info' : 'bg-secondary'; ?> text-white">
                            <div class="card-body">
                                <h5 class="card-title">Overall</h5>
                                <h2><?php echo $all_approved ? 'CLEARED' : 'PENDING'; ?></h2>
                                <p class="card-text">Status</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Clearance Status -->
                <div class="card mb-4" id="status">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks"></i> Clearance Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($clearance_status)): ?>
                            <div class="alert alert-info">
                                No clearance requests found. Click "Request Clearance" to start.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Unit</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th>Approved By</th>
                                            <th>Approval Date</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clearance_status as $item): ?>
                                            <tr class="clearance-item <?php echo $item['status']; ?>">
                                                <td>
                                                    <strong><?php echo $item['unit_name']; ?></strong><br>
                                                    <small class="text-muted"><?php echo $item['description']; ?></small>
                                                </td>
                                                <td><?php echo format_date($item['requested_at']); ?></td>
                                                <td><?php echo get_status_badge($item['status']); ?></td>
                                                <td><?php echo $item['approver_name'] ?: 'N/A'; ?></td>
                                                <td><?php echo format_date($item['approved_at']); ?></td>
                                                <td><?php echo $item['comments'] ?: 'No comments'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bell"></i> Recent Notifications
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <div class="alert alert-info">
                                No new notifications.
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($notifications as $notification): ?>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo $notification['title']; ?></h6>
                                            <small><?php echo format_date($notification['created_at']); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo $notification['message']; ?></p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Request Clearance Modal -->
    <div class="modal fade" id="requestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Request Clearance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to submit clearance requests to all departments?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            This will create pending clearance requests for all active units.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="request_clearance" class="btn btn-primary">
                            Submit Requests
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh status every 30 seconds
        setInterval(function() {
            window.location.reload();
        }, 30000);
        
        // Mark notifications as read when clicked
        document.querySelectorAll('.list-group-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.add('list-group-item-light');
            });
        });
    </script>
</body>
</html>