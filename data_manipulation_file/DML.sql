-- Insert Roles
INSERT INTO Role (role_name, description) 
VALUES 
('Admin', 'Full system privileges'),
('Coach', 'Team management privileges'),
('Player', 'Player account access'),
('Fan', 'Read-only access to games and player stats');

-- Insert Mock Stadiums
INSERT INTO Stadium (stadium_name, city)
VALUES 
('SoFi Stadium', 'Los Angeles'),
('Gillette Stadium', 'Foxborough'),
('AT&T Stadium', 'Dallas'),
('Lambeau Field', 'Green Bay');

-- Insert Mock Teams
INSERT INTO Team (team_name, stadium_id, conference, division, city)
VALUES 
('Los Angeles Rams', 1, 'NFC', 'West', 'Los Angeles'),
('New England Patriots', 2, 'AFC', 'East', 'Foxborough'),
('Dallas Cowboys', 3, 'NFC', 'East', 'Dallas'),
('Green Bay Packers', 4, 'NFC', 'North', 'Green Bay');

-- Insert Mock Players
INSERT INTO Player (first_name, last_name, position, status)
VALUES 
('Matthew', 'Stafford', 'QB', 'Active'),
('Aaron', 'Donald', 'DE', 'Active'),
('Tom', 'Brady', 'QB', 'Inactive'),
('Davante', 'Adams', 'WR', 'Active');

-- Insert Mock Games
INSERT INTO Game (home_team_id, away_team_id, stadium_id, season_id, game_date, home_score, away_score)
VALUES 
(1, 2, 1, 1, '2026-09-12', 24, 17),
(2, 3, 2, 1, '2026-09-13', 28, 24),
(3, 4, 3, 1, '2026-09-14', 31, 28);

-- Insert Mock Player Stats
INSERT INTO Player_Game_Stats (player_id, game_id, touchdowns, passing_yards, rushing_yards, receiving_yards)
VALUES 
(1, 1, 3, 280, 50, 25),
(2, 1, 1, 0, 10, 15),
(3, 2, 2, 230, 20, 40),
(4, 3, 1, 200, 25, 45);

-- Insert Mock User Accounts
INSERT INTO UserAccount (username, password_hash, email, role_id, is_active)
VALUES 
('admin1', 'hashed_password_here', 'admin@football.com', 1, TRUE),
('coach1', 'hashed_password_here', 'coach@football.com', 2, TRUE),
('player1', 'hashed_password_here', 'player@football.com', 3, TRUE),
('fan1', 'hashed_password_here', 'fan@football.com', 4, TRUE);
