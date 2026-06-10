// script.js - game engine for pairs (simple / medium / complex)

// image paths
var IMG = {
    skin:  'images/emoji assets/skin/',
    eyes:  'images/emoji assets/eyes/',
    mouth: 'images/emoji assets/mouth/'
};


// difficulty configs
var CONFIG = {
    simple: {
        groups: 3, cardsPerGroup: 2, totalCards: 6,
        usePresets: true, gridClass: 'cards-6',
        maxWrongAttempts: 10
    },
    medium: {
        groups: 5, cardsPerGroup: 2, totalCards: 10,
        usePresets: false, gridClass: 'cards-10',
        maxWrongAttempts: 15
    },
    complex: {
        levels: [
            { groups: 3, cardsPerGroup: 2, totalCards: 6,  gridClass: 'cards-6',  maxWrong: 10 },
            { groups: 5, cardsPerGroup: 2, totalCards: 10, gridClass: 'cards-10', maxWrong: 15 },
            { groups: 4, cardsPerGroup: 3, totalCards: 12, gridClass: 'cards-12', maxWrong: 18 },
            { groups: 4, cardsPerGroup: 4, totalCards: 16, gridClass: 'cards-16', maxWrong: 22 },
        ]
    }
};

var EMOJI_FEATURES = {
    colours: ['yellow', 'green', 'red'],
    eyes:    ['normal', 'closed', 'rolling', 'winking', 'laughing', 'long'],
    mouths:  ['smiling', 'sad', 'teeth', 'straight', 'open', 'surprise']
};

// presets for simple mode
var SIMPLE_PRESETS = [
    { colour: 'yellow', eyes: 'normal',   mouth: 'smiling'  },
    { colour: 'green',  eyes: 'winking',  mouth: 'teeth'    },
    { colour: 'red',    eyes: 'laughing',  mouth: 'open'     },
];


// game state
var game = {
    difficulty: 'simple',
    cards: [],
    flippedCards: [],
    matchedCount: 0,
    attempts: 0,
    wrongAttempts: 0,
    maxWrongAttempts: 10,
    points: 0,
    timerInterval: null,
    timeElapsed: 0,
    isPlaying: false,
    isLocked: false,
    currentLevel: 1,
    totalLevelPoints: 0,
    levelScores: {},
    cardsPerGroup: 2,
    totalGroups: 0,
    bestScoreForLevel: 0,
    isGold: false,
};


// --- Audio (Web Audio API synth sounds) ---

var audioCtx = null;

function initAudio() {
    if (!audioCtx) {
        try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); }
        catch(e) { audioCtx = null; }
    }
}

function playTone(freq, duration, type) {
    if (!audioCtx) return;
    try {
        var osc = audioCtx.createOscillator();
        var gain = audioCtx.createGain();
        osc.type = type || 'square';
        osc.frequency.value = freq;
        gain.gain.value = 0.08;
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
    } catch(e) {}
}

// sfx
function playFlipSound()     { playTone(600, 0.1, 'square'); }
function playMatchSound()    { playTone(800, 0.12, 'square'); setTimeout(function(){ playTone(1200, 0.15, 'square'); }, 100); }
function playMismatchSound() { playTone(200, 0.25, 'sawtooth'); }
function playWinSound()      { playTone(523, 0.15, 'square'); setTimeout(function(){ playTone(659, 0.15, 'square'); }, 120); setTimeout(function(){ playTone(784, 0.2, 'square'); }, 240); setTimeout(function(){ playTone(1047, 0.3, 'square'); }, 360); }
function playLoseSound()     { playTone(400, 0.2, 'sawtooth'); setTimeout(function(){ playTone(300, 0.2, 'sawtooth'); }, 200); setTimeout(function(){ playTone(200, 0.4, 'sawtooth'); }, 400); }
function playLevelUpSound()  { playTone(660, 0.1, 'square'); setTimeout(function(){ playTone(880, 0.1, 'square'); }, 100); setTimeout(function(){ playTone(1100, 0.15, 'square'); }, 200); }


function getEl(id) { return document.getElementById(id); }


