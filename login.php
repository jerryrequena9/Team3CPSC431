<?php
// Include session handling and database connection
require_once('StartSession.php');

// Include helper functions (e.g., sanitize_str)
require_once('helpers.php');

// Include HTML components for header/footer display
require_once('html_components.php');

// Check if form data was submitted
if (!filled_out($_POST)) {
    // If not, redirect back to login page
    header("Location: login_page.php");
    exit;
}

// Sanitize user input
$username = sanitize_str($_POST['username']);
$password = sanitize_str($_POST['password']);

try {
    // Attempt to log the user in
    login($username, $password);

    // If successful, redirect to home page
    header("Location: home_page.php");
    exit;

} catch (Exception $e) {
    // If login fails, display error message
    do_html_header('Login Failed');
    echo "Error: " . $e->getMessage() . "<br>";
    echo "<a href='login_page.php'>Login</a>";
    do_html_footer();
    exit;
}

/**
 * Function: login
 * ----------------
 * Authenticates a user using username and password.
 * On success, stores session variables for user identity and role.
 */
function login($username, $password) {

    // Access the global database connection from StartSession.php
    global $db;
    
    // Query to retrieve user credentials and role
    $query = "
        SELECT
            u.user_id,
            u.username,
            u.password_hash,
            r.name
        FROM UserAccount u
        JOIN Role r ON u.role_id = r.role_id
        WHERE u.username = ?
    ";

    // Prepare SQL statement
    $stmt = $db->prepare($query);
    if (!$stmt || !$stmt->bind_param("s", $username) || !$stmt->execute() || !$stmt->store_result()) {
      throw new Exception("Login failed");
    }

    // If no user found, throw error
    if ($stmt->num_rows !== 1) {
        throw new Exception("User not found");
    }

    // Bind results to variables
    if (!$stmt->bind_result($user_id, $db_username, $hash, $role) || !$stmt->fetch()) {
        throw new Exception("Login failed");
    }

    // Verify password using hashed password
    if (!password_verify($password, $hash)) {
        throw new Exception("Invalid password");
    }

    // Login successful
    // Query to update user login time
    $query = "
        UPDATE UserAccount
        SET last_login = NOW()
        WHERE username = ?
    ";

    // Prepare SQL statement
    $stmt = $db->prepare($query);
    if (!$stmt || !$stmt->bind_param("s", $username) || !$stmt->execute()) {
      throw new Exception("Login failed");
    }

    // Store session variables
    $_SESSION['UserName'] = $db_username;
    $_SESSION['UserRole'] = $role;
    $_SESSION['UserID'] = $user_id;
}
?>
