<?php
require_once(__DIR__ . '/../scripts/StartSession.php');
require_once(__DIR__ . "/html_components.php");
do_html_header("Login");

echo '
  <form method="post" action="../scripts/user/login.php">
      <label>Username:</label><br>
      <input type="text" name="username" minlength="4" maxlength="50" required><br><br>

      <label>Password:</label><br>
      <input type="password" name="password" minlength="4" required><br><br>

      <input type="submit" value="Login"><br><br>

      <a href="forgot_password_page.php">Forgot Password?</a><br>
      <a href="register_user_page.php">Register</a>
  </form>
';

do_html_footer();
?>
