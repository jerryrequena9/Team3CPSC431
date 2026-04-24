<?php
  // TODO: integrate form data and rewrite in general
  require_once( 'StartSession.php' );
  // home page after user has logged in successfully

  require_once('helpers.php');
  require_once('StartSession.php');

  //create short variable names
  if (!isset($_POST['username'])) {
    //if not isset -> set with dummy value
    $_POST['username'] = " ";
  }
  $username = sanitize_str($_POST['username']);
  if (!isset($_POST['password'])) {
    //if not isset -> set with dummy value
    $_POST['password'] = " ";
  }
  $passwd = sanitize_str($_POST['password']);
  if ($username && $password) {
    // they have just tried logging in
    try {
      login($username, $password);
    }
    catch(Exception $e) {
      // unsuccessful login
      do_html_header('Problem:');
      echo 'You could not be logged in.<br>Error: '.$e->getMessage().'<br>You must be logged in to view this page.';
      echo "<a href='login_page.php'>Login</a>";
      do_html_footer();
      exit;
    }
  }

  require('home_page.php');

  function login($username, $password) {
    // TODO: fix query
    $query = "SELECT
                Roles.roleName, UserAccount.Password
              FROM
                UserLogin, Roles
               WHERE
                  UserName = ?  AND
                  UserLogin.Role = Roles.ID_Role";

    if(($stmt = $db->prepare($query)) === FALSE) {
      throw new Exception("Failed to prepare query");
    }

    if(($stmt->bind_param('s', $userName)) === FALSE) {
      throw new Exception("Failed to bind query parameters to query");
    }

    if(!($stmt->execute() && $stmt->store_result() && $stmt->num_rows === 1)) {
      throw new Excception("Existing user $userName not found");
    }

    if( ($stmt->bind_result($roleName, $PWHash)) === FALSE ) {
      throw new Exception("Failed to bind query results to local variables");
    }

    if(($stmt->fetch()) === FALSE) {
      throw new Exception("Failed to fetch query results");
    }

    if (!password_verify($password, $PWHash)) {
      throw new Exception("Password is incorrect");
    }

    $_SESSION['username'] = $userName;
    $_SESSION['user_role'] = $roleName;
  }
?>
