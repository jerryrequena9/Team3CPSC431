<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');
  require_once(__DIR__ . '/../../pages/html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    error('Required fields are missing', '../../pages/change_password_page.php');
  }

  /*
   * The username comes from the session, not from POST.
   * This prevents a user from changing another user's password by editing form data.
   */
  $username = $_SESSION['UserName'];

  $old_password = trim($_POST['change_old_password']);
  $new_password = trim($_POST['change_new_password']);
  $repeat_new_password = trim($_POST['change_repeat_new_password']);

  if ($new_password !== $repeat_new_password) {
    error('Passwords do not match', '../../pages/change_password_page.php');
  }

  try {
    change_password($username, $old_password, $new_password);

    success('Password changed', '../../pages/home_page.php');
  } catch (Exception $e) {
    error($e->getMessage(), '../../pages/change_password_page.php');
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
    $stmt->bind_param("s", $username);
    try {
      $stmt->execute();
    } catch (mysqli_sql_exception $e) {
      $stmt->close();
      throw new Exception('Could not change password');
    }

    $stmt->bind_result($old_hash);
    if (!$stmt->fetch()) {
      $stmt->close();
      throw new Exception('User not found');
    }
    $stmt->close();

    if (!password_verify($old_password, $old_hash)) {
      throw new Exception('Current password is incorrect');
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
    $stmt->bind_param("ss", $new_hash, $username);
    try {
      $stmt->execute();
    } catch (mysqli_sql_exception $e) {
      throw new Exception('Could not update password');
    } finally {
      $stmt->close();
    }
  }
?>
