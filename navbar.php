<?php
// navbar.php - shared nav across all pages
if (!function_exists('isRegistered')) {
    require_once 'functions.php';
}
$registered = isRegistered();
$profile = $registered ? getProfile() : [];
?>

<nav class="navbar">
    <!-- home on the left -->
    <div class="navbar-left">
        <a href="index.php" name="home">Home</a>
    </div>

    <!-- play pairs + leaderboard/register on the right -->
    <div class="navbar-right">
        <a href="pairs.php" name="memory">Play Pairs</a>

        <?php if ($registered): ?>
            <a href="leaderboard.php" name="leaderboard">Leaderboard</a>

            <!-- show avatar + username when logged in -->
            <span class="navbar-avatar">
                <span class="emoji-stack navbar-emoji-stack">
                    <?= emojiSkinImg($profile['avatar_colour']); ?>
                    <?= emojiEyesImg($profile['avatar_eyes']); ?>
                    <?= emojiMouthImg($profile['avatar_mouth']); ?>
                </span>
                <span class="navbar-username"><?= htmlspecialchars($profile['username']); ?></span>
            </span>
        <?php else: ?>
            <a href="registration.php" name="register">Register</a>
        <?php endif; ?>
    </div>
</nav>