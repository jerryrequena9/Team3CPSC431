<?php
  require_once('StartSession.php');
  require_once("html_components.php");
  do_html_header("Register");

  echo '
    <form method="post" action="register_user.php">
        <label>Email:</label><br>
        <input type="email" name="email"><br><br>

        <label>Username:</label><br>
        <input type="text" name="register_username"><br><br>

        <label>Password:</label><br>
        <input type="password" name="register_password"><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="register_confirm_password"><br><br>

        <input type="submit" value="Register"><br><br>

        <a href="login_page.php">Login</a>
        <br>
        <a href="forgot_password_page.php">Forgot Password?</a>
    </form>
  ';

  do_html_footer();
?>
