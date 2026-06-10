<?php
// index.php - landing page
require_once 'functions.php';
$registered = isRegistered();
$profile = $registered ? getProfile() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pairs — Memory Card Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div id="main">
    <div class="content-panel" style="text-align:center;">
        <?php if ($registered): ?>
            <!-- welcome message + play button for registered users -->
            <h1 class="welcome-heading">Welcome to Pairs</h1>
            <p class="subtitle">Hey <?= htmlspecialchars($profile['username']); ?>, ready to test your memory?</p>
            <a href="pairs.php" class="btn btn-primary" style="margin-top:8px;">Click here to play</a>
        <?php else: ?>
            <!-- not registered yet, show register link -->
            <h1 class="welcome-heading">Pairs</h1>
            <p style="margin-top:12px;font-size:14px;color:rgba(255,255,255,0.6);">
                You're not using a registered session? <a href="registration.php">Register now</a>
            </p>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>