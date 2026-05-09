<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  check_valid_user();

  /*
   * Game management is currently treated as Manager-only.
   * MySQL permissions should still enforce INSERT/UPDATE/DELETE permissions
   * on the Game table if this page is developed later.
   */
  if ($_SESSION['UserRole'] !== 'Manager') {
    err_permission_denied();
  }

  do_html_header('Manage Games');
  display_user_nav();

  echo "<h2>Manage Games</h2>";
  echo "<p>Game management is currently under development.</p>";

  do_html_footer();
?>
