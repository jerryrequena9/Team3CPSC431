DROP DATABASE IF EXISTS football;

CREATE DATABASE football;
USE football;

-- Role Table: Defines the roles in the system (e.g., Admin, Coach, Player, Fan)
CREATE TABLE Role (
  role_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(20) NOT NULL UNIQUE,  -- "Manager", "Coach", "Player", "Fan"
  description TEXT  -- Description of the role (e.g., "Player has access to personal stats")
);

-- UserAccount Table: Stores user login information linked to a role (e.g., Fan, Coach, Player)
CREATE TABLE UserAccount (
  user_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  role_id INT UNSIGNED DEFAULT 1,  -- Foreign Key to Role, default is Fan
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash CHAR(60) NOT NULL,
  email VARCHAR(200) NOT NULL,
  last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT FALSE,

  FOREIGN KEY (role_id) REFERENCES Role(role_id) ON DELETE RESTRICT
);

-- Player Table: Stores player information, linked to UserAccount for authentication
CREATE TABLE Player (
  player_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED,  -- Foreign Key to UserAccount
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  position VARCHAR(5) NOT NULL,  -- Add positions
  status VARCHAR(10) NOT NULL, -- Active or Inactive

  FOREIGN KEY (user_id) REFERENCES UserAccount(user_id) ON DELETE CASCADE
);

-- Stadium Table: Stores details about stadiums where teams play
CREATE TABLE Stadium (
  stadium_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL
);

-- Team Table: Stores details about teams, linked to Stadium
CREATE TABLE Team (
  team_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  stadium_id INT UNSIGNED,
  name VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  conference VARCHAR(3) NOT NULL, -- NFC or AFC
  division VARCHAR(5) NOT NULL, -- South, North, West, or East

  FOREIGN KEY(stadium_id) REFERENCES Stadium(stadium_id) ON DELETE SET NULL
);

-- Coach Table: Stores coach information, linked to UserAccount and Team
CREATE TABLE Coach (
  coach_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED,  -- Foreign Key to UserAccount
  team_id INT UNSIGNED,  -- Foreign Key to Team
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,

  FOREIGN KEY (user_id) REFERENCES UserAccount(user_id) ON DELETE CASCADE,
  FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE SET NULL
);

-- Player_Team Table: Links players to teams over time
CREATE TABLE Player_Team (
  player_team_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  player_id INT UNSIGNED,
  team_id INT UNSIGNED,
  start_date DATE NOT NULL,
  end_date DATE,

  FOREIGN KEY (player_id) REFERENCES Player(player_id) ON DELETE CASCADE,
  FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE,

  CHECK (end_date IS NULL or end_date >= start_date)
);

-- Season Table: Stores season data (e.g., year)
CREATE TABLE Season (
  season_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  champion INT UNSIGNED,
  year YEAR,

  FOREIGN KEY (champion) REFERENCES Team(team_id) ON DELETE CASCADE,

  UNIQUE (year) -- Seasons correspond to years
);

-- Team_Season Table: Links teams to seasons and tracks wins/losses
CREATE TABLE Team_Season (
  team_season_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  team_id INT UNSIGNED,
  season_id INT UNSIGNED,
  wins INT UNSIGNED,
  losses INT UNSIGNED,

  FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE RESTRICT,
  FOREIGN KEY (season_id) REFERENCES Season(season_id) ON DELETE RESTRICT,

  UNIQUE (team_id, season_id) -- A team participates at most once per season
);

-- Game Table: Stores game details including teams, stadium, scores, and date
CREATE TABLE Game (
  game_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  season_id INT UNSIGNED NOT NULL,
  home_team_id INT UNSIGNED NOT NULL,
  away_team_id INT UNSIGNED NOT NULL,
  stadium_id INT UNSIGNED,
  week INT UNSIGNED,
  date DATE NOT NULL,
  home_score INT NOT NULL,
  away_score INT NOT NULL,

  FOREIGN KEY (home_team_id) REFERENCES Team(team_id) ON DELETE RESTRICT,
  FOREIGN KEY (away_team_id) REFERENCES Team(team_id) ON DELETE RESTRICT,
  FOREIGN KEY (stadium_id) REFERENCES Stadium(stadium_id) ON DELETE SET NULL,
  FOREIGN KEY (season_id) REFERENCES Season(season_id) ON DELETE RESTRICT,

  CHECK (home_team_id != away_team_id)  -- Ensures a game cannot have the same home and away team
);

-- Stat Table: Stores player statistics for each game
CREATE TABLE Stat (
  stat_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  player_id INT UNSIGNED NOT NULL,
  game_id INT UNSIGNED NOT NULL,
  touchdowns INT UNSIGNED DEFAULT 0,
  passing_yards INT UNSIGNED DEFAULT 0,
  rushing_yards INT UNSIGNED DEFAULT 0,
  receiving_yards INT UNSIGNED DEFAULT 0,
  tackles INT UNSIGNED DEFAULT 0,
  interceptions INT UNSIGNED DEFAULT 0,

  FOREIGN KEY (player_id) REFERENCES Player(player_id) ON DELETE CASCADE,
  FOREIGN KEY (game_id) REFERENCES Game(game_id) ON DELETE CASCADE,

  UNIQUE (player_id, game_id) -- A player only has one statistic entry for each game
);

FLUSH PRIVILEGES;

--Role based access users
DROP   USER IF EXISTS 'Fan'@'localhost';
CREATE USER           'Fan'@'localhost' IDENTIFIED BY 'fan_pass';

DROP   USER IF EXISTS 'Player'@'localhost';
CREATE USER           'Player'@'localhost' IDENTIFIED BY 'player_pass';

DROP   USER IF EXISTS 'Coach'@'localhost';
CREATE USER           'Coach'@'localhost' IDENTIFIED BY 'coach_pass';

DROP   USER IF EXISTS 'Manager'@'localhost';
CREATE USER           'Manager'@'localhost' IDENTIFIED BY 'manager_pass';

GRANT select, insert, delete, update ON Roles TO 'Manager'@'localhost';

--Database administration users
DROP USER IF EXISTS 'admin'@'localhost';
Create USER 'admin'@'localhost' IDENTIFIED BY 'admin_pass';

GRANT ALL PRIVILEGES ON football.* to 'admin'@'localhost';
-- TODO: write appropiate grant statements for other users on every table
