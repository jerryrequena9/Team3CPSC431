<?php
    /**
     * Edit Coach Information
     * 
     * Permissions:
     * - Coaches can only edit their own personal information (name, team assignment)
     * - Managers can edit any coach's information
     * 
     */
    
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST, ['team_id'])) {
        error("Required fields missing", "../../pages/coach_page.php");
    }

    $coach_id = intval($_POST['coach_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    // Coaches can have no team
    if (!empty($_POST['team_id'])) {
        $team_id = intval($_POST['team_id']);
    } else {
        $team_id = null;
    }

    global $db;

    /**
     * Permission check: verify user has authority to edit this coach.
     * - Coaches can only edit themselves
     * - Managers can edit any coach
     */

    /**
     * If user is a coach, verify they are editing their own record.
     * This prevents coaches from editing other coaches' information.
     */
    // Query to get the coach_id for this user
    $query = "
        SELECT coach_id
        FROM Coach
        WHERE user_id = ?
    ";
    
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $_SESSION['UserID']);
    
    // Ensure that a coach can only edit his/her own information
    try {
        $stmt->execute();
        $stmt->bind_result($user_coach_id);
        if ($stmt->fetch() && intval($user_coach_id) !== $coach_id) {
            error("You can only edit your own information", "../../pages/coach_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Permission denied", "../../pages/coach_page.php");
    } finally {
        $stmt->close();
    }

    // Update coach information
    $query = "
        UPDATE Coach
        SET first_name = ?, last_name = ?, team_id = ?
        WHERE coach_id = ?
    ";
    
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ssii", $first_name, $last_name, $team_id, $coach_id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            $stmt->close();
            error("Coach not found", "../../pages/coach_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Coach was not updated", "../../pages/coach_page.php");
    } finally {
        $stmt->close();
    }
    
    success("Coach updated successfully", "../../pages/coach_page.php");
?>