// --- Difficulty ---

function selectDifficulty(diff) {
    game.difficulty = diff;
    document.querySelectorAll('.difficulty-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.diff === diff);
    });
    var lvl = getEl('levelIndicator');
    if (lvl) lvl.style.display = diff === 'complex' ? 'block' : 'none';
    resetGame();
}


// --- Start ---
// hides start button, sets up the round

function startGame() {
    initAudio(); // needs user click to init audio context

    getEl('startArea').style.display = 'none';
    getEl('gameBoard').style.display = 'block';
    getEl('gameStats').style.display = 'flex';
    getEl('gameComplete').style.display = 'none';

    getEl('gameContainer').classList.remove('gold-bg');
    game.isGold = false;
    game.flippedCards = [];
    game.matchedCount = 0;
    game.attempts = 0;
    game.wrongAttempts = 0;
    game.points = 0;
    game.timeElapsed = 0;
    game.isPlaying = true;
    game.isLocked = false;

    if (game.difficulty === 'complex') {
        game.currentLevel = 1;
        game.totalLevelPoints = 0;
        game.levelScores = {};
        updateLevelIndicator();
    }

    buildCards();
    renderCards();
    updateStatsDisplay();
    startTimer();
}


// --- Build Cards ---

function buildCards() {
    var cfg;
    if (game.difficulty === 'complex') {
        cfg = CONFIG.complex.levels[game.currentLevel - 1];
        game.maxWrongAttempts = cfg.maxWrong;
    } else {
        cfg = CONFIG[game.difficulty];
        game.maxWrongAttempts = cfg.maxWrongAttempts;
    }

    game.cardsPerGroup = cfg.cardsPerGroup;
    game.totalGroups = cfg.groups;

    var emojis = generateEmojis(cfg.groups, game.difficulty === 'simple');

    // create card objects
    game.cards = [];
    emojis.forEach(function(emoji, groupIndex) {
        for (var i = 0; i < cfg.cardsPerGroup; i++) {
            game.cards.push({
                id: groupIndex + '-' + i,
                groupId: groupIndex,
                emoji: emoji,
                isFlipped: false,
                isMatched: false
            });
        }
    });

    // shuffle the cards
    shuffle(game.cards);

    var grid = getEl('cardGrid');
    grid.className = 'card-grid ' + cfg.gridClass;

    // check previous best for gold bg
    if (game.difficulty === 'complex' && typeof USER_BEST_SCORES !== 'undefined') {
        game.bestScoreForLevel = USER_BEST_SCORES[game.currentLevel] || 0;
    }
}

// generate emoji combos (or use presets for simple)
function generateEmojis(count, usePresets) {
    if (usePresets) return SIMPLE_PRESETS.slice(0, count);

    var emojis = [];
    var usedKeys = {};
    while (emojis.length < count) {
        var colour = randomFrom(EMOJI_FEATURES.colours);
        var eyes   = randomFrom(EMOJI_FEATURES.eyes);
        var mouth  = randomFrom(EMOJI_FEATURES.mouths);
        var key    = colour + '-' + eyes + '-' + mouth;
        if (!usedKeys[key]) {
            usedKeys[key] = true;
            emojis.push({ colour: colour, eyes: eyes, mouth: mouth });
        }
    }
    return emojis;
}


// --- Render ---

function renderCards() {
    var grid = getEl('cardGrid');
    grid.innerHTML = '';

    game.cards.forEach(function(card, index) {
        var el = document.createElement('div');
        el.className = 'card';
        el.dataset.index = index;

        // card HTML - back side shows ?, front shows emoji
        el.innerHTML =
            '<div class="card-inner">' +
                '<div class="card-back"></div>' +
                '<div class="card-front">' +
                    '<div class="card-emoji">' +
                        '<img src="' + IMG.skin  + card.emoji.colour + '.png" alt="face" class="emoji-layer">' +
                        '<img src="' + IMG.eyes  + card.emoji.eyes   + '.png" alt="eyes" class="emoji-layer">' +
                        '<img src="' + IMG.mouth + card.emoji.mouth  + '.png" alt="mouth" class="emoji-layer">' +
                    '</div>' +
                '</div>' +
            '</div>';

        el.addEventListener('click', function() { flipCard(index); });
        grid.appendChild(el);
    });
}


