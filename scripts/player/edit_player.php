<?php
    /**
     * Edit Player Information
     * 
     * Permissions:
     * - Players can only edit their own personal information (name, position, status)
     * - Managers can edit any player's information
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

    if ($status !== 'Active' || $status !== 'Inactive') {
        error("Status must be 'Active' or 'Inactive'", "../../pages/player_page.php");
    }

    global $db;

    /**
     * Permission check: verify user has authority to edit this player.
     * - Players can only edit themselves
     * - Managers can edit any player
     */
    
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
        if ($stmt->fetch() && intval($user_player_id) !== $player_id) {
            error("You may only edit your own information", "../../pages/player_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Permission denied", "../../pages/player_page.php");
    } finally {
        $stmt->close();
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
