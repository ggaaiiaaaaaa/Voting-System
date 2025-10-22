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
    }

    public function login() {
        $sql = "SELECT * FROM users WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $this->username);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ($user && $this->password === $user['password']) {
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
        $query->bindParam(":password", $this->password);
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
            $query->bindParam(":password", $this->password);
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