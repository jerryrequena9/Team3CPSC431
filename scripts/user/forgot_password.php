<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../../pages/html_components.php');
  require_once(__DIR__ . '/../helpers.php');

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/forgot_password_page.php");
  }

  $username = trim($_POST['forgot_username']);

  try {
    /*
     * Reset the password to a temporary random password.
     * A stronger production system would use one-time password reset tokens
     * instead of emailing a temporary password.
     */
    $password = reset_password($username);

    email_password($username, $password);

    success("An email has been sent with your new password", "../../pages/login_page.php");

  } catch (Exception $e) {
    error($e->getMessage(), "../../pages/forgot_password_page.php");
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
      "Please log in and change your password immediately.\r\n";

    if (!mail($email, 'Football password reset', $message, $from)) {
      throw new Exception('Email was not sent');
    }
  }
?>
