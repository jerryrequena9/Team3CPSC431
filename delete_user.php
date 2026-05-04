<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
  }

  $query = "
    DELETE FROM UserAccount
    WHERE username = ?;
  ";
  
  global $db;
  $stmt = prepare_with_perms($db, $query);
  $username = sanitize_str($_POST['manage_user_delete_username']);
  if (!$stmt || !$stmt->bind_param("s", $username) || !$stmt->execute()) {
    display_error_exit("failed to delete user");
  }
  $stmt->close();

  // Weird case: if the user deletes themselves, log them out to reset the session
  if ($username == $_SESSION['UserName']) {
    header('Location: logout.php');
    exit;
  }

  header('Location: manage_user_page.php');
  exit;
?>