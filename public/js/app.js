async function loadBeltHolder() {
  const section = document.getElementById("belt-holder-section");
  try {
    const [holderRes, leadersRes] = await Promise.all([
      fetch("/api/belt/holder"),
      fetch("/api/belt/leaders"),
    ]);
    const data = await holderRes.json();
    const leaders = await leadersRes.json();

    if (!holderRes.ok) {
      section.innerHTML =
        '<span class="holder-meta">Belt holder unknown</span>';
      return;
    }

    const abbr =
      data.abbreviation?.toLowerCase() ?? data.team_name?.toLowerCase() ?? "";
    const leaderEntry = leaders.find(
      (l) => l.team_name.toLowerCase() === abbr
    );
    const totalDays = leaderEntry?.total_days ?? data.days_held ?? 0;

    section.innerHTML = `
            <div class="crown mb-2">🏆</div>
            <div class="holder-label mb-2">Current Belt Holder</div>
            ${teamLogo(abbr, true)}
            <div class="holder-team-name mt-2">${data.full_name}</div>
            <div class="holder-meta mt-1 mb-4">Since ${formatDate(data.acquired_date)}</div>
            <div class="d-flex justify-content-center gap-3">
                <div class="stat-pill text-center">
                    <div class="stat-value">${totalDays}</div>
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
    section.innerHTML =
      '<span class="holder-since">Could not load belt holder</span>';
  }
}

async function loadTodaysGames() {
  const container = document.getElementById("games-container");
  const dateLabel = document.getElementById("games-date");
  try {
    const res = await fetch("/api/games/today");
    const data = await res.json();
    const games = data.data ?? [];

    const today = new Date().toLocaleDateString("en-US", {
      weekday: "long",
      month: "long",
      day: "numeric",
    });
    dateLabel.textContent = today;

    if (games.length === 0) {
      container.innerHTML =
        '<div class="col-12 text-secondary">No games scheduled today.</div>';
      return;
    }

    container.innerHTML = games.map((game) => buildGameCard(game)).join("");
  } catch (e) {
    container.innerHTML =
      '<div class="col-12 text-danger">Failed to load games.</div>';
  }
}

function teamLogo(abbreviation, large = false) {
  if (!abbreviation) return "";
  const abbr = abbreviation.toLowerCase();
  return `<img src="/img/team-logos/${abbr}.png" alt="${abbreviation}" class="team-logo${large ? " team-logo--large" : ""}">`;
}

function buildGameCard(game) {
  const isBeltGame = game.belt_on_the_line;
  const homeAbbr = game.home_team?.abbreviation ?? "";
  const awayAbbr = game.visitor_team?.abbreviation ?? "";
  const status = game.status ?? "";
  const state = gameState(status);

  const beltBadge = isBeltGame
    ? `<div class="mb-2"><span class="belt-badge">🏆 BELT ON THE LINE</span></div>`
    : "";

  let bottomHtml = "";
  if (state === "scheduled") {
    bottomHtml = `<div class="game-status mt-2">${formatScheduledTime(status)}</div>`;
  } else {
    const homeScore = game.home_team_score ?? 0;
    const awayScore = game.visitor_team_score ?? 0;
    const winnerHome = state === "final" && homeScore > awayScore;
    const winnerAway = state === "final" && awayScore > homeScore;
    bottomHtml = `
            <div class="d-flex justify-content-center align-items-center gap-3 mt-2">
                <span class="score ${winnerAway ? "score--winner" : ""}">${awayScore}</span>
                <span class="score-dash">-</span>
                <span class="score ${winnerHome ? "score--winner" : ""}">${homeScore}</span>
            </div>
            <div class="game-status mt-1">${formatLiveStatus(state, game.period, game.time)}</div>`;
  }

  return `
        <div class="col-xs-12 col-sm-6 col-lg-3">
            <div class="game-card p-4 h-100 text-center ${isBeltGame ? "belt-game" : ""} ${state === "live" ? "game-card--live" : ""}">
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
                ${bottomHtml}
            </div>
        </div>
    `;
}

function gameState(status) {
  if (!status) return "scheduled";
  if (/^\d{4}-\d{2}-\d{2}T/.test(status)) return "scheduled";
  if (status === "Final") return "final";
  return "live";
}

function formatScheduledTime(status) {
  if (!status || !/^\d{4}-\d{2}-\d{2}T/.test(status)) return status || "TBD";
  return new Date(status).toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    timeZone: "America/New_York",
  });
}

function formatLiveStatus(state, period, time) {
  if (state === "final") return "FINAL";
  if (!period) return "LIVE";

  return time;
}

function formatDate(dateStr) {
  if (!dateStr) return "";
  const [y, m, d] = dateStr.split("-");
  return new Date(y, m - 1, d).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

async function loadBeltHistory() {
  const container = document.getElementById("belt-history");
  try {
    const res = await fetch("/api/belt/history");
    const data = await res.json();
    if (!data.length) {
      container.innerHTML = "<p>No history yet.</p>";
      return;
    }

    // Reverse so oldest is first (left to right)
    const history = [...data].reverse();

    container.innerHTML = `
            <div class="belt-timeline-vertical">
                ${history
                  .map((entry, i) => {
                    const isCurrent = !entry.lost_date;
                    const abbr = entry.team_name?.toLowerCase() ?? "";
                    const defenses = entry.defense_count ?? 0;
                    const days = entry.days_held ?? 0;
                    const side = i % 2 === 0 ? "left" : "right";
                    return `
                        <div class="vtl-item vtl-item--${side}">
                            <div class="vtl-card ${isCurrent ? "vtl-card--current" : ""}">
                                ${teamLogo(abbr)}
                                <div class="timeline-abbr">${entry.team_name}</div>
                                <div class="timeline-date">${formatDate(entry.acquired_date)}</div>
                                <div class="timeline-defenses">${days} day${days !== 1 ? "s" : ""}</div>
                                <div class="timeline-defenses">${defenses} defense${defenses !== 1 ? "s" : ""}</div>
                                ${isCurrent ? '<div class="timeline-current-badge">Current</div>' : ""}
                            </div>
                            <div class="vtl-dot ${isCurrent ? "vtl-dot--current" : ""}"></div>
                        </div>
                    `;
                  })
                  .join("")}
            </div>
        `;
  } catch (e) {
    container.innerHTML =
      '<p class="text-danger">Failed to load belt history.</p>';
  }
}

async function loadBeltLeaders() {
    const container = document.getElementById('belt-leaders');
    try {
        const res = await fetch('/api/belt/leaders');
        const data = await res.json();
        if (!data.length) {
            container.innerHTML = '<p class="text-secondary">No data yet.</p>';
            return;
        }

        const byDays     = [...data].sort((a, b) => b.total_days - a.total_days).slice(0, 3);
        const byDefenses = [...data].sort((a, b) => b.total_defenses - a.total_defenses).slice(0, 3);
        const byReigns   = [...data].sort((a, b) => b.total_reigns - a.total_reigns).slice(0, 3);

        const buildList = (teams, valueKey, label) => `
            <div class="col-md-4">
                <div class="leaders-card">
                    <div class="leaders-title">${label}</div>
                    ${teams.map((t, i) => `
                        <div class="leaders-row">
                            <span class="leaders-rank">${i + 1}</span>
                            ${teamLogo(t.team_name.toLowerCase())}
                            <span class="leaders-name">${t.team_name}</span>
                            <span class="leaders-value">${t[valueKey]}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        container.innerHTML =
            buildList(byDays,     'total_days',     'Days Held') +
            buildList(byDefenses, 'total_defenses', 'Defenses') +
            buildList(byReigns,   'total_reigns',   'Reigns');

    } catch (e) {
        container.innerHTML = '<p class="text-danger">Failed to load leaders.</p>';
    }
}

loadBeltHolder();
loadTodaysGames();
loadBeltLeaders();
loadBeltHistory();
