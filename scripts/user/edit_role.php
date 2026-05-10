<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');

  check_valid_user();
  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/user_page.php");
  }

  $query = "
    UPDATE UserAccount u
    JOIN Role r
      ON r.name = ?
    SET u.role_id = r.role_id
    WHERE u.username = ?;
  ";
  
  global $db;
  $stmt = prepare_with_perms($db, $query); 
  $username = trim($_POST['manage_user_username']);
  $role = trim($_POST['manage_user_role']);
  $stmt->bind_param("ss", $role, $username);
  
  try {
    $stmt->execute();
  } catch (mysqli_sql_exception $e) {
    error("Role not changed", "../../pages/user_page.php");
  } finally {
    $stmt->close();
  }

  // If the user changes their own role, log them out to reset the session/database connection
	if ($username == $_SESSION['UserName']) {
	  success('Your role was changed. Please log in again.', '../../pages/login_page.php');
	}

  success('Role changed.', '../../pages/user_page.php');
?>

