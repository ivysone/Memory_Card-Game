<?php
// registration.php
require_once 'functions.php';
$error = '';
$username = '';

// handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $error = validateUsername($username);
    if ($error === '') {
        $tier = $_POST['avatar_tier'] ?? 'simple';
        switch ($tier) {
            case 'medium':
                $p = getMediumAvatarPresets()[intval($_POST['medium_preset'] ?? 0)] ?? getMediumAvatarPresets()[0];
                saveProfile($username, 'medium', $p['colour'], $p['eyes'], $p['mouth']);
                break;
            case 'complex':
                saveProfile($username, 'complex',
                    $_POST['avatar_colour'] ?? 'yellow',
                    $_POST['avatar_eyes'] ?? 'normal',
                    $_POST['avatar_mouth'] ?? 'smiling');
                break;
            default:
                saveProfile($username, 'simple', 'yellow', 'normal', 'smiling');
        }
        header('Location: index.php');
        exit;
    }
}
$opts = getAvatarOptions();
$presets = getMediumAvatarPresets();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Pairs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div id="main">
    <div class="content-panel">
        <h1>Create Your Profile</h1>
        <p class="subtitle">Choose a username and build your avatar</p>

        <form method="POST" action="">
            <!-- username input -->
            <div class="form-group <?= $error ? 'has-error' : ''; ?>">
                <label for="username">Username / Nickname</label>
                <input type="text" id="username" name="username"
                       value="<?= htmlspecialchars($username); ?>"
                       placeholder="Enter your username" required>
                <?php if ($error): ?>
                    <span class="form-error"><?= htmlspecialchars($error); ?></span>
                <?php endif; ?>
            </div>

            <!-- avatar tier tabs -->
            <div class="avatar-section">
                <label>Choose Your Avatar</label>
                <div class="tier-tabs">
                    <input type="radio" name="avatar_tier" value="simple" id="tier-simple" class="tier-radio" checked>
                    <label for="tier-simple" class="tier-tab active" onclick="switchTier('simple')">Simple</label>
                    <input type="radio" name="avatar_tier" value="medium" id="tier-medium" class="tier-radio">
                    <label for="tier-medium" class="tier-tab" onclick="switchTier('medium')">Medium</label>
                    <input type="radio" name="avatar_tier" value="complex" id="tier-complex" class="tier-radio">
                    <label for="tier-complex" class="tier-tab" onclick="switchTier('complex')">Complex</label>
                </div>

                <!-- simple tier - default avatar -->
                <div class="tier-content active" id="tier-content-simple">
                    <div class="avatar-preview-area">
                        <div class="avatar-preview">
                            <?= emojiSkinImg('yellow'); ?><?= emojiEyesImg('normal'); ?><?= emojiMouthImg('smiling'); ?>
                        </div>
                        <div class="simple-message">Default avatar — same for all players</div>
                    </div>
                </div>

                <!-- medium tier - pick from presets -->
                <div class="tier-content" id="tier-content-medium">
                    <div class="preset-grid">
                        <?php foreach ($presets as $i => $p): ?>
                        <label class="preset-option <?= $i===0?'selected':''; ?>" onclick="selectPreset(this)">
                            <input type="radio" name="medium_preset" value="<?= $i; ?>" <?= $i===0?'checked':''; ?>>
                            <div class="preset-emoji">
                                <?= emojiSkinImg($p['colour']); ?><?= emojiEyesImg($p['eyes']); ?><?= emojiMouthImg($p['mouth']); ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- complex tier - custom feature selection -->
                <div class="tier-content" id="tier-content-complex">
                    <div class="avatar-preview-area">
                        <div class="avatar-preview">
                            <img src="<?= IMG_SKIN ?>yellow.png"  id="preview-colour" alt="face"  class="emoji-layer">
                            <img src="<?= IMG_EYES ?>normal.png"  id="preview-eyes"   alt="eyes"  class="emoji-layer">
                            <img src="<?= IMG_MOUTH ?>smiling.png" id="preview-mouth"  alt="mouth" class="emoji-layer">
                        </div>
                        <span class="avatar-preview-label">Your custom emoji — pick features below</span>
                    </div>

                    <div class="feature-selector">
                        <h4>Face Colour</h4>
                        <div class="feature-options">
                            <?php foreach ($opts['colours'] as $c): ?>
                            <label class="feature-option <?= $c==='yellow'?'selected':''; ?>" onclick="selectFeature(this,'avatar_colour','<?= $c; ?>')">
                                <input type="radio" name="avatar_colour" value="<?= $c; ?>" <?= $c==='yellow'?'checked':''; ?>>
                                <img src="<?= IMG_SKIN.$c; ?>.png" alt="<?= $c; ?>"><span><?= ucfirst($c); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="feature-selector">
                        <h4>Eyes</h4>
                        <div class="feature-options">
                            <?php foreach ($opts['eyes'] as $e): ?>
                            <label class="feature-option <?= $e==='normal'?'selected':''; ?>" onclick="selectFeature(this,'avatar_eyes','<?= $e; ?>')">
                                <input type="radio" name="avatar_eyes" value="<?= $e; ?>" <?= $e==='normal'?'checked':''; ?>>
                                <img src="<?= IMG_EYES.$e; ?>.png" alt="<?= $e; ?>"><span><?= ucfirst($e); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="feature-selector">
                        <h4>Mouth</h4>
                        <div class="feature-options">
                            <?php foreach ($opts['mouths'] as $m): ?>
                            <label class="feature-option <?= $m==='smiling'?'selected':''; ?>" onclick="selectFeature(this,'avatar_mouth','<?= $m; ?>')">
                                <input type="radio" name="avatar_mouth" value="<?= $m; ?>" <?= $m==='smiling'?'checked':''; ?>>
                                <img src="<?= IMG_MOUTH.$m; ?>.png" alt="<?= $m; ?>"><span><?= ucfirst($m); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Register</button>
        </form>
    </div>
</div>

<script>
// for updating the complex avatar preview live
var IMG_MAP = { 'avatar_colour': '<?= IMG_SKIN ?>', 'avatar_eyes': '<?= IMG_EYES ?>', 'avatar_mouth': '<?= IMG_MOUTH ?>' };
var PREVIEW_MAP = { 'avatar_colour': 'preview-colour', 'avatar_eyes': 'preview-eyes', 'avatar_mouth': 'preview-mouth' };

function switchTier(t) {
    document.querySelectorAll('.tier-tab').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.tier-content').forEach(function(el) { el.classList.remove('active'); });
    document.querySelector('label[for="tier-' + t + '"]').classList.add('active');
    document.getElementById('tier-content-' + t).classList.add('active');
}

function selectPreset(label) {
    document.querySelectorAll('.preset-option').forEach(function(o) { o.classList.remove('selected'); });
    label.classList.add('selected');
}

function selectFeature(label, name, val) {
    label.parentElement.querySelectorAll('.feature-option').forEach(function(o) { o.classList.remove('selected'); });
    label.classList.add('selected');
    // update preview
    var img = document.getElementById(PREVIEW_MAP[name]);
    if (img) img.src = IMG_MAP[name] + val + '.png';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>