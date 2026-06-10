<?php
// leaderboard.php - shows best scores, handles score submission
require_once 'functions.php';
$registered = isRegistered();
$submitted = false;

// handle score submission (POST from game page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_score'])) {
    $u = trim($_POST['username'] ?? '');
    $s = intval($_POST['score'] ?? 0);
    $d = $_POST['difficulty'] ?? 'simple';
    $a = intval($_POST['attempts'] ?? 0);
    $t = intval($_POST['time_taken'] ?? 0);
    $l = intval($_POST['level'] ?? 0);
    $ls = [];
    if (!empty($_POST['level_scores'])) {
        $dec = json_decode($_POST['level_scores'], true);
        if (is_array($dec)) $ls = $dec;
    }
    if ($u && $s > 0) {
        saveScore($u, $s, $d, $a, $t, $l, $ls);
        $submitted = true;
    }
}
$bestScores = getBestScores();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard — Pairs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div id="main">
    <div class="leaderboard-container">
        <h2>LEADERBOARD</h2>

        <?php if ($submitted): ?>
            <p style="text-align:center;color:#FFD700;font-weight:bold;margin-bottom:16px;font-size:13px;">Score submitted!</p>
        <?php endif; ?>

        <?php if (empty($bestScores)): ?>
            <div class="leaderboard-empty">
                <p>No scores yet — be the first!</p>
                <a href="pairs.php" class="btn btn-primary" style="margin-top:14px;">Play Now</a>
            </div>
        <?php else: ?>
            <table>
                <tr>
                    <th>Rank</th><th>Player</th><th>Best Score</th>
                    <th>Difficulty</th><th>Attempts</th><th>Time</th>
                </tr>
                <?php foreach ($bestScores as $rank => $e): ?>
                <?php
                    $r = $rank + 1;
                    $cls = $r===1 ? 'rank-1' : ($r===2 ? 'rank-2' : ($r===3 ? 'rank-3' : ''));
                    $tm = sprintf('%02d:%02d', floor(($e['time'] ?? 0) / 60), ($e['time'] ?? 0) % 60);
                ?>
                <tr class="<?= $cls; ?>">
                    <td><?= $r; ?></td>
                    <td><?= htmlspecialchars($e['username']); ?></td>
                    <td><strong><?= $e['score']; ?></strong></td>
                    <td><?= ucfirst($e['difficulty'] ?? 'simple'); ?></td>
                    <td><?= $e['attempts'] ?? '-'; ?></td>
                    <td><?= $tm; ?></td>
                </tr>
                <!-- level breakdown for complex scores -->
                <?php if (!empty($e['levelScores']) && ($e['difficulty'] ?? '') === 'complex'): ?>
                <tr>
                    <td></td>
                    <td colspan="5" style="font-size:11px;color:#666;padding:4px 14px;">
                        <?php foreach ($e['levelScores'] as $lv => $pts): ?>
                            Level <?= $lv; ?>: <?= intval($pts); ?>pts &nbsp;
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <div style="text-align:center;margin-top:20px;">
            <a href="pairs.php" class="btn btn-primary">Play Again</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>