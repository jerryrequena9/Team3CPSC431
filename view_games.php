<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  check_valid_user();

  do_html_header('View Games');

  display_user_nav();

  global $db;

  display_recent_games($db);

  do_html_footer();
?>
