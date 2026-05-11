<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();
    global $db;

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/game_page.php");
    }

    $home_team_id = intval($_POST['home_team_id']);
    $away_team_id = intval($_POST['away_team_id']);
    $season_id = intval($_POST['season_id']);
    $week = intval($_POST['week']);
    $date = $_POST['date'];
    $home_score = intval($_POST['home_score']);
    $away_score = intval($_POST['away_score']);
    $game_id = intval($_POST['game_id']);

    $query = "
        UPDATE Game
        SET season_id = ?, home_team_id = ?, away_team_id = ?,
            date = ?, week = ?, home_score = ?, away_score = ?
        WHERE game_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("iiisiiii", $season_id, $home_team_id, $away_team_id, $date, $week, $home_score, $away_score, $game_id);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 4025) {
            error("Home and away team must be different", "../../pages/game_page.php");
        } else if ($e->getCode() == 1062) {
            error("A team already plays on that week", "../../pages/game_page.php");
        } else {
            error("Game not updated", "../../pages/game_page.php");
        }
    } finally {
        $stmt->close();
    }
    success("Game updated", "../../pages/game_page.php");
?>