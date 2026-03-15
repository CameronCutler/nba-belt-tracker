<?php

require_once __DIR__ . '/common.php';

echo " NBA Belt Tracker - Seed Teams (Offline)\n";
echo "-----------------------------------------\n\n";


try {
    // Initialize database connection
    $dbPath = db_get_path();
    echo "Database path: {$dbPath}\n";
    echo "Database file exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";

    $pdo = db_init($dbPath);
    echo "Database connection established.\n";

    // Hardcoded team data - All NBA teams from Ball Don't Lie API
    $teams = [
        [
            'id' => 1,
            'name' => 'Hawks',
            'full_name' => 'Atlanta Hawks',
            'abbreviation' => 'ATL',
            'city' => 'Atlanta',
            'conference' => 'East',
            'division' => 'Southeast'
        ],
        [
            'id' => 2,
            'name' => 'Celtics',
            'full_name' => 'Boston Celtics',
            'abbreviation' => 'BOS',
            'city' => 'Boston',
            'conference' => 'East',
            'division' => 'Atlantic'
        ],
        [
            'id' => 3,
            'name' => 'Nets',
            'full_name' => 'Brooklyn Nets',
            'abbreviation' => 'BKN',
            'city' => 'Brooklyn',
            'conference' => 'East',
            'division' => 'Atlantic'
        ],
        [
            'id' => 4,
            'name' => 'Hornets',
            'full_name' => 'Charlotte Hornets',
            'abbreviation' => 'CHA',
            'city' => 'Charlotte',
            'conference' => 'East',
            'division' => 'Southeast'
        ],
        [
            'id' => 5,
            'name' => 'Bulls',
            'full_name' => 'Chicago Bulls',
            'abbreviation' => 'CHI',
            'city' => 'Chicago',
            'conference' => 'East',
            'division' => 'Central'
        ],
        [
            'id' => 6,
            'name' => 'Cavaliers',
            'full_name' => 'Cleveland Cavaliers',
            'abbreviation' => 'CLE',
            'city' => 'Cleveland',
            'conference' => 'East',
            'division' => 'Central'
        ],
        [
            'id' => 7,
            'name' => 'Mavericks',
            'full_name' => 'Dallas Mavericks',
            'abbreviation' => 'DAL',
            'city' => 'Dallas',
            'conference' => 'West',
            'division' => 'Southwest'
        ],
        [
            'id' => 8,
            'name' => 'Nuggets',
            'full_name' => 'Denver Nuggets',
            'abbreviation' => 'DEN',
            'city' => 'Denver',
            'conference' => 'West',
            'division' => 'Northwest'
        ],
        [
            'id' => 9,
            'name' => 'Pistons',
            'full_name' => 'Detroit Pistons',
            'abbreviation' => 'DET',
            'city' => 'Detroit',
            'conference' => 'East',
            'division' => 'Central'
        ],
        [
            'id' => 10,
            'name' => 'Warriors',
            'full_name' => 'Golden State Warriors',
            'abbreviation' => 'GSW',
            'city' => 'Golden State',
            'conference' => 'West',
            'division' => 'Pacific'
        ],
        [
            'id' => 11,
            'name' => 'Rockets',
            'full_name' => 'Houston Rockets',
            'abbreviation' => 'HOU',
            'city' => 'Houston',
            'conference' => 'West',
            'division' => 'Southwest'
        ],
        [
            'id' => 12,
            'name' => 'Pacers',
            'full_name' => 'Indiana Pacers',
            'abbreviation' => 'IND',
            'city' => 'Indiana',
            'conference' => 'East',
            'division' => 'Central'
        ],
        [
            'id' => 13,
            'name' => 'Clippers',
            'full_name' => 'LA Clippers',
            'abbreviation' => 'LAC',
            'city' => 'LA',
            'conference' => 'West',
            'division' => 'Pacific'
        ],
        [
            'id' => 14,
            'name' => 'Lakers',
            'full_name' => 'Los Angeles Lakers',
            'abbreviation' => 'LAL',
            'city' => 'Los Angeles',
            'conference' => 'West',
            'division' => 'Pacific'
        ],
        [
            'id' => 15,
            'name' => 'Grizzlies',
            'full_name' => 'Memphis Grizzlies',
            'abbreviation' => 'MEM',
            'city' => 'Memphis',
            'conference' => 'West',
            'division' => 'Southwest'
        ],
        [
            'id' => 16,
            'name' => 'Heat',
            'full_name' => 'Miami Heat',
            'abbreviation' => 'MIA',
            'city' => 'Miami',
            'conference' => 'East',
            'division' => 'Southeast'
        ],
        [
            'id' => 17,
            'name' => 'Bucks',
            'full_name' => 'Milwaukee Bucks',
            'abbreviation' => 'MIL',
            'city' => 'Milwaukee',
            'conference' => 'East',
            'division' => 'Central'
        ],
        [
            'id' => 18,
            'name' => 'Timberwolves',
            'full_name' => 'Minnesota Timberwolves',
            'abbreviation' => 'MIN',
            'city' => 'Minnesota',
            'conference' => 'West',
            'division' => 'Northwest'
        ],
        [
            'id' => 19,
            'name' => 'Pelicans',
            'full_name' => 'New Orleans Pelicans',
            'abbreviation' => 'NOP',
            'city' => 'New Orleans',
            'conference' => 'West',
            'division' => 'Southwest'
        ],
        [
            'id' => 20,
            'name' => 'Knicks',
            'full_name' => 'New York Knicks',
            'abbreviation' => 'NYK',
            'city' => 'New York',
            'conference' => 'East',
            'division' => 'Atlantic'
        ],
        [
            'id' => 21,
            'name' => 'Thunder',
            'full_name' => 'Oklahoma City Thunder',
            'abbreviation' => 'OKC',
            'city' => 'Oklahoma City',
            'conference' => 'West',
            'division' => 'Northwest'
        ],
        [
            'id' => 22,
            'name' => 'Magic',
            'full_name' => 'Orlando Magic',
            'abbreviation' => 'ORL',
            'city' => 'Orlando',
            'conference' => 'East',
            'division' => 'Southeast'
        ],
        [
            'id' => 23,
            'name' => '76ers',
            'full_name' => 'Philadelphia 76ers',
            'abbreviation' => 'PHI',
            'city' => 'Philadelphia',
            'conference' => 'East',
            'division' => 'Atlantic'
        ],
        [
            'id' => 24,
            'name' => 'Suns',
            'full_name' => 'Phoenix Suns',
            'abbreviation' => 'PHX',
            'city' => 'Phoenix',
            'conference' => 'West',
            'division' => 'Pacific'
        ],
        [
            'id' => 25,
            'name' => 'Trail Blazers',
            'full_name' => 'Portland Trail Blazers',
            'abbreviation' => 'POR',
            'city' => 'Portland',
            'conference' => 'West',
            'division' => 'Northwest'
        ],
        [
            'id' => 26,
            'name' => 'Kings',
            'full_name' => 'Sacramento Kings',
            'abbreviation' => 'SAC',
            'city' => 'Sacramento',
            'conference' => 'West',
            'division' => 'Pacific'
        ],
        [
            'id' => 27,
            'name' => 'Spurs',
            'full_name' => 'San Antonio Spurs',
            'abbreviation' => 'SAS',
            'city' => 'San Antonio',
            'conference' => 'West',
            'division' => 'Southwest'
        ],
        [
            'id' => 28,
            'name' => 'Raptors',
            'full_name' => 'Toronto Raptors',
            'abbreviation' => 'TOR',
            'city' => 'Toronto',
            'conference' => 'East',
            'division' => 'Atlantic'
        ],
        [
            'id' => 29,
            'name' => 'Jazz',
            'full_name' => 'Utah Jazz',
            'abbreviation' => 'UTA',
            'city' => 'Utah',
            'conference' => 'West',
            'division' => 'Northwest'
        ],
        [
            'id' => 30,
            'name' => 'Wizards',
            'full_name' => 'Washington Wizards',
            'abbreviation' => 'WAS',
            'city' => 'Washington',
            'conference' => 'East',
            'division' => 'Southeast'
        ]
    ];

    echo "Seeding " . count($teams) . " teams into the database...\n";

    // Insert teams into database
    $sql = "INSERT OR IGNORE INTO teams (id, name, full_name, abbreviation, city, conference, division) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    foreach ($teams as $team) {
        $stmt->execute([
            $team['id'],
            $team['name'],
            $team['full_name'],
            $team['abbreviation'],
            $team['city'],
            $team['conference'],
            $team['division']
        ]);
    }

    echo "Successfully seeded " . count($teams) . " teams into the database.\n";
    echo "OKC Thunder (ID: 21) is included as the initial belt holder.\n";

} catch (Exception $e) {
    echo "Error seeding teams: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nTeam seeding completed.\n";