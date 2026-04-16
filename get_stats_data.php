<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

header('Content-Type: application/json');

if(!isLoggedIn() || isResident()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$type = isset($_GET['type']) ? $_GET['type'] : '';

if($type == 'incident_breakdown') {
    $query = "SELECT incident_type, COUNT(*) as count FROM reports GROUP BY incident_type";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $values = [];
    foreach($results as $row) {
        $labels[] = getIncidentTypeLabel($row['incident_type']);
        $values[] = (int)$row['count'];
    }

    echo json_encode(['labels' => $labels, 'values' => $values]);
    exit();
}

if($type == 'trend') {
    // Last 7 days trend
    $labels = [];
    $values = [];
    for($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('M d', strtotime($date));
        
        $query = "SELECT COUNT(*) as count FROM reports WHERE DATE(created_at) = :date";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $values[] = (int)$count;
    }

    echo json_encode(['labels' => $labels, 'values' => $values]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid type']);
?>
