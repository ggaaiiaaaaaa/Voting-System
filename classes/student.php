<?php
require_once __DIR__ . "/../config/database.php";

class Student {
    public $id;
    public $fullname;
    public $student_id;
    public $password;
    public $grade_section;
    public $status;
    public $email;

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // -------------------- LOGIN --------------------
public function login() {
    // Support login via student_id OR email
    $sql = "SELECT * FROM students WHERE (student_id = :identifier OR email = :identifier)";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(":identifier", $this->student_id);
    $query->execute();

    $student = $query->fetch(PDO::FETCH_ASSOC);

    if ($student && $this->password === $student['password']) {
        $this->id = $student['id'];
        $this->fullname = $student['fullname'];
        $this->email = $student['email'];
        $this->grade_section = $student['grade_section'];
        $this->status = $student['status'];
        return true;
    }

    return false;
}

    // -------------------- CRUD --------------------
public function addStudent() {
    $sql = "INSERT INTO students (fullname, student_id, email, password, grade_section, status) 
            VALUES (:fullname, :student_id, :email, :password, :grade_section, :status)";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(":fullname", $this->fullname);
    $query->bindParam(":student_id", $this->student_id);
    $query->bindParam(":email", $this->email);
    $query->bindParam(":password", $this->password);
    $query->bindParam(":grade_section", $this->grade_section);
    $query->bindParam(":status", $this->status);
    $result = $query->execute();

    if ($result) {
        $this->logAction($_SESSION['user_id'] ?? null, "Added student", "Student ID: {$this->student_id}");
    }

    return $result;
}

    public function viewStudents() {
        $sql = "SELECT * FROM students ORDER BY student_id ASC";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchStudent($id) {
        $sql = "SELECT * FROM students WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentById($id) {
        // Alias for fetchStudent to match dashboard code
        return $this->fetchStudent($id);
    }

public function editStudent($id) {
    $sql = "UPDATE students 
            SET fullname = :fullname, student_id = :student_id, email = :email, 
                grade_section = :grade_section, status = :status
            WHERE id = :id";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(":fullname", $this->fullname);
    $query->bindParam(":student_id", $this->student_id);
    $query->bindParam(":email", $this->email);
    $query->bindParam(":grade_section", $this->grade_section);
    $query->bindParam(":status", $this->status);
    $query->bindParam(":id", $id);
    $result = $query->execute();

    if ($result) {
        $this->logAction($_SESSION['user_id'] ?? null, "Edited student", "Student ID: {$this->student_id}");
    }

    return $result;
}

    public function deleteStudent($id) {
        $student = $this->fetchStudent($id); // get student info for logging
        $sql = "DELETE FROM students WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        $result = $query->execute();

        if ($result && $student) {
            $this->logAction($_SESSION['user_id'] ?? null, "Deleted student", "Student ID: {$student['student_id']}");
        }

        return $result;
    }

    // -------------------- VALIDATIONS --------------------
    public function isStudentIdExist($student_id, $id = null) {
        $sql = "SELECT id FROM students WHERE student_id = :student_id";
        if ($id) {
            $sql .= " AND id != :id";
        }
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":student_id", $student_id);
        if ($id) {
            $query->bindParam(":id", $id);
        }
        $query->execute();
        return $query->fetch() ? true : false;
    }

    public function isEmailExist($email, $id = null) {
    $sql = "SELECT id FROM students WHERE email = :email";
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

    // -------------------- DASHBOARD --------------------
    public function countStudents() {
        $sql = "SELECT COUNT(*) AS total FROM students";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    // -------------------- LOGGING --------------------
    public function logAction($user_id, $action, $details = null) {
        if (!$user_id) return false;
        $sql = "INSERT INTO audit_log (user_id, action, details, created_at) 
                VALUES (:user_id, :action, :details, NOW())";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":user_id", $user_id);
        $query->bindParam(":action", $action);
        $query->bindParam(":details", $details);
        return $query->execute();
    }

    public function viewAllStudents() {
    $sql = "SELECT id, fullname AS name, grade_section AS grade 
            FROM students 
            WHERE status = 'Active'
            ORDER BY fullname ASC";
    $query = $this->db->connect()->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
public function getStudentEmailById($student_id) {
    $sql = "SELECT email FROM students WHERE id = :id";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(":id", $student_id);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result['email'] ?? null;
}

public function getAllStudentEmails() {
    $sql = "SELECT id, email, fullname FROM students WHERE status = 'Active' AND email IS NOT NULL";
    $query = $this->db->connect()->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

}
?>
