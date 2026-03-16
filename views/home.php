<!DOCTYPE html>
<html>
<head>
    <title>NBA Belt Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
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

<div class="hero py-5">
    <div class="container text-center">
        <h1 class="display-4 fb-bold">NBA Championship Belt Tracker</h1>
        <img src="/img/nba_larryO_belt.png" alt="Championship Belt" class="img-fluid mb-4" style="max-height: 220px;">
    </div>
</div>

<div class="container py-5">
    <h2 class="mb-3">Test Endpoints</h2>
    <div class="endpoint">
        GET <a href="/api/teams">/api/teams</a> - Get all NBA teams
    </div>
    <div class="endpoint">
        GET <a href="/api/games/today">/api/games/today</a> - Get today's games
    </div>
    <div class="endpoint">
        GET <a href="/api/games/2025-12-10">/api/games/2025-12-10</a> - Get game stats from a specific date (YYYY-mm-dd) (EX. 2025-12-10)
    </div>
    <div class="endpoint">
        GET <a href="/api/games/belt">/api/games/belt</a> - Get all belt games for the current season
    </div>
</div>

</body>
</html>