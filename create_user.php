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
  try {
    register($username, $email, $password, $repeat_password);
    do_html_header('Success');
    echo 'Success: user created';
    display_user_nav();
    do_html_footer();
    exit;
  }
  catch (Exception $e) {
    display_error_exit($e->getMessage());
    exit;
  }

  // this is the same function as in register_user.php
  // maybe refactor? but its only used twice
  function register($username, $email, $password, $confirm_password) {
    // Check that the email is valid
    if (!valid_email($email)) {
      throw new Exception('That is not a valid email address.
      Please go back and try again.');
    }
    // Check that the passwords match
    if ($password != $confirm_password) {
      throw new Exception('The passwords you entered do not match –
      please go back and try again.');
    }
    // Check for valid password length
    if (!validate_password($password)) {
      throw new Exception('The password does not meet the requirements.');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    global $db;
    $query = "
      INSERT INTO UserAccount (username, password_hash, email)
      VALUES (?, ?, ?)
    ";
    $stmt = prepare_with_perms($db, $query);
    if (!$stmt->bind_param("sss", $username, $hashed_password, $email) || !$stmt->execute()) {
      // duplicate entry
      // source: https://mariadb.com/docs/server/reference/error-codes/mariadb-error-codes-1000-to-1099/e1062
      if ($db->errno === 1062) {
        throw new Exception('That username is already taken.');
      }
      throw new Exception('Could not register the user.');
    }
    $stmt->close();
  }
?>