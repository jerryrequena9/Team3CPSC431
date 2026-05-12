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
                error("Coaches can only update games involving their own team", "../../pages/game_page.php");
            }
        }
    } catch (mysqli_sql_exception $e) {
        error("Game not updated", "../../pages/game_page.php");
    }

    try {
        // Same idea as add and delete game: we need a transaction.
        // However, edit game is a bit more work.
        // In addition to updating the wins and losses for the newly updated game,
        // we also need to revert the wins and losses for the outdated game.
        $db->begin_transaction();

        // Find outdated game info
        $query = "
            SELECT season_id, home_team_id, away_team_id, home_score, away_score
            FROM Game
            WHERE game_id = ?
        ";
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->bind_result($old_season_id, $old_home_team_id, $old_away_team_id, $old_home_score, $old_away_score);
        $stmt->fetch();
        $stmt->close();

        // Find outdated game winner and loser
        if ($old_home_score > $old_away_score) {
            $old_winner = $old_home_team_id;
            $old_loser = $old_away_team_id;
        } else if ($old_away_score > $old_home_score) {
            $old_winner = $old_away_team_id;
            $old_loser = $old_home_team_id;
        } else {
            $old_winner = null;
            $old_loser = null;
        }

        // Tie
        if (is_null($old_winner) && is_null($old_loser)) {
            $query = "
                UPDATE Team_Season
                SET ties = ties - 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $old_home_team_id, $season_id);
            $stmt->execute();
            $stmt->close();

            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $old_away_team_id, $season_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Decrement old winner's wins
            $query = "
                UPDATE Team_Season
                SET wins = wins - 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $old_winner, $old_season_id);
            $stmt->execute();
            $stmt->close();

            // Decrement old loser's losses
            $query = "
                UPDATE Team_Season
                SET losses = losses - 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $old_loser, $old_season_id);
            $stmt->execute();
            $stmt->close();
        }

        // Update game
        $query = "
            UPDATE Game
            SET season_id = ?, home_team_id = ?, away_team_id = ?,
                date = ?, week = ?, home_score = ?, away_score = ?
            WHERE game_id = ?
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
            $game_id
        );
        $stmt->execute();
        $stmt->close();

        // Update new winner and loser
        if ($home_score > $away_score) {
            $new_winner = $home_team_id;
            $new_loser = $away_team_id;
        } else if ($away_score > $home_score) {
            $new_winner = $away_team_id;
            $new_loser = $home_team_id;
        } else {
            $new_winner = null;
            $new_loser = null;
        }

        // Tie
        if (is_null($new_winner) && is_null($new_winner)) {
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
            // Increment new winner's wins
            $query = "
                UPDATE Team_Season
                SET wins = wins + 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $new_winner, $season_id);
            $stmt->execute();
            $stmt->close();

            // Increment new loser's losses
            $query = "
                UPDATE Team_Season
                SET losses = losses + 1
                WHERE team_id = ? AND season_id = ?
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("ii", $new_loser, $season_id);
            $stmt->execute();
            $stmt->close();
        }

        $db->commit();
        success("Game updated and team records adjusted", "../../pages/game_page.php");

    } catch (mysqli_sql_exception $e) {
        $db->rollback();

        if ($e->getCode() == 4025) {
            error("Home and away team must be different", "../../pages/game_page.php");
        } else if ($e->getCode() == 1062) {
            error("A team already plays on that week", "../../pages/game_page.php");
        } else {
            error("Game not updated", "../../pages/game_page.php");
        }
   } catch (mysqli_sql_exception $e) {
        $db->rollback();
        error("Game not updated", "../../pages/game_page.php");
   }
?>