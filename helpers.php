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
function is_valid_post($form_vars, $not_required=[]) {
  foreach ($form_vars as $key => $value) {
    if (!in_array($key, $not_required) && (!isset($value) || $value === '')) {
      return false;
    }
  }
  return $_SERVER['REQUEST_METHOD'] === 'POST';
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

  echo "Logged in as <b>" . sanitize_str($_SESSION['UserName']) . "</b> ";
  echo "Role: <b>" . sanitize_str($_SESSION['UserRole']) . "</b><br><br>";
}

// Generic error page for permission errors
// For logined users
function err_permission_denied() {
  header('Location: permission_denied_page.php');
  exit;
}

// Prepare with db permissions
// ALL prepares should use this function
function prepare_with_perms($db, $query) {
  try {
    $stmt = $db->prepare($query);
    return $stmt;
  } catch (Exception $e) {
    err_permission_denied();
  }
}

// Query with db permissions
// ALL queries should use this function
function query_with_perms($db, $query) {
  try {
    $result = $db->query($query);
    return $result;
  } catch (Exception $e) {
    err_permission_denied();
  }
}

// Validate password
function validate_password($password) {
  // Check for valid password length
  if ((strlen($password) < 6) || (strlen($password) > 16)) {
    return false;
  }
  return true;
}
?>
