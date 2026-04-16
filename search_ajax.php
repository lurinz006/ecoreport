<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

if(!isset($_GET['q']) || strlen(trim($_GET['q'])) < 2) {
    echo json_encode(['success' => false, 'message' => 'Search query must be at least 2 characters']);
    exit();
}

$search_query = trim($_GET['q']);

$database = new Database();
$db = $database->getConnection();
$report = new Report($db);

// Limit results for AJAX search
$search_results = $report->search($search_query, 5, 0);

$formatted_results = [];
foreach($search_results as $result) {
    $formatted_results[] = [
        'id' => $result['id'],
        'title' => htmlspecialchars($result['title']),
        'description' => htmlspecialchars($result['description']),
        'incident_type' => $result['incident_type'],
        'incident_type_label' => getIncidentTypeLabel($result['incident_type']),
        'status' => $result['status'],
        'priority' => $result['priority'],
        'created_at' => formatDate($result['created_at']),
        'reporter_name' => isOfficial() ? htmlspecialchars($result['reporter_name']) : null
    ];
}

echo json_encode([
    'success' => true,
    'results' => $formatted_results
]);
?>
