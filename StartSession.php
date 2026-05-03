<?php
  session_start();
  require_once('Adaptation.php');
  require_once('helpers.php');

  if( authenticatedUser() )  $DBName = $_SESSION['UserRole'];
  else                       $DBName = AUTH_ROLE;

  $DBPassword  = $DBPasswords[$DBName];

  function db_connect() {
    $db = new mysqli(DATA_BASE_HOST, $DBName, $DBPassword, DATA_BASE_NAME);

    if( $db->connect_errno != 0)  // if connection not successful
    {
      echo "Error: failed to make a MySQL connection:  " . $db->connect_error . "<br/>";
      return -1;
    }
    printf("Connected to DB as '%s'/'%s'<br/>", $DBName, $DBPassword);

    return $db;
  }

?>
