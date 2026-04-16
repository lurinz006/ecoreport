<?php
require_once 'includes/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Check if column already exists
    $check_query = "SHOW COLUMNS FROM users LIKE 'profile_image'";
    $check_stmt = $db->query($check_query);
    
    if($check_stmt->rowCount() == 0) {
        $query = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL";
        $db->exec($query);
        echo "Successfully added profile_image column to users table.\n";
    } else {
        echo "Column profile_image already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
