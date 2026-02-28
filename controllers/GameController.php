<?php
// controllers/GameController.php

class GameController {
    private $db;
    private $roomModel;
    private $playerModel;

    // Phase durations in seconds
    const WORD_REVEAL_DURATION = 15;  // 15s to read role
    const CLUE_DURATION        = 90;  // 90s for clue submissions
    const DECISION_DURATION    = 60;  // 60s for voting
    const REVEAL_DURATION      = 12;  // 12s on reveal screen, then auto-return to lobby

    public function __construct() {
        $this->db = Database::getInstance();
        $this->roomModel = new Room();
        $this->playerModel = new Player();
    }

    public function startRound($roomId) {
        $room = $this->roomModel->getById($roomId);
        if (!$room) return false;

        $players = $this->playerModel->getPlayersInRoom($roomId);
        $alivePlayers = array_values(array_filter($players, function($p) { return $p['is_alive'] == 1; }));

        if (count($alivePlayers) < 3) return false;  // Need at least 3 to play

        // Select Imposter randomly from alive players
        $imposter = $alivePlayers[array_rand($alivePlayers)];

        // Select Word (exclude the word from the immediately preceding round in this room)
        $previousWordId = $this->db->query("SELECT word_id FROM rounds WHERE room_id = $roomId ORDER BY created_at DESC LIMIT 1")->fetchColumn();
        
        $sql = "SELECT id FROM words WHERE category_id = ? AND difficulty = ? AND status = 'active'";
        $params = [$room['category_id'], $room['difficulty']];
        
        if ($previousWordId) {
            $sql .= " AND id != ?";
            $params[] = $previousWordId;
        }
        
        $sql .= " ORDER BY RAND() LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $word = $stmt->fetch();
        
        // Fallback: if no other word exists, allow the repeat
        if (!$word && $previousWordId) {
            $stmt = $this->db->prepare("SELECT id FROM words WHERE category_id = ? AND difficulty = ? AND status = 'active' ORDER BY RAND() LIMIT 1");
            $stmt->execute([$room['category_id'], $room['difficulty']]);
            $word = $stmt->fetch();
        }

        if (!$word) return false;

        // Mark any old rounds as completed
        $this->db->prepare("UPDATE rounds SET status = 'completed' WHERE room_id = ? AND status = 'active'")->execute([$roomId]);

        // Create Round
        $stmt = $this->db->prepare("INSERT INTO rounds (room_id, word_id, imposter_id) VALUES (?, ?, ?)");
        $stmt->execute([$roomId, $word['id'], $imposter['id']]);
        $roundId = $this->db->lastInsertId();

        $this->roomModel->updateStatus($roomId, 'word_reveal');
        return $roundId;
    }

    public function submitClue($roundId, $playerId, $clueText) {
        $stmt = $this->db->prepare("INSERT INTO clues (round_id, player_id, clue_text) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE clue_text = VALUES(clue_text)");
        return $stmt->execute([$roundId, $playerId, clean($clueText)]);
    }

    /**
     * Imposter submits a guess for the secret word.
     * Returns: ['correct' => bool, 'word' => string]
     */
    public function submitImposterGuess($roundId, $playerId, $guess, $roomId) {
        $stmt = $this->db->prepare("SELECT w.word FROM rounds r JOIN words w ON r.word_id = w.id WHERE r.id = ?");
        $stmt->execute([$roundId]);
        $row = $stmt->fetch();
        if (!$row) return ['correct' => false, 'word' => ''];

        // Fuzzy Matching: strip spaces, punctuation, and case
        $sanitize = function($s) { return preg_replace('/[^a-z0-9]/', '', strtolower(trim($s))); };
        $isCorrect = ($sanitize($guess) === $sanitize($row['word']));

        // Record the guess
        $stmt = $this->db->prepare("INSERT INTO imposter_guesses (round_id, player_id, guess, is_correct) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE guess = VALUES(guess), is_correct = VALUES(is_correct)");
        $stmt->execute([$round['id'], $playerId, clean($guess), $isCorrect ? 1 : 0]);

        $fakeRound = ['id' => $roundId, 'imposter_id' => $playerId];
        if ($isCorrect) {
            // Imposter correctly guessed the word → Imposter wins
            $this->endGame($roomId, $fakeRound, 'imposter', null, 'imposter_guessed');
        } else {
            // Imposter failed → Crew wins (High Stakes)
            $this->endGame($roomId, $fakeRound, 'crew', $playerId, 'imposter_failed_guess');
        }

        return ['correct' => $isCorrect, 'word' => $row['word']];
    }

    /**
     * Submit an eliminate vote (targeting a specific player)
     */
    public function submitVote($roundId, $voterId, $votedId) {
        $stmt = $this->db->prepare("INSERT INTO votes (round_id, voter_id, voted_player_id, vote_type) VALUES (?, ?, ?, 'eliminate') ON DUPLICATE KEY UPDATE voted_player_id = VALUES(voted_player_id), vote_type = 'eliminate'");
        return $stmt->execute([$roundId, $voterId, $votedId]);
    }

