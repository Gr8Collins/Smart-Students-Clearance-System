<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

Auth::requireRole(['library', 'bursary', 'department', 'faculty']);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $comments = sanitize_input($_POST['comments'] ?? '');
    
    $db->beginTransaction();
    
    try {
        // Get request details
        $stmt = $conn->prepare("
            SELECT cr.*, u.email as student_email 
            FROM clearance_requests cr
            JOIN users u ON cr.matric_no = u.matric_no
            WHERE cr.id = ?
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();
        
        if ($request) {
            // Update request status
            $updateStmt = $conn->prepare("
                UPDATE clearance_requests 
                SET status = ?, 
                    approved_by = ?, 
                    approved_at = NOW(),
                    comments = CONCAT(IFNULL(comments, ''), ?)
                WHERE id = ?
            ");
            
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $comment_text = "\n[" . date('Y-m-d H:i') . "] {$status} by {$_SESSION['full_name']}: {$comments}";
            
            $updateStmt->bind_param("sisi", $status, $user_id, $comment_text, $request_id);
            $updateStmt->execute();
            
            // Log the action
            log_clearance_action($request_id, $status, $comments);
            
            // Add notification for student
            $studentStmt = $conn->prepare("SELECT id FROM users WHERE matric_no = ?");
            $studentStmt->bind_param("s", $request['matric_no']);
            $studentStmt->execute();
            $studentResult = $studentStmt->get_result();
            
            if ($studentRow = $studentResult->fetch_assoc()) {
                add_notification(
                    $studentRow['id'],
                    'Clearance Update',
                    "Your {$request['unit_name']} clearance has been {$status}. {$comments}",
                    $action === 'approve' ? 'success' : 'error'
                );
            }
            
            $db->commit();
            $success = "Request {$status} successfully!";
        }
    } catch (Exception $e) {
        $db->rollback();
        $error = "Failed to process request: " . $e->getMessage();
    }
}

// Get pending requests for this role
$pending_requests = get_pending_requests($role);
$processed_requests = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($role); ?> Dashboard - Smart Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-tie"></i> <?php echo ucfirst($role); ?> Portal
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <?php echo $_SESSION['full_name']; ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
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
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">
                            <i class="fas fa-clock"></i> Pending Clearance Requests
                            <span class="badge bg-danger float-end"><?php echo count($pending_requests); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_requests)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> No pending requests.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Request Code</th>
                                            <th>Student</th>
                                            <th>Matric No</th>
                                            <th>Department</th>
                                            <th>Request Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_requests as $request): ?>
                                            <tr>
                                                <td><code><?php echo $request['request_code']; ?></code></td>
                                                <td><?php echo $request['student_name']; ?></td>
                                                <td><?php echo $request['matric_no']; ?></td>
                                                <td><?php echo $request['student_department']; ?></td>
                                                <td><?php echo format_date($request['requested_at']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#actionModal"
                                                            data-request-id="<?php echo $request['id']; ?>"
                                                            data-student-name="<?php echo $request['student_name']; ?>"
                                                            data-action="approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#actionModal"
                                                            data-request-id="<?php echo $request['id']; ?>"
                                                            data-student-name="<?php echo $request['student_name']; ?>"
                                                            data-action="reject">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Pending Requests</h6>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: <?php echo min(count($pending_requests) * 20, 100); ?>%">
                                    <?php echo count($pending_requests); ?>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Today's Approvals</h6>
                            <h4 class="text-success">0</h4>
                        </div>
                        <div class="mb-3">
                            <h6>Role</h6>
                            <span class="badge bg-primary"><?php echo ucfirst($role); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item">
                                <small>No recent actions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Process Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="request_id" id="modalRequestId">
                        <input type="hidden" name="action" id="modalAction">
                        
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <input type="text" class="form-control" id="modalStudentName" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea class="form-control" name="comments" rows="3" 
                                      placeholder="Add comments (optional)"></textarea>
                        </div>
                        
                        <div class="alert" id="actionAlert">
                            <i class="fas fa-info-circle"></i>
                            <span id="actionMessage"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="modalSubmitBtn">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const actionModal = document.getElementById('actionModal');
        actionModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-request-id');
            const studentName = button.getAttribute('data-student-name');
            const action = button.getAttribute('data-action');
            
            document.getElementById('modalRequestId').value = requestId;
            document.getElementById('modalAction').value = action;
            document.getElementById('modalStudentName').value = studentName;
            
            const alertDiv = document.getElementById('actionAlert');
            const messageSpan = document.getElementById('actionMessage');
            const submitBtn = document.getElementById('modalSubmitBtn');
            
            if (action === 'approve') {
                alertDiv.className = 'alert alert-success';
                messageSpan.textContent = `You are about to approve clearance for ${studentName}.`;
                submitBtn.className = 'btn btn-success';
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
            } else {
                alertDiv.className = 'alert alert-danger';
                messageSpan.textContent = `You are about to reject clearance for ${studentName}.`;
                submitBtn.className = 'btn btn-danger';
                submitBtn.innerHTML = '<i class="fas fa-times"></i> Reject';
            }
        });
        
        // Auto-refresh every 60 seconds
        setInterval(function() {
            window.location.reload();
        }, 60000);
    </script>
</body>
</html>