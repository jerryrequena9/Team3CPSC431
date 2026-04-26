-- ============================================================
-- ROLE-BASED MYSQL USERS
-- These are database-level users used by the PHP application.
-- The application selects which MySQL user to connect as based
-- on the logged-in user's role.
-- ============================================================

-- Fan database user: read-only access to public football data
DROP USER IF EXISTS 'Fan'@'localhost';
CREATE USER 'Fan'@'localhost' IDENTIFIED BY 'fan_pass';

-- Player database user: can read football data and update limited personal data
DROP USER IF EXISTS 'Player'@'localhost';
CREATE USER 'Player'@'localhost' IDENTIFIED BY 'player_pass';

-- Coach database user: can manage roster, game, and stat data
DROP USER IF EXISTS 'Coach'@'localhost';
CREATE USER 'Coach'@'localhost' IDENTIFIED BY 'coach_pass';

-- Manager database user: application-level manager with full application privileges
DROP USER IF EXISTS 'Manager'@'localhost';
CREATE USER 'Manager'@'localhost' IDENTIFIED BY 'manager_pass';

-- Admin database user: database administrator account for maintenance
DROP USER IF EXISTS 'admin'@'localhost';
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin_pass';


-- ============================================================
-- MANAGER PRIVILEGES
-- Manager can fully manage the football application database.
-- ============================================================

GRANT ALL PRIVILEGES ON football.* TO 'Manager'@'localhost';


-- ============================================================
-- FAN PRIVILEGES
-- Fan has read-only access to non-sensitive football data.
-- Fans can view teams, players, coaches, games, stats, seasons,
-- stadiums, and team season records.
-- ============================================================

GRANT SELECT ON football.Team TO 'Fan'@'localhost';
GRANT SELECT ON football.Player TO 'Fan'@'localhost';
GRANT SELECT ON football.Coach TO 'Fan'@'localhost';
GRANT SELECT ON football.Game TO 'Fan'@'localhost';
GRANT SELECT ON football.Stat TO 'Fan'@'localhost';
GRANT SELECT ON football.Season TO 'Fan'@'localhost';
GRANT SELECT ON football.Stadium TO 'Fan'@'localhost';
GRANT SELECT ON football.Team_Season TO 'Fan'@'localhost';


-- ============================================================
-- PLAYER PRIVILEGES
-- Player can view football data and update account/player data.
-- IMPORTANT: The database grants table-level UPDATE access,
-- but PHP session logic must restrict players so they can only
-- update their own UserAccount and Player records.
-- ============================================================

GRANT SELECT ON football.Team TO 'Player'@'localhost';
GRANT SELECT ON football.Game TO 'Player'@'localhost';
GRANT SELECT ON football.Stat TO 'Player'@'localhost';
GRANT SELECT ON football.Season TO 'Player'@'localhost';
GRANT SELECT ON football.Stadium TO 'Player'@'localhost';
GRANT SELECT, UPDATE ON football.UserAccount TO 'Player'@'localhost';
GRANT SELECT, UPDATE ON football.Player TO 'Player'@'localhost';


-- ============================================================
-- COACH PRIVILEGES
-- Coach can view reference data and manage roster/game/stat data.
-- ============================================================

GRANT SELECT ON football.Team TO 'Coach'@'localhost';
GRANT SELECT ON football.Season TO 'Coach'@'localhost';
GRANT SELECT ON football.Stadium TO 'Coach'@'localhost';
GRANT SELECT, UPDATE ON football.Player TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE ON football.Player_Team TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE ON football.Game TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE ON football.Stat TO 'Coach'@'localhost';
GRANT SELECT, UPDATE ON football.UserAccount TO 'Coach'@'localhost';


-- ============================================================
-- ADMIN PRIVILEGES
-- Admin has full database privileges for setup and maintenance.
-- ============================================================

GRANT ALL PRIVILEGES ON football.* TO 'admin'@'localhost';


-- Reload MySQL privilege tables after user and grant changes
FLUSH PRIVILEGES;
