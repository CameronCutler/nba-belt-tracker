<?php
$offseasonChampion = $_ENV['OFFSEASON_CHAMPION'] ?? 'New York Knicks';
$nextSeasonLabel = $_ENV['NEXT_SEASON_LABEL'] ?? '2026-27';
$nextSeasonStartRaw = $_ENV['NEXT_SEASON_START'] ?? '2026-10-20';
$currentSeasonStartYear = (int) date('Y') - ((int) date('n') < 10 ? 1 : 0);
$currentSeasonLabel = sprintf('%d-%02d', $currentSeasonStartYear, ($currentSeasonStartYear + 1) % 100);

$nextSeasonStartDate = DateTimeImmutable::createFromFormat('Y-m-d', $nextSeasonStartRaw) ?: null;
$today = new DateTimeImmutable('today');
$appJsPath = __DIR__ . '/../public/js/app.js';
$appJsVersion = is_file($appJsPath) ? (string) filemtime($appJsPath) : '1';

$countdownText = 'Tip-off date coming soon';
$seasonBannerTitle = 'Offseason Update';
$nextSeasonStartDisplay = $nextSeasonStartRaw;

if ($nextSeasonStartDate instanceof DateTimeImmutable) {
    $nextSeasonStartDisplay = $nextSeasonStartDate->format('F j, Y');
    $daysUntil = (int) $today->diff($nextSeasonStartDate)->format('%r%a');

    if ($daysUntil <= 0) {
        $seasonBannerTitle = 'Season Update';
    }

    if ($daysUntil > 1) {
        $countdownText = $daysUntil . ' days until opening night';
    } elseif ($daysUntil === 1) {
        $countdownText = '1 day until opening night';
    } elseif ($daysUntil === 0) {
        $countdownText = 'Opening night is today';
    } else {
        $countdownText = 'Season is underway';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>NBA Belt Tracker</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏆</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/styles--main.css">
</head>
<body>

<div class="hero py-5 text-white">
    <div class="container text-center">
        <img src="/img/nba_larryO_belt.png" alt="Championship Belt" class="img-fluid mb-4" style="max-height: 180px;">
        <h1 class="display-5 fw-bold mb-4">NBA Championship Belt Tracker</h1>
        <div class="offseason-banner mx-auto mb-4 text-start">
            <div class="offseason-banner__title">🏀 <?php echo htmlspecialchars($seasonBannerTitle, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="offseason-banner__body"><?php echo htmlspecialchars($offseasonChampion, ENT_QUOTES, 'UTF-8'); ?> are the reigning champions.</div>
            <div class="offseason-banner__meta">
                <?php echo htmlspecialchars($nextSeasonLabel, ENT_QUOTES, 'UTF-8'); ?> season starts <?php echo htmlspecialchars($nextSeasonStartDisplay, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($countdownText, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
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

<div class="container py-5">
    <h2 class="fw-semibold mb-4" style="color:#e5e7eb;">Season Leaders <?php echo htmlspecialchars($currentSeasonLabel, ENT_QUOTES, 'UTF-8'); ?></h2>
    <div id="belt-leaders" class="row g-3">
        <div class="col-12 text-secondary">Loading leaders...</div>
    </div>
    <h2 class="fw-semibold mt-5 mb-4" style="color:#e5e7eb;">Belt History <?php echo htmlspecialchars($currentSeasonLabel, ENT_QUOTES, 'UTF-8'); ?></h2>
    <div id="belt-history" class="text-secondary">Loading history...</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="/js/app.js?v=<?php echo htmlspecialchars($appJsVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>

</body>
</html>
