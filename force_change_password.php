<?php
  require_once('StartSession.php');
  require_once('helpers.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
  }

  $username = sanitize_str($_POST['manage_user_change_password_username']);
  $password = sanitize_str($_POST['manage_user_change_new_password']);
  try {
    change_password($username, $password);
    do_html_header('Success');
    display_user_nav();
    echo 'Success: password was changed';
    do_html_footer();
    exit;
  } catch (Exception $e) {
    display_error_exit($e->getMessage());
  }

function change_password($username, $new_password) {
    global $db;
    if (!validate_password($new_password)) { 
        throw new Exception('Password does not meet the requirements.');
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    if (!$stmt || !$stmt->bind_param("ss", $new_hash, $username) || !$stmt->execute()) {
        throw new Exception('Password was not changed');
    }
    if ($stmt->affected_rows == 0) {
      err_permission_denied();
    }

    $stmt->close();
  }
?>