<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');
  require_once(__DIR__ . '/../../pages/html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/user_page.php");
  }

  $username = trim($_POST['delete_username']);

  // Delete user
  $query = "
    DELETE FROM UserAccount
    WHERE username = ?;
  ";
  
  global $db;
  $stmt = prepare_with_perms($db, $query);
  $stmt->bind_param("s", $username);
  try {
    $stmt->execute();
  } catch (mysqli_sql_exception $e) {
    error("User not deleted", "../../pages/user_page.php");
  } finally {
    $stmt->close();
  }

  // If the user deletes themselves, log them out to reset the session
  if ($username == $_SESSION['UserName']) {
    success("Your account was deleted", "../../pages/login_page.php");
  }

  success("User deleted", "../../pages/user_page.php");
?>
