<?php
  require_once('StartSession.php');
  require_once('html_components.php');

  do_html_header('Forgot Password');


  echo '
    <form method="post" action="forgot_password.php">
        <label>Username:</label><br>
        <input type="text" name="forgot_username"><br><br>

        <input type="submit" name="forgot_password" value="Submit"><br><br>
    </form>
  ';

  do_html_footer();
?>
