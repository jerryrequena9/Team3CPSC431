<?php
  session_start();
  require_once( 'Adaptation.php' );
  
  // If user has already been authenticated
  function authenticatedUser()
  {
    global $DBPasswords;
    return  isset($_SESSION['UserName']) && !empty($_SESSION['UserName'])   &&
            isset($_SESSION['UserRole']) && !empty($_SESSION['UserRole'])   &&  
            isset($DBPasswords[$_SESSION['UserRole']]);
  }
  
  
  
  if( authenticatedUser() )  $DBName = $_SESSION['UserRole'];
  else                       $DBName = NO_ROLE;
  
  $DBPassword  = $DBPasswords[$DBName];

  
  printf("Connecting to DB as '%s'/'%s'<br/>", $DBName, $DBPassword);
  $db = new mysqli(DATA_BASE_HOST, $DBName, $DBPassword, DATA_BASE_NAME);
  
  if( $db->connect_errno != 0)  // if connection not successful
  {
    echo "Error: failed to make a MySQL connection:  " . $db->connect_error . "<br/>";
    return -1;
  }

?>
