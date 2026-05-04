<?php
require_once("html_components.php");

/**
 * Sanitize input string
 */
function sanitize_str($data) {
  return htmlspecialchars(trim($data));
}

/**
 * Check that all form fields are filled out
 */
function filled_out($form_vars) {
  foreach ($form_vars as $value) {
    if (!isset($value) || $value === '') {
      return false;
    }
  }
  return true;
}

/**
 * Validate email format
 */
function valid_email($address) {
  return preg_match('/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/', $address);
}

/**
 * Check if user is logged in (UPDATED TO MATCH YOUR LOGIN SYSTEM)
 */
function check_valid_user() {

  // Check session variables set during login
  if (isset($_SESSION['UserName']) && isset($_SESSION['UserRole'])) {

    echo "Logged in as <b>" . $_SESSION['UserName'] . "</b> (" . $_SESSION['UserRole'] . ")<br><br>";

  } else {

    // User not logged in
    do_html_header('Problem');
    echo 'You are not logged in.<br>';
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
    exit;
  }
}

/**
 * Optional helper: enforce role-based access (use later)
 */
function require_role($role) {
  if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] !== $role) {
    do_html_header('Access Denied');
    echo "You do not have permission to access this page.<br>";
    do_html_footer();
    exit;
  }
}

?>
