<?php
// functions.php - sessions, validation, avatars, scores etc

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SCORES_FILE', __DIR__ . '/scores.json');
define('IMG_SKIN',  'images/emoji assets/skin/');
define('IMG_EYES',  'images/emoji assets/eyes/');
define('IMG_MOUTH', 'images/emoji assets/mouth/');


// check if user has registered (session or cookie)
function isRegistered() {
    return (isset($_SESSION['registered']) && $_SESSION['registered'] === true)
        || isset($_COOKIE['username']);
}

// get the current user profile
function getProfile() {
    return [
        'username'      => $_COOKIE['username']      ?? $_SESSION['username']      ?? '',
        'avatar_type'   => $_COOKIE['avatar_type']   ?? $_SESSION['avatar_type']   ?? 'simple',
        'avatar_colour' => $_COOKIE['avatar_colour'] ?? $_SESSION['avatar_colour'] ?? 'yellow',
        'avatar_eyes'   => $_COOKIE['avatar_eyes']   ?? $_SESSION['avatar_eyes']   ?? 'normal',
        'avatar_mouth'  => $_COOKIE['avatar_mouth']  ?? $_SESSION['avatar_mouth']  ?? 'smiling',
    ];
}

// save profile into session + cookies (30 day expiry)
function saveProfile($username, $avatarType, $colour = 'yellow', $eyes = 'normal', $mouth = 'smiling') {
    $expiry = time() + (30 * 24 * 60 * 60);
    $path = '/';
    setcookie('username',      $username,   $expiry, $path);
    setcookie('avatar_type',   $avatarType, $expiry, $path);
    setcookie('avatar_colour', $colour,     $expiry, $path);
    setcookie('avatar_eyes',   $eyes,       $expiry, $path);
    setcookie('avatar_mouth',  $mouth,      $expiry, $path);

    $_SESSION['registered']    = true;
    $_SESSION['username']      = $username;
    $_SESSION['avatar_type']   = $avatarType;
    $_SESSION['avatar_colour'] = $colour;
    $_SESSION['avatar_eyes']   = $eyes;
    $_SESSION['avatar_mouth']  = $mouth;
}


// validate username against the invalid character list from spec
function validateUsername($username) {
    if (empty(trim($username))) {
        return 'Username cannot be empty.';
    }
    // invalid chars: " ! @ # % & ^ * ( ) + = { } [ ] — ; : " ' < > ? /
    $pattern = '/[\"!@#%&\^*\(\)\+=\{\}\[\]\x{2014};:\x{201C}\x{201D}\' <>?\/\\\\]/u';
    if (preg_match($pattern, $username)) {
        return 'Username contains invalid characters. These are not allowed: ! @ # % & ^ * ( ) + = { } [ ] — ; : " \' < > ? /';
    }
    return '';
}


// avatar options for the selector
function getAvatarOptions() {
    return [
        'colours' => ['yellow', 'green', 'red'],
        'eyes'    => ['normal', 'closed', 'rolling', 'winking', 'laughing', 'long'],
        'mouths'  => ['smiling', 'sad', 'teeth', 'straight', 'open', 'surprise'],
    ];
}

// 6 preset combos for medium tier
function getMediumAvatarPresets() {
    return [
        ['colour' => 'yellow', 'eyes' => 'normal',   'mouth' => 'smiling'],
        ['colour' => 'yellow', 'eyes' => 'winking',  'mouth' => 'teeth'],
        ['colour' => 'green',  'eyes' => 'laughing',  'mouth' => 'open'],
        ['colour' => 'green',  'eyes' => 'rolling',  'mouth' => 'surprise'],
        ['colour' => 'red',    'eyes' => 'closed',   'mouth' => 'sad'],
        ['colour' => 'red',    'eyes' => 'long',     'mouth' => 'straight'],
    ];
}

// emoji layer img tags (skin, eyes, mouth layered on top of each other)
function emojiSkinImg($c, $cls = 'emoji-layer') {
    return '<img src="' . htmlspecialchars(IMG_SKIN . $c . '.png') . '" alt="face" class="' . $cls . '">';
}
function emojiEyesImg($e, $cls = 'emoji-layer') {
    return '<img src="' . htmlspecialchars(IMG_EYES . $e . '.png') . '" alt="eyes" class="' . $cls . '">';
}
function emojiMouthImg($m, $cls = 'emoji-layer') {
    return '<img src="' . htmlspecialchars(IMG_MOUTH . $m . '.png') . '" alt="mouth" class="' . $cls . '">';
}


// --- Leaderboard (uses JSON file, no database) ---

function loadScores() {
    if (!file_exists(SCORES_FILE)) return [];
    $data = json_decode(file_get_contents(SCORES_FILE), true);
    return is_array($data) ? $data : [];
}

// add a new score entry to scores.json
function saveScore($username, $score, $difficulty, $attempts, $timeTaken, $level = 0, $levelScores = []) {
    $scores = loadScores();
    $scores[] = [
        'username' => $username, 'score' => $score, 'difficulty' => $difficulty,
        'attempts' => $attempts, 'time' => $timeTaken, 'level' => $level,
        'levelScores' => $levelScores, 'timestamp' => time(),
    ];
    file_put_contents(SCORES_FILE, json_encode($scores, JSON_PRETTY_PRINT));
}

// get each user's single best score, sorted highest first
function getBestScores() {
    $scores = loadScores();
    $best = [];
    foreach ($scores as $entry) {
        $user = $entry['username'];
        if (!isset($best[$user]) || $entry['score'] > $best[$user]['score']) {
            $best[$user] = $entry;
        }
    }
    usort($best, function ($a, $b) { return $b['score'] - $a['score']; });
    return $best;
}