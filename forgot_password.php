<?php
  require('html_components.php');
  require('helpers.php');

  do_html_header('Resetting Password');
  $username = sanitize_str($_POST['forgot_username']);
  try {
    $password = reset_password($username);
    email_password($username, $password);
    echo 'Your new password has been emailed to you.<br>';
  } catch (Exception $e) {
    echo 'Your password could not be reset.<br>';
  }

  echo "<a href='login_page.php'>Login</a>";
  do_html_footer();

  function reset_password($username) {
    // set password for username to a random value
    // return the new password or false on failure
    // get a random dictionary word b/w 6 and 13 chars in length
    $new_password = get_random_word(6, 13);
    if($new_password == false) {
      // give a default password
      $new_password = "changeMe!";
    }
    // add a number between 0 and 999 to it
    // to make it a slightly better password
    $rand_number = rand(0, 999);
    $new_password .= $rand_number;
    // set user's password to this in database or return false
    $conn = db_connect();
    $result = $conn->query(); // TODO: fill in
    if (!$result) {
      throw new Exception('Could not change password.'); // not changed
    } else {
      return $new_password; // changed successfully
    }
  }

  // NOTE: this function could be improved, I just copied its API for now
  function get_random_word($min_length, $max_length) {
    // grab a random word from dictionary between the two lengths
    // and return it
    // generate a random word
    $word = '';
    // remember to change this path to suit your system
    $dictionary = '/usr/dict/words'; // the ispell dictionary
    $fp = @fopen($dictionary, 'r');
    if(!$fp) {
      return false;
    }
    $size = filesize($dictionary);
    // go to a random location in dictionary
    $rand_location = rand(0, $size);
    fseek($fp, $rand_location);
    // get the next whole word of the right length in the file
    while ((strlen($word) < $min_length) || (strlen($word)>$max_length) ||
    (strstr($word, "'"))) {
      if (feof($fp)) {
        fseek($fp, 0); // if at end, go to start
      }
      $word = fgets($fp, 80); // skip first word as it could be partial
      $word = fgets($fp, 80); // the potential password
    }
    $word = trim($word); // trim the trailing \n from fgets
    return $word;
  }

  function email_password($username, $password) {
    // notify the user that their password has been changed
    $conn = db_connect();
    $result = $conn->query(); // TODO: fill in
    if (!$result) {
      throw new Exception('Could not find email address.');
    } else if ($result->num_rows == 0) {
      throw new Exception('Could not find email address.');
      // username not in db
    } else {
      $row = $result->fetch_object();
      $email = $row->email;
      $from = "From: support@football \r\n";
      $mesg = "Your football password has been changed to ".$password."\r\n".
      "Please change it next time you log in.\r\n";
      // NOTE: test this email functionality
      if (mail($email, 'Football login information', $mesg, $from)) {
        return true;
      } else {
      throw new Exception('Could not send email.');
      }
    }
  }
  }
?>
