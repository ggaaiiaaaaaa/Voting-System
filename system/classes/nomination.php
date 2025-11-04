<?php
require_once __DIR__ . "/../config/database.php";

class Nomination {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function addNomination($studentID, $positionID, $userID = null) {
        $phase = $this->currentPhase();
        if ($phase !== 'Nomination') return ['error' => 'Nominations are not allowed now.'];
        if ($this->isAlreadyNominated($studentID, $positionID)) {
            return ['error' => 'You have already nominated for this position.'];
        }

        $stmt = $this->conn->prepare("INSERT INTO nominations (student_id, position_id, status, created_at) VALUES (?, ?, 'Pending', NOW())");
        $success = $stmt->execute([$studentID, $positionID]);

        if ($success && $userID) {
            $this->logAction($userID, "Added nomination", "Student ID: $studentID, Position ID: $positionID");
        }

        return $success ? ['success' => true] : ['error' => 'Failed to add nomination.'];
    }

    public function isAlreadyNominated($studentID, $positionID) {
        $stmt = $this->conn->prepare("SELECT id FROM nominations WHERE student_id = ? AND position_id = ?");
        $stmt->execute([$studentID, $positionID]);
        return $stmt->fetch() ? true : false;
    }

    public function fetchNominations($positionID = null) {
        $sql = "
            SELECT 
                n.id,
                n.student_id AS studentID,
                s.fullname AS student_name,
                n.position_id AS positionID,
                p.position_name,
                n.status,
                n.created_at
            FROM nominations n
            JOIN students s ON n.student_id = s.id
            JOIN positions p ON n.position_id = p.id
        ";
        if ($positionID) $sql .= " WHERE n.position_id = :positionID";
        $sql .= " ORDER BY p.position_name ASC, s.fullname ASC";

        $stmt = $this->conn->prepare($sql);
        if ($positionID) $stmt->bindParam(":positionID", $positionID);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function viewNominations($positionID = null) {
        return $this->fetchNominations($positionID);
    }

public function fetchNomination($id) {
    $sql = "
        SELECT 
            n.id,
            nominator.fullname AS nominator_name,
            nominee.fullname AS nominee_name,
            p.position_name,
            n.status,
            n.created_at
        FROM nominations n
        INNER JOIN students AS nominator ON n.nominator_id = nominator.id
        INNER JOIN students AS nominee ON n.nominee_id = nominee.id
        INNER JOIN positions AS p ON n.position_id = p.id
        WHERE n.id = :id
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    public function approveNomination($nomID, $userID = null) {
        $stmt = $this->conn->prepare("UPDATE nominations SET status = 'Approved' WHERE id = ?");
        $success = $stmt->execute([$nomID]);
        if ($success && $userID) {
            $this->logAction($userID, "Approved nomination", "Nomination ID: $nomID");
        }
        return $success;
    }

    public function rejectNomination($nomID, $userID = null) {
        $stmt = $this->conn->prepare("UPDATE nominations SET status = 'Rejected' WHERE id = ?");
        $success = $stmt->execute([$nomID]);
        if ($success && $userID) {
            $this->logAction($userID, "Rejected nomination", "Nomination ID: $nomID");
        }
        return $success;
    }

    public function deleteNomination($nomID, $userID = null) {
        $stmt = $this->conn->prepare("DELETE FROM nominations WHERE id = ?");
        $success = $stmt->execute([$nomID]);
        if ($success && $userID) {
            $this->logAction($userID, "Deleted nomination", "Nomination ID: $nomID");
        }
        return $success;
    }

    public function getNominationPhaseStatus() {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("SELECT phase FROM election_phases WHERE start_date <= ? AND end_date >= ? LIMIT 1");
        $stmt->execute([$now, $now]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['phase'] : 'Not Started';
    }

    public function startNominationPhase($userID = null) {
        $stmt = $this->conn->prepare("UPDATE election_phases SET phase = 'Nomination', start_date = NOW() WHERE phase != 'Nomination'");
        $success = $stmt->execute();
        if ($success && $userID) $this->logAction($userID, "Started nomination phase");
        return $success;
    }

    public function pauseNominationPhase($userID = null) {
        $stmt = $this->conn->prepare("UPDATE election_phases SET phase = 'Paused' WHERE phase = 'Nomination'");
        $success = $stmt->execute();
        if ($success && $userID) $this->logAction($userID, "Paused nomination phase");
        return $success;
    }

    public function endNominationPhase($userID = null) {
        $stmt = $this->conn->prepare("UPDATE election_phases SET phase = 'Closed' WHERE phase IN ('Nomination', 'Paused')");
        $success = $stmt->execute();
        if ($success && $userID) $this->logAction($userID, "Ended nomination phase");
        return $success;
    }

    public function currentPhase() {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("SELECT phase FROM election_phases WHERE start_date <= ? AND end_date >= ? LIMIT 1");
        $stmt->execute([$now, $now]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['phase'] : 'Inactive';
    }

    public function logAction($userID, $action, $details = null) {
        $stmt = $this->conn->prepare("INSERT INTO audit_log (user_id, action, details) VALUES (?, ?, ?)");
        return $stmt->execute([$userID, $action, $details]);
    }
public function viewNominationsWithDetails() {
    $sql = "
        SELECT 
            n.id,
            nominator.fullname AS nominator_name,
            nominee.fullname AS nominee_name,
            p.position_name,
            n.status,
            n.created_at
        FROM nominations n
        INNER JOIN students AS nominator ON n.nominator_id = nominator.id
        INNER JOIN students AS nominee ON n.nominee_id = nominee.id
        INNER JOIN positions AS p ON n.position_id = p.id
        ORDER BY n.created_at DESC
    ";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}
?>
