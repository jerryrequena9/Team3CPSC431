-- =====================================================
-- HW3 DATABASE SETUP
-- =====================================================

-- =====================================================
-- DROP AND CREATE DATABASE
-- =====================================================
DROP DATABASE IF EXISTS hw3;

CREATE DATABASE IF NOT EXISTS hw3;
USE hw3;

CREATE TABLE Team (
  team_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  city VARCHAR(100) NOT NULL,
  conference ENUM('NFC', 'AFC') NOT NULL,
  division ENUM('East', 'North', 'South', 'West') NOT NULL,

  CONSTRAINT fk_stadium
    FOREIGN KEY(team_id)
    REFERENCES Stadium(stadium_id)
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
    'LS',

  ) NOT NULL,
  -- status <- this is in the ERD but I'm not sure what it means

  CONSTRAINT fk_team
    FOREIGN KEY(team_id)
    REFERENCES Team(player_id)
);
-- =====================================================
--  CREATE TeamRoster TABLE
-- Same structure as HW2
-- =====================================================
CREATE TABLE TeamRoster (
    ID INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    Name_First VARCHAR(100),
    Name_Last VARCHAR(150) NOT NULL,
    Street VARCHAR(250),
    City VARCHAR(100),
    State VARCHAR(100),
    Country VARCHAR(100),
    ZipCode CHAR(10),

    CONSTRAINT chk_zipcode
        CHECK (
            ZipCode IS NULL OR
            ZipCode REGEXP '^(?!0{5})(?!9{5})\\d{5}(-(?!0{4})(?!9{4})\\d{4})?$'
        ),

    UNIQUE KEY uq_name (Name_Last, Name_First),
    INDEX idx_lastname (Name_Last)
);

-- =====================================================
-- INSERT TEAM ROSTER DATA
-- =====================================================
INSERT INTO TeamRoster
(ID, Name_First, Name_Last, Street, City, State, Country, ZipCode)
VALUES
(100, 'Donald', 'Duck', '1313 S. Harbor Blvd.', 'Anaheim', 'CA', 'USA', '92808-3232'),
(101, 'Daisy', 'Duck', '1180 Seven Seas Dr.', 'Lake Buena Vista', 'FL', 'USA', '32830'),
(107, 'Mickey', 'Mouse', '1313 S. Harbor Blvd.', 'Anaheim', 'CA', 'USA', '92808-3232'),
(111, 'Pluto', 'Dog', '1313 S. Harbor Blvd.', 'Anaheim', 'CA', 'USA', '92808-3232'),
(118, 'Scrooge', 'McDuck', '1180 Seven Seas Dr.', 'Lake Buena Vista', 'FL', 'USA', '32830'),
(119, 'Huebert (Huey)', 'Duck', '1110 Seven Seas Dr.', 'Lake Buena Vista', 'FL', 'USA', '32830'),
(123, 'Deuteronomy (Dewey)', 'Duck', '1110 Seven Seas Dr.', 'Lake Buena Vista', 'FL', 'USA', '32830'),
(128, 'Louie', 'Duck', '1110 Seven Seas Dr.', 'Lake Buena Vista', 'FL', 'USA', '32830'),
(129, 'Phooey', 'Duck', '1-1 Maihama', 'Urayasu', 'Chiba Prefecture', 'Disney Tokyo Japan', NULL),
(131, 'Della', 'Duck', '77700 Boulevard du Parc', 'Coupvray', NULL, 'Disney Paris France', NULL);

-- =====================================================
-- CREATE Statistics TABLE
-- =====================================================
CREATE TABLE Statistics (
    ID INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    Player INT UNSIGNED NOT NULL,
    PlayingTimeMin TINYINT UNSIGNED DEFAULT 0,
    PlayingTimeSec TINYINT UNSIGNED DEFAULT 0,
    Points TINYINT UNSIGNED DEFAULT 0,
    Assists TINYINT UNSIGNED DEFAULT 0,
    Rebounds TINYINT UNSIGNED DEFAULT 0,

    CONSTRAINT fk_player
        FOREIGN KEY (Player)
        REFERENCES TeamRoster(ID)
        ON DELETE CASCADE,

    CONSTRAINT chk_playing_time
        CHECK (
            (PlayingTimeMin BETWEEN 0 AND 40) AND
            (PlayingTimeSec BETWEEN 0 AND 59) AND
            (PlayingTimeMin * 60 + PlayingTimeSec BETWEEN 1 AND 2400)
        )
);

