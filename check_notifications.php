<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

$unread_count = getUnreadNotifications($_SESSION['user_id']);

echo json_encode([
    'success' => true,
    'new_count' => $unread_count
]);
?>
