<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');
  require_once(__DIR__ . '/../../pages/html_components.php');

  // Check that all fields are filled
  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/register_user_page.php");
  }

  $email = trim($_POST['register_email']);
  $username = trim($_POST['register_username']);
  $password = trim($_POST['register_password']);
  $confirm_password = trim($_POST['register_confirm_password']);

  try {
	  register($username, $email, $password, $confirm_password);
    success("User registered", "../../pages/login_page.php");
	}
   catch (Exception $e) {
    error($e->getMessage(), "../../pages/register_user_page.php");
	}

  function register($username, $email, $password, $confirm_password) {
    // Check that the passwords match
    if ($password != $confirm_password) {
      throw new Exception('The passwords you entered do not match. Please go back and try again.');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    global $db;
    /*
    all self-registered users become Fans
    nobody self-registers as Manager
    role always exists
    avoids broken accounts
    */
    $query = "
      INSERT INTO UserAccount (username, password_hash, email)
      VALUES (?, ?, ?)
    ";
	
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("sss", $username, $hashed_password, $email);
    try {
      $stmt->execute();
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
          throw new Exception('Could not register the user');
        }
      }
    }

    if ($stmt->affected_rows === 0) {
      throw new Exception('Could not register the user');
    }

    $stmt->close();
  }

?>
