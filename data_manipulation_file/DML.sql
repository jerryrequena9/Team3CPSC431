USE football;

-- Roles
INSERT INTO Role (name, description) VALUES
('Fan', 'Read-only access to games and player stats'),
('Player', 'Player account access'),
('Coach', 'Team management privileges'),
('Manager', 'Full system privileges (but not to the level of an admin)');

-- Stadiums
INSERT INTO Stadium (name, city) VALUES
('SoFi Stadium', 'Los Angeles'),
('Gillette Stadium', 'Foxborough'),
('AT&T Stadium', 'Dallas'),
('Lambeau Field', 'Green Bay');

-- Teams
INSERT INTO Team (name, stadium_id, conference, division, city) VALUES
('Los Angeles Rams', 1, 'NFC', 'West', 'Los Angeles'),
('New England Patriots', 2, 'AFC', 'East', 'Foxborough'),
('Dallas Cowboys', 3, 'NFC', 'East', 'Dallas'),
('Green Bay Packers', 4, 'NFC', 'North', 'Green Bay');

-- Season (2025 NFL Season)
INSERT INTO Season (champion, year) VALUES
(NULL, 2025);

-- Games
INSERT INTO Game (home_team_id, away_team_id, stadium_id, season_id, week, date, home_score, away_score) VALUES
(1, 2, 1, 1, 1, '2025-09-12', 24, 17),
(2, 3, 2, 1, 2, '2025-09-13', 28, 24),
(3, 4, 3, 1, 3, '2025-09-14', 31, 28);

-- Players
INSERT INTO Player (first_name, last_name, position, status) VALUES
('Matthew', 'Stafford', 'QB', 'Active'),
('Aaron', 'Donald', 'DE', 'Active'),
('Tom', 'Brady', 'QB', 'Inactive'),
('Davante', 'Adams', 'WR', 'Active');

-- Player Stats
INSERT INTO Stat (player_id, game_id, touchdowns, passing_yards, rushing_yards, receiving_yards, tackles, interceptions) VALUES
(1, 1, 3, 280, 50, 25, 0, 0),
(2, 1, 1, 0, 10, 15, 5, 0),
(3, 2, 2, 230, 20, 40, 0, 1),
(4, 3, 1, 200, 25, 45, 2, 0);

-- User Accounts
INSERT INTO UserAccount (username, password_hash, email, role_id, is_active) VALUES
('manager1', '$2y$10$ZtONA431NVTqTVj.PqwBKOomuc39KzejQ0xVqYwvzNcu5BHt/ndT.', 'manager@football.com', 4, TRUE),
('coach1', '$2y$10$qYU6hWcXFpfypBY44MaxXuwudN2X4hm5x9vrT6krjhTzdIenODPHS', 'coach@football.com', 3, TRUE),
('player1', '$2y$10$znkhq0bbHC0a8Jw5XtPH2O3LFzSr36JkYRqNoF37SbO7MX9DAZe9.', 'player@football.com', 2, TRUE),
('fan1', '$2y$10$U8WujKSZDiDUlW4fBBZbcOabc123examplehash', 'fan@football.com', 1, TRUE);
