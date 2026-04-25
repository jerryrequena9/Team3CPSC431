<?php
  require('StartSession');
  require('html_components.php');

  do_html_header('Forgot Password');

  display_forgot_password_form();

  do_html_footer();
?>
