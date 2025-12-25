<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_request_code($matric_no) {
    $date = date('Ymd');
    $random = mt_rand(1000, 9999);
    $initials = substr(preg_replace('/[^A-Z]/', '', strtoupper($matric_no)), 0, 3);
    return "CLR-{$date}-{$initials}-{$random}";
}

function get_clearance_units() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM clearance_units WHERE is_active = 1 ORDER BY unit_name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $units = [];
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
    
    return $units;
}

function get_user_clearance_status($matric_no) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            cr.*,
            cu.unit_name,
            cu.description,
            u.full_name as approver_name
        FROM clearance_requests cr
        JOIN clearance_units cu ON cr.unit_id = cu.id
        LEFT JOIN users u ON cr.approved_by = u.id
        WHERE cr.matric_no = ?
        ORDER BY cr.requested_at DESC
    ");
    $stmt->bind_param("s", $matric_no);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $status = [];
    while ($row = $result->fetch_assoc()) {
        $status[] = $row;
    }
    
    return $status;
}

function get_pending_requests($role) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            cr.*,
            cu.unit_name,
            u.full_name as student_name,
            u.department as student_department
        FROM clearance_requests cr
        JOIN clearance_units cu ON cr.unit_id = cu.id
        JOIN users u ON cr.matric_no = u.matric_no
        WHERE cu.approval_role = ? 
        AND cr.status = 'pending'
        ORDER BY cr.requested_at ASC
    ");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    return $requests;
}

function add_notification($user_id, $title, $message, $type = 'info') {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    return $stmt->execute();
}

function get_user_notifications($user_id, $unread_only = false) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "SELECT * FROM notifications WHERE user_id = ?";
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    $sql .= " ORDER BY created_at DESC LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

function mark_notification_read($notification_id) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

function log_clearance_action($request_id, $action, $notes = '') {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $performed_by = $_SESSION['user_id'] ?? 0;
    $performed_role = $_SESSION['role'] ?? 'system';
    
    $stmt = $conn->prepare("
        INSERT INTO clearance_history (request_id, action, performed_by, performed_role, notes)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isiss", $request_id, $action, $performed_by, $performed_role, $notes);
    return $stmt->execute();
}

function format_date($date_string, $format = 'M d, Y h:i A') {
    if (empty($date_string)) return 'N/A';
    
    $date = new DateTime($date_string);
    return $date->format($format);
}

function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'approved' => '<span class="badge badge-success">Approved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'on_hold' => '<span class="badge badge-info">On Hold</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}
?>