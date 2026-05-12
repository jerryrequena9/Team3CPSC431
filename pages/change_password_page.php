<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/html_components.php');
  require_once(__DIR__ . '/../scripts/helpers.php');

  do_html_header('Change Password');
  check_valid_user();
  display_user_nav();

  echo '
    <form method="post" action="../scripts/user/change_password.php">
        <label>Old Password:</label><br>
        <input type="password" name="old_password" required><br><br>

        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br><br>

        <label>Repeat New Password:</label><br>
        <input type="password" name="repeat_new_password" required><br><br>

        <input type="submit" value="Change Password"><br><br>
    </form>
  ';

  do_html_footer();
?>
