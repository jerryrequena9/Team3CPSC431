<?php
  /*
    Database-level authorization
       - After the application identifies the user's role
         (manager, coach, or player),
         PHP connects to MySQL using the matching database account.
       - This allows MySQL privileges to enforce role-based access.

    auth_user
    -------------------------
    Before the application knows whether someone is a manager, coach,
    or player, it must first read the Users table to verify:
      - username
      - password hash
      - role
      - roster_id

    Because of that, we use a separate MySQL account called auth_user.
    This account should have read-only access to hw3.Users only.

  */

  define('DATA_BASE_NAME', 'football');
  define('DATA_BASE_HOST', 'localhost');

  /*
    Database credentials used only to authenticate the user
    against the Users table before the role is known.
  */
  define('AUTH_ROLE', 'Manager');

  // Lookup table from role to database password
  $DBPasswords = ['Manager'  => 'manager_pass',
                  'Coach'  => 'coach_pass',
                  'Player' => 'player_pass',
                  'Fan'    => 'fan_pass'];

  $DBRoles = ['Manager', 'Coach', 'Player', 'Fan'];
?>
