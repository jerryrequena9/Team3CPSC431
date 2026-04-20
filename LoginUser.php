<?php
  require_once( 'StartSession.php' );

  // Test data here, but you would replace with your Login Form data processing
  $userName = strtolower('JProg');  // design decision: usernames are case insensitive
  $password = 'SomethingClever';

  $query = "SELECT
              Roles.roleName, UserLogin.Password
            FROM
              UserLogin, Roles
             WHERE
                UserName = ?  AND
                UserLogin.Role = Roles.ID_Role";

  if( ($stmt = $db->prepare($query)) === FALSE )
  {
    echo "Error: failed to prepare query: ". $db->error . "<br/>";
    return -2;
  }

  if( ($stmt->bind_param('s', $userName)) === FALSE )
  {
    echo "Error: failed to bind query parameters to query: ". $db->error . "<br/>";
    return -3;
  }

  if( !($stmt->execute() && $stmt->store_result() && $stmt->num_rows === 1) )
  {
    echo "Login attempt failed<br/>";
    // echo "Failure: existing user '$userName' not found<br/>";
    echo "-- display login form --<br/>";
    return -4;
  }

  if( ($stmt->bind_result($roleName, $PWHash)) === FALSE )
  {
    echo "Error: failed to bind query results to local variables: ". $db->error . "<br/>";
    return -5;
  }


  if( ($stmt->fetch()) === FALSE )
  {
    echo "Error: failed to fetch query results: ". $db->error . "<br/>";
    return -6;
  }

  if (! password_verify($password, $PWHash))
  {
    echo "Login attempt failed<br/>";
    // echo 'Password is valid!';
    echo "-- display login form --<br/>";
    return -7;
  }

  // Login successful at this point, do some book keeping ...
  echo "Login successful for user '$userName' as '$roleName'<br/>";
  $_SESSION['UserName'] = $userName;
  $_SESSION['UserRole'] = $roleName;
?>
