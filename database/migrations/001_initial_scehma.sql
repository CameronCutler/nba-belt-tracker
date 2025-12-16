-- NBA Teams table
-- Stores all active NBA teams from the API
CREATE TABLE IF NOT EXISTS teams (
                                     id INTEGER PRIMARY KEY,
                                     name TEXT NOT NULL,
                                     full_name TEXT NOT NULL,
                                     abbreviation TEXT NOT NULL,
                                     city TEXT NOT NULL,
                                     conference TEXT NOT NULL,
                                     division TEXT NOT NULL,
                                     created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
    );

-- Belt History table
-- Tracks every time the belt changes hands
CREATE TABLE IF NOT EXISTS belt_history (
                                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                                            team_id INTEGER NOT NULL,
                                            acquired_date TEXT NOT NULL,  -- ISO 8601 format (YYYY-MM-DD)
                                            lost_date TEXT,                -- NULL means current holder
                                            game_id INTEGER,               -- Reference to game where belt transferred
                                            defense_count INTEGER DEFAULT 0,  -- How many games defended before losing
                                            notes TEXT,                    -- Optional notes about the transfer
                                            created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (team_id) REFERENCES teams(id)
    );

-- Games table (optional but useful)
-- Cache of games where belt was involved
CREATE TABLE IF NOT EXISTS games (
                                     id INTEGER PRIMARY KEY,
                                     game_date TEXT NOT NULL,
                                     home_team_id INTEGER NOT NULL,
                                     away_team_id INTEGER NOT NULL,
                                     home_score INTEGER,
                                     away_score INTEGER,
                                     season INTEGER NOT NULL,
                                     belt_involved INTEGER DEFAULT 0,  -- Boolean: was belt on the line?
                                     winner_team_id INTEGER,
                                     created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (home_team_id) REFERENCES teams(id),
    FOREIGN KEY (away_team_id) REFERENCES teams(id),
    FOREIGN KEY (winner_team_id) REFERENCES teams(id)
    );

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_belt_current ON belt_history(lost_date) WHERE lost_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_belt_team ON belt_history(team_id);
CREATE INDEX IF NOT EXISTS idx_belt_acquired ON belt_history(acquired_date);
CREATE INDEX IF NOT EXISTS idx_games_date ON games(game_date);
CREATE INDEX IF NOT EXISTS idx_games_teams ON games(home_team_id, away_team_id);
CREATE INDEX IF NOT EXISTS idx_games_belt ON games(belt_involved) WHERE belt_involved = 1;
CREATE INDEX IF NOT EXISTS idx_teams_conference ON teams(conference);