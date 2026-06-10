<?php
// pairs.php - the game page
require_once 'functions.php';
$registered = isRegistered();
$profile = $registered ? getProfile() : [];
$username = $profile['username'] ?? '';

// grab user's best scores per level for gold bg comparison
$userBestScores = [];
if ($registered && $username) {
    foreach (loadScores() as $entry) {
        if ($entry['username'] === $username && !empty($entry['levelScores'])) {
            foreach ($entry['levelScores'] as $lvl => $pts) {
                if (!isset($userBestScores[$lvl]) || $pts > $userBestScores[$lvl])
                    $userBestScores[$lvl] = $pts;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Pairs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div id="main">
    <div class="game-container" id="gameContainer">

        <div class="game-header">
            <h2>PAIRS</h2>
            <div class="difficulty-selector">
                <button class="difficulty-btn active" data-diff="simple" onclick="selectDifficulty('simple')">Simple</button>
                <button class="difficulty-btn" data-diff="medium" onclick="selectDifficulty('medium')">Medium</button>
                <button class="difficulty-btn" data-diff="complex" onclick="selectDifficulty('complex')">Complex</button>
            </div>
        </div>

        <!-- level indicator, only visible in complex mode -->
        <div class="level-indicator" id="levelIndicator">LEVEL 1 OF 4</div>

        <!-- stats shown during gameplay -->
        <div class="game-stats" id="gameStats" style="display:none;">
            <div class="stat-item">
                <div class="stat-label">Points</div>
                <div class="stat-value" id="statPoints">0</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Attempts</div>
                <div class="stat-value" id="statAttempts">0</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Time</div>
                <div class="stat-value" id="statTime">00:00</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Matches</div>
                <div class="stat-value" id="statMatches">0/0</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Lives</div>
                <div class="stat-value" id="statWrong">0/10</div>
            </div>
        </div>

        <!-- start button -->
        <div class="start-area" id="startArea">
            <p>Select a difficulty, then press start</p>
            <button class="start-btn" onclick="startGame()">Start the game</button>
        </div>

        <div id="gameBoard" style="display:none;">
            <div class="card-grid" id="cardGrid"></div>
        </div>

        <!-- shown when game ends -->
        <div class="game-complete" id="gameComplete"></div>
    </div>
</div>

<!-- pass data from PHP to JS -->
<script>
    var IS_REGISTERED = <?= $registered ? 'true' : 'false'; ?>;
    var USERNAME = <?= json_encode($username); ?>;
    var USER_BEST_SCORES = <?= json_encode($userBestScores); ?>;
</script>
<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>