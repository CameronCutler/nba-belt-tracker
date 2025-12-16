<!DOCTYPE html>
<html>
<head>
	<title>NBA Belt Tracker</title>
	<style>
      body {
          font-family: system-ui, -apple-system, sans-serif;
          max-width: 800px;
          margin: 50px auto;
          padding: 20px;
          line-height: 1.6;
      }
      h1 { color: #1d4ed8; }
      .endpoint {
          background: #f3f4f6;
          padding: 10px;
          margin: 10px 0;
          border-radius: 5px;
          font-family: monospace;
      }
      .endpoint a {
          color: #1d4ed8;
          text-decoration: none;
      }
      .endpoint a:hover {
          text-decoration: underline;
      }
	</style>
</head>
<body>
<h1>🏀 NBA Belt Tracker</h1>
<p>The championship belt tracker for the NBA season.</p>

<h2>Test Endpoints:</h2>
<div class="endpoint">
	GET <a href="/api/teams">/api/teams</a> - Get all NBA teams
</div>
<div class="endpoint">
	GET <a href="/api/games/today">/api/games/today</a> - Get today's games
</div>
<div class="endpoint">
	GET <a href="/api/games/2025-12-10">/api/games/2025-12-10</a> - Get game stats from a specific date (YYYY-mm-dd) (EX. 2025-12-10)
</div>
</body>
</html>