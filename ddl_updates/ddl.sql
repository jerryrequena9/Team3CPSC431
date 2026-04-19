DROP DATABASE IF EXISTS football;

CREATE DATABASE IF NOT EXISTS football;
USE football;

-- Stadium Table: Stores details about stadiums where teams play.
CREATE TABLE Stadium (
  stadium_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key: unique ID for each stadium.
  name VARCHAR(100) NOT NULL,  -- Name of the stadium.
  city VARCHAR(100) NOT NULL   -- City where the stadium is located.
);

-- Team Table: Stores details about teams, including their home stadium.
CREATE TABLE Team (
  team_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key: unique ID for each team.
  stadium_id INT UNSIGNED,  -- Foreign key: links each team to a stadium.
  name VARCHAR(100) NOT NULL,  -- Name of the team.
  city VARCHAR(100) NOT NULL,  -- City the team is based in.
  conference ENUM('NFC', 'AFC') NOT NULL,  -- Conference the team belongs to.
  division ENUM('East', 'North', 'South', 'West') NOT NULL,  -- Division the team plays in.
  
  CONSTRAINT fk_stadium  -- Foreign key constraint linking team to stadium.
    FOREIGN KEY(stadium_id)
    REFERENCES Stadium(stadium_id)
    ON DELETE SET NULL  -- If a stadium is deleted, set the team's stadium to NULL.
);

-- Player Table: Stores details about players, including their position and status.
CREATE TABLE Player (
  player_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key: unique ID for each player.
  first_name VARCHAR(100) NOT NULL,  -- First name of the player.
  last_name VARCHAR(100) NOT NULL,  -- Last name of the player.
  position ENUM(
    'QB', 'RB', 'FB', 'LT', 'LG', 'C', 'RG', 'RT', 'WR', 'TE',  -- Offensive positions.
    'DE', 'DT', 'OLB', 'ILB', 'MLB', 'CB', 'SS', 'FS', 'K', 'P', 'KR', 'PR', 'LS'  -- Defensive and special teams positions.
  ) NOT NULL  -- Player's position on the team.
);

-- Player_Team Table: Tracks the teams each player has been on over time.
CREATE TABLE Player_Team (
  player_id INT UNSIGNED,  -- Foreign key: links to Player table.
  team_id INT UNSIGNED,  -- Foreign key: links to Team table.
  start_date DATE,  -- Date when the player joined the team.
  end_date DATE,  -- Date when the player left the team.

  CONSTRAINT fk_player  -- Foreign key constraint linking to Player.
    FOREIGN KEY (player_id)
    REFERENCES Player(player_id)
    ON DELETE CASCADE,  -- If a player is deleted, remove their team assignments.

  CONSTRAINT fk_team  -- Foreign key constraint linking to Team.
    FOREIGN KEY (team_id)
    REFERENCES Team(team_id)
    ON DELETE CASCADE  -- If a team is deleted, remove its player assignments.
);

-- Season Table: Stores different seasons for tracking team performance.
CREATE TABLE Season (
  season_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key for each season.
  year YEAR  -- The year of the season.
);

-- Team_Season Table: Tracks the performance (wins/losses) of teams during a specific season.
CREATE TABLE Team_Season (
  team_id INT UNSIGNED,  -- Foreign key: links to Team table.
  season_id INT UNSIGNED,  -- Foreign key: links to Season table.
  wins INT UNSIGNED,  -- Number of wins in the season.
  losses INT UNSIGNED,  -- Number of losses in the season.

  CONSTRAINT fk_team  -- Foreign key constraint linking to Team.
    FOREIGN KEY (team_id)
    REFERENCES Team(team_id)
    ON DELETE RESTRICT,  -- Cannot delete a team if it's linked to a season.

  CONSTRAINT fk_season  -- Foreign key constraint linking to Season.
    FOREIGN KEY (season_id)
    REFERENCES Season(season_id)
    ON DELETE RESTRICT  -- Cannot delete a season if it's linked to a team.
);

-- Role Table: Defines roles that users may have (such as coach, admin).
CREATE TABLE Role (
  role_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key for each role.
  name VARCHAR(100) NOT NULL,  -- Name of the role (e.g., Coach, Admin).
  description TEXT  -- Description of the role's responsibilities.
);

