<?php
require_once("html_components.php");

do_html_header("Login");

echo '
  <form method="post" action="login.php">
      <label>Username:</label><br>
      <input type="text" name="username"><br><br>

      <label>Password:</label><br>
      <input type="password" name="password"><br><br>

      <input type="submit" name="login" value="Login"><br><br>

      <a href="forgot_password_page.php">Forgot Password?</a><br>
      <a href="register_user_page.php">Register</a>
  </form>
<?php
';

do_html_footer();
?>
