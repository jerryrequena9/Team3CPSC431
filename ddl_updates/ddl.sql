-- =========================
-- USERS
-- =========================
DROP USER IF EXISTS 'Fan'@'localhost';
CREATE USER 'Fan'@'localhost' IDENTIFIED BY 'fan_pass';

DROP USER IF EXISTS 'Player'@'localhost';
CREATE USER 'Player'@'localhost' IDENTIFIED BY 'player_pass';

DROP USER IF EXISTS 'Coach'@'localhost';
CREATE USER 'Coach'@'localhost' IDENTIFIED BY 'coach_pass';

DROP USER IF EXISTS 'Manager'@'localhost';
CREATE USER 'Manager'@'localhost' IDENTIFIED BY 'manager_pass';

-- =========================
-- MANAGER (FULL ACCESS)
-- =========================
GRANT ALL PRIVILEGES ON football.* TO 'Manager'@'localhost';

-- =========================
-- FAN (READ ONLY)
-- =========================
GRANT SELECT ON football.Team TO 'Fan'@'localhost';
GRANT SELECT ON football.Player TO 'Fan'@'localhost';
GRANT SELECT ON football.Coach TO 'Fan'@'localhost';
GRANT SELECT ON football.Game TO 'Fan'@'localhost';
GRANT SELECT ON football.Stat TO 'Fan'@'localhost';
GRANT SELECT ON football.Season TO 'Fan'@'localhost';
GRANT SELECT ON football.Stadium TO 'Fan'@'localhost';
GRANT SELECT ON football.Team_Season TO 'Fan'@'localhost';
GRANT SELECT ON football.Role TO 'Fan'@'localhost';

-- =========================
-- PLAYER
-- =========================
GRANT SELECT ON football.Team TO 'Player'@'localhost';
GRANT SELECT ON football.Game TO 'Player'@'localhost';
GRANT SELECT ON football.Stat TO 'Player'@'localhost';
GRANT SELECT ON football.Season TO 'Player'@'localhost';
GRANT SELECT ON football.Stadium TO 'Player'@'localhost';
GRANT SELECT ON football.Team_Season TO 'Player'@'localhost';
GRANT SELECT ON football.Role TO 'Player'@'localhost';

GRANT SELECT, UPDATE ON football.UserAccount TO 'Player'@'localhost';
GRANT SELECT, UPDATE ON football.Player TO 'Player'@'localhost';

-- =========================
-- COACH
-- =========================
GRANT SELECT ON football.Season TO 'Coach'@'localhost';
GRANT SELECT ON football.Stadium TO 'Coach'@'localhost';
GRANT SELECT ON football.Role TO 'Coach'@'localhost';

GRANT SELECT, UPDATE ON football.Team TO 'Coach'@'localhost';
GRANT SELECT, UPDATE ON football.Player TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE ON football.Player_Team TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE ON football.Game TO 'Coach'@'localhost';
GRANT SELECT, INSERT, UPDATE ON football.Stat TO 'Coach'@'localhost';
GRANT SELECT, UPDATE ON football.Team_Season TO 'Coach'@'localhost';
GRANT SELECT, UPDATE ON football.UserAccount TO 'Coach'@'localhost';

-- =========================
-- APPLY
-- =========================
FLUSH PRIVILEGES;
