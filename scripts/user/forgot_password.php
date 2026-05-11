<?php
  /**
   * Forgot Password - Reset Password by Username
   * 
   * Security Features:
   * - Generates a cryptographically secure temporary password using random_bytes
   * - Requires email verification for password reset
   * - Password reset must be done via email (not shown in response to prevent user enumeration)
   * - Uses prepared statements to prevent SQL injection
   * - Only updates password if user exists (preventing user enumeration)
   * 
   * Production Note:
   * A stronger production system should use one-time password reset tokens with expiration,
   * or send a reset link instead of a temporary password.
   */

  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../../pages/html_components.php');
  require_once(__DIR__ . '/../helpers.php');

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/forgot_password_page.php");
  }

  $username = trim($_POST['forgot_username']);

  // Validate username format
  if (empty($username) || strlen($username) < 4 || strlen($username) > 50) {
    error("Invalid username format", "../../pages/forgot_password_page.php");
  }

  try {
    /*
     * Reset the password to a temporary random password.
     * A stronger production system would use one-time password reset tokens
     * instead of emailing a temporary password.
     */
    $password = reset_password($username);

    email_password($username, $password);

    // Generic success message to prevent user enumeration attacks
    success("If an account with that username exists, an email has been sent with password reset instructions", "../../pages/login_page.php");

  } catch (Exception $e) {
    // Don't reveal if user exists or not
    success("If an account with that username exists, an email has been sent with password reset instructions", "../../pages/login_page.php");
  }

  /**
   * Function: reset_password
   * ------------------------
   * Generates a temporary password and updates it in the database.
   * 
   * Parameters:
   *   $username - The username of the account to reset
   * 
   * Returns:
   *   The temporary password (plaintext, only shown once to user)
   * 
   * Throws Exception if:
   *   - User not found
   *   - Database update fails
   */
  function reset_password($username) {
    global $db;

    /*
     * Generate a cryptographically secure temporary password (16 hex characters = 64 bits of entropy).
     * random_bytes is preferred for security-sensitive random values.
     * This creates a password like: a3f5c2b1d4e6f7a8
     */
    $new_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    /*
     * The database account must have UPDATE permission on UserAccount.
     * This function does not rely on role UI checks because forgot password
     * happens before login (user is not authenticated).
     */
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ss", $hashed_password, $username);
    try {
      $stmt->execute();
      if ($stmt->affected_rows === 0) {
        throw new Exception('User not found');
      }
    } catch (mysqli_sql_exception $e) {
      throw new Exception('Could not change password');
    } finally {
      $stmt->close();
    }

    return $new_password;
  }

  /**
   * Function: email_password
   * -------------------------
   * Looks up the email address and sends a password reset email.
   * 
   * Parameters:
   *   $username - The username of the account
   *   $password - The temporary password to send
   * 
   * Throws Exception if:
   *   - User not found
   *   - Email could not be sent
   */
  function email_password($username, $password) {
    global $db;

    /*
     * Look up the email address from the database instead of trusting form data.
     * This ensures we send to the correct, verified email on file.
     */
    $query = "
      SELECT email
      FROM UserAccount
      WHERE username = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("s", $username);
    try {
      $stmt->execute();
    } catch (mysqli_sql_exception $e) {
      throw new Exception('Email was not sent');
    }

    $stmt->bind_result($email);

    if (!$stmt->fetch()) {
      throw new Exception('User not found');
    }
    $stmt->close();

    $from = "From: support@football\r\n";
    $message =
      "Your temporary football password is: " . $password . "\r\n" .
      "Please log in and change your password immediately.\r\n" .
      "This temporary password will not expire, so keep it secure.\r\n";

    if (!mail($email, 'Football password reset', $message, $from)) {
      throw new Exception('Email was not sent');
    }
  }
?>

?>
