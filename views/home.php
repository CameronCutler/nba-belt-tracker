<!DOCTYPE html>
<html>
<head>
    <title>NBA Belt Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
        body { background: #0f1117; color: #e5e7eb; }

        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            border-bottom: 1px solid #1e3a5f;
        }
        .hero img { filter: drop-shadow(0 8px 24px rgba(0,0,0,0.5)); }
        .holder-name { color: #fbbf24; }
        .holder-since { color: #9ca3af; font-size: 0.9rem; }

        .games-section { background: #0f1117; }

        .game-card {
            background: #1c1f2e;
            border: 1px solid #2d3148;
            border-radius: 12px;
            transition: transform 0.15s ease;
        }
        .game-card:hover { transform: translateY(-2px); }
        .game-card.belt-game {
            border-color: #f59e0b;
            box-shadow: 0 0 16px rgba(245, 158, 11, 0.25);
        }
        .belt-badge {
            background: #f59e0b;
            color: #000;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .team-abbr { font-size: 1.4rem; font-weight: 700; }
        .game-status { color: #6b7280; font-size: 0.85rem; }
        .vs { color: #4b5563; }
        .score { font-size: 1.1rem; font-weight: 600; color: #e5e7eb; }

        .holder-card {
            background: linear-gradient(135deg, #1a1200 0%, #2d1f00 50%, #1a1200 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            box-shadow: 0 0 40px rgba(245, 158, 11, 0.3), 0 0 80px rgba(245, 158, 11, 0.1);
        }
        .holder-card .crown { font-size: 2.5rem; line-height: 1; }
        .holder-card .holder-label {
            text-transform: uppercase;
            letter-spacing: 0.15em;
            font-size: 0.75rem;
            color: #f59e0b;
            font-weight: 700;
        }
        .holder-card .holder-team-name {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
        }
        .holder-card .holder-meta {
            color: #9ca3af;
            font-size: 0.9rem;
        }
        .stat-pill {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 10px;
            padding: 10px 16px;
            min-width: 80px;
        }
        .stat-pill .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fbbf24;
            line-height: 1;
        }
        .stat-pill .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<div class="hero py-5 text-white">
    <div class="container text-center">
        <img src="/img/nba_larryO_belt.png" alt="Championship Belt" class="img-fluid mb-4" style="max-height: 180px;">
        <h1 class="display-5 fw-bold mb-4">NBA Championship Belt Tracker</h1>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div id="belt-holder-section" class="holder-card p-4">
                    <div class="holder-since">Loading belt holder...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="games-section py-5">
    <div class="container">
        <div class="d-flex align-items-baseline gap-3 mb-4 flex-wrap">
            <h2 class="fw-semibold mb-0" style="color:#e5e7eb;">Today's Games</h2>
            <span class="fs-6 text-secondary" id="games-date"></span>
            <span class="ms-auto fs-6 text-secondary">All times Eastern (ET)</span>
        </div>
        <div id="games-container" class="row g-3">
            <div class="col-12 text-secondary">Loading games...</div>
        </div>
    </div>
</div>

<script>
async function loadBeltHolder() {
    const section = document.getElementById('belt-holder-section');
    try {
        const res = await fetch('/api/belt/holder');
        const data = await res.json();
        if (!res.ok) {
            section.innerHTML = '<span class="holder-meta">Belt holder unknown</span>';
            return;
        }
        const abbr = data.abbreviation?.toLowerCase() ?? data.team_name?.toLowerCase() ?? '';
        section.innerHTML = `
            <div class="crown mb-2">🏆</div>
            <div class="holder-label mb-2">Current Belt Holder</div>
            ${teamLogo(abbr, 150)}
            <div class="holder-team-name mt-2">${data.full_name}</div>
            <div class="holder-meta mt-1 mb-4">Since ${formatDate(data.acquired_date)}</div>
            <div class="d-flex justify-content-center gap-3">
                <div class="stat-pill text-center">
                    <div class="stat-value">${data.days_held ?? 0}</div>
                    <div class="stat-label">Days</div>
                </div>
                <div class="stat-pill text-center">
                    <div class="stat-value">${data.season_defenses ?? 0}</div>
                    <div class="stat-label">Defenses</div>
                </div>
                <div class="stat-pill text-center">
                    <div class="stat-value">${data.season_reigns ?? 1}</div>
                    <div class="stat-label">Reigns</div>
                </div>
            </div>
        `;
    } catch (e) {
        section.innerHTML = '<span class="holder-since">Could not load belt holder</span>';
    }
}

async function loadTodaysGames() {
    const container = document.getElementById('games-container');
    const dateLabel = document.getElementById('games-date');
    try {
        const res = await fetch('/api/games/today');
        const data = await res.json();
        const games = data.data ?? [];

        const today = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
        dateLabel.textContent = today;

        if (games.length === 0) {
            container.innerHTML = '<div class="col-12 text-secondary">No games scheduled today.</div>';
            return;
        }

        container.innerHTML = games.map(game => buildGameCard(game)).join('');
    } catch (e) {
        container.innerHTML = '<div class="col-12 text-danger">Failed to load games.</div>';
    }
}

function teamLogo(abbreviation, size = 48) {
    if (!abbreviation) return '';
    const abbr = abbreviation.toLowerCase();
    return `
        <picture>
            <source srcset="/img/Icons/${abbr}.svg" type="image/svg+xml">
            <img src="/img/team-logos/${abbr}.png" alt="${abbreviation}" style="width:${size}px;height:${size}px;">
        </picture>`;
}

function buildGameCard(game) {
    const isBeltGame = game.belt_on_the_line;
    const homeAbbr = game.home_team?.abbreviation ?? '';
    const awayAbbr = game.visitor_team?.abbreviation ?? '';
    const hasScore = game.home_team_score && game.visitor_team_score;
    const status = game.status ?? '';

    const scoreHtml = hasScore
        ? `<div class="score mt-2">${game.visitor_team_score} &ndash; ${game.home_team_score}</div>`
        : `<div class="game-status mt-2">${formatStatus(status)}</div>`;

    const beltBadge = isBeltGame
        ? `<div class="mb-2"><span class="belt-badge">🏆 BELT ON THE LINE</span></div>`
        : '';

    return `
        <div class="col-sm-6 col-lg-3">
            <div class="game-card p-4 h-100 text-center ${isBeltGame ? 'belt-game' : ''}">
                ${beltBadge}
                <div class="d-flex justify-content-center align-items-center gap-4">
                    <div>
                        ${teamLogo(awayAbbr)}
                        <div class="team-abbr mt-1">${awayAbbr}</div>
                    </div>
                    <span class="vs">@</span>
                    <div>
                        ${teamLogo(homeAbbr)}
                        <div class="team-abbr mt-1">${homeAbbr}</div>
                    </div>
                </div>
                ${scoreHtml}
            </div>
        </div>
    `;
}

function formatStatus(status) {
    if (!status) return 'Scheduled';
    // BDL returns UTC ISO timestamps for scheduled games e.g. "2026-03-16T23:00:00Z"
    if (/^\d{4}-\d{2}-\d{2}T/.test(status)) {
        const date = new Date(status);
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            timeZone: 'America/New_York'
        }) + ' ET';
    }
    return status;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    return new Date(y, m - 1, d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

loadBeltHolder();
loadTodaysGames();
</script>

</body>
</html>