// --- Card Flip + Matching ---

function flipCard(index) {
    if (!game.isPlaying || game.isLocked) return;

    var card = game.cards[index];
    if (card.isFlipped || card.isMatched) return;
    if (game.flippedCards.length >= game.cardsPerGroup) return;

    card.isFlipped = true;
    game.flippedCards.push(index);
    // console.log('flipped card:', index, card.emoji.colour);

    var cardEls = document.querySelectorAll('.card');
    cardEls[index].classList.add('flipped');
    playFlipSound();

    // check for match once enough cards flipped
    if (game.flippedCards.length === game.cardsPerGroup) {
        game.attempts++;
        game.isLocked = true;

        var groupId = game.cards[game.flippedCards[0]].groupId;
        var allMatch = game.flippedCards.every(function(i) {
            return game.cards[i].groupId === groupId;
        });

        if (allMatch) {
            handleMatch();
        } else {
            game.wrongAttempts++;
            handleMismatch();
        }

        updateStatsDisplay();
    }
}

function handleMatch() {
    var matched = game.flippedCards.slice();

    matched.forEach(function(i) { game.cards[i].isMatched = true; });

    setTimeout(function() {
        var cardEls = document.querySelectorAll('.card');
        matched.forEach(function(i) { cardEls[i].classList.add('matched'); });
        playMatchSound();
    }, 300);

    game.matchedCount++;
    game.flippedCards = [];
    game.isLocked = false;

    var matchPoints = calculateMatchPoints();
    game.points += matchPoints;
    // console.log('match! +' + matchPoints + ' pts, total: ' + game.points);

    // gold bg when beating previous best (complex only)
    if (game.difficulty === 'complex' && game.points > game.bestScoreForLevel && !game.isGold) {
        getEl('gameContainer').classList.add('gold-bg');
        game.isGold = true;
    }

    updateStatsDisplay();

    if (game.matchedCount >= game.totalGroups) {
        handleLevelComplete();
    }
}

// flip back + shake on mismatch
function handleMismatch() {
    var mismatched = game.flippedCards.slice();
    playMismatchSound();

    var cardEls = document.querySelectorAll('.card');
    mismatched.forEach(function(i) { cardEls[i].classList.add('mismatch'); });

    setTimeout(function() {
        mismatched.forEach(function(i) {
            game.cards[i].isFlipped = false;
            cardEls[i].classList.remove('flipped', 'mismatch');
        });
        game.flippedCards = [];
        game.isLocked = false;

        // check if too many wrong guesses
        if (game.wrongAttempts >= game.maxWrongAttempts) {
            handleGameOver();
        }
    }, 900);
}

function handleGameOver() {
    game.isPlaying = false;
    stopTimer();
    playLoseSound();

    getEl('gameBoard').style.display = 'none';
    getEl('gameStats').style.display = 'none';

    var completeEl = getEl('gameComplete');
    completeEl.style.display = 'block';

    var totalScore = game.difficulty === 'complex' ? game.totalLevelPoints + game.points : game.points;

    var html =
        '<div class="game-over-text">GAME OVER</div>' +
        '<h3>Too many wrong guesses!</h3>' +
        '<div class="score-breakdown">' +
            'You made ' + game.wrongAttempts + ' incorrect attempts (max: ' + game.maxWrongAttempts + ')<br>' +
            'Matches found: ' + game.matchedCount + '/' + game.totalGroups + ' | ' +
            'Points: ' + totalScore +
        '</div>';

    // registered users can still submit even on game over
    if (IS_REGISTERED && totalScore > 0) {
        html +=
            '<div class="complete-actions">' +
                '<form method="POST" action="leaderboard.php" style="display:inline">' +
                    '<input type="hidden" name="username" value="' + escapeHtml(USERNAME) + '">' +
                    '<input type="hidden" name="score" value="' + totalScore + '">' +
                    '<input type="hidden" name="difficulty" value="' + game.difficulty + '">' +
                    '<input type="hidden" name="attempts" value="' + game.attempts + '">' +
                    '<input type="hidden" name="time_taken" value="' + game.timeElapsed + '">' +
                    '<input type="hidden" name="level" value="' + game.currentLevel + '">' +
                    '<input type="hidden" name="level_scores" value=\'' + JSON.stringify(game.levelScores) + '\'>' +
                    '<button type="submit" name="submit_score" class="btn btn-gold">Submit Score</button>' +
                '</form>' +
                '<button class="btn btn-secondary" onclick="playAgain()">Play Again</button>' +
            '</div>';
    } else {
        html +=
            '<div class="complete-actions">' +
                '<button class="btn btn-primary" onclick="playAgain()">Try Again</button>' +
            '</div>';
    }

    completeEl.innerHTML = html;
}


