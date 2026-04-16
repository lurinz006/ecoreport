<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['databases' => $dbs]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
