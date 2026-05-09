<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  if (!is_valid_post($_POST)) {
    header('Location: forgot_password_page.php?error=missing_fields');
    exit;
  }

  $username = sanitize_str($_POST['forgot_username']);

  if ($username === '') {
    header('Location: forgot_password_page.php?error=missing_username');
    exit;
  }

  try {
    /*
     * Reset the password to a temporary random password.
     * A stronger production system would use one-time password reset tokens
     * instead of emailing a temporary password.
     */
    $password = reset_password($username);

    email_password($username, $password);

    header('Location: login_page.php?success=password_reset_email_sent');
    exit;

  } catch (Exception $e) {
    header('Location: forgot_password_page.php?error=' . urlencode($e->getMessage()));
    exit;
  }

  function reset_password($username) {
    global $db;

    /*
     * Generate a stronger temporary password than the previous 8 hex chars.
     * random_bytes is preferred for security-sensitive random values.
     */
    $new_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    /*
     * The database account must have UPDATE permission on UserAccount.
     * This function does not rely on role UI checks because forgot password
     * happens before login.
     */
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?
    ";

    $stmt = prepare_with_perms($db, $query);

    if (!$stmt || !$stmt->bind_param("ss", $hashed_password, $username) || !$stmt->execute()) {
      throw new Exception('could_not_change_password');
    }

    if ($stmt->affected_rows === 0) {
      throw new Exception('user_not_found');
    }

    $stmt->close();

    return $new_password;
  }

  function email_password($username, $password) {
    global $db;

    /*
     * Look up the email address from the database instead of trusting form data.
     */
    $query = "
      SELECT email
      FROM UserAccount
      WHERE username = ?
    ";

    $stmt = prepare_with_perms($db, $query);

    if (!$stmt || !$stmt->bind_param("s", $username) || !$stmt->execute()) {
      throw new Exception('could_not_send_email');
    }

    $stmt->bind_result($email);

    if (!$stmt->fetch()) {
      throw new Exception('user_not_found');
    }

    $stmt->close();

    /*
     * For a real production app, do not email passwords.
     * Use a password reset token with an expiration time instead.
     */
    $from = "From: support@football\r\n";
    $message =
      "Your temporary football password is: " . $password . "\r\n" .
      "Please log in and change your password immediately.\r\n";

    if (!mail($email, 'Football password reset', $message, $from)) {
      throw new Exception('could_not_send_email');
    }
  }
?>
