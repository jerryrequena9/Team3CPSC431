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

    if ($week < 1 || $week > 16) {
        error("Week must be between 1 and 16", "../../pages/game_page.php");
    }
    if ($home_team_id == $away_team_id) {
        error("Home and away team cannot be the same", "../../pages/game_page.php");
    }

    // Check if user is a coach and get the team they coach
    $query = "
        SELECT team_id
        FROM Coach
        WHERE user_id = ?
        LIMIT 1
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $_SESSION['UserID']);
    try {
        $stmt->execute();
        $stmt->bind_result($coached_team_id);
        $is_coach = $stmt->fetch();
        $stmt->close();

        if ($is_coach) {
            // Ensure a coach can only add games if they coach a team in the game
            if ($home_team_id !== $coached_team_id && $away_team_id !== $coached_team_id) {
                error("Coaches can only add games involving their own team", "../../pages/game_page.php");
            }
        }
    } catch (mysqli_sql_exception $e) {
        error("Game not added", "../../pages/game_page.php");
    }

    try {
        // Use a transaction to add a game and update wins and losses
        // A transaction is necessary because if updating wins or losses fails, the game shouldn't be added.
        $db->begin_transaction();

        // Add a game
        $query = "
            INSERT INTO Game (
                season_id,
                home_team_id,
                away_team_id,
                date,
                week,
                home_score,
                away_score,
                stadium_id
            )
            SELECT ?, ?, ?, ?, ?, ?, ?, t.stadium_id
            FROM Team t
            WHERE t.team_id = ?;
        ";
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param(
            "iiisiiii",
            $season_id,
            $home_team_id,
            $away_team_id,
            $date,
            $week,
            $home_score,
            $away_score,
            $home_team_id
        );
        $stmt->execute();
        $stmt->close();

        // Find winner and loser
        if ($home_score > $away_score) {
            $winner_id = $home_team_id;
            $loser_id = $away_team_id;
        } else if ($away_score > $home_score) {
            $winner_id = $away_team_id;
            $loser_id = $home_team_id;
        } else {
            // Allow ties
            $winner_id = null;
            $loser_id = null;
        }

        // Tie
        if (is_null($winner_id) && is_null($loser_id)) {
            $query = "
                UPDATE Team_Season
                SET ties = ties + 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $home_team_id, $season_id);
            $stmt->execute();
            $stmt->close();

            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $away_team_id, $season_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Update wins
            $query = "
                UPDATE Team_Season
                SET wins = wins + 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $winner_id, $season_id);
            $stmt->execute();
            $stmt->close();

            // Update losses
            $query = "
                UPDATE Team_Season
                SET losses = losses + 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $loser_id, $season_id);
            $stmt->execute();
            $stmt->close();
        }

        $db->commit();

        success("Game added", "../../pages/game_page.php");
    } catch (mysqli_sql_exception $e) {
        // Roll back on any error
        $db->rollback();

        if ($e->getCode() == 4025) {
            if (strpos($e->getMessage(), "home_away_different") !== false) {
                error("Home and away team must be different", "../../pages/game_page.php");
            } else if (strpos($e->getMessage(), "valid_date") !== false) {
                error("Week must be between 1 and 16", "../../pages/game_page.php");
            }
        } else if ($e->getCode() == 1062) {
            if (strpos($e->getCode(), 'home_team_id') !== false) {
                throw new Exception("This home team already has a game in that week");
            } else if (strpos($e->getCode(), 'away_team_id') !== false) {
                throw new Exception("This away team already has a game in that week");
            }
        }

        error("Game not added", "../../pages/game_page.php");
    } catch (Exception $e) {
        $db->rollback();
        error("Game not added", "../../pages/game_page.php");
    }
?>
