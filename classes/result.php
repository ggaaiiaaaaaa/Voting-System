<?php
require_once __DIR__ . "/election.php";
require_once __DIR__ . "/vote.php";

class Result {
    private $election;
    private $vote;

    public function __construct() {
        $this->election = new Election();
        $this->vote = new Vote();
    }

    // Calculate results per position
    public function calculateResults() {
        $votes = $this->vote->fetchVotes();
        $results = [];

        foreach ($votes as $v) {
            $pos_id = $v['position_id'];
            $cand_id = $v['candidate_id'];

            if (!isset($results[$pos_id][$cand_id])) {
                $results[$pos_id][$cand_id] = 0;
            }
            $results[$pos_id][$cand_id]++;
        }

        // Store results in database
        foreach ($results as $pos_id => $candidates) {
            $max_votes = max($candidates);
            foreach ($candidates as $cand_id => $vote_count) {
                $status = ($vote_count === $max_votes) ? 'Winner' : 'Not Winner';
                $this->election->conn->prepare("
                    INSERT INTO results (position_id, candidate_id, votes, status)
                    VALUES (:pos_id, :cand_id, :votes, :status)
                    ON DUPLICATE KEY UPDATE votes = :votes, status = :status
                ")->execute([
                    ':pos_id' => $pos_id,
                    ':cand_id' => $cand_id,
                    ':votes' => $vote_count,
                    ':status' => $status
                ]);
            }
        }

        return true;
    }

    // Fetch results for display
    public function fetchResults() {
        return $this->election->fetchResults();
    }
}
?>
