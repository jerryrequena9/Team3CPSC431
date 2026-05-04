<?php
  require_once('StartSession.php');
  require_once('html_components.php');
  require_once('helpers.php');

  do_html_header('Password Reset');

  if (!filled_out($_POST)) {
    header('Location: forgot_password_page.php');
    exit;
  }
  try {
    $username = sanitize_str($_POST['forgot_username']);
    $password = reset_password($username);
    email_password($username, $password);
    echo 'Your new password has been emailed to you.';
  } catch (Exception $e) {
    echo 'Your password could not be reset.';
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
    exit;
  }

  function reset_password($username) {
    // random password generation
    // source: https://stackoverflow.com/a/21498316
    $new_password = bin2hex(openssl_random_pseudo_bytes(4));
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    global $db;
    $query = "
      UPDATE UserAccount
      SET password_hash = ?
      WHERE username = ?;
    ";

    $stmt = $db->prepare($query);
    if (!$stmt || !$stmt->bind_param("ss", $hashed_password, $username) || !$stmt->execute()) {
      throw new Exception('Could not change the password.');
    }
    if ($stmt->affected_rows === 0) {
      throw new Exception('Could not find the user.');
    }
    $stmt->close();

    return $new_password;
  }

  function email_password($username, $password) {
    // notify the user that their password has been changed
    $query = "
      SELECT email
      FROM UserAccount
      WHERE username = ?
    ";
    if (!$stmt || !$stmt->bind_param("s", $username) || !$stmt->execute()) {
      throw new Exception('Could not send the email.');
    }
    $stmt->bind_result($email);
    if (!$stmt->fetch()) {
      throw new Exception('Could not find the user.');
    }
    $stmt->close();

    $from = "From: support@football \r\n";
    $mesg = "Your football password has been changed to ".$password."\r\n".
    "Please change it next time you log in.\r\n";
    // NOTE: not tested
    if (!mail($email, 'Football login information', $mesg, $from)) {
      throw new Exception('Could not send the email.');
    }
  }
?>
