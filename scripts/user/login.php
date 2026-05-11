<?php
// Include session handling and database connection
require_once(__DIR__ . "/../StartSession.php");

// Include helper functions (e.g., sanitize_str)
require_once(__DIR__ . "/../helpers.php");

// Include HTML components for header/footer display
require_once(__DIR__ . "/../../pages/html_components.php");

// Check if form data was submitted
if (!is_valid_post($_POST)) {
    // If not, redirect back to login page
    error("Required fields are missing", "../../pages/login_page.php");
}

$username = trim($_POST['login_username']);
$password = trim($_POST['login_password']);

try {
    // Attempt to log the user in
    login($username, $password);

    // If successful, redirect to home page
    success('User logged in', '../../pages/home_page.php');

} catch (Exception $e) {
    // If login fails, display error message
    error($e->getMessage(), "../../pages/login_page.php");
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
        JOIN Role r
            ON u.role_id = r.role_id
        WHERE u.username = ?
    ";

    // Prepare SQL statement
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("s", $username);
    try {
        $stmt->execute();
        $stmt->store_result();

        $stmt->bind_result($user_id, $db_username, $hash, $role);
        if (!$stmt->fetch()) {
            throw new Exception("Invalid credentials.");
        }

    } catch (mysqli_sql_exception $e) {
        throw new Exception("Invalid credentials.");
    } finally {
        $stmt->close();
    }

    // Verify password
    if (!password_verify($password, $hash)) {
        throw new Exception("Invalid credentials");
    }

    // Login successful
    // Query to update user login time
    $query = "
        UPDATE UserAccount
        SET last_login = NOW()
        WHERE username = ?
    ";

    // Prepare SQL statement
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("s", $username);
    
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
      throw new Exception("Invalid credentials.");
    } finally {
        $stmt->close();
    }

    // Store session variables
    $_SESSION['UserName'] = $db_username;
    $_SESSION['UserRole'] = $role;
    $_SESSION['UserID'] = $user_id;

    // Fetch additional role-specific data (coach team_id or player_id)
    $query = "
        SELECT COALESCE(c.team_id, p.player_id) as role_specific_id,
               CASE 
                   WHEN c.coach_id IS NOT NULL THEN 'team_id'
                   WHEN p.player_id IS NOT NULL THEN 'player_id'
               END as id_type
        FROM UserAccount u
        LEFT JOIN Coach c ON u.user_id = c.user_id
        LEFT JOIN Player p ON u.user_id = p.user_id
        WHERE u.user_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $user_id);
    
    try {
        $stmt->execute();
        $stmt->bind_result($role_id, $id_type);
        if ($stmt->fetch()) {
            // Store role-specific IDs for permission checks
            if ($id_type === 'team_id' && $role_id !== null) {
                $_SESSION['team_id'] = $role_id;
            } elseif ($id_type === 'player_id' && $role_id !== null) {
                $_SESSION['player_id'] = $role_id;
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Role-specific data is optional; continue without it
    } finally {
        $stmt->close();
    }
}
?>
