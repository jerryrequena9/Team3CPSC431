<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  // Check that all fields are filled
  if (!filled_out($_POST)) {
    header('Location: register_user_page.php');
    exit;
  }

  try {
    /* $first_name = sanitize_str($_POST['first_name']); */
    /* $last_name = sanitize_str($_POST['last_name']); */
    $email = sanitize_str($_POST['email']);
    $username = sanitize_str($_POST['username']);
    $password = sanitize_str($_POST['password']);
    $confirm_password = sanitize_str($_POST['confirm_password']);

    // Register user
    register($first_name, $last_name, $username, $email, $passwd);
    // Registration successful -- prompt user to login
    do_html_header('Registration Successful');
    echo 'Your registration was successful!';
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
  }
  catch (Exception $e) {
    do_html_header('Problem');
    echo $e->getMessage();
    echo "<a href='register_user_page.php'>Register</a>";
    do_html_footer();
    exit;
  }

  function register($first_name, $last_name, $username, $email, $password) {
    // Check that the email is valid
    if (!valid_email($email)) {
      throw new Exception('That is not a valid email address.
      Please go back and try again.');
    }
    // Check that the passwords match
    if ($password != $confirm_password) {
      throw new Exception('The passwords you entered do not match –
      please go back and try again.');
    }
    // Check for valid password length
    if ((strlen($passwd) < 6) || (strlen($passwd) > 16)) {
      throw new Exception('Your password must be between 6 and 16 characters.
      Please go back and try again.');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "
      INSERT INTO UserAccount (username, password_hash, email)
      VALUES (?, ?, ?)
    ");
    $stmt = $db->prepare($query);
    if (!$stmt || !$stmt->bind_param("sss", $username, $hashed_password, $email) || !$stmt->execute()) {
      // duplicate entry
      // source: https://mariadb.com/docs/server/reference/error-codes/mariadb-error-codes-1000-to-1099/e1062
      if ($db->errno === 1062) {
        throw new Exception('That username is already taken.');
      }
      throw new Exception('Could not register the user.');
    }
    $stmt->close();
  }

?>
