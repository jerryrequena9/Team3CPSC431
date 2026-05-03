<?php
require_once("html_components.php");

function sanitize_str($data) {
  return htmlspecialchars(trim($data));
}

function filled_out($form_vars) {
  // test that each variable has a value
  foreach ($form_vars as $key => $value) {
    if ((!isset($key)) || ($value == '')) {
      return false;
    }
  }
  return true;
}

function valid_email($address) {
  return preg_match('/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/', $address);
}

// TODO: finish implementation, add roles, USE PREPARED STATEMENTS
function register($first_name, $last_name, $username, $email, $password) {
  // register new person with db
  // return true or error message
  // connect to db
  $conn = db_connect();
  // check if username is unique
  $result = $conn->query("select * from UserAccount where username='".$username."'");
  if (!$result) {
    throw new Exception('Could not execute query');
  }
  if ($result->num_rows > 0) {
    throw new Exception('That username is taken - go back and choose another one.');
  }

  // if ok, put in db
  $result = $conn->query("insert into UserAccount values");
  if (!$result) {
    throw new Exception('Could not register you in database - please try again later.');
  }
  return true;
}

function check_valid_user() {
  // see if somebody is logged in and notify them if not
  if (isset($_SESSION['valid_user'])) {
    echo "Logged in as ".$_SESSION['valid_user'].".<br>";
  } else {
    // they are not logged in
    do_html_heading('Problem:');
    echo 'You are not logged in.<br>';
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
    exit;
  }
}

?>
