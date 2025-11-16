<?php
require_once __DIR__ . "/../config/database.php";

class Position {
    public $id;
    public $position_name;
    public $position_order;
    public $max_nominees;
    public $status;

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // -------------------- ADD POSITION --------------------
    public function addPosition() {
        try {
            $this->conn->beginTransaction();

            if (empty($this->position_order)) {
                $this->position_order = $this->getMaxOrder();
            } else {
                // Shift existing positions to make space for this order
                $this->makeRoomForOrder($this->position_order);
            }

            $sql = "INSERT INTO positions (position_name, position_order, max_nominees, status)
                    VALUES (:position_name, :position_order, :max_nominees, :status)";
            $stmt = $this->conn->prepare($sql);

            $status = 'Active';

            $result = $stmt->execute([
                ':position_name' => $this->position_name,
                ':position_order' => $this->position_order,
                ':max_nominees' => $this->max_nominees,
                ':status' => $status
            ]);

            if ($result) {
                $this->logAction($_SESSION['user_id'] ?? null, "Added position", "Position: {$this->position_name}");
            }

            $this->conn->commit();
            return $result;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // -------------------- EDIT POSITION --------------------
    public function editPosition($id) {
        try {
            $this->conn->beginTransaction();

            $old = $this->fetchPosition($id);

            if ($old && $old['position_order'] != $this->position_order) {
                $this->overrideOrder($old['position_order'], $this->position_order, $id);
            }

            $sql = "UPDATE positions 
                    SET position_name = :position_name, 
                        position_order = :position_order, 
                        max_nominees = :max_nominees, 
                        status = :status
                    WHERE id = :id";

            $stmt = $this->conn->prepare($sql);

            $result = $stmt->execute([
                ':position_name' => $this->position_name,
                ':position_order' => $this->position_order,
                ':max_nominees' => $this->max_nominees,
                ':status' => $this->status ?? 'Active',
                ':id' => $id
            ]);

            if ($result) {
                $this->logAction($_SESSION['user_id'] ?? null, "Edited position", "Position: {$this->position_name}");
            }

            $this->conn->commit();
            return $result;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // -------------------- DELETE POSITION --------------------
    public function deletePosition($id) {
        $position = $this->fetchPosition($id);

        $sql = "DELETE FROM positions WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([':id' => $id]);

        if ($result && $position) {
            $this->logAction($_SESSION['user_id'] ?? null, "Deleted position", "Position: {$position['position_name']}");
            $this->normalizeOrder();
        }

        return $result;
    }

    // -------------------- FETCH SINGLE POSITION --------------------
    public function fetchPosition($id) {
        $sql = "SELECT * FROM positions WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -------------------- GET POSITION BY ID (For compatibility) --------------------
    public function getPositionById($id) {
        return $this->fetchPosition($id);
    }

    // -------------------- VIEW POSITIONS --------------------
public function viewPositions() {
    $sql = "SELECT * FROM positions ORDER BY position_order ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result ?: []; // return empty array if no rows
}


    // -------------------- DUPLICATE CHECKS --------------------
    public function isNameExist($position_name, $id = "") {
        $sql = "SELECT COUNT(*) as total FROM positions
                WHERE position_name = :position_name AND id <> :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':position_name' => $position_name,
            ':id' => $id
        ]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record["total"] > 0;
    }

    public function isPositionOrderExist($order, $id = "") {
        $sql = "SELECT COUNT(*) as total FROM positions
                WHERE position_order = :position_order AND id <> :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':position_order' => $order,
            ':id' => $id
        ]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record["total"] > 0;
    }

    // -------------------- ORDER HANDLING --------------------
    public function getMaxOrder() {
        $sql = "SELECT MAX(position_order) AS max_order FROM positions";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($record["max_order"] === null) ? 1 : $record["max_order"] + 1;
    }

    private function makeRoomForOrder($new_order) {
        $sql = "UPDATE positions 
                SET position_order = position_order + 1 
                WHERE position_order >= :new_order";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":new_order", $new_order, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function overrideOrder($old_order, $new_order, $current_id) {
        if ($new_order == $old_order) return;

        if ($new_order < $old_order) {
            // Moving up: shift others down
            $sql = "UPDATE positions 
                    SET position_order = position_order + 1 
                    WHERE position_order >= :new_order 
                      AND position_order < :old_order 
                      AND id <> :id";
        } else {
            // Moving down: shift others up
            $sql = "UPDATE positions 
                    SET position_order = position_order - 1 
                    WHERE position_order <= :new_order 
                      AND position_order > :old_order 
                      AND id <> :id";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':new_order' => $new_order,
            ':old_order' => $old_order,
            ':id' => $current_id
        ]);
    }

    private function normalizeOrder() {
        $positions = $this->viewPositions();
        $order = 1;
        foreach ($positions as $pos) {
            $sql = "UPDATE positions SET position_order = :order WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':order' => $order++,
                ':id' => $pos['id']
            ]);
        }
    }

    // -------------------- SHIFT POSITIONS UP AFTER DELETION --------------------
    public function shiftPositionsUp($deleted_order) {
        $sql = "UPDATE positions 
                SET position_order = position_order - 1 
                WHERE position_order > :deleted_order";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':deleted_order' => $deleted_order]);
    }

    // -------------------- SHIFT POSITIONS DOWN (when inserting) --------------------
    public function shiftPositionsDown($new_order) {
        $sql = "UPDATE positions 
                SET position_order = position_order + 1 
                WHERE position_order >= :new_order";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':new_order' => $new_order]);
    }

    // -------------------- COUNT POSITIONS --------------------
    public function countPositions() {
        $sql = "SELECT COUNT(*) AS total FROM positions";
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

    // -------------------- CONVERT ORDER TO WORDS --------------------
    public function numberToWords($num) {
        $words = [
            1 => "First", 2 => "Second", 3 => "Third", 4 => "Fourth", 5 => "Fifth",
            6 => "Sixth", 7 => "Seventh", 8 => "Eighth", 9 => "Ninth", 10 => "Tenth"
        ];
        return $words[$num] ?? $num;
    }
}
?>
