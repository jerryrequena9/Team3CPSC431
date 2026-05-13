<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/user_page.php");
  }

  $username = trim($_POST['change_password_username']);
  $password = trim($_POST['change_password_password']);

  if (strlen($password) < 8) {
    error("Password must be at least 8 characters", "../../pages/user_page.php");
  }

  try {
      change_password($username, $password);
      success("Password changed", "../../pages/user_page.php");
  } catch (Exception $e) {
      error($e->getMessage(), "../../pages/user_page.php");
  }

function change_password($username, $new_password) {
    global $db;

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    // Update user password
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ss", $new_hash, $username);
    try {
      $stmt->execute();
      if ($stmt->affected_rows == 0) {
        throw new Exception('Password was not changed');
      }
    } catch (mysqli_sql_exception $e) {
      throw new Exception('Password was not changed');
    } finally {
      $stmt->close();
    }
  }
?>
