async function loadBeltHolder() {
  const section = document.getElementById("belt-holder-section");
  try {
    const res = await fetch("/api/belt/holder");
    const data = await res.json();
    if (!res.ok) {
      section.innerHTML =
        '<span class="holder-meta">Belt holder unknown</span>';
      return;
    }
    const abbr =
      data.abbreviation?.toLowerCase() ?? data.team_name?.toLowerCase() ?? "";
    section.innerHTML = `
            <div class="crown mb-2">🏆</div>
            <div class="holder-label mb-2">Current Belt Holder</div>
            ${teamLogo(abbr, true)}
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
  const state = gameState(status, game.period);

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
        <div class="col-sm-6 col-lg-3">
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

function gameState(status, period) {
  if (!status) return "scheduled";
  if (/^\d{4}-\d{2}-\d{2}T/.test(status)) return "scheduled";
  if (status === "Final") return "final";
  return "live";
}

function formatScheduledTime(status) {
  if (!status || !/^\d{4}-\d{2}-\d{2}T/.test(status)) return status || "TBD";
  return (
    new Date(status).toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
      timeZone: "America/New_York",
    }) + " ET"
  );
}

function formatLiveStatus(state, period, time) {
  if (state === "final") return "FINAL";
  if (!period) return "LIVE";
  // const quarter = period <= 4 ? `Q${period}` : `OT${period > 5 ? period - 4 : ''}`;
  // return time ? `${quarter} · ${time}` : quarter;
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

loadBeltHolder();
loadTodaysGames();
