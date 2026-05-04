<?php
  require_once('StartSession.php');
  require_once("html_components.php");
  do_html_header("Register");

  echo '
    <form method="post" action="register_user.php">
        <label>Email:</label><br>
        <input type="email" name="email"><br><br>

        <label>Username:</label><br>
        <input type="text" name="username"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password"><br><br>

        <input type="submit" name="register" value="Register"><br><br>

        <a href="login_page.php">Login</a>
    </form>
  ';

  do_html_footer();
?>
