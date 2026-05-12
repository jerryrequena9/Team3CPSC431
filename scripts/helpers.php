<?php
require_once(__DIR__ . "/Adaptation.php");
require_once(__DIR__ . "/../pages/html_components.php");
require_once(__DIR__ . "/../config.php");
/**
 * Sanitize input string
 */
function sanitize_str($data) {
  if (empty($data) || is_null($data)) {
    return $data;
  }
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

function error($err, $redirect) {
  $_SESSION['error'] = sanitize_str($err);
  header('Location: '.$redirect);
  exit;
}

function success($success, $redirect) {
  $_SESSION['success'] = sanitize_str($success);
  header('Location: '.$redirect);
  exit;
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

  echo "Logged in as <b>" . sanitize_str($_SESSION['UserName']) . "</b><br><br>";
}

// Generic error page for permission errors
function err_permission_denied() {
  header('Location: ' . BASE_URL . '/pages/permission_denied_page.php');
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
?>
