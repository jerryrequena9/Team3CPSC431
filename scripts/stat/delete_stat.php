<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');
  require_once(__DIR__ . '/../../pages/html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/user_page.php");
  }

  $stat_id = intval($_POST['delete_stat_id']);

  $user_role = $_SESSION['UserRole'] ?? '';
  $coach_team_id = isset($_SESSION['team_id']) ? intval($_SESSION['team_id']) : 0;

  global $db;

  if ($user_role === 'Coach') {
    if ($coach_team_id <= 0) {
      error("Required fields are missing", "../../pages/user_page.php");
    }

    /*
     * Coaches may only delete stats for players assigned to their own team.
     * This prevents a Coach from changing the hidden stat_id and deleting
     * another team's player stat.
     */
    $query = "
      DELETE s
      FROM Stat s
      INNER JOIN Player_Team pt ON s.player_id = pt.player_id
      WHERE s.stat_id = ?
        AND pt.team_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ii", $stat_id, $coach_team_id);
    
    try {
      $stmt->execute();
    } catch (mysqli_sql_exception $e) {
      error("Stat not deleted", "../../pages/stat_page.php");
    } finally {
      $stmt->close();
    }
  } else {
    /*
     * Manager-level delete:
     * Managers can delete any stat, but the database account still needs
     * DELETE permission on the Stat table.
     */
    $query = "
      DELETE s
      FROM Stat s
      WHERE s.stat_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);

    if (!$stmt || !$stmt->bind_param("i", $stat_id) || !$stmt->execute()) {
      error("Stat not deleted", "../../pages/stat_page.php");
    }
  }

  if ($stmt->affected_rows == 0) {
    $stmt->close();
    error("Stat not deleted", "../../pages/stat_page.php");
  }

  $stmt->close();

  success("Stat deleted", "../../pages/stat_page.php");
?>
