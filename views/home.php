<!DOCTYPE html>
<html>
<head>
    <title>NBA Belt Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/styles--main.css">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="/js/app.js"></script>

</body>
</html>
