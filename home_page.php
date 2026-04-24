<?php
  require("html_components.php");
  require("StartSession.php");

  do_html_header('Home');
  check_valid_user();

  // give menu of options
  display_user_nav();

  do_html_footer();
?>
