<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    check_valid_user();

    /*
     * Deleting teams should be Manager-only.
     * This prevents Coach/Player/Fan users from posting directly to this file.
     */
    if ($_SESSION['UserRole'] !== 'Manager') {
        err_permission_denied();
    }

    if (!is_valid_post($_POST)) {
        display_error_exit("required fields are missing");
    }

    global $db;

    $team_id = intval($_POST['team_id']);

    if ($team_id <= 0) {
        display_error_exit("invalid team id");
    }

    try {
        /*
         * Actual delete permission should still be enforced by the Manager
         * database account having DELETE permission on Team.
         */
        $query = "
            DELETE FROM Team
            WHERE team_id = ?
        ";

        $stmt = prepare_with_perms($db, $query);

        if (!$stmt || !$stmt->bind_param("i", $team_id) || !$stmt->execute()) {
            display_error_exit("failed to delete team");
        }

        if ($stmt->affected_rows === 0) {
            $stmt->close();
            header("Location: manage_team_page.php?error=team_not_deleted");
            exit;
        }

        $stmt->close();

    } catch (Exception $e) {
        /*
         * Error 1451 means this team is referenced by another table.
         * Example: Game, Player_Team, Coach, Team_Season, or Season champion.
         */
        if ($e->getCode() == 1451) {
            header("Location: manage_team_page.php?error=cannot_delete_team_in_use");
            exit;
        }

        display_error_exit("failed to delete team");
    }

    header("Location: manage_team_page.php?success=team_deleted");
    exit;
?>
