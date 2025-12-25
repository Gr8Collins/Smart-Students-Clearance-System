<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

Auth::requireRole(['admin']);

// Get statistics
$db = Database::getInstance();
$conn = $db->getConnection();

// Count users by role
$roleStats = [];
$roleQuery = $conn->query("
    SELECT role, COUNT(*) as count 
    FROM users 
    WHERE role != 'admin' 
    GROUP BY role
");
while ($row = $roleQuery->fetch_assoc()) {
    $roleStats[$row['role']] = $row['count'];
}

// Count clearance requests by status
$statusQuery = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM clearance_requests 
    GROUP BY status
");
$statusStats = [];
while ($row = $statusQuery->fetch_assoc()) {
    $statusStats[$row['status']] = $row['count'];
}

// Recent activities
$activitiesQuery = $conn->query("
    SELECT ch.*, u.full_name, u.role
    FROM clearance_history ch
    JOIN users u ON ch.performed_by = u.id
    ORDER BY ch.created_at DESC
    LIMIT 10
");
$recentActivities = [];
while ($row = $activitiesQuery->fetch_assoc()) {
    $recentActivities[] = $row;
}

// System overview
$totalUsers = array_sum($roleStats);
$totalRequests = array_sum($statusStats);
$pendingRequests = $statusStats['pending'] ?? 0;
$approvedRequests = $statusStats['approved'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cogs"></i> Admin Dashboard
            </a>
            <div class="d-flex">
                <a href="manage_users.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Stats Cards -->
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Users</h6>
                                <h2><?php echo $totalUsers; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Requests</h6>
                                <h2><?php echo $totalRequests; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Pending Requests</h6>
                                <h2><?php echo $pendingRequests; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Approved Requests</h6>
                                <h2><?php echo $approvedRequests; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Charts -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Users by Role</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="roleChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Clearance Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent System Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Action</th>
                                        <th>Performed By</th>
                                        <th>Role</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentActivities as $activity): ?>
                                        <tr>
                                            <td><?php echo format_date($activity['created_at']); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $activity['action'] == 'approved' ? 'bg-success' : 
                                                           ($activity['action'] == 'rejected' ? 'bg-danger' : 'bg-info'); ?>">
                                                    <?php echo ucfirst($activity['action']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $activity['full_name']; ?></td>
                                            <td><?php echo ucfirst($activity['role']); ?></td>
                                            <td><?php echo $activity['notes']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Role Distribution Chart
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($roleStats)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($roleStats)); ?>,
                    backgroundColor: [
                        '#ff6384', '#36a2eb', '#cc65fe', '#ffce56', '#4bc0c0'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($statusStats)); ?>,
                datasets: [{
                    label: 'Number of Requests',
                    data: <?php echo json_encode(array_values($statusStats)); ?>,
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.7)',  // pending
                        'rgba(75, 192, 192, 0.7)',   // approved
                        'rgba(255, 99, 132, 0.7)',   // rejected
                        'rgba(54, 162, 235, 0.7)'    // on_hold
                    ],
                    borderColor: [
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>