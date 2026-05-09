<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  check_valid_user();

  /*
   * Coach management should be Manager-only.
   * Coaches should not manage other Coach records.
   * MySQL permissions should still enforce access to the Coach table.
   */
  if ($_SESSION['UserRole'] !== 'Manager') {
    err_permission_denied();
  }

  do_html_header('Manage Coaches');
  display_user_nav();

  echo "<h2>Manage Coaches</h2>";
  echo "<p>Coach management is currently under development.</p>";

  do_html_footer();
?>
