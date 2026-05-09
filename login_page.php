<?php
require_once("html_components.php");

do_html_header("Login");

if (isset($_GET['success'])) {
    echo "<p style='color:green; font-weight:bold;'>" . htmlspecialchars($_GET['success']) . "</p>";
}

if (isset($_GET['error'])) {
    echo "<p style='color:red; font-weight:bold;'>" . htmlspecialchars($_GET['error']) . "</p>";
}

echo '
  <form method="post" action="login.php">
      <label>Username:</label><br>
      <input type="text" name="login_username"><br><br>

      <label>Password:</label><br>
      <input type="password" name="login_password"><br><br>

      <input type="submit" value="Login"><br><br>

      <a href="forgot_password_page.php">Forgot Password?</a><br>
      <a href="register_user_page.php">Register</a>
  </form>
';

do_html_footer();
?>
