<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  do_html_header('Manage Seasons');
  check_valid_user();
  display_user_nav();
  do_html_footer();
?>
