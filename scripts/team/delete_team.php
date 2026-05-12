<?php
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../../pages/html_components.php");
    require_once(__DIR__ . "/../helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields are missing", "../../pages/team_page.php");
    }

    global $db;

    $team_id = intval($_POST['team_id']);

    // Delete team
    $query = "
        DELETE FROM Team
        WHERE team_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $team_id);
    
    try {
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            error("Team not deleted", "../../pages/team_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) {
            error("Team not deleted. Team is referenced by another entity", "../../pages/team_page.php");
        }
        error("Team not deleted", "../../pages/team_page.php");
    } finally {
        $stmt->close();
    }

    success("Deleted team", "../../pages/team_page.php");
?>
