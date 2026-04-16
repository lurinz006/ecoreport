<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT n.*, r.title as report_title 
          FROM notifications n 
          LEFT JOIN reports r ON n.report_id = r.id 
          WHERE n.user_id = :user_id 
          ORDER BY n.created_at DESC 
          LIMIT 50";

$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$formatted_notifications = [];
foreach($notifications as $notif) {
    $type_labels = [
        'status_update' => 'Status Update',
        'new_report' => 'New Report',
        'official_remark' => 'Official Remark',
        'system' => 'System'
    ];
    
    $formatted_notifications[] = [
        'id' => $notif['id'],
        'message' => htmlspecialchars($notif['message']),
        'type' => $notif['type'],
        'type_label' => $type_labels[$notif['type']] ?? 'Unknown',
        'is_read' => (bool)$notif['is_read'],
        'created_at' => formatDate($notif['created_at']),
        'report_title' => $notif['report_title'] ? htmlspecialchars($notif['report_title']) : null
    ];
}

echo json_encode(['success' => true, 'notifications' => $formatted_notifications]);
?>
