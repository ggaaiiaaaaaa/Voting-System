<?php

require_once __DIR__ . "/../config/database.php";

class Position {
    public $id = "";
    public $name = "";
    public $position_order = "";

    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addPosition() {
        $sql = "INSERT INTO positions (name, position_order) 
                VALUE (:name, :position_order)";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":position_order", $this->position_order);

        return $query->execute();
    }

    public function viewPosition($search = "") {
        $sql = "SELECT * FROM positions WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND name LIKE CONCAT('%', :search, '%')";
        }

        $sql .= " ORDER BY position_order ASC";

        $query = $this->db->connect()->prepare($sql);

        if (!empty($search)) {
            $query->bindParam(":search", $search);
        }

        if ($query->execute()) {
            return $query->fetchAll();
        } else {
            return null;
        }
    }

    public function isPositionExist($name, $order, $id = "") {
        $sql = "SELECT COUNT(*) as total_positions 
                FROM positions 
                WHERE (name = :name OR position_order = :position_order) 
                AND id <> :id";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":name", $name);
        $query->bindParam(":position_order", $order);
        $query->bindParam(":id", $id);

        if ($query->execute()) {
            $record = $query->fetch();
            return $record["total_positions"] > 0;
        } else {
            return false;
        }
    }

    public function isNameExist($name, $id = "") {
        $sql = "SELECT COUNT(*) as total FROM positions
                 WHERE name = :name AND id <> :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":name", $name);
        $query->bindParam(":id", $id);
        $query->execute();
        $record = $query->fetch();

        return $record["total"] > 0;
    }

    public function isPositionOrderExist($order, $id="") {
        $sql = "SELECT COUNT(*) as total FROM positions
                 WHERE position_order = :position_order AND id <> :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":position_order", $order);
        $query->bindParam(":id", $id);
        $query->execute();
        $record = $query->fetch();

        return $record["total"] > 0;
    }

    public function fetchPosition($positionId) {
        $sql = "SELECT * FROM positions WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $positionId);

        if ($query->execute()) {
            return $query->fetch();
        } else {
            return null;
        }
    }

    public function editPosition($positionId) {
        $sql = "UPDATE positions 
                SET name = :name, position_order = :position_order 
                WHERE id = :id";

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":position_order", $this->position_order);
        $query->bindParam(":id", $positionId);

        return $query->execute();
    }

    public function deletePosition($positionId) {
        $sql = "DELETE FROM positions WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $positionId);

        return $query->execute();
    }

    public function numberToWords($num) {
        $words = [
            1 => "First", 2 => "Second", 3 => "Third", 4 => "Fourth", 5 => "Fifth",
            6 => "Sixth", 7 => "Seventh", 8 => "Eighth", 9 => "Ninth", 10 => "Tenth"
        ];
        return $words[$num] ?? $num;
    }

    public function getMaxOrder() {
        $sql = "SELECT MAX(position_order) AS max_order FROM positions";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        $record = $query->fetch();
        return ($record["max_order"] === null) ? 1 : $record["max_order"] + 1;
    }

    public function overridePositionOrder($new_order) {
        $sql = "UPDATE positions SET position_order = position_order + 1 WHERE position_order >= :new_order";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":new_order", $new_order);
        return $query->execute();
    }
}