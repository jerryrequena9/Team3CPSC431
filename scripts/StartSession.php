<?php
session_start();

// TODO: remove later
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/Adaptation.php');

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
  exit("Error: failed to make a MySQL connection");
}

if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
  echo '<p style="color: red">Error: '.htmlspecialchars($_SESSION['error']).'</p><br>';
} else if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
  echo '<p style="color: green">Success: '.htmlspecialchars($_SESSION['success']).'</p><br>';
}

unset($_SESSION['error']);
unset($_SESSION['success'])
?>
