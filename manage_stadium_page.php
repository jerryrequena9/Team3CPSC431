<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  check_valid_user();

  /*
   * Stadium management is a Manager-level feature.
   * Actual INSERT/UPDATE/DELETE enforcement should still come from MySQL
   * permissions on the Stadium table.
   */
  if ($_SESSION['UserRole'] !== 'Manager') {
    err_permission_denied();
  }

  do_html_header('Manage Stadiums');
  display_user_nav();

  echo "<h2>Manage Stadiums</h2>";
  echo "<p>Stadium management is currently under development.</p>";

  do_html_footer();
?>
