<?php
  require_once('StartSession.php');
  require_once("html_components.php");
  do_html_header("Login");

  display_login_form();

  do_html_footer();
?>
