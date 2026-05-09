<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
  }

  $stat_id = intval($_POST['delete_stat_id']);

  if ($stat_id <= 0) {
    display_error_exit("invalid stat id");
  }

  $user_role = $_SESSION['UserRole'] ?? '';
  $coach_team_id = isset($_SESSION['team_id']) ? intval($_SESSION['team_id']) : 0;

  global $db;

  if ($user_role === 'Coach') {
    if ($coach_team_id <= 0) {
      display_error_exit("coach team is missing from session");
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

    if (!$stmt || !$stmt->bind_param("ii", $stat_id, $coach_team_id) || !$stmt->execute()) {
      display_error_exit("failed to delete stat");
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
      display_error_exit("failed to delete stat");
    }
  }

  if ($stmt->affected_rows == 0) {
    $stmt->close();
    header('Location: manage_player_stats_page.php?error=stat_not_deleted');
    exit;
  }

  $stmt->close();

  header('Location: manage_player_stats_page.php?success=stat_deleted');
  exit;
?>
