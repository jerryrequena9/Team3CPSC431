<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/player_page.php");
    }

    $player_id = intval($_POST['player_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $position = trim($_POST['position']);
    $status = trim($_POST['status']);

    global $db;

    $query = "
        UPDATE Player
        SET first_name = ?, last_name = ?, position = ?, status = ?
        WHERE player_id = ?
    ";

    try {
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("ssssi", $first_name, $last_name, $position, $status, $player_id);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            error("Player not updated", "../../pages/player_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        error("Player not updated", "../../pages/player_page.php");
    } finally {
        $stmt->close();
    }
    success("Player updated", "../../pages/player_page.php");
?>