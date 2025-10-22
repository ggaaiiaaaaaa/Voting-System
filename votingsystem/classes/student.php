<?php
require_once __DIR__ . "/../config/database.php";

class Student {
    public $id;
    public $name;
    public $student_id;
    public $password;

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function login() {
        $sql = "SELECT * FROM students WHERE student_id = :student_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":student_id", $this->student_id);
        $query->execute();

        $student = $query->fetch(PDO::FETCH_ASSOC);

        if ($student && $this->password === $student['password']) {
            $this->id = $student['id'];
            $this->name = $student['name'];
            return true;
        }

        return false;
    }

    public function addStudent() {
        $sql = "INSERT INTO students (name, student_id, password) VALUES (:name, :student_id, :password)";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":student_id", $this->student_id);
        $query->bindParam(":password", $this->password);
        return $query->execute();
    }

    public function viewStudents() {
        $sql = "SELECT * FROM students ORDER BY name ASC";
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

    public function editStudent($id) {
        $sql = "UPDATE students SET name = :name, student_id = :student_id WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":student_id", $this->student_id);
        $query->bindParam(":id", $id);
        return $query->execute();
    }

    public function deleteStudent($id) {
        $sql = "DELETE FROM students WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        return $query->execute();
    }

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
}
?>