function do_html_header($title) {
  // print an HTML header
  ?>
  <!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title><?php echo $title;?></title>
      <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px }
        li, td { font-family: Arial, Helvetica, sans-serif; font-size: 13px }
        hr { color: #3333cc;}
        a { color: #000 }
        div.formblock
        { background: #ccc; width: 300px; padding: 6px; border: 1px solid #000;}
      </style>
    </head>
    <body>
      <hr/>

  <?php
    if ($title) {
      echo "<h1>".$title."</h1>"
    }
}

function do_html_footer() {
?>
  </body>
  </html>
<?php
}

function display_login_form() {
?>
  <form method="post" action="login.php">
      <label>Username:</label><br>
      <input type="text" name="username"><br><br>

      <label>Password:</label><br>
      <input type="password" name="password"><br><br>

      <input type="submit" name="login" value="Login"><br><br>

      <a href="forgot_password_page.php">Forgot Password?</a><br>
      <a href="register_user_page.php">Create User</a>
  </form>
<?php
}

function display_register_form() {
?>
  <form method="post" action="register_user.php">
      <label>First Name:</label><br>
      <input type="text" name="first_name"><br><br>

      <label>Last Name:</label><br>
      <input type="text" name="last_name"><br><br>

      <label>Email:</label><br>
      <input type="email" name="email"><br><br>

      <label>Username:</label><br>
      <input type="text" name="username"><br><br>

      <label>Password:</label><br>
      <input type="password" name="password"><br><br>

      <label>Confirm Password:</label><br>
      <input type="password" name="confirm_password"><br><br>

      <input type="submit" name="register" value="Register"><br><br>

      <a href="login_page.php">Back to Login</a>
  </form>
<?php
}

function display_user_nav() {
?>
  <a href='logout.php'>Logout</a>
  <a href='change_password_page.php'>Change password</a>
<?php
}

function display_change_password_form() {
?>
  <form method="post" action="change_password.php">
      <label>Old Password:</label><br>
      <input type="text" name="change_old_password"><br><br>

      <label>New Password:</label><br>
      <input type="text" name="change_new_password"><br><br>

      <label>Repeat New Password:</label><br>
      <input type="email" name="change_repeat_new_password"><br><br>

      <input type="submit" name="change_password" value="Change password"><br><br>
  </form>
<?php
}

function display_forgot_password_form() {
?>
  <form method="post" action="forgot_password.php">
      <label>Username:</label><br>
      <input type="text" name="forgot_username"><br><br>

      <input type="submit" name="forgot_password" value="Change password"><br><br>
  </form>
<?php
}
