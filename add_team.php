<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    check_valid_user();

    /*
     * Adding teams is Manager-only.
     * MySQL permissions should still enforce INSERT access on the Team table.
     */
    if ($_SESSION['UserRole'] !== 'Manager') {
        err_permission_denied();
    }

    if (!is_valid_post($_POST, ["stadium_id"])) {
        display_error_exit("required fields are missing");
    }

    global $db;

    $name = sanitize_str($_POST['name']);
    $city = sanitize_str($_POST['city']);
    $conference = sanitize_str($_POST['conference']);
    $division = sanitize_str($_POST['division']);

    /*
     * Teams are allowed to have no stadium.
     */
    $stadium_id = !empty($_POST['stadium_id']) ? intval($_POST['stadium_id']) : null;

    if ($name === '' || $city === '') {
        display_error_exit("team name and city are required");
    }

    /*
     * These are controlled football domain values, not permission logic.
     */
    $valid_conferences = ['NFC', 'AFC'];
    $valid_divisions = ['North', 'South', 'East', 'West'];

    if (!in_array($conference, $valid_conferences, true) || !in_array($division, $valid_divisions, true)) {
        display_error_exit("invalid conference or division");
    }

    if ($stadium_id !== null && $stadium_id <= 0) {
        display_error_exit("invalid stadium id");
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

    if (!$stmt || !$stmt->bind_param("ssssi", $name, $city, $conference, $division, $stadium_id) || !$stmt->execute()) {
        display_error_exit("failed to add team");
    }

    $stmt->close();

    header("Location: manage_team_page.php?success=team_added");
    exit;
?>
