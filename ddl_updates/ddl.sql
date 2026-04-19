DROP DATABASE IF EXISTS football;

CREATE DATABASE IF NOT EXISTS football;
USE football;

-- Role Table: Defines the roles in the system (e.g., Admin, Coach, Player, Fan)
CREATE TABLE Role (
  role_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  role_name VARCHAR(100) NOT NULL,  -- "Admin", "Coach", "Player", "Fan"
  role_description TEXT  -- Description of the role (e.g., "Player has access to personal stats")
);

-- UserAccount Table: Stores user login information linked to a role (e.g., Fan, Coach, Player)
CREATE TABLE UserAccount (
  user_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  role_id INT UNSIGNED,  -- Foreign Key to Role
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash CHAR(60) NOT NULL,
  email VARCHAR(200) NOT NULL,
  last_login DATE,
  is_active BOOLEAN DEFAULT FALSE,
  CONSTRAINT fk_role FOREIGN KEY (role_id) REFERENCES Role(role_id) ON DELETE SET NULL
);

-- Player Table: Stores player information, linked to UserAccount for authentication
CREATE TABLE Player (
  player_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED,  -- Foreign Key to UserAccount
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  position ENUM('QB', 'RB', 'WR', ...) NOT NULL,  -- Add positions
  CONSTRAINT fk_user_account FOREIGN KEY (user_id) REFERENCES UserAccount(user_id) ON DELETE CASCADE
);

-- Coach Table: Stores coach information, linked to UserAccount and Team
CREATE TABLE Coach (
  coach_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED,  -- Foreign Key to UserAccount
  team_id INT UNSIGNED,  -- Foreign Key to Team
  role_id INT UNSIGNED,  -- Foreign Key to Role (Coach role)
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  CONSTRAINT fk_user_account FOREIGN KEY (user_id) REFERENCES UserAccount(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_team FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE SET NULL,
  CONSTRAINT fk_role FOREIGN KEY (role_id) REFERENCES Role(role_id) ON DELETE SET NULL
);

-- Fan Table: Stores fan information, linked to UserAccount for viewing access
CREATE TABLE Fan (
  fan_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED,  -- Foreign Key to UserAccount
  CONSTRAINT fk_user_account FOREIGN KEY (user_id) REFERENCES UserAccount(user_id) ON DELETE CASCADE
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
  conference ENUM('NFC', 'AFC') NOT NULL,
  division ENUM('East', 'North', 'South', 'West') NOT NULL,
  CONSTRAINT fk_stadium FOREIGN KEY(stadium_id) REFERENCES Stadium(stadium_id) ON DELETE SET NULL
);

-- Player_Team Table: Links players to teams over time
CREATE TABLE Player_Team (
  player_id INT UNSIGNED,
  team_id INT UNSIGNED,
  start_date DATE,
  end_date DATE,
  CONSTRAINT fk_player FOREIGN KEY (player_id) REFERENCES Player(player_id) ON DELETE CASCADE,
  CONSTRAINT fk_team FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE
);

-- Season Table: Stores season data (e.g., year)
CREATE TABLE Season (
  season_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  year YEAR
);

-- Team_Season Table: Links teams to seasons and tracks wins/losses
CREATE TABLE Team_Season (
  team_id INT UNSIGNED,
  season_id INT UNSIGNED,
  wins INT UNSIGNED,
  losses INT UNSIGNED,
  CONSTRAINT fk_team FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE RESTRICT,
  CONSTRAINT fk_season FOREIGN KEY (season_id) REFERENCES Season(season_id) ON DELETE RESTRICT
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
  weather VARCHAR(50),
  is_playoff BOOLEAN DEFAULT FALSE,
  CONSTRAINT fk_home_team FOREIGN KEY (home_team_id) REFERENCES Team(team_id) ON DELETE RESTRICT,
  CONSTRAINT fk_away_team FOREIGN KEY (away_team_id) REFERENCES Team(team_id) ON DELETE RESTRICT,
  CONSTRAINT fk_stadium FOREIGN KEY (stadium_id) REFERENCES Stadium(stadium_id) ON DELETE SET NULL,
  CONSTRAINT fk_season FOREIGN KEY (season_id) REFERENCES Season(season_id) ON DELETE RESTRICT,
  CONSTRAINT CHECK (home_team_id != away_team_id)  -- Ensures a game cannot have the same home and away team
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
  CONSTRAINT fk_player FOREIGN KEY (player_id) REFERENCES Player(player_id) ON DELETE CASCADE,
  CONSTRAINT fk_game FOREIGN KEY (game_id) REFERENCES Game(game_id) ON DELETE CASCADE
);

FLUSH PRIVILEGES;
