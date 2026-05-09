<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    header('Location: change_password_page.php?error=missing_fields');
    exit;
  }

  /*
   * The username comes from the session, not from POST.
   * This prevents a user from changing another user's password by editing form data.
   */
  $username = $_SESSION['UserName'];

  $old_password = $_POST['change_old_password'];
  $new_password = $_POST['change_new_password'];
  $repeat_new_password = $_POST['change_repeat_new_password'];

  if ($new_password !== $repeat_new_password) {
    header('Location: change_password_page.php?error=passwords_do_not_match');
    exit;
  }

  try {
    change_password($username, $old_password, $new_password);

    header('Location: home_page.php?success=password_changed');
    exit;

  } catch (Exception $e) {
    header('Location: change_password_page.php?error=' . urlencode($e->getMessage()));
    exit;
  }

  function change_password($username, $old_password, $new_password) {
    global $db;

    /*
     * Verify the current user's existing password before allowing a change.
     */
    $query = "
      SELECT password_hash
      FROM UserAccount
      WHERE username = ?
    ";

    $stmt = prepare_with_perms($db, $query);

    if (!$stmt || !$stmt->bind_param("s", $username) || !$stmt->execute()) {
      throw new Exception('could_not_change_password');
    }

    $stmt->bind_result($old_hash);

    if (!$stmt->fetch()) {
      throw new Exception('user_not_found');
    }

    $stmt->close();

    if (!password_verify($old_password, $old_hash)) {
      throw new Exception('current_password_incorrect');
    }

    /*
     * Enforce your existing password rules before hashing.
     */
    if (!validate_password($new_password)) {
      throw new Exception('password_requirements_not_met');
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    /*
     * Password updates are restricted to the logged-in username only.
     * MySQL permissions should still enforce UPDATE access on UserAccount.
     */
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?
    ";

    $stmt = prepare_with_perms($db, $query);

    if (!$stmt || !$stmt->bind_param("ss", $new_hash, $username) || !$stmt->execute()) {
      throw new Exception('could_not_update_password');
    }

    $stmt->close();
  }
?>
