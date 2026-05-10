<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();

    global $db;

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/season_page.php");
    }

    $season_id = intval($_POST['season_id']);
    $team_id = intval($_POST['team_id']);

    $query = "
        UPDATE Season s
        JOIN Team t ON t.team_id = ?
        SET s.champion = ?
        WHERE s.season_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("iii", $team_id, $team_id, $season_id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
          error("Champion not updated", "../../pages/season_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Champion not set", "../../pages/season_page.php");
    } finally {
      $stmt->close();
    }

    success("Champion updated", "../../pages/season_page.php");
?>
