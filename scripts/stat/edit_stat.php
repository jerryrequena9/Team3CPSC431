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

    $field = trim($_POST['stat_field']);
    $stat_id = intval($_POST['stat_id']);
    $value = intval($_POST['stat_value']);
    $player_id = intval($_POST['player_id']);
    $game_id = intval($_POST['game_id']);

    global $db;

    // Get coach id if it exists
    $query = "
        SELECT coach_id
        FROM Coach
        WHERE user_id = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $_SESSION['UserID']);
    try {
        $stmt->execute();
        $stmt->bind_result($user_coach_id);

        $is_coach = $stmt->fetch();
        $stmt->close();

        if ($is_coach) {
            // If the user is a coach, check that they are editing stats
            // only of players they coach
            $query = "
                SELECT 1
                FROM Player_Team pt
                JOIN Coach c
                    ON c.team_id = pt.team_id
                JOIN Game g
                    ON g.home_team_id = pt.team_id OR g.away_team_id = pt.team_id
                JOIN Team_Season ts
                    ON ts.team_id = pt.team_id AND ts.season_id = g.season_id
                WHERE pt.player_id = ?
                AND g.game_id = ?
                AND c.user_id = ?
                AND pt.end_date IS NULL
                LIMIT 1
            ";

            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("iii", $player_id, $game_id, $_SESSION['UserID']);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                error("You can only update stats for players you currently coach", "../../pages/stat_page.php");
            }
            $stmt->close();
        }
    } catch (mysqli_sql_exception $e) {
        error("Stat not updated", "../../pages/stat_page.php");
    }

    // Update Stat
    $query = "
        UPDATE Stat s
        SET s.$field = ?
        WHERE s.stat_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param('ii', $value, $stat_id);
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
