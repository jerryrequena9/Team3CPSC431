<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    check_valid_user();

    /*
     * Team editing is Manager-only.
     * This prevents Coach/Player/Fan users from bypassing the UI and posting
     * directly to this file.
     */
    if ($_SESSION['UserRole'] !== 'Manager') {
        err_permission_denied();
    }

    if (!is_valid_post($_POST)) {
        display_error_exit("required fields are missing");
    }

    global $db;

    $team_id = intval($_POST['team_id']);
    $name = sanitize_str($_POST['name']);
    $city = sanitize_str($_POST['city']);
    $conference = sanitize_str($_POST['conference']);
    $division = sanitize_str($_POST['division']);

    if ($team_id <= 0) {
        display_error_exit("invalid team id");
    }

    if ($name === '' || $city === '') {
        display_error_exit("team name and city are required");
    }

    /*
     * These are controlled football domain values.
     * They are not permission hardcoding.
     */
    $valid_conferences = ['NFC', 'AFC'];
    $valid_divisions = ['North', 'South', 'East', 'West'];

    if (!in_array($conference, $valid_conferences, true) || !in_array($division, $valid_divisions, true)) {
        display_error_exit("invalid conference or division");
    }

    /*
     * Actual write enforcement should still come from the Manager database
     * account having UPDATE permission on Team.
     */
    $query = "
        UPDATE Team
        SET name = ?, city = ?, conference = ?, division = ?
        WHERE team_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);

    if (!$stmt || !$stmt->bind_param("ssssi", $name, $city, $conference, $division, $team_id) || !$stmt->execute()) {
        display_error_exit("failed to update team");
    }

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header("Location: manage_team_page.php?error=team_not_updated");
        exit;
    }

    $stmt->close();

    header("Location: manage_team_page.php?success=team_updated");
    exit;
?>