-- =====================================================
-- INSERT STATISTICS DATA
-- =====================================================
INSERT INTO Statistics
(ID, Player, PlayingTimeMin, PlayingTimeSec, Points, Assists, Rebounds)
VALUES
(17, 100, 35, 12, 47, 11, 21),
(18, 107, 13, 22, 13, 1, 3),
(19, 111, 10, 0, 18, 2, 4),
(20, 128, 2, 45, 9, 1, 2),
(21, 107, 15, 39, 26, 3, 7),
(22, 100, 29, 47, 27, 9, 8);

-- =====================================================
-- USERS TABLE
-- Stores application login info
-- Passwords must be hashed with password_hash() in PHP
-- =====================================================
CREATE TABLE Users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('manager','coach','player') NOT NULL,
    roster_id INT UNSIGNED NULL,

    CONSTRAINT fk_user_player
        FOREIGN KEY (roster_id)
        REFERENCES TeamRoster(ID)
        ON DELETE SET NULL
);

-- =====================================================
-- SAMPLE LOGIN USERS
--
-- =====================================================
INSERT INTO Users (username, password_hash, role, roster_id)
VALUES
('manager1', '$2y$10$v3gvhGXLVjpagNUanwY5COgqyh7qFnWvmL6URxn5uoTB.1QJuk3fa', 'manager', NULL),
('coach1', '$2y$10$Z15qzwy1lQkrOFHUI3Tm0.wiWw/TyDh7wU9PdJsf/Abm3yatjwdSe', 'coach', NULL),
('donald', '$2y$10$AHNs4usxpDOrnix1qi5sPe19ox94idxwQrRmWMuDPA6qV9q9UVT6O', 'player', 100),
('mickey', '$2y$10$BdprHaCb13VVOEIKRu5RJuL8/B1hhgQdmrg8kmHFyxoild2Z0txRi', 'player', 107),
('louie', '$2y$10$kxzs.J/u3BcprwPz3YW7ZecW5l8ZG2tzrqiealwSTT7Ya/jGKh9K2', 'player', 128);

-- =====================================================
-- DROP EXISTING MYSQL USERS
-- =====================================================
DROP USER IF EXISTS 'manager_user'@'localhost';
DROP USER IF EXISTS 'coach_user'@'localhost';
DROP USER IF EXISTS 'player_user'@'localhost';
DROP USER IF EXISTS 'auth_user'@'localhost';

-- =====================================================
-- CREATE MYSQL USERS
-- =====================================================
CREATE USER 'manager_user'@'localhost' IDENTIFIED BY 'manager_password';
CREATE USER 'coach_user'@'localhost' IDENTIFIED BY 'coach_password';
CREATE USER 'player_user'@'localhost' IDENTIFIED BY 'player_password';
CREATE USER 'auth_user'@'localhost' IDENTIFIED BY 'auth_password';

-- =====================================================
-- ROLE PRIVILEGES
-- auth_user is only for reading Users during login
-- =====================================================
GRANT SELECT
ON hw3.Users
TO 'auth_user'@'localhost';

-- Manager can modify TeamRoster and Statistics data,
-- but cannot create/drop/alter tables
GRANT SELECT, INSERT, UPDATE, DELETE
ON hw3.TeamRoster
TO 'manager_user'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE
ON hw3.Statistics
TO 'manager_user'@'localhost';

-- Coach can view roster and work with statistics
GRANT SELECT
ON hw3.TeamRoster
TO 'coach_user'@'localhost';

-- coach can only make corrections to, but not add or delete, a player’s statistics
GRANT SELECT, UPDATE
ON hw3.Statistics
TO 'coach_user'@'localhost';

-- Player can view all roster/statistics rows,
-- but PHP enforces that they may only CRUD their own statistics
GRANT SELECT
ON hw3.TeamRoster
TO 'player_user'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE
ON hw3.Statistics
TO 'player_user'@'localhost';

FLUSH PRIVILEGES;
