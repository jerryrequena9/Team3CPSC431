<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST)) {
        display_error_exit("required fields are missing");
    }

    global $db;
    $team_id = intval($_POST['team_id']);

    try {
        $query = "DELETE FROM Team WHERE team_id = ?";
        $stmt  = prepare_with_perms($db, $query);
        if (!$stmt || !$stmt->bind_param("i", $team_id)) {
            display_error_exit("failed to delete team");
        }
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // update fk constraint error
        // https://mariadb.com/docs/server/reference/error-codes/mariadb-error-codes-1400-to-1499/e1452
        if ($e->getCode() == 1451) {
            display_error_exit("cannot delete team: team has existing games or players");
        }
        display_error_exit("failed to delete team");
    }

    header("Location: manage_team_page.php");
    exit;
?>