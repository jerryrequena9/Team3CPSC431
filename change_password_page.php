<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  do_html_header('Change Password');
  check_valid_user();
  display_user_nav();

  echo '
    <form method="post" action="change_password.php">
        <label>Old Password:</label><br>
        <input type="password" name="change_old_password"><br><br>

        <label>New Password:</label><br>
        <input type="password" name="change_new_password"><br><br>

        <label>Repeat New Password:</label><br>
        <input type="password" name="change_repeat_new_password"><br><br>

        <input type="submit" value="Change Password"><br><br>
    </form>
  ';

  do_html_footer();
?>
