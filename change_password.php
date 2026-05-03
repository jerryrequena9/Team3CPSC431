<?php
  require('StartSession.php');
  require('helpers.php');
  require('html_components.php');

  do_html_header("Change Password");
  check_valid_user();

  try {
    if (!filled_out($_POST)) {
      throw new Exception("All fields are not filled out");
    }

    $username = $_SESSION['UserName'];
    $old_password = sanitize_str($_POST['change_old_password']);
    $new_password = sanitize_str($_POST['change_new_password']);
    $repeat_new_password = sanitize_str($_POST['change_repeat_new_password']);

    if ($new_password != $repeat_new_password) {
      throw new Exception("Repeated new password and new password do not match");
    }

    change_password($username, $old_password, $new_password);

    echo "Password changed successfully";
    echo "<a href='home_page.php'>Home</a>";
    do_user_nav();
    do_html_footer();
  } catch (Exception $e) {
    do_html_header("Problem");
    echo "Error: ".$e->getMessage()."<br>";
    echo "<a href='change_password_page.php'>Change password</a>";
    do_user_nav();
    do_html_footer();
    exit;
  }

  function change_password($username, $old_password, $new_password) {
    $db = db_connect();

    $query = "
      SELECT password_hash
      FROM UserAccount
      WHERE username = ?
    ";
    $stmt = $db->prepare($query);
    if (!$stmt || !$stmt->bind_param("s", $username) || !$stmt->execute()) {
      throw new Exception('Could not change password.');
    }

    $stmt->bind_result($old_hash);
    if (!$stmt->fetch()) {
      throw new Exception('User not found.');
    }
    $stmt->close();

    if (!password_verify($old_password, $old_hash)) {
      throw new Exception('Current password is incorrect.');
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?
    ";
    $stmt = $db->prepare($query);
    if (!$stmt || !$stmt->bind_param("ss", $new_hash, $username) || !$stmt->execute()) {
      throw new Exception('Could not update password.');
    }

    $stmt->close();
  }
?>
