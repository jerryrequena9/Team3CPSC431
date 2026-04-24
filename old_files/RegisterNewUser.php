<?php
  require_once( 'StartSession.php' );


  // Test data here, but you would replace with your User Registration Form data processing
  $firstName = 'Joe';
  $lastName  = 'Programmer';
  $eMail     = 'Joe.Programmer@fullerton.edu';
  $userName  = strtolower('JProg');  // design decision: usernames are case insensitive
  $password  = 'SomethingClever';
  // Role will be defaulted in DB, do not set here


  $query = "INSERT INTO UserLogin SET
              Name_First = ?,
              Name_Last  = ?,
              Email      = ?,
              UserName   = ?,
              Password   = ?";

  if( ($stmt = $db->prepare($query)) === FALSE )
  {
    echo "Error: failed to prepare query: ". $db->error . "<br/>";
    return -2;
  }

  if( ($stmt->bind_param('sssss', $firstName, $lastName, $eMail, $userName, password_hash($password, PASSWORD_DEFAULT))) === FALSE )
  {
    echo "Error: failed to bind query parameters to query: ". $db->error . "<br/>";
    return -3;
  }


  if( ($stmt->execute() && $stmt->affected_rows === 1) )
  {
    echo "Success: new user '$userName' created<br/>";
    echo "-- display login form --<br/>";
  }
  else                              // failure
  {
    echo "Failure: new user '$userName' not created:  " . $db->error . "<br/>";
    echo "-- redisplay registration form --<br/>";
  }
?>
