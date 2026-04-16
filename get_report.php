<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

if(!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit();
}

$report_id = intval($_GET['id']);

$database = new Database();
$db = $database->getConnection();
$report = new Report($db);

$report_data = $report->getById($report_id);

if(!$report_data) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit();
}

// Check if user has permission to view this report
if(isResident() && $report_data['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to view this report']);
    exit();
}

$updates = $report->getUpdates($report_id);
$formatted_updates = [];
foreach($updates as $update) {
    if($update['update_type'] === 'status_change') {
        $formatted_updates[] = [
            'id' => $update['id'],
            'updated_by_name' => htmlspecialchars($update['updated_by_name']),
            'updated_by_role' => $update['updated_by_role'],
            'new_status_label' => getStatusBadge($update['new_value']) ? strip_tags(getStatusBadge($update['new_value'])) : 'Status Update',
            'description' => htmlspecialchars($update['description']),
            'created_at' => formatDate($update['created_at'])
        ];
    }
}

// Format response
$response = [
    'success' => true,
    'report' => [
        'id' => $report_data['id'],
        'title' => htmlspecialchars($report_data['title']),
        'description' => htmlspecialchars($report_data['description']),
        'incident_type' => $report_data['incident_type'],
        'incident_type_label' => getIncidentTypeLabel($report_data['incident_type']),
        'location_address' => htmlspecialchars($report_data['location_address']),
        'latitude' => $report_data['latitude'],
        'longitude' => $report_data['longitude'],
        'status' => $report_data['status'],
        'status_badge' => getStatusBadge($report_data['status']),
        'priority' => $report_data['priority'],
        'priority_badge' => getPriorityBadge($report_data['priority']),
        'image_path' => $report_data['image_path'],
        'official_remarks' => $report_data['official_remarks'] ? htmlspecialchars($report_data['official_remarks']) : null,
        'reporter_name' => htmlspecialchars($report_data['reporter_name']),
        'reporter_contact' => htmlspecialchars($report_data['reporter_contact']),
        'assigned_official_name' => $report_data['assigned_official_name'] ? htmlspecialchars($report_data['assigned_official_name']) : null,
        'created_at' => formatDate($report_data['created_at']),
        'updated_at' => formatDate($report_data['updated_at']),
        'updates' => $formatted_updates
    ]
];

echo json_encode($response);
?>
