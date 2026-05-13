<?php
  /**
   * Change Password - For Authenticated Users
   *
   * Security Features:
   * - Requires verification of current password before allowing change
   * - Only allows users to change their own password (not others')
   * - Validates that new passwords match before hashing
   * - Uses prepared statements to prevent SQL injection
   * - Uses password_hash/password_verify for secure password handling
   *
   * Validation:
   * - New password must be at least 4 characters
   * - New passwords must match in confirmation field
   */

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

  $old_password = trim($_POST['old_password']);
  $new_password = trim($_POST['new_password']);
  $repeat_new_password = trim($_POST['repeat_new_password']);

  // Validate old password was provided
  if (empty($old_password)) {
    error('Current password is required', '../../pages/change_password_page.php');
  }

  // Validate new password requirements
  if (empty($new_password)) {
    error('New password is required', '../../pages/change_password_page.php');
  }

  if (strlen($new_password) < 8) {
    error('New password must be at least 8 characters', '../../pages/change_password_page.php');
  }

  if ($new_password !== $repeat_new_password) {
    error('New passwords do not match', '../../pages/change_password_page.php');
  }

  if ($old_password === $new_password) {
    error('New password must be different from current password', '../../pages/change_password_page.php');
  }

  try {
    change_password($username, $old_password, $new_password);

    success('Password changed successfully', '../../pages/home_page.php');
  } catch (Exception $e) {
    error($e->getMessage(), '../../pages/change_password_page.php');
  }

  /**
   * Function: change_password
   * --------------------------
   * Verifies the current password and updates to the new password.
   *
   * Parameters:
   *   $username - Username of the logged-in user
   *   $old_password - Current password (plaintext, for verification)
   *   $new_password - New password (plaintext, will be hashed)
   *
   * Throws Exception if:
   *   - User not found
   *   - Current password is incorrect
   *   - Password update fails
   */
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

    // Verify the provided old password matches the hash on file
    if (!password_verify($old_password, $old_hash)) {
      throw new Exception('Current password is incorrect');
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    /*
     * Password updates are restricted to the logged-in username only.
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
