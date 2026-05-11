<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');
    require_once(__DIR__ . '/../../pages/html_components.php');

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields are missing", "../../pages/stat_page.php");
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

    $field = trim($_POST['edit_stat_field']);
    $stat_id = intval($_POST['edit_stat_id']);
    $value = intval($_POST['edit_stat_value']);
    /*
     * Authorization should be enforced by the database account permissions.
     * Manager/Coach DB users should be the only accounts with UPDATE permission
     * on Stat. Fan/Player should fail here even if they bypass the UI.
     * Coaches should not be able to edit stats for players outside their team.
     * This query restricts the update to stats connected to the coach's team.
     *
     */
    global $db;

    /*
    * Coach-scoped update:
    * The JOIN prevents a Coach from editing a Stat row unless that stat
    * belongs to a player on the Coach's own team.
    */
    $query = "
        UPDATE Stat s
        JOIN Player p ON s.player_id = p.player_id
        JOIN Player_Team pt ON p.player_id = pt.player_id
        LEFT JOIN Coach c ON pt.team_id = c.team_id
        SET s.$field = ?
        WHERE s.stat_id = ?
        AND (
                (c.user_id IS NOT NULL AND c.user_id = ?)
                OR c.user_id IS NULL
            )
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param('iii', $value, $stat_id, $_SESSION['UserID']);
    try {
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            error("Stat not updated", "../../pages/stat_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Stat not updated", "../../pages/stat_page.php");
    } finally {
        $stmt->close();
    }

    success("Stat updated", "../../pages/stat_page.php");
?>
