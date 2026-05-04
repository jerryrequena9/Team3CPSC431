<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  do_html_header("Change Password");
  check_valid_user();

  if (!is_valid_post($_POST)) {
    header('Location: change_password_page.php');
    exit;
  }
  
  $username = $_SESSION['UserName'];
  $old_password = sanitize_str($_POST['change_old_password']);
  $new_password = sanitize_str($_POST['change_new_password']);
  $repeat_new_password = sanitize_str($_POST['change_repeat_new_password']);

  if ($new_password != $repeat_new_password) {
    display_error_exit("new password and confirm new password do not match");
  }

  try {
    change_password($username, $old_password, $new_password);
    do_html_header('Success');
    echo "Password changed successfully";
    echo "<a href='home_page.php'>Home</a>";
    display_user_nav();
    do_html_footer();
  } catch (Exception $e) {
    display_error_exit($e->getMessage());
  }

  function change_password($username, $old_password, $new_password) {
    global $db;
    $query = "
      SELECT password_hash
      FROM UserAccount
      WHERE username = ?
    ";
    $stmt = prepare_with_perms($db, $query);
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

    if (!validate_password($new_password)) {
      throw new Exception('The new password does not meet the requirements');
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?
    ";
    $stmt = prepare_with_perms($db, $query); 
    if (!$stmt || !$stmt->bind_param("ss", $new_hash, $username) || !$stmt->execute()) {
      throw new Exception('Could not update password.');
    }

    $stmt->close();
  }
?>
