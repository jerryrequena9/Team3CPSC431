<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');
  require_once(__DIR__ . '/../../pages/html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/user_page.php");
  }

  $username = trim($_POST['manage_user_create_user_username']);
  $email = trim($_POST['manage_user_create_user_email']);
  $password = trim($_POST['manage_user_create_user_password']);
  $repeat_password = trim($_POST['manage_user_create_user_repeat_password']);
  $role = trim($_POST['manage_user_create_user_role']);

  try {
    register($username, $email, $password, $repeat_password, $role);
    success('User created', '../../pages/user_page.php');
  }
  catch (Exception $e) {
    error($e->getMessage(), '../../pages/user_page.php');
  }

  function register($username, $email, $password, $confirm_password, $role) {
    if ($password != $confirm_password) {
      throw new Exception('The passwords you entered do not match. Please go back and try again.');
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
    $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);
    
    try {
      $stmt->execute();
      if ($stmt->affected_rows === 0) {
        throw new Exception('Could not create the user');
      }
    } catch (mysqli_sql_exception $e) {
      if ($e->getCode() == 4025) {
        if (strpos($stmt->error, 'valid_email') !== false) {
          throw new Exception("Invalid email format");
        }

        if (strpos($stmt->error, 'valid_username') !== false) {
          throw new Exception("Username must be between 4 and 50 characters");
        }
      } else if ($e->getCode() == 1062) {
        if (strpos($stmt->error, 'username') !== false) {
          throw new Exception('That username is already taken.');
        } else if (strpos($stmt->error, 'email') !== false) {
          throw new Exception('That email is already taken.');
        } else {
          throw new Exception('Could not create the user');
        }
      }
    } finally {
      $stmt->close();
    }
  }
?>
