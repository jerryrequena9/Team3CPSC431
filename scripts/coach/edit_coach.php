<?php
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../helpers.php");

    if (!is_valid_post($_POST, ['team_id'])) {
        error("Required fields missing", "../../pages/coach_page.php");
    }
    $coach_id = intval($_POST['coach_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    if ($_POST['team_id'] !== '') {
        $team_id = intval($_POST['team_id']);
    } else {
        $team_id = null;
    }

    global $db;

    $query = "
        UPDATE Coach
        SET first_name = ?, last_name = ?, team_id = ?
        WHERE coach_id = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ssii", $first_name, $last_name, $team_id, $coach_id);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        error("Coach was not updated", "../../pages/coach_page.php");
    } finally {
        $stmt->close();
    }
    success("Coach updated successfully", "../../pages/coach_page.php");
?>