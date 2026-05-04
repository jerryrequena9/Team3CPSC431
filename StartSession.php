<?php
session_start();

require_once('Adaptation.php');

function authenticatedUser()
{
  global $DBPasswords;

  return isset($_SESSION['UserName']) && !empty($_SESSION['UserName']) &&
         isset($_SESSION['UserRole']) && !empty($_SESSION['UserRole']) &&
         isset($DBPasswords[$_SESSION['UserRole']]);
}

// Use the logged-in user's role as the MySQL database user.
// If not logged in yet, use AUTH_ROLE from Adaptation.php.
if (authenticatedUser()) {
  $DBName = $_SESSION['UserRole'];
} else {
  $DBName = AUTH_ROLE;
}

$DBPassword = $DBPasswords[$DBName];

$db = new mysqli(DATA_BASE_HOST, $DBName, $DBPassword, DATA_BASE_NAME);

if ($db->connect_errno) {
  die("Error: failed to make a MySQL connection: " . $db->connect_error);
}
?>
