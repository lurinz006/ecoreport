<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $email, $password, $full_name, $role = 'resident', $contact_number = '', $address = '') {
        $query = "INSERT INTO " . $this->table_name . " 
                (username, email, password, full_name, role, contact_number, address) 
                VALUES (:username, :email, :password, :full_name, :role, :contact_number, :address)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $username = htmlspecialchars(strip_tags($username));
        $email = htmlspecialchars(strip_tags($email));
        $full_name = htmlspecialchars(strip_tags($full_name));
        $role = htmlspecialchars(strip_tags($role));
        $contact_number = htmlspecialchars(strip_tags($contact_number));
        $address = htmlspecialchars(strip_tags($address));
        // $password = password_hash($password, PASSWORD_DEFAULT);

        // Bind
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":contact_number", $contact_number);
        $stmt->bindParam(":address", $address);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, email, password, full_name, role, contact_number, address, profile_image 
                 FROM " . $this->table_name . " 
                 WHERE username = :username OR email = :username LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && $password === $row['password']) {
            return $row;
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT id, username, email, full_name, role, contact_number, address, profile_image, created_at 
                 FROM " . $this->table_name . " 
                 WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllOfficials() {
        $query = "SELECT id, username, email, full_name, contact_number 
                 FROM " . $this->table_name . " 
                 WHERE role = 'official' 
                 ORDER BY full_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class Report {
    private $conn;
    private $table_name = "reports";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $title, $description, $incident_type, $location_address, 
                          $latitude = null, $longitude = null, $image_path = null, $priority = 'medium') {
        $query = "INSERT INTO " . $this->table_name . " 
                (user_id, title, description, incident_type, location_address, latitude, longitude, image_path, priority) 
                VALUES (:user_id, :title, :description, :incident_type, :location_address, :latitude, :longitude, :image_path, :priority)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $title = htmlspecialchars(strip_tags($title));
        $description = htmlspecialchars(strip_tags($description));
        $incident_type = htmlspecialchars(strip_tags($incident_type));
        $location_address = htmlspecialchars(strip_tags($location_address));
        $priority = htmlspecialchars(strip_tags($priority));

        // Bind
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":incident_type", $incident_type);
        $stmt->bindParam(":location_address", $location_address);
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);
        $stmt->bindParam(":image_path", $image_path);
        $stmt->bindParam(":priority", $priority);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT r.*, u.full_name as reporter_name, u.contact_number as reporter_contact,
                 o.full_name as assigned_official_name
                 FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.id
                 LEFT JOIN users o ON r.assigned_official_id = o.id
                 WHERE r.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($user_id, $limit = 10, $offset = 0) {
        $query = "SELECT r.*, 
                 CASE 
                     WHEN r.status = 'pending' THEN 'Pending'
                     WHEN r.status = 'under_investigation' THEN 'Under Investigation'
                     WHEN r.status = 'resolved' THEN 'Resolved'
                     WHEN r.status = 'rejected' THEN 'Rejected'
                 END as status_text
                 FROM " . $this->table_name . " r
                 WHERE r.user_id = :user_id
                 ORDER BY r.created_at DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($limit = 10, $offset = 0, $status = '', $incident_type = '') {
        $query = "SELECT r.*, u.full_name as reporter_name, u.contact_number as reporter_contact,
                 o.full_name as assigned_official_name
                 FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.id
                 LEFT JOIN users o ON r.assigned_official_id = o.id
                 WHERE 1=1";

        if(!empty($status)) {
            $query .= " AND r.status = :status";
        }
        if(!empty($incident_type)) {
            $query .= " AND r.incident_type = :incident_type";
        }

        $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        if(!empty($status)) {
            $stmt->bindParam(":status", $status);
        }
        if(!empty($incident_type)) {
            $stmt->bindParam(":incident_type", $incident_type);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $official_id, $remarks = '') {
        $query = "UPDATE " . $this->table_name . " 
                SET status = :status, assigned_official_id = :official_id, official_remarks = :remarks,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":official_id", $official_id);
        $stmt->bindParam(":remarks", $remarks);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function search($keyword, $limit = 10, $offset = 0) {
        $query = "SELECT r.*, u.full_name as reporter_name, u.contact_number as reporter_contact,
                 o.full_name as assigned_official_name
                 FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.id
                 LEFT JOIN users o ON r.assigned_official_id = o.id
                 WHERE r.title LIKE :keyword OR r.description LIKE :keyword OR r.location_address LIKE :keyword
                 ORDER BY r.created_at DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpdates($report_id) {
        $query = "SELECT ru.*, u.full_name as updated_by_name, u.role as updated_by_role
                 FROM report_updates ru
                 LEFT JOIN users u ON ru.updated_by = u.id
                 WHERE ru.report_id = :report_id
                 ORDER BY ru.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":report_id", $report_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