    /**
     * Submit a skip vote (skip elimination, do another round of clues)
     */
    public function submitSkipVote($roundId, $voterId) {
        $stmt = $this->db->prepare("INSERT INTO votes (round_id, voter_id, voted_player_id, vote_type) VALUES (?, ?, NULL, 'skip') ON DUPLICATE KEY UPDATE voted_player_id = NULL, vote_type = 'skip'");
        return $stmt->execute([$roundId, $voterId]);
    }

    public function getClues($roundId) {
        $stmt = $this->db->prepare("
            SELECT c.clue_text, p.nickname, p.avatar 
            FROM clues c JOIN players p ON c.player_id = p.id 
            WHERE c.round_id = ? ORDER BY c.created_at ASC
        ");
        $stmt->execute([$roundId]);
        return $stmt->fetchAll();
    }

    public function getVoteTally($roundId) {
        $stmt = $this->db->prepare("
            SELECT voted_player_id, COUNT(*) as votes 
            FROM votes WHERE round_id = ? AND vote_type = 'eliminate' AND voted_player_id IS NOT NULL
            GROUP BY voted_player_id ORDER BY votes DESC
        ");
        $stmt->execute([$roundId]);
        return $stmt->fetchAll();
    }

    public function getVoteSummary($roundId) {
        // Returns skip_count, eliminate_count, total
        $stmt = $this->db->prepare("
            SELECT vote_type, COUNT(*) as cnt 
            FROM votes WHERE round_id = ? GROUP BY vote_type
        ");
        $stmt->execute([$roundId]);
        $rows = $stmt->fetchAll();
        $summary = ['skip' => 0, 'eliminate' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $summary[$r['vote_type']] = (int)$r['cnt'];
            $summary['total'] += (int)$r['cnt'];
        }
        return $summary;
    }

    public function getRoundState($roomId) {
        $stmt = $this->db->prepare("SELECT r.*, w.word FROM rounds r JOIN words w ON r.word_id = w.id WHERE r.room_id = ? AND r.status = 'active' ORDER BY r.created_at DESC LIMIT 1");
        $stmt->execute([$roomId]);
        return $stmt->fetch();
    }

    public function getLastRound($roomId) {
        $stmt = $this->db->prepare("SELECT r.*, w.word FROM rounds r JOIN words w ON r.word_id = w.id WHERE r.room_id = ? ORDER BY r.created_at DESC LIMIT 1");
        $stmt->execute([$roomId]);
        return $stmt->fetch();
    }

    public function checkPhaseTransition($roomId) {
        $room = $this->roomModel->getById($roomId);
        $now        = time();
        $phaseStart = (int)$room['phase_start_time'];

        // Check reveal BEFORE loading round (round is 'completed' at this point)
        if ($room['status'] === 'reveal') {
            if ($now - $phaseStart >= self::REVEAL_DURATION) {
                $this->resetForNewGame($roomId);
            }
            return;
        }

        $round = $this->getRoundState($roomId);
        if (!$round) return;

        $players = $this->playerModel->getPlayersInRoom($roomId);
        
        // Only count players as 'active' for transitions if they polled in the last 30s
        $activeAlivePlayers = array_filter($players, function($p) { 
            return $p['is_alive'] == 1 && (time() - strtotime($p['last_active_at'])) < 30; 
        });
        $aliveCount = count($activeAlivePlayers);

        switch ($room['status']) {
            case 'word_reveal':
                if ($now - $phaseStart >= self::WORD_REVEAL_DURATION) {
                    $this->roomModel->updateStatus($roomId, 'clue');
                }
                break;

            case 'clue':
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM clues WHERE round_id = ?");
                $stmt->execute([$round['id']]);
                $clueCount = (int)$stmt->fetchColumn();

                // Only auto-transition if we have at least one active player and everyone submitted.
                // Otherwise, wait for the timeout.
                if (($aliveCount > 0 && $clueCount >= $aliveCount) || ($now - $phaseStart >= self::CLUE_DURATION)) {
                    $this->roomModel->updateStatus($roomId, 'voting');
                }
                break;

            case 'voting':
                $summary = $this->getVoteSummary($round['id']);

                // Only auto-transition if we have at least one active player and everyone voted.
                // Otherwise, wait for the timeout.
                if (($aliveCount > 0 && $summary['total'] >= $aliveCount) || ($now - $phaseStart >= self::DECISION_DURATION)) {
                    // Atomic mutex: only one concurrent poll can resolve voting
                    $stmt = $this->db->prepare("UPDATE rooms SET status = 'resolving' WHERE id = ? AND status = 'voting'");
                    $stmt->execute([$roomId]);
                    if ($stmt->rowCount() > 0) {
                        $this->resolveVoting($roomId, $round, $summary, $aliveCount);
                    }
                }
                break;
        }
    }

    private function resolveVoting($roomId, $round, $summary, $aliveCount) {
        $skipCount     = $summary['skip'];
        $eliminateVotes = $summary['eliminate'];
        $totalVotes    = $summary['total'];

        // If no votes at all — imposter wins
        if ($totalVotes === 0) {
            $this->endGame($roomId, $round, 'imposter', null, 'no_votes');
            return;
        }

        // Majority voted SKIP → start new round of clues (keep same imposter & word for fairness,
        // but start fresh clue collection on this round)
        if ($skipCount > $eliminateVotes) {
            $this->skipToNewClueRound($roomId, $round);
            return;
        }

        // Majority voted ELIMINATE (or tie → eliminate)
        $tally = $this->getVoteTally($round['id']);

        if (empty($tally)) {
            // Only skip votes, no elimination targets
            $this->skipToNewClueRound($roomId, $round);
            return;
        }

        // Tie between two players → skip (no elimination)
        if (count($tally) > 1 && $tally[0]['votes'] == $tally[1]['votes']) {
            $this->skipToNewClueRound($roomId, $round);
            return;
        }

        $eliminatedId = $tally[0]['voted_player_id'];
        $this->playerModel->kill($eliminatedId);

        $isImposter = ($eliminatedId == $round['imposter_id']);
        if ($isImposter) {
            $this->endGame($roomId, $round, 'crew', $eliminatedId, 'voted_out');
        } else {
            $this->endGame($roomId, $round, 'imposter', $eliminatedId, 'wrong_elimination');
        }
    }

    /**
     * Skip: clear votes, clear clues, stay in same round but go back to word_reveal
     * (15s reveal so players remember their roles, then clue phase again)
     */
    private function skipToNewClueRound($roomId, $round) {
        // Clear only votes so players can cast new ones, but keep clues!
        $this->db->prepare("DELETE FROM votes WHERE round_id = ?")->execute([$round['id']]);

        // Go back to clue phase directly (they already know their roles)
        $this->roomModel->updateStatus($roomId, 'clue');
    }

    private function endGame($roomId, $round, $winner, $eliminatedId, $reason = '') {
        $stmt = $this->db->prepare("UPDATE rounds SET status = 'completed', winner = ? WHERE id = ?");
        $stmt->execute([$winner, $round['id']]);

        $stmt = $this->db->prepare("UPDATE rooms SET winner = ?, eliminated_player_id = ?, end_reason = ? WHERE id = ?");
        $stmt->execute([$winner, $eliminatedId, $reason, $roomId]);

        $this->roomModel->updateStatus($roomId, 'reveal');
    }
    /**
     * Reset room back to lobby (waiting) for a new game.
     * Revives all players, clears round results.
     */
    public function resetForNewGame($roomId) {
        // Revive all players in the room
        $this->db->prepare("UPDATE players SET is_alive = 1 WHERE room_id = ?")->execute([$roomId]);

        // Clear winner / elimination data so reveal doesn't persist
        $this->db->prepare("
            UPDATE rooms
            SET winner = NULL, eliminated_player_id = NULL, end_reason = NULL
            WHERE id = ?
        ")->execute([$roomId]);

        // Back to lobby
        $this->roomModel->updateStatus($roomId, 'waiting');
    }

    public function cleanupRoom($roomId) {
        // Find players who haven't polled in > 20 seconds
        $stmt = $this->db->prepare("SELECT id, is_host FROM players WHERE room_id = ? AND last_active_at < (NOW() - INTERVAL 20 SECOND)");
        $stmt->execute([$roomId]);
        $inactive = $stmt->fetchAll();

        if (empty($inactive)) return;

        $room = $this->roomModel->getById($roomId);
        if (!$room) return;

        $hostLeft = false;
        foreach ($inactive as $p) {
            if ($p['is_host']) $hostLeft = true;
            
            // Only auto-delete players if we are in the lobby (waiting)
            // If in-game, we keep them so the alive-count doesn't shift unexpectedly
            // They will be "revived" or cleaned up when the game resets.
            if ($room['status'] === 'waiting') {
                $this->db->prepare("DELETE FROM players WHERE id = ?")->execute([$p['id']]);
            }
        }

        if ($hostLeft) {
            $this->migrateHost($roomId);
        }

        // Occasional global cleanup (1% chance)
        if (mt_rand(1, 100) === 1) {
            $this->db->query("DELETE FROM rooms WHERE last_activity_at < (NOW() - INTERVAL 2 HOUR)");
        }
    }

    private function migrateHost($roomId) {
        // Find the oldest player who IS active
        $stmt = $this->db->prepare("SELECT id FROM players WHERE room_id = ? AND last_active_at >= (NOW() - INTERVAL 20 SECOND) ORDER BY created_at ASC LIMIT 1");
        $stmt->execute([$roomId]);
        $newHostId = $stmt->fetchColumn();

        if ($newHostId) {
            $this->db->prepare("UPDATE players SET is_host = 0 WHERE room_id = ?")->execute([$roomId]);
            $this->db->prepare("UPDATE players SET is_host = 1 WHERE id = ?")->execute([$newHostId]);
            $this->roomModel->updateHost($roomId, $newHostId);
        }
    }
}
