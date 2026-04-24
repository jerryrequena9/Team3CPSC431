<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  $first_name = sanitize_str($_POST['first_name']);
  $last_name = sanitize_str($_POST['last_name']);
  $email = sanitize_str($_POST['email']);
  $username = sanitize_str($_POST['username']);
  $password = sanitize_str($_POST['password']);
  $confirm_password = sanitize_str($_POST['confirm_password']);

  try {
    // check forms filled in
    if (!filled_out($_POST)) {
      throw new Exception('You have not filled the form out correctly –
      please go back and try again.');
    }

    // email address not valid
    if (!valid_email($email)) {
      throw new Exception('That is not a valid email address.
      Please go back and try again.');
    }
    // passwords not the same
    if ($password != $confirm_password) {
      throw new Exception('The passwords you entered do not match –
      please go back and try again.');
    }
    // check password length is ok
    // ok if username truncates, but passwords will get
    // munged if they are too long.
    if ((strlen($passwd) < 6) || (strlen($passwd) > 16)) {
      throw new Exception('Your password must be between 6 and 16 characters.
      Please go back and try again.');
    }
    // attempt to register
    // this function can also throw an exception
    // TODO:
    register($username, $email, $passwd);
    // register session variable
    $_SESSION['valid_user'] = $username;
    // provide link to members page
    do_html_header('Registration successful');
    echo 'Your registration was successful!';
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
  }
  catch (Exception $e) {
    do_html_header('Problem:');
    echo $e->getMessage();
    echo "<a href='register_user_page.php'>Try again</a>";
    do_html_footer();
    exit;
  }

?>
