<?php
// HW3: Insert a new player address record into MySQL (TeamRoster table)
// This file is the form handler for the "Add Name and Address" form on home_page.php

require_once(__DIR__ . '/Adaptation.php');

/*
-------------------------------------------------------
 REQUIRE HTTP BASIC AUTH
No session control is used in this assignment.
-------------------------------------------------------
*/
if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="HW3 Basketball Login"');
  header('HTTP/1.0 401 Unauthorized');
  echo "Authentication required.";
  exit;
}

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];


/*
-------------------------------------------------------
AUTHENTICATE USER USING auth_user
This account only reads the Users table.
-------------------------------------------------------
*/
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$authConn = new mysqli(
  DATA_BASE_HOST,
  AUTH_USER_NAME,
  AUTH_USER_PASSWORD,
  DATA_BASE_NAME
);
$authConn->set_charset('utf8mb4');

$authSql = "SELECT password_hash, role, roster_id
            FROM Users
            WHERE username = ?";

$authStmt = $authConn->prepare($authSql);
$authStmt->bind_param("s", $username);
$authStmt->execute();
$authStmt->bind_result($password_hash, $role, $roster_id);

if (!$authStmt->fetch()) {
  $authStmt->close();
  $authConn->close();
  die("Invalid username.");
}

$authStmt->close();
$authConn->close();

if (!password_verify($password, $password_hash)) {
  die("Invalid password.");
}


/*
-------------------------------------------------------
APPLICATION-LEVEL AUTHORIZATION
Only managers may add new player/address records.
Coaches and players are denied for this insert action.
-------------------------------------------------------
*/
if ($role !== 'manager') {
  die("Access denied: only managers may add player address records.");
}


/*
-------------------------------------------------------
READ + SANITIZE POST INPUTS
-------------------------------------------------------
*/
$first   = isset($_POST['firstName']) ? trim($_POST['firstName']) : "";
$last    = isset($_POST['lastName'])  ? trim($_POST['lastName'])  : "";
$street  = isset($_POST['street'])    ? trim($_POST['street'])    : "";
$city    = isset($_POST['city'])      ? trim($_POST['city'])      : "";
$state   = isset($_POST['state'])     ? trim($_POST['state'])     : "";
$country = isset($_POST['country'])   ? trim($_POST['country'])   : "";
$zip     = isset($_POST['zipCode'])   ? trim($_POST['zipCode'])   : "";


/*
-------------------------------------------------------
VALIDATE REQUIRED INPUT
Name_Last is NOT NULL in the schema, so last name is required.
-------------------------------------------------------
*/
if ($last !== "") {

  /*
  -------------------------------------------------------
 CONNECT USING ROLE-BASED DATABASE ACCOUNT
  Manager DB account should have INSERT permission on TeamRoster.
  -------------------------------------------------------
  */
  $creds = getDatabaseCredentials($role);

  if ($creds === null) {
    die("Database credentials not found for role.");
  }

  $conn = new mysqli(
    DATA_BASE_HOST,
    $creds['username'],
    $creds['password'],
    DATA_BASE_NAME
  );
  $conn->set_charset('utf8mb4');

  /*
  -------------------------------------------------------
INSERT INTO TeamRoster
  -------------------------------------------------------
  */
  $sql = "INSERT INTO TeamRoster
            (Name_First, Name_Last, Street, City, State, Country, ZipCode)
          VALUES (?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssss", $first, $last, $street, $city, $state, $country, $zip);
  $stmt->execute();

  $stmt->close();
  $conn->close();
}


/*
-------------------------------------------------------
REDIRECT BACK TO HOME PAGE
-------------------------------------------------------
*/
header("Location: home_page.php");
exit;
?>
