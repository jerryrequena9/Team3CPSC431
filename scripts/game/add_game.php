<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();
    global $db;

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/game_page.php");
    }

    $season_id = intval($_POST['season_id']);
    $home_team_id = intval($_POST['home_team_id']);
    $away_team_id = intval($_POST['away_team_id']);
    $week = intval($_POST['week']);
    $date = trim($_POST['date']);
    $home_score = intval($_POST['home_score']);
    $away_score = intval($_POST['away_score']);

    $query = "
        INSERT INTO Game
            (season_id, home_team_id, away_team_id, date, week, home_score, away_score, stadium_id)
        SELECT ?, ?, ?, ?, ?, ?, ?, t.stadium_id
        FROM Team t
        WHERE t.team_id = ?;
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("iiisiiii", $season_id, $home_team_id, $away_team_id, $date, $week, $home_score, $away_score, $home_team_id);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 4025) {
            if (strpos($e->getMessage(), "home") !== false) {
                error("Home and away team must be different", "../../pages/game_page.php");
            } else if (strpos($e->getMessage(), "week") !== false) {
                error("Week must be between 1 and 16", "../../pages/game_page.php");
            }
        } else if ($e->getCode() == 1062) {
            error("A team already plays on that week", "../../pages/game_page.php");
        }
        error("Game not added", "../../pages/game_page.php");
    } finally {
        $stmt->close();
    }
    success("Game added", "../../pages/game_page.php");
?>
