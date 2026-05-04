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
 * Require a valid logged-in user.
 */
function check_valid_user() {
  if (!authenticatedUser()) {
    do_html_header('Problem');
    echo 'You are not logged in.<br>';
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
    exit;
  }

  echo "Logged in as <b>" . htmlspecialchars($_SESSION['UserName']) . "</b> ";
  echo "Role: <b>" . htmlspecialchars($_SESSION['UserRole']) . "</b><br><br>";
}

/**
 * Require a specific role.
 */
function require_role($role) {
  if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] !== $role) {
    do_html_header('Access Denied');
    echo "You do not have permission to access this page.<br>";
    do_html_footer();
    exit;
  }
}

/**
 * Require one of several allowed roles.
 */
function require_any_role($roles) {
  if (!isset($_SESSION['UserRole']) || !in_array($_SESSION['UserRole'], $roles)) {
    do_html_header('Access Denied');
    echo "You do not have permission to access this page.<br>";
    do_html_footer();
    exit;
  }
}

?>
