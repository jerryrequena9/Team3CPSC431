<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();
    global $db;

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/game_page.php");
    }

    $game_id = intval($_POST['game_id']);
    $season_id = intval($_POST['season_id']);
    $home_team_id = intval($_POST['home_team_id']);
    $away_team_id = intval($_POST['away_team_id']);

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
                error("Coaches can only delete games involving their own team", "../../pages/game_page.php");
            }
        }
    } catch (mysqli_sql_exception $e) {
        error("Game not deleted", "../../pages/game_page.php");
    }

    try {
        // Get home and away score
        $query = "
            SELECT home_score, away_score
            FROM Game
            WHERE game_id = ?
        ";
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->bind_result($home_score, $away_score);
        if (!$stmt->fetch()) {
            error("Game not deleted", "../../pages/game_page.php");
        }
        $stmt->close();

        // Find winner and loser
        if ($home_score > $away_score) {
            $winner_id = $home_team_id;
            $loser_id = $away_team_id;
        } else if ($away_score > $home_score) {
            $winner_id = $away_team_id;
            $loser_id = $home_team_id;
        } else {
            // Check tie
            $winner_id = null;
            $loser_id  = null;
        }

        // Use a transaction to add a game and update wins and losses
        // A transaction is necessary because if updating wins or losses fails, the game shouldn't be added.
        $db->begin_transaction();

        // Delete game
        $query = "
            DELETE FROM Game
            WHERE game_id = ?
        ";
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->close();

        // Tie
        if (is_null($winner_id) && is_null($loser_id)) {
            $query = "
                UPDATE Team_Season
                SET ties = ties - 1
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
            // Decrement wins for winner
            $update_wins = "
                UPDATE Team_Season
                SET wins = wins - 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $update_wins);
            $stmt->bind_param("ii", $winner_id, $season_id);
            $stmt->execute();
            $stmt->close();

            // Decrement losses for loser
            $update_losses = "
                UPDATE Team_Season
                SET losses = losses - 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $update_losses);
            $stmt->bind_param("ii", $loser_id, $season_id);
            $stmt->execute();
            $stmt->close();
        }

        // Commit
        $db->commit();
        success("Game deleted and team records updated", "../../pages/game_page.php");

    } catch (mysqli_sql_exception $e) {
        // Rollback on DB error
        $db->rollback();

        if ($e->getCode() == 1451) {
            error("Game not deleted. Game is referenced by another entity", "../../pages/game_page.php");
        }
        echo $e->getMessage();
        exit;
        error("Game not deleted", "../../pages/game_page.php");
    } catch (Exception $e) {
        $db->rollback();
        error("Game not deleted", "../../pages/game_page.php");
    }
?>