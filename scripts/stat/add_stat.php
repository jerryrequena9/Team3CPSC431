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

    // Check if player played in this game
    $query = "
        SELECT 1
        FROM Player_Team pt
        JOIN Game g ON g.season_id = ?
        WHERE pt.player_id = ? AND pt.team_id = ?
        LIMIT 1
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("iii", $season_id, $player_id, $team_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        error("Invalid player/team/season combination", "../../pages/stat_page.php");
    }

    $stmt->close();

    $query = "
        INSERT INTO Stat (
            player_id, game_id, touchdowns, passing_yards,
            rushing_yards, receiving_yards, tackles, interceptions
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