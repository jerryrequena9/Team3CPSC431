<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/html_components.php');

  do_html_header('Forgot Password');

  echo "<p>Enter your username and we'll send you an email with instructions on how to access your account.</p>";
  echo '
    <form method="post" action="../scripts/user/forgot_password.php">
        <label>Username:</label><br>
        <input type="text" name="username" required minlength="4" maxlength="50"><br><br>

        <input type="submit" value="Submit"><br><br>
        <a href="login_page.php">Login</a>
        <br>
        <a href="register_user_page.php">Register</a>
    </form>
  ';

  do_html_footer();
?>
