<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST, ["stadium_id"])) {
        display_error_exit("required fields are missing");
    }

    global $db;
    $name = sanitize_str($_POST['name']);
    $city = sanitize_str($_POST['city']);
    $conference = sanitize_str($_POST['conference']);
    $division = sanitize_str($_POST['division']);
    // teams can have no stadiums
    $stadium_id = !empty($_POST['stadium_id']) ? intval($_POST['stadium_id']) : null;

    $valid_conferences = ['NFC', 'AFC'];
    $valid_divisions = ['North', 'South', 'East', 'West'];
    if (!in_array($conference, $valid_conferences) || !in_array($division, $valid_divisions)) {
        display_error_exit("invalid conference or division");
    }

    $query = "
        INSERT INTO Team
            (name, city, conference, division, stadium_id)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt  = prepare_with_perms($db, $query);
    if (!$stmt->bind_param("ssssi", $name, $city, $conference, $division, $stadium_id) || !$stmt->execute()) {
        display_error_exit("failed to add team");
    }
    $stmt->close();

    header("Location: manage_team_page.php");
    exit;
?>