<?php
  require('StartSession');
  require('html_components.php');
  do_html_header('Change Password');
  check_valid_user();
  display_change_password_form();
  display_user_nav();
  do_html_footer();
?>
