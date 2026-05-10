<?php
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../../pages/html_components.php");
    require_once(__DIR__ . "/../helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST, ["stadium_id"])) {
        error("Required fields are missing", "../../pages/team_page.php");
    }

    global $db;

    $name = trim($_POST['name']);
    $city = trim($_POST['city']);
    $conference = trim($_POST['conference']);
    $division = trim($_POST['division']);
    $stadium_id = intval($_POST['stadium_id']);

    /*
     * These are controlled football domain values, not permission logic.
     */
    $valid_conferences = ['NFC', 'AFC'];
    $valid_divisions = ['North', 'South', 'East', 'West'];

    if (!in_array($conference, $valid_conferences, true)) {
        error("Invalid conference", "../../pages/team_page.php");
    } else if (!in_array($division, $valid_divisions, true)) {
        error("Invalid division", "../../pages/team_page.php");
    }

    /*
     * The foreign key from Team.stadium_id to Stadium.stadium_id should reject
     * invalid stadium IDs. This keeps database integrity enforcement in MySQL.
     */
    $query = "
        INSERT INTO Team
            (name, city, conference, division, stadium_id)
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ssssi", $name, $city, $conference, $division, $stadium_id);
    
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            error("Team already exists", "../../pages/team_page.php");
        } else {
            error("Team not added", "../../pages/team_page.php");
        }
    } finally {
        $stmt->close();
    }

    success("Added team", "../../pages/team_page.php");
?>