-- Coach Table: Stores information about coaches, linked to both teams and roles.
CREATE TABLE Coach (
  coach_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key: unique ID for each coach.
  team_id INT UNSIGNED,  -- Foreign key: links to Team table.
  role_id INT UNSIGNED,  -- Foreign key: links to Role table (defines the coach's role).
  first_name VARCHAR(100) NOT NULL,  -- First name of the coach.
  last_name VARCHAR(100) NOT NULL,  -- Last name of the coach.

  CONSTRAINT fk_team  -- Foreign key constraint linking to Team.
    FOREIGN KEY (team_id)
    REFERENCES Team(team_id)
    ON DELETE SET NULL,  -- If a team is deleted, set the coach's team to NULL.

  CONSTRAINT fk_role  -- Foreign key constraint linking to Role.
    FOREIGN KEY (role_id)
    REFERENCES Role(role_id)
    ON DELETE SET NULL  -- If a role is deleted, set the coach's role to NULL.
);

-- Game Table: Stores information about games, including the teams, stadium, and scores.
CREATE TABLE Game (
  game_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key: unique ID for each game.
  season_id INT UNSIGNED NOT NULL,  -- Foreign key: links to Season table.
  home_team_id INT UNSIGNED NOT NULL,  -- Foreign key: links to Team table (home team).
  away_team_id INT UNSIGNED NOT NULL,  -- Foreign key: links to Team table (away team).
  stadium_id INT UNSIGNED,  -- Foreign key: links to Stadium table.
  week INT UNSIGNED,  -- Week of the season when the game occurred.
  date DATE NOT NULL,  -- Date of the game.
  home_score INT NOT NULL,  -- Score of the home team.
  away_score INT NOT NULL,  -- Score of the away team.
  weather VARCHAR(50),  -- Weather conditions during the game.
  is_playoff BOOLEAN DEFAULT FALSE,  -- Whether the game was a playoff.

  CONSTRAINT fk_home_team  -- Foreign key constraint linking to home team.
    FOREIGN KEY (home_team_id)
    REFERENCES Team(team_id)
    ON DELETE RESTRICT,  -- If a home team is deleted, prevent deleting the game.

  CONSTRAINT fk_away_team  -- Foreign key constraint linking to away team.
    FOREIGN KEY (away_team_id)
    REFERENCES Team(team_id)
    ON DELETE RESTRICT,  -- If an away team is deleted, prevent deleting the game.

  CONSTRAINT fk_stadium  -- Foreign key constraint linking to stadium.
    FOREIGN KEY (stadium_id)
    REFERENCES Stadium(stadium_id)
    ON DELETE SET NULL,  -- If a stadium is deleted, set the game's stadium to NULL.

  CONSTRAINT fk_season  -- Foreign key constraint linking to season.
    FOREIGN KEY (season_id)
    REFERENCES Season(season_id)
    ON DELETE RESTRICT,  -- If a season is deleted, prevent deleting the game.

  CONSTRAINT CHECK (home_team_id != away_team_id)  -- Ensures a game cannot have the same home and away team.
);

-- Stat Table: Stores player statistics for each game played.
CREATE TABLE Stat (
  stat_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key: unique ID for each stat.
  player_id INT UNSIGNED NOT NULL,  -- Foreign key: links to Player table.
  game_id INT UNSIGNED NOT NULL,  -- Foreign key: links to Game table.
  touchdowns INT UNSIGNED DEFAULT 0,  -- Number of touchdowns scored by the player.
  passing_yards INT UNSIGNED DEFAULT 0,  -- Passing yards for the player.
  rushing_yards INT UNSIGNED DEFAULT 0,  -- Rushing yards for the player.
  receiving_yards INT UNSIGNED DEFAULT 0,  -- Receiving yards for the player.
  tackles INT UNSIGNED DEFAULT 0,  -- Tackles made by the player.
  interceptions INT UNSIGNED DEFAULT 0,  -- Interceptions made by the player.

  CONSTRAINT fk_player  -- Foreign key constraint linking to Player.
    FOREIGN KEY (player_id)
    REFERENCES Player(player_id)
    ON DELETE CASCADE,  -- If a player is deleted, delete their stats.

  CONSTRAINT fk_game  -- Foreign key constraint linking to Game.
    FOREIGN KEY (game_id)
    REFERENCES Game(game_id)
    ON DELETE CASCADE  -- If a game is deleted, delete all related stats.
);

-- UserAccount Table: Stores user account information, linked to roles.
CREATE TABLE UserAccount (
  user_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,  -- Primary key: unique ID for each user.
  role_id INT UNSIGNED,  -- Foreign key: links to Role table (defines user role).
  username VARCHAR(50) UNIQUE NOT NULL,  -- Unique username for the user.
  password_hash CHAR(60) NOT NULL,  -- Hashed password for authentication.
  email VARCHAR(200) NOT NULL,  -- User's email address.
  last_login DATE,  -- Last login date.
  is_active BOOLEAN DEFAULT FALSE,  -- Whether the user's account is active.

  CONSTRAINT fk_role  -- Foreign key constraint linking to Role.
    FOREIGN KEY (role_id)
    REFERENCES Role(role_id)
    ON DELETE SET NULL  -- If a role is deleted, set the user's role to NULL.
);

FLUSH PRIVILEGES;
