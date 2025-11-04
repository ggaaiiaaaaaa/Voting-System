<?php
require_once __DIR__ . "/../config/database.php";

class User {
    public $id;
    public $username;
    public $password;
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
            $insert = $this->db->connect()->prepare(
                "INSERT INTO users (username, password, role) VALUES (:username, :password, 'admin')"
            );
            $insert->execute([
                'username' => 'admin',
                'password' => $defaultPassword
            ]);
        }
    }

    public function login() {
        $sql = "SELECT * FROM users WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $this->username);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($this->password, $user['password'])) {
            $this->id = $user['id'];
            $this->role = $user['role'];
            return true;
        }

        return false;
    }

    public function addAdmin() {
        $sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, 'admin')";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $this->username);
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $query->bindParam(":password", $hashedPassword);
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
        $sql = "UPDATE users SET username = :username";
        
        if (!empty($this->password)) {
            $sql .= ", password = :password";
        }
        $sql .= " WHERE id = :id AND role = 'admin'";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $this->username);
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
}
?>
