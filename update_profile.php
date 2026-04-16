<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Create profiles directory if it doesn't exist
    $upload_dir = 'uploads/profiles/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $uploadCount = uploadFile($_FILES['profile_image'], $upload_dir);
    
    if($uploadCount['success']) {
        $filename = $uploadCount['filename'];
        
        // Update database
        $query = "UPDATE users SET profile_image = :profile_image WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":profile_image", $filename);
        $stmt->bindParam(":id", $_SESSION['user_id']);
        
        if($stmt->execute()) {
            // Update session
            $_SESSION['profile_image'] = $filename;
            echo json_encode(['success' => true, 'message' => 'Profile image updated successfully', 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $uploadCount['message']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
