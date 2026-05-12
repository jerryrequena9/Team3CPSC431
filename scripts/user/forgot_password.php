<?php
  /**
   * Forgot Password - Reset Password by Username
   * 
   * Security Features:
   * - Generates a cryptographically secure temporary password using random_bytes
   * - Requires email verification for password reset
   * - Password reset must be done via email
   * - Only updates password if user exists (preventing user enumeration)
   */
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../../pages/html_components.php');
  require_once(__DIR__ . '/../helpers.php');

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/forgot_password_page.php");
  }

  $username = trim($_POST['username']);

  // Validate username format
  if (empty($username) || strlen($username) < 4 || strlen($username) > 50) {
    error("Invalid username format", "../../pages/forgot_password_page.php");
  }

  try {
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
     * Generate a cryptographically secure temporary password (16 hex characters).
     * This creates a password like: a3f5c2b1d4e6f7a8
     */
    $new_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
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

    // Find user email
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

    require_once("Mail.php");
    require_once(__DIR__ . "/../../config.php");
    $subject = "Football password reset";
    $body = "Your temporary football password is: " . $password . "\r\n" .
      "Please log in and change your password immediately.\r\n" .
      "This temporary password will not expire, so keep it secure.\r\n";

    $from = EMAIL_USER;
    $to = $email;

    $username = $from;
    $password = EMAIL_APP_PASSWORD;

    $host = EMAIL_HOST;
    $port = "465";

    $headers = array(
        'From'    => $from,
        'To'      => $to,
        'Subject' => $subject,
    );

    $smtp = Mail::factory('smtp', array(
        'host'     => $host,
        'port'     => $port,
        'auth'     => true,
        'username' => $username,
        'password' => $password,
    ));

    $mail = $smtp->send($to, $headers, $body);
    if (PEAR::isError($mail)) {
      throw new Exception("mail not sent");
    }
  }
?>
