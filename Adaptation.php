<?php
  /*
    HW3 database configuration

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

  define('DATA_BASE_NAME', 'hw3');
  define('DATA_BASE_HOST', 'localhost');

  /*
    Read-only database credentials used only to authenticate the user
    against the Users table before the role is known.
  */
  define('AUTH_USER_NAME', 'auth_user');
  define('AUTH_USER_PASSWORD', 'auth_password');

  /*
    Returns database credentials based on the authenticated user's role.

    manager -> full data CRUD on TeamRoster and Statistics
    coach   -> can read roster and update statistics
    player  -> can view all stats, but PHP must restrict CRUD to only
               that player's own rows
  */
  function getDatabaseCredentials($role) {
    if ($role === 'manager') {
      return [
        'username' => 'manager_user',
        'password' => 'manager_password'
      ];
    }

    if ($role === 'coach') {
      return [
        'username' => 'coach_user',
        'password' => 'coach_password'
      ];
    }

    if ($role === 'player') {
      return [
        'username' => 'player_user',
        'password' => 'player_password'
      ];
    }

    return null;
  }
?>
