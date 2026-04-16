<?php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isOfficial() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'official';
}

function isResident() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'resident';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function uploadFile($file, $upload_dir = 'uploads/') {
    if(!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }

    switch($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File too large'];
        case UPLOAD_ERR_PARTIAL:
            return ['success' => false, 'message' => 'File upload was incomplete'];
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file was uploaded'];
        default:
            return ['success' => false, 'message' => 'Unknown upload error'];
    }

    if($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum limit'];
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if(!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only images are allowed'];
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $upload_path = $upload_dir . $filename;

    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if(move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $filename, 'path' => $upload_path];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

function formatDate($date) {
    return date('M d, Y h:i A', strtotime($date));
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'under_investigation' => '<span class="badge bg-info">Under Investigation</span>',
        'resolved' => '<span class="badge bg-success">Resolved</span>',
        'rejected' => '<span class="badge bg-danger">Rejected</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">Unknown</span>';
}

function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge bg-secondary">Low</span>',
        'medium' => '<span class="badge bg-primary">Medium</span>',
        'high' => '<span class="badge bg-warning">High</span>',
        'urgent' => '<span class="badge bg-danger">Urgent</span>'
    ];
    
    return isset($badges[$priority]) ? $badges[$priority] : '<span class="badge bg-secondary">Unknown</span>';
}

function getIncidentTypeLabel($type) {
    $types = [
        'pollution' => 'Pollution',
        'illegal_dumping' => 'Illegal Dumping',
        'flood' => 'Flood',
        'fire_hazard' => 'Fire Hazard',
        'noise_pollution' => 'Noise Pollution',
        'air_pollution' => 'Air Pollution',
        'water_pollution' => 'Water Pollution',
        'other' => 'Other'
    ];
    
    return isset($types[$type]) ? $types[$type] : 'Unknown';
}

function createNotification($user_id, $report_id, $message, $type) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO notifications (user_id, report_id, message, type) 
              VALUES (:user_id, :report_id, :message, :type)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":report_id", $report_id);
    $stmt->bindParam(":message", $message);
    $stmt->bindParam(":type", $type);
    
    return $stmt->execute();
}

function getUnreadNotifications($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT COUNT(*) as count FROM notifications 
              WHERE user_id = :user_id AND is_read = FALSE";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}

function markNotificationsAsRead($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    
    return $stmt->execute();
}

function paginate($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    return [
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => ($current_page - 1) * $items_per_page
    ];
}

function flashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessages() {
    $messages = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
    unset($_SESSION['flash']);
    return $messages;
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Handle Password Reset Request
 */
function handlePasswordResetRequest($username, $email) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if both username and email match the same account
    $query = "SELECT id FROM users WHERE username = :username AND email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if($stmt->rowCount() == 0) {
        return ['success' => false, 'message' => 'Incorrect Email, try to put the correct email.'];
    }
    
    // Generate 6-digit code
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Clear old codes
    $query = "DELETE FROM password_resets WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    // Insert new code
    $query = "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":token", $code);
    $stmt->bindParam(":expires_at", $expires_at);
    
    if($stmt->execute()) {
        // For development/demo, store in session
        $_SESSION['demo_reset_code'] = $code; 
        return ['success' => true, 'code' => $code];
    }
    
    return ['success' => false, 'message' => 'Failed to generate reset code.'];
}

/**
 * Verify Reset Code
 */
function verifyResetCode($email, $code) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM password_resets WHERE email = :email AND token = :token AND expires_at > NOW() LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":token", $code);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}

/**
 * Update User Password
 */
function resetUserPassword($email, $new_password) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Storing as plain text as requested (No Hashing)
    $query = "UPDATE users SET password = :password WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":password", $new_password);
    $stmt->bindParam(":email", $email);
    
    if($stmt->execute()) {
        $query = "DELETE FROM password_resets WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return true;
    }
    return false;
}
?>
