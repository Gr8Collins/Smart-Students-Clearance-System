<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has registry/admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'registry')) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$status = $_GET['status'] ?? 'all';

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Build query based on filters
$query = "
    SELECT cr.*, u.full_name as student_name, u.department as student_department,
           u.email, u2.full_name as approver_name
    FROM clearance_requests cr
    JOIN users u ON cr.matric_no = u.matric_no
    LEFT JOIN users u2 ON cr.approved_by = u2.id
    WHERE cr.unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG')
    AND cr.requested_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
";

$params = [$start_date, $end_date];
$types = "ss";

if ($status != 'all') {
    $query .= " AND cr.status = ?";
    $params[] = $status;
    $types .= "s";
}

$query .= " ORDER BY cr.requested_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get statistics for the filtered period
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM clearance_requests
    WHERE unit_id = (SELECT id FROM clearance_units WHERE unit_code = 'REG')
    AND requested_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
";

if ($status != 'all') {
    $stats_query .= " AND status = '$status'";
}

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("ss", $start_date, $end_date);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registry Reports - Smart Clearance System</title>
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

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
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
            font-size: 14px;
        }

        .btn-primary {
            background: #10b981;
            color: white;
        }

        .btn-secondary {
            background: #3b82f6;
            color: white;
        }

        .btn-back {
            background: #64748b;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .content {
            padding: 30px;
        }

        .filter-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #4f46e5;
        }

        .filter-info h3 {
            color: #475569;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .filter-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-tag {
            background: #e2e8f0;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .stat-card h4 {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #1e293b;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .report-table th {
            background: #f1f5f9;
            padding: 15px;
            text-align: left;
            color: #475569;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }

        .report-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .report-table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #d1fae5; color: #059669; }
        .status-rejected { background: #fee2e2; color: #dc2626; }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #64748b;
        }

        .no-data i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .export-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
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
            
            .header-actions, .filter-info, .export-options {
                display: none;
            }
            
            .report-table {
                font-size: 12px;
            }
            
            .report-table th, .report-table td {
                padding: 8px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-chart-bar"></i>
                Registry Clearance Reports
            </h1>
            <div class="header-actions">
                <a href="registry_dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <button class="btn btn-secondary" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>

        <div class="content">
            <!-- Filter Information -->
            <div class="filter-info">
                <h3><i class="fas fa-filter"></i> Current Filters</h3>
                <div class="filter-tags">
                    <div class="filter-tag">
                        <i class="fas fa-calendar-start"></i>
                        Start Date: <?php echo date('F d, Y', strtotime($start_date)); ?>
                    </div>
                    <div class="filter-tag">
                        <i class="fas fa-calendar-end"></i>
                        End Date: <?php echo date('F d, Y', strtotime($end_date)); ?>
                    </div>
                    <div class="filter-tag">
                        <i class="fas fa-chart-pie"></i>
                        Status: <?php echo ucfirst($status); ?>
                    </div>
                    <div class="filter-tag">
                        <i class="fas fa-clock"></i>
                        Generated: <?php echo date('F d, Y H:i'); ?>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Requests</h4>
                    <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Pending</h4>
                    <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Approved</h4>
                    <div class="stat-value"><?php echo $stats['approved'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Rejected</h4>
                    <div class="stat-value"><?php echo $stats['rejected'] ?? 0; ?></div>
                </div>
            </div>

            <!-- Report Table -->
            <?php if ($result->num_rows > 0): ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Request Code</th>
                            <th>Student Name</th>
                            <th>Matric No</th>
                            <th>Department</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Approval Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['request_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['matric_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_department']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['requested_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['approver_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $row['approved_at'] ? date('M d, Y H:i', strtotime($row['approved_at'])) : 'N/A'; ?></td>
                                <td>
                                    <a href="clearance_details.php?id=<?php echo $row['id']; ?>" 
                                       class="btn" style="background: #3b82f6; color: white; padding: 5px 10px; font-size: 12px;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-chart-bar"></i>
                    <h3>No Data Found</h3>
                    <p>No clearance requests match the selected filters.</p>
                    <a href="registry_dashboard.php" class="btn btn-back" style="margin-top: 20px;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function exportToExcel() {
            // Create a table element for export
            const table = document.querySelector('.report-table');
            if (!table) {
                alert('No data to export!');
                return;
            }

            // Create CSV content
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const rowData = [];
                const cols = row.querySelectorAll('th, td');
                
                cols.forEach(col => {
                    // Remove action buttons from export
                    if (!col.querySelector('.btn')) {
                        // Clean up the text content
                        let text = col.textContent.trim();
                        // Remove status badge styling text
                        text = text.replace(/\s+/g, ' ').trim();
                        rowData.push(`"${text}"`);
                    }
                });
                
                if (rowData.length > 0) {
                    csv.push(rowData.join(','));
                }
            });

            // Download CSV file
            const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `registry_report_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportToPDF() {
            alert('PDF export functionality would require additional libraries like jsPDF. Consider implementing with a backend PDF generator.');
        }
    </script>
</body>
</html>