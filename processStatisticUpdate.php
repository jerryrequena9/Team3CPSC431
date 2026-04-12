<?php
require_once(__DIR__ . '/Adaptation.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  /*
  -------------------------------------------------------
  REQUIRE HTTP BASIC AUTH
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
  -------------------------------------------------------
  */
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
  VALIDATE ROLE
  -------------------------------------------------------
  */
  if (!in_array($role, ['manager', 'coach', 'player'])) {
    die("Access denied: invalid role.");
  }

  /*
  -------------------------------------------------------
  READ + SANITIZE INPUT
  -------------------------------------------------------
  */
  $playerId = isset($_POST['name_ID']) ? (int)$_POST['name_ID'] : 0;
  $statId   = isset($_POST['stat_id']) ? (int)$_POST['stat_id'] : 0;

  $time = isset($_POST['time'])
    ? trim(preg_replace("/\t|\R/", ' ', $_POST['time']))
    : "0:0";

  $mins = 0;
  $secs = 0;

  if (strpos($time, ':') !== false) {
    $parts = explode(':', $time);
    $mins  = isset($parts[0]) ? (int)$parts[0] : 0;
    $secs  = isset($parts[1]) ? (int)$parts[1] : 0;
  } else {
    $mins = (int)$time;
    $secs = 0;
  }

  if ($mins < 0 || $mins > 40 || $secs < 0 || $secs > 59 || ($mins === 0 && $secs === 0)) {
    die("Invalid time value.");
  }

  $points   = isset($_POST['points'])   ? (int)$_POST['points']   : 0;
  $assists  = isset($_POST['assists'])  ? (int)$_POST['assists']  : 0;
  $rebounds = isset($_POST['rebounds']) ? (int)$_POST['rebounds'] : 0;

  /*
  -------------------------------------------------------
  PLAYER APP-LEVEL AUTHORIZATION
  Players may only add statistics for themselves.
  -------------------------------------------------------
  */
  if ($role === 'player') {
    if ($roster_id === null || $playerId !== (int)$roster_id) {
      die("Access denied: players may only add statistics for themselves.");
    }
  }

  /*
  -------------------------------------------------------
  CONNECT USING ROLE-BASED DATABASE ACCOUNT
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
  ROLE-BASED STATISTICS ACTION
  -------------------------------------------------------
  */

  if ($role === 'coach') {
    /*
    -------------------------------------------------------
    COACH: UPDATE EXISTING STATISTIC ONLY
    Requires stat_id from form
    -------------------------------------------------------
    */
    if ($statId <= 0) {
      die("Invalid statistic record. Coach must select an existing statistic row to update.");
    }

    $sql = "UPDATE Statistics
            SET PlayingTimeMin = ?, PlayingTimeSec = ?, Points = ?, Assists = ?, Rebounds = ?
            WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiii", $mins, $secs, $points, $assists, $rebounds, $statId);
    $stmt->execute();
    $stmt->close();
  }

  elseif ($role === 'manager') {
    /*
    -------------------------------------------------------
    MANAGER: ADD NEW STATISTIC ROW
    -------------------------------------------------------
    */
    if ($playerId <= 0) {
      die("Invalid player ID.");
    }

    $sql = "INSERT INTO Statistics
              (Player, PlayingTimeMin, PlayingTimeSec, Points, Assists, Rebounds)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiii", $playerId, $mins, $secs, $points, $assists, $rebounds);
    $stmt->execute();
    $stmt->close();
  }

  elseif ($role === 'player') {
    /*
    -------------------------------------------------------
    PLAYER: ADD NEW STATISTIC ROW FOR SELF ONLY
    PHP already enforced playerId == roster_id
    -------------------------------------------------------
    */
    if ($playerId <= 0) {
      die("Invalid player ID.");
    }

    $sql = "INSERT INTO Statistics
              (Player, PlayingTimeMin, PlayingTimeSec, Points, Assists, Rebounds)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiii", $playerId, $mins, $secs, $points, $assists, $rebounds);
    $stmt->execute();
    $stmt->close();
  }

  $conn->close();

  /*
  -------------------------------------------------------
  REDIRECT BACK TO HOME PAGE
  -------------------------------------------------------
  */
  header("Location: home_page.php");
  exit;

} catch (Throwable $e) {
  echo "<h3>Actual Error</h3>";
  echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
