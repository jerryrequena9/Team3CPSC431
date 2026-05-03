DROP   DATABASE IF EXISTS football;
CREATE DATABASE           football;

USE football;

-- =====================================================
-- ROLE & AUTH
-- =====================================================

CREATE TABLE Role (
    role_id     INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(20)  NOT NULL UNIQUE,   -- Manager, Coach, Player, Fan
    description TEXT
);

CREATE TABLE UserAccount (
    user_id       INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    role_id       INT UNSIGNED DEFAULT 1,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash CHAR(60)     NOT NULL,
    email         VARCHAR(200) NOT NULL,
    last_login    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    is_active     BOOLEAN      DEFAULT FALSE,

    FOREIGN KEY (role_id)
        REFERENCES Role(role_id)
        ON DELETE RESTRICT
);

-- =====================================================
-- CORE ENTITIES
-- =====================================================

CREATE TABLE Player (
    player_id  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id    INT UNSIGNED,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    position   VARCHAR(5)   NOT NULL,
    status     VARCHAR(10)  NOT NULL,

    FOREIGN KEY (user_id)
        REFERENCES UserAccount(user_id)
        ON DELETE CASCADE
);

CREATE TABLE Stadium (
    stadium_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    city       VARCHAR(100) NOT NULL
);

CREATE TABLE Team (
    team_id    INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    stadium_id INT UNSIGNED,
    name       VARCHAR(100) NOT NULL,
    city       VARCHAR(100) NOT NULL,
    conference VARCHAR(3)   NOT NULL,   -- NFC / AFC
    division   VARCHAR(5)   NOT NULL,   -- North, South, East, West

    FOREIGN KEY (stadium_id)
        REFERENCES Stadium(stadium_id)
        ON DELETE SET NULL
);

