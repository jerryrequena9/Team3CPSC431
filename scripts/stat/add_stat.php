<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields are missing", "../../pages/stat_page.php");
    }

    global $db;

    $player_id = intval($_POST['player_id']);
    $game_id = intval($_POST['game_id']);
    $touchdowns = intval($_POST['touchdowns']);
    $passing_yards = intval($_POST['passing_yards']);
    $rushing_yards = intval($_POST['rushing_yards']);
    $receiving_yards = intval($_POST['receiving_yards']);
    $tackles = intval($_POST['tackles']);
    $interceptions = intval($_POST['interceptions']);

    // Query to get the coach_id for this user
    $query = "
        SELECT coach_id
        FROM Coach
        WHERE user_id = ?
    ";
    
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $_SESSION['UserID']);
    
    $coach = false;
    try {
        $stmt->execute();
        $stmt->bind_result($user_coach_id);

        $is_coach = $stmt->fetch();
        $stmt->close();
        if ($is_coach) {
            // Checks that:
            // game was played in the season
            // team played in the game
            // player was on the team when the game was played
            // player is on the team that the coach coaches
            $query = "
                SELECT 1
                FROM Player_Team pt
                JOIN Coach c
                    ON c.team_id = pt.team_id
                JOIN Game g
                    ON g.home_team_id = pt.team_id OR g.away_team_id = pt.team_id
                JOIN Team_Season ts
                    ON ts.team_id = pt.team_id
                    AND ts.season_id = g.season_id
                WHERE pt.player_id = ?
                AND c.user_id = ?
                AND g.game_id = ?
                AND pt.end_date IS NULL
                LIMIT 1
            ";

            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("iii", $player_id, $_SESSION['UserID'], $game_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                error("You can only add stats for players you coach who played in this game", "../../pages/stat_page.php");
            }
        } else {
            // User is not a coach
            // Checks that:
            // game was played in the season
            // team played in the game
            // player was on the team when the game was played
            $query = "
                SELECT 1
                FROM Player_Team pt
                JOIN Game g
                    ON g.home_team_id = pt.team_id OR g.away_team_id = pt.team_id
                JOIN Team_Season ts
                    ON ts.team_id = pt.team_id
                    AND ts.season_id = g.season_id
                WHERE pt.player_id = ?
                AND g.game_id = ?
                AND pt.end_date IS NULL
                LIMIT 1
            ";

            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $player_id, $game_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                error("Player did not play in this game", "../../pages/stat_page.php");
            }
        }
    } catch (mysqli_sql_exception $e) {
        error("Stat not added", "../../pages/stat_page.php");
    } finally {
        $stmt->close();
    }

    // Insert Stat
    $query = "
        INSERT INTO Stat (
            player_id,
            game_id,
            touchdowns,
            passing_yards,
            rushing_yards,
            receiving_yards,
            tackles,
            interceptions
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param(
        "iiiiiiii",
        $player_id, $game_id, $touchdowns, $passing_yards,
        $rushing_yards, $receiving_yards, $tackles, $interceptions
    );

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($db->errno === 1062) {
            error("Stat for this game and player already exists", "../../pages/stat_page.php");
        }
        error("Stat not added", "../../pages/stat_page.php");
    } finally {
        $stmt->close();
    }
    success("Stat added", "../../pages/stat_page.php");
?>