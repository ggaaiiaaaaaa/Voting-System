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

    // -------------------- LOGIN (FIXED: Check Active Status) --------------------
    public function login() {
        // Support login via student_id OR email
        $sql = "SELECT * FROM students WHERE (student_id = :identifier OR email = :identifier)";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":identifier", $this->student_id);
        $query->execute();

        $student = $query->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            // ✅ FIXED: Check if account is active BEFORE password verification
            if ($student['status'] !== 'Active') {
                return false; // Inactive accounts cannot login
            }
            
            // Check password (with auto-migration for plain text)
            $isPasswordValid = false;
            
            // If password starts with $2y$, it's bcrypt hashed
            if (substr($student['password'], 0, 4) === '$2y$') {
                $isPasswordValid = password_verify($this->password, $student['password']);
            } else {
                // Plain text comparison (for existing records)
                $isPasswordValid = ($this->password === $student['password']);
                
                // ✅ AUTO-MIGRATE: If plain text matches, hash it for next time
                if ($isPasswordValid) {
                    $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
                    $updateStmt = $this->db->connect()->prepare("UPDATE students SET password = :password WHERE id = :id");
                    $updateStmt->execute([':password' => $hashedPassword, ':id' => $student['id']]);
                }
            }
            
            if ($isPasswordValid) {
                $this->id = $student['id'];
                $this->fullname = $student['fullname'];
                $this->email = $student['email'];
                $this->grade_section = $student['grade_section'];
                $this->status = $student['status'];
                return true;
            }
        }

        return false;
    }

    // -------------------- CRUD (FIXED: Password Hashing) --------------------
    public function addStudent() {
        // ✅ FIXED: Hash password before storing
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO students (fullname, student_id, email, password, grade_section, status) 
                VALUES (:fullname, :student_id, :email, :password, :grade_section, :status)";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":fullname", $this->fullname);
        $query->bindParam(":student_id", $this->student_id);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":password", $hashedPassword);
        $query->bindParam(":grade_section", $this->grade_section);
        $query->bindParam(":status", $this->status);
        $result = $query->execute();

        if ($result) {
            $this->logAction($_SESSION['user_id'] ?? null, "Added student", "Student ID: {$this->student_id}");
        }

        return $result;
    }

    public function viewStudents() {
        $sql = "SELECT * FROM students ORDER BY status DESC, student_id ASC";
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
        return $this->fetchStudent($id);
    }

    public function editStudent($id) {
        // ✅ FIXED: Only hash password if it's being changed
        $sql = "UPDATE students 
                SET fullname = :fullname, student_id = :student_id, email = :email, 
                    grade_section = :grade_section, status = :status";
        
        // If password is provided and not empty, update it (hashed)
        if (!empty($this->password)) {
            $sql .= ", password = :password";
        }
        
        $sql .= " WHERE id = :id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":fullname", $this->fullname);
        $query->bindParam(":student_id", $this->student_id);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":grade_section", $this->grade_section);
        $query->bindParam(":status", $this->status);
        
        if (!empty($this->password)) {
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            $query->bindParam(":password", $hashedPassword);
        }
        
        $query->bindParam(":id", $id);
        $result = $query->execute();

        if ($result) {
            $this->logAction($_SESSION['user_id'] ?? null, "Edited student", "Student ID: {$this->student_id} - Status: {$this->status}");
        }

        return $result;
    }

    public function deleteStudent($id) {
        $student = $this->fetchStudent($id);
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

    // -------------------- STATUS MANAGEMENT --------------------
    public function toggleStatus($id) {
        $student = $this->fetchStudent($id);
        if (!$student) return false;
        
        $newStatus = ($student['status'] === 'Active') ? 'Inactive' : 'Active';
        
        $sql = "UPDATE students SET status = :status WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":status", $newStatus);
        $query->bindParam(":id", $id);
        $result = $query->execute();
        
        if ($result) {
            $this->logAction($_SESSION['user_id'] ?? null, "Changed student status", "Student ID: {$student['student_id']} - New Status: {$newStatus}");
        }
        
        return $result;
    }

    public function countActiveStudents() {
        $sql = "SELECT COUNT(*) AS total FROM students WHERE status = 'Active'";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
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