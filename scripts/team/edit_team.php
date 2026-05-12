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
    $name = trim($_POST['name']);
    $city = trim($_POST['city']);
    $conference = trim($_POST['conference']);
    $division = trim($_POST['division']);
    $stadium_id = intval($_POST['stadium_id']);

    /*
     * Valid conferences and divisions.
     */
    $valid_conferences = ['NFC', 'AFC'];
    $valid_divisions = ['North', 'South', 'East', 'West'];

    if (!in_array($conference, $valid_conferences, true)) {
        error("Invalid conference", "../../pages/team_page.php");
    }
    if (!in_array($division, $valid_divisions, true)) {
        error("Invalid division", "../../pages/team_page.php");
    }

    // Edit team
    $query = "
        UPDATE Team
        SET name = ?, city = ?, conference = ?, division = ?, stadium_id = ?
        WHERE team_id = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ssssii", $name, $city, $conference, $division, $stadium_id, $team_id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            error("Team not updated", "../../pages/team_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            if (strpos($stmt->error, "stadium") !== false) {
                error("Stadium is already assigned", "../../pages/team_page.php");
            } else if (strpos($stmt->error, "name" !== false)) {
                error("Team already exists", "../../pages/team_page.php");
            }
        } else {
            error("Team not updated", "../../pages/team_page.php");
        }
    } finally {
        $stmt->close();
    }

    success("Team updated", "../../pages/team_page.php");
?>
