<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a report']);
    exit();
}

if(isOfficial()) {
    echo json_encode(['success' => false, 'message' => 'Residents can only submit reports']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get form data
$title = sanitizeInput($_POST['title']);
$description = sanitizeInput($_POST['description']);
$incident_type = sanitizeInput($_POST['incident_type']);
$location_address = sanitizeInput($_POST['location_address']);
$latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$priority = sanitizeInput($_POST['priority']);

// Validation
if(empty($title) || empty($description) || empty($incident_type) || empty($location_address)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit();
}

// Handle file upload
$image_path = null;
if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_result = uploadFile($_FILES['image']);
    if($upload_result['success']) {
        $image_path = $upload_result['filename'];
    } else {
        echo json_encode(['success' => false, 'message' => $upload_result['message']]);
        exit();
    }
}

// Create report
$database = new Database();
$db = $database->getConnection();
$report = new Report($db);

$report_id = $report->create(
    $_SESSION['user_id'],
    $title,
    $description,
    $incident_type,
    $location_address,
    $latitude,
    $longitude,
    $image_path,
    $priority
);

if($report_id) {
    // Create notification for officials
    $officials_query = "SELECT id FROM users WHERE role = 'official'";
    $officials_stmt = $db->prepare($officials_query);
    $officials_stmt->execute();
    $officials = $officials_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($officials as $official) {
        createNotification(
            $official['id'],
            $report_id,
            "New environmental report submitted: " . $title,
            'new_report'
        );
    }
    
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
}
?>
