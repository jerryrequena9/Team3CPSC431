<?php
  require_once('StartSession.php');
  require_once('helpers.php');

  check_valid_user();
  if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
  }

  $query = "
    UPDATE UserAccount u
    JOIN Role r ON r.name = ?
    SET u.role_id = r.role_id
    WHERE u.username = ?;
  ";
  
  global $db;
  $stmt = prepare_with_perms($db, $query); 
  $username = sanitize_str($_POST['manage_user_username']);
  $role = sanitize_str($_POST['manage_user_role']);
  if (!$stmt || !$stmt->bind_param("ss", $role, $username) || !$stmt->execute()) {
    display_error_exit("failed to change role");
  }
  $stmt->close();

  // Weird case: if the user changes their own role, log them out to reset the session
  if ($username == $_SESSION['UserName']) {
    header('Location: logout.php');
    exit;
  }

  header('Location: manage_user_page.php');
  exit;
?>