CREATE TABLE Coach (
    coach_id   INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id    INT UNSIGNED,
    team_id    INT UNSIGNED,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,

    FOREIGN KEY (user_id)
        REFERENCES UserAccount(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (team_id)
        REFERENCES Team(team_id)
        ON DELETE SET NULL
);

-- =====================================================
-- RELATIONSHIPS
-- =====================================================

CREATE TABLE Player_Team (
    player_team_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    player_id      INT UNSIGNED,
    team_id        INT UNSIGNED,
    start_date     DATE NOT NULL,
    end_date       DATE,

    FOREIGN KEY (player_id)
        REFERENCES Player(player_id)
        ON DELETE CASCADE,

    FOREIGN KEY (team_id)
        REFERENCES Team(team_id)
        ON DELETE CASCADE,

    CHECK (end_date IS NULL OR end_date >= start_date)
);

-- =====================================================
-- SEASON & GAMES
-- =====================================================

CREATE TABLE Season (
    season_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    champion  INT UNSIGNED,
    year      YEAR,

    FOREIGN KEY (champion)
        REFERENCES Team(team_id)
        ON DELETE CASCADE,

    UNIQUE (year)
);

CREATE TABLE Team_Season (
    team_season_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    team_id        INT UNSIGNED,
    season_id      INT UNSIGNED,
    wins           INT UNSIGNED,
    losses         INT UNSIGNED,

    FOREIGN KEY (team_id)
        REFERENCES Team(team_id)
        ON DELETE RESTRICT,

    FOREIGN KEY (season_id)
        REFERENCES Season(season_id)
        ON DELETE RESTRICT,

    UNIQUE (team_id, season_id)
);

CREATE TABLE Game (
    game_id      INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    season_id    INT UNSIGNED NOT NULL,
    home_team_id INT UNSIGNED NOT NULL,
    away_team_id INT UNSIGNED NOT NULL,
    stadium_id   INT UNSIGNED,
    week         INT UNSIGNED,
    date         DATE NOT NULL,
    home_score   INT NOT NULL,
    away_score   INT NOT NULL,

    FOREIGN KEY (season_id)
        REFERENCES Season(season_id)
        ON DELETE RESTRICT,

    FOREIGN KEY (home_team_id)
        REFERENCES Team(team_id)
        ON DELETE RESTRICT,

    FOREIGN KEY (away_team_id)
        REFERENCES Team(team_id)
        ON DELETE RESTRICT,

    FOREIGN KEY (stadium_id)
        REFERENCES Stadium(stadium_id)
        ON DELETE SET NULL,

    CHECK (home_team_id != away_team_id)
);

-- =====================================================
-- STATS
-- =====================================================

CREATE TABLE Stat (
    stat_id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    player_id       INT UNSIGNED NOT NULL,
    game_id         INT UNSIGNED NOT NULL,
    touchdowns      INT UNSIGNED DEFAULT 0,
    passing_yards   INT UNSIGNED DEFAULT 0,
    rushing_yards   INT UNSIGNED DEFAULT 0,
    receiving_yards INT UNSIGNED DEFAULT 0,
    tackles         INT UNSIGNED DEFAULT 0,
    interceptions   INT UNSIGNED DEFAULT 0,

    FOREIGN KEY (player_id)
        REFERENCES Player(player_id)
        ON DELETE CASCADE,

    FOREIGN KEY (game_id)
        REFERENCES Game(game_id)
        ON DELETE CASCADE,

    UNIQUE (player_id, game_id)
);

-- =====================================================
-- USERS
-- =====================================================

DROP USER IF EXISTS 'Guest'@'localhost';
CREATE USER 'Guest'@'localhost' IDENTIFIED BY 'guest_pass';

DROP USER IF EXISTS 'Fan'@'localhost';
CREATE USER 'Fan'@'localhost' IDENTIFIED BY 'fan_pass';

DROP USER IF EXISTS 'Player'@'localhost';
CREATE USER 'Player'@'localhost' IDENTIFIED BY 'player_pass';

DROP USER IF EXISTS 'Coach'@'localhost';
CREATE USER 'Coach'@'localhost' IDENTIFIED BY 'coach_pass';

DROP USER IF EXISTS 'Manager'@'localhost';
CREATE USER 'Manager'@'localhost' IDENTIFIED BY 'manager_pass';

-- NOTE: Admin is a user but not a role
-- Users cannot register or login as an admin
DROP USER IF EXISTS 'admin'@'localhost';
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin_pass';

-- =====================================================
-- PERMISSIONS
-- =====================================================

-- Admin
GRANT ALL PRIVILEGES ON football.* TO 'admin'@'localhost';

-- Manager
GRANT SELECT, INSERT, DELETE, UPDATE ON football.UserAccount TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Stat        TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Team        TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Player      TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Season      TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Stadium     TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Team_Season TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Player_Team TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Role        TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Coach       TO 'Manager'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON football.Game        TO 'Manager'@'localhost';

-- Fan
GRANT SELECT ON football.Team          TO 'Fan'@'localhost';
GRANT SELECT ON football.Player        TO 'Fan'@'localhost';
GRANT SELECT ON football.Coach         TO 'Fan'@'localhost';
GRANT SELECT ON football.Game          TO 'Fan'@'localhost';
GRANT SELECT ON football.Stat          TO 'Fan'@'localhost';
GRANT SELECT ON football.Season        TO 'Fan'@'localhost';
GRANT SELECT ON football.Stadium       TO 'Fan'@'localhost';
GRANT SELECT ON football.Team_Season   TO 'Fan'@'localhost';
GRANT SELECT ON football.Player_Team   TO 'Fan'@'localhost';

GRANT SELECT, UPDATE ON football.UserAccount TO 'Fan'@'localhost';

-- Player
GRANT SELECT ON football.Team          TO 'Player'@'localhost';
GRANT SELECT ON football.Coach         TO 'Player'@'localhost';
GRANT SELECT ON football.Game          TO 'Player'@'localhost';
GRANT SELECT ON football.Stat          TO 'Player'@'localhost';
GRANT SELECT ON football.Season        TO 'Player'@'localhost';
GRANT SELECT ON football.Stadium       TO 'Player'@'localhost';
GRANT SELECT ON football.Team_Season   TO 'Player'@'localhost';
GRANT SELECT ON football.Player_Team   TO 'Player'@'localhost';
GRANT SELECT ON football.Stat          TO 'Player'@'localhost';

GRANT SELECT, UPDATE ON football.UserAccount TO 'Player'@'localhost';
GRANT SELECT, UPDATE ON football.Player      TO 'Player'@'localhost';

-- Coach
GRANT SELECT ON football.Season        TO 'Coach'@'localhost';
GRANT SELECT ON football.Stadium       TO 'Coach'@'localhost';
GRANT SELECT ON football.Team_Season   TO 'Coach'@'localhost';

GRANT SELECT, UPDATE           ON football.Team         TO 'Coach'@'localhost';
GRANT SELECT, UPDATE           ON football.Player       TO 'Coach'@'localhost';
GRANT SELECT, UPDATE           ON football.UserAccount  TO 'Coach'@'localhost';
GRANT SELECT, UPDATE           ON football.Coach        TO 'Coach'@'localhost';

GRANT SELECT, INSERT, UPDATE   ON football.Player_Team  TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE   ON football.Game         TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE   ON football.Stat         TO 'Coach'@'localhost';

-- Guest
-- Permissions only allow logins, registrations, and password resets
GRANT SELECT                 ON football.Role TO 'Guest'@'localhost';
GRANT SELECT, INSERT, UPDATE ON football.UserAccount TO 'Guest'@'localhost';

-- =====================================================
-- APPLY
-- =====================================================

FLUSH PRIVILEGES;
