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
('Lambeau Field', 'Green Bay'),
('Arrowhead Stadium', 'Kansas City'),
('Levis Stadium', 'Santa Clara'),
('MetLife Stadium', 'East Rutherford'),
('Soldier Field', 'Chicago');

-- Teams
INSERT INTO Team (name, stadium_id, conference, division, city) VALUES
('Los Angeles Rams', 1, 'NFC', 'West', 'Los Angeles'),
('New England Patriots', 2, 'AFC', 'East', 'Foxborough'),
('Dallas Cowboys', 3, 'NFC', 'East', 'Dallas'),
('Green Bay Packers', 4, 'NFC', 'North', 'Green Bay'),
('Kansas City Chiefs', 5, 'AFC', 'West', 'Kansas City'),
('San Francisco 49ers', 6, 'NFC', 'West', 'Santa Clara'),
('New York Giants', 7, 'NFC', 'East', 'East Rutherford'),
('Chicago Bears', 8, 'NFC', 'North', 'Chicago');

-- Seasons
INSERT INTO Season (champion, year) VALUES
(4, 2024),
(1, 2025);

-- Games
INSERT INTO Game (home_team_id, away_team_id, stadium_id, season_id, week, date, home_score, away_score) VALUES
(1, 2, 1, 2, 1, '2025-09-12', 24, 17),
(2, 3, 2, 2, 2, '2025-09-13', 28, 24),
(3, 4, 3, 2, 3, '2025-09-14', 31, 28),
(5, 6, 5, 2, 1, '2025-09-12', 35, 20),
(7, 8, 7, 2, 1, '2025-09-12', 14, 21),
(6, 1, 6, 2, 2, '2025-09-19', 27, 24),
(4, 5, 4, 2, 2, '2025-09-19', 17, 30),
(8, 2, 8, 2, 3, '2025-09-20', 10, 33),

(1, 2, 1, 1, 1, '2024-10-12',  1, 12),
(2, 3, 2, 1, 2, '2024-10-13',  0, 30),
(3, 4, 3, 1, 3, '2024-10-14',  9, 21),
(5, 6, 5, 1, 1, '2024-10-12', 28, 14),
(7, 8, 7, 1, 1, '2024-10-12', 20, 17),
(6, 3, 6, 1, 2, '2024-10-19', 24, 10),
(4, 2, 4, 1, 3, '2024-10-20', 35,  7),
(8, 5, 8, 1, 3, '2024-10-20', 13, 40);

-- Players
INSERT INTO Player (first_name, last_name, position, status) VALUES
('Matthew', 'Stafford',  'QB', 'Active'),
('Aaron',   'Donald',    'DE', 'Active'),
('Tom',     'Brady',     'QB', 'Inactive'),
('Davante', 'Adams',     'WR', 'Active'),
('Patrick', 'Mahomes',   'QB', 'Active'),
('Travis',  'Kelce',     'TE', 'Active'),
('Brock',   'Purdy',     'QB', 'Active'),
('Deebo',   'Samuel',    'WR', 'Active'),
('Saquon',  'Barkley',   'RB', 'Active'),
('Justin',  'Fields',    'QB', 'Active'),
('Cooper',  'Kupp',      'WR', 'Active'),
('Micah',   'Parsons',   'LB', 'Active');

-- Player_Team
INSERT INTO Player_Team (player_id, team_id, start_date) VALUES
(1,  1, '2017-01-01'),
(2,  1, '2014-01-01'),
(3,  2, '2000-01-01'),
(4,  4, '2018-01-01'),
(5,  5, '2018-01-01'),
(6,  5, '2013-01-01'),
(7,  6, '2022-01-01'),
(8,  6, '2019-01-01'),
(9,  7, '2018-01-01'),
(10, 8, '2021-01-01'),
(11, 1, '2017-01-01'),
(12, 3, '2021-01-01');

-- Stats
INSERT INTO Stat (player_id, game_id, touchdowns, passing_yards, rushing_yards, receiving_yards, tackles, interceptions) VALUES
(1,  1, 3, 310,  22,   0, 0, 0),
(2,  1, 0,   0,   0,   0, 7, 1), 
(11, 1, 1,   0,   0,  95, 0, 0), 
(3,  1, 1, 198,  10,   0, 0, 0),

(3,  2, 2, 230,  20,   0, 0, 1),
(12, 2, 0,   0,   0,   0, 9, 0),

(12, 3, 0,   0,   0,   0, 6, 0),
(4,  3, 2,   0,   5, 110, 0, 0),

(5,  4, 4, 385,  35,   0, 0, 0),
(6,  4, 2,   0,   0, 120, 0, 0),
(7,  4, 1, 280,  18,   0, 0, 0),
(8,  4, 1,   0,   0,  88, 0, 0),

(9,  5, 1,   0, 112,   0, 0, 0),
(10, 5, 1, 210,  45,   0, 0, 0),

(7,  6, 2, 265,  20,   0, 0, 0),
(8,  6, 1,   0,  10,  75, 0, 0),
(1,  6, 2, 290,  15,   0, 0, 0),
(11, 6, 1,   0,   0,  88, 0, 0),

(4,  7, 1,   0,   0,  70, 0, 0),
(5,  7, 3, 340,  28,   0, 0, 0),
(6,  7, 1,   0,   0,  95, 0, 0),

(10, 8, 0, 185,  30,   0, 0, 0),
(3,  8, 3, 275,   5,   0, 0, 0),

(1,  9, 0, 155,  10,   0, 0, 0),
(3,  9, 1, 200,   8,   0, 0, 0),

(3,  10, 0, 178,  12,   0, 0, 1), 
(12, 10, 0,   0,   0,   0, 8, 0),

(12, 11, 0,   0,   0,   0, 5, 1),
(4,  11, 3,   0,   0, 130, 0, 0),

(5,  12, 3, 350,  40,   0, 0, 0),
(6,  12, 1,   0,   0, 100, 0, 0),
(7,  12, 1, 240,  12,   0, 0, 0),

(9,  13, 2,   0,  98,   0, 0, 0),
(10, 13, 1, 195,  40,   0, 0, 0),

(7,  14, 2, 255,  18,   0, 0, 0),
(12, 14, 0,   0,   0,   0, 7, 1),

(4,  15, 2,   0,   0, 115, 0, 0),
(3,  15, 1, 210,   5,   0, 0, 0),

(10, 16, 0, 170,  28,   0, 0, 0),
(5,  16, 4, 400,  30,   0, 0, 0),
(6,  16, 2,   0,   0, 110, 0, 0);

-- User Accounts
INSERT INTO UserAccount (username, password_hash, email, role_id) VALUES
('manager1', '$2y$10$J8QZ4j1Q8DAf.pM28SXzUOS1YBa5p29ysl6JZdYf9rWXUsTXinGHa', 'manager@football.com', 4),
('coach1', '$2y$10$nYkrRSdckNDGsb2w/A6XoO0YBCEn.ywEBXYM9aVIUCchj7YdpDOtm', 'coach@football.com', 3),
('player1', '$2y$10$/sywK/C63wQrQRZKyb2X8e6/kMkL1d6i/8n7nM7Fu1eL7/HRFD6yO', 'player@football.com', 2),
('fan1', '$2y$10$Ow2wuFWyCoUtaCXNuxIaT.D.nYXnF88WO8T8.s0E7440N921NLmEe', 'fan@football.com', 1);