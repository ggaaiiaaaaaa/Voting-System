<?php
require_once __DIR__ . "/../config/database.php";

class User {
    public $id;
    public $username;
    public $password;
    public $email;
    public $role;

    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->createDefaultAdmin();
    }

    // Automatically create default admin if none exists
    private function createDefaultAdmin() {
        $stmt = $this->db->connect()->prepare("SELECT * FROM users WHERE username = :username AND role = 'admin'");
        $stmt->execute(['username' => 'admin']);
        if (!$stmt->fetch()) {
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $defaultEmail = 'admin@gmail.com'; // Default admin email
            
            $insert = $this->db->connect()->prepare(
                "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, 'admin')"
            );
            $insert->execute([
                'username' => 'admin',
                'password' => $defaultPassword,
                'email' => $defaultEmail
            ]);
        }
    }

    // Login with username OR email
    public function login() {
        $sql = "SELECT * FROM users WHERE (username = :identifier OR email = :identifier)";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":identifier", $this->username);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($this->password, $user['password'])) {
            $this->id = $user['id'];
            $this->email = $user['email'] ?? null;
            $this->role = $user['role'];
            return true;
        }

        return false;
    }

    public function addAdmin() {
        // Check if email already exists
        if ($this->isEmailExist($this->email)) {
            return false;
        }
        
        $sql = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, 'admin')";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $this->username);
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $query->bindParam(":password", $hashedPassword);
        $query->bindParam(":email", $this->email);
        return $query->execute();
    }

    public function viewAdmins() {
        $sql = "SELECT * FROM users WHERE role = 'admin' ORDER BY username ASC";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAdmin($id) {
        $sql = "SELECT * FROM users WHERE id = :id AND role = 'admin'";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function editAdmin($id) {
        $sql = "UPDATE users SET username = :username, email = :email";
        
        if (!empty($this->password)) {
            $sql .= ", password = :password";
        }
        $sql .= " WHERE id = :id AND role = 'admin'";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $this->username);
        $query->bindParam(":email", $this->email);
        if (!empty($this->password)) {
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            $query->bindParam(":password", $hashedPassword);
        }
        $query->bindParam(":id", $id);
        return $query->execute();
    }

    public function deleteAdmin($id) {
        $sql = "DELETE FROM users WHERE id = :id AND role = 'admin'";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        return $query->execute();
    }

    public function isUsernameExist($username, $id = null) {
        $sql = "SELECT id FROM users WHERE username = :username";
        if ($id) {
            $sql .= " AND id != :id";
        }
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $username);
        if ($id) {
            $query->bindParam(":id", $id);
        }
        $query->execute();
        return $query->fetch() ? true : false;
    }

    public function isEmailExist($email, $id = null) {
        $sql = "SELECT id FROM users WHERE email = :email";
        if ($id) {
            $sql .= " AND id != :id";
        }
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":email", $email);
        if ($id) {
            $query->bindParam(":id", $id);
        }
        $query->execute();
        return $query->fetch() ? true : false;
    }

    // Get admin email (for notifications)
    public function getAdminEmail() {
        $sql = "SELECT email FROM users WHERE role = 'admin' LIMIT 1";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['email'] ?? 'rhonjames95@gmail.com';
    }

    // Get all admin emails (if multiple admins)
    public function getAllAdminEmails() {
        $sql = "SELECT id, email, username FROM users WHERE role = 'admin' AND email IS NOT NULL";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get admin ID by email
    public function getAdminIdByEmail($email) {
        $sql = "SELECT id FROM users WHERE email = :email AND role = 'admin'";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":email", $email);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['id'] ?? null;
    }
}
?>