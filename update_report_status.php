<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

if(!isOfficial()) {
    echo json_encode(['success' => false, 'message' => 'Only officials can update report status']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON data
$json_data = json_decode(file_get_contents('php://input'), true);

if(!$json_data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$report_id = intval($json_data['report_id']);
$new_status = sanitizeInput($json_data['status']);
$remarks = sanitizeInput($json_data['remarks']);

// Validation
if(empty($report_id) || empty($new_status) || empty($remarks)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit();
}

$valid_statuses = ['under_investigation', 'resolved', 'rejected'];
if(!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$report = new Report($db);

// Get current report details for notification
$current_report = $report->getById($report_id);
if(!$current_report) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit();
}

// Update report status
$result = $report->updateStatus($report_id, $new_status, $_SESSION['user_id'], $remarks);

if($result) {
    // Create notification for the reporter
    $status_labels = [
        'under_investigation' => 'Under Investigation',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected'
    ];
    
    createNotification(
        $current_report['user_id'],
        $report_id,
        "Your report '" . $current_report['title'] . "' status has been updated to: " . $status_labels[$new_status],
        'status_update'
    );
    
    // Create report update record
    $update_query = "INSERT INTO report_updates (report_id, updated_by, update_type, new_value, description) 
                     VALUES (:report_id, :updated_by, 'status_change', :new_value, :description)";
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(":report_id", $report_id);
    $update_stmt->bindParam(":updated_by", $_SESSION['user_id']);
    $update_stmt->bindParam(":new_value", $new_status);
    $update_stmt->bindParam(":description", $remarks);
    $update_stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Report status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update report status']);
}
?>
