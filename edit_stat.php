<?php
    require_once('StartSession.php');
    require_once('helpers.php');
    require_once('html_components.php');

    check_valid_user();

    if (!is_valid_post($_POST)) {
        display_error_exit("required fields are missing");
    }

    // mysql doesn't allow you have a parameterized column name apparently
    $allowed_fields = ['touchdowns', 'passing_yards', 'rushing_yards', 'receiving_yards', 'tackles', 'interceptions'];
    $field = sanitize_str($_POST['edit_stat_field']);
    $stat_id = intval($_POST['edit_stat_id']);
    $value = intval($_POST['edit_stat_value']);
    if (!in_array($field, $allowed_fields)) {
        display_error_exit("invalid stat field");
    }

    if ($value < 0) {
        display_error_exit("stat value cannot be negative");
    }

    // For now, this allows a coach to edit ANY stat
    // we hardcode $field, but it's checked above
    $query = "
        UPDATE Stat s
        SET s.$field = ?
        WHERE s.stat_id = ?
    ";

    global $db;
    $stmt = prepare_with_perms($db, $query);
    if (!$stmt->bind_param('ii', $value, $stat_id) || !$stmt->execute()) {
        display_error_exit("failed to update stat");
    }
    if ($stmt->affected_rows == 0) {
        err_permission_denied();
    }
    $stmt->close();

    header('Location: manage_player_stats_page.php');
    exit;
?>