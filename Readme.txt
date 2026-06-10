Coursework Submission - ECM1417
------------------------------------------------------------
Live Website Link
------------------------------------------------------------
http://ml-lab-4d78f073-aa49-4f0e-bce2-31e5254052c7.ukwest.cloudapp.azure.com:65501/index.php

------------------------------------------------------------
Project Structure
------------------------------------------------------------
- index.php, registration.php, pairs.php, leaderboard.php
- navbar.php (shared navigation), functions.php (helper functions)
- style.css (all styling), script.js (game logic)
- scores.json (leaderboard data, no database used)
- images/ (background image and emoji assets)
------------------------------------------------------------
Features by Page
------------------------------------------------------------
INDEX.PHP
- Welcome message and play button for registered users
- Register prompt with hyperlink for guests

REGISTRATION.PHP
- Username validation with error shown under input
- Simple: default emoji avatar for all users
- Medium: pick from 6 preset emoji combinations
- Complex: choose face colour, eyes and mouth with live preview
- Profile saved to cookies (30 days) and session variables

PAIRS.PHP + SCRIPT.JS
- Simple: 6 cards, preset emojis, score by attempts
- Medium: 10 cards, random emoji combos, score by attempts and time
- Complex: 4 levels (6/10/12/16 cards), groups up to 4, per-level scores
- Background turns gold (#FFD700) when beating previous best
- CSS 3D flip animation, shake on mismatch, Fisher-Yates shuffle
- Sound effects via Web Audio API
- Score submitted via POST on game completion

LEADERBOARD.PHP
- Best score per user sorted descending
- Blue header cells, border-spacing 2px, top 3 highlighted
- Complex entries show per-level score breakdown

NAVBAR.PHP
- Blue background, Verdana bold 12px
- Emoji avatar shown for registered users
- Register link shown for guests
