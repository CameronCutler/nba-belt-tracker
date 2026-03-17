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
