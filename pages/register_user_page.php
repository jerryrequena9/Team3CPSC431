<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . "/html_components.php");
  do_html_header("Register");

  echo '
    <form method="post" action="../scripts/user/register_user.php">
        <label>Email:</label><br>
        <input type="email" name="egister_email" required><br><br>

        <label>Username:</label><br>
        <input type="text" name="username" minlength="4" maxlength="50" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" minlength="4" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" minlength="4" required><br><br>

        <input type="submit" value="Register"><br><br>

        <a href="login_page.php">Login</a>
        <br>
        <a href="forgot_password_page.php">Forgot Password?</a>
    </form>
  ';

  do_html_footer();
?>