// scoring - base 100 minus penalties
function calculateMatchPoints() {
    var base = 100;
    var extraAttempts = Math.max(0, game.attempts - game.matchedCount);
    var attemptPenalty = extraAttempts * 10;
    var timePenalty = 0;
    if (game.difficulty !== 'simple') {
        timePenalty = Math.floor(game.timeElapsed / 5);
    }
    return Math.max(10, base - attemptPenalty - timePenalty);
}


// --- Level / Game Completion ---

function handleLevelComplete() {
    game.isPlaying = false;
    stopTimer();

    if (game.difficulty === 'complex') {
        game.levelScores[game.currentLevel] = game.points;
        game.totalLevelPoints += game.points;

        // more levels left? show transition
        if (game.currentLevel < CONFIG.complex.levels.length) {
            playLevelUpSound();
            showLevelTransition();
            return;
        }
        // otherwise use total across all levels
        game.points = game.totalLevelPoints;
    }

    playWinSound();
    showGameComplete();
}

function showLevelTransition() {
    getEl('gameBoard').style.display = 'none';
    var el = getEl('gameComplete');
    el.style.display = 'block';
    el.innerHTML =
        '<h3>Level ' + game.currentLevel + ' Complete!</h3>' +
        '<div class="final-score">' + game.points + ' pts</div>' +
        '<div class="score-breakdown">' +
            'Attempts: ' + game.attempts + ' | Time: ' + formatTime(game.timeElapsed) +
        '</div>' +
        '<div class="complete-actions">' +
            '<button class="btn btn-primary" onclick="nextLevel()">Next Level &#8594;</button>' +
        '</div>';
}

function nextLevel() {
    game.currentLevel++;
    game.flippedCards = [];
    game.matchedCount = 0;
    game.attempts = 0;
    game.wrongAttempts = 0;
    game.points = 0;
    game.timeElapsed = 0;
    game.isPlaying = true;
    game.isLocked = false;
    game.isGold = false;
    getEl('gameContainer').classList.remove('gold-bg');

    updateLevelIndicator();
    buildCards();
    renderCards();

    getEl('gameComplete').style.display = 'none';
    getEl('gameBoard').style.display = 'block';
    getEl('gameStats').style.display = 'flex';

    updateStatsDisplay();
    startTimer();
}

