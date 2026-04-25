DROP DATABASE IF EXISTS football;

CREATE DATABASE IF NOT EXISTS football;
USE football;

CREATE TABLE Stadium (
  stadium_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL
);

CREATE TABLE Team (
  team_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  stadium_id INT UNSIGNED,
  name VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  conference ENUM('NFC', 'AFC') NOT NULL,
  division ENUM('East', 'North', 'South', 'West') NOT NULL,

  CONSTRAINT fk_stadium
    FOREIGN KEY(stadium_id)
    REFERENCES Stadium(stadium_id)
    ON DELETE SET NULL
);

CREATE TABLE Player (
  player_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  position ENUM(
    -- OFFENSIVE POSITIONS
    -- quarterback
    'QB',
    -- running back
    'RB',
    -- fullback
    'FB',
    -- left tackle
    'LT',
    -- left guard
    'LG',
    -- center
    'C',
    -- right guard
    'RG',
    -- right tackle
    'RT',
    -- wide receiver
    'WR',
    -- tight end
    'TE',

    -- DEFENSIVE POSITIONS
    -- defensive end
    'DE',
    -- defensive tackle
    'DT',
    -- outside linebacker
    'OLB',
    -- inside linebacker
    'ILB',
    -- middle linebacker
    'MLB',
    -- cornerback
    'CB',
    -- strong safety
    'SS',
    -- free safety
    'FS',

    -- SPECIAL TEAMS
    -- kicker
    'K',
    -- punter
    'P',
    -- kick returner
    'KR',
    -- punt returner
    'PR',
    -- long snapper
    'LS'
  ) NOT NULL
  -- status <- this is in the ERD but I'm not sure what it means
);

CREATE TABLE Player_Team (
  player_id INT UNSIGNED,
  team_id INT UNSIGNED,
  start_date DATE,
  end_date DATE,

  CONSTRAINT fk_player
    FOREIGN KEY (player_id)
    REFERENCES Player(player_id)
    ON DELETE CASCADE,

  CONSTRAINT fk_team
    FOREIGN KEY (team_id)
    REFERENCES Team(team_id)
    ON DELETE CASCADE
);

CREATE TABLE Season (
  season_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  year YEAR
);

CREATE TABLE Team_Season (
  team_id INT UNSIGNED,
  season_id INT UNSIGNED,
  wins INT UNSIGNED,
  losses INT UNSIGNED,

  CONSTRAINT fk_team
    FOREIGN KEY (team_id)
    REFERENCES Team(team_id)
    ON DELETE RESTRICT,

  CONSTRAINT fk_season
    FOREIGN KEY (season_id)
    REFERENCES Season(season_id)
    ON DELETE RESTRICT
);

CREATE TABLE Role (
  role_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT
);

CREATE TABLE Coach (
  coach_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  team_id INT UNSIGNED,
  role_id INT UNSIGNED,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,

  CONSTRAINT fk_team
    FOREIGN KEY (team_id)
    REFERENCES Team(team_id)
    ON DELETE SET NULL,

  CONSTRAINT fk_role
    FOREIGN KEY (role_id)
    REFERENCES Role(role_id)
    ON DELETE SET NULL
);

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

  CONSTRAINT fk_home_team
    FOREIGN KEY (home_team_id)
    REFERENCES Team(team_id)
    ON DELETE RESTRICT,

  CONSTRAINT fk_away_team
    FOREIGN KEY (away_team_id)
    REFERENCES Team(team_id)
    ON DELETE RESTRICT,

  CONSTRAINT fk_stadium
    FOREIGN KEY (stadium_id)
    REFERENCES Stadium(stadium_id)
    ON DELETE SET NULL,

  CONSTRAINT fk_season
    FOREIGN KEY (season_id)
    REFERENCES Season(season_id)
    ON DELETE RESTRICT,

  CONSTRAINT CHECK (home_team_id != away_team_id)
);

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

  CONSTRAINT fk_player
    FOREIGN KEY (player_id)
    REFERENCES Player(player_id)
    ON DELETE CASCADE,

  CONSTRAINT fk_game
    FOREIGN KEY (game_id)
    REFERENCES Game(game_id)
    ON DELETE CASCADE
);

CREATE TABLE UserAccount (
  user_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  role_id INT UNSIGNED,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash CHAR(60) NOT NULL,
  email VARCHAR(200) NOT NULL,
  last_login DATE,
  is_active BOOLEAN DEFAULT FALSE,

  CONSTRAINT fk_role
    FOREIGN KEY (role_id)
    REFERENCES Role(role_id)
    ON DELETE SET NULL
);

FLUSH PRIVILEGES;
