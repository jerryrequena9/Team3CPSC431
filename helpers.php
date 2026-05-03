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

// If user has already been authenticated
function authenticatedUser()
{
  global $DBPasswords;
  return  isset($_SESSION['UserName']) && !empty($_SESSION['UserName'])   &&
          isset($_SESSION['UserRole']) && !empty($_SESSION['UserRole'])   &&
          isset($DBPasswords[$_SESSION['UserRole']]) && $_SESSION['UserRole'] != AUTH_ROLE;
}

function check_valid_user() {
  // see if somebody is logged in and notify them if not
  if (!authenticatedUser()) {
    do_html_heading('Problem');
    echo 'You are not logged in.<br>';
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
    exit;
  } else {
    echo "Logged in as ".$_SESSION['UserName']."Role: ".$_SESSION['UserRole']."<br>";
  }
}

?>
