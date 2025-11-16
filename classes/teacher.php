<?php
require_once __DIR__ . "/../config/database.php";

class Teacher {
    public $id;
    public $fullname;
    public $teacher_id;
    public $password;
    public $advisory_section;
    public $status;

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect(); // ✅ Fix: assign PDO connection once
    }

    // -------------------- ADD TEACHER --------------------
    public function addTeacher() {
        $sql = "INSERT INTO teachers (fullname, teacher_id, password, advisory_section, status)
                VALUES (:fullname, :teacher_id, :password, :advisory_section, :status)";
        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $status = 'Active'; // ✅ Default active when added

        $result = $stmt->execute([
            ':fullname' => $this->fullname,
            ':teacher_id' => $this->teacher_id,
            ':password' => $hashedPassword,
            ':advisory_section' => $this->advisory_section,
            ':status' => $status
        ]);

        if ($result) {
            $this->logAction($_SESSION['user_id'] ?? null, "Added teacher", "Teacher ID: {$this->teacher_id}");
        }

        return $result;
    }

    // -------------------- EDIT TEACHER --------------------
    public function editTeacher($id) {
        $sql = "UPDATE teachers 
                SET fullname = :fullname, teacher_id = :teacher_id, advisory_section = :advisory_section, status = :status 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $result = $stmt->execute([
            ':fullname' => $this->fullname,
            ':teacher_id' => $this->teacher_id,
            ':advisory_section' => $this->advisory_section,
            ':status' => $this->status ?? 'Active',
            ':id' => $id
        ]);

        if ($result) {
            $this->logAction($_SESSION['user_id'] ?? null, "Edited teacher", "Teacher ID: {$this->teacher_id}");
        }

        return $result;
    }

    // -------------------- DELETE TEACHER --------------------
    public function deleteTeacher($id) {
        $teacher = $this->fetchTeacher($id); // For logging
        $sql = "DELETE FROM teachers WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([':id' => $id]);

        if ($result && $teacher) {
            $this->logAction($_SESSION['user_id'] ?? null, "Deleted teacher", "Teacher ID: {$teacher['teacher_id']}");
        }

        return $result;
    }

    // -------------------- GET ALL ADVISORY SECTIONS --------------------
    public function getAllAdvisorySections() {
        $sql = "SELECT advisory_section 
                FROM teachers 
                WHERE advisory_section IS NOT NULL 
                  AND advisory_section != '' 
                ORDER BY advisory_section ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'advisory_section');
    }

    // -------------------- VIEW ALL TEACHERS --------------------
    public function viewTeachers($search = "") {
        $sql = "SELECT * FROM teachers WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND fullname LIKE CONCAT('%', :search, '%')";
        }
        $sql .= " ORDER BY fullname ASC";
        $stmt = $this->conn->prepare($sql);

        if (!empty($search)) {
            $stmt->bindParam(':search', $search);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------- FETCH SINGLE TEACHER --------------------
    public function fetchTeacher($id) {
        $sql = "SELECT * FROM teachers WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -------------------- CHECK DUPLICATE TEACHER ID --------------------
    public function isTeacherIdExist($teacher_id, $id = null) {
        $sql = "SELECT id FROM teachers WHERE teacher_id = :teacher_id";
        if ($id) {
            $sql .= " AND id != :id";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":teacher_id", $teacher_id);
        if ($id) {
            $stmt->bindParam(":id", $id);
        }
        $stmt->execute();
        return $stmt->fetch() ? true : false;
    }

    // -------------------- COUNT TEACHERS --------------------
    public function countTeachers() {
        $sql = "SELECT COUNT(*) AS total FROM teachers";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    // -------------------- LOG ACTION --------------------
    public function logAction($user_id, $action, $details = null) {
        if (!$user_id) return false;
        $sql = "INSERT INTO audit_log (user_id, action, details, created_at) 
                VALUES (:user_id, :action, :details, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":details", $details);
        return $stmt->execute();
    }
}
?>
