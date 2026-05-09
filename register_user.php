<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  // Check that all fields are filled
  if (!is_valid_post($_POST)) {
    header('Location: register_user_page.php');
    exit;
  }

  $email = sanitize_str($_POST['register_email']);
  $username = sanitize_str($_POST['register_username']);
  $password = sanitize_str($_POST['register_password']);
  $confirm_password = sanitize_str($_POST['register_confirm_password']);

   try {
	 register($username, $email, $password, $confirm_password);
	 header('Location: login_page.php?success=Registration successful. Please log in.');
	  exit;
	}
	
   catch (Exception $e) {
	  do_html_header('Error');
	  echo $e->getMessage();
	  echo "<br><a href='register_user_page.php'>Register</a>";
	  do_html_footer();
	  exit;
	}

  function register($username, $email, $password, $confirm_password) {
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
    if (!validate_password($password)) {
      throw new Exception('The password does not meet the requirements.');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    global $db;
    /*
    all self-registered users become Fans
    nobody self-registers as Manager
    role always exists
    avoids broken accounts
    */
    $query = "
  	INSERT INTO UserAccount (username, password_hash, email, role_id)
  	SELECT ?, ?, ?, role_id
  	FROM Role
  	WHERE name = 'Fan'
	";
	
    $stmt = prepare_with_perms($db, $query);
    if (!$stmt->bind_param("sss", $username, $hashed_password, $email) || !$stmt->execute()) {
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
