<?php
require_once "../config/database.php";

class Vote {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Cast a vote
    public function castVote($student_id, $position_id, $candidate_id, $user_id = null) {
        // Check if election is in voting phase
        if ($this->currentPhase() !== 'Voting') {
            return ['error' => 'Voting is not active right now.'];
        }

        // Prevent duplicate vote for the same position
        if ($this->hasVoted($student_id, $position_id)) {
            return ['error' => 'You have already voted for this position.'];
        }

        $stmt = $this->conn->prepare("INSERT INTO votes (student_id, position_id, candidate_id) VALUES (?, ?, ?)");
        $success = $stmt->execute([$student_id, $position_id, $candidate_id]);

        if ($success && $user_id) {
            $this->logAction($user_id, "Cast vote", "Student ID: $student_id, Position ID: $position_id, Candidate ID: $candidate_id");
        }

        return $success ? ['success' => true] : ['error' => 'Failed to cast vote.'];
    }

    // Check if student has voted for a position
    public function hasVoted($student_id, $position_id) {
        $stmt = $this->conn->prepare("SELECT id FROM votes WHERE student_id=? AND position_id=?");
        $stmt->execute([$student_id, $position_id]);
        return $stmt->fetch() ? true : false;
    }

    // Fetch votes (optionally by position)
    public function fetchVotes($position_id = null) {
        $sql = "
            SELECT v.id, v.student_id, s.name AS student_name, v.position_id, p.position_name, v.candidate_id, u.full_name AS candidate_name
            FROM votes v
            JOIN students s ON v.student_id = s.id
            JOIN positions p ON v.position_id = p.id
            JOIN users u ON v.candidate_id = u.id
        ";
        if ($position_id) $sql .= " WHERE v.position_id = :position_id";
        $sql .= " ORDER BY p.position_order ASC, s.name ASC";

        $stmt = $this->conn->prepare($sql);
        if ($position_id) $stmt->bindParam(":position_id", $position_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Current election phase helper
    public function currentPhase() {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("SELECT phase FROM election_phases WHERE start_date <= ? AND end_date >= ? LIMIT 1");
        $stmt->execute([$now, $now]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['phase'] : 'Inactive';
    }

    // Audit log
    public function logAction($user_id, $action, $details = null) {
        $stmt = $this->conn->prepare("INSERT INTO audit_log (user_id, action, details) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $action, $details]);
    }
}
?>
