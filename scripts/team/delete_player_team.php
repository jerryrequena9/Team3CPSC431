<?php
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../../pages/html_components.php");
    require_once(__DIR__ . "/../helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields are missing", "../../pages/team_page.php");
    }

    global $db;

    $player_team_id = intval($_POST['player_team_id']);
    $player_id = intval($_POST['player_id']);

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
            // Coaches can only remove players from their own team
            $query = "
                SELECT 1
                FROM Player_Team pt
                JOIN Coach c
                    ON c.team_id = pt.team_id
                WHERE pt.player_id = ?
                AND c.user_id = ?
                AND pt.end_date IS NULL
                LIMIT 1
            ";

            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $player_id, $_SESSION['UserID']);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                error("You can only remove players from the team you coach", "../../pages/team_page.php");
            }
            $stmt->close();
        }
    } catch (mysqli_sql_exception $e) {
        error("Player not removed from team", "../../pages/team_page.php");
    } 

    // Remove the player from their team
    $query = "
        UPDATE Player_Team
        SET end_date = CURDATE()
        WHERE player_team_id = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $player_team_id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            error("Player not removed from team", "../../pages/team_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Player not removed from team", "../../pages/team_page.php");
    } finally {
        $stmt->close();
    }
    success("Player removed from team", "../../pages/team_page.php");
?>
