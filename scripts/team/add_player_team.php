<?php
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../../pages/html_components.php");
    require_once(__DIR__ . "/../helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields are missing", "../../pages/team_page.php");
    }

    global $db;
    $player_id = intval($_POST['add_player_id']);
    $team_id = intval($_POST['add_team_id']);

    // Check if user is a coach
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
            // If the user is a coach, they can only
            // add active players who are not on another team
            $query = "
                SELECT 1
                FROM Player p
                LEFT JOIN Player_Team pt 
                    ON pt.player_id = p.player_id AND pt.end_date IS NOT NULL
                WHERE p.player_id = ? AND p.status = 'Active'
                LIMIT 1
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("i", $player_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                error("Attempted to add inactive player or player on another team", "../../pages/team_page.php");
            }
            $stmt->close();
        } else {
            // If the user is not a coach, they can add any active player
            $query = "
                SELECT 1
                FROM Player
                WHERE player_id = ? AND status = 'Active'
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("i", $player_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                error("Player is inactive", "../../pages/team_page.php");
            }
        }
    } catch (mysqli_sql_exception $e) {
        error("Player not added to team", "../../pages/team_page.php");
    }

    $query = "
        INSERT INTO Player_Team (player_id, team_id, start_date, end_date)
        VALUES (?, ?, CURDATE(), NULL)
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ii", $player_id, $team_id);
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 4025) {
            if (strpos($stmt->error, 'uniq_active_team') !== false) {
                error("Player can only be on one team at a time", "../../pages/team_page.php");
            }
        }
        error("Player not added to team", "../../pages/team_page.php");
    } finally {
        $stmt->close();
    }
    success("Player added to team", "../../pages/team_page.php");
?>
