<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
  }

  $username = sanitize_str($_POST['manage_user_create_user_username']);
  $email = sanitize_str($_POST['manage_user_create_user_email']);
  $password = sanitize_str($_POST['manage_user_create_user_password']);
  $repeat_password = sanitize_str($_POST['manage_user_create_user_repeat_password']);
  $role = sanitize_str($_POST['manage_user_create_user_role']);

  try {
    register($username, $email, $password, $repeat_password, $role);
    header("Location: manage_user_page.php?success=User created successfully");
    exit;
  }
  catch (Exception $e) {
    display_error_exit($e->getMessage());
    exit;
  }

  function register($username, $email, $password, $confirm_password, $role) {
    if (!valid_email($email)) {
      throw new Exception('That is not a valid email address. Please go back and try again.');
    }

    if ($password != $confirm_password) {
      throw new Exception('The passwords you entered do not match. Please go back and try again.');
    }

    if (!validate_password($password)) {
      throw new Exception('The password does not meet the requirements.');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    global $db;

    $query = "
      INSERT INTO UserAccount (username, password_hash, email, role_id)
      SELECT ?, ?, ?, role_id
      FROM Role
      WHERE name = ?
    ";

    $stmt = prepare_with_perms($db, $query);

    if (!$stmt || !$stmt->bind_param("ssss", $username, $hashed_password, $email, $role) || !$stmt->execute()) {
      if ($db->errno === 1062) {
        throw new Exception('That username is already taken.');
      }
      throw new Exception('Could not register the user.');
    }

    if ($stmt->affected_rows === 0) {
      throw new Exception('Invalid role selected.');
    }

    $stmt->close();
  }
?>
