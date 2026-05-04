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
INSERT INTO UserAccount (username, password_hash, email, role_id) VALUES
('manager1', '$2y$10$J8QZ4j1Q8DAf.pM28SXzUOS1YBa5p29ysl6JZdYf9rWXUsTXinGHa', 'manager@football.com', 4),
('coach1', '$2y$10$nYkrRSdckNDGsb2w/A6XoO0YBCEn.ywEBXYM9aVIUCchj7YdpDOtm', 'coach@football.com', 3),
('player1', '$2y$10$/sywK/C63wQrQRZKyb2X8e6/kMkL1d6i/8n7nM7Fu1eL7/HRFD6yO', 'player@football.com', 2),
('fan1', '$2y$10$Ow2wuFWyCoUtaCXNuxIaT.D.nYXnF88WO8T8.s0E7440N921NLmEe', 'fan@football.com', 1);