// show final score + submit/play again
function showGameComplete() {
    getEl('gameBoard').style.display = 'none';
    getEl('gameStats').style.display = 'none';

    var el = getEl('gameComplete');
    el.style.display = 'block';

    var totalScore = game.difficulty === 'complex' ? game.totalLevelPoints : game.points;
    var diffLabel  = game.difficulty.charAt(0).toUpperCase() + game.difficulty.slice(1);

    // level breakdown for complex
    var levelBreakdown = '';
    if (game.difficulty === 'complex') {
        levelBreakdown = '<div class="score-breakdown">';
        for (var lvl in game.levelScores) {
            levelBreakdown += 'Level ' + lvl + ': ' + game.levelScores[lvl] + ' pts &nbsp;|&nbsp; ';
        }
        levelBreakdown += '</div>';
    }

    var html =
        '<h3>YOU WIN!</h3>' +
        '<div class="final-score">' + totalScore + ' pts</div>' +
        '<div class="score-breakdown">' +
            'Difficulty: ' + diffLabel + ' | ' +
            'Attempts: ' + game.attempts + ' | ' +
            'Time: ' + formatTime(game.timeElapsed) +
        '</div>' +
        levelBreakdown;

    // submit button only shows for registered users (POST to leaderboard)
    if (IS_REGISTERED) {
        html +=
            '<div class="complete-actions">' +
                '<form method="POST" action="leaderboard.php" style="display:inline">' +
                    '<input type="hidden" name="username" value="' + escapeHtml(USERNAME) + '">' +
                    '<input type="hidden" name="score" value="' + totalScore + '">' +
                    '<input type="hidden" name="difficulty" value="' + game.difficulty + '">' +
                    '<input type="hidden" name="attempts" value="' + game.attempts + '">' +
                    '<input type="hidden" name="time_taken" value="' + game.timeElapsed + '">' +
                    '<input type="hidden" name="level" value="' + game.currentLevel + '">' +
                    '<input type="hidden" name="level_scores" value=\'' + JSON.stringify(game.levelScores) + '\'>' +
                    '<button type="submit" name="submit_score" class="btn btn-gold">Submit Score</button>' +
                '</form>' +
                '<button class="btn btn-secondary" onclick="playAgain()">Play Again</button>' +
            '</div>';
    } else {
        html +=
            '<div class="complete-actions">' +
                '<button class="btn btn-secondary" onclick="playAgain()">Play Again</button>' +
                '<a href="registration.php" class="btn btn-primary">Register to save</a>' +
            '</div>';
    }

    el.innerHTML = html;
}

function playAgain() { resetGame(); }

function resetGame() {
    game.isPlaying = false;
    game.isLocked = false;
    game.flippedCards = [];
    game.matchedCount = 0;
    game.attempts = 0;
    game.wrongAttempts = 0;
    game.points = 0;
    game.timeElapsed = 0;
    game.currentLevel = 1;
    game.totalLevelPoints = 0;
    game.levelScores = {};
    game.isGold = false;
    stopTimer();

    getEl('gameContainer').classList.remove('gold-bg');
    getEl('startArea').style.display = 'block';
    getEl('gameBoard').style.display = 'none';
    getEl('gameComplete').style.display = 'none';
    getEl('gameStats').style.display = 'none';

    if (game.difficulty === 'complex') updateLevelIndicator();
    updateStatsDisplay();
}


// --- Timer ---

function startTimer() {
    stopTimer();
    game.timerInterval = setInterval(function() {
        game.timeElapsed++;
        updateStatsDisplay();
    }, 1000);
}

function stopTimer() {
    if (game.timerInterval) { clearInterval(game.timerInterval); game.timerInterval = null; }
}

function formatTime(s) {
    var m = Math.floor(s / 60);
    var sec = s % 60;
    return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
}


// --- UI Updates ---

function updateStatsDisplay() {
    var p = getEl('statPoints');
    var a = getEl('statAttempts');
    var t = getEl('statTime');
    var m = getEl('statMatches');
    var w = getEl('statWrong');
    if (p) p.textContent = game.points;
    if (a) a.textContent = game.attempts;
    if (t) t.textContent = formatTime(game.timeElapsed);
    if (m) m.textContent = game.matchedCount + '/' + game.totalGroups;
    if (w) w.textContent = game.wrongAttempts + '/' + game.maxWrongAttempts;
}

function updateLevelIndicator() {
    var el = getEl('levelIndicator');
    if (el) {
        el.textContent = 'LEVEL ' + game.currentLevel + ' OF ' + CONFIG.complex.levels.length;
        el.style.display = game.difficulty === 'complex' ? 'block' : 'none';
    }
}


// --- Utilities ---

// TODO: maybe add a high score animation later

// fisher-yates shuffle
function shuffle(arr) {
    for (var i = arr.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var tmp = arr[i]; arr[i] = arr[j]; arr[j] = tmp;
    }
    return arr;
}

function randomFrom(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}