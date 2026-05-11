<?php
    /**
     * Edit Player Information
     * 
     * Permissions:
     * - Players can only edit their own personal information (name, position, status)
     * - Managers can edit any player's information
     * 
     * Security Notes:
     * - Permission checks prevent players from modifying other players' data
     * - Database-level permissions provide additional enforcement
     */

    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/player_page.php");
    }

    $player_id = intval($_POST['player_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $position = trim($_POST['position']);
    $status = trim($_POST['status']);

    // Validate player_id is a positive integer
    if ($player_id <= 0) {
        error("Invalid player ID", "../../pages/player_page.php");
    }

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($position) || empty($status)) {
        error("All fields are required", "../../pages/player_page.php");
    }

    // Validate position is exactly 2 characters
    if (strlen($position) !== 2) {
        error("Position must be exactly 2 characters", "../../pages/player_page.php");
    }

    // Validate status is one of the allowed values
    $valid_statuses = ['Active', 'Inactive'];
    if (!in_array($status, $valid_statuses)) {
        error("Invalid status value", "../../pages/player_page.php");
    }

    global $db;

    /**
     * Permission check: verify user has authority to edit this player.
     * - Players can only edit themselves
     * - Managers can edit any player
     */
    
    // Get user's role to determine permissions
    $query = "
        SELECT r.role_id, r.name
        FROM UserAccount u
        JOIN Role r ON u.role_id = r.role_id
        WHERE u.user_id = ?
    ";
    
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $_SESSION['UserID']);
    
    try {
        $stmt->execute();
        $stmt->bind_result($user_role_id, $user_role_name);
        if (!$stmt->fetch()) {
            error("User role not found", "../../pages/player_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Could not determine permissions", "../../pages/player_page.php");
    } finally {
        $stmt->close();
    }

    // Get the Player role_id for comparison
    $query = "
        SELECT role_id
        FROM Role
        WHERE name = 'Player'
    ";
    
    $player_role_result = query_with_perms($db, $query);
    $player_role_row = $player_role_result->fetch_assoc();
    $player_role_id = intval($player_role_row['role_id']);

    /**
     * If user is a player, verify they are editing their own record.
     * This prevents players from editing other players' information.
     */
    if ($user_role_id == $player_role_id) {
        // Query to get the player_id for this user
        $query = "
            SELECT player_id
            FROM Player
            WHERE user_id = ?
        ";
        
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("i", $_SESSION['UserID']);
        
        try {
            $stmt->execute();
            $stmt->bind_result($user_player_id);
            if (!$stmt->fetch() || intval($user_player_id) !== $player_id) {
                error("You can only edit your own information", "../../pages/player_page.php");
            }
        } catch (mysqli_sql_exception $e) {
            error("Permission denied", "../../pages/player_page.php");
        } finally {
            $stmt->close();
        }
    }

    // Update player information
    $query = "
        UPDATE Player
        SET first_name = ?, last_name = ?, position = ?, status = ?
        WHERE player_id = ?
    ";

    try {
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("ssssi", $first_name, $last_name, $position, $status, $player_id);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            $stmt->close();
            error("Player not found", "../../pages/player_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Player not updated", "../../pages/player_page.php");
    } finally {
        $stmt->close();
    }
    
    success("Player updated", "../../pages/player_page.php");
?>
