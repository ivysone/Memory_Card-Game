#  Memory Card Game

A browser-based memory card game built with PHP, vanilla JavaScript, and Bootstrap. Players flip cards to find matching emoji pairs across three difficulty levels. Includes user registration, a custom layered emoji avatar system, and a shared leaderboard — all without a database.

## Features

- Three difficulty modes: Simple (6 cards), Medium (10 cards), and Complex (4 progressive levels up to 16 cards)
- Layered emoji avatars built from skin, eyes, and mouth image layers — fully customisable in the registration flow
- Shared JSON leaderboard that stores each user's best score
- Gold background effect when a registered user beats their personal best mid-game
- Synthesised sound effects via the Web Audio API (flip, match, mismatch, win, lose, level up)
- 3D CSS card flip animations and shake animation on mismatch
- Sessions and cookies for persistent user profiles (30-day expiry) — no database required
- Responsive layout, works on mobile and desktop

## File Structure

```
├── index.php           Landing page — welcome screen or play prompt
├── pairs.php           Game page — renders the board and passes user data to JS
├── registration.php    Profile creation — username + avatar tier selector
├── leaderboard.php     Displays best scores, handles score submission via POST
├── navbar.php          Shared nav included across all pages
├── functions.php       All shared logic — sessions, cookies, avatar helpers, score read/write
├── script.js           Game engine — card logic, scoring, timer, audio, state management
├── style.css           All styles
├── scores.json         Leaderboard data (auto-created, no database needed)
└── images/
    └── emoji assets/
        ├── skin/       Face colour layer PNGs (yellow, green, red)
        ├── eyes/       Eyes layer PNGs (normal, closed, rolling, winking, laughing, long)
        └── mouth/      Mouth layer PNGs (smiling, sad, teeth, straight, open, surprise)
```

## How to Run

You need a PHP server with write access to the project directory (so `scores.json` can be updated).

**Option 1 — PHP built-in server (local dev):**
```bash
cd project-folder
php -S localhost:8000
```
Then open `http://localhost:8000` in your browser.

**Option 2 — Apache/XAMPP:**
Drop the folder into your `htdocs` directory and navigate to `http://localhost/pairs`.

**Option 3 — Azure VM (as deployed):**
Place files in the Apache web root, ensure the directory is writable, and access via your VM's public IP.

> `scores.json` is created automatically on first score submission. Make sure the web server has write permission on the project directory.

## Difficulty Modes

| Mode | Cards | Groups | Lives | Notes |
|---|---|---|---|---|
| Simple | 6 | 3 pairs | 10 | Fixed emoji presets |
| Medium | 10 | 5 pairs | 15 | Random emoji combos |
| Complex | 6 → 10 → 12 → 16 | 4 levels | 10–22 | Cards per group increases each level; score accumulates across all levels |

## Scoring

Each match starts at 100 points. Penalties are subtracted:
- **-10** per extra attempt beyond the minimum needed
- **-1** per 5 seconds elapsed (Medium and Complex only)
- Minimum score per match is 10 points

In Complex mode, scores from each level are added together for the final total.

## Avatar System

Registration has three tiers:

- **Simple** — default emoji, same for everyone
- **Medium** — pick from 6 preset combinations
- **Complex** — choose face colour, eyes, and mouth independently; live preview updates as you select

Avatars are rendered by stacking three PNG layers (skin, eyes, mouth) using absolute positioning. The same system is used in the navbar, registration page, and on the cards themselves.

## Tech Stack

- **Backend:** PHP (no framework), sessions + cookies for auth, JSON file for data storage
- **Frontend:** Vanilla JavaScript, Bootstrap 5.3, CSS3 (3D transforms, animations)
- **Audio:** Web Audio API — all sounds are synthesised, no audio files
- **Hosting:** Apache on Azure VM
