<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST)) {
        display_error_exit("required fields are missing");
    }

    global $db;
    $team_id  = intval($_POST['team_id']);
    $name = sanitize_str($_POST['name']);
    $city = sanitize_str($_POST['city']);
    $conference = sanitize_str($_POST['conference']);
    $division = sanitize_str($_POST['division']);

    $valid_conferences = ['NFC', 'AFC'];
    $valid_divisions = ['North', 'South', 'East', 'West'];
    if (!in_array($conference, $valid_conferences) || !in_array($division, $valid_divisions)) {
        display_error_exit("invalid conference or division");
    }

    $query = "
        UPDATE Team
        SET name = ?, city = ?, conference = ?, division = ?
        WHERE team_id = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    if (!$stmt->bind_param("ssssi", $name, $city, $conference, $division, $team_id) || !$stmt->execute()) {
        display_error_exit("failed to update team");
    }
    if ($stmt->affected_rows == 0) {
        err_permission_denied();
    }
    $stmt->close();

    header("Location: manage_team_page.php");
    exit;
?>