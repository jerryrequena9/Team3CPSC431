<?php
  require('StartSession.php');
  require('helpers.php');
  require('html_components.php');

  check_valid_user();

  try {
    $username = // GET USER;
    $old_password = sanitize_str($_POST['change_old_password']);
    $new_password = sanitize_str($_POST['change_new_password']);
    $repeat_new_password = sanitize_str($_POST['change_repeat_new_password']);

    if ($new_password != $repeat_new_password) {
      throw new Exception("Repeated new password and new password do not match");
    }

    change_password($username, $old_password, $new_password);

    do_html_header("Change Password");
    echo "Password changed successfully";
    echo "<a href='home_page.php'>Home</a>";
    do_html_footer();
    do_user_nav();
  } catch (Exception $e) {
    do_html_header("Problem");
    echo "Error: ".$e->getMessage()."<br>";
    echo "<a href='change_password_page.php'>Change password</a>";
    do_html_footer();
    do_user_nav();
    exit;
  }

  function change_password($username, $old_password, $new_password) {
    // change password for username/old_password to new_password
    // return true or false
    // if the old password is right
    // change their password to new_password and return true
    // else throw an exception
    require('helpers.php');
    login($username, $old_password);
    $conn = db_connect();
    $result = $conn->query(); // TODO: fill in
    if (!$result) {
      throw new Exception('Password could not be changed.');
    } else {
      return true;
    }
  }
?>
