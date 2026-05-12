<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');
  require_once(__DIR__ . '/../../pages/html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/user_page.php");
  }

  $stat_id = intval($_POST['stat_id']);
  $player_id = intval($_POST['player_id']);
  $game_id = intval($_POST['game_id']);

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
        // If a coach, check that the stat belongs to a player
        // on his/her team
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
            error("You can only delete stats for players you currently coach", "../../pages/stat_page.php");
        }
        $stmt->close();
    }
  } catch (mysqli_sql_exception $e) {
    error("Stat not deleted", "../../pages/stat_page.php");
  }

  // Delete the stat
  $query = "
    DELETE FROM Stat
    WHERE stat_id = ?
  ";
  $stmt = prepare_with_perms($db, $query);
  $stmt->bind_param("i", $stat_id);

  try {
      $stmt->execute();
  } catch (mysqli_sql_exception $e) {
      error("Stat not deleted", "../../pages/stat_page.php");
  } finally {
    $stmt->close();
  }
  success("Stat deleted", "../../pages/stat_page.php");
?>

