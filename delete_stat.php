<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
  }

  // For now, this allows a coach to delete ANY stat
  $query = "
      DELETE s FROM Stat s
      WHERE s.stat_id = ?
  ";
  $stat_id = intval($_POST['delete_stat_id']);

  global $db;
  $stmt = prepare_with_perms($db, $query);
  if (!$stmt || !$stmt->bind_param("i", $stat_id) || !$stmt->execute()) {
    display_error_exit("failed to delete stat");
  }

  if ($stmt->affected_rows == 0) {
    err_permission_denied();
  }
  $stmt->close();

  header('Location: manage_player_stats_page.php');
  exit;
?>