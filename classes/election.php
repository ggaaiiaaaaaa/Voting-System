<?php

require_once __DIR__ . "/../config/database.php";

class Election {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

public function getAdminControlledStatus() {
    $schedule = $this->fetchSchedule();
    $current = $this->fetchCurrentElection(); // admin-controlled status

    if (!$schedule) return 'No Election';

    $now = date('Y-m-d H:i:s');

    // If admin has set current election status
    if ($current) {
        return $current['status']; // Ongoing, Paused, Ended
    }

    // No admin control yet, fallback to schedule dates
    if ($now < $schedule['start_date']) {
        // Fully reset previous election for a new cycle
        $this->resetElectionForNewCycle();
        return 'Upcoming';
    } elseif ($now >= $schedule['start_date'] && $now <= $schedule['end_date']) {
        return 'Ongoing';
    } else {
        return 'Ended';
    }
}



    // -------------------- FETCH CURRENT ELECTION SCHEDULE --------------------
    public function fetchSchedule() {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, name, start_date, end_date, status
                FROM elections
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("fetchSchedule Error: " . $e->getMessage());
            return null;
        }
    }

    public function addElection($start_date, $end_date) {
        try {
            // Only one election allowed at a time
            $stmt = $this->conn->query("SELECT COUNT(*) FROM elections");
            if ($stmt->fetchColumn() > 0) {
                return false;
            }

            $stmt = $this->conn->prepare("
                INSERT INTO elections (name, start_date, end_date, status)
                VALUES ('Official Election', ?, ?, 'Upcoming')
            ");
            return $stmt->execute([$start_date, $end_date]);
        } catch (PDOException $e) {
            error_log("addElection Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateElection($id, $start_date, $end_date) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE elections
                SET start_date = ?, end_date = ?, status = 'Upcoming'
                WHERE id = ?
            ");
            return $stmt->execute([$start_date, $end_date, $id]);
        } catch (PDOException $e) {
            error_log("updateElection Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteElection($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM elections WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("deleteElection Error: " . $e->getMessage());
            return false;
        }
    }

    // -------------------- ELECTION CONTROLS --------------------
    public function startElection() {
        $schedule = $this->fetchSchedule();
        if (!$schedule || $schedule['status'] === 'Ongoing' || $schedule['status'] === 'Ended') {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE elections SET status='Ongoing' WHERE id=?");
        $stmt->execute([$schedule['id']]);
        $this->logAction($_SESSION['user_id'], 'admin', 'Start Election', "Election ID {$schedule['id']} started.");
        return true;
    }

    public function pauseElection() {
        $schedule = $this->fetchSchedule();
        if (!$schedule || $schedule['status'] !== 'Ongoing') return false;

        $stmt = $this->conn->prepare("UPDATE elections SET status='Paused' WHERE id=?");
        $stmt->execute([$schedule['id']]);
        $this->logAction($_SESSION['user_id'], 'admin', 'Pause Election', "Election ID {$schedule['id']} paused.");
        return true;
    }

    public function endElection() {
        $schedule = $this->fetchSchedule();
        if (!$schedule || $schedule['status'] === 'Ended') return false;

        $stmt = $this->conn->prepare("UPDATE elections SET status='Ended' WHERE id=?");
        $stmt->execute([$schedule['id']]);
        $this->logAction($_SESSION['user_id'], 'admin', 'End Election', "Election ID {$schedule['id']} ended.");

        $this->calculateResults();
        return true;
    }

    // -------------------- NOMINATIONS --------------------
    public function getStudentNominations($student_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    n.id AS nomination_id,
                    n.position_id,
                    p.position_name,
                    s.fullname AS nominee_name,
                    n.status
                FROM nominations n
                INNER JOIN positions p ON n.position_id = p.id
                INNER JOIN students s ON n.nominee_id = s.id
                WHERE n.nominator_id = ?
                ORDER BY p.position_name ASC
            ");
            $stmt->execute([$student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getStudentNominations Error: " . $e->getMessage());
            return [];
        }
    }

    public function countStudentNominations($student_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM nominations WHERE nominator_id = ?");
        $stmt->execute([$student_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function submitNomination($nominator_id, $nominee_id, $position_id) {
        try {
            $checkSql = "SELECT id FROM nominations WHERE nominator_id = ? AND position_id = ?";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([$nominator_id, $position_id]);

            if ($checkStmt->fetch()) {
                return ['error' => 'You already nominated someone for this position.'];
            }

            $sql = "INSERT INTO nominations (nominator_id, nominee_id, position_id, status, created_at)
                     VALUES (?, ?, ?, 'Pending', NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$nominator_id, $nominee_id, $position_id]);

            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Nomination error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getApprovedNominationsByPosition($position_id) {
        $sql = "
            SELECT DISTINCT
                n.id AS nomination_id,
                n.nominee_id AS candidate_id, 
                s.fullname AS candidate_name
            FROM nominations n
            INNER JOIN students s ON n.nominee_id = s.id
            WHERE n.status = 'Approved' AND n.position_id = :position_id
            ORDER BY s.fullname ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':position_id', $position_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------- VOTING --------------------
public function hasStudentVoted($student_id) {
    try {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT position_id) AS total 
            FROM votes 
            WHERE voter_id = ?
        ");
        $stmt->execute([$student_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['total'] ?? 0) > 0;
    } catch (PDOException $e) {
        error_log("hasStudentVoted Error: " . $e->getMessage());
        return false;
    }
}

    public function submitVote($student_id, $votes) {
        if (!is_array($votes) || empty($votes)) {
            return ['error' => 'Invalid or empty vote submission.'];
        }

        try {
            if ($this->hasStudentVoted($student_id)) {
                return ['error' => 'You have already voted.'];
            }

            $this->conn->beginTransaction();

            foreach ($votes as $position_id => $nomination_id) {
                $check = $this->conn->prepare("
                    SELECT id FROM nominations 
                    WHERE id = ? AND position_id = ? AND status = 'Approved'
                ");
                $check->execute([$nomination_id, $position_id]);

                if (!$check->fetch()) {
                    $this->conn->rollBack();
                    return ['error' => 'Invalid or unapproved candidate for one of the positions.'];
                }

                $insert = $this->conn->prepare("
                    INSERT INTO votes (voter_id, nomination_id, position_id, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $insert->execute([$student_id, $nomination_id, $position_id]);
            }

            $this->conn->commit();

            $this->logAction($student_id, 'student', 'Vote Submitted', 'Student completed all votes');
            $this->updateVoterTurnout();

            return ['success' => true];

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log("Vote Error: " . $e->getMessage());
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function updateVoterTurnout() {
        $stats = $this->getVoterStats();
        $sql = "UPDATE election_summary SET total_students=?, total_voted=?, turnout=? WHERE id=1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$stats['total_students'], $stats['voted'], $stats['turnout']]);
    }

public function getVoterStats() {
    try {
        $totalStudentsQuery = $this->conn->query("SELECT COUNT(*) AS total FROM students WHERE status='Active'");
        $totalStudents = $totalStudentsQuery->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $votedQuery = $this->conn->query("SELECT COUNT(DISTINCT voter_id) AS voted FROM votes");
        $votedCount = $votedQuery->fetch(PDO::FETCH_ASSOC)['voted'] ?? 0;

        $turnout = ($totalStudents > 0) ? ($votedCount / $totalStudents) * 100 : 0;

        return [
            'total_students' => (int)$totalStudents,
            'voted' => (int)$votedCount,
            'turnout' => round($turnout, 2)
        ];
    } catch (PDOException $e) {
        error_log("getVoterStats Error: " . $e->getMessage());
        return ['total_students'=>0,'voted'=>0,'turnout'=>0];
    }
}

    public function countTotalVotes() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_votes FROM votes");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total_votes'] ?? 0;
        } catch (PDOException $e) {
            error_log("Count total votes error: " . $e->getMessage());
            return 0;
        }
    }

public function countVoters() {
    try {
        $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT voter_id) AS total_voters FROM votes");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_voters'] ?? 0;
    } catch (PDOException $e) {
        error_log("Count voters error: " . $e->getMessage());
        return 0;
    }
}


    // -------------------- NOMINATION SUMMARY --------------------
    public function getNominationSummary() {
        try {
            $query = "
                SELECT 
                    p.id AS position_id,
                    p.position_name,
                    COUNT(CASE WHEN n.status = 'Approved' THEN 1 END) AS total_nominees,
                    COUNT(CASE WHEN n.status = 'Pending' THEN 1 END) AS pending
                FROM positions p
                LEFT JOIN nominations n ON p.id = n.position_id
                GROUP BY p.id, p.position_name
                ORDER BY p.position_name ASC
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getNominationSummary: " . $e->getMessage());
            return [];
        }
    }

    // -------------------- RESULTS --------------------
 public function getLeadingCandidates($onlyTop = false) {
    try {
        $sql = "
            SELECT 
                p.id AS position_id,
                p.position_name,
                s.fullname AS candidate_name,
                COUNT(v.id) AS total_votes
            FROM votes v
            INNER JOIN nominations n ON v.nomination_id = n.id
            INNER JOIN positions p ON v.position_id = p.id
            INNER JOIN students s ON n.nominee_id = s.id
            GROUP BY p.id, n.nominee_id
            ORDER BY p.id, total_votes DESC
        ";
        $stmt = $this->conn->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($onlyTop) {
            $top = [];
            foreach ($results as $r) {
                if (!isset($top[$r['position_id']])) {
                    $top[$r['position_id']] = $r;
                }
            }
            return array_values($top);
        }
        return $results;

    } catch (PDOException $e) {
        error_log("getLeadingCandidates Error: " . $e->getMessage());
        return [];
    }
}

    public function calculateResults() {
        $stmt = $this->conn->prepare("
            SELECT n.position_id, n.nominee_id AS candidate_id, COUNT(v.id) AS votes
            FROM nominations n
            LEFT JOIN votes v ON v.nomination_id = n.id
            WHERE LOWER(n.status)='approved'
            GROUP BY n.id, n.position_id, n.nominee_id
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->conn->exec("TRUNCATE TABLE results");

        foreach ($results as $r) {
            $stmtInsert = $this->conn->prepare("
                INSERT INTO results (position_id, candidate_id, votes)
                VALUES (?, ?, ?)
            ");
            $stmtInsert->execute([$r['position_id'], $r['candidate_id'], $r['votes']]);
        }

        $positions = $this->conn->prepare("SELECT id FROM positions");
        $positions->execute();
        foreach ($positions->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $stmtUpdate = $this->conn->prepare("
                UPDATE results r
                JOIN (
                    SELECT position_id, MAX(votes) AS max_votes FROM results WHERE position_id=? GROUP BY position_id
                ) m ON r.position_id=m.position_id
                SET r.status=CASE WHEN r.votes=m.max_votes THEN 'Winner' ELSE 'Not Winner' END
                WHERE r.position_id=?
            ");
            $stmtUpdate->execute([$p['id'], $p['id']]);
        }
        return true;
    }

    public function fetchResults() {
        $stmt = $this->conn->prepare("
            SELECT p.position_name, CONCAT(s.fullname) AS candidate_name,
                   r.votes, r.status
            FROM results r
            JOIN positions p ON r.position_id=p.id
            JOIN students s ON r.candidate_id=s.id
            ORDER BY p.id, r.votes DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------- AUDIT LOG --------------------
    public function logAction($user_id, $user_type, $action, $details=null) {
        $stmt = $this->conn->prepare("
            INSERT INTO audit_log (user_id, user_type, action, details, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$user_id, $user_type, $action, $details]);
    }

    public function fetchAuditLogs() {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, user_id, user_type, action, details, created_at AS timestamp
                FROM audit_log 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching audit logs: ' . $e->getMessage());
            return [];
        }
    }

    public function fetchCurrentElection() {
        $stmt = $this->conn->prepare("SELECT * FROM elections ORDER BY start_date DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function resetElectionForNewCycle() {
    try {
        // 1. Delete previous nominations (completely reset)
        $stmt = $this->conn->prepare("DELETE FROM nominations");
        $stmt->execute();

        // 2. Reset results table
        $stmt2 = $this->conn->prepare("UPDATE results SET votes = 0, status = 'Pending'");
        $stmt2->execute();

        // 3. Reset student voting status
        $stmt3 = $this->conn->prepare("UPDATE students SET has_voted = 0");
        $stmt3->execute();

        // 4. Optional: clear votes table if it stores detailed vote records
        $stmt4 = $this->conn->prepare("DELETE FROM votes");
        $stmt4->execute();

        return true;
    } catch (PDOException $e) {
        error_log("resetElectionForNewCycle Error: " . $e->getMessage());
        return false;
    }
}

public function getVoteDistribution() {
    $sql = "SELECT p.position_name, COUNT(v.id) as total_votes 
            FROM positions p 
            LEFT JOIN votes v ON p.id = v.position_id 
            GROUP BY p.id";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}

?>
