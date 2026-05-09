<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  check_valid_user();

  /*
   * Season management is a Manager-level feature.
   * MySQL permissions should still enforce write access on the Season table.
   */
  if ($_SESSION['UserRole'] !== 'Manager') {
    err_permission_denied();
  }

  do_html_header('Manage Seasons');
  display_user_nav();

  echo "<h2>Manage Seasons</h2>";
  echo "<p>Season management is currently under development.</p>";

  do_html_footer();
?>
