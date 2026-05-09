<?php
    require_once('StartSession.php');
    require_once('helpers.php');
    require_once('html_components.php');

    check_valid_user();

    if (!is_valid_post($_POST)) {
        display_error_exit("required fields are missing");
    }

    /*
     * Only allow known Stat columns to be updated.
     * Column names cannot be parameterized, so we whitelist them before placing
     * the column name directly into the SQL statement.
     */
    $allowed_fields = [
        'touchdowns',
        'passing_yards',
        'rushing_yards',
        'receiving_yards',
        'tackles',
        'interceptions'
    ];

    $field = sanitize_str($_POST['edit_stat_field']);
    $stat_id = intval($_POST['edit_stat_id']);
    $value = intval($_POST['edit_stat_value']);

    if (!in_array($field, $allowed_fields, true)) {
        display_error_exit("invalid stat field");
    }

    if ($stat_id <= 0) {
        display_error_exit("invalid stat id");
    }

    if ($value < 0) {
        display_error_exit("stat value cannot be negative");
    }

    /*
     * Authorization should be enforced by the database account permissions.
     * Manager/Coach DB users should be the only accounts with UPDATE permission
     * on Stat. Fan/Player should fail here even if they bypass the UI.
     * Coaches should not be able to edit stats for players outside their team.
     * This query restricts the update to stats connected to the coach's team.
     *
     * This assumes the logged-in coach has a session value for team_id.
     */
    $user_role = $_SESSION['UserRole'] ?? '';
    $coach_team_id = isset($_SESSION['team_id']) ? intval($_SESSION['team_id']) : 0;

    global $db;

    if ($user_role === 'Coach') {
        if ($coach_team_id <= 0) {
            display_error_exit("coach team is missing from session");
        }

        /*
         * Coach-scoped update:
         * The JOIN prevents a Coach from editing a Stat row unless that stat
         * belongs to a player on the Coach's own team.
         */
        $query = "
            UPDATE Stat s
            INNER JOIN Player p ON s.player_id = p.player_id
            INNER JOIN Player_Team pt ON p.player_id = pt.player_id
            SET s.$field = ?
            WHERE s.stat_id = ?
              AND pt.team_id = ?
        ";

        $stmt = prepare_with_perms($db, $query);

        if (!$stmt->bind_param('iii', $value, $stat_id, $coach_team_id) || !$stmt->execute()) {
            display_error_exit("failed to update stat");
        }
    } else {
        /*
         * Manager-level update:
         * Managers can update any stat, but this should still rely on the
         * Manager DB account having UPDATE permission on Stat.
         */
        $query = "
            UPDATE Stat s
            SET s.$field = ?
            WHERE s.stat_id = ?
        ";

        $stmt = prepare_with_perms($db, $query);

        if (!$stmt->bind_param('ii', $value, $stat_id) || !$stmt->execute()) {
            display_error_exit("failed to update stat");
        }
    }

    /*
     * If zero rows were affected, either the stat does not exist, the Coach does
     * not own that player's team, or the submitted value was the same as before.
     */
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header('Location: manage_player_stats_page.php?error=stat_not_updated');
        exit;
    }

    $stmt->close();

    header('Location: manage_player_stats_page.php?success=stat_updated');
    exit;
?